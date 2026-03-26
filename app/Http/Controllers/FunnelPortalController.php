<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use App\Services\PayMongoCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class FunnelPortalController extends Controller
{
    public function show(string $funnelSlug, ?string $stepSlug = null)
    {
        $funnel = Funnel::with(['tenant', 'steps'])->where('slug', $funnelSlug)->where('status', 'published')->firstOrFail();
        $steps = $funnel->steps->where('is_active', true)->sortBy('position')->values();
        abort_if($steps->isEmpty(), 404);

        $step = $stepSlug
            ? $steps->firstWhere('slug', $stepSlug)
            : $steps->first();

        abort_if(! $step, 404);

        return view('funnels.portal.step', [
            'funnel' => $funnel,
            'step' => $step,
            'nextStep' => $this->nextStep($steps, $step->id),
            'allSteps' => $steps,
            'isFirstStep' => $steps->first()->id === $step->id,
        ]);
    }

    public function optIn(Request $request, string $funnelSlug, string $stepSlug)
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

        $lead->increment('score', 20);
        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => 'Form Submitted (+20 points)',
        ]);

        session()->put($this->leadSessionKey($funnel->id), $lead->id);

        $next = $this->nextStep($steps, $step->id);
        abort_if(! $next, 422, 'No next step configured.');

        return redirect()->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $next->slug]);
    }

    public function checkout(Request $request, PayMongoCheckoutService $payMongo, string $funnelSlug, string $stepSlug)
    {
        [$funnel, $steps, $step] = $this->resolveStepContext($funnelSlug, $stepSlug, 'checkout');

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $amount = $validated['amount'] ?? (float) ($step->price ?? 0);
        abort_if($amount <= 0, 422, 'Checkout amount is not configured.');

        if ($payMongo->isConfigured()) {
            return $this->checkoutWithPayMongo($payMongo, $funnel, $steps, $step, $amount);
        }

        Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'lead_id' => $this->currentLeadId($funnel->id),
            'amount' => $amount,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        return $this->redirectAfterPaidCheckout($funnel, $steps, $step);
    }

    public function paymongoReturn(PayMongoCheckoutService $payMongo, string $funnelSlug, string $stepSlug, int $payment)
    {
        $funnel = Funnel::with('steps')->where('slug', $funnelSlug)->where('status', 'published')->firstOrFail();
        $steps = $funnel->steps->where('is_active', true)->sortBy('position')->values();
        $step = $steps->firstWhere('slug', $stepSlug);
        abort_if(! $step || $step->type !== 'checkout', 404);

        $record = Payment::query()->findOrFail($payment);
        abort_unless($record->provider === 'paymongo', 403);
        abort_unless((int) $record->tenant_id === (int) $funnel->tenant_id, 403);

        if ($record->status === 'paid') {
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

            return $this->redirectAfterPaidCheckout($funnel, $steps, $step);
        }

        return redirect()
            ->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug])
            ->with('error', 'Payment was not completed. You can try again.');
    }

    private function checkoutWithPayMongo(
        PayMongoCheckoutService $payMongo,
        Funnel $funnel,
        $steps,
        FunnelStep $step,
        float $amount,
    ): \Illuminate\Http\RedirectResponse {
        $centavos = (int) round($amount * 100);
        abort_if($centavos < 1, 422, 'Checkout amount is not configured.');

        $leadId = $this->currentLeadId($funnel->id);
        $lead = $leadId ? Lead::query()->find($leadId) : null;

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'lead_id' => $leadId,
            'amount' => $amount,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
        ]);

        $successUrl = URL::signedRoute('funnels.portal.paymongo.return', [
            'funnelSlug' => $funnel->slug,
            'stepSlug' => $step->slug,
            'payment' => $payment->id,
        ], now()->addHours(48));

        $cancelUrl = route('funnels.portal.step', [
            'funnelSlug' => $funnel->slug,
            'stepSlug' => $step->slug,
        ]);

        $lineName = trim((string) ($step->title ?? '')) !== '' ? (string) $step->title : (string) $funnel->name;
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

    public function offer(Request $request, string $funnelSlug, string $stepSlug)
    {
        [$funnel, $steps, $step] = $this->resolveStepContext($funnelSlug, $stepSlug, null);
        abort_unless(in_array($step->type, ['upsell', 'downsell'], true), 422, 'Invalid offer step.');

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['accept', 'decline'])],
        ]);

        $accept = $validated['decision'] === 'accept';
        if ($accept && (float) $step->price > 0) {
            Payment::create([
                'tenant_id' => $funnel->tenant_id,
                'lead_id' => $this->currentLeadId($funnel->id),
                'amount' => (float) $step->price,
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
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

    private function leadSessionKey(int $funnelId): string
    {
        return "funnel_lead_{$funnelId}";
    }

    private function currentLeadId(int $funnelId): ?int
    {
        $leadId = session()->get($this->leadSessionKey($funnelId));

        return $leadId ? (int) $leadId : null;
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
