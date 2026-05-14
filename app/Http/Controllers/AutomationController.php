<?php

namespace App\Http\Controllers;

use App\Models\OnboardingAuditLog;
use App\Services\N8nWorkflowControlService;
use App\Services\PlanAutomationService;
use App\Support\TenantPlanEnforcer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RuntimeException;

class AutomationController extends Controller
{
    public function index(N8nWorkflowControlService $workflowControlService)
    {
        $this->authorizeAutomationAccess();
        $canControlWorkflow = $this->canControlWorkflow();

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

        $deliverySummary = [
            'failed_last_24h' => 0,
            'sent_last_24h' => 0,
        ];
        $recentFailures = collect();
        if ($canControlWorkflow) {
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
                    ->where('event_type', 'onboarding_email_sent')
                    ->where('status', 'success')
                    ->where('occurred_at', '>=', now()->subDay())
                    ->count(),
            ];
        }

        return view('automation.index', [
            'status' => $status,
            'statusError' => $statusError,
            'recentFailures' => $recentFailures,
            'deliverySummary' => $deliverySummary,
            'canControlWorkflow' => $canControlWorkflow,
            'tenantAutomation' => auth()->user()->hasRole('account-owner')
                ? $this->buildTenantAutomationStatus(auth()->user()->tenant, $status)
                : null,
        ]);
    }

    public function toggle(Request $request, N8nWorkflowControlService $workflowControlService)
    {
        $this->authorizeGlobalWorkflowControl();

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

    private function canControlWorkflow(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }

    private function authorizeAutomationAccess(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'account-owner']),
            403,
            'You are not authorized to access automation.'
        );
    }

    private function authorizeGlobalWorkflowControl(): void
    {
        abort_unless(
            $this->canControlWorkflow(),
            403,
            'Only super admins can control the shared automation workflow.'
        );
    }

    /**
     * @param  array{configured: bool, active: bool|null, name: string|null, raw: array<string, mixed>|null}  $status
     * @return array<string, array<string, string>>
     */
    private function buildTenantAutomationStatus($tenant, array $status): array
    {
        $plan = $tenant ? app(TenantPlanEnforcer::class)->resolvePlan($tenant) : null;
        $planName = $plan?->name ?: ($tenant?->subscription_plan ?: 'No Plan');
        $automationIncluded = (bool) ($plan?->automation_enabled ?? false);
        $automationMode = app(PlanAutomationService::class)->modeForPlan($plan);
        $platformState = $this->platformStateSummary($status, $automationIncluded);
        $billingState = $this->billingStateSummary($tenant);

        $tenantLabel = 'Status Unknown';
        $tenantTone = 'neutral';
        $tenantSummary = 'Your tenant automation eligibility could not be confirmed yet.';

        if ($automationMode === PlanAutomationService::MODE_LIMITED) {
            $tenantLabel = 'Limited';
            $tenantTone = 'warning';
            $tenantSummary = sprintf(
                '%s includes shared n8n email automations plus limited built-in workflow automations. Upgrade to Growth or Scale to unlock the full shared n8n automation engine.',
                $planName
            );
        } elseif (! $automationIncluded) {
            $tenantLabel = 'Not Included';
            $tenantTone = 'warning';
            $tenantSummary = sprintf(
                '%s keeps automation in built-in tracking mode only. Upgrade to Growth or Scale to unlock the shared n8n automation engine.',
                $planName
            );
        } elseif (! $tenant || $tenant->status === 'inactive' || $tenant->billing_status === 'inactive') {
            $tenantLabel = 'Suspended';
            $tenantTone = 'danger';
            $tenantSummary = 'Automation is included in your plan, but your workspace is inactive so shared automation is suspended.';
        } elseif ($tenant->billing_status === 'overdue') {
            $tenantLabel = 'Billing Overdue';
            $tenantTone = 'warning';
            $tenantSummary = 'Automation is included in your plan, but billing is overdue. Shared automations can be interrupted if payment is not recovered.';
        } elseif (($status['active'] ?? null) === false) {
            $tenantLabel = 'Platform Paused';
            $tenantTone = 'warning';
            $tenantSummary = 'Your plan includes automation, but the shared platform workflow is currently paused by the platform team.';
        } elseif (($status['active'] ?? null) === true) {
            $tenantLabel = 'Active';
            $tenantTone = 'positive';
            $tenantSummary = sprintf(
                'Your %s plan includes the shared n8n automation engine and the platform workflow is currently online.',
                $planName
            );
        }

        return [
            'plan_access' => [
                'label' => $automationIncluded ? 'Included' : ($automationMode === PlanAutomationService::MODE_LIMITED ? 'Limited' : 'Not Included'),
                'tone' => $automationIncluded ? 'positive' : 'warning',
                'summary' => $automationIncluded
                    ? sprintf('%s includes access to the shared n8n automation engine.', $planName)
                    : ($automationMode === PlanAutomationService::MODE_LIMITED
                        ? sprintf('%s includes shared n8n email automations and limited workflow automations.', $planName)
                        : sprintf('%s does not include advanced shared n8n automation.', $planName)),
            ],
            'platform' => $platformState,
            'billing' => $billingState,
            'tenant' => [
                'label' => $tenantLabel,
                'tone' => $tenantTone,
                'summary' => $tenantSummary,
            ],
        ];
    }

    /**
     * @param  array{configured: bool, active: bool|null, name: string|null, raw: array<string, mixed>|null}  $status
     * @return array{label: string, tone: string, summary: string}
     */
    private function platformStateSummary(array $status, bool $automationIncluded): array
    {
        if (! ($status['configured'] ?? false)) {
            return [
                'label' => 'Not Configured',
                'tone' => 'warning',
                'summary' => 'The shared n8n workflow is not fully configured on the platform yet.',
            ];
        }

        if (($status['active'] ?? null) === true) {
            return [
                'label' => 'Online',
                'tone' => 'positive',
                'summary' => $automationIncluded
                    ? 'The shared n8n automation engine is online for eligible tenants.'
                    : 'The shared n8n automation engine is online, but your plan still governs whether you can use it.',
            ];
        }

        if (($status['active'] ?? null) === false) {
            return [
                'label' => 'Paused',
                'tone' => 'warning',
                'summary' => 'The shared n8n automation engine is currently paused by the platform team.',
            ];
        }

        return [
            'label' => 'Unknown',
            'tone' => 'neutral',
            'summary' => 'The shared n8n workflow could not be reached, so platform automation status is unknown right now.',
        ];
    }

    /**
     * @return array{label: string, tone: string, summary: string}
     */
    private function billingStateSummary($tenant): array
    {
        if (! $tenant) {
            return [
                'label' => 'No Workspace',
                'tone' => 'neutral',
                'summary' => 'No tenant workspace is linked to this account yet.',
            ];
        }

        if ($tenant->status === 'inactive' || $tenant->billing_status === 'inactive') {
            return [
                'label' => 'Inactive',
                'tone' => 'danger',
                'summary' => 'Your workspace is inactive, so platform-managed automations are suspended until billing is restored.',
            ];
        }

        if ($tenant->billing_status === 'overdue') {
            $deadline = $tenant->billing_grace_ends_at instanceof Carbon
                ? ' Grace period ends on '.$tenant->billing_grace_ends_at->format('F j, Y g:i A').'.'
                : '';

            return [
                'label' => 'Overdue',
                'tone' => 'warning',
                'summary' => 'Billing is overdue for this workspace.'.$deadline,
            ];
        }

        if ($tenant->status === 'trial' || $tenant->billing_status === 'trial') {
            $deadline = $tenant->trial_ends_at instanceof Carbon
                ? ' Trial ends on '.$tenant->trial_ends_at->format('F j, Y g:i A').'.'
                : '';

            return [
                'label' => 'Trial',
                'tone' => 'neutral',
                'summary' => 'This workspace is currently in trial mode.'.$deadline,
            ];
        }

        return [
            'label' => 'Active',
            'tone' => 'positive',
            'summary' => 'Billing is current and the workspace is eligible to keep using plan-approved automations.',
        ];
    }
}
