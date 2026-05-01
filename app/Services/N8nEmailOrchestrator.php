<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class N8nEmailOrchestrator
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $eventName, array $payload): bool
    {
        $url = (string) config('services.n8n.webhook_url');
        $eventId = trim((string) ($payload['event_id'] ?? ''));
        if ($eventId === '') {
            $eventId = (string) Str::uuid();
        }

        $occurredAt = $payload['occurred_at'] ?? now()->toIso8601String();
        $idempotencyKey = trim((string) ($payload['idempotency_key'] ?? ''));
        if ($idempotencyKey === '') {
            $idempotencyKey = $eventName . ':' . $eventId;
        }

        $dispatchPayload = array_merge($payload, [
            'event_id' => $eventId,
            'event_name' => $eventName,
            'occurred_at' => $occurredAt,
            'idempotency_key' => $idempotencyKey,
        ]);

        $this->ingestInAppNotification($eventName, $dispatchPayload);

        if ($url === '') {
            Log::warning('N8N webhook URL is not configured.', ['event_name' => $eventName]);
            app(DeliveryLogService::class)->record('webhook', [
                'tenant_id' => $payload['tenant_id'] ?? null,
                'event_name' => $eventName,
                'recipient' => $url,
                'provider' => 'n8n',
                'status' => 'failed',
                'error_message' => 'N8N webhook URL is not configured.',
                'idempotency_key' => $idempotencyKey,
                'meta' => $dispatchPayload,
            ]);

            return false;
        }

        $headers = [];
        $token = (string) config('services.n8n.webhook_token');
        if ($token !== '') {
            $headers['X-Webhook-Token'] = $token;
        }

        try {
            $retryTimes = max(1, (int) config('services.n8n.webhook_retry_times', 1));
            $retryDelayMs = max(0, (int) config('services.n8n.webhook_retry_delay_ms', 200));
            $connectTimeoutSeconds = max(1, (int) config('services.n8n.webhook_connect_timeout_seconds', 2));
            $timeoutSeconds = max($connectTimeoutSeconds, (int) config('services.n8n.webhook_timeout_seconds', 4));

            $response = Http::retry($retryTimes, $retryDelayMs)
                ->connectTimeout($connectTimeoutSeconds)
                ->timeout($timeoutSeconds)
                ->acceptJson()
                ->withHeaders($headers)
                ->post($url, $dispatchPayload);

            if ($response->successful()) {
                app(DeliveryLogService::class)->record('webhook', [
                    'tenant_id' => $payload['tenant_id'] ?? null,
                    'event_name' => $eventName,
                    'recipient' => $url,
                    'provider' => 'n8n',
                    'status' => 'sent',
                    'response_code' => $response->status(),
                    'idempotency_key' => $idempotencyKey,
                    'meta' => $dispatchPayload,
                ]);

                return true;
            }

            Log::warning('N8N webhook dispatch failed.', [
                'event_name' => $eventName,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            app(DeliveryLogService::class)->record('webhook', [
                'tenant_id' => $payload['tenant_id'] ?? null,
                'event_name' => $eventName,
                'recipient' => $url,
                'provider' => 'n8n',
                'status' => 'failed',
                'response_code' => $response->status(),
                'error_message' => 'N8N webhook returned an unsuccessful response.',
                'idempotency_key' => $idempotencyKey,
                'meta' => $dispatchPayload,
            ]);
        } catch (\Throwable $e) {
            Log::warning('N8N webhook dispatch exception.', [
                'event_name' => $eventName,
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
            app(DeliveryLogService::class)->record('webhook', [
                'tenant_id' => $payload['tenant_id'] ?? null,
                'event_name' => $eventName,
                'recipient' => $url,
                'provider' => 'n8n',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'idempotency_key' => $idempotencyKey,
                'meta' => $dispatchPayload,
            ]);
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function ingestInAppNotification(string $eventName, array $payload): void
    {
        $tenantId = isset($payload['tenant_id']) ? (int) $payload['tenant_id'] : 0;
        if ($tenantId <= 0) {
            return;
        }

        try {
            app(InAppNotificationService::class)->ingestAutomationEvent($tenantId, $eventName, $payload, 'laravel');
        } catch (\Throwable $e) {
            Log::warning('Failed to create in-app notification before n8n dispatch.', [
                'tenant_id' => $tenantId,
                'event_name' => $eventName,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
