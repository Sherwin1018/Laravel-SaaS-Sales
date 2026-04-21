<?php

namespace App\Services;

use App\Models\Funnel;
use App\Models\FunnelEvent;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FunnelTrackingService
{
    public const EVENT_STEP_VIEWED = 'funnel_step_viewed';
    public const EVENT_OPT_IN_SUBMITTED = 'funnel_opt_in_submitted';
    public const EVENT_CHECKOUT_STARTED = 'funnel_checkout_started';
    public const EVENT_PAYMENT_PAID = 'funnel_payment_paid';
    public const EVENT_UPSELL_ACCEPTED = 'funnel_upsell_accepted';
    public const EVENT_UPSELL_DECLINED = 'funnel_upsell_declined';
    public const EVENT_DOWNSELL_ACCEPTED = 'funnel_downsell_accepted';
    public const EVENT_DOWNSELL_DECLINED = 'funnel_downsell_declined';
    public const EVENT_CHECKOUT_ABANDONED = 'funnel_checkout_abandoned';
    public const EVENT_ORDER_DELIVERY_UPDATED = 'funnel_order_delivery_updated';

    public function sessionIdentifier(?Request $request = null): ?string
    {
        $request ??= request();
        if (! $request->hasSession()) {
            return null;
        }

        $session = $request->session();
        if (! $session->isStarted()) {
            $session->start();
        }

        $id = trim((string) $session->getId());

        return $id !== '' ? $id : null;
    }

    public function trackStepViewed(Funnel $funnel, FunnelStep $step, Request $request, array $meta = []): FunnelEvent
    {
        return $this->recordEvent([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'event_name' => self::EVENT_STEP_VIEWED,
            'session_identifier' => $this->sessionIdentifier($request),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'meta' => array_merge([
                'step_slug' => $step->slug,
                'step_type' => $step->type,
                'query' => $request->query(),
                'referer' => (string) $request->headers->get('referer', ''),
            ], $meta),
        ]);
    }

    public function trackOptInSubmitted(Funnel $funnel, FunnelStep $step, Lead $lead, Request $request, array $meta = []): FunnelEvent
    {
        return $this->recordEvent([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'event_name' => self::EVENT_OPT_IN_SUBMITTED,
            'session_identifier' => $this->sessionIdentifier($request),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'meta' => array_merge([
                'step_slug' => $step->slug,
                'step_type' => $step->type,
                'lead_email' => $lead->email,
            ], $meta),
        ]);
    }

    public function trackCheckoutStarted(
        Funnel $funnel,
        FunnelStep $step,
        Payment $payment,
        Request $request,
        ?array $pricing = null,
        array $meta = []
    ): FunnelEvent {
        $orderKey = trim((string) ($meta['order_key'] ?? ''));
        if ($orderKey === '' && (int) ($payment->id ?? 0) > 0) {
            $orderKey = 'payment:'.$payment->id;
        }

        return $this->recordEvent([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $payment->lead_id,
            'payment_id' => $payment->id,
            'event_name' => self::EVENT_CHECKOUT_STARTED,
            'session_identifier' => $payment->session_identifier ?: $this->sessionIdentifier($request),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'meta' => array_merge([
                'step_slug' => $step->slug,
                'step_type' => $step->type,
                'amount' => (float) $payment->amount,
                'payment_status' => $payment->status,
                'provider' => $payment->provider,
                'pricing' => $pricing,
                'order_key' => $orderKey,
            ], $meta),
        ]);
    }

    public function trackPaymentPaid(Payment $payment, array $meta = []): ?FunnelEvent
    {
        if ((int) ($payment->funnel_id ?? 0) <= 0) {
            return null;
        }

        $checkoutStarted = FunnelEvent::query()
            ->where('payment_id', $payment->id)
            ->where('event_name', self::EVENT_CHECKOUT_STARTED)
            ->latest('id')
            ->first();

        $checkoutMeta = is_array($checkoutStarted?->meta) ? $checkoutStarted->meta : [];
        $derivedMeta = [];
        foreach ([
            'funnel_purpose',
            'pricing',
            'customer',
            'shipping',
            'delivery_address',
            'order_items',
            'order_item_count',
            'order_quantity',
            'order_items_label',
        ] as $key) {
            if (array_key_exists($key, $checkoutMeta)) {
                $derivedMeta[$key] = $checkoutMeta[$key];
            }
        }

        $eventMeta = array_merge([
            'amount' => (float) $payment->amount,
            'provider' => $payment->provider,
            'provider_reference' => $payment->provider_reference,
            'payment_method' => $payment->payment_method,
            'order_key' => 'payment:'.$payment->id,
        ], $derivedMeta, $meta);

        return FunnelEvent::query()->updateOrCreate(
            [
                'payment_id' => $payment->id,
                'event_name' => self::EVENT_PAYMENT_PAID,
            ],
            [
                'tenant_id' => $payment->tenant_id,
                'funnel_id' => $payment->funnel_id,
                'funnel_step_id' => $payment->funnel_step_id,
                'lead_id' => $payment->lead_id,
                'session_identifier' => $payment->session_identifier,
                'meta' => $eventMeta,
                'occurred_at' => now(),
            ]
        );
    }

    public function trackOfferDecision(
        Funnel $funnel,
        FunnelStep $step,
        string $decision,
        ?Lead $lead = null,
        ?Payment $payment = null,
        ?Request $request = null,
        array $meta = []
    ): FunnelEvent {
        $decision = strtolower(trim($decision));
        $eventName = match ([$step->type, $decision]) {
            ['upsell', 'accept'] => self::EVENT_UPSELL_ACCEPTED,
            ['upsell', 'decline'] => self::EVENT_UPSELL_DECLINED,
            ['downsell', 'accept'] => self::EVENT_DOWNSELL_ACCEPTED,
            default => self::EVENT_DOWNSELL_DECLINED,
        };

        return $this->recordEvent([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead?->id,
            'payment_id' => $payment?->id,
            'event_name' => $eventName,
            'session_identifier' => $payment?->session_identifier ?: $this->sessionIdentifier($request),
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? (string) $request->userAgent() : null,
            'meta' => array_merge([
                'step_slug' => $step->slug,
                'step_type' => $step->type,
                'decision' => $decision,
                'amount' => $payment ? (float) $payment->amount : (float) ($step->price ?? 0),
            ], $meta),
        ]);
    }

    public function trackOrderDeliveryUpdate(Funnel $funnel, array $orderRow, array $meta = []): FunnelEvent
    {
        return $this->recordEvent([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => (int) ($orderRow['funnel_step_id'] ?? 0) ?: null,
            'lead_id' => (int) ($orderRow['lead_id'] ?? 0) ?: null,
            'payment_id' => (int) ($orderRow['payment_id'] ?? 0) ?: null,
            'event_name' => self::EVENT_ORDER_DELIVERY_UPDATED,
            'session_identifier' => trim((string) ($orderRow['session_identifier'] ?? '')) ?: null,
            'meta' => array_merge([
                'order_key' => (string) ($orderRow['order_key'] ?? ''),
                'recipient_email' => (string) ($orderRow['email'] ?? ''),
                'customer_name' => (string) ($orderRow['customer'] ?? ''),
                'delivery_status' => (string) ($meta['delivery_status'] ?? ''),
                'tracking_url' => (string) ($meta['tracking_url'] ?? ''),
                'tracking_number' => (string) ($meta['tracking_number'] ?? ''),
                'courier_name' => (string) ($meta['courier_name'] ?? ''),
                'custom_message' => (string) ($meta['custom_message'] ?? ''),
                'source' => 'manual_delivery_update',
            ], $meta),
        ]);
    }

    public function findPhysicalOrderRow(Funnel $funnel, string $orderKey, array $filters = []): ?array
    {
        $analytics = $this->analyticsForFunnel($funnel, $filters);

        return collect($analytics['physical_orders'] ?? [])
            ->first(fn (array $row) => trim((string) ($row['order_key'] ?? '')) === trim($orderKey));
    }

    public function hasRecentEvent(array $filters, int $seconds = 120): bool
    {
        return FunnelEvent::query()
            ->when(isset($filters['tenant_id']), fn (Builder $query) => $query->where('tenant_id', $filters['tenant_id']))
            ->when(isset($filters['funnel_id']), fn (Builder $query) => $query->where('funnel_id', $filters['funnel_id']))
            ->when(isset($filters['funnel_step_id']), fn (Builder $query) => $query->where('funnel_step_id', $filters['funnel_step_id']))
            ->when(isset($filters['lead_id']), fn (Builder $query) => $query->where('lead_id', $filters['lead_id']))
            ->when(isset($filters['payment_id']), fn (Builder $query) => $query->where('payment_id', $filters['payment_id']))
            ->when(isset($filters['event_name']), fn (Builder $query) => $query->where('event_name', $filters['event_name']))
            ->when(array_key_exists('session_identifier', $filters), function (Builder $query) use ($filters) {
                $value = $filters['session_identifier'] ?? null;
                if ($value === null || $value === '') {
                    $query->whereNull('session_identifier');
                    return;
                }

                $query->where('session_identifier', $value);
            })
            ->where('occurred_at', '>=', now()->subSeconds(max(1, $seconds)))
            ->exists();
    }

    public function eventsForFunnel(Funnel $funnel, array $filters = []): LengthAwarePaginator
    {
        $this->syncAbandonedCheckoutEvents($funnel);

        $perPage = max(1, min((int) ($filters['per_page'] ?? 50), 100));

        return $this->baseFunnelQuery($funnel, $filters)
            ->with(['step:id,funnel_id,title,slug,type', 'lead:id,name,email,phone', 'payment:id,amount,status,payment_date,coupon_code,subtotal_amount,discount_amount'])
            ->orderByDesc('occurred_at')
            ->paginate($perPage);
    }

    public function eventsCollectionForExport(Funnel $funnel, array $filters = []): Collection
    {
        $this->syncAbandonedCheckoutEvents($funnel);

        return $this->baseFunnelQuery($funnel, $filters)
            ->with(['step:id,funnel_id,title,slug,type', 'lead:id,name,email,phone', 'payment:id,amount,status,payment_date,coupon_code,subtotal_amount,discount_amount'])
            ->orderByDesc('occurred_at')
            ->get();
    }

    public function analyticsForFunnel(Funnel $funnel, array $filters = []): array
    {
        $this->syncAbandonedCheckoutEvents($funnel);

        $funnel->loadMissing('steps');
        $events = $this->baseFunnelQuery($funnel, $filters)
            ->with([
                'step:id,funnel_id,title,slug,type',
                'lead:id,name,email,phone',
                'payment:id,amount,status,payment_date,coupon_code,subtotal_amount,discount_amount',
            ])
            ->get();
        $activeSteps = $funnel->steps->where('is_active', true)->sortBy('position')->values();
        $stepMap = $activeSteps->keyBy('id');
        $stepViewEvents = $events->where('event_name', self::EVENT_STEP_VIEWED);
        $firstStepId = $activeSteps->first()?->id;
        $entryVisits = $firstStepId ? $this->uniqueVisitCount($stepViewEvents->where('funnel_step_id', $firstStepId)) : 0;
        $checkoutStarts = $events->where('event_name', self::EVENT_CHECKOUT_STARTED)->count();
        $paidCount = $events->where('event_name', self::EVENT_PAYMENT_PAID)->count();
        $optInCount = $events->where('event_name', self::EVENT_OPT_IN_SUBMITTED)->count();
        $upsellAccepted = $events->where('event_name', self::EVENT_UPSELL_ACCEPTED)->count();
        $upsellDeclined = $events->where('event_name', self::EVENT_UPSELL_DECLINED)->count();
        $downsellAccepted = $events->where('event_name', self::EVENT_DOWNSELL_ACCEPTED)->count();
        $downsellDeclined = $events->where('event_name', self::EVENT_DOWNSELL_DECLINED)->count();
        $abandonedCount = $events->where('event_name', self::EVENT_CHECKOUT_ABANDONED)->count();

        $payments = Payment::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id)
            ->where('status', 'paid')
            ->when($filters['from'] ?? null, fn (Builder $query, Carbon $from) => $query->where('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, Carbon $to) => $query->where('created_at', '<=', $to))
            ->get();
        $dailySeries = $this->dailySeries($events, $payments);

        $stepVisits = $activeSteps->map(function (FunnelStep $step) use ($stepViewEvents) {
            $visits = $this->uniqueVisitCount($stepViewEvents->where('funnel_step_id', $step->id));

            return [
                'step_id' => $step->id,
                'step_slug' => $step->slug,
                'step_title' => $step->title,
                'step_type' => $step->type,
                'visits' => $visits,
            ];
        })->values();

        $dropOff = $stepVisits->map(function (array $stepVisit, int $index) use ($stepVisits) {
            $nextVisits = (int) ($stepVisits->get($index + 1)['visits'] ?? 0);

            return [
                'step_id' => $stepVisit['step_id'],
                'step_slug' => $stepVisit['step_slug'],
                'step_title' => $stepVisit['step_title'],
                'from_visits' => $stepVisit['visits'],
                'to_next_visits' => $nextVisits,
                'drop_off' => max(0, $stepVisit['visits'] - $nextVisits),
                'drop_off_rate' => $this->rate(max(0, $stepVisit['visits'] - $nextVisits), $stepVisit['visits']),
            ];
        })->values();

        $offerCustomerSummary = $this->offerCustomerSummaryRows($events);
        $physicalOrderRows = collect($offerCustomerSummary)
            ->filter(function (array $row) use ($funnel) {
                $purpose = trim((string) ($row['funnel_purpose'] ?? $funnel->purpose ?? 'service'));
                return in_array($purpose, ['physical_product', 'hybrid'], true);
            })
            ->values()
            ->all();

        return [
            'filters' => [
                'from' => ($filters['from'] ?? null)?->toIso8601String(),
                'to' => ($filters['to'] ?? null)?->toIso8601String(),
                'step_id' => $filters['step_id'] ?? null,
                'event_name' => $filters['event_name'] ?? null,
            ],
            'events_supported' => [
                self::EVENT_STEP_VIEWED,
                self::EVENT_OPT_IN_SUBMITTED,
                self::EVENT_CHECKOUT_STARTED,
                self::EVENT_PAYMENT_PAID,
                self::EVENT_UPSELL_ACCEPTED,
                self::EVENT_UPSELL_DECLINED,
                self::EVENT_DOWNSELL_ACCEPTED,
                self::EVENT_DOWNSELL_DECLINED,
                self::EVENT_CHECKOUT_ABANDONED,
                self::EVENT_ORDER_DELIVERY_UPDATED,
            ],
            'totals' => [
                'entry_visits' => $entryVisits,
                'opt_in_count' => $optInCount,
                'checkout_start_count' => $checkoutStarts,
                'paid_count' => $paidCount,
                'abandoned_checkout_count' => $abandonedCount,
                'revenue' => (float) $payments->sum('amount'),
                'average_order_value' => $paidCount > 0 ? round(((float) $payments->sum('amount')) / $paidCount, 2) : 0.0,
                'revenue_per_visit' => $entryVisits > 0 ? round(((float) $payments->sum('amount')) / $entryVisits, 2) : 0.0,
            ],
            'rates' => [
                'opt_in_conversion_rate' => $this->rate($optInCount, $entryVisits),
                'checkout_conversion_rate' => $this->rate($checkoutStarts, $entryVisits),
                'paid_conversion_rate' => $this->rate($paidCount, $entryVisits),
                'upsell_acceptance_rate' => $this->rate($upsellAccepted, $upsellAccepted + $upsellDeclined),
                'downsell_acceptance_rate' => $this->rate($downsellAccepted, $downsellAccepted + $downsellDeclined),
                'abandoned_checkout_rate' => $this->rate($abandonedCount, $checkoutStarts),
            ],
            'offer_counts' => [
                'upsell_accepted' => $upsellAccepted,
                'upsell_declined' => $upsellDeclined,
                'downsell_accepted' => $downsellAccepted,
                'downsell_declined' => $downsellDeclined,
            ],
            'offer_activity' => [
                'upsell_accepted' => $this->offerActivityRows($events, self::EVENT_UPSELL_ACCEPTED),
                'upsell_declined' => $this->offerActivityRows($events, self::EVENT_UPSELL_DECLINED),
                'downsell_accepted' => $this->offerActivityRows($events, self::EVENT_DOWNSELL_ACCEPTED),
                'downsell_declined' => $this->offerActivityRows($events, self::EVENT_DOWNSELL_DECLINED),
            ],
            'offer_customer_summary' => $offerCustomerSummary,
            'physical_orders' => $physicalOrderRows,
            'physical_order_totals' => $this->physicalOrderTotals($physicalOrderRows),
            'physical_pending_orders' => array_values(array_filter($physicalOrderRows, fn (array $row) => ($row['order_status'] ?? '') === 'pending')),
            'physical_paid_orders' => array_values(array_filter($physicalOrderRows, fn (array $row) => ($row['order_status'] ?? '') === 'paid')),
            'physical_product_breakdown' => $this->physicalProductBreakdown($physicalOrderRows),
            'conversion_funnel' => [
                ['label' => 'Entry Visits', 'count' => $entryVisits],
                ['label' => 'Opt-ins', 'count' => $optInCount],
                ['label' => 'Checkout Starts', 'count' => $checkoutStarts],
                ['label' => 'Paid', 'count' => $paidCount],
            ],
            'daily_series' => $dailySeries,
            'step_visits' => $stepVisits,
            'drop_off' => $dropOff,
            'step_event_breakdown' => $events
                ->groupBy('funnel_step_id')
                ->map(function (Collection $items, $stepId) use ($stepMap) {
                    $step = $stepMap->get((int) $stepId);

                    return [
                        'step_id' => $step ? $step->id : null,
                        'step_slug' => $step?->slug,
                        'step_title' => $step?->title,
                        'step_type' => $step?->type,
                        'events' => $items->groupBy('event_name')->map->count()->sortKeys()->all(),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    private function offerActivityRows(Collection $events, string $eventName): array
    {
        return $events
            ->where('event_name', $eventName)
            ->sortByDesc(fn (FunnelEvent $event) => optional($event->occurred_at)?->getTimestamp() ?? 0)
            ->take(50)
            ->map(function (FunnelEvent $event) {
                $leadName = trim((string) ($event->lead->name ?? ''));
                $leadEmail = trim((string) ($event->lead->email ?? ''));
                $leadLabel = $leadName !== '' ? $leadName : ($leadEmail !== '' ? $leadEmail : 'Anonymous visitor');
                $paidBeforeOffer = $this->paidBeforeOffer($event);
                $selectedOffer = $this->selectedOfferLabel($event);

                return [
                    'event_name' => $event->event_name,
                    'occurred_at' => optional($event->occurred_at)?->toIso8601String(),
                    'occurred_at_label' => optional($event->occurred_at)?->format('M j, Y g:i A') ?? 'N/A',
                    'lead_name' => $leadName !== '' ? $leadName : null,
                    'lead_email' => $leadEmail !== '' ? $leadEmail : null,
                    'lead_label' => $leadLabel,
                    'selected_offer' => $selectedOffer,
                    'step_title' => $event->step->title ?? 'N/A',
                    'step_slug' => $event->step->slug ?? data_get($event->meta, 'step_slug'),
                    'amount' => $event->payment ? (float) $event->payment->amount : (float) data_get($event->meta, 'amount', 0),
                    'paid_before_offer' => $paidBeforeOffer,
                    'payment_status' => $event->payment->status ?? null,
                    'session_identifier' => $event->session_identifier,
                ];
            })
            ->values()
            ->all();
    }

    private function offerCustomerSummaryRows(Collection $events): array
    {
        $relevantEvents = $events->filter(function (FunnelEvent $event) {
            return in_array($event->event_name, [
                self::EVENT_CHECKOUT_STARTED,
                self::EVENT_PAYMENT_PAID,
                self::EVENT_CHECKOUT_ABANDONED,
                self::EVENT_ORDER_DELIVERY_UPDATED,
                self::EVENT_UPSELL_ACCEPTED,
                self::EVENT_UPSELL_DECLINED,
                self::EVENT_DOWNSELL_ACCEPTED,
                self::EVENT_DOWNSELL_DECLINED,
            ], true);
        });

        return $relevantEvents
            ->groupBy(fn (FunnelEvent $event) => $this->eventOrderGroupKey($event))
            ->map(function (Collection $group) {
                $ordered = $group->sortBy(fn (FunnelEvent $event) => optional($event->occurred_at)?->getTimestamp() ?? 0)->values();
                $latest = $ordered->last();
                $leadEvent = $ordered->first(fn (FunnelEvent $event) => $event->lead !== null);
                $leadName = trim((string) ($leadEvent?->lead?->name ?? ''));
                $leadEmail = trim((string) ($leadEvent?->lead?->email ?? ''));
                $leadPhone = trim((string) ($leadEvent?->lead?->phone ?? ''));
                $checkoutStarted = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_CHECKOUT_STARTED);

                $customerName = trim((string) (
                    data_get($checkoutStarted?->meta, 'customer.full_name')
                    ?: data_get($latest?->meta, 'customer.full_name')
                    ?: $leadName
                ));
                $customerEmail = trim((string) (
                    data_get($checkoutStarted?->meta, 'customer.email')
                    ?: data_get($latest?->meta, 'customer.email')
                    ?: $leadEmail
                ));
                $customerPhone = trim((string) (
                    data_get($checkoutStarted?->meta, 'customer.phone')
                    ?: data_get($latest?->meta, 'customer.phone')
                    ?: $leadPhone
                ));
                $firstName = trim((string) (
                    data_get($checkoutStarted?->meta, 'customer.first_name')
                    ?: data_get($latest?->meta, 'customer.first_name')
                ));
                $lastName = trim((string) (
                    data_get($checkoutStarted?->meta, 'customer.last_name')
                    ?: data_get($latest?->meta, 'customer.last_name')
                ));
                $street = trim((string) (
                    data_get($checkoutStarted?->meta, 'shipping.street')
                    ?: data_get($latest?->meta, 'shipping.street')
                ));
                $barangay = trim((string) (
                    data_get($checkoutStarted?->meta, 'shipping.barangay')
                    ?: data_get($latest?->meta, 'shipping.barangay')
                ));
                $cityMunicipality = trim((string) (
                    data_get($checkoutStarted?->meta, 'shipping.city_municipality')
                    ?: data_get($latest?->meta, 'shipping.city_municipality')
                ));
                $province = trim((string) (
                    data_get($checkoutStarted?->meta, 'shipping.province')
                    ?: data_get($latest?->meta, 'shipping.province')
                ));
                $postalCode = trim((string) (
                    data_get($checkoutStarted?->meta, 'shipping.postal_code')
                    ?: data_get($latest?->meta, 'shipping.postal_code')
                ));
                $notes = trim((string) (
                    data_get($checkoutStarted?->meta, 'shipping.notes')
                    ?: data_get($latest?->meta, 'shipping.notes')
                ));
                $deliveryAddress = trim((string) (
                    data_get($checkoutStarted?->meta, 'delivery_address')
                    ?: data_get($latest?->meta, 'delivery_address')
                ));
                if ($deliveryAddress === '') {
                    $deliveryAddress = collect([$street, $barangay, $cityMunicipality, $province, $postalCode])
                        ->filter(fn (?string $value) => $value !== null && $value !== '')
                        ->implode(', ');
                }
                $orderItemsRaw = data_get($checkoutStarted?->meta, 'order_items');
                if (! is_array($orderItemsRaw)) {
                    $orderItemsRaw = data_get($latest?->meta, 'order_items');
                }
                $orderItems = [];
                $orderQuantity = 0;
                if (is_array($orderItemsRaw)) {
                    foreach ($orderItemsRaw as $item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $itemName = trim((string) ($item['name'] ?? ''));
                        $itemPrice = trim((string) ($item['price'] ?? ''));
                        $itemRegularPrice = trim((string) ($item['regular_price'] ?? ($item['regularPrice'] ?? '')));
                        $itemBadge = trim((string) ($item['badge'] ?? ''));
                        $itemPeriod = trim((string) ($item['period'] ?? ''));
                        $quantity = max(1, (int) ($item['quantity'] ?? 1));
                        if ($itemName === '' && $itemPrice === '' && $itemRegularPrice === '' && $itemBadge === '') {
                            continue;
                        }
                        $orderItems[] = [
                            'name' => $itemName !== '' ? $itemName : 'Product',
                            'price' => $itemPrice !== '' ? $itemPrice : null,
                            'regular_price' => $itemRegularPrice !== '' ? $itemRegularPrice : null,
                            'badge' => $itemBadge !== '' ? $itemBadge : null,
                            'period' => $itemPeriod !== '' ? $itemPeriod : null,
                            'quantity' => $quantity,
                        ];
                        $orderQuantity += $quantity;
                    }
                }
                $orderItemsLabel = trim((string) (
                    data_get($checkoutStarted?->meta, 'order_items_label')
                    ?: data_get($latest?->meta, 'order_items_label')
                ));
                if ($orderItemsLabel === '' && $orderItems !== []) {
                    $orderItemsLabel = collect($orderItems)
                        ->map(fn (array $item) => ($item['name'] ?? 'Product') . ' x' . max(1, (int) ($item['quantity'] ?? 1)))
                        ->implode(', ');
                }
                $leadLabel = $customerName !== '' ? $customerName : ($customerEmail !== '' ? $customerEmail : 'Anonymous visitor');
                $orderKey = trim((string) (
                    data_get($latest?->meta, 'order_key')
                    ?: ($checkoutStarted ? $this->eventOrderGroupKey($checkoutStarted) : $this->eventOrderGroupKey($latest))
                ));

                $selectedOffer = $ordered
                    ->map(function (FunnelEvent $event) {
                        return trim((string) (data_get($event->meta, 'selected_pricing.plan')
                            ?? data_get($event->meta, 'pricing.plan')
                            ?? ''));
                    })
                    ->first(fn (?string $value) => $value !== null && $value !== '');

                $checkoutAmount = $checkoutStarted
                    ? (float) data_get($checkoutStarted->meta, 'amount', $checkoutStarted->payment?->amount ?? 0)
                    : 0.0;

                $upsellAccepted = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_UPSELL_ACCEPTED);
                $upsellDeclined = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_UPSELL_DECLINED);
                $downsellAccepted = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_DOWNSELL_ACCEPTED);
                $downsellDeclined = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_DOWNSELL_DECLINED);
                $paymentPaid = $ordered->last(fn (FunnelEvent $event) => $event->event_name === self::EVENT_PAYMENT_PAID);
                $abandoned = $ordered->last(fn (FunnelEvent $event) => $event->event_name === self::EVENT_CHECKOUT_ABANDONED);
                $paymentCarrier = $ordered->reverse()->first(function (FunnelEvent $event) {
                    return $event->payment !== null || trim((string) data_get($event->meta, 'payment_status')) !== '';
                });
                $paymentModel = $paymentPaid?->payment
                    ?? $checkoutStarted?->payment
                    ?? $paymentCarrier?->payment
                    ?? null;
                $paymentStatus = strtolower(trim((string) (
                    $paymentPaid?->payment?->status
                    ?? data_get($paymentPaid?->meta, 'payment_status')
                    ?? $paymentCarrier?->payment?->status
                    ?? data_get($paymentCarrier?->meta, 'payment_status')
                    ?? ''
                )));
                if ($paymentStatus === '') {
                    if ($paymentPaid) {
                        $paymentStatus = 'paid';
                    } elseif ($abandoned) {
                        $paymentStatus = 'abandoned';
                    } elseif ($checkoutStarted) {
                        $paymentStatus = 'pending';
                    }
                }
                $provider = trim((string) (
                    data_get($paymentPaid?->meta, 'provider')
                    ?? data_get($paymentCarrier?->meta, 'provider')
                    ?? $paymentPaid?->payment?->provider
                    ?? $paymentCarrier?->payment?->provider
                    ?? ''
                ));
                $orderStatus = $paymentPaid || $paymentStatus === 'paid'
                    ? 'paid'
                    : ($abandoned ? 'abandoned' : ($checkoutStarted ? 'pending' : 'unknown'));
                $deliveryUpdate = $ordered->last(fn (FunnelEvent $event) => $event->event_name === self::EVENT_ORDER_DELIVERY_UPDATED);
                $deliveryStatus = trim((string) (
                    data_get($deliveryUpdate?->meta, 'delivery_status')
                    ?: ($orderStatus === 'paid' ? 'paid' : ($orderStatus === 'pending' ? 'pending_payment' : $orderStatus))
                ));
                $trackingUrl = trim((string) (
                    data_get($deliveryUpdate?->meta, 'tracking_number')
                    ?: data_get($deliveryUpdate?->meta, 'tracking_url')
                ));
                $courierName = trim((string) data_get($deliveryUpdate?->meta, 'courier_name'));
                $deliveryMessage = trim((string) data_get($deliveryUpdate?->meta, 'custom_message'));
                $deliveryUpdatedAt = optional($deliveryUpdate?->occurred_at)?->toIso8601String();
                $deliveryUpdatedLabel = optional($deliveryUpdate?->occurred_at)?->format('M j, Y g:i A');
                $couponCode = trim((string) ($paymentModel?->coupon_code ?? ''));
                $subtotalAmount = is_numeric($paymentModel?->subtotal_amount) ? (float) $paymentModel->subtotal_amount : null;
                $discountAmount = is_numeric($paymentModel?->discount_amount) ? (float) $paymentModel->discount_amount : null;

                return [
                    'order_key' => $orderKey,
                    'session_identifier' => trim((string) ($latest?->session_identifier ?? $checkoutStarted?->session_identifier ?? '')) ?: null,
                    'lead_id' => $leadEvent?->lead?->id ?? $checkoutStarted?->lead_id ?? $latest?->lead_id,
                    'payment_id' => $paymentPaid?->payment_id ?? $checkoutStarted?->payment_id ?? $latest?->payment_id,
                    'funnel_step_id' => $checkoutStarted?->funnel_step_id ?? $latest?->funnel_step_id,
                    'customer' => $leadLabel,
                    'email' => $customerEmail !== '' ? $customerEmail : null,
                    'phone' => $customerPhone !== '' ? $customerPhone : null,
                    'first_name' => $firstName !== '' ? $firstName : null,
                    'last_name' => $lastName !== '' ? $lastName : null,
                    'selected_offer' => $selectedOffer !== '' ? $selectedOffer : null,
                    'order_items' => $orderItems,
                    'order_items_label' => $orderItemsLabel !== '' ? $orderItemsLabel : ($selectedOffer !== '' ? $selectedOffer : null),
                    'order_quantity' => $orderQuantity > 0 ? $orderQuantity : ($selectedOffer !== '' ? 1 : 0),
                    'checkout_amount' => round($checkoutAmount, 2),
                    'coupon_code' => $couponCode !== '' ? $couponCode : null,
                    'subtotal_amount' => $subtotalAmount,
                    'discount_amount' => $discountAmount,
                    'street' => $street !== '' ? $street : null,
                    'barangay' => $barangay !== '' ? $barangay : null,
                    'city_municipality' => $cityMunicipality !== '' ? $cityMunicipality : null,
                    'province' => $province !== '' ? $province : null,
                    'postal_code' => $postalCode !== '' ? $postalCode : null,
                    'notes' => $notes !== '' ? $notes : null,
                    'delivery_address' => $deliveryAddress !== '' ? $deliveryAddress : null,
                    'funnel_purpose' => trim((string) (
                        data_get($checkoutStarted?->meta, 'funnel_purpose')
                        ?: data_get($latest?->meta, 'funnel_purpose')
                        ?: 'service'
                    )),
                    'provider' => $provider !== '' ? $provider : null,
                    'payment_status' => $paymentStatus !== '' ? $paymentStatus : null,
                    'order_status' => $orderStatus,
                    'delivery_status' => $deliveryStatus !== '' ? $deliveryStatus : null,
                    'tracking_url' => $trackingUrl !== '' ? $trackingUrl : null,
                    'courier_name' => $courierName !== '' ? $courierName : null,
                    'delivery_message' => $deliveryMessage !== '' ? $deliveryMessage : null,
                    'delivery_updated_at' => $deliveryUpdatedAt,
                    'delivery_updated_label' => $deliveryUpdatedLabel ?: null,
                    'upsell_status' => $this->offerStatusLabel($upsellAccepted, $upsellDeclined),
                    'downsell_status' => $this->offerStatusLabel($downsellAccepted, $downsellDeclined),
                    'last_activity' => optional($latest?->occurred_at)?->format('M j, Y g:i A') ?? 'N/A',
                    'last_activity_at' => optional($latest?->occurred_at)?->toIso8601String(),
                ];
            })
            ->sortByDesc(fn (array $row) => strtotime((string) ($row['last_activity_at'] ?? '')) ?: 0)
            ->values()
            ->all();
    }

    private function eventOrderGroupKey(?FunnelEvent $event): string
    {
        if (! $event) {
            return 'event:0';
        }

        $metaOrderKey = trim((string) data_get($event->meta, 'order_key'));
        if ($metaOrderKey !== '') {
            return $metaOrderKey;
        }

        $sessionId = trim((string) ($event->session_identifier ?? ''));
        if ($sessionId !== '') {
            return 'session:'.$sessionId;
        }

        if ($event->lead_id) {
            return 'lead:'.$event->lead_id;
        }

        return 'event:'.$event->id;
    }

    private function physicalOrderTotals(array $rows): array
    {
        $collection = collect($rows);
        $paid = $collection->where('order_status', 'paid');
        $pending = $collection->where('order_status', 'pending');
        $abandoned = $collection->where('order_status', 'abandoned');
        $activeOrders = $collection->reject(fn (array $row) => ($row['order_status'] ?? '') === 'abandoned');
        $units = $activeOrders->sum(fn (array $row) => max(0, (int) ($row['order_quantity'] ?? 0)));

        return [
            'total_orders' => $activeOrders->count(),
            'paid_orders' => $paid->count(),
            'pending_orders' => $pending->count(),
            'abandoned_orders' => $abandoned->count(),
            'units_ordered' => (int) $units,
            'paid_revenue' => round((float) $paid->sum(fn (array $row) => (float) ($row['checkout_amount'] ?? 0)), 2),
        ];
    }

    private function physicalProductBreakdown(array $rows): array
    {
        $products = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $status = trim((string) ($row['order_status'] ?? ''));
            if ($status === 'abandoned') {
                continue;
            }

            foreach ((is_array($row['order_items'] ?? null) ? $row['order_items'] : []) as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $name = trim((string) ($item['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $key = mb_strtolower($name);
                if (! isset($products[$key])) {
                    $products[$key] = [
                        'name' => $name,
                        'units' => 0,
                        'orders' => 0,
                        'paid_units' => 0,
                    ];
                }

                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $products[$key]['units'] += $qty;
                $products[$key]['orders'] += 1;
                if ($status === 'paid') {
                    $products[$key]['paid_units'] += $qty;
                }
            }
        }

        return collect($products)
            ->sortByDesc(fn (array $item) => $item['units'])
            ->values()
            ->all();
    }

    private function offerStatusLabel(?FunnelEvent $acceptedEvent, ?FunnelEvent $declinedEvent): string
    {
        if ($acceptedEvent) {
            $amount = $acceptedEvent->payment ? (float) $acceptedEvent->payment->amount : (float) data_get($acceptedEvent->meta, 'amount', 0);

            return $amount > 0
                ? 'Accepted - PHP '.number_format($amount, 2)
                : 'Accepted';
        }

        if ($declinedEvent) {
            return 'Declined';
        }

        return 'Did not avail';
    }

    private function paidBeforeOffer(FunnelEvent $event): float
    {
        $query = Payment::query()
            ->where('funnel_id', $event->funnel_id)
            ->where('status', 'paid');

        $sessionId = trim((string) ($event->session_identifier ?? ''));
        if ($sessionId !== '') {
            $query->where('session_identifier', $sessionId);
        } elseif ($event->lead_id) {
            $query->where('lead_id', $event->lead_id);
        } else {
            return 0.0;
        }

        if ($event->occurred_at) {
            $query->where('created_at', '<=', $event->occurred_at);
        }

        if ($event->payment_id) {
            $query->whereKeyNot($event->payment_id);
        }

        return round((float) $query->sum('amount'), 2);
    }

    private function selectedOfferLabel(FunnelEvent $event): ?string
    {
        $directPlan = trim((string) (data_get($event->meta, 'selected_pricing.plan')
            ?? data_get($event->meta, 'pricing.plan')
            ?? ''));
        if ($directPlan !== '') {
            return $directPlan;
        }

        $query = FunnelEvent::query()
            ->where('funnel_id', $event->funnel_id)
            ->where('event_name', self::EVENT_CHECKOUT_STARTED);

        $sessionId = trim((string) ($event->session_identifier ?? ''));
        if ($sessionId !== '') {
            $query->where('session_identifier', $sessionId);
        } elseif ($event->lead_id) {
            $query->where('lead_id', $event->lead_id);
        } else {
            return null;
        }

        if ($event->occurred_at) {
            $query->where('occurred_at', '<=', $event->occurred_at);
        }

        /** @var FunnelEvent|null $checkoutEvent */
        $checkoutEvent = $query->latest('occurred_at')->first();
        if (! $checkoutEvent) {
            return null;
        }

        $checkoutPlan = trim((string) (data_get($checkoutEvent->meta, 'pricing.plan')
            ?? data_get($checkoutEvent->meta, 'selected_pricing.plan')
            ?? ''));

        return $checkoutPlan !== '' ? $checkoutPlan : null;
    }

    public function syncAbandonedCheckoutEvents(?Funnel $funnel = null): int
    {
        $startedEvents = FunnelEvent::query()
            ->where('event_name', self::EVENT_CHECKOUT_STARTED)
            ->when($funnel, fn (Builder $query) => $query->where('funnel_id', $funnel->id))
            ->where('occurred_at', '<=', now()->subSeconds($this->abandonedAfterSeconds()))
            ->get();

        $created = 0;

        foreach ($startedEvents as $startedEvent) {
            $sessionId = trim((string) ($startedEvent->session_identifier ?? ''));
            if ($sessionId === '') {
                continue;
            }

            $paidEventExists = FunnelEvent::query()
                ->where('funnel_id', $startedEvent->funnel_id)
                ->where('session_identifier', $sessionId)
                ->where('event_name', self::EVENT_PAYMENT_PAID)
                ->where('occurred_at', '>=', $startedEvent->occurred_at)
                ->exists();

            $paidPaymentExists = Payment::query()
                ->where('funnel_id', $startedEvent->funnel_id)
                ->where('session_identifier', $sessionId)
                ->where('status', 'paid')
                ->where('created_at', '>=', $startedEvent->occurred_at)
                ->exists();

            if ($paidEventExists || $paidPaymentExists) {
                continue;
            }

            $abandonedEvent = $this->recordEvent([
                'tenant_id' => $startedEvent->tenant_id,
                'funnel_id' => $startedEvent->funnel_id,
                'funnel_step_id' => $startedEvent->funnel_step_id,
                'lead_id' => $startedEvent->lead_id,
                'payment_id' => $startedEvent->payment_id,
                'event_name' => self::EVENT_CHECKOUT_ABANDONED,
                'session_identifier' => $sessionId,
                'occurred_at' => $startedEvent->occurred_at->copy()->addSeconds($this->abandonedAfterSeconds()),
                'meta' => [
                    'source_event_id' => $startedEvent->id,
                    'trigger' => 'checkout_started_without_paid_completion',
                ],
            ]);

            if ($abandonedEvent->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    public function normalizeDateFilters(array $input): array
    {
        return [
            'from' => $this->normalizeBoundary($input['from'] ?? null, true),
            'to' => $this->normalizeBoundary($input['to'] ?? null, false),
            'step_id' => isset($input['step_id']) && (int) $input['step_id'] > 0 ? (int) $input['step_id'] : null,
            'event_name' => $this->normalizeEventName($input['event_name'] ?? null),
            'per_page' => isset($input['per_page']) ? (int) $input['per_page'] : 50,
        ];
    }

    public function recordEvent(array $attributes): FunnelEvent
    {
        $payload = [
            'tenant_id' => (int) ($attributes['tenant_id'] ?? 0),
            'funnel_id' => (int) ($attributes['funnel_id'] ?? 0),
            'funnel_step_id' => isset($attributes['funnel_step_id']) ? (int) $attributes['funnel_step_id'] : null,
            'lead_id' => isset($attributes['lead_id']) ? (int) $attributes['lead_id'] : null,
            'payment_id' => isset($attributes['payment_id']) ? (int) $attributes['payment_id'] : null,
            'event_name' => (string) ($attributes['event_name'] ?? ''),
            'session_identifier' => $this->normalizeNullableString($attributes['session_identifier'] ?? null, 120),
            'ip_address' => $this->normalizeNullableString($attributes['ip_address'] ?? null, 45),
            'user_agent' => $this->normalizeNullableString($attributes['user_agent'] ?? null, 65535),
            'meta' => is_array($attributes['meta'] ?? null) ? $attributes['meta'] : null,
            'occurred_at' => (($attributes['occurred_at'] ?? null) instanceof Carbon) ? $attributes['occurred_at'] : now(),
        ];

        if ($payload['payment_id'] && $payload['event_name'] === self::EVENT_PAYMENT_PAID) {
            return FunnelEvent::query()->firstOrCreate(
                [
                    'payment_id' => $payload['payment_id'],
                    'event_name' => $payload['event_name'],
                ],
                $payload
            );
        }

        if (
            $payload['event_name'] === self::EVENT_CHECKOUT_ABANDONED
            && $payload['funnel_id']
            && $payload['session_identifier']
        ) {
            return FunnelEvent::query()->firstOrCreate(
                [
                    'funnel_id' => $payload['funnel_id'],
                    'event_name' => $payload['event_name'],
                    'session_identifier' => $payload['session_identifier'],
                ],
                $payload
            );
        }

        return FunnelEvent::query()->create($payload);
    }

    private function baseFunnelQuery(Funnel $funnel, array $filters = []): Builder
    {
        return FunnelEvent::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id)
            ->when($filters['step_id'] ?? null, fn (Builder $query, int $stepId) => $query->where('funnel_step_id', $stepId))
            ->when($filters['event_name'] ?? null, fn (Builder $query, string $eventName) => $query->where('event_name', $eventName))
            ->when($filters['from'] ?? null, fn (Builder $query, Carbon $from) => $query->where('occurred_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, Carbon $to) => $query->where('occurred_at', '<=', $to));
    }

    private function normalizeBoundary(mixed $value, bool $startOfDay): ?Carbon
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        try {
            $date = Carbon::parse($text);
        } catch (\Throwable) {
            return null;
        }

        return $startOfDay ? $date->startOfDay() : $date->endOfDay();
    }

    private function normalizeEventName(mixed $value): ?string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, 80);
    }

    private function normalizeNullableString(mixed $value, int $maxLength): ?string
    {
        $text = trim((string) $value);

        return $text !== '' ? mb_substr($text, 0, $maxLength) : null;
    }

    private function uniqueVisitCount(Collection $events): int
    {
        return $events
            ->map(fn (FunnelEvent $event) => $this->visitKey($event))
            ->filter()
            ->unique()
            ->count();
    }

    private function visitKey(FunnelEvent $event): string
    {
        $session = trim((string) ($event->session_identifier ?? ''));

        return $session !== '' ? $session : ('event:' . $event->id);
    }

    private function rate(int|float $numerator, int|float $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(((float) $numerator / (float) $denominator) * 100, 2);
    }

    private function abandonedAfterSeconds(): int
    {
        return max(60, (int) config('funnels.checkout_abandoned_after_seconds', 86400));
    }

    private function dailySeries(Collection $events, Collection $payments): array
    {
        $days = collect();

        foreach ($events as $event) {
            $days->push(optional($event->occurred_at)->format('Y-m-d'));
        }

        foreach ($payments as $payment) {
            $days->push(optional($payment->created_at)->format('Y-m-d'));
        }

        $labels = $days
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return $labels->map(function (string $date) use ($events, $payments) {
            return [
                'date' => $date,
                'entry_visits' => $events->where('event_name', self::EVENT_STEP_VIEWED)->filter(fn (FunnelEvent $event) => optional($event->occurred_at)->format('Y-m-d') === $date)->count(),
                'opt_ins' => $events->where('event_name', self::EVENT_OPT_IN_SUBMITTED)->filter(fn (FunnelEvent $event) => optional($event->occurred_at)->format('Y-m-d') === $date)->count(),
                'checkout_starts' => $events->where('event_name', self::EVENT_CHECKOUT_STARTED)->filter(fn (FunnelEvent $event) => optional($event->occurred_at)->format('Y-m-d') === $date)->count(),
                'paid' => $events->where('event_name', self::EVENT_PAYMENT_PAID)->filter(fn (FunnelEvent $event) => optional($event->occurred_at)->format('Y-m-d') === $date)->count(),
                'revenue' => round((float) $payments->filter(fn (Payment $payment) => optional($payment->created_at)->format('Y-m-d') === $date)->sum('amount'), 2),
            ];
        })->all();
    }
}
