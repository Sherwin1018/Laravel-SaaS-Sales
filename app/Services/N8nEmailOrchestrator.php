<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class N8nEmailOrchestrator
{
    private const WEBHOOK_PATH_UNIFIED = 'laravel-unified-events';

    private const LEGACY_WEBHOOK_PATH_UNIFIED = 'laravel-events';

    private const WEBHOOK_PATH_AUTH = 'laravel-auth-events';

    private const WEBHOOK_PATH_AUTOMATION = 'saas-automation-events';

    private const WEBHOOK_PATH_FINANCE = 'laravel-finance-events';

    private const AUTH_EVENTS = [
        'account_owner_paid_signup_created',
        'account_owner_google_paid_signup_created',
        'team_member_invited',
        'customer_portal_invited',
        'funnel_payment_paid_customer',
        'setup_link_expiring',
        'setup_link_expired',
        'payment_successful',
    ];

    private const AUTOMATION_EVENTS = [
        'lead_captured',
        'lead_stage_changed',
        'payment_failed',
        'payment_recovered',
        'funnel_opt_in_submitted',
        'funnel_checkout_started',
        'funnel_payment_paid',
        'funnel_checkout_abandoned',
        'funnel_order_delivery_updated',
        'funnel_upsell_accepted',
        'funnel_upsell_declined',
        'funnel_downsell_accepted',
        'funnel_downsell_declined',
    ];

    private const FINANCE_EVENTS = [
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
        'settlement_payout_recorded',
    ];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $eventName, array $payload): bool
    {
        [$url, $webhookRoute] = $this->resolveWebhookDestination($eventName);
        $provider = 'n8n';
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
            'webhook_route' => $webhookRoute,
        ]);

        $deliveryLogs = app(DeliveryLogService::class);
        if ($deliveryLogs->successfulDispatchExists('webhook', $provider, $idempotencyKey)) {
            $deliveryLogs->record('webhook', [
                'tenant_id' => $payload['tenant_id'] ?? null,
                'event_name' => $eventName,
                'recipient' => $url,
                'provider' => $provider,
                'status' => 'duplicate',
                'idempotency_key' => $idempotencyKey,
                'meta' => array_merge($dispatchPayload, [
                    'duplicate_skipped' => true,
                    'webhook_route' => $webhookRoute,
                ]),
            ]);

            return true;
        }

        $this->ingestInAppNotification($eventName, $dispatchPayload);

        $nonSharedResult = app(PlanAutomationService::class)->handleNonSharedDispatch($eventName, $dispatchPayload);
        if ($nonSharedResult !== null) {
            return $nonSharedResult;
        }

        if ($url === '') {
            Log::warning('N8N webhook URL is not configured.', ['event_name' => $eventName]);
            $deliveryLogs->record('webhook', [
                'tenant_id' => $payload['tenant_id'] ?? null,
                'event_name' => $eventName,
                'recipient' => $url,
                'provider' => $provider,
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
            $attemptNumber = $deliveryLogs->attemptCount('webhook', $provider, $idempotencyKey) + 1;

            $dispatchPayload['dispatch_meta'] = [
                'attempt_number' => $attemptNumber,
                'retry_times' => $retryTimes,
                'retry_delay_ms' => $retryDelayMs,
                'timeout_seconds' => $timeoutSeconds,
                'connect_timeout_seconds' => $connectTimeoutSeconds,
            ];

            $response = Http::retry($retryTimes, $retryDelayMs)
                ->connectTimeout($connectTimeoutSeconds)
                ->timeout($timeoutSeconds)
                ->acceptJson()
                ->withHeaders($headers)
                ->post($url, $dispatchPayload);

            if ($response->successful()) {
                $deliveryLogs->record('webhook', [
                    'tenant_id' => $payload['tenant_id'] ?? null,
                    'event_name' => $eventName,
                    'recipient' => $url,
                    'provider' => $provider,
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
            $deliveryLogs->record('webhook', [
                'tenant_id' => $payload['tenant_id'] ?? null,
                'event_name' => $eventName,
                'recipient' => $url,
                'provider' => $provider,
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
            $deliveryLogs->record('webhook', [
                'tenant_id' => $payload['tenant_id'] ?? null,
                'event_name' => $eventName,
                'recipient' => $url,
                'provider' => $provider,
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

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveWebhookDestination(string $eventName): array
    {
        $route = $this->routeForEvent($eventName);

        return [
            $this->resolveWebhookUrlForRoute($route),
            $route,
        ];
    }

    private function routeForEvent(string $eventName): string
    {
        return match (true) {
            in_array($eventName, self::AUTH_EVENTS, true) => 'auth',
            in_array($eventName, self::AUTOMATION_EVENTS, true) => 'automation',
            in_array($eventName, self::FINANCE_EVENTS, true) => 'finance',
            default => 'unified',
        };
    }

    private function resolveWebhookUrlForRoute(string $route): string
    {
        $configuredUrl = trim((string) config('services.n8n.webhook_url'));
        $path = match ($route) {
            'auth' => self::WEBHOOK_PATH_AUTH,
            'automation' => self::WEBHOOK_PATH_AUTOMATION,
            'finance' => self::WEBHOOK_PATH_FINANCE,
            default => self::WEBHOOK_PATH_UNIFIED,
        };

        if ($configuredUrl !== '') {
            return $this->normalizeConfiguredWebhookUrl($configuredUrl, $path, $route === 'unified');
        }

        return $this->fallbackWebhookUrl($path);
    }

    private function normalizeConfiguredWebhookUrl(string $configuredUrl, string $path, bool $allowExactUrl): string
    {
        $trimmedUrl = rtrim(trim($configuredUrl), '/');
        if ($trimmedUrl === '') {
            return '';
        }

        $configuredBase = $this->configuredWebhookBase($trimmedUrl);
        if ($configuredBase !== '' && $configuredBase !== $trimmedUrl) {
            return $configuredBase . '/' . $path;
        }

        if (str_ends_with($trimmedUrl, '/webhook')) {
            return $trimmedUrl . '/' . $path;
        }

        return $allowExactUrl ? $trimmedUrl : $trimmedUrl . '/' . $path;
    }

    private function configuredWebhookBase(string $configuredUrl): string
    {
        $configuredUrl = trim($configuredUrl);
        if ($configuredUrl === '') {
            return '';
        }

        foreach ([
            self::WEBHOOK_PATH_UNIFIED,
            self::LEGACY_WEBHOOK_PATH_UNIFIED,
            self::WEBHOOK_PATH_AUTH,
            self::WEBHOOK_PATH_AUTOMATION,
            self::WEBHOOK_PATH_FINANCE,
        ] as $path) {
            $suffix = '/' . $path;
            if (str_ends_with($configuredUrl, $suffix)) {
                return substr($configuredUrl, 0, -strlen($suffix));
            }
        }

        return rtrim($configuredUrl, '/');
    }

    private function fallbackWebhookUrl(string $path): string
    {
        $baseUrl = rtrim((string) config('services.n8n.base_url'), '/');
        if ($baseUrl === '') {
            return '';
        }

        return $baseUrl . '/webhook/' . $path;
    }
}
