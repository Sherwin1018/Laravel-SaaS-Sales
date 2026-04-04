<?php

namespace App\Http\Controllers;

use App\Models\OnboardingAuditLog;
use App\Services\N8nWorkflowControlService;
use App\Support\TenantPlanEnforcer;
use Illuminate\Http\Request;
use RuntimeException;

class AutomationController extends Controller
{
    public function index(N8nWorkflowControlService $workflowControlService)
    {
        if (! $this->canBypassPlanEnforcement()) {
            app(TenantPlanEnforcer::class)->ensureAutomationEnabled(auth()->user()->tenant);
        }

        $statusError = null;
        try {
            $status = $workflowControlService->status();
        } catch (\Throwable $e) {
            $status = [
                'configured' => $workflowControlService->isConfigured(),
                'active' => null,
                'name' => null,
                'raw' => null,
            ];
            $statusError = $e->getMessage();
        }
        $recentFailures = OnboardingAuditLog::query()
            ->whereIn('event_type', ['onboarding_email_failed', 'onboarding_email_callback'])
            ->where('status', 'failed')
            ->latest('occurred_at')
            ->take(8)
            ->get();

        $deliverySummary = [
            'failed_last_24h' => OnboardingAuditLog::query()
                ->whereIn('event_type', ['onboarding_email_failed', 'onboarding_email_callback'])
                ->where('status', 'failed')
                ->where('occurred_at', '>=', now()->subDay())
                ->count(),
            'sent_last_24h' => OnboardingAuditLog::query()
                ->whereIn('event_type', ['onboarding_email_sent', 'onboarding_email_callback'])
                ->where('status', 'success')
                ->where('occurred_at', '>=', now()->subDay())
                ->count(),
        ];

        return view('automation.index', [
            'status' => $status,
            'statusError' => $statusError,
            'recentFailures' => $recentFailures,
            'deliverySummary' => $deliverySummary,
        ]);
    }

    public function toggle(Request $request, N8nWorkflowControlService $workflowControlService)
    {
        if (! $this->canBypassPlanEnforcement()) {
            app(TenantPlanEnforcer::class)->ensureAutomationEnabled(auth()->user()->tenant);
        }

        $validated = $request->validate([
            'active' => 'required|boolean',
        ]);

        try {
            $updated = $validated['active']
                ? $workflowControlService->activate()
                : $workflowControlService->deactivate();

            if (! $updated) {
                return back()->with('error', 'Could not update n8n workflow state.');
            }

            return back()->with('success', $validated['active']
                ? 'Automation workflow has been turned on.'
                : 'Automation workflow has been turned off.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return back()->with('error', 'Unable to reach n8n right now. Please try again.');
        }
    }

    private function canBypassPlanEnforcement(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }
}
