<?php

namespace App\Services;

use App\Models\ExternalDeliveryLog;
use App\Models\FunnelEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class FunnelPaidCustomerEmailService
{
    public function sendForPaidEvent(FunnelEvent $event): bool
    {
        if (! (bool) config('funnels.paid_confirmation_email_enabled', true)) {
            return false;
        }

        $event->loadMissing([
            'funnel:id,tenant_id,name',
            'lead:id,name,email,phone',
            'payment:id,tenant_id,lead_id,payment_type,amount,status,payment_method,provider,provider_reference',
        ]);

        $payment = $event->payment;
        if (! $payment || ! $payment->isFunnelSale() || $payment->status !== 'paid') {
            return false;
        }

        $meta = is_array($event->meta) ? $event->meta : [];
        $recipient = trim((string) (
            data_get($meta, 'customer.email')
            ?: data_get($meta, 'recipient_email')
            ?: data_get($meta, 'lead_email')
            ?: $event->lead?->email
        ));

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $baseIdempotencyKey = 'funnel_paid_customer_email:' . $event->id;
        $existingLogs = ExternalDeliveryLog::query()
            ->where('channel', 'email')
            ->where('event_name', 'funnel_payment_paid_customer')
            ->where(function ($query) use ($baseIdempotencyKey) {
                $query->where('idempotency_key', $baseIdempotencyKey)
                    ->orWhere('idempotency_key', 'like', $baseIdempotencyKey . ':retry:%');
            })
            ->orderByDesc('id')
            ->get();

        if ($this->hasConfirmedDelivery($existingLogs) || $this->hasRecentPendingHandoff($existingLogs)) {
            return true;
        }

        $idempotencyKey = $this->resolveDispatchIdempotencyKey($existingLogs, $baseIdempotencyKey);

        $funnelName = trim((string) ($event->funnel?->name ?? 'your order'));
        $customerName = trim((string) (
            data_get($meta, 'customer.full_name')
            ?: data_get($meta, 'customer.first_name')
            ?: $event->lead?->name
            ?: 'Customer'
        ));
        $customerPhone = trim((string) (
            data_get($meta, 'customer.phone')
            ?: $event->lead?->phone
            ?: ''
        ));

        $portalAccess = [
            'user' => null,
            'setup_url' => null,
            'login_url' => route('login'),
            'setup_required' => false,
            'setup_expires_at' => null,
        ];

        try {
            if ($event->funnel?->tenant) {
                $portalAccess = app(FunnelCustomerPortalService::class)->provisionForPaidCustomer(
                    $event->funnel->tenant,
                    $recipient,
                    $customerName,
                    $customerPhone !== '' ? $customerPhone : null,
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Funnel customer portal provisioning failed.', [
                'funnel_event_id' => $event->id,
                'payment_id' => $payment->id,
                'recipient' => $recipient,
                'message' => $e->getMessage(),
            ]);
        }

        $subject = 'Payment successful';
        if ($funnelName !== '' && $funnelName !== 'your order') {
            $subject .= ' - ' . $funnelName;
        }

        $emailData = $this->buildEmailData($customerName, $funnelName, $payment, $meta, $portalAccess, $event);
        $htmlBody = View::make('emails.funnels.paid-order-confirmation', $emailData)->render();
        $textBody = $this->buildTextBody($emailData);

        if ($this->shouldDispatchViaN8n()) {
            $handoffResult = $this->dispatchViaN8n(
                $event,
                $payment->id,
                $recipient,
                $customerName,
                $subject,
                $htmlBody,
                $textBody,
                $portalAccess,
                $emailData,
                $idempotencyKey,
            );

            if ($handoffResult) {
                return true;
            }
        }

        try {
            $result = app(TransactionalEmailService::class)->sendHtml(
                $recipient,
                $subject,
                $htmlBody,
                [
                    'template' => 'funnel_payment_paid_customer',
                    'event_name' => 'funnel_payment_paid_customer',
                    'tenant_id' => $event->tenant_id,
                    'lead_id' => $event->lead_id,
                    'user_id' => $portalAccess['user']?->id,
                    'payment_id' => $payment->id,
                    'funnel_event_id' => $event->id,
                    'idempotency_key' => $idempotencyKey,
                    'is_billable' => true,
                ],
                $textBody
            );

            return (bool) ($result['sent'] ?? false);
        } catch (\Throwable $e) {
            Log::warning('Funnel paid customer email send failed.', [
                'funnel_event_id' => $event->id,
                'payment_id' => $payment->id,
                'recipient' => $recipient,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $portalAccess
     * @return array<string, mixed>
     */
    private function buildEmailData(
        string $customerName,
        string $funnelName,
        mixed $payment,
        array $meta,
        array $portalAccess,
        FunnelEvent $event,
    ): array {
        $orderItems = is_array($meta['order_items'] ?? null) ? $meta['order_items'] : [];
        $orderedAt = $event->occurred_at ?? $payment->created_at ?? now();
        $orderedAt = $orderedAt instanceof Carbon ? $orderedAt : Carbon::parse((string) $orderedAt);
        $paymentStatus = 'paid';
        $deliveryStatus = trim((string) data_get($meta, 'delivery_status'));
        if ($deliveryStatus === '') {
            $deliveryStatus = 'processing';
        }
        $courierName = trim((string) data_get($meta, 'courier_name'));
        if ($courierName === '') {
            $courierName = 'LBC';
        }
        $estimatedDelivery = $this->estimatedDeliveryWindow($orderedAt, $meta, $courierName);

        return [
            'customerName' => $customerName !== '' ? $customerName : 'Customer',
            'funnelName' => $funnelName !== '' ? $funnelName : 'Your Order',
            'amount' => (float) $payment->amount,
            'paymentMethod' => $this->formatPaymentMethod(
                trim((string) ($payment->payment_method ?: $payment->provider ?: ''))
            ),
            'reference' => trim((string) ($payment->provider_reference ?? '')),
            'orderItems' => $orderItems,
            'paymentStatus' => $paymentStatus,
            'paymentStatusLabel' => 'Paid',
            'deliveryStatus' => $deliveryStatus,
            'deliveryStatusLabel' => $this->statusLabel($deliveryStatus),
            'orderedAtLabel' => $orderedAt->format('M j, Y g:i A'),
            'orderedAtIso' => $orderedAt->toIso8601String(),
            'setupRequired' => (bool) ($portalAccess['setup_required'] ?? false),
            'setupUrl' => trim((string) ($portalAccess['setup_url'] ?? '')) ?: null,
            'loginUrl' => trim((string) ($portalAccess['login_url'] ?? route('login'))),
            'setupExpiresLabel' => $this->formatIsoDateTime($portalAccess['setup_expires_at'] ?? null),
            'portalRole' => 'Customer',
            'shippingAddress' => $this->shippingAddressFromMeta($meta),
            'courierName' => $courierName,
            'estimatedArrivalLabel' => $estimatedDelivery['label'],
            'estimatedArrivalRangeLabel' => $estimatedDelivery['range_label'],
            'estimatedArrivalRegionLabel' => $estimatedDelivery['region_label'],
            'estimatedArrivalNote' => $estimatedDelivery['note'],
            'statusTimeline' => [
                ['key' => 'paid', 'label' => 'Paid'],
                ['key' => 'processing', 'label' => 'Processing'],
                ['key' => 'shipped', 'label' => 'Shipped'],
                ['key' => 'out_for_delivery', 'label' => 'Out for Delivery'],
                ['key' => 'delivered', 'label' => 'Delivered'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $emailData
     */
    private function buildTextBody(array $emailData): string
    {
        $lines = [
            'Hello ' . (string) ($emailData['customerName'] ?? 'Customer') . ',',
            '',
            'Thank you. Your payment was successful.',
        ];

        $funnelName = trim((string) ($emailData['funnelName'] ?? ''));
        if ($funnelName !== '') {
            $lines[] = 'Order: ' . $funnelName;
        }

        $lines[] = 'Amount: PHP ' . number_format((float) ($emailData['amount'] ?? 0), 2);
        $lines[] = 'Ordered on: ' . (string) ($emailData['orderedAtLabel'] ?? '-');
        $lines[] = 'Payment status: ' . (string) ($emailData['paymentStatusLabel'] ?? 'Paid');
        $lines[] = 'Delivery status: ' . (string) ($emailData['deliveryStatusLabel'] ?? 'Processing');

        $paymentMethod = trim((string) ($emailData['paymentMethod'] ?? ''));
        if ($paymentMethod !== '') {
            $lines[] = 'Payment method: ' . $paymentMethod;
        }

        $reference = trim((string) ($emailData['reference'] ?? ''));
        if ($reference !== '') {
            $lines[] = 'Reference: ' . $reference;
        }

        $orderItems = is_array($emailData['orderItems'] ?? null) ? $emailData['orderItems'] : [];
        if ($orderItems !== []) {
            $lines[] = '';
            $lines[] = 'Order summary:';

            foreach ($orderItems as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $name = trim((string) ($item['name'] ?? 'Product'));
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $price = trim((string) ($item['price'] ?? ''));

                $line = '- ' . ($name !== '' ? $name : 'Product') . ' x' . $quantity;
                if ($price !== '') {
                    $line .= ' (' . $price . ')';
                }

                $lines[] = $line;
            }
        }

        $shippingAddress = trim((string) ($emailData['shippingAddress'] ?? ''));
        if ($shippingAddress !== '') {
            $lines[] = '';
            $lines[] = 'Shipping address: ' . $shippingAddress;
        }

        $estimatedArrival = trim((string) ($emailData['estimatedArrivalLabel'] ?? ''));
        if ($estimatedArrival !== '') {
            $lines[] = '';
            $lines[] = 'Courier: ' . (string) ($emailData['courierName'] ?? 'LBC');
            $lines[] = 'Estimated arrival: ' . $estimatedArrival;
            $arrivalNote = trim((string) ($emailData['estimatedArrivalNote'] ?? ''));
            if ($arrivalNote !== '') {
                $lines[] = $arrivalNote;
            }
        }

        $lines[] = '';
        if (! empty($emailData['setupRequired']) && ! empty($emailData['setupUrl'])) {
            $lines[] = 'A customer portal account has been prepared for you.';
            $lines[] = 'Role: ' . (string) ($emailData['portalRole'] ?? 'Customer');
            $lines[] = 'Set your password here: ' . (string) $emailData['setupUrl'];
            $expires = trim((string) ($emailData['setupExpiresLabel'] ?? ''));
            if ($expires !== '') {
                $lines[] = 'Setup link expires: ' . $expires;
            }
        } else {
            $lines[] = 'Your customer portal is ready.';
        }
        $lines[] = 'Login here: ' . (string) ($emailData['loginUrl'] ?? route('login'));
        $lines[] = 'You can use the portal to view your ordered products and delivery status updates.';
        $lines[] = 'Your order is now being processed. We will contact you again if we need any additional fulfillment details.';
        $lines[] = '';
        $lines[] = 'Thank you,';
        $lines[] = (string) config('app.name', 'Sales & Marketing Funnel System');

        return implode("\n", $lines);
    }

    private function formatPaymentMethod(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }

        return match ($normalized) {
            'gcash' => 'GCash',
            'card' => 'Card',
            'manual_transfer', 'manual' => 'Manual Transfer',
            default => ucwords(str_replace('_', ' ', $normalized)),
        };
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function shippingAddressFromMeta(array $meta): ?string
    {
        $address = trim((string) data_get($meta, 'delivery_address'));
        if ($address !== '') {
            return $address;
        }

        $parts = [
            trim((string) data_get($meta, 'shipping.street')),
            trim((string) data_get($meta, 'shipping.barangay')),
            trim((string) data_get($meta, 'shipping.city_municipality')),
            trim((string) data_get($meta, 'shipping.province')),
            trim((string) data_get($meta, 'shipping.postal_code')),
        ];

        $address = collect($parts)
            ->filter(fn (string $value) => $value !== '')
            ->implode(', ');

        return $address !== '' ? $address : null;
    }

    private function formatIsoDateTime(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('M j, Y g:i A');
        } catch (\Throwable) {
            return null;
        }
    }

    private function statusLabel(string $value): string
    {
        return ucwords(str_replace('_', ' ', trim($value)));
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array{label: string|null, range_label: string|null, region_label: string, note: string|null}
     */
    private function estimatedDeliveryWindow(Carbon $orderedAt, array $meta, string $courierName): array
    {
        $regionKey = $this->shippingRegionKey($meta);
        $windows = config('funnels.lbc_delivery_windows', []);
        $window = is_array($windows[$regionKey] ?? null) ? $windows[$regionKey] : [
            'min_business_days' => 2,
            'max_business_days' => 4,
        ];

        $minDays = max(1, (int) ($window['min_business_days'] ?? 2));
        $maxDays = max($minDays, (int) ($window['max_business_days'] ?? $minDays));
        $cutoffHour = max(0, min(23, (int) config('funnels.lbc_delivery_cutoff_hour', 15)));

        $dispatchStart = $orderedAt->copy();
        if ((int) $dispatchStart->format('G') >= $cutoffHour) {
            $dispatchStart = $dispatchStart->addDay();
        }
        $dispatchStart = $this->moveToBusinessDayStart($dispatchStart);

        $minArrival = $this->addBusinessDays($dispatchStart, $minDays)->setTime(9, 0);
        $maxArrival = $this->addBusinessDays($dispatchStart, $maxDays)->setTime(18, 0);
        $regionLabel = $this->shippingRegionLabel($regionKey);

        return [
            'label' => $minArrival->format('M j, Y g:i A') . ' to ' . $maxArrival->format('M j, Y g:i A'),
            'range_label' => $minDays . '-' . $maxDays . ' business days',
            'region_label' => $regionLabel,
            'note' => $courierName . ' estimate for ' . $regionLabel . ': about ' . $minDays . '-' . $maxDays . ' business days after payment verification.',
        ];
    }

    /**
     * @param  array<string, mixed>  $portalAccess
     * @param  array<string, mixed>  $emailData
     */
    private function dispatchViaN8n(
        FunnelEvent $event,
        int $paymentId,
        string $recipient,
        string $customerName,
        string $subject,
        string $htmlBody,
        string $textBody,
        array $portalAccess,
        array $emailData,
        string $idempotencyKey,
    ): bool {
        $dispatched = app(N8nEmailOrchestrator::class)->dispatch('funnel_payment_paid_customer', [
            'event_id' => 'funnel_paid_customer_email:' . $event->id,
            'idempotency_key' => $idempotencyKey,
            'tenant_id' => $event->tenant_id,
            'user_id' => $portalAccess['user']?->id,
            'lead_id' => $event->lead_id,
            'payment_id' => $paymentId,
            'funnel_event_id' => $event->id,
            'funnel_id' => $event->funnel_id,
            'email' => $recipient,
            'recipient_email' => $recipient,
            'name' => $customerName !== '' ? $customerName : 'Customer',
            'subject' => $subject,
            'html' => $htmlBody,
            'text' => $textBody,
            'login_url' => (string) ($emailData['loginUrl'] ?? route('login')),
            'setup_url' => (string) ($emailData['setupUrl'] ?? ''),
            'expires_at' => (string) ($portalAccess['setup_expires_at'] ?? ''),
            'portal_role' => (string) ($emailData['portalRole'] ?? 'Customer'),
            'payment_status' => (string) ($emailData['paymentStatusLabel'] ?? 'Paid'),
            'delivery_status' => (string) ($emailData['deliveryStatusLabel'] ?? 'Processing'),
            'ordered_at' => (string) ($emailData['orderedAtIso'] ?? ''),
            'ordered_at_label' => (string) ($emailData['orderedAtLabel'] ?? ''),
            'estimated_arrival' => (string) ($emailData['estimatedArrivalLabel'] ?? ''),
            'estimated_arrival_range' => (string) ($emailData['estimatedArrivalRangeLabel'] ?? ''),
            'courier_name' => (string) ($emailData['courierName'] ?? 'LBC'),
            'shipping_address' => (string) ($emailData['shippingAddress'] ?? ''),
            'order_items' => is_array($emailData['orderItems'] ?? null) ? $emailData['orderItems'] : [],
            'occurred_at' => optional($event->occurred_at)->toIso8601String(),
        ]);

        if (! $dispatched) {
            return false;
        }

        app(DeliveryLogService::class)->record('email', [
            'tenant_id' => $event->tenant_id,
            'user_id' => $portalAccess['user']?->id,
            'lead_id' => $event->lead_id,
            'event_name' => 'funnel_payment_paid_customer',
            'recipient' => $recipient,
            'provider' => 'n8n',
            'status' => 'processed',
            'idempotency_key' => $idempotencyKey,
            'is_billable' => true,
            'meta' => [
                'template' => 'funnel_payment_paid_customer',
                'funnel_event_id' => $event->id,
                'payment_id' => $paymentId,
                'handoff_only' => true,
            ],
        ]);

        return true;
    }

    private function shouldDispatchViaN8n(): bool
    {
        return trim((string) config('services.n8n.webhook_url')) !== ''
            || trim((string) config('services.n8n.base_url')) !== '';
    }

    /**
     * @param  Collection<int, ExternalDeliveryLog>  $logs
     */
    private function hasConfirmedDelivery(Collection $logs): bool
    {
        return $logs->contains(function (ExternalDeliveryLog $log): bool {
            if ($log->status === 'sent') {
                return true;
            }

            if ($log->status !== 'processed') {
                return false;
            }

            $meta = is_array($log->meta) ? $log->meta : [];
            $handoffOnly = (bool) ($meta['handoff_only'] ?? false);
            $isNonDeliveryFallback = $log->provider === 'log'
                || str_contains(strtolower((string) $log->error_message), 'non-delivery');

            return ! $handoffOnly && ! $isNonDeliveryFallback;
        });
    }

    /**
     * @param  Collection<int, ExternalDeliveryLog>  $logs
     */
    private function hasRecentPendingHandoff(Collection $logs): bool
    {
        $staleThreshold = now()->subMinutes(10);

        return $logs->contains(function (ExternalDeliveryLog $log) use ($staleThreshold): bool {
            if ($log->provider !== 'n8n' || $log->status !== 'processed') {
                return false;
            }

            $meta = is_array($log->meta) ? $log->meta : [];
            $handoffOnly = (bool) ($meta['handoff_only'] ?? false);

            return $handoffOnly
                && $log->created_at !== null
                && $log->created_at->greaterThanOrEqualTo($staleThreshold);
        });
    }

    /**
     * @param  Collection<int, ExternalDeliveryLog>  $logs
     */
    private function hasStalePendingHandoff(Collection $logs): bool
    {
        $staleThreshold = now()->subMinutes(10);

        return $logs->contains(function (ExternalDeliveryLog $log) use ($staleThreshold): bool {
            if ($log->provider !== 'n8n' || $log->status !== 'processed') {
                return false;
            }

            $meta = is_array($log->meta) ? $log->meta : [];
            $handoffOnly = (bool) ($meta['handoff_only'] ?? false);

            return $handoffOnly
                && $log->created_at !== null
                && $log->created_at->lt($staleThreshold);
        });
    }

    /**
     * @param  Collection<int, ExternalDeliveryLog>  $logs
     */
    private function resolveDispatchIdempotencyKey(Collection $logs, string $baseIdempotencyKey): string
    {
        if (! $this->hasStalePendingHandoff($logs)) {
            return $baseIdempotencyKey;
        }

        $nextRetryNumber = $logs
            ->map(function (ExternalDeliveryLog $log) use ($baseIdempotencyKey): int {
                $key = trim((string) $log->idempotency_key);
                if (! preg_match('/^' . preg_quote($baseIdempotencyKey, '/') . ':retry:(\d+)$/', $key, $matches)) {
                    return 0;
                }

                return (int) ($matches[1] ?? 0);
            })
            ->max() + 1;

        return $baseIdempotencyKey . ':retry:' . $nextRetryNumber;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function shippingRegionKey(array $meta): string
    {
        $province = strtolower(trim((string) data_get($meta, 'shipping.province')));
        $city = strtolower(trim((string) data_get($meta, 'shipping.city_municipality')));

        if ($province === '' && $city === '') {
            return 'luzon';
        }

        $metroManilaTerms = [
            'metro manila', 'ncr', 'manila', 'quezon city', 'makati', 'taguig', 'pasig',
            'paranaque', 'paranaque city', 'pasay', 'mandaluyong', 'muntinlupa', 'marikina',
            'las pinas', 'las pinas city', 'navotas', 'malabon', 'valenzuela', 'caloocan',
            'san juan', 'pateros',
        ];
        if (in_array($province, $metroManilaTerms, true) || in_array($city, $metroManilaTerms, true)) {
            return 'metro_manila';
        }

        $mindanaoTerms = [
            'davao del norte', 'davao del sur', 'davao de oro', 'davao oriental', 'davao occidental',
            'bukidnon', 'misamis oriental', 'misamis occidental', 'camiguin', 'lanao del norte',
            'lanao del sur', 'maguindanao', 'maguindanao del norte', 'maguindanao del sur',
            'south cotabato', 'north cotabato', 'cotabato', 'sultan kudarat', 'sarangani',
            'zamboanga del norte', 'zamboanga del sur', 'zamboanga sibugay', 'agusan del norte',
            'agusan del sur', 'surigao del norte', 'surigao del sur', 'dinagat islands', 'basilan',
            'sulu', 'tawi-tawi', 'davao', 'butuan', 'cagayan de oro', 'cdo', 'gensan', 'general santos',
        ];
        if (in_array($province, $mindanaoTerms, true) || in_array($city, $mindanaoTerms, true)) {
            return 'mindanao';
        }

        $visayasTerms = [
            'cebu', 'bohol', 'negros oriental', 'negros occidental', 'iloilo', 'capiz', 'aklan',
            'antique', 'guimaras', 'leyte', 'southern leyte', 'biliran', 'samar', 'eastern samar',
            'northern samar', 'siquijor', 'ormoc', 'bacolod', 'iloilo city', 'cebu city', 'tacloban',
        ];
        if (in_array($province, $visayasTerms, true) || in_array($city, $visayasTerms, true)) {
            return 'visayas';
        }

        return 'luzon';
    }

    private function shippingRegionLabel(string $regionKey): string
    {
        return match ($regionKey) {
            'metro_manila' => 'Metro Manila',
            'visayas' => 'Visayas',
            'mindanao' => 'Mindanao',
            default => 'Luzon',
        };
    }

    private function moveToBusinessDayStart(Carbon $date): Carbon
    {
        $normalized = $date->copy();

        while ($normalized->isWeekend()) {
            $normalized->addDay();
        }

        return $normalized->setTime(9, 0);
    }

    private function addBusinessDays(Carbon $date, int $businessDays): Carbon
    {
        $current = $this->moveToBusinessDayStart($date);
        $remaining = max(0, $businessDays);

        while ($remaining > 0) {
            $current->addDay();
            if ($current->isWeekend()) {
                continue;
            }
            $remaining--;
        }

        return $current;
    }
}
