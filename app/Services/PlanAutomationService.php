<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantPlanEnforcer;
use Illuminate\Support\Carbon;

class PlanAutomationService
{
    public const MODE_NONE = 'none';
    public const MODE_LIMITED = 'limited';
    public const MODE_SHARED = 'shared';

    private const STARTER_SHARED_EMAIL_EVENTS = [
        'account_owner_paid_signup_created',
        'account_owner_google_paid_signup_created',
        'team_member_invited',
        'customer_portal_invited',
        'setup_link_expiring',
        'setup_link_expired',
        'payment_successful',
        'payment_failed',
        'payment_recovered',
        'funnel_opt_in_submitted',
        'funnel_checkout_started',
        'funnel_payment_paid_customer',
        'funnel_checkout_abandoned',
        'funnel_order_delivery_updated',
        'funnel_upsell_accepted',
        'funnel_upsell_declined',
        'funnel_downsell_accepted',
        'funnel_downsell_declined',
        'receipt_uploaded',
        'receipt_auto_approved',
        'receipt_approved',
        'receipt_rejected',
        'commission_created',
        'commission_payable',
        'subscription_deadline_reminder_7_days_owner',
        'subscription_deadline_reminder_3_days_owner',
        'payout_account_pending_review',
        'payout_account_approved',
        'payout_account_rejected',
    ];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleNonSharedDispatch(string $eventName, array $payload): ?bool
    {
        $tenant = $this->resolveTenantFromPayload($payload);
        $mode = $this->modeForTenant($tenant);

        if ($mode === self::MODE_SHARED) {
            return null;
        }

        if ($mode === self::MODE_LIMITED) {
            return $this->handleLimitedDispatch($eventName, $payload, $tenant);
        }

        $this->recordWebhookState($eventName, $payload, 'skipped', [
            'reason' => 'plan_has_no_automation_entitlement',
            'automation_mode' => $mode,
        ]);

        return false;
    }

    public function modeForTenant(?Tenant $tenant): string
    {
        if (! $tenant) {
            return self::MODE_SHARED;
        }

        return $this->modeForPlan(
            app(TenantPlanEnforcer::class)->resolvePlan($tenant)
        );
    }

    public function modeForPlan(?Plan $plan): string
    {
        if (! $plan) {
            return self::MODE_SHARED;
        }

        if ((bool) $plan->automation_enabled) {
            return self::MODE_SHARED;
        }

        return strtolower(trim((string) $plan->code)) === 'starter'
            ? self::MODE_LIMITED
            : self::MODE_NONE;
    }

