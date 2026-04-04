<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nEmailOrchestrator
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $eventName, array $payload): bool
    {
        $url = (string) config('services.n8n.webhook_url');
        if ($url === '') {
            Log::warning('N8N webhook URL is not configured.', ['event_name' => $eventName]);

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
                ->post($url, array_merge($payload, [
                    'event_name' => $eventName,
                ]));

            if ($response->successful()) {
                return true;
            }

            Log::warning('N8N webhook dispatch failed.', [
                'event_name' => $eventName,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('N8N webhook dispatch exception.', [
                'event_name' => $eventName,
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
        }

        return false;
    }
}
