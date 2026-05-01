<?php

namespace App\Services;

use App\Models\ExternalDeliveryLog;
use App\Models\Tenant;

class DeliveryLogService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $channel, array $context = []): ExternalDeliveryLog
    {
        return ExternalDeliveryLog::query()->create([
            'tenant_id' => $this->nullableInt($context['tenant_id'] ?? null),
            'user_id' => $this->nullableInt($context['user_id'] ?? null),
            'lead_id' => $this->nullableInt($context['lead_id'] ?? null),
            'channel' => $channel,
            'event_name' => $this->nullableString($context['event_name'] ?? $context['template'] ?? null),
            'recipient' => $this->nullableString($context['recipient'] ?? null),
            'provider' => $this->nullableString($context['provider'] ?? null),
            'status' => $this->nullableString($context['status'] ?? 'failed') ?? 'failed',
            'response_code' => isset($context['response_code']) ? (int) $context['response_code'] : null,
            'error_message' => $this->nullableString($context['error_message'] ?? null),
            'idempotency_key' => $this->nullableString($context['idempotency_key'] ?? null),
            'is_billable' => (bool) ($context['is_billable'] ?? false),
            'meta' => is_array($context['meta'] ?? null) ? $context['meta'] : [],
            'sent_at' => in_array(($context['status'] ?? 'failed'), ['sent', 'processed'], true) ? now() : null,
        ]);
    }

    public function currentMonthBillableUsage(Tenant $tenant): int
    {
        return ExternalDeliveryLog::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_billable', true)
            ->whereIn('channel', ['email', 'sms'])
            ->where('status', 'sent')
            ->where('sent_at', '>=', now()->copy()->startOfMonth())
            ->count();
    }

    private function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }
}
