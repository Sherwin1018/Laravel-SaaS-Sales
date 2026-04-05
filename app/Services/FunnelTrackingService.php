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
            ], $meta),
        ]);
    }

    public function trackPaymentPaid(Payment $payment, array $meta = []): ?FunnelEvent
    {
        if ((int) ($payment->funnel_id ?? 0) <= 0) {
            return null;
        }

        return $this->recordEvent([
            'tenant_id' => $payment->tenant_id,
            'funnel_id' => $payment->funnel_id,
            'funnel_step_id' => $payment->funnel_step_id,
            'lead_id' => $payment->lead_id,
            'payment_id' => $payment->id,
            'event_name' => self::EVENT_PAYMENT_PAID,
            'session_identifier' => $payment->session_identifier,
            'meta' => array_merge([
                'amount' => (float) $payment->amount,
                'provider' => $payment->provider,
                'provider_reference' => $payment->provider_reference,
                'payment_method' => $payment->payment_method,
            ], $meta),
        ]);
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
            ->with(['step:id,funnel_id,title,slug,type', 'lead:id,name,email', 'payment:id,amount,status,payment_date'])
            ->orderByDesc('occurred_at')
            ->paginate($perPage);
    }

    public function eventsCollectionForExport(Funnel $funnel, array $filters = []): Collection
    {
        $this->syncAbandonedCheckoutEvents($funnel);

        return $this->baseFunnelQuery($funnel, $filters)
            ->with(['step:id,funnel_id,title,slug,type', 'lead:id,name,email', 'payment:id,amount,status,payment_date'])
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
                'lead:id,name,email',
                'payment:id,amount,status,payment_date',
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
            'offer_customer_summary' => $this->offerCustomerSummaryRows($events),
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
                self::EVENT_UPSELL_ACCEPTED,
                self::EVENT_UPSELL_DECLINED,
                self::EVENT_DOWNSELL_ACCEPTED,
                self::EVENT_DOWNSELL_DECLINED,
            ], true);
        });

        return $relevantEvents
            ->groupBy(function (FunnelEvent $event) {
                $sessionId = trim((string) ($event->session_identifier ?? ''));
                if ($sessionId !== '') {
                    return 'session:'.$sessionId;
                }

                if ($event->lead_id) {
                    return 'lead:'.$event->lead_id;
                }

                return 'event:'.$event->id;
            })
            ->map(function (Collection $group) {
                $ordered = $group->sortBy(fn (FunnelEvent $event) => optional($event->occurred_at)?->getTimestamp() ?? 0)->values();
                $latest = $ordered->last();
                $leadEvent = $ordered->first(fn (FunnelEvent $event) => $event->lead !== null);
                $leadName = trim((string) ($leadEvent?->lead?->name ?? ''));
                $leadEmail = trim((string) ($leadEvent?->lead?->email ?? ''));
                $leadLabel = $leadName !== '' ? $leadName : ($leadEmail !== '' ? $leadEmail : 'Anonymous visitor');

                $selectedOffer = $ordered
                    ->map(function (FunnelEvent $event) {
                        return trim((string) (data_get($event->meta, 'selected_pricing.plan')
                            ?? data_get($event->meta, 'pricing.plan')
                            ?? ''));
                    })
                    ->first(fn (?string $value) => $value !== null && $value !== '');

                $checkoutStarted = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_CHECKOUT_STARTED);
                $checkoutAmount = $checkoutStarted
                    ? (float) data_get($checkoutStarted->meta, 'amount', $checkoutStarted->payment?->amount ?? 0)
                    : 0.0;

                $upsellAccepted = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_UPSELL_ACCEPTED);
                $upsellDeclined = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_UPSELL_DECLINED);
                $downsellAccepted = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_DOWNSELL_ACCEPTED);
                $downsellDeclined = $ordered->first(fn (FunnelEvent $event) => $event->event_name === self::EVENT_DOWNSELL_DECLINED);

                return [
                    'customer' => $leadLabel,
                    'email' => $leadEmail !== '' ? $leadEmail : null,
                    'selected_offer' => $selectedOffer !== '' ? $selectedOffer : null,
                    'checkout_amount' => round($checkoutAmount, 2),
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
