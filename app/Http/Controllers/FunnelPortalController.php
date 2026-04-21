<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelEvent;
use App\Models\FunnelReview;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use App\Services\CouponService;
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
        $lead = null;
        $leadId = $this->currentLeadId($funnel->id);
        if ($leadId) {
            $lead = Lead::query()->find($leadId);
        }
        $recentReviewPayment = $this->recentPaidPaymentForReview($request, $funnel, $lead, $tracking);
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
            'resolvedCheckoutAmount' => $this->resolvePortalCheckoutAmountForStep($step, $selectedPricing),
            'productInventory' => $this->productInventorySummary($funnel),
            'approvedReviews' => $this->approvedReviewsForFunnel($funnel),
            // Used by the coupon popup (any step) and the checkout coupon prompt (checkout/offer steps only).
            'availableCoupons' => app(CouponService::class)->availableForFunnel($funnel),
            'reviewPrefill' => [
                'name' => trim((string) ($lead->name ?? '')),
                'email' => trim((string) ($lead->email ?? '')),
            ],
            'reviewAlreadySubmitted' => $this->hasSubmittedReviewForJourney($request, $funnel, $lead, $recentReviewPayment, $tracking),
            'currentJourneyReview' => $this->currentJourneyReview($request, $funnel, $lead, $recentReviewPayment, $tracking),
        ]);
    }

    public function submitReview(
        Request $request,
        FunnelTrackingService $tracking,
        string $funnelSlug,
        string $stepSlug
    ) {
        [$funnel, $steps, $step] = $this->resolveStepContext($funnelSlug, $stepSlug, 'thank_you');

        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_email' => 'nullable|email|max:150',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:2000',
            'is_public' => 'nullable|boolean',
        ], [
            'customer_name.required' => 'Your name is required.',
            'review_text.required' => 'Please write a short review.',
        ]);

        $lead = null;
        $leadId = $this->currentLeadId($funnel->id);
        if ($leadId) {
            $lead = Lead::query()->find($leadId);
        }

        $recentPaidPayment = $this->recentPaidPaymentForReview($request, $funnel, $lead, $tracking);
        if (! $lead && ! $recentPaidPayment) {
            return redirect()
                ->back()
                ->withErrors(['review' => 'A recent funnel journey was not detected for this review.'])
                ->withInput();
        }

        $email = trim((string) ($validated['customer_email'] ?? ''));
        if ($email === '' && $lead && filter_var((string) $lead->email, FILTER_VALIDATE_EMAIL)) {
            $email = (string) $lead->email;
        }

        $existing = FunnelReview::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id)
            ->when($recentPaidPayment, fn ($query) => $query->where('payment_id', $recentPaidPayment->id))
            ->when(! $recentPaidPayment && $lead, fn ($query) => $query->where('lead_id', $lead->id))
            ->latest('id')
            ->first();

        $review = $existing ?? new FunnelReview();
        $review->tenant_id = $funnel->tenant_id;
        $review->funnel_id = $funnel->id;
        $review->funnel_step_id = $step->id;
        $review->lead_id = $lead?->id;
        $review->payment_id = $recentPaidPayment?->id;
        $review->customer_name = trim((string) $validated['customer_name']);
        $review->customer_email = $email !== '' ? $email : null;
        $review->rating = (int) $validated['rating'];
        $review->review_text = trim((string) $validated['review_text']);
        $review->status = 'pending';
        $review->is_public = (bool) ($validated['is_public'] ?? true);
        $review->source = 'thank_you_form';
        $review->approved_at = null;
        $review->approved_by = null;
        $review->meta = array_filter([
            'step_slug' => (string) $step->slug,
            'session_identifier' => $tracking->sessionIdentifier($request),
            'payment_amount' => $recentPaidPayment ? (float) $recentPaidPayment->amount : null,
        ], fn ($value) => $value !== null && $value !== '');
        $review->save();

        return redirect()
            ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
            ->with('review_status', 'Thanks for the review. It is now waiting for approval.');
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
        CouponService $coupons,
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
            'checkout_pricing_image' => 'nullable|string|max:2000',
            'checkout_pricing_features' => 'nullable|string|max:5000',
            'checkout_cart_items' => 'nullable|string|max:20000',
            'coupon_code' => 'nullable|string|max:40',
            'first_name' => 'nullable|string|max:150',
            'last_name' => 'nullable|string|max:150',
            'name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'province' => 'nullable|string|max:150',
            'city_municipality' => 'nullable|string|max:150',
            'barangay' => 'nullable|string|max:150',
            'street' => 'nullable|string|max:180',
            'postal_code' => 'nullable|string|max:40',
            'notes' => 'nullable|string|max:500',
            'website' => 'nullable|string|size:0',
        ]);

        $effectiveFunnelPurpose = strtolower(trim((string) (($funnel->purpose ?? null) ?: ($funnel->template_type ?? 'service'))));
        if (! in_array($effectiveFunnelPurpose, ['service', 'single_page', 'digital_product', 'physical_product', 'hybrid'], true)) {
            $effectiveFunnelPurpose = 'service';
        }
        $isPhysicalCheckout = in_array($effectiveFunnelPurpose, ['physical_product', 'hybrid'], true);
        $checkoutName = trim(
            (string) (($validated['name'] ?? '')
            ?: trim((string) (($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''))))
        );
        $checkoutPhone = trim((string) (($validated['phone_number'] ?? '') ?: ($validated['phone'] ?? '')));
        $checkoutEmail = trim((string) ($validated['email'] ?? ''));
        if ($checkoutPhone !== '' && ! preg_match('/^09\d{9}$/', $checkoutPhone)) {
            return redirect()->back()->withErrors(['phone_number' => 'Phone must be a valid Philippine mobile number (09XXXXXXXXX).'])->withInput();
        }
        if ($isPhysicalCheckout) {
            $missing = [];
            if ($checkoutName === '') $missing['name'] = 'Full name is required.';
            if ($checkoutEmail === '') $missing['email'] = 'Email is required.';
            if ($checkoutPhone === '') $missing['phone_number'] = 'Phone number is required.';
            if (trim((string) ($validated['province'] ?? '')) === '') $missing['province'] = 'Province is required.';
            if (trim((string) ($validated['city_municipality'] ?? '')) === '') $missing['city_municipality'] = 'City / Municipality is required.';
            if (trim((string) ($validated['barangay'] ?? '')) === '') $missing['barangay'] = 'Barangay is required.';
            if (trim((string) ($validated['street'] ?? '')) === '') $missing['street'] = 'Street address is required.';
            if (! empty($missing)) {
                return redirect()->back()->withErrors($missing)->withInput();
            }
            if ($checkoutEmail !== '') {
                $lead = Lead::firstOrNew([
                    'tenant_id' => $funnel->tenant_id,
                    'email' => $checkoutEmail,
                ]);
                if (! $lead->exists) {
                    $lead->assigned_to = null;
                    $lead->status = 'new';
                    $lead->score = 0;
                }
                $lead->name = $checkoutName !== '' ? $checkoutName : $lead->name;
                $lead->phone = $checkoutPhone !== '' ? $checkoutPhone : ($lead->phone ?? '');
                $lead->tags = $this->mergeTags(
                    $lead->tags ?? [],
                    $funnel->default_tags ?? [],
                    $step->step_tags ?? []
                );
                $lead->save();
                session()->put($this->leadSessionKey($funnel->id), $lead->id);
            }
        }

        $submittedAmount = array_key_exists('amount', $validated) ? (float) $validated['amount'] : null;
        $selectedPricing = $this->currentSelectedPricing($funnel->id);
        $postedPricing = $this->pricingSelectionFromCheckoutRequest($validated);
        if ($postedPricing !== null) {
            $selectedPricing = $this->mergePricingSelections($selectedPricing, $postedPricing);
            if ($selectedPricing !== null) {
                session()->put($this->selectedPricingSessionKey($funnel->id), $selectedPricing);
            }
        }
        $stockErrors = $this->checkoutStockErrors($funnel, $validated, $selectedPricing);
        if ($stockErrors !== []) {
            return redirect()->back()->withErrors($stockErrors)->withInput();
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
        $subtotalAmount = $amount;
        $leadId = $this->currentLeadId($funnel->id);
        $lead = $leadId ? Lead::query()->find($leadId) : null;
        $couponCheck = $coupons->validateForCheckout(
            $funnel,
            $subtotalAmount,
            $validated['coupon_code'] ?? null,
            $checkoutEmail !== '' ? $checkoutEmail : ($lead?->email ?? null),
            $lead
        );
        if ($couponCheck['error']) {
            return redirect()->back()->withErrors(['coupon_code' => $couponCheck['error']])->withInput();
        }
        $coupon = $couponCheck['coupon'];
        $discountAmount = (float) $couponCheck['discount_amount'];
        $amount = (float) $couponCheck['final_amount'];
        $request->attributes->set('checkout_coupon_id', $coupon?->id);
        $request->attributes->set('checkout_coupon_code', $coupon?->code);
        $request->attributes->set('checkout_subtotal_amount', $subtotalAmount);
        $request->attributes->set('checkout_discount_amount', $discountAmount);
        $checkoutTrackingMeta = $this->checkoutTrackingMeta(
            $validated,
            $checkoutName,
            $checkoutEmail,
            $checkoutPhone,
            $effectiveFunnelPurpose,
            $isPhysicalCheckout,
            $selectedPricing
        );
        if ($coupon) {
            $checkoutTrackingMeta['coupon'] = [
                'code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'subtotal_amount' => $subtotalAmount,
                'final_amount' => $amount,
            ];
        }

        if ($sessionIdentifier !== null && $sessionIdentifier !== '') {
            $recentPayment = Payment::query()
                ->where('tenant_id', $funnel->tenant_id)
                ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
                ->where('funnel_id', $funnel->id)
                ->where('funnel_step_id', $step->id)
                ->where('session_identifier', $sessionIdentifier)
                ->where('amount', $amount)
                ->whereIn('status', ['pending', 'paid'])
                ->where('created_at', '>=', now()->subMinutes(10))
                ->latest('id')
                ->first();

            if ($recentPayment) {
                if ($recentPayment->status === 'paid') {
                    $tracking->trackPaymentPaid($recentPayment, ['source' => 'checkout_repeat_guard']);

                    return $this->redirectAfterPaidCheckout($funnel, $steps, $step);
                }

                if ($recentPayment->provider === 'paymongo' && ! empty($recentPayment->provider_reference)) {
                    if ($payMongo->isConfigured()) {
                        $existingSession = $payMongo->retrieveCheckoutSession((string) $recentPayment->provider_reference);
                        $existingCheckoutUrl = is_array($existingSession['attributes'] ?? null)
                            ? (string) ($existingSession['attributes']['checkout_url'] ?? '')
                            : '';
                        if ($existingCheckoutUrl !== '') {
                            return response()->view('funnels.portal.paymongo-redirect', [
                                'checkoutUrl' => $existingCheckoutUrl,
                            ]);
                        }
                    }

                    return redirect()
                        ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
                        ->with('error', 'Checkout is already in progress for this session. Please finish the current payment before trying again.');
                }
            }
        }

        if ($payMongo->isConfigured()) {
            return $this->checkoutWithPayMongo($payMongo, $tracking, $request, $funnel, $steps, $step, $amount, $selectedPricing, $checkoutTrackingMeta);
        }

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $this->currentLeadId($funnel->id),
            'coupon_id' => $coupon?->id,
            'coupon_code' => $coupon?->code,
            'amount' => $amount,
            'subtotal_amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'session_identifier' => $sessionIdentifier,
        ]);

        $tracking->trackCheckoutStarted(
            $funnel,
            $step,
            $payment,
            $request,
            $selectedPricing,
            array_merge($checkoutTrackingMeta, ['source' => 'direct_checkout'])
        );
        $tracking->trackPaymentPaid($payment, ['source' => 'direct_checkout']);
        if ($coupon) {
            $coupons->redeem(
                $coupon,
                $payment,
                $funnel,
                $step,
                $lead,
                $checkoutEmail !== '' ? strtolower($checkoutEmail) : ($lead?->email ? strtolower((string) $lead->email) : null),
                $subtotalAmount,
                $discountAmount,
                $amount
            );
        }

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
        abort_if(! $step || ! in_array($step->type, ['checkout', 'upsell', 'downsell'], true), 404);

        $record = Payment::query()->findOrFail($payment);
        abort_unless($record->provider === 'paymongo', 403);
        abort_unless((int) $record->tenant_id === (int) $funnel->tenant_id, 403);

        if ($record->status === 'paid') {
            return $this->completeConfirmedPayment($tracking, $record, $funnel, $steps, $step, 'paymongo_return_cached');
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
            return $this->completeConfirmedPayment($tracking, $record->fresh(), $funnel, $steps, $step, 'paymongo_return');
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
        array $checkoutTrackingMeta = [],
    ): \Symfony\Component\HttpFoundation\Response {
        $centavos = (int) round($amount * 100);
        abort_if($centavos < 1, 422, 'Checkout amount is not configured.');

        $leadId = $this->currentLeadId($funnel->id);
        $lead = $leadId ? Lead::query()->find($leadId) : null;

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $leadId,
            'coupon_id' => $couponId = $this->nullableCouponIdFromRequest(),
            'coupon_code' => $couponCode = $this->currentCouponCodeFromRequest(),
            'amount' => $amount,
            'subtotal_amount' => $this->currentSubtotalAmountFromRequest($amount),
            'discount_amount' => $this->currentDiscountAmountFromRequest(),
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
            'session_identifier' => $tracking->sessionIdentifier($request),
        ]);

        $tracking->trackCheckoutStarted(
            $funnel,
            $step,
            $payment,
            $request,
            $selectedPricing,
            array_merge($checkoutTrackingMeta, ['source' => 'paymongo'])
        );

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
                'coupon_id' => $couponId ? (string) $couponId : null,
                'coupon_code' => $couponCode !== '' ? $couponCode : null,
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

        return response()->view('funnels.portal.paymongo-redirect', [
            'checkoutUrl' => $session['checkout_url'],
        ]);
    }

    private function redirectAfterPaidCheckout(Funnel $funnel, $steps, FunnelStep $step): \Illuminate\Http\RedirectResponse
    {
        $next = $this->nextStep($steps, $step->id);
        if (! $next) {
            return redirect()
                ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
                ->with('clear_portal_cart', true);
        }

        return redirect()
            ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $next->slug])
            ->with('clear_portal_cart', true);
    }

    private function checkoutTrackingMeta(
        array $validated,
        string $checkoutName,
        string $checkoutEmail,
        string $checkoutPhone,
        string $funnelPurpose,
        bool $isPhysicalCheckout,
        ?array $selectedPricing = null
    ): array {
        $meta = [
            'funnel_purpose' => $funnelPurpose,
        ];

        $customer = array_filter([
            'full_name' => $checkoutName !== '' ? $checkoutName : null,
            'first_name' => trim((string) ($validated['first_name'] ?? '')) ?: null,
            'last_name' => trim((string) ($validated['last_name'] ?? '')) ?: null,
            'email' => $checkoutEmail !== '' ? $checkoutEmail : null,
            'phone' => $checkoutPhone !== '' ? $checkoutPhone : null,
        ], fn ($value) => $value !== null && $value !== '');
        if ($customer !== []) {
            $meta['customer'] = $customer;
        }

        $orderItems = $this->checkoutOrderItemsFromPayload($validated, $selectedPricing);
        if ($orderItems !== []) {
            $orderQuantity = 0;
            $orderLabels = [];
            foreach ($orderItems as $item) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $orderQuantity += $qty;
                $name = trim((string) ($item['name'] ?? ''));
                if ($name !== '') {
                    $orderLabels[] = $name . ' x' . $qty;
                }
            }

            $meta['order_items'] = $orderItems;
            $meta['order_item_count'] = count($orderItems);
            $meta['order_quantity'] = $orderQuantity;
            if ($orderLabels !== []) {
                $meta['order_items_label'] = implode(', ', $orderLabels);
            }
        }

        if ($isPhysicalCheckout) {
            $street = trim((string) ($validated['street'] ?? ''));
            $barangay = trim((string) ($validated['barangay'] ?? ''));
            $cityMunicipality = trim((string) ($validated['city_municipality'] ?? ''));
            $province = trim((string) ($validated['province'] ?? ''));
            $postalCode = trim((string) ($validated['postal_code'] ?? ''));
            $notes = trim((string) ($validated['notes'] ?? ''));

            $shipping = array_filter([
                'street' => $street !== '' ? $street : null,
                'barangay' => $barangay !== '' ? $barangay : null,
                'city_municipality' => $cityMunicipality !== '' ? $cityMunicipality : null,
                'province' => $province !== '' ? $province : null,
                'postal_code' => $postalCode !== '' ? $postalCode : null,
                'notes' => $notes !== '' ? $notes : null,
            ], fn ($value) => $value !== null && $value !== '');

            if ($shipping !== []) {
                $meta['shipping'] = $shipping;
                $meta['delivery_address'] = implode(', ', array_filter([
                    $street !== '' ? $street : null,
                    $barangay !== '' ? $barangay : null,
                    $cityMunicipality !== '' ? $cityMunicipality : null,
                    $province !== '' ? $province : null,
                    $postalCode !== '' ? $postalCode : null,
                ]));
            }
        }

        return $meta;
    }

    private function checkoutOrderItemsFromPayload(array $payload, ?array $selectedPricing = null): array
    {
        $items = [];
        $rawItems = trim((string) ($payload['checkout_cart_items'] ?? ''));
        if ($rawItems !== '') {
            $decoded = json_decode($rawItems, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $name = mb_substr(trim((string) ($item['name'] ?? '')), 0, 200);
                    $price = $this->normalizeMoneyDisplay($item['price'] ?? '');
                    $regularPrice = $this->normalizeMoneyDisplay($item['regularPrice'] ?? ($item['regular_price'] ?? ''));
                    $period = mb_substr(trim((string) ($item['period'] ?? '')), 0, 60);
                    $badge = mb_substr(trim((string) ($item['badge'] ?? '')), 0, 80);
                    $image = mb_substr(trim((string) ($item['image'] ?? '')), 0, 2000);
                    $quantity = (int) ($item['quantity'] ?? 1);
                    $quantity = max(1, min($quantity, 999));

                    if ($name === '' && $price === '' && $regularPrice === '' && $badge === '' && $image === '') {
                        continue;
                    }

                    $items[] = [
                        'id' => mb_substr(trim((string) ($item['id'] ?? '')), 0, 120),
                        'step_slug' => strtolower(mb_substr(trim((string) ($item['stepSlug'] ?? ($item['step_slug'] ?? ''))), 0, 120)),
                        'name' => $name !== '' ? $name : 'Product',
                        'price' => $price,
                        'regular_price' => $regularPrice,
                        'period' => $period,
                        'badge' => $badge,
                        'image' => $image,
                        'quantity' => $quantity,
                    ];
                }
            }
        }

        if ($items !== []) {
            return $items;
        }

        if (! is_array($selectedPricing)) {
            return [];
        }

        $name = mb_substr(trim((string) ($selectedPricing['plan'] ?? '')), 0, 200);
        $price = $this->normalizeMoneyDisplay($selectedPricing['price'] ?? '');
        $regularPrice = $this->normalizeMoneyDisplay($selectedPricing['regularPrice'] ?? '');
        $period = mb_substr(trim((string) ($selectedPricing['period'] ?? '')), 0, 60);
        $badge = mb_substr(trim((string) ($selectedPricing['badge'] ?? '')), 0, 80);
        $image = mb_substr(trim((string) ($selectedPricing['image'] ?? '')), 0, 2000);

        if ($name === '' && $price === '' && $regularPrice === '' && $badge === '' && $image === '') {
            return [];
        }

        return [[
            'id' => mb_substr(trim((string) ($selectedPricing['pricingId'] ?? '')), 0, 120),
            'step_slug' => strtolower(mb_substr(trim((string) ($selectedPricing['sourceStepSlug'] ?? '')), 0, 120)),
            'name' => $name !== '' ? $name : 'Selected item',
            'price' => $price,
            'regular_price' => $regularPrice,
            'period' => $period,
            'badge' => $badge,
            'image' => $image,
            'quantity' => 1,
        ]];
    }

    private function checkoutStockErrors(Funnel $funnel, array $payload, ?array $selectedPricing = null): array
    {
        $orderItems = $this->checkoutOrderItemsFromPayload($payload, $selectedPricing);
        if ($orderItems === []) {
            return [];
        }

        $inventory = $this->productInventorySummary($funnel);
        $requested = [];

        foreach ($orderItems as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = trim((string) ($item['id'] ?? ''));
            if ($productId === '' || ! isset($inventory[$productId])) {
                continue;
            }

            if (! isset($requested[$productId])) {
                $requested[$productId] = [
                    'name' => trim((string) ($item['name'] ?? 'Product')) ?: 'Product',
                    'quantity' => 0,
                ];
            }

            $requested[$productId]['quantity'] += max(1, (int) ($item['quantity'] ?? 1));
        }

        foreach ($requested as $productId => $row) {
            $remaining = (int) ($inventory[$productId]['remaining_stock'] ?? 0);
            if ($remaining <= 0) {
                return ['checkout_cart_items' => ($row['name'] ?? 'This product') . ' is already out of stock.'];
            }

            if ((int) ($row['quantity'] ?? 0) > $remaining) {
                return ['checkout_cart_items' => ($row['name'] ?? 'This product') . ' only has ' . $remaining . ' item' . ($remaining === 1 ? '' : 's') . ' left in stock.'];
            }
        }

        return [];
    }

    private function selectionStockErrors(Funnel $funnel, ?array $selectedPricing): array
    {
        if (! is_array($selectedPricing)) {
            return [];
        }

        $productId = trim((string) ($selectedPricing['pricingId'] ?? ''));
        if ($productId === '') {
            return [];
        }

        $inventory = $this->productInventorySummary($funnel);
        if (! isset($inventory[$productId])) {
            return [];
        }

        $remaining = (int) ($inventory[$productId]['remaining_stock'] ?? 0);
        if ($remaining > 0) {
            return [];
        }

        $name = trim((string) ($selectedPricing['plan'] ?? '')) ?: 'This product';

        return ['decision' => $name . ' is already out of stock.'];
    }

    private function productInventorySummary(Funnel $funnel): array
    {
        $products = [];

        foreach ($funnel->steps()->where('is_active', true)->get(['layout_json']) as $step) {
            $layout = is_array($step->layout_json ?? null) ? $step->layout_json : [];
            $roots = is_array($layout['root'] ?? null)
                ? $layout['root']
                : (is_array($layout['sections'] ?? null) ? $layout['sections'] : []);

            foreach ($roots as $node) {
                $this->collectProductInventoryNodes($node, $products);
            }
        }

        if ($products === []) {
            return [];
        }

        $paidEvents = FunnelEvent::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id)
            ->where('event_name', FunnelTrackingService::EVENT_PAYMENT_PAID)
            ->get(['meta']);

        foreach ($paidEvents as $event) {
            $items = data_get($event->meta, 'order_items');
            if (! is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $productId = trim((string) ($item['id'] ?? ''));
                if ($productId === '' || ! isset($products[$productId])) {
                    continue;
                }

                $products[$productId]['sold_units'] += max(1, (int) ($item['quantity'] ?? 1));
            }
        }

        foreach ($products as $productId => $row) {
            $stockQuantity = (int) ($row['stock_quantity'] ?? 0);
            $soldUnits = (int) ($row['sold_units'] ?? 0);
            $soldOffset = (int) ($row['sold_offset'] ?? 0);
            // Restock-friendly fallback:
            // If the merchant sets stock to a small number AFTER many sales, but the UI didn't
            // capture a sold offset, assume they meant "new remaining stock" and start counting from now.
            if ($soldOffset <= 0 && $soldUnits > $stockQuantity) {
                $soldOffset = $soldUnits;
            }
            $effectiveSold = max(0, $soldUnits - $soldOffset);
            $products[$productId]['remaining_stock'] = max(0, $stockQuantity - $effectiveSold);
            $products[$productId]['is_out_of_stock'] = $products[$productId]['remaining_stock'] <= 0;
        }

        return $products;
    }

    private function collectProductInventoryNodes(mixed $node, array &$products): void
    {
        if (! is_array($node)) {
            return;
        }

        if (strtolower(trim((string) ($node['type'] ?? ''))) === 'product_offer') {
            $productId = trim((string) ($node['id'] ?? ''));
            $settings = is_array($node['settings'] ?? null) ? $node['settings'] : [];
            $stockQuantity = max(0, (int) ($settings['stockQuantity'] ?? 0));
            $soldOffset = max(0, (int) ($settings['stockSoldOffset'] ?? 0));

            if ($productId !== '' && $stockQuantity > 0) {
                $products[$productId] = [
                    'name' => trim((string) ($settings['plan'] ?? '')) ?: 'Product',
                    'stock_quantity' => $stockQuantity,
                    'sold_units' => (int) ($products[$productId]['sold_units'] ?? 0),
                    'sold_offset' => $soldOffset,
                    'remaining_stock' => $stockQuantity,
                    'is_out_of_stock' => false,
                ];
            }
        }

        foreach (['root', 'sections', 'rows', 'columns', 'elements'] as $childrenKey) {
            $children = $node[$childrenKey] ?? null;
            if (! is_array($children)) {
                continue;
            }

            foreach ($children as $child) {
                $this->collectProductInventoryNodes($child, $products);
            }
        }
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
            'checkout_pricing_image' => 'nullable|string|max:2000',
            'checkout_pricing_features' => 'nullable|string|max:4000',
        ]);

        $requestSelection = $this->pricingSelectionFromCheckoutRequest($validated);
        $currentStepPricing = $this->primaryPricingSelectionFromLayout($step);
        $requestTargetsCurrentStep = is_array($requestSelection)
            && strtolower(trim((string) ($requestSelection['sourceStepSlug'] ?? ''))) === strtolower(trim((string) ($step->slug ?? '')));
        if ($requestTargetsCurrentStep) {
            $selectedPricing = $this->mergePricingSelections($currentStepPricing, $requestSelection);
        } elseif (is_array($currentStepPricing)) {
            $selectedPricing = $currentStepPricing;
        } else {
            $selectedPricing = $this->mergePricingSelections($this->currentSelectedPricing($funnel->id), $requestSelection);
        }
        $offerAmount = $this->amountFromSelectedPricing($selectedPricing)
            ?? ((float) ($step->price ?? 0) > 0 ? (float) $step->price : null)
            ?? $this->primaryPricingAmountFromLayout($step)
            ?? 0.0;

        $accept = $validated['decision'] === 'accept';
        if ($accept) {
            $offerStockErrors = $this->selectionStockErrors($funnel, $selectedPricing);
            if ($offerStockErrors !== []) {
                return redirect()->back()->withErrors($offerStockErrors)->withInput();
            }
        }
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
        if ($accept && $offerAmount > 0) {
            $sessionIdentifier = $tracking->sessionIdentifier($request);
            $recentPayment = Payment::query()
                ->where('tenant_id', $funnel->tenant_id)
                ->where('funnel_id', $funnel->id)
                ->where('funnel_step_id', $step->id)
                ->where('session_identifier', $sessionIdentifier)
                ->where('amount', $offerAmount)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->latest('id')
                ->first();

            if ($recentPayment) {
                if ($recentPayment->status === 'paid') {
                    return $this->completeConfirmedPayment($tracking, $recentPayment, $funnel, $steps, $step, 'offer_repeat_guard');
                }

                if ($recentPayment->provider === 'paymongo' && ! empty($recentPayment->provider_reference)) {
                    $payMongo = app(PayMongoCheckoutService::class);
                    if ($payMongo->isConfigured()) {
                        $existingSession = $payMongo->retrieveCheckoutSession((string) $recentPayment->provider_reference);
                        $existingCheckoutUrl = is_array($existingSession['attributes'] ?? null)
                            ? (string) ($existingSession['attributes']['checkout_url'] ?? '')
                            : '';
                        if ($existingCheckoutUrl !== '') {
                            return response()->view('funnels.portal.paymongo-redirect', [
                                'checkoutUrl' => $existingCheckoutUrl,
                            ]);
                        }
                    }
                }

                return redirect()
                    ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
                    ->with('error', 'Offer checkout is already in progress for this session. Please finish the current payment before trying again.');
            }

            if (app(PayMongoCheckoutService::class)->isConfigured()) {
                return $this->checkoutWithPayMongo(
                    app(PayMongoCheckoutService::class),
                    $tracking,
                    $request,
                    $funnel,
                    $steps,
                    $step,
                    $offerAmount,
                    $selectedPricing
                );
            }

            $payment = Payment::create([
                'tenant_id' => $funnel->tenant_id,
                'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
                'funnel_id' => $funnel->id,
                'funnel_step_id' => $step->id,
                'lead_id' => $this->currentLeadId($funnel->id),
                'amount' => $offerAmount,
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
                'session_identifier' => $sessionIdentifier,
            ]);
            $tracking->trackPaymentPaid($payment, ['source' => 'offer_accept_direct']);
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

        return $this->redirectAfterOfferDecision($funnel, $steps, $step, $accept);
    }

    private function completeConfirmedPayment(
        FunnelTrackingService $tracking,
        Payment $payment,
        Funnel $funnel,
        $steps,
        FunnelStep $step,
        string $source
    ): \Illuminate\Http\RedirectResponse {
        if ($payment->coupon_id && $payment->status === 'paid') {
            $lead = $payment->lead_id ? Lead::query()->find($payment->lead_id) : null;
            $coupon = $payment->coupon;
            if ($coupon) {
                app(CouponService::class)->redeem(
                    $coupon,
                    $payment,
                    $funnel,
                    $step,
                    $lead,
                    $lead?->email ? strtolower((string) $lead->email) : null,
                    (float) ($payment->subtotal_amount ?? $payment->amount),
                    (float) ($payment->discount_amount ?? 0),
                    (float) $payment->amount
                );
            }
        }

        $tracking->trackPaymentPaid($payment, ['source' => $source]);

        if (in_array($step->type, ['upsell', 'downsell'], true)) {
            $this->trackAcceptedOfferIfMissing($tracking, $funnel, $step, $payment);

            return $this->redirectAfterOfferDecision($funnel, $steps, $step, true);
        }

        return $this->redirectAfterPaidCheckout($funnel, $steps, $step);
    }

    private function trackAcceptedOfferIfMissing(
        FunnelTrackingService $tracking,
        Funnel $funnel,
        FunnelStep $step,
        Payment $payment
    ): void {
        $eventName = $step->type === 'upsell'
            ? FunnelTrackingService::EVENT_UPSELL_ACCEPTED
            : FunnelTrackingService::EVENT_DOWNSELL_ACCEPTED;

        $exists = $tracking->hasRecentEvent([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'payment_id' => $payment->id,
            'event_name' => $eventName,
        ], 86400);

        if ($exists) {
            return;
        }

        $lead = null;
        if ($payment->lead_id) {
            $lead = Lead::query()->find($payment->lead_id);
        }

        $tracking->trackOfferDecision($funnel, $step, 'accept', $lead, $payment, null, [
            'amount' => (float) $payment->amount,
            'source' => 'confirmed_payment',
        ]);
    }

    private function redirectAfterOfferDecision(Funnel $funnel, $steps, FunnelStep $step, bool $accept): \Illuminate\Http\RedirectResponse
    {
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

    private function approvedReviewsForFunnel(Funnel $funnel)
    {
        return FunnelReview::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id)
            ->where('status', 'approved')
            ->where('is_public', true)
            ->latest('approved_at')
            ->latest('id')
            ->get();
    }

    private function recentPaidPaymentForReview(Request $request, Funnel $funnel, ?Lead $lead, FunnelTrackingService $tracking): ?Payment
    {
        $query = Payment::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id)
            ->where('payment_type', Payment::TYPE_FUNNEL_CHECKOUT)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30));

        $sessionIdentifier = $tracking->sessionIdentifier($request);
        $query->where(function ($builder) use ($lead, $sessionIdentifier) {
            if ($lead) {
                $builder->orWhere('lead_id', $lead->id);
            }
            if ($sessionIdentifier !== '') {
                $builder->orWhere('session_identifier', $sessionIdentifier);
            }
        });

        return $query->latest('id')->first();
    }

    private function hasSubmittedReviewForJourney(
        Request $request,
        Funnel $funnel,
        ?Lead $lead,
        ?Payment $payment = null,
        ?FunnelTrackingService $tracking = null
    ): bool
    {
        return $this->currentJourneyReview($request, $funnel, $lead, $payment, $tracking) !== null;
    }

    private function currentJourneyReview(
        Request $request,
        Funnel $funnel,
        ?Lead $lead,
        ?Payment $payment = null,
        ?FunnelTrackingService $tracking = null
    ): ?FunnelReview
    {
        $query = FunnelReview::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->where('funnel_id', $funnel->id);

        if ($payment) {
            $query->where('payment_id', $payment->id);
        } elseif ($lead) {
            $query->where('lead_id', $lead->id);
        } else {
            $sessionIdentifier = trim((string) ($tracking?->sessionIdentifier($request) ?? ''));
            if ($sessionIdentifier === '') {
                return null;
            }
            $query->where('meta->session_identifier', $sessionIdentifier);
        }

        return $query->latest('id')->first();
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
        $image = mb_substr(trim((string) ($payload['checkout_pricing_image'] ?? '')), 0, 2000);
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
            && $image === ''
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
            'image' => $image,
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

    /**
     * Same amount resolution as checkout(), without a posted amount — used so the portal
     * hidden checkout field and coupon modal never stay at zero when pricing exists in session or layout.
     */
    private function resolvePortalCheckoutAmountForStep(FunnelStep $step, ?array $selectedPricing): float
    {
        $selectedAmount = $this->amountFromSelectedPricing($selectedPricing);
        $layoutAmount = $this->primaryPricingAmountFromLayout($step);
        $stepAmount = (float) ($step->price ?? 0);

        $preferredAmount = ($selectedAmount !== null && $selectedAmount > 0)
            ? $selectedAmount
            : (($layoutAmount !== null && $layoutAmount > 0) ? $layoutAmount : null);

        $amount = $preferredAmount ?? $stepAmount;

        return $amount > 0 ? $amount : 0.0;
    }

    private function nullableCouponIdFromRequest(): ?int
    {
        $couponId = request()->attributes->get('checkout_coupon_id');

        return is_numeric($couponId) ? (int) $couponId : null;
    }

    private function currentCouponCodeFromRequest(): string
    {
        return trim((string) request()->attributes->get('checkout_coupon_code', ''));
    }

    private function currentSubtotalAmountFromRequest(float $fallback): float
    {
        $subtotal = request()->attributes->get('checkout_subtotal_amount');

        return is_numeric($subtotal) ? (float) $subtotal : $fallback;
    }

    private function currentDiscountAmountFromRequest(): float
    {
        $discount = request()->attributes->get('checkout_discount_amount');

        return is_numeric($discount) ? (float) $discount : 0.0;
    }

    private function primaryPricingAmountFromLayout(FunnelStep $step): ?float
    {
        $selection = $this->primaryPricingSelectionFromLayout($step);
        if (is_array($selection)) {
            $amount = $this->amountFromSelectedPricing($selection);
            if ($amount !== null && $amount > 0) {
                return $amount;
            }
        }

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

    private function primaryPricingSelectionFromLayout(FunnelStep $step): ?array
    {
        $layout = $step->layout_json;
        if (! is_array($layout)) {
            return null;
        }

        $findInElements = function (array $elements) use (&$findInElements, $step): ?array {
            foreach ($elements as $element) {
                if (! is_array($element)) {
                    continue;
                }
                if (strtolower(trim((string) ($element['type'] ?? ''))) === 'pricing') {
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
                        'pricingId' => trim((string) ($element['id'] ?? '')),
                        'sourceStepSlug' => (string) ($step->slug ?? ''),
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
