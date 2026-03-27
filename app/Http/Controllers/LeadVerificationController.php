<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\Lead;
use App\Services\AutomationWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadVerificationController extends Controller
{
    public function __construct(
        protected AutomationWebhookService $webhookService
    ) {}

    /**
     * Verify lead email (called when lead clicks the link in the verification email).
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'hash' => 'required|string',
            'funnel_id' => 'nullable|integer',
            'opt_in_step_id' => 'nullable|integer',
        ]);

        $lead = Lead::withoutGlobalScope('tenant')->find($request->id);

        if (!$lead) {
            return redirect('/')->with('error', 'Invalid or expired verification link.');
        }

        if ($lead->hasVerifiedEmail()) {
            return redirect()->route('funnels.lead.verified')->with('success', 'Your email is already verified.');
        }

        if (!hash_equals((string) $request->hash, sha1($lead->getEmailForVerification()))) {
            return redirect('/')->with('error', 'Invalid or expired verification link.');
        }

        $lead->forceFill(['email_verified_at' => now()])->save();

        $lead->increment('score', 20);
        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => 'Email Verified (+20 points)',
        ]);

        $funnelId = (int) ($request->funnel_id ?? 0);
        $optInStepId = (int) ($request->opt_in_step_id ?? 0);
        $funnelName = null;
        $funnelSlug = null;
        if ($funnelId > 0) {
            $funnel = Funnel::find($funnelId);
            $funnelName = $funnel?->name;
            $funnelSlug = $funnel?->slug;
        }

        $payload = $this->webhookService->buildFunnelOptInPayload($lead, $funnelId ?: null, $funnelName, []);
        $this->webhookService->dispatchEvent('funnel.opt_in', $payload);

        // Make sure subsequent funnel steps can read the current lead from session.
        if ($funnelId > 0) {
            session()->put("funnel_lead_{$funnelId}", $lead->id);
        }

        $resolvedRedirect = $this->resolvePostVerificationRedirect($funnel, $optInStepId);
        if ($resolvedRedirect !== null) {
            return redirect()->to($resolvedRedirect);
        }

        // Fallback: if we can't resolve the next step, show success page.
        return redirect()->route('funnels.lead.verified')->with('success', 'Your email has been verified. Thank you!');
    }

    /**
     * Show the "email verified" confirmation page.
     */
    public function verified()
    {
        return view('funnels.portal.lead-verified');
    }

    /**
     * Show the "check your email" page (when DOI is enabled and lead just submitted the form).
     */
    public function confirmEmail(string $funnelSlug)
    {
        $funnel = Funnel::where('slug', $funnelSlug)->where('status', 'published')->first();
        $funnelId = (int) ($funnel?->id ?? 0);
        $leadId = (int) session()->get("funnel_lead_{$funnelId}", 0);
        $hasPendingVerification = $funnelId > 0 && $leadId > 0;

        return view('funnels.portal.confirm-email', [
            'funnel' => $funnel,
            'hasPendingVerification' => $hasPendingVerification,
        ]);
    }

    /**
     * Polling endpoint for the confirm-email page to detect cross-tab verification completion.
     */
    public function confirmEmailStatus(string $funnelSlug): JsonResponse
    {
        $funnel = Funnel::where('slug', $funnelSlug)->where('status', 'published')->first();
        if (!$funnel) {
            return response()->json([
                'verified' => false,
                'message' => 'Funnel not found.',
            ], 404);
        }

        $leadId = (int) session()->get("funnel_lead_{$funnel->id}", 0);
        if ($leadId <= 0) {
            return response()->json([
                'verified' => false,
                'message' => 'No pending lead verification in this browser session.',
            ]);
        }

        $lead = Lead::withoutGlobalScope('tenant')
            ->where('id', $leadId)
            ->where('tenant_id', $funnel->tenant_id)
            ->first();

        if (!$lead || !$lead->hasVerifiedEmail()) {
            return response()->json([
                'verified' => false,
                'message' => 'Still waiting for email verification.',
            ]);
        }

        $redirectTo = $this->resolvePostVerificationRedirect($funnel, 0)
            ?? route('funnels.portal.step', ['funnelSlug' => $funnel->slug])
            ?? route('funnels.lead.verified');

        return response()->json([
            'verified' => true,
            'redirect_to' => $redirectTo,
        ]);
    }

    private function resolvePostVerificationRedirect(?Funnel $funnel, int $optInStepId): ?string
    {
        if (!$funnel) {
            return null;
        }

        $funnelSlug = (string) $funnel->slug;
        if ($funnelSlug === '') {
            return null;
        }

        $steps = $funnel->steps()->where('is_active', true)->orderBy('position')->get()->values();
        if ($steps->isEmpty()) {
            return null;
        }

        if ($optInStepId > 0) {
            $idx = $steps->search(fn ($s) => (int) $s->id === (int) $optInStepId);
            if ($idx !== false) {
                $nextStep = $steps->get($idx + 1);
                if ($nextStep) {
                    return route('funnels.portal.step', [
                        'funnelSlug' => $funnelSlug,
                        'stepSlug' => $nextStep->slug,
                    ]);
                }

                $lastStep = $steps->last();
                if ($lastStep) {
                    return route('funnels.portal.step', [
                        'funnelSlug' => $funnelSlug,
                        'stepSlug' => $lastStep->slug,
                    ]);
                }
            }
        }

        $pendingStepSlug = trim((string) session()->get("funnel_pending_step_{$funnel->id}", ''));
        if ($pendingStepSlug !== '') {
            $matchedStep = $steps->firstWhere('slug', $pendingStepSlug);
            if ($matchedStep) {
                return route('funnels.portal.step', [
                    'funnelSlug' => $funnelSlug,
                    'stepSlug' => $matchedStep->slug,
                ]);
            }
        }

        $firstStep = $steps->first();
        return $firstStep
            ? route('funnels.portal.step', ['funnelSlug' => $funnelSlug, 'stepSlug' => $firstStep->slug])
            : null;
    }
}
