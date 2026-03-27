<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoCheckoutService
{
    private const API_BASE = 'https://api.paymongo.com/v1';

    public function isConfigured(): bool
    {
        $key = config('services.paymongo.secret');

        return is_string($key) && $key !== '';
    }

    /**
     * @return array{checkout_url: string, id: string}|null
     */
    public function createCheckoutSession(
        int $amountCentavos,
        string $lineItemName,
        string $description,
        string $successUrl,
        string $cancelUrl,
        array $metadata,
        ?array $billing = null,
    ): ?array {
        $types = config('services.paymongo.payment_method_types');
        if (! is_array($types) || $types === []) {
            $types = ['card', 'gcash'];
        }

        $attributes = [
            'line_items' => [
                [
                    'amount' => $amountCentavos,
                    'currency' => 'PHP',
                    'name' => mb_substr($lineItemName, 0, 255),
                    'quantity' => 1,
                    'description' => mb_substr($description, 0, 255),
                ],
            ],
            'payment_method_types' => $types,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'description' => mb_substr($description, 0, 255),
            'show_description' => true,
            'show_line_items' => true,
            'send_email_receipt' => false,
            'metadata' => $this->stringifyMetadata($metadata),
        ];

        if ($billing !== null && $billing !== []) {
            $attributes['billing'] = $billing;
        }

        $response = $this->request()->post(self::API_BASE.'/checkout_sessions', [
            'data' => [
                'attributes' => $attributes,
            ],
        ]);

        if (! $response->successful()) {
            Log::warning('PayMongo checkout session failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        $data = $response->json('data');
        if (! is_array($data)) {
            return null;
        }

        $id = isset($data['id']) ? (string) $data['id'] : '';
        $checkoutUrl = is_array($data['attributes'] ?? null)
            ? (string) ($data['attributes']['checkout_url'] ?? '')
            : '';

        if ($id === '' || $checkoutUrl === '') {
            return null;
        }

        return ['id' => $id, 'checkout_url' => $checkoutUrl];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function retrieveCheckoutSession(string $checkoutSessionId): ?array
    {
        $response = $this->request()->get(self::API_BASE.'/checkout_sessions/'.$checkoutSessionId);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json('data');

        return is_array($data) ? $data : null;
    }

    private function request()
    {
        $secret = config('services.paymongo.secret');

        return Http::withBasicAuth((string) $secret, '')
            ->acceptJson()
            ->asJson();
    }

    /**
     * @param  array<string, scalar|null>  $metadata
     * @return array<string, string>
     */
    private function stringifyMetadata(array $metadata): array
    {
        $out = [];
        foreach ($metadata as $k => $v) {
            if ($v === null) {
                continue;
            }
            $key = preg_replace('/[^a-z0-9_\-]/i', '', (string) $k) ?? '';
            if ($key === '') {
                continue;
            }
            $out[mb_substr($key, 0, 40)] = mb_substr((string) $v, 0, 500);
        }

        return $out;
    }
}
