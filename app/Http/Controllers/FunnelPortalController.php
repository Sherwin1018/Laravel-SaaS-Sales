<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use App\Services\PayMongoCheckoutService;
use App\Services\FunnelTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class FunnelPortalController extends Controller
{
    public function show(Request $request, FunnelTrackingService $tracking, string $funnelSlug, ?string $stepSlug = null)
    {
        $funnel = Funnel::with(['tenant', 'steps'])->where('slug', $funnelSlug)->where('status', 'published')->firstOrFail();
        $steps = $funnel->steps->where('is_active', true)->sortBy('position')->values();
        abort_if($steps->isEmpty(), 404);

        $step = $stepSlug
            ? $steps->firstWhere('slug', $stepSlug)
            : $steps->first();

        abort_if(! $step, 404);

        $isFirstStep = (int) $steps->first()->id === (int) $step->id;
        $selectedPricing = $this->syncSelectedPricingFromRequest($request, $funnel, $steps, $step, $isFirstStep);
        $tracking->trackStepViewed($funnel, $step, $request, [
            'is_first_step' => $isFirstStep,
        ]);

        return view('funnels.portal.step', [
            'funnel' => $funnel,
            'step' => $step,
            'nextStep' => $this->nextStep($steps, $step->id),
            'allSteps' => $steps,
            'isFirstStep' => $isFirstStep,
            'selectedPricing' => $selectedPricing,
        ]);
    }

    public function optIn(Request $request, FunnelTrackingService $tracking, string $funnelSlug, string $stepSlug)
    {
        [$funnel, $steps, $step] = $this->resolveStepContext($funnelSlug, $stepSlug, 'opt_in');

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:150',
            'last_name' => 'nullable|string|max:150',
            'name' => 'nullable|string|max:150',
            'email' => 'required|email|max:150',
            'phone_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'province' => 'nullable|string|max:150',
            'city_municipality' => 'nullable|string|max:150',
            'barangay' => 'nullable|string|max:150',
            'street' => 'nullable|string|max:180',
            'website' => 'nullable|string|size:0',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email.',
        ]);

        $name = trim(
            ($validated['name'] ?? '')
            ?: trim(($validated['first_name'] ?? '').' '.($validated['last_name'] ?? ''))
        );
        $phone = $validated['phone_number'] ?? $validated['phone'] ?? '';
        if ($phone !== '' && ! preg_match('/^09\d{9}$/', $phone)) {
            return redirect()->back()->withErrors(['phone_number' => 'Phone must be a valid Philippine mobile number (09XXXXXXXXX).'])->withInput();
        }

        $lead = Lead::firstOrNew([
            'tenant_id' => $funnel->tenant_id,
            'email' => $validated['email'],
        ]);

        if (! $lead->exists) {
            $lead->assigned_to = null;
            $lead->status = 'new';
            $lead->score = 0;
        }

        $lead->name = $name !== '' ? $name : $lead->name;
        $lead->phone = $phone !== '' ? $phone : ($lead->phone ?? '');
        $lead->tags = $this->mergeTags(
            $lead->tags ?? [],
            $funnel->default_tags ?? [],
            $step->step_tags ?? []
        );
        $lead->save();

        $currentLeadId = $this->currentLeadId($funnel->id);
        $currentLead = $currentLeadId ? Lead::query()->find($currentLeadId) : null;
        $submittedEmail = mb_strtolower(trim((string) $lead->email));
        $currentEmail = mb_strtolower(trim((string) ($currentLead->email ?? '')));
        $isNewJourney = $currentLead && $currentEmail !== '' && $submittedEmail !== '' && $currentEmail !== $submittedEmail;

        if ($isNewJourney) {
            session()->forget($this->leadSessionKey($funnel->id));
            session()->forget($this->selectedPricingSessionKey($funnel->id));
            $request->session()->regenerate();
        }

        $sessionIdentifier = $tracking->sessionIdentifier($request);

        $isRepeatSubmission = $tracking->hasRecentEvent([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'event_name' => FunnelTrackingService::EVENT_OPT_IN_SUBMITTED,
            'session_identifier' => $sessionIdentifier,
        ], 90);

        if (! $isRepeatSubmission) {
            $lead->increment('score', 20);
            $lead->activities()->create([
                'activity_type' => 'Scoring',
                'notes' => 'Form Submitted (+20 points)',
            ]);

            $tracking->trackOptInSubmitted($funnel, $step, $lead, $request, [
                'submitted_fields' => collect(array_keys($validated))
                    ->reject(fn (string $field) => $field === 'website')
                    ->values()
                    ->all(),
            ]);
        }

        session()->put($this->leadSessionKey($funnel->id), $lead->id);

        $next = $this->nextStep($steps, $step->id);
        abort_if(! $next, 422, 'No next step configured.');
        return redirect()->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $next->slug]);
    }

    public function checkout(
        Request $request,
        PayMongoCheckoutService $payMongo,
        FunnelTrackingService $tracking,
        string $funnelSlug,
        string $stepSlug
    )
    {
        [$funnel, $steps, $step] = $this->resolveStepContext($funnelSlug, $stepSlug, 'checkout');
        $sessionIdentifier = $tracking->sessionIdentifier($request);

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'checkout_pricing_id' => 'nullable|string|max:120',
            'checkout_pricing_source_step' => 'nullable|string|max:120',
            'checkout_pricing_plan' => 'nullable|string|max:200',
            'checkout_pricing_price' => 'nullable|string|max:120',
            'checkout_pricing_regular_price' => 'nullable|string|max:120',
            'checkout_pricing_period' => 'nullable|string|max:60',
            'checkout_pricing_subtitle' => 'nullable|string|max:300',
            'checkout_pricing_badge' => 'nullable|string|max:80',
            'checkout_pricing_features' => 'nullable|string|max:5000',
            'website' => 'nullable|string|size:0',
        ]);

        $submittedAmount = array_key_exists('amount', $validated) ? (float) $validated['amount'] : null;
        $selectedPricing = $this->currentSelectedPricing($funnel->id);
        $postedPricing = $this->pricingSelectionFromCheckoutRequest($validated);
        if ($postedPricing !== null) {
            $selectedPricing = $this->mergePricingSelections($selectedPricing, $postedPricing);
            if ($selectedPricing !== null) {
                session()->put($this->selectedPricingSessionKey($funnel->id), $selectedPricing);
            }
        }
        $selectedAmount = $this->amountFromSelectedPricing($selectedPricing);
        $layoutAmount = $this->primaryPricingAmountFromLayout($step);
        $stepAmount = (float) ($step->price ?? 0);
        $preferredAmount = ($selectedAmount !== null && $selectedAmount > 0)
            ? $selectedAmount
            : (($layoutAmount !== null && $layoutAmount > 0) ? $layoutAmount : null);
        if ($submittedAmount !== null && $submittedAmount > 0) {
            $amount = $submittedAmount;
            if ($preferredAmount !== null && $stepAmount > 0) {
                $usesStaleDefault = abs($submittedAmount - $stepAmount) < 0.00001 && abs($preferredAmount - $stepAmount) > 0.00001;
                if ($usesStaleDefault) {
                    $amount = $preferredAmount;
                }
            }
        } else {
            $amount = $preferredAmount ?? $stepAmount;
        }
        abort_if($amount <= 0, 422, 'Checkout amount is not configured.');

        $recentPayment = Payment::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id)
            ->where('funnel_step_id', $step->id)
            ->where('session_identifier', $sessionIdentifier)
            ->where('amount', $amount)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->latest('id')
            ->first();

        if ($recentPayment) {
            if ($recentPayment->status === 'paid') {
                $tracking->trackPaymentPaid($recentPayment, ['source' => 'checkout_repeat_guard']);

                return $this->redirectAfterPaidCheckout($funnel, $steps, $step);
            }

            return redirect()
                ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
                ->with('error', 'Checkout is already in progress for this session. Please finish the current payment before trying again.');
        }

        if ($payMongo->isConfigured()) {
            return $this->checkoutWithPayMongo($payMongo, $tracking, $request, $funnel, $steps, $step, $amount, $selectedPricing);
        }

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $this->currentLeadId($funnel->id),
            'amount' => $amount,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'session_identifier' => $sessionIdentifier,
        ]);

        $tracking->trackCheckoutStarted($funnel, $step, $payment, $request, $selectedPricing, ['source' => 'direct_checkout']);
        $tracking->trackPaymentPaid($payment, ['source' => 'direct_checkout']);

        return $this->redirectAfterPaidCheckout($funnel, $steps, $step);
    }

    public function paymongoReturn(
        PayMongoCheckoutService $payMongo,
        FunnelTrackingService $tracking,
        string $funnelSlug,
        string $stepSlug,
        int $payment
    )
    {
        $funnel = Funnel::with('steps')->where('slug', $funnelSlug)->where('status', 'published')->firstOrFail();
        $steps = $funnel->steps->where('is_active', true)->sortBy('position')->values();
        $step = $steps->firstWhere('slug', $stepSlug);
        abort_if(! $step || $step->type !== 'checkout', 404);

        $record = Payment::query()->findOrFail($payment);
        abort_unless($record->provider === 'paymongo', 403);
        abort_unless((int) $record->tenant_id === (int) $funnel->tenant_id, 403);

        if ($record->status === 'paid') {
            $tracking->trackPaymentPaid($record, ['source' => 'paymongo_return_cached']);
            return $this->redirectAfterPaidCheckout($funnel, $steps, $step);
        }

        $sessionId = $record->provider_reference;
        abort_if(! is_string($sessionId) || $sessionId === '', 422);

        $data = $payMongo->retrieveCheckoutSession($sessionId);
        if ($data === null) {
            return redirect()
                ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
                ->with('error', 'We could not verify your payment yet. If you completed checkout, wait a moment or contact support.');
        }

        $paid = false;
        $method = null;
        $payments = data_get($data, 'attributes.payments');
        if (is_array($payments)) {
            foreach ($payments as $p) {
                if (! is_array($p)) {
                    continue;
                }
                $status = data_get($p, 'attributes.status');
                if ($status === 'paid') {
                    $paid = true;
                    $t = data_get($p, 'attributes.source.type');
                    $method = is_string($t) ? $t : null;
                    break;
                }
            }
        }

        if ($paid) {
            $record->update([
                'status' => 'paid',
                'payment_method' => $method ?? $record->payment_method,
                'payment_date' => now()->toDateString(),
            ]);
            $tracking->trackPaymentPaid($record->fresh(), ['source' => 'paymongo_return']);

            return $this->redirectAfterPaidCheckout($funnel, $steps, $step);
        }

        return redirect()
            ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
            ->with('error', 'Payment was not completed. You can try again.');
    }

    private function checkoutWithPayMongo(
        PayMongoCheckoutService $payMongo,
        FunnelTrackingService $tracking,
        Request $request,
        Funnel $funnel,
        $steps,
        FunnelStep $step,
        float $amount,
        ?array $selectedPricing = null,
    ): \Illuminate\Http\RedirectResponse {
        $centavos = (int) round($amount * 100);
        abort_if($centavos < 1, 422, 'Checkout amount is not configured.');

        $leadId = $this->currentLeadId($funnel->id);
        $lead = $leadId ? Lead::query()->find($leadId) : null;

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $leadId,
            'amount' => $amount,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
            'session_identifier' => $tracking->sessionIdentifier($request),
        ]);

        $tracking->trackCheckoutStarted($funnel, $step, $payment, $request, $selectedPricing, ['source' => 'paymongo']);

        $successUrl = URL::signedRoute('funnels.portal.paymongo.return', [
            'funnelSlug' => $funnel->slug,
            'stepSlug' => $step->slug,
            'payment' => $payment->id,
        ], now()->addHours(48));

        $cancelUrl = route('funnels.portal.step', [
            'funnelSlug' => $funnel->slug,
            'stepSlug' => $step->slug,
        ]);

        if (! is_array($selectedPricing)) {
            $selectedPricing = $this->currentSelectedPricing($funnel->id);
        }
        $selectedPlan = trim((string) ($selectedPricing['plan'] ?? ''));
        $lineName = $selectedPlan !== ''
            ? $selectedPlan
            : (trim((string) ($step->title ?? '')) !== '' ? (string) $step->title : (string) $funnel->name);
        $description = trim((string) $funnel->name).' — '.$lineName;

        $billing = null;
        if ($lead) {
            $billing = array_filter([
                'name' => trim((string) ($lead->name ?? '')) !== '' ? (string) $lead->name : null,
                'email' => filter_var($lead->email, FILTER_VALIDATE_EMAIL) ? (string) $lead->email : null,
            ]);
            if ($billing === []) {
                $billing = null;
            }
        }

        $session = $payMongo->createCheckoutSession(
            $centavos,
            $lineName,
            $description,
            $successUrl,
            $cancelUrl,
            [
                'payment_id' => (string) $payment->id,
                'funnel_slug' => $funnel->slug,
                'step_slug' => $step->slug,
            ],
            $billing,
        );

        if ($session === null) {
            $payment->delete();

            return redirect()
                ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
                ->with('error', 'Unable to start PayMongo checkout. Check your keys and payment method settings, then try again.');
        }

        $payment->update(['provider_reference' => $session['id']]);

        return redirect()->away($session['checkout_url']);
    }

    private function redirectAfterPaidCheckout(Funnel $funnel, $steps, FunnelStep $step): \Illuminate\Http\RedirectResponse
    {
        $next = $this->nextStep($steps, $step->id);
        if (! $next) {
            return redirect()->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]);
        }

        return redirect()->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $next->slug]);
    }

    public function offer(Request $request, FunnelTrackingService $tracking, string $funnelSlug, string $stepSlug)
    {
        [$funnel, $steps, $step] = $this->resolveStepContext($funnelSlug, $stepSlug, null);
        abort_unless(in_array($step->type, ['upsell', 'downsell'], true), 422, 'Invalid offer step.');

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['accept', 'decline'])],
            'website' => 'nullable|string|size:0',
            'checkout_pricing_id' => 'nullable|string|max:120',
            'checkout_pricing_source_step' => 'nullable|string|max:120',
            'checkout_pricing_plan' => 'nullable|string|max:200',
            'checkout_pricing_price' => 'nullable|string|max:120',
            'checkout_pricing_regular_price' => 'nullable|string|max:120',
            'checkout_pricing_period' => 'nullable|string|max:60',
            'checkout_pricing_subtitle' => 'nullable|string|max:300',
            'checkout_pricing_badge' => 'nullable|string|max:80',
            'checkout_pricing_features' => 'nullable|string|max:4000',
        ]);

        $requestSelection = $this->pricingSelectionFromCheckoutRequest($validated);
        $selectedPricing = $this->mergePricingSelections($this->currentSelectedPricing($funnel->id), $requestSelection);
        $offerAmount = $this->amountFromSelectedPricing($selectedPricing)
            ?? ((float) ($step->price ?? 0) > 0 ? (float) $step->price : null)
            ?? $this->primaryPricingAmountFromLayout($step)
            ?? 0.0;

        $accept = $validated['decision'] === 'accept';
        $recentDecision = $tracking->hasRecentEvent([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'event_name' => $step->type === 'upsell'
                ? ($accept ? FunnelTrackingService::EVENT_UPSELL_ACCEPTED : FunnelTrackingService::EVENT_UPSELL_DECLINED)
                : ($accept ? FunnelTrackingService::EVENT_DOWNSELL_ACCEPTED : FunnelTrackingService::EVENT_DOWNSELL_DECLINED),
            'session_identifier' => $tracking->sessionIdentifier($request),
        ], 90);

        $payment = null;
        if (! $recentDecision && $accept && $offerAmount > 0) {
            $payment = Payment::create([
                'tenant_id' => $funnel->tenant_id,
                'funnel_id' => $funnel->id,
                'funnel_step_id' => $step->id,
                'lead_id' => $this->currentLeadId($funnel->id),
                'amount' => $offerAmount,
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
                'session_identifier' => $tracking->sessionIdentifier($request),
            ]);
            $tracking->trackPaymentPaid($payment, ['source' => 'offer_accept']);
        }

        if (! $recentDecision) {
            $lead = null;
            $leadId = $this->currentLeadId($funnel->id);
            if ($leadId) {
                $lead = Lead::query()->find($leadId);
            }

            $tracking->trackOfferDecision($funnel, $step, $validated['decision'], $lead, $payment, $request, [
                'amount' => $offerAmount,
                'selected_pricing' => $selectedPricing,
            ]);
        }

        $ordered = $steps->values();
        $currentIndex = $ordered->search(fn ($item) => (int) $item->id === (int) $step->id);
        $target = null;

        if ($currentIndex !== false) {
            $immediateNext = $ordered->get($currentIndex + 1);
            if ($step->type === 'upsell' && ! $accept && $immediateNext && $immediateNext->type === 'downsell') {
                $target = $immediateNext;
            } elseif ($step->type === 'upsell' && $accept && $immediateNext && $immediateNext->type === 'downsell') {
                $target = $ordered->get($currentIndex + 2);
            } else {
                $target = $immediateNext;
            }
        }

        if (! $target) {
            $target = $ordered->last();
        }

        return redirect()->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $target->slug]);
    }

    private function resolveStepContext(string $funnelSlug, string $stepSlug, ?string $expectedType): array
    {
        $funnel = Funnel::with('steps')->where('slug', $funnelSlug)->where('status', 'published')->firstOrFail();
        $steps = $funnel->steps->where('is_active', true)->sortBy('position')->values();
        $step = $steps->firstWhere('slug', $stepSlug);
        abort_if(! $step, 404);

        if ($expectedType !== null) {
            abort_unless($step->type === $expectedType, 422, 'Invalid funnel step type.');
        }

        return [$funnel, $steps, $step];
    }

    private function nextStep($steps, int $currentStepId): ?FunnelStep
    {
        $ordered = $steps->values();
        $index = $ordered->search(fn ($step) => (int) $step->id === (int) $currentStepId);
        if ($index === false) {
            return null;
        }

        return $ordered->get($index + 1);
    }

    private function syncSelectedPricingFromRequest(Request $request, Funnel $funnel, $steps, FunnelStep $step, bool $isFirstStep): ?array
    {
        $sourceStepSlug = strtolower(trim((string) $request->query('offer_step', '')));
        $pricingId = trim((string) $request->query('offer_pricing', ''));

        if ($sourceStepSlug !== '' && $pricingId !== '') {
            $sourceStep = $steps->first(function ($candidate) use ($sourceStepSlug) {
                return strtolower(trim((string) ($candidate->slug ?? ''))) === $sourceStepSlug;
            });

            if ($sourceStep instanceof FunnelStep) {
                $selection = $this->pricingSelectionFromStep($sourceStep, $pricingId);
                if ($selection !== null) {
                    session()->put($this->selectedPricingSessionKey($funnel->id), $selection);

                    return $selection;
                }
            }

            $snapshotSelection = $this->pricingSelectionSnapshotFromRequest($request, $pricingId, $sourceStepSlug);
            if ($snapshotSelection !== null) {
                session()->put($this->selectedPricingSessionKey($funnel->id), $snapshotSelection);

                return $snapshotSelection;
            }
        }

        if ($isFirstStep && $sourceStepSlug === '' && $pricingId === '') {
            session()->forget($this->selectedPricingSessionKey($funnel->id));

            return null;
        }

        return $this->currentSelectedPricing($funnel->id);
    }

    private function pricingSelectionFromStep(FunnelStep $sourceStep, string $pricingId): ?array
    {
        $layout = $sourceStep->layout_json;
        if (! is_array($layout)) {
            return null;
        }

        $findInElements = function (array $elements) use (&$findInElements, $pricingId, $sourceStep): ?array {
            foreach ($elements as $element) {
                if (! is_array($element)) {
                    continue;
                }

                if (
                    strtolower(trim((string) ($element['type'] ?? ''))) === 'pricing'
                    && trim((string) ($element['id'] ?? '')) === $pricingId
                ) {
                    $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                    $features = [];
                    foreach ((is_array($settings['features'] ?? null) ? $settings['features'] : []) as $feature) {
                        if (! is_scalar($feature)) {
                            continue;
                        }
                        $featureText = mb_substr(trim((string) $feature), 0, 200);
                        if ($featureText !== '') {
                            $features[] = $featureText;
                        }
                    }

                    return [
                        'pricingId' => $pricingId,
                        'sourceStepSlug' => (string) $sourceStep->slug,
                        'plan' => mb_substr(trim((string) ($settings['plan'] ?? '')), 0, 200),
                        'price' => $this->normalizeMoneyDisplay($settings['price'] ?? ''),
                        'regularPrice' => $this->normalizeMoneyDisplay($settings['regularPrice'] ?? ''),
                        'period' => mb_substr(trim((string) ($settings['period'] ?? '')), 0, 60),
                        'subtitle' => mb_substr(trim((string) ($settings['subtitle'] ?? '')), 0, 300),
                        'badge' => mb_substr(trim((string) ($settings['badge'] ?? '')), 0, 80),
                        'features' => $features,
                    ];
                }

                $slides = is_array(data_get($element, 'settings.slides')) ? data_get($element, 'settings.slides') : [];
                foreach ($slides as $slide) {
                    $nested = $findInElements(is_array($slide['elements'] ?? null) ? $slide['elements'] : []);
                    if ($nested !== null) {
                        return $nested;
                    }
                }
            }

            return null;
        };

        $findInSections = function (array $sections) use ($findInElements): ?array {
            foreach ($sections as $section) {
                if (! is_array($section)) {
                    continue;
                }

                $sectionSelection = $findInElements(is_array($section['elements'] ?? null) ? $section['elements'] : []);
                if ($sectionSelection !== null) {
                    return $sectionSelection;
                }

                foreach ((is_array($section['rows'] ?? null) ? $section['rows'] : []) as $row) {
                    foreach ((is_array($row['columns'] ?? null) ? $row['columns'] : []) as $column) {
                        $columnSelection = $findInElements(is_array($column['elements'] ?? null) ? $column['elements'] : []);
                        if ($columnSelection !== null) {
                            return $columnSelection;
                        }
                    }
                }
            }

            return null;
        };

        $rootSelection = $findInSections(is_array($layout['root'] ?? null) ? $layout['root'] : []);
        if ($rootSelection !== null) {
            return $rootSelection;
        }

        return $findInSections(is_array($layout['sections'] ?? null) ? $layout['sections'] : []);
    }

    private function pricingSelectionSnapshotFromRequest(Request $request, string $pricingId = '', string $sourceStepSlug = ''): ?array
    {
        $pricingId = trim($pricingId) !== '' ? trim($pricingId) : trim((string) $request->query('offer_pricing', ''));
        $sourceStepSlug = trim($sourceStepSlug) !== '' ? strtolower(trim($sourceStepSlug)) : strtolower(trim((string) $request->query('offer_step', '')));
        $plan = mb_substr(trim((string) $request->query('offer_plan', '')), 0, 200);
        $price = $this->normalizeMoneyDisplay($request->query('offer_price', ''));
        $regularPrice = $this->normalizeMoneyDisplay($request->query('offer_regular_price', ''));
        $period = mb_substr(trim((string) $request->query('offer_period', '')), 0, 60);
        $subtitle = mb_substr(trim((string) $request->query('offer_subtitle', '')), 0, 300);
        $badge = mb_substr(trim((string) $request->query('offer_badge', '')), 0, 80);
        $features = [];
        $rawFeatures = trim((string) $request->query('offer_features', ''));
        if ($rawFeatures !== '') {
            $decoded = json_decode($rawFeatures, true);
            if (is_array($decoded)) {
                foreach ($decoded as $feature) {
                    if (! is_scalar($feature)) {
                        continue;
                    }
                    $featureText = mb_substr(trim((string) $feature), 0, 200);
                    if ($featureText !== '') {
                        $features[] = $featureText;
                    }
                }
            }
        }

        if (
            $pricingId === ''
            && $sourceStepSlug === ''
            && $plan === ''
            && $price === ''
            && $regularPrice === ''
            && $period === ''
            && $subtitle === ''
            && $badge === ''
            && $features === []
        ) {
            return null;
        }

        return [
            'pricingId' => $pricingId,
            'sourceStepSlug' => $sourceStepSlug,
            'plan' => $plan,
            'price' => $price,
            'regularPrice' => $regularPrice,
            'period' => $period,
            'subtitle' => $subtitle,
            'badge' => $badge,
            'features' => $features,
        ];
    }

    private function leadSessionKey(int $funnelId): string
    {
        return "funnel_lead_{$funnelId}";
    }

    private function selectedPricingSessionKey(int $funnelId): string
    {
        return "funnel_selected_pricing_{$funnelId}";
    }

    private function currentLeadId(int $funnelId): ?int
    {
        $leadId = session()->get($this->leadSessionKey($funnelId));

        return $leadId ? (int) $leadId : null;
    }

    private function currentSelectedPricing(int $funnelId): ?array
    {
        $selection = session()->get($this->selectedPricingSessionKey($funnelId));

        return is_array($selection) ? $selection : null;
    }

    private function pricingSelectionFromCheckoutRequest(array $payload): ?array
    {
        $pricingId = trim((string) ($payload['checkout_pricing_id'] ?? ''));
        $sourceStepSlug = strtolower(trim((string) ($payload['checkout_pricing_source_step'] ?? '')));
        $plan = mb_substr(trim((string) ($payload['checkout_pricing_plan'] ?? '')), 0, 200);
        $price = $this->normalizeMoneyDisplay($payload['checkout_pricing_price'] ?? '');
        $regularPrice = $this->normalizeMoneyDisplay($payload['checkout_pricing_regular_price'] ?? '');
        $period = mb_substr(trim((string) ($payload['checkout_pricing_period'] ?? '')), 0, 60);
        $subtitle = mb_substr(trim((string) ($payload['checkout_pricing_subtitle'] ?? '')), 0, 300);
        $badge = mb_substr(trim((string) ($payload['checkout_pricing_badge'] ?? '')), 0, 80);
        $features = [];
        $rawFeatures = trim((string) ($payload['checkout_pricing_features'] ?? ''));
        if ($rawFeatures !== '') {
            $decoded = json_decode($rawFeatures, true);
            if (is_array($decoded)) {
                foreach ($decoded as $feature) {
                    if (! is_scalar($feature)) {
                        continue;
                    }
                    $featureText = mb_substr(trim((string) $feature), 0, 200);
                    if ($featureText !== '') {
                        $features[] = $featureText;
                    }
                }
            }
        }

        if (
            $pricingId === ''
            && $sourceStepSlug === ''
            && $plan === ''
            && $price === ''
            && $regularPrice === ''
            && $period === ''
            && $subtitle === ''
            && $badge === ''
            && $features === []
        ) {
            return null;
        }

        return [
            'pricingId' => $pricingId,
            'sourceStepSlug' => $sourceStepSlug,
            'plan' => $plan,
            'price' => $price,
            'regularPrice' => $regularPrice,
            'period' => $period,
            'subtitle' => $subtitle,
            'badge' => $badge,
            'features' => $features,
        ];
    }

    private function mergePricingSelections(?array $base, ?array $override): ?array
    {
        if (! is_array($base) && ! is_array($override)) {
            return null;
        }

        $merged = is_array($base) ? $base : [];
        foreach ((is_array($override) ? $override : []) as $key => $value) {
            if (is_array($value)) {
                if ($value !== []) {
                    $merged[$key] = array_values($value);
                }
                continue;
            }

            $text = trim((string) $value);
            if ($text !== '') {
                $merged[$key] = $text;
            }
        }

        return $merged !== [] ? $merged : null;
    }

    private function amountFromSelectedPricing(?array $selection): ?float
    {
        if (! is_array($selection)) {
            return null;
        }

        return $this->parseMoneyString($selection['price'] ?? null)
            ?? $this->parseMoneyString($selection['regularPrice'] ?? null);
    }

    private function primaryPricingAmountFromLayout(FunnelStep $step): ?float
    {
        $layout = $step->layout_json;
        if (! is_array($layout)) {
            return null;
        }

        $findInElements = function (array $elements) use (&$findInElements): ?float {
            foreach ($elements as $element) {
                if (! is_array($element)) {
                    continue;
                }
                if (strtolower(trim((string) ($element['type'] ?? ''))) === 'pricing') {
                    $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                    foreach (['price', 'regularPrice'] as $key) {
                        $amount = $this->parseMoneyString($settings[$key] ?? null);
                        if ($amount !== null && $amount > 0) {
                            return $amount;
                        }
                    }
                }
                $slides = is_array(data_get($element, 'settings.slides')) ? data_get($element, 'settings.slides') : [];
                foreach ($slides as $slide) {
                    $nested = $findInElements(is_array($slide['elements'] ?? null) ? $slide['elements'] : []);
                    if ($nested !== null) {
                        return $nested;
                    }
                }
            }

            return null;
        };

        $findInSections = function (array $sections) use ($findInElements): ?float {
            foreach ($sections as $section) {
                if (! is_array($section)) {
                    continue;
                }
                $sectionAmount = $findInElements(is_array($section['elements'] ?? null) ? $section['elements'] : []);
                if ($sectionAmount !== null) {
                    return $sectionAmount;
                }
                $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                foreach ($rows as $row) {
                    $columns = is_array($row['columns'] ?? null) ? $row['columns'] : [];
                    foreach ($columns as $column) {
                        $amount = $findInElements(is_array($column['elements'] ?? null) ? $column['elements'] : []);
                        if ($amount !== null) {
                            return $amount;
                        }
                    }
                }
            }

            return null;
        };

        $rootAmount = $findInSections(is_array($layout['root'] ?? null) ? $layout['root'] : []);
        if ($rootAmount !== null) {
            return $rootAmount;
        }

        return $findInSections(is_array($layout['sections'] ?? null) ? $layout['sections'] : []);
    }

    private function parseMoneyString(mixed $raw): ?float
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9,.\-]/', '', $value);
        if (! is_string($clean) || $clean === '') {
            return null;
        }

        $amount = (float) str_replace(',', '', $clean);

        return $amount > 0 ? $amount : null;
    }

    private function normalizeMoneyDisplay(mixed $raw): string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\s*\$/', $value) === 1) {
            $value = preg_replace('/^\s*\$/', '₱', $value) ?? $value;
        }

        return $value;
    }

    private function mergeTags(array ...$groups): array
    {
        return collect($groups)
            ->flatten(1)
            ->map(fn ($tag) => mb_strtolower(trim((string) $tag)))
            ->filter(fn ($tag) => $tag !== '')
            ->map(function ($tag) {
                $clean = preg_replace('/[^a-z0-9\-_ ]/i', '', $tag) ?? '';

                return mb_substr(trim($clean), 0, 40);
            })
            ->filter(fn ($tag) => $tag !== '')
            ->unique()
            ->take(30)
            ->values()
            ->all();
    }
}
