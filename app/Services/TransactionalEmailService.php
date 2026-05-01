<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionalEmailService
{
    /**
     * @param  array<string, mixed>  $meta
     * @return array{sent: bool, provider: string, error_message: string|null}
     */
    public function sendPlainText(string $recipient, string $subject, string $body, array $meta = []): array
    {
        $apiKey = ltrim(trim((string) config('services.brevo.api_key')), '=');
        $senderEmail = trim((string) config('mail.from.address'));
        $senderName = trim((string) config('mail.from.name'));
        $lastError = null;

        if ($apiKey !== '' && $senderEmail !== '') {
            try {
                $response = Http::timeout(15)
                    ->acceptJson()
                    ->withHeaders([
                        'api-key' => $apiKey,
                    ])
                    ->post('https://api.brevo.com/v3/smtp/email', [
                        'sender' => array_filter([
                            'email' => $senderEmail,
                            'name' => $senderName !== '' ? $senderName : null,
                        ]),
                        'to' => [
                            ['email' => $recipient],
                        ],
                        'subject' => $subject,
                        'textContent' => $body,
                    ]);

                if ($response->successful()) {
                    app(DeliveryLogService::class)->record('email', [
                        'tenant_id' => $meta['tenant_id'] ?? null,
                        'user_id' => $meta['user_id'] ?? null,
                        'lead_id' => $meta['lead_id'] ?? null,
                        'event_name' => $meta['template'] ?? $meta['event_name'] ?? null,
                        'recipient' => $recipient,
                        'provider' => 'brevo',
                        'status' => 'sent',
                        'is_billable' => (bool) ($meta['is_billable'] ?? false),
                        'meta' => $meta,
                    ]);

                    return ['sent' => true, 'provider' => 'brevo', 'error_message' => null];
                }

                $lastError = 'Brevo returned HTTP ' . $response->status() . '.';
                Log::warning('Brevo transactional email send failed.', [
                    'recipient' => $recipient,
                    'status' => $response->status(),
                    'body' => $response->json() ?: $response->body(),
                    'meta' => $meta,
                ]);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::warning('Brevo transactional email send exception.', [
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                    'meta' => $meta,
                ]);
            }
        }

        try {
            Mail::raw($body, function ($message) use ($recipient, $subject) {
                $message->to($recipient)->subject($subject);
            });

            app(DeliveryLogService::class)->record('email', [
                'tenant_id' => $meta['tenant_id'] ?? null,
                'user_id' => $meta['user_id'] ?? null,
                'lead_id' => $meta['lead_id'] ?? null,
                'event_name' => $meta['template'] ?? $meta['event_name'] ?? null,
                'recipient' => $recipient,
                'provider' => (string) config('mail.default', 'log'),
                'status' => 'sent',
                'is_billable' => (bool) ($meta['is_billable'] ?? false),
                'meta' => $meta,
            ]);

            return ['sent' => true, 'provider' => (string) config('mail.default', 'log'), 'error_message' => null];
        } catch (\Throwable $e) {
            $lastError = $e->getMessage();
            Log::warning('Transactional email fallback mail send failed.', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'meta' => $meta,
            ]);
        }

        app(DeliveryLogService::class)->record('email', [
            'tenant_id' => $meta['tenant_id'] ?? null,
            'user_id' => $meta['user_id'] ?? null,
            'lead_id' => $meta['lead_id'] ?? null,
            'event_name' => $meta['template'] ?? $meta['event_name'] ?? null,
            'recipient' => $recipient,
            'provider' => (string) config('mail.default', 'log'),
            'status' => 'failed',
            'error_message' => $lastError,
            'is_billable' => (bool) ($meta['is_billable'] ?? false),
            'meta' => $meta,
        ]);

        return ['sent' => false, 'provider' => (string) config('mail.default', 'log'), 'error_message' => $lastError];
    }
}
