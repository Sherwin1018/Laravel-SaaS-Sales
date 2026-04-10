<?php

namespace App\Jobs;

use App\Models\AutomationEventOutbox;
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

    /** @var array<string, string> */
    protected static array $eventToPathKey = [
        // Business events
        'lead.created' => 'lead_created',
        'funnel.opt_in' => 'funnel_opt_in',
        'lead.status_changed' => 'lead_status_changed',
        'payment.paid' => 'payment_paid',
        'payment.failed' => 'payment_failed',
        
        // Account events (all use the unified saas-events router)
        'account_owner_paid_signup_created' => 'saas_events',
        'team_member_invited' => 'saas_events',
        'customer_portal_invited' => 'saas_events',
        'setup_link_expiring' => 'saas_events',
        'setup_link_expired' => 'saas_events',
    ];

    public function __construct(
        public string $eventId,
        public array $payload
    ) {}

    public function handle(): void
    {
        $event = $this->payload['event'] ?? '';
        $baseUrl = rtrim(config('n8n.webhook_base_url', ''), '/');

        if ($baseUrl === '') {
            Log::debug('Automation webhook: n8n not configured', ['event' => $event]);
            return;
        }

        $path = $this->resolveWebhookPath($event);
        if ($path === '') {
            Log::debug('Automation webhook: no path resolved', ['event' => $event, 'event_id' => $this->eventId]);
            return;
        }

        $segment = config('n8n.webhook_segment', 'webhook');
        $url = "{$baseUrl}/{$segment}/{$path}";

        Log::debug('Automation webhook: sending', ['url' => $url, 'event' => $event]);

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $this->payload);

            if (!$response->successful()) {
                Log::warning('Automation webhook failed', [
                    'url' => $url,
                    'event_id' => $this->eventId,
                    'event' => $event,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return;
            }

            AutomationEventOutbox::where('event_id', $this->eventId)->update(['sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Automation webhook exception', [
                'url' => $url ?? 'unknown',
                'event_id' => $this->eventId,
                'event' => $event,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Resolve webhook path: single router path if use_router is true, else per-event path.
     */
    private function resolveWebhookPath(string $event): string
    {
        if (filter_var(config('n8n.use_router', false), FILTER_VALIDATE_BOOLEAN)) {
            return (string) config('n8n.router_path', 'saas-events');
        }

        $pathKey = self::$eventToPathKey[$event] ?? null;
        if (!$pathKey) {
            Log::warning('Automation webhook: unknown event', ['event' => $event, 'event_id' => $this->eventId]);
            return '';
        }

        return (string) config("n8n.paths.{$pathKey}", '');
    }
}
