<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;
use App\Models\User;
use App\Services\DeliveryLogService;
use App\Services\InAppNotificationService;
use App\Services\N8nEmailOrchestrator;
use App\Services\PlanAutomationService;
use App\Services\SubscriptionDeadlineReminderService;
use App\Services\SubscriptionLifecycleService;
use App\Services\TransactionalEmailService;
use App\Support\TenantPlanEnforcer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class N8nAutomationController extends Controller
{
    public function leadActivity(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'required|integer|exists:leads,id',
            'activity_type' => 'required|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $lead = Lead::withoutGlobalScope('tenant')
            ->where('id', $validated['lead_id'])
            ->where('tenant_id', $validated['tenant_id'])
            ->firstOrFail();

        $this->ensureSharedAutomationTenant((int) $validated['tenant_id']);

        $activity = $lead->activities()->create([
            'activity_type' => $validated['activity_type'],
            'notes' => $validated['notes'] ?? '',
        ]);

        return response()->json(['ok' => true, 'id' => $activity->id]);
    }

    public function leadScore(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'required|integer|exists:leads,id',
            'event' => 'required|string|max:120',
            'points' => 'nullable|integer|min:0|max:10000',
        ]);

        $lead = Lead::withoutGlobalScope('tenant')
            ->where('id', $validated['lead_id'])
            ->where('tenant_id', $validated['tenant_id'])
            ->firstOrFail();

        $this->ensureSharedAutomationTenant((int) $validated['tenant_id']);

        $points = (int) ($validated['points'] ?? 0);
        if ($points > 0) {
            $lead->increment('score', $points);
        }

        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => $validated['event'] . " (+{$points} points)",
        ]);

        $lead->refresh();

        return response()->json([
            'ok' => true,
            'lead_id' => $lead->id,
            'score' => (int) $lead->score,
        ]);
    }

    public function sendEmail(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'nullable|integer|exists:leads,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'recipient_email' => 'nullable|email',
            'template' => 'required|string|max:160',
            'invoice_id' => 'nullable|string|max:120',
            'amount' => 'nullable|numeric',
            'payment_reference' => 'nullable|string|max:160',
            'destination_type' => 'nullable|string|max:80',
            'masked_destination' => 'nullable|string|max:160',
            'paid_at' => 'nullable|string|max:80',
            'payments_count' => 'nullable|integer|min:0',
        ]);

        $tenantId = (int) $validated['tenant_id'];
        $recipient = null;
        $lead = null;

        if (! empty($validated['lead_id'])) {
            $lead = Lead::withoutGlobalScope('tenant')
                ->where('id', (int) $validated['lead_id'])
                ->where('tenant_id', $tenantId)
                ->first();
            if ($lead?->email) {
                $recipient = $lead->email;
            }
        }

        if (! $recipient && ! empty($validated['user_id'])) {
            $user = User::query()
                ->where('id', (int) $validated['user_id'])
                ->where('tenant_id', $tenantId)
                ->first();
            if ($user?->email) {
                $recipient = $user->email;
            }
        }

        if (! $recipient && ! empty($validated['recipient_email'])) {
            $recipient = (string) $validated['recipient_email'];
        }

        if (! $recipient) {
            $owner = $this->resolveAccountOwner($tenantId);
            $recipient = $owner?->email;
        }

        if (! $recipient) {
            throw ValidationException::withMessages([
                'recipient' => 'Could not resolve recipient email for this request.',
            ]);
        }

        $tenant = Tenant::query()->findOrFail($tenantId);
        $this->ensureSharedAutomationTenant($tenantId);
        app(TenantPlanEnforcer::class)->ensureCanSendOutboundMessages($tenant);

        $template = (string) $validated['template'];
        ['subject' => $subject, 'body' => $body] = $this->buildEmailContent(
            $template,
            $tenantId,
            $validated,
            $lead,
            $recipient
        );

        $sendResult = app(TransactionalEmailService::class)->sendPlainText($recipient, $subject, $body, [
            'template' => $template,
            'tenant_id' => $tenantId,
            'lead_id' => $lead?->id,
            'invoice_id' => $validated['invoice_id'] ?? null,
            'is_billable' => true,
        ]);

        if ($lead) {
            $lead->activities()->create([
                'activity_type' => 'Email Sent',
                'notes' => 'Template: ' . $template,
            ]);
        }

        return response()->json([
            'ok' => (bool) $sendResult['sent'],
            'recipient' => $recipient,
            'template' => $template,
            'provider' => $sendResult['provider'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{subject: string, body: string}
     */
    private function buildEmailContent(string $template, int $tenantId, array $validated, ?Lead $lead, string $recipient): array
    {
        $tenant = Tenant::query()->find($tenantId);
        $owner = $this->resolveAccountOwner($tenantId);

        if (in_array($template, [
            'payout_account_pending_review',
            'payout_account_approved',
            'payout_account_rejected',
        ], true)) {
            return $this->buildPayoutEmailContent($template, $tenant, $owner, $validated);
        }

        if ($template === 'settlement_payout_recorded') {
            return $this->buildSettlementPayoutEmailContent($tenant, $owner, $validated);
        }

        if (in_array($template, [
            'subscription_deadline_reminder_7_days_owner',
            'subscription_deadline_reminder_3_days_owner',
        ], true)) {
            return $this->buildSubscriptionDeadlineEmailContent($template, $tenant, $owner);
        }

        return $this->buildGenericAutomationEmailContent($template, $tenant, $owner, $validated, $lead, $recipient, $tenantId);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{subject: string, body: string}
     */
    private function buildPayoutEmailContent(string $template, ?Tenant $tenant, ?User $owner, array $validated): array
    {
        $payoutAccount = $this->resolvePayoutAccount(
            $tenant?->id,
            isset($validated['invoice_id']) ? (string) $validated['invoice_id'] : null
        );

        $ownerName = trim((string) ($owner?->name ?? 'Account Owner'));
        $tenantName = trim((string) ($tenant?->company_name ?? 'your business'));
        $destinationType = $this->formatPayoutDestinationType($payoutAccount?->destination_type);
        $maskedDestination = trim((string) ($payoutAccount?->masked_destination ?? 'Not available'));
        $reviewNotes = trim((string) ($payoutAccount?->review_notes ?? ''));

        if ($template === 'payout_account_pending_review') {
            $subject = $destinationType . ' payout account needs platform review';
            $body = implode("\n", [
                'A tenant payout destination has been submitted for review.',
                '',
                'Tenant: ' . $tenantName,
                'Account owner: ' . $ownerName,
                'Owner email: ' . ($owner?->email ?? 'Not available'),
                'Payout method: ' . $destinationType,
                'Masked destination: ' . $maskedDestination,
                'Status: Pending platform review',
                '',
                'Open the Platform Finance Admin dashboard to approve or reject this payout account.',
            ]);

            return compact('subject', 'body');
        }

        if ($template === 'payout_account_approved') {
            $subject = 'Your ' . $destinationType . ' payout account has been approved';
            $body = implode("\n", [
                'Hello ' . $ownerName . ',',
                '',
                'Your ' . $destinationType . ' payout account is now approved for payout operations.',
                '',
                'Business: ' . $tenantName,
                'Payout method: ' . $destinationType,
                'Approved destination: ' . $maskedDestination,
                'Status: Approved',
                '',
                'If you update your payout account later, it may return to review status until the new details are verified.',
                '',
                'Thank you,',
                config('app.name', 'SaaS System') . ' Platform Finance Team',
            ]);

            return compact('subject', 'body');
        }

        $subject = 'Your ' . $destinationType . ' payout account needs an update';
        $bodyLines = [
            'Hello ' . $ownerName . ',',
            '',
            'Your ' . $destinationType . ' payout account could not be approved yet and needs an update before payouts can continue.',
            '',
            'Business: ' . $tenantName,
            'Payout method: ' . $destinationType,
            'Submitted destination: ' . $maskedDestination,
            'Status: Rejected',
        ];

        if ($reviewNotes !== '') {
            $bodyLines[] = 'Review notes: ' . $reviewNotes;
        }

        $bodyLines = array_merge($bodyLines, [
            '',
            'Please log in to your account, update the payout details, and submit them again for review.',
            '',
            'Thank you,',
            config('app.name', 'SaaS System') . ' Platform Finance Team',
        ]);

        $body = implode("\n", $bodyLines);

        return compact('subject', 'body');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{subject: string, body: string}
     */
    private function buildSettlementPayoutEmailContent(?Tenant $tenant, ?User $owner, array $validated): array
    {
        $tenantName = trim((string) ($tenant?->company_name ?? 'your business'));
        $ownerName = trim((string) ($owner?->name ?? 'Account Owner'));
        $amount = (float) ($validated['amount'] ?? 0);
        $maskedDestination = trim((string) ($validated['masked_destination'] ?? ''));
        $paymentReference = trim((string) ($validated['payment_reference'] ?? ''));
        $paymentsCount = (int) ($validated['payments_count'] ?? 0);
        $paidAt = trim((string) ($validated['paid_at'] ?? ''));

        $subject = 'Payout recorded for ' . $tenantName;
        $body = implode("\n", array_values(array_filter([
            'Hello ' . $ownerName . ',',
            '',
            'A platform payout has been recorded for your workspace.',
            '',
            'Business: ' . $tenantName,
            $amount > 0 ? 'Payout amount: PHP ' . number_format($amount, 2) : null,
            $paymentsCount > 0 ? 'Covered payments: ' . number_format($paymentsCount) : null,
            $maskedDestination !== '' ? 'Destination: ' . $maskedDestination : null,
            $paymentReference !== '' ? 'Transfer reference: ' . $paymentReference : null,
            $paidAt !== '' ? 'Recorded at: ' . $paidAt : null,
            '',
            'Please review your owner reports or payment history if you need more details.',
            '',
            'Thank you,',
            config('app.name', 'SaaS System') . ' Platform Finance Team',
        ])));

        return compact('subject', 'body');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{subject: string, body: string}
     */
    private function buildGenericAutomationEmailContent(
        string $template,
        ?Tenant $tenant,
        ?User $owner,
        array $validated,
        ?Lead $lead,
        string $recipient,
        int $tenantId,
    ): array {
        $platformName = (string) config('app.name', 'Sales & Marketing Funnel System');
        $recipientName = $this->resolveAutomationRecipientName($lead, $owner, $recipient);
        $tenantName = trim((string) ($tenant?->company_name ?? 'your workspace'));
        $ownerName = trim((string) ($owner?->name ?? 'your team'));
        $invoiceId = trim((string) ($validated['invoice_id'] ?? ''));
        $templateLabel = $this->humanizeAutomationTemplate($template);

        $subject = match ($template) {
            'lead_captured' => 'New lead captured for ' . $tenantName,
            'lead_stage_changed' => 'Lead update for ' . $tenantName,
            'payment_failed' => 'Action required: payment could not be completed',
            'payment_recovered', 'payment_recovered_success' => 'Payment received successfully',
            'funnel_opt_in_submitted_customer' => 'Thank you for your interest',
            'funnel_checkout_started_customer' => 'Your checkout has been started',
            'funnel_payment_paid_customer' => 'Your payment has been received',
            'funnel_checkout_abandoned_customer' => 'Your checkout is still available',
            'funnel_order_delivery_updated_customer' => 'Your order status has been updated',
            'funnel_upsell_accepted_customer' => 'Your order has been updated',
            'funnel_upsell_declined_customer' => 'Your order preferences have been recorded',
            'funnel_downsell_accepted_customer' => 'Your order has been updated',
            'funnel_downsell_declined_customer' => 'Your order preferences have been recorded',
            default => $templateLabel . ' Update',
        };

        $bodyLines = match ($template) {
            'lead_captured' => [
                'Hello ' . $recipientName . ',',
                '',
                'A new lead has been captured for your workspace.',
                '',
                'Business: ' . $tenantName,
                'Lead: ' . trim((string) ($lead?->name ?: 'New lead')),
                'Lead email: ' . trim((string) ($lead?->email ?: 'Not available')),
                '',
                'Please log in to review the lead details and continue follow-up as needed.',
                '',
                'Thank you,',
                $platformName . ' Automation Team',
            ],
            'lead_stage_changed' => [
                'Hello ' . $recipientName . ',',
                '',
                'A lead record in your workspace has been updated through the automation flow.',
                '',
                'Business: ' . $tenantName,
                'Lead: ' . trim((string) ($lead?->name ?: 'Lead record')),
                'Lead email: ' . trim((string) ($lead?->email ?: 'Not available')),
                '',
                'Please log in to review the latest status and next recommended action.',
                '',
                'Thank you,',
                $platformName . ' Automation Team',
            ],
            'payment_failed' => array_values(array_filter([
                'Hello ' . $recipientName . ',',
                '',
                'We were unable to complete the latest payment associated with your workspace.',
                '',
                'Business: ' . $tenantName,
                $invoiceId !== '' ? 'Invoice or reference: ' . $invoiceId : null,
                '',
                'Please review your billing details and complete the payment as soon as possible to avoid service interruption.',
                '',
                'Thank you,',
                $platformName . ' Billing Team',
            ])),
            'payment_recovered', 'payment_recovered_success' => array_values(array_filter([
                'Hello ' . $recipientName . ',',
                '',
                'This is a confirmation that your recent payment has been received successfully.',
                '',
                'Business: ' . $tenantName,
                $invoiceId !== '' ? 'Invoice or reference: ' . $invoiceId : null,
                '',
                'Your billing status should now continue normally. If you have any questions, please contact your administrator or support team.',
                '',
                'Thank you,',
                $platformName . ' Billing Team',
            ])),
            'funnel_opt_in_submitted_customer' => [
                'Hello ' . $recipientName . ',',
                '',
                'Thank you for your interest. We have received your information successfully.',
                '',
                'A member of the ' . $tenantName . ' team may contact you soon with the next steps or additional details.',
                '',
                'Thank you,',
                $tenantName . ' via ' . $platformName,
            ],
            'funnel_checkout_started_customer' => [
                'Hello ' . $recipientName . ',',
                '',
                'We noticed that you started your checkout process.',
                '',
                'If you need more time, you may return and complete your order when ready. If you have any questions, please contact the ' . $tenantName . ' team.',
                '',
                'Thank you,',
                $tenantName . ' via ' . $platformName,
            ],
            'funnel_payment_paid_customer' => [
                'Hello ' . $recipientName . ',',
                '',
                'Thank you. Your payment has been received successfully.',
                '',
                'Your order is now being processed by the ' . $tenantName . ' team. If additional fulfillment details are needed, you will receive a follow-up update.',
                '',
                'Thank you,',
                $tenantName . ' via ' . $platformName,
            ],
            'funnel_checkout_abandoned_customer' => [
                'Hello ' . $recipientName . ',',
                '',
                'This is a quick reminder that your checkout is still available if you would like to continue your order.',
                '',
                'If you experienced any issue during checkout, please contact the ' . $tenantName . ' team for assistance.',
                '',
                'Thank you,',
                $tenantName . ' via ' . $platformName,
            ],
            'funnel_order_delivery_updated_customer',
            'funnel_upsell_accepted_customer',
            'funnel_upsell_declined_customer',
            'funnel_downsell_accepted_customer',
            'funnel_downsell_declined_customer' => [
                'Hello ' . $recipientName . ',',
                '',
                'This is a confirmation that your recent order activity has been recorded successfully.',
                '',
                'If the ' . $tenantName . ' team needs any additional details, they will contact you directly.',
                '',
                'Thank you,',
                $tenantName . ' via ' . $platformName,
            ],
            default => array_values(array_filter([
                'Hello ' . $recipientName . ',',
                '',
                'This is an automated update from ' . $platformName . '.',
                '',
                'Update type: ' . $templateLabel,
                'Business: ' . $tenantName,
                $lead ? 'Lead: ' . trim((string) ($lead->name ?: $lead->email ?: 'Lead record')) : null,
                $invoiceId !== '' ? 'Invoice or reference: ' . $invoiceId : null,
                ! empty($validated['recipient_email']) && $validated['recipient_email'] !== $recipient
                    ? 'Requested recipient: ' . (string) $validated['recipient_email']
                    : null,
                'Workspace ID: ' . $tenantId,
                '',
                'Please review your account for the latest details or contact ' . $ownerName . ' if you need assistance.',
                '',
                'Thank you,',
                $platformName . ' Automation Team',
            ])),
        };

        return [
            'subject' => $subject,
            'body' => implode("\n", $bodyLines),
        ];
    }

    private function resolveAutomationRecipientName(?Lead $lead, ?User $owner, string $recipient): string
    {
        $leadName = trim((string) ($lead?->name ?? ''));
        if ($leadName !== '') {
            return $leadName;
        }

        if ($owner && strcasecmp((string) $owner->email, $recipient) === 0) {
            $ownerName = trim((string) $owner->name);
            if ($ownerName !== '') {
                return $ownerName;
            }
        }

        $localPart = trim((string) strtok($recipient, '@'));
        if ($localPart === '') {
            return 'Customer';
        }

        return ucwords(str_replace(['.', '_', '-'], ' ', $localPart));
    }

    private function humanizeAutomationTemplate(string $template): string
    {
        $label = trim(str_replace('_', ' ', $template));
        if ($label === '') {
            return 'Automation';
        }

        return ucwords($label);
    }

    /**
     * @return array{subject: string, body: string}
     */
    private function buildSubscriptionDeadlineEmailContent(string $template, ?Tenant $tenant, ?User $owner): array
    {
        $daysBefore = str_contains($template, '7_days') ? 7 : 3;
        $ownerName = trim((string) ($owner?->name ?? 'Account Owner'));
        $tenantName = trim((string) ($tenant?->company_name ?? 'your workspace'));
        $planName = trim((string) ($tenant?->subscription_plan ?? 'Current Plan'));
        $deadlineKind = 'subscription_renewal';
        $deadlineAt = $tenant?->subscription_renews_at;
        $billingState = 'Monthly subscription';

        if (! $deadlineAt && $tenant?->billing_status === SubscriptionLifecycleService::BILLING_OVERDUE) {
            $deadlineAt = $tenant?->billing_grace_ends_at;
            $billingState = 'Payment grace period';
            $deadlineKind = 'billing_grace';
        }

        if (! $deadlineAt) {
            $deadlineAt = $tenant?->trial_ends_at;
            $billingState = 'Trial access';
            $deadlineKind = 'trial';
        }

        $deadlineText = $deadlineAt ? $deadlineAt->format('F j, Y g:i A') : 'Not available';

        $subject = match ($deadlineKind) {
            'billing_grace' => 'Your workspace billing grace period ends in ' . $daysBefore . ' days',
            'trial' => 'Your workspace trial ends in ' . $daysBefore . ' days',
            default => 'Your monthly subscription renews in ' . $daysBefore . ' days',
        };

        $body = implode("\n", [
            'Hello ' . $ownerName . ',',
            '',
            'This is a reminder that your workspace has ' . $daysBefore . ' day' . ($daysBefore === 1 ? '' : 's') . ' remaining before the current access deadline.',
            '',
            'Business: ' . $tenantName,
            'Plan: ' . $planName,
            'Billing status: ' . $billingState,
            'Deadline: ' . $deadlineText,
            '',
            $deadlineKind === 'billing_grace'
                ? 'Please complete payment before the deadline to avoid service interruption for your workspace and team.'
                : ($deadlineKind === 'trial'
                    ? 'Please upgrade or renew before the deadline to keep your workspace and team access uninterrupted.'
                    : 'Please complete your monthly renewal before the deadline to keep your workspace and team access uninterrupted.'),
            '',
            'Thank you,',
            config('app.name', 'SaaS System') . ' Billing Team',
        ]);

        return compact('subject', 'body');
    }

    private function resolveAccountOwner(int $tenantId): ?User
    {
        return User::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('role', 'account-owner')
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->where('slug', 'account-owner');
                    });
            })
            ->orderBy('id')
            ->first();
    }

    private function resolvePayoutAccount(?int $tenantId, ?string $referenceId): ?TenantPayoutAccount
    {
        $referenceId = trim((string) $referenceId);
        if (! $tenantId || $referenceId === '' || ! ctype_digit($referenceId)) {
            return null;
        }

        return TenantPayoutAccount::query()
            ->where('tenant_id', $tenantId)
            ->whereKey((int) $referenceId)
            ->first();
    }

    private function formatPayoutDestinationType(?string $destinationType): string
    {
        $value = strtolower(trim((string) $destinationType));

        return match ($value) {
            'gcash' => 'GCash',
            'card' => 'Card',
            '' => 'Payout',
            default => ucwords(str_replace('_', ' ', $value)),
        };
    }

    public function sendSms(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'nullable|integer|exists:leads,id',
            'template' => 'required|string|max:160',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $tenantId = (int) $validated['tenant_id'];
        $tenant = Tenant::query()->findOrFail($tenantId);
        $this->ensureSharedAutomationTenant($tenantId);
        app(TenantPlanEnforcer::class)->ensureCanSendOutboundMessages($tenant);
        $phone = null;
        $lead = null;

        if (! empty($validated['lead_id'])) {
            $lead = Lead::withoutGlobalScope('tenant')
                ->where('id', (int) $validated['lead_id'])
                ->where('tenant_id', $tenantId)
                ->first();
            $phone = $lead?->phone;
        }

        Log::info('N8N SMS simulation dispatch.', [
            'tenant_id' => $tenantId,
            'lead_id' => $lead?->id,
            'phone' => $phone,
            'template' => $validated['template'],
            'invoice_id' => $validated['invoice_id'] ?? null,
        ]);

        app(DeliveryLogService::class)->record('sms', [
            'tenant_id' => $tenantId,
            'lead_id' => $lead?->id,
            'event_name' => (string) $validated['template'],
            'recipient' => $phone,
            'provider' => 'simulated',
            'status' => 'sent',
            'is_billable' => true,
            'meta' => [
                'invoice_id' => $validated['invoice_id'] ?? null,
                'simulated' => true,
            ],
        ]);

        if ($lead) {
            $lead->activities()->create([
                'activity_type' => 'SMS Sent',
                'notes' => 'Template: ' . $validated['template'],
            ]);
        }

        return response()->json([
            'ok' => true,
            'simulated' => true,
            'phone' => $phone,
        ]);
    }

    public function agentTask(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'lead_id' => 'required|integer|exists:leads,id',
            'title' => 'required|string|max:255',
            'priority' => 'nullable|string|max:50',
        ]);

        $lead = Lead::withoutGlobalScope('tenant')
            ->where('id', $validated['lead_id'])
            ->where('tenant_id', $validated['tenant_id'])
            ->firstOrFail();

        $this->ensureSharedAutomationTenant((int) $validated['tenant_id']);

        $lead->activities()->create([
            'activity_type' => 'Task',
            'notes' => $validated['title'] . ' (Priority: ' . ($validated['priority'] ?? 'normal') . ')',
        ]);

        return response()->json(['ok' => true]);
    }

    public function invoiceStatus(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $query = Payment::query()
            ->where('tenant_id', (int) $validated['tenant_id'])
            ->platformSubscriptions()
            ->latest('id');

        $invoiceId = (string) ($validated['invoice_id'] ?? '');
        if ($invoiceId !== '') {
            $query->where(function ($q) use ($invoiceId) {
                $q->where('provider_reference', $invoiceId);
                if (is_numeric($invoiceId)) {
                    $q->orWhere('id', (int) $invoiceId);
                }
            });
        }

        $payment = $query->first();

        return response()->json([
            'ok' => true,
            'status' => $payment?->status ?? 'unknown',
            'invoice_id' => $invoiceId !== '' ? $invoiceId : null,
            'payment_id' => $payment?->id,
        ]);
    }

    public function suspendSubscription(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'reason' => 'nullable|string|max:255',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $tenant = Tenant::query()->findOrFail((int) $validated['tenant_id']);
        $tenant->update([
            'status' => 'inactive',
            'billing_status' => 'inactive',
        ]);

        Log::warning('Tenant suspended from N8N automation endpoint.', [
            'tenant_id' => $tenant->id,
            'invoice_id' => $validated['invoice_id'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json(['ok' => true, 'tenant_status' => $tenant->fresh()->status]);
    }

    public function paymentRecovered(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'invoice_id' => 'nullable|string|max:120',
        ]);

        $tenant = Tenant::query()->findOrFail((int) $validated['tenant_id']);
        $tenant = app(SubscriptionLifecycleService::class)->restoreTenantBilling($tenant);

        return response()->json([
            'ok' => true,
            'tenant_status' => $tenant->status,
            'billing_status' => $tenant->billing_status,
        ]);
    }

    public function automationLog(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'event_name' => 'required|string|max:120',
            'payload' => 'nullable|string',
        ]);

        Log::info('N8N automation-log endpoint called.', $validated);

        $payload = [];
        if (! empty($validated['payload'])) {
            $decoded = json_decode((string) $validated['payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        app(InAppNotificationService::class)->ingestAutomationEvent(
            (int) $validated['tenant_id'],
            (string) $validated['event_name'],
            $payload
        );

        return response()->json(['ok' => true]);
    }

    public function analyticsDaily(Request $request)
    {
        $this->authorizeN8n($request);

        $tenantCount = Tenant::query()->count();
        $leadCount = Lead::withoutGlobalScope('tenant')->count();
        $paidCount = Payment::query()->where('status', 'paid')->count();
        $failedCount = Payment::query()->where('status', 'failed')->count();
        $mrr = (float) Payment::query()
            ->platformSubscriptions()
            ->where('status', 'paid')
            ->whereDate('payment_date', '>=', now()->startOfMonth())
            ->sum('amount');

        return response()->json([
            'ok' => true,
            'date' => now()->toDateString(),
            'tenants' => $tenantCount,
            'leads' => $leadCount,
            'payments_paid' => $paidCount,
            'payments_failed' => $failedCount,
            'mrr' => $mrr,
        ]);
    }

    public function analyticsStore(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'date' => 'required|date',
            'snapshot' => 'required|string',
        ]);

        Cache::put('n8n.analytics.snapshot.' . $validated['date'], $validated['snapshot'], now()->addDays(60));

        return response()->json(['ok' => true]);
    }

    public function sendOwnerDigest(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'date' => 'nullable|date',
            'audience' => 'nullable|string|max:80',
            'include_upgrade_nudges' => 'nullable',
            'recipient_email' => 'nullable|email',
        ]);

        $audience = (string) ($validated['audience'] ?? 'all_owners');
        $forcedRecipient = trim((string) ($validated['recipient_email'] ?? ''));
        $users = User::query()->where('role', 'account-owner');
        if ($audience === 'trial_owners') {
            $users->whereHas('tenant', function ($query) {
                $query->where('status', 'trial');
            });
        }

        $sent = 0;
        foreach ($users->get(['email', 'tenant_id', 'id']) as $user) {
            $recipient = $forcedRecipient !== '' ? $forcedRecipient : (string) $user->email;
            if (! $recipient) {
                continue;
            }

            $sendResult = app(TransactionalEmailService::class)->sendPlainText(
                $recipient,
                'Owner Digest',
                'Owner digest generated for ' . ($validated['date'] ?? now()->toDateString()),
                [
                    'event_name' => 'owner_digest',
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'is_billable' => false,
                    'audience' => $audience,
                    'requested_recipient_email' => $recipient,
                ]
            );

            if (! $sendResult['sent']) {
                Log::warning('Owner digest email dispatch failed.', [
                    'email' => $recipient,
                    'error' => $sendResult['error_message'] ?? 'unknown',
                ]);
                continue;
            }

            $sent++;
        }

        return response()->json(['ok' => true, 'sent' => $sent]);
    }

    public function trialInactiveRecovery(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'reason' => 'nullable|string|max:120',
        ]);

        Log::info('Trial inactive recovery signal received.', $validated);

        return response()->json(['ok' => true]);
    }

    public function runInactiveTrialRecovery(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);

        $processed = 0;
        $tenants = Tenant::query()
            ->where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', '<=', now()->addDays(2)->toDateString())
            ->get(['id']);

        foreach ($tenants as $tenant) {
            Log::info('Trial recovery candidate.', [
                'tenant_id' => $tenant->id,
                'run_date' => $validated['date'] ?? now()->toDateString(),
            ]);
            $processed++;
        }

        return response()->json(['ok' => true, 'processed' => $processed]);
    }

    public function runSubscriptionDeadlineReminders(Request $request)
    {
        $this->authorizeN8n($request);

        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);

        $runDate = ! empty($validated['date'])
            ? Carbon::parse((string) $validated['date'])->startOfDay()
            : now()->startOfDay();

        return response()->json(
            app(SubscriptionDeadlineReminderService::class)->dispatch($runDate)
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchAutomationEvent(string $eventName, array $payload): void
    {
        try {
            app(N8nEmailOrchestrator::class)->dispatch($eventName, $payload);
        } catch (\Throwable) {
            // Best-effort dispatch only.
        }
    }

    private function authorizeN8n(Request $request): void
    {
        $callbackToken = (string) config('services.n8n.callback_bearer_token');
        $webhookToken = (string) config('services.n8n.webhook_token');
        $expected = $callbackToken !== '' ? $callbackToken : $webhookToken;
        if ($expected === '') {
            return;
        }

        $authorization = (string) $request->header('Authorization');
        $received = trim(str_ireplace('Bearer', '', $authorization));
        if (! hash_equals($expected, $received)) {
            abort(401, 'Unauthorized');
        }
    }

    private function ensureSharedAutomationTenant(int $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);
        if (! $tenant) {
            return;
        }

        if (! app(PlanAutomationService::class)->usesSharedAutomation($tenant)) {
            abort(403, 'Shared automation is not available on this plan.');
        }
    }

}
