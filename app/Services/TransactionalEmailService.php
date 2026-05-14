<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class TransactionalEmailService
{
    /**
     * @param  array<string, mixed>  $meta
     * @return array{sent: bool, provider: string, error_message: string|null}
     */
    public function sendPlainText(string $recipient, string $subject, string $body, array $meta = []): array
    {
        return $this->deliver($recipient, $subject, $body, null, $meta);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{sent: bool, provider: string, error_message: string|null}
     */
    public function sendHtml(string $recipient, string $subject, string $html, array $meta = [], ?string $textBody = null): array
    {
        return $this->deliver($recipient, $subject, $textBody ?? trim(strip_tags($html)), $html, $meta);
    }

    /**
     * @param  array<string, mixed>  $viewData
     * @param  array<string, mixed>  $meta
     * @return array{sent: bool, provider: string, error_message: string|null}
     */
    public function sendView(string $recipient, string $subject, string $view, array $viewData = [], array $meta = [], ?string $textBody = null): array
    {
        $html = View::make($view, $viewData)->render();

        return $this->sendHtml($recipient, $subject, $html, $meta, $textBody);
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $meta
     * @return array{sent: bool, provider: string, error_message: string|null}
     */
    public function sendDeliveryUpdateEmail(string $recipient, array $context, array $meta = []): array
    {
        $funnelName = trim((string) ($context['funnel_name'] ?? 'Your Order'));
        $customerName = trim((string) ($context['customer_name'] ?? 'Customer'));
        $deliveryStatus = trim((string) ($context['delivery_status'] ?? 'processing'));
        $courierName = trim((string) ($context['courier_name'] ?? 'LBC'));
        $trackingValue = isset($context['tracking_value']) ? trim((string) $context['tracking_value']) : null;
        $customMessage = isset($context['custom_message']) ? trim((string) $context['custom_message']) : null;
        $orderItems = is_array($context['order_items'] ?? null) ? $context['order_items'] : [];
        $orderQuantity = max(0, (int) ($context['order_quantity'] ?? 0));
        $statusLabel = ucwords(str_replace('_', ' ', $deliveryStatus));

        return $this->sendView(
            $recipient,
            'Order Update: ' . $statusLabel . ' - ' . $funnelName,
            'emails.funnels.order-delivery-update',
            [
                'funnelName' => $funnelName,
                'customerName' => $customerName,
                'deliveryStatus' => $deliveryStatus,
                'trackingUrl' => $trackingValue !== '' ? $trackingValue : null,
                'courierName' => $courierName,
                'orderItems' => $orderItems,
                'orderQuantity' => $orderQuantity,
                'customMessage' => $customMessage !== '' ? $customMessage : null,
            ],
            $meta,
            $this->deliveryUpdateTextBody(
                $funnelName,
                $customerName,
                $deliveryStatus,
                $courierName,
                $orderItems,
                $orderQuantity,
                $trackingValue !== '' ? $trackingValue : null,
                $customMessage !== '' ? $customMessage : null
            )
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{sent: bool, provider: string, error_message: string|null}
     */
    private function deliver(string $recipient, string $subject, string $textBody, ?string $htmlBody, array $meta): array
    {
        $apiKey = ltrim(trim((string) config('services.brevo.api_key')), '=');
        $senderEmail = trim((string) config('mail.from.address'));
        $senderName = trim((string) config('mail.from.name'));
        $idempotencyKey = trim((string) ($meta['idempotency_key'] ?? ''));
        $lastError = null;

        if ($apiKey !== '' && $senderEmail !== '') {
            try {
                $payload = [
                    'sender' => array_filter([
                        'email' => $senderEmail,
                        'name' => $senderName !== '' ? $senderName : null,
                    ]),
                    'to' => [
                        ['email' => $recipient],
                    ],
                    'subject' => $subject,
                    'textContent' => $textBody,
                ];

                if ($htmlBody !== null && trim($htmlBody) !== '') {
                    $payload['htmlContent'] = $htmlBody;
                }

                $response = Http::timeout(15)
                    ->acceptJson()
                    ->withHeaders([
                        'api-key' => $apiKey,
                    ])
                    ->post('https://api.brevo.com/v3/smtp/email', $payload);

                if ($response->successful()) {
                    $this->recordDelivery($recipient, 'brevo', 'sent', null, $idempotencyKey, $meta, null);

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
            if ($htmlBody !== null && trim($htmlBody) !== '') {
                Mail::html($htmlBody, function ($message) use ($recipient, $subject) {
                    $message->to($recipient)->subject($subject);
                });
            } else {
                Mail::raw($textBody, function ($message) use ($recipient, $subject) {
                    $message->to($recipient)->subject($subject);
                });
            }

            $provider = (string) config('mail.default', 'log');
            $status = in_array($provider, ['log', 'array'], true) ? 'processed' : 'sent';
            $error = $status === 'processed'
                ? 'Laravel mail fallback used a non-delivery mailer (' . $provider . ').'
                : null;

            $this->recordDelivery($recipient, $provider, $status, $error, $idempotencyKey, $meta, null);

            return [
                'sent' => in_array($status, ['sent', 'processed'], true),
                'provider' => $provider,
                'error_message' => $error,
            ];
        } catch (\Throwable $e) {
            $lastError = $e->getMessage();
            Log::warning('Transactional email fallback mail send failed.', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'meta' => $meta,
            ]);
        }

        $provider = (string) config('mail.default', 'log');
        $this->recordDelivery($recipient, $provider, 'failed', $lastError, $idempotencyKey, $meta, null);

        return ['sent' => false, 'provider' => $provider, 'error_message' => $lastError];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function recordDelivery(
        string $recipient,
        string $provider,
        string $status,
        ?string $errorMessage,
        string $idempotencyKey,
        array $meta,
        ?int $responseCode,
    ): void {
        app(DeliveryLogService::class)->record('email', [
            'tenant_id' => $meta['tenant_id'] ?? null,
            'user_id' => $meta['user_id'] ?? null,
            'lead_id' => $meta['lead_id'] ?? null,
            'event_name' => $meta['template'] ?? $meta['event_name'] ?? null,
            'recipient' => $recipient,
            'provider' => $provider,
            'status' => $status,
            'response_code' => $responseCode,
            'error_message' => $errorMessage,
            'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
            'is_billable' => (bool) ($meta['is_billable'] ?? false),
            'meta' => $meta,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $orderItems
     */
    private function deliveryUpdateTextBody(
        string $funnelName,
        string $customerName,
        string $deliveryStatus,
        string $courierName,
        array $orderItems,
        int $orderQuantity,
        ?string $trackingValue,
        ?string $customMessage,
    ): string {
        $statusLabel = ucwords(str_replace('_', ' ', $deliveryStatus));
        $lines = [
            'Hello ' . ($customerName !== '' ? $customerName : 'Customer') . ',',
            '',
            'Your order from ' . $funnelName . ' is now ' . strtolower($statusLabel) . '.',
            'Courier: ' . $courierName,
        ];

        if ($orderItems !== []) {
            $lines[] = '';
            $lines[] = 'Order summary:';

            foreach ($orderItems as $item) {
                $name = trim((string) ($item['name'] ?? 'Product'));
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $details = array_values(array_filter([
                    trim((string) ($item['badge'] ?? '')),
                    trim((string) ($item['price'] ?? '')),
                ]));

                $line = '- ' . ($name !== '' ? $name : 'Product') . ' x' . $quantity;
                if ($details !== []) {
                    $line .= ' (' . implode(' | ', $details) . ')';
                }

                $lines[] = $line;
            }

            $lines[] = 'Total quantity: ' . ($orderQuantity > 0 ? $orderQuantity : collect($orderItems)->sum(fn (array $item) => max(1, (int) ($item['quantity'] ?? 1))));
        }

        if ($customMessage) {
            $lines[] = '';
            $lines[] = 'Message from our team:';
            $lines[] = $customMessage;
        }

        if ($trackingValue) {
            $lines[] = '';
            $lines[] = preg_match('/^https?:\\/\\//i', $trackingValue) === 1
                ? 'Track delivery: ' . $trackingValue
                : 'Tracking number: ' . $trackingValue;
        }

        return implode("\n", $lines);
    }
}