    public function usesSharedAutomation(?Tenant $tenant): bool
    {
        return $this->modeForTenant($tenant) === self::MODE_SHARED;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handleLimitedDispatch(string $eventName, array $payload, ?Tenant $tenant): ?bool
    {
        if ($this->starterUsesSharedEmailAutomation($eventName)) {
            return null;
        }

        if (in_array($eventName, [
            'account_owner_paid_signup_created',
            'team_member_invited',
            'customer_portal_invited',
            'setup_link_expiring',
            'setup_link_expired',
            'funnel_payment_paid_customer',
        ], true)) {
            $this->recordWebhookState($eventName, $payload, 'skipped', [
                'reason' => 'local_fallback_expected_from_caller',
                'automation_mode' => self::MODE_LIMITED,
            ]);

            return false;
        }

        return match ($eventName) {
            'account_owner_google_paid_signup_created',
            'payment_successful' => $this->sendLimitedWelcomeEmail($eventName, $payload, $tenant),
            'funnel_opt_in_submitted' => $this->sendLimitedCustomerEmail(
                $eventName,
                $payload,
                $tenant,
                'funnel_opt_in_submitted_customer',
                'Thank you for your interest',
                $this->limitedOptInBody($payload, $tenant)
            ),
            'funnel_checkout_abandoned' => $this->sendLimitedCustomerEmail(
                $eventName,
                $payload,
                $tenant,
                'funnel_checkout_abandoned_customer',
                'Your checkout is still available',
                $this->limitedAbandonedCheckoutBody($payload, $tenant)
            ),
            'payment_failed' => $this->sendLimitedOwnerNotification(
                $eventName,
                $payload,
                $tenant,
                'Action required: payment could not be completed',
                $this->limitedPaymentFailedBody($payload, $tenant)
            ),
            'payment_recovered' => $this->sendLimitedOwnerNotification(
                $eventName,
                $payload,
                $tenant,
                'Payment received successfully',
                $this->limitedPaymentRecoveredBody($payload, $tenant)
            ),
            'subscription_deadline_reminder_7_days_owner',
            'subscription_deadline_reminder_3_days_owner' => $this->sendLimitedOwnerNotification(
                $eventName,
                $payload,
                $tenant,
                $this->limitedSubscriptionReminderSubject($eventName),
                $this->limitedSubscriptionReminderBody($eventName, $tenant)
            ),
            default => $this->skipLimitedAdvancedEvent($eventName, $payload),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendLimitedWelcomeEmail(string $eventName, array $payload, ?Tenant $tenant): bool
    {
        $recipient = $this->resolveRecipientEmail($payload, $tenant);
        if ($recipient === '') {
            return $this->skipLimitedAdvancedEvent($eventName, $payload, 'missing_recipient');
        }

        $subject = $eventName === 'payment_successful'
            ? 'Payment received successfully'
            : 'Welcome to ' . config('app.name', 'Sales & Marketing Funnel System');

        $name = $this->resolveRecipientName($payload, $tenant);
        $planName = trim((string) ($payload['plan_name'] ?? $tenant?->subscription_plan ?? 'your plan'));
        $amount = isset($payload['amount']) && is_numeric($payload['amount'])
            ? 'Amount: PHP ' . number_format((float) $payload['amount'], 2)
            : null;
        $loginUrl = trim((string) ($payload['login_url'] ?? route('login')));

        $body = implode("\n", array_values(array_filter([
            'Hello ' . $name . ',',
            '',
            'Your account payment has been confirmed successfully.',
            $planName !== '' ? 'Plan: ' . $planName : null,
            $amount,
            '',
            'You can now continue by logging in here: ' . $loginUrl,
            '',
            'Thank you,',
            config('app.name', 'Sales & Marketing Funnel System'),
        ])));

        return $this->deliverLimitedEmail(
            $eventName,
            $payload,
            $tenant,
            $recipient,
            $subject,
            $body,
            false
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendLimitedCustomerEmail(
        string $eventName,
        array $payload,
        ?Tenant $tenant,
        string $template,
        string $subject,
        string $body,
    ): bool {
        $recipient = $this->resolveRecipientEmail($payload, $tenant);
        if ($recipient === '') {
            return $this->skipLimitedAdvancedEvent($eventName, $payload, 'missing_recipient');
        }

        return $this->deliverLimitedEmail(
            $template,
            $payload,
            $tenant,
            $recipient,
            $subject,
            $body,
            true
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendLimitedOwnerNotification(
        string $eventName,
        array $payload,
        ?Tenant $tenant,
        string $subject,
        string $body,
    ): bool {
        $recipient = $this->resolveOwnerEmail($tenant, $payload);
        if ($recipient === '') {
            return $this->skipLimitedAdvancedEvent($eventName, $payload, 'missing_owner_recipient');
        }

        return $this->deliverLimitedEmail(
            $eventName,
            $payload,
            $tenant,
            $recipient,
            $subject,
            $body,
            true
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function deliverLimitedEmail(
        string $template,
        array $payload,
        ?Tenant $tenant,
        string $recipient,
        string $subject,
        string $body,
        bool $billable,
    ): bool {
        $idempotencyKey = $this->resolveIdempotencyKey($template, $payload);

        if ($billable && $tenant) {
            app(TenantPlanEnforcer::class)->ensureCanSendOutboundMessages($tenant);
        }

        $result = app(TransactionalEmailService::class)->sendPlainText($recipient, $subject, $body, [
            'template' => $template,
            'event_name' => $template,
            'tenant_id' => $tenant?->id,
            'user_id' => isset($payload['user_id']) && is_numeric($payload['user_id']) ? (int) $payload['user_id'] : null,
            'lead_id' => isset($payload['lead_id']) && is_numeric($payload['lead_id']) ? (int) $payload['lead_id'] : null,
            'idempotency_key' => $idempotencyKey,
            'is_billable' => $billable,
            'requested_recipient_email' => $recipient,
            'source_event_name' => $payload['event_name'] ?? null,
            'automation_mode' => self::MODE_LIMITED,
        ]);

        $this->recordWebhookState($template, $payload, ($result['sent'] ?? false) ? 'processed' : 'failed', [
            'reason' => 'limited_local_delivery',
            'automation_mode' => self::MODE_LIMITED,
            'provider' => $result['provider'] ?? null,
            'recipient_email' => $recipient,
        ], $idempotencyKey);

        return (bool) ($result['sent'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function skipLimitedAdvancedEvent(string $eventName, array $payload, string $reason = 'starter_advanced_event_blocked'): bool
    {
        $this->recordWebhookState($eventName, $payload, 'skipped', [
            'reason' => $reason,
            'automation_mode' => self::MODE_LIMITED,
        ]);

        return false;
    }

    private function starterUsesSharedEmailAutomation(string $eventName): bool
    {
        return in_array($eventName, self::STARTER_SHARED_EMAIL_EVENTS, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveTenantFromPayload(array $payload): ?Tenant
    {
        $tenantId = isset($payload['tenant_id']) && is_numeric($payload['tenant_id'])
            ? (int) $payload['tenant_id']
            : 0;

        if ($tenantId <= 0) {
            return null;
        }

        return Tenant::query()->find($tenantId);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveRecipientEmail(array $payload, ?Tenant $tenant): string
    {
        foreach (['recipient_email', 'email'] as $key) {
            $value = trim((string) ($payload[$key] ?? ''));
            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return $value;
            }
        }

        return $this->resolveOwnerEmail($tenant, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveOwnerEmail(?Tenant $tenant, array $payload): string
    {
        $userId = isset($payload['user_id']) && is_numeric($payload['user_id'])
            ? (int) $payload['user_id']
            : 0;
        if ($userId > 0) {
            $user = User::query()->find($userId);
            if ($user && filter_var((string) $user->email, FILTER_VALIDATE_EMAIL)) {
                return (string) $user->email;
            }
        }

        $owner = $this->resolveAccountOwner($tenant);

        return ($owner && filter_var((string) $owner->email, FILTER_VALIDATE_EMAIL))
            ? (string) $owner->email
            : '';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveRecipientName(array $payload, ?Tenant $tenant): string
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $owner = $this->resolveAccountOwner($tenant);
        $ownerName = trim((string) ($owner?->name ?? ''));

        return $ownerName !== '' ? $ownerName : 'Customer';
    }

    private function resolveAccountOwner(?Tenant $tenant): ?User
    {
        if (! $tenant) {
            return null;
        }

        return User::query()
            ->where('tenant_id', $tenant->id)
            ->where(function ($query) {
                $query->where('role', 'account-owner')
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->where('slug', 'account-owner');
                    });
            })
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveIdempotencyKey(string $eventName, array $payload): string
    {
        $idempotencyKey = trim((string) ($payload['idempotency_key'] ?? ''));
        if ($idempotencyKey !== '') {
            return $idempotencyKey;
        }

        $eventId = trim((string) ($payload['event_id'] ?? ''));
        if ($eventId !== '') {
            return $eventName . ':' . $eventId;
        }

        return $eventName . ':' . md5(json_encode($payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $meta
     */
    private function recordWebhookState(
        string $eventName,
        array $payload,
        string $status,
        array $meta = [],
        ?string $idempotencyKey = null,
    ): void {
        app(DeliveryLogService::class)->record('webhook', [
            'tenant_id' => $payload['tenant_id'] ?? null,
            'user_id' => $payload['user_id'] ?? null,
            'lead_id' => $payload['lead_id'] ?? null,
            'event_name' => $eventName,
            'recipient' => 'local-plan-automation',
            'provider' => 'n8n',
            'status' => $status,
            'idempotency_key' => $idempotencyKey ?? $this->resolveIdempotencyKey($eventName, $payload),
            'meta' => array_merge($payload, $meta),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function limitedOptInBody(array $payload, ?Tenant $tenant): string
    {
        $name = $this->resolveRecipientName($payload, $tenant);
        $tenantName = trim((string) ($tenant?->company_name ?? 'our team'));

        return implode("\n", [
            'Hello ' . $name . ',',
            '',
            'Thank you for your interest. We have received your information successfully.',
            '',
            'A member of the ' . $tenantName . ' team may contact you soon with the next steps or additional details.',
            '',
            'Thank you,',
            $tenantName . ' via ' . config('app.name', 'Sales & Marketing Funnel System'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function limitedAbandonedCheckoutBody(array $payload, ?Tenant $tenant): string
    {
        $name = $this->resolveRecipientName($payload, $tenant);
        $tenantName = trim((string) ($tenant?->company_name ?? 'our team'));

        return implode("\n", [
            'Hello ' . $name . ',',
            '',
            'This is a quick reminder that your checkout is still available if you would like to continue your order.',
            '',
            'If you experienced any issue during checkout, please contact the ' . $tenantName . ' team for assistance.',
            '',
            'Thank you,',
            $tenantName . ' via ' . config('app.name', 'Sales & Marketing Funnel System'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function limitedPaymentFailedBody(array $payload, ?Tenant $tenant): string
    {
        $name = trim((string) ($this->resolveAccountOwner($tenant)?->name ?? 'Account Owner'));
        $tenantName = trim((string) ($tenant?->company_name ?? 'your workspace'));
        $invoiceId = trim((string) ($payload['invoice_id'] ?? ''));

        return implode("\n", array_values(array_filter([
            'Hello ' . $name . ',',
            '',
            'We were unable to complete the latest payment associated with your workspace.',
            '',
            'Business: ' . $tenantName,
            $invoiceId !== '' ? 'Invoice or reference: ' . $invoiceId : null,
            '',
            'Please review your billing details and complete the payment as soon as possible to avoid service interruption.',
            '',
            'Thank you,',
            config('app.name', 'Sales & Marketing Funnel System') . ' Billing Team',
        ])));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function limitedPaymentRecoveredBody(array $payload, ?Tenant $tenant): string
    {
        $name = trim((string) ($this->resolveAccountOwner($tenant)?->name ?? 'Account Owner'));
        $tenantName = trim((string) ($tenant?->company_name ?? 'your workspace'));
        $invoiceId = trim((string) ($payload['invoice_id'] ?? ''));

        return implode("\n", array_values(array_filter([
            'Hello ' . $name . ',',
            '',
            'This is a confirmation that your recent payment has been received successfully.',
            '',
            'Business: ' . $tenantName,
            $invoiceId !== '' ? 'Invoice or reference: ' . $invoiceId : null,
            '',
            'Your billing status should now continue normally.',
            '',
            'Thank you,',
            config('app.name', 'Sales & Marketing Funnel System') . ' Billing Team',
        ])));
    }

    private function limitedSubscriptionReminderSubject(string $eventName): string
    {
        $daysBefore = str_contains($eventName, '7_days') ? 7 : 3;

        return 'Your workspace subscription renews in ' . $daysBefore . ' days';
    }

    private function limitedSubscriptionReminderBody(string $eventName, ?Tenant $tenant): string
    {
        $daysBefore = str_contains($eventName, '7_days') ? 7 : 3;
        $ownerName = trim((string) ($this->resolveAccountOwner($tenant)?->name ?? 'Account Owner'));
        $tenantName = trim((string) ($tenant?->company_name ?? 'your workspace'));
        $planName = trim((string) ($tenant?->subscription_plan ?? 'Current Plan'));
        $deadlineAt = $tenant?->subscription_renews_at ?: $tenant?->trial_ends_at ?: $tenant?->billing_grace_ends_at;
        $deadlineText = $deadlineAt instanceof Carbon
            ? $deadlineAt->format('F j, Y g:i A')
            : 'Not available';

        return implode("\n", [
            'Hello ' . $ownerName . ',',
            '',
            'This is a reminder that your workspace has ' . $daysBefore . ' days remaining before the current access deadline.',
            '',
            'Business: ' . $tenantName,
            'Plan: ' . $planName,
            'Deadline: ' . $deadlineText,
            '',
            'Please renew before the deadline to keep your workspace and team access uninterrupted.',
            '',
            'Thank you,',
            config('app.name', 'Sales & Marketing Funnel System') . ' Billing Team',
        ]);
    }
}
