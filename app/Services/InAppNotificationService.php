<?php

namespace App\Services;

use App\Models\InAppNotification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InAppNotificationService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function ingestAutomationEvent(int $tenantId, string $eventName, array $payload = [], string $source = 'n8n'): void
    {
        try {
            $tenant = Tenant::query()->find($tenantId);
            if (! $tenant) {
                return;
            }

            $recipients = $this->resolveRecipients($tenantId, $eventName, $payload);
            if ($recipients->isEmpty()) {
                return;
            }

            $content = $this->buildContent($tenant, $eventName, $payload);
            $idempotencyKey = $this->resolveIdempotencyKey($eventName, $payload);
            $occurredAt = $this->resolveOccurredAt($payload);

            foreach ($recipients as $recipient) {
                $notification = InAppNotification::query()->firstOrNew([
                    'user_id' => $recipient->id,
                    'idempotency_key' => $idempotencyKey,
                ]);

                if ($notification->exists) {
                    continue;
                }

                $notification->fill([
                    'tenant_id' => $tenantId,
                    'source' => trim($source) !== '' ? $source : 'n8n',
                    'event_name' => $eventName,
                    'level' => $content['level'],
                    'title' => $content['title'],
                    'message' => $content['message'],
                    'action_url' => $this->resolveActionUrl($recipient, $eventName),
                    'payload' => $payload,
                    'occurred_at' => $occurredAt,
                ]);
                $notification->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to ingest in-app notification from n8n automation event.', [
                'tenant_id' => $tenantId,
                'event_name' => $eventName,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return Collection<int, User>
     */
    private function resolveRecipients(int $tenantId, string $eventName, array $payload): Collection
    {
        $tenantUsers = User::query()
            ->with('roles')
            ->where('tenant_id', $tenantId)
            ->get();

        $recipients = collect();
        $this->pushRoleUsers($recipients, 'super-admin');

        if (str_starts_with($eventName, 'payout_account_')) {
            $this->pushRoleUsers($recipients, 'payout-admin');
        }

        $tenantRoleTargets = match (true) {
            in_array($eventName, [
                'lead_captured',
                'lead_stage_changed',
                'funnel_opt_in_submitted',
                'funnel_checkout_started',
                'funnel_checkout_abandoned',
                'funnel_upsell_accepted',
                'funnel_upsell_declined',
                'funnel_downsell_accepted',
                'funnel_downsell_declined',
            ], true) => ['account-owner', 'marketing-manager', 'sales-agent'],
            in_array($eventName, [
                'funnel_payment_paid',
                'payment_failed',
                'payment_recovered',
                'funnel_order_delivery_updated',
            ], true) => ['account-owner', 'marketing-manager', 'sales-agent', 'finance'],
            in_array($eventName, [
                'receipt_uploaded',
                'receipt_auto_approved',
                'receipt_approved',
                'receipt_rejected',
                'commission_created',
                'commission_payable',
                'subscription_paid',
                'subscription_overdue',
                'subscription_recovered',
                'subscription_deadline_reminder_7_days_owner',
                'subscription_deadline_reminder_3_days_owner',
                'payout_account_pending_review',
                'payout_account_approved',
                'payout_account_rejected',
            ], true) => ['account-owner', 'finance'],
            default => ['account-owner'],
        };

        foreach ($tenantRoleTargets as $roleSlug) {
            $tenantUsers
                ->filter(fn (User $user) => $user->hasRole($roleSlug))
                ->each(fn (User $user) => $recipients->put($user->id, $user));
        }

        foreach (['user_id', 'account_owner_id'] as $userIdKey) {
            $userId = (int) ($payload[$userIdKey] ?? 0);
            if ($userId > 0) {
                $user = User::query()->with('roles')->find($userId);
                if ($user) {
                    $recipients->put($user->id, $user);
                }
            }
        }

        $email = strtolower(trim((string) ($payload['recipient_email'] ?? $payload['account_owner_email'] ?? '')));
        if ($email !== '') {
            $user = User::query()
                ->with('roles')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();
            if ($user) {
                $recipients->put($user->id, $user);
            }
        }

        return $recipients->values();
    }

    private function pushRoleUsers(Collection $recipients, string $roleSlug): void
    {
        User::query()
            ->with('roles')
            ->whereHas('roles', function ($query) use ($roleSlug) {
                $query->where('slug', $roleSlug);
            })
            ->get()
            ->each(fn (User $user) => $recipients->put($user->id, $user));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{title: string, message: string, level: string}
     */
    private function buildContent(Tenant $tenant, string $eventName, array $payload): array
    {
        $tenantName = trim((string) ($tenant->company_name ?: 'Workspace'));
        $leadName = trim((string) ($payload['lead_name'] ?? $payload['name'] ?? ''));
        $campaign = trim((string) ($payload['source_campaign'] ?? ''));
        $maskedDestination = trim((string) ($payload['masked_destination'] ?? ''));
        $destinationType = trim((string) ($payload['destination_type'] ?? ''));
        $reviewNotes = trim((string) ($payload['review_notes'] ?? ''));

        return match ($eventName) {
            'lead_captured' => [
                'title' => 'New lead captured',
                'message' => trim($tenantName . ': ' . ($leadName !== '' ? $leadName : 'A new lead') . ($campaign !== '' ? ' from ' . $campaign : '') . '.'),
                'level' => 'info',
            ],
            'lead_stage_changed' => [
                'title' => 'Lead stage changed',
                'message' => trim($tenantName . ': lead stage updated' . (! empty($payload['to_stage']) ? ' to ' . $payload['to_stage'] : '') . '.'),
                'level' => 'info',
            ],
            'payment_failed', 'subscription_overdue', 'payout_account_rejected', 'receipt_rejected' => [
                'title' => $this->humanizeEvent($eventName),
                'message' => $this->buildGenericMessage($tenantName, $eventName, $payload, $reviewNotes),
                'level' => 'error',
            ],
            'payment_recovered', 'subscription_recovered', 'receipt_approved', 'receipt_auto_approved', 'payout_account_approved' => [
                'title' => $this->humanizeEvent($eventName),
                'message' => $this->buildGenericMessage($tenantName, $eventName, $payload, $reviewNotes),
                'level' => 'success',
            ],
            'payout_account_pending_review' => [
                'title' => 'Payout account pending review',
                'message' => trim($tenantName . ': ' . ($destinationType !== '' ? ucfirst(str_replace('_', ' ', $destinationType)) . ' ' : '') . 'payout account ' . ($maskedDestination !== '' ? '(' . $maskedDestination . ') ' : '') . 'was submitted for review.'),
                'level' => 'warning',
            ],
            default => [
                'title' => $this->humanizeEvent($eventName),
                'message' => $this->buildGenericMessage($tenantName, $eventName, $payload, $reviewNotes),
                'level' => 'info',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function buildGenericMessage(string $tenantName, string $eventName, array $payload, string $reviewNotes = ''): string
    {
        $parts = [$tenantName . ': ' . $this->humanizeEvent($eventName) . '.'];

        if (! empty($payload['message'])) {
            $parts[] = trim((string) $payload['message']);
        } elseif (! empty($payload['status'])) {
            $parts[] = 'Status: ' . trim((string) $payload['status']) . '.';
        }

        if ($reviewNotes !== '') {
            $parts[] = 'Notes: ' . $reviewNotes;
        }

        return implode(' ', $parts);
    }

    private function humanizeEvent(string $eventName): string
    {
        return ucwords(str_replace('_', ' ', $eventName));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveIdempotencyKey(string $eventName, array $payload): string
    {
        $key = trim((string) ($payload['idempotency_key'] ?? ''));
        if ($key !== '') {
            return $key;
        }

        $eventId = trim((string) ($payload['event_id'] ?? ''));
        if ($eventId !== '') {
            return $eventName . ':' . $eventId;
        }

        return $eventName . ':' . sha1(json_encode($payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveOccurredAt(array $payload): Carbon
    {
        $occurredAt = trim((string) ($payload['occurred_at'] ?? ''));

        try {
            return $occurredAt !== '' ? Carbon::parse($occurredAt) : now();
        } catch (\Throwable) {
            return now();
        }
    }

    private function resolveActionUrl(User $user, string $eventName): ?string
    {
        if ($user->hasRole('payout-admin') && str_starts_with($eventName, 'payout_account_')) {
            return route('platform.payouts.index');
        }

        if ($user->hasRole('super-admin')) {
            return in_array($eventName, [
                'receipt_uploaded',
                'receipt_auto_approved',
                'receipt_approved',
                'receipt_rejected',
            ], true)
                ? route('admin.receipts.index')
                : route('admin.automation.index');
        }

        if ($user->hasRole('account-owner')) {
            if (str_starts_with($eventName, 'payout_account_')) {
                return route('profile.show');
            }

            if (str_starts_with($eventName, 'subscription_') || str_starts_with($eventName, 'receipt_') || str_starts_with($eventName, 'commission_') || str_starts_with($eventName, 'payment_')) {
                return route('payments.index');
            }

            if (str_starts_with($eventName, 'lead_') || str_starts_with($eventName, 'funnel_')) {
                return route('funnels.index');
            }
        }

        if ($user->hasRole('finance')) {
            return route('payments.index');
        }

        if ($user->hasRole('marketing-manager') || $user->hasRole('sales-agent')) {
            return str_starts_with($eventName, 'lead_') ? route('leads.index') : route('funnels.index');
        }

        return route('notifications.index');
    }
}
