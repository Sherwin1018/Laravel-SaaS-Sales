<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
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

        abort_if(!$step, 404);

        return view('funnels.portal.step', [
            'funnel' => $funnel,
            'step' => $step,
            'nextStep' => $this->nextStep($steps, $step->id),
        ]);
    }

    public function optIn(Request $request, string $funnelSlug, string $stepSlug)
    {
        [$funnel, $steps, $step] = $this->resolveStepContext($funnelSlug, $stepSlug, 'opt_in');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => ['required', 'regex:/^09\d{9}$/'],
        ], [
            'phone.regex' => 'Phone number must be a valid Philippine mobile number (09XXXXXXXXX).',
        ]);

        $lead = Lead::firstOrNew([
            'tenant_id' => $funnel->tenant_id,
            'email' => $validated['email'],
        ]);

        if (!$lead->exists) {
            $defaultAgent = User::where('tenant_id', $funnel->tenant_id)
                ->whereHas('roles', fn ($q) => $q->where('slug', 'sales-agent'))
                ->orderBy('id')
                ->first();

            $lead->assigned_to = $defaultAgent?->id;
            $lead->status = 'new';
            $lead->score = 0;
        }

        $lead->name = $validated['name'];
        $lead->phone = $validated['phone'];
        $lead->save();

        session()->put($this->leadSessionKey($funnel->id), $lead->id);

        $next = $this->nextStep($steps, $step->id);
        abort_if(!$next, 422, 'No next step configured.');

        return redirect()->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $next->slug]);
    }

    public function checkout(Request $request, string $funnelSlug, string $stepSlug)
    {
        [$funnel, $steps, $step] = $this->resolveStepContext($funnelSlug, $stepSlug, 'checkout');

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        $amount = $validated['amount'] ?? (float) ($step->price ?? 0);
        abort_if($amount <= 0, 422, 'Checkout amount is not configured.');

        Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'lead_id' => $this->currentLeadId($funnel->id),
            'amount' => $amount,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        $next = $this->nextStep($steps, $step->id);
        if (!$next) {
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
            if ($step->type === 'upsell' && !$accept && $immediateNext && $immediateNext->type === 'downsell') {
                $target = $immediateNext;
            } elseif ($step->type === 'upsell' && $accept && $immediateNext && $immediateNext->type === 'downsell') {
                $target = $ordered->get($currentIndex + 2);
            } else {
                $target = $immediateNext;
            }
        }

        if (!$target) {
            $target = $ordered->last();
        }

        return redirect()->route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $target->slug]);
    }

    private function resolveStepContext(string $funnelSlug, string $stepSlug, ?string $expectedType): array
    {
        $funnel = Funnel::with('steps')->where('slug', $funnelSlug)->where('status', 'published')->firstOrFail();
        $steps = $funnel->steps->where('is_active', true)->sortBy('position')->values();
        $step = $steps->firstWhere('slug', $stepSlug);
        abort_if(!$step, 404);

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
}
