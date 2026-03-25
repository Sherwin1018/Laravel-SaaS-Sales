<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelVisit;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use App\Notifications\LeadVerifyEmail;
use App\Services\AutomationWebhookService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FunnelPortalController extends Controller
{
    public function show(Request $request, string $funnelSlug, ?string $stepSlug = null)
    {
        $funnel = Funnel::with(['tenant', 'steps'])->where('slug', $funnelSlug)->where('status', 'published')->firstOrFail();
        $steps = $funnel->steps->where('is_active', true)->sortBy('position')->values();
        abort_if($steps->isEmpty(), 404);

        $step = $stepSlug
            ? $steps->firstWhere('slug', $stepSlug)
            : $steps->first();

        abort_if(!$step, 404);

        $utmSource = $this->normalizeSourceCampaign($request->query('utm_source'));
        $utmMedium = $this->normalizeSourceCampaign($request->query('utm_medium'));
        $utmCampaign = $this->normalizeSourceCampaign($request->query('utm_campaign'));
        $referrer = $request->header('referer');
        $referrer = $referrer ? mb_substr(trim((string) $referrer), 0, 500) : null;

        // Persist attribution so it survives internal redirects between funnel steps.
        // (We only store it when we actually have something useful to store.)
        if ($utmSource || $utmMedium || $utmCampaign || $referrer) {
            session()->put($this->funnelUtmSessionKey($funnel->id), [
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'referrer' => $referrer,
            ]);
        }

        // Always log funnel landing so the tenant can see “how many times link was clicked”.
        try {
            FunnelVisit::create([
                'tenant_id' => $funnel->tenant_id,
                'funnel_id' => $funnel->id,
                'funnel_step_id' => $step->id,
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'referrer' => $referrer,
                'visited_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Tracking must never break the funnel UI.
            report($e);
        }

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
            ?: trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''))
        );
        $phone = $validated['phone_number'] ?? $validated['phone'] ?? '';
        if ($phone !== '' && !preg_match('/^09\d{9}$/', $phone)) {
            return redirect()->back()->withErrors(['phone_number' => 'Phone must be a valid Philippine mobile number (09XXXXXXXXX).'])->withInput();
        }

        $lead = Lead::firstOrNew([
            'tenant_id' => $funnel->tenant_id,
            'email' => $validated['email'],
        ]);

        if (!$lead->exists) {
            $lead->assigned_to = null;
            $lead->status = 'new';
            $lead->score = 0;
        }

        $utmFromRequest = $this->normalizeSourceCampaign($request->query('utm_source'));
        $storedUtm = session()->get($this->funnelUtmSessionKey($funnel->id), []);
        $utmFromSession = $this->normalizeSourceCampaign($storedUtm['utm_source'] ?? null);
        $referrerFromSession = $storedUtm['referrer'] ?? null;

        $resolvedSource = $utmFromRequest ?: $utmFromSession ?: $this->inferSourceFromReferrer($referrerFromSession);
        if ($resolvedSource) {
            $lead->source_campaign = $resolvedSource;
        }

        $lead->name = $name !== '' ? $name : $lead->name;
        $lead->phone = $phone !== '' ? $phone : ($lead->phone ?? '');
        $lead->tags = $this->mergeTags(
            $lead->tags ?? [],
            $funnel->default_tags ?? [],
            $step->step_tags ?? []
        );
        $lead->save();

        session()->put($this->leadSessionKey($funnel->id), $lead->id);

        // Fresh funnel flags (avoid stale model); DOI only when lead has never confirmed this email.
        $funnel->refresh();
        $needsDoubleOptIn = filter_var($funnel->getAttributes()['require_double_opt_in'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($needsDoubleOptIn && !$lead->hasVerifiedEmail()) {
            $next = $this->nextStep($steps, $step->id);
            session()->put("funnel_pending_step_{$funnel->id}", $next?->slug);

            // Double opt-in: send verification email, do not dispatch webhook or score yet.
            try {
                logger()->info('DOI: sending lead verification email', [
                    'lead_id' => $lead->id,
                    'lead_email' => $lead->email,
                    'funnel_id' => $funnel->id,
                    'funnel_require_double_opt_in' => $funnel->require_double_opt_in,
                ]);
                // Pass opt-in step id so after verification we can redirect to the next funnel step.
                $lead->notify(new LeadVerifyEmail($lead, (int) $funnel->id, $funnel->name, (int) $step->id));
                logger()->info('DOI: lead verification email notify() returned', [
                    'lead_id' => $lead->id,
                    'lead_email' => $lead->email,
                    'funnel_id' => $funnel->id,
                ]);
            } catch (\Throwable $e) {
                report($e);
                return redirect()->back()->withErrors([
                    'email' => 'We could not send the confirmation email. Please try again later or contact support.',
                ])->withInput();
            }

            return redirect()->route('funnels.lead.confirm-email', ['funnelSlug' => $funnel->slug]);
        }

        $lead->increment('score', 20);
        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => 'Form Submitted (+20 points)',
        ]);

        $this->dispatchFunnelOptInWebhook($lead, $funnel->id, $funnel->name ?? null);

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

        $leadId = $this->currentLeadId($funnel->id);
        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'lead_id' => $leadId,
            'amount' => $amount,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        $this->dispatchPaymentWebhookIfLeadExists($payment, 'payment.paid');

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
            $payment = Payment::create([
                'tenant_id' => $funnel->tenant_id,
                'lead_id' => $this->currentLeadId($funnel->id),
                'amount' => (float) $step->price,
                'status' => 'paid',
                'payment_date' => now()->toDateString(),
            ]);
            $this->dispatchPaymentWebhookIfLeadExists($payment, 'payment.paid');
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

    private function funnelUtmSessionKey(int $funnelId): string
    {
        return "funnel_utm_{$funnelId}";
    }

    private function normalizeSourceCampaign(?string $value): ?string
    {
        $v = trim((string) ($value ?? ''));
        if ($v === '') {
            return null;
        }

        // Keep it URL/DB friendly and consistent for analytics breakdowns.
        $v = preg_replace('/\s+/', '_', $v) ?? '';
        $v = mb_substr(trim($v), 0, 100);

        return $v !== '' ? $v : null;
    }

    private function inferSourceFromReferrer(?string $referrer): ?string
    {
        if (!$referrer) {
            return null;
        }

        $r = mb_strtolower($referrer);

        if (str_contains($r, 'facebook')) {
            return 'facebook';
        }

        if (str_contains($r, 'youtube')) {
            return 'youtube';
        }

        if (str_contains($r, 'instagram')) {
            return 'instagram';
        }

        if (str_contains($r, 'tiktok')) {
            return 'tiktok';
        }

        return null;
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

    private function dispatchFunnelOptInWebhook(Lead $lead, int $funnelId, ?string $funnelName): void
    {
        $service = app(AutomationWebhookService::class);
        $payload = $service->buildFunnelOptInPayload($lead, $funnelId, $funnelName, []);
        $service->dispatchEvent('funnel.opt_in', $payload);
    }

    private function dispatchPaymentWebhookIfLeadExists(Payment $payment, string $event): void
    {
        if (!$payment->lead_id) {
            return;
        }
        $lead = Lead::withoutGlobalScope('tenant')->where('id', $payment->lead_id)->where('tenant_id', $payment->tenant_id)->first();
        if (!$lead) {
            return;
        }
        $service = app(AutomationWebhookService::class);

        // MVP rule: payment.paid => Closed Won (and notify n8n via lead.status_changed).
        if ($event === 'payment.paid') {
            $oldStatus = (string) ($lead->status ?? '');
            $newStatus = 'closed_won';

            if ($oldStatus !== $newStatus && !in_array($oldStatus, ['closed_lost', 'closed_won'], true)) {
                $lead->status = $newStatus;
                $lead->save();

                $lead->activities()->create([
                    'activity_type' => 'Scoring',
                    'notes' => 'Auto: Pipeline Stage updated to closed_won (+0 points)',
                ]);

                $statusPayload = $service->buildLeadStatusChangedPayload($lead, $oldStatus, $newStatus, []);
                $service->dispatchEvent('lead.status_changed', $statusPayload);
            }
        }

        $payload = $service->buildPaymentPayload($event, $lead, $payment, []);
        $service->dispatchEvent($event, $payload);
    }
}
