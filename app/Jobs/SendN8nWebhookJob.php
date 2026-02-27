<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendN8nWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $event,
        public array $payload
    ) {}

    public function handle(): void
    {
        $baseUrl = rtrim(config('n8n.webhook_base_url', ''), '/');
        if ($baseUrl === '') {
            Log::debug('n8n: webhook base URL not set, skipping', ['event' => $this->event]);
            return;
        }

        $pathKey = match ($this->event) {
            'lead.created' => 'lead_created',
            'funnel.opt_in' => 'funnel_opt_in',
            'lead.status_changed' => 'lead_status_changed',
            default => null,
        };

        if ($pathKey === null) {
            Log::warning('n8n: unknown event, skipping', ['event' => $this->event]);
            return;
        }

        $path = config("n8n.paths.{$pathKey}", '');
        if ($path === '') {
            Log::warning('n8n: path not configured', ['event' => $this->event]);
            return;
        }

        $url = $baseUrl . '/webhook/' . $path;
        $payload = array_merge(['event' => $this->event], $this->payload);

        Log::info('n8n: sending webhook', ['event' => $this->event, 'url' => $url]);

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('n8n: webhook request failed', [
                    'event' => $this->event,
                    'url' => $url,
                    'status' => $response->status(),
                ]);
            } else {
                Log::info('n8n: webhook sent successfully', ['event' => $this->event, 'url' => $url]);
            }
        } catch (\Throwable $e) {
            Log::error('n8n: webhook request exception', [
                'event' => $this->event,
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
