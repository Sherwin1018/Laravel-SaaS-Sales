<?php

namespace App\Services;

use App\Jobs\SendN8nWebhookJob;
use App\Models\AutomationEventOutbox;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Central service for dispatching automation webhooks to n8n.
 * Builds consistent payloads and uses outbox for idempotency/audit.
 */
class AutomationWebhookService
{
    /**
     * Build the standard payload shape for n8n (event_id set by dispatchEvent).
     */
    public function buildPayload(string $event, Lead $lead, array $metadata = [], array $steps = []): array
    {
        $lead->loadMissing('assignedAgent');

        $payload = [
            'event' => $event,
            'tenant_id' => $lead->tenant_id,
            'from_email' => $this->resolveTenantFromEmail($lead->tenant_id),
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'status' => $lead->status,
                'assigned_to' => $lead->assigned_to ? (string) $lead->assigned_to : null,
                'source_campaign' => $lead->source_campaign ?? null,
            ],
            'metadata' => $metadata,
            'steps' => $steps,
        ];

        if ($lead->relationLoaded('assignedAgent') && $lead->assignedAgent) {
            $payload['assigned_agent'] = [
                'id' => $lead->assignedAgent->id,
                'email' => $lead->assignedAgent->email,
                'name' => $lead->assignedAgent->name ?? null,
            ];
        } else {
            $payload['assigned_agent'] = null;
        }

        return $payload;
    }

    /**
     * Resolve tenant "From" email: automation_from_email or account owner email.
     */
    private function resolveTenantFromEmail(int $tenantId): ?string
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return null;
        }
        $from = trim((string) ($tenant->automation_from_email ?? ''));
        if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return $from;
        }
        $owner = User::where('tenant_id', $tenantId)
            ->whereHas('roles', fn ($q) => $q->where('slug', 'account-owner'))
            ->first();
        if ($owner && $owner->email) {
            $email = trim((string) $owner->email);
            return $email !== '' ? $email : null;
        }
        return null;
    }

    /**
     * Build payload for lead.created (keeps backward compatibility with lead_id at root for existing n8n).
     * When $createdBy is provided (e.g. the user who created the lead in the CRM), it is added as lead.created_by
     * so n8n can use it (e.g. IF created_by.id != assigned_agent.id then send email to assigned_agent).
     */
    public function buildLeadCreatedPayload(Lead $lead, array $steps = [], ?User $createdBy = null): array
    {
        $base = $this->buildPayload('lead.created', $lead, [], $steps);
        $base['lead_id'] = $lead->id;

        if ($createdBy) {
            $base['lead']['created_by'] = [
                'id' => $createdBy->id,
                'email' => $createdBy->email,
                'name' => $createdBy->name ?? null,
            ];
        } else {
            $base['lead']['created_by'] = null;
        }

        return $base;
    }

    /**
     * Build payload for funnel.opt_in.
     */
    public function buildFunnelOptInPayload(Lead $lead, ?int $funnelId = null, ?string $funnelName = null, array $steps = []): array
    {
        $metadata = array_filter([
            'funnel_id' => $funnelId,
            'funnel_name' => $funnelName,
        ]);

        return $this->buildPayload('funnel.opt_in', $lead, $metadata, $steps);
    }

    /**
     * Build payload for lead.status_changed.
     */
    public function buildLeadStatusChangedPayload(Lead $lead, string $oldStatus, string $newStatus, array $steps = []): array
    {
        $metadata = [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ];

        return $this->buildPayload('lead.status_changed', $lead, $metadata, $steps);
    }

    /**
     * Build payload for payment.paid or payment.failed.
     */
    public function buildPaymentPayload(string $event, Lead $lead, Payment $payment, array $steps = []): array
    {
        $metadata = [
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'amount' => (float) $payment->amount,
            'payment_date' => $payment->payment_date?->toDateString(),
        ];

        return $this->buildPayload($event, $lead, $metadata, $steps);
    }

    /**
     * Build payload for account events (account_owner_paid_signup_created, team_member_invited, etc.)
     */
    public function buildAccountEventPayload(string $event, string $email, string $name, array $additionalData = []): array
    {
        $payload = [
            'event' => $event,
            'email' => $email,
            'name' => $name,
            'tenant_id' => $additionalData['tenant_id'] ?? 0,
        ];

        // Add account-specific fields
        if (isset($additionalData['setup_url'])) {
            $payload['setup_url'] = $additionalData['setup_url'];
        }
        if (isset($additionalData['expires_at'])) {
            $payload['expires_at'] = $additionalData['expires_at'];
        }
        if (isset($additionalData['login_url'])) {
            $payload['login_url'] = $additionalData['login_url'];
        }
        if (isset($additionalData['user_id'])) {
            $payload['user_id'] = $additionalData['user_id'];
        }
        if (isset($additionalData['event_name'])) {
            $payload['event_name'] = $additionalData['event_name'];
        }

        return $payload;
    }

    /**
     * Validate account event payload
     */
    private function validateAccountEvent(string $event, array $payload): bool
    {
        $requiredFields = [
            'account_owner_paid_signup_created' => ['email', 'name', 'setup_url', 'expires_at'],
            'team_member_invited' => ['email', 'name', 'setup_url', 'expires_at'],
            'customer_portal_invited' => ['email', 'name', 'setup_url', 'expires_at'],
            'setup_link_expiring' => ['email', 'name', 'setup_url', 'expires_at'],
            'setup_link_expired' => ['email', 'name']
        ];

        $required = $requiredFields[$event] ?? [];

        foreach ($required as $field) {
            if (empty($payload[$field])) {
                Log::error("Missing required field '{$field}' for event '{$event}'", $payload);
                return false;
            }
        }

        return true;
    }

    /**
     * Generate unique event_id, persist to outbox, and dispatch job. Prevents duplicate send by using unique event_id.
     * One logical occurrence = one event_id = one outbox row = one job.
     */
    public function dispatchEvent(string $event, array $payload): bool
    {
        // Validate account events
        if ($this->isAccountEvent($event) && !$this->validateAccountEvent($event, $payload)) {
            return false;
        }

        $eventId = (string) Str::uuid();

        $payload['event_id'] = $eventId;
        $payload['event'] = $event;

        try {
            AutomationEventOutbox::create([
                'event_id' => $eventId,
                'event' => $event,
                'tenant_id' => $payload['tenant_id'] ?? 0,
                'payload' => $payload,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Idempotency: event_id already exists, skip dispatch
            return false;
        }

        SendN8nWebhookJob::dispatch($eventId, $payload);

        return true;
    }

    /**
     * Check if event is an account event
     */
    private function isAccountEvent(string $event): bool
    {
        return in_array($event, [
            'account_owner_paid_signup_created',
            'team_member_invited',
            'customer_portal_invited',
            'setup_link_expiring',
            'setup_link_expired'
        ]);
    }
}
