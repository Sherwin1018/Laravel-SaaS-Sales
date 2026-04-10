<?php

namespace App\Http\Controllers;

use App\Models\AutomationLog;
use App\Models\AutomationSequence;
use App\Models\AutomationSequenceStep;
use App\Models\AutomationWorkflow;
use App\Models\Funnel;
use App\Models\OnboardingAuditLog;
use App\Services\N8nWorkflowControlService;
use App\Support\TenantPlanEnforcer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class AutomationController extends Controller
{
    private function tenantId(): int
    {
        return (int) auth()->user()->tenant_id;
    }

    public function overview()
    {
        $tenantId = $this->tenantId();
        $workflows = AutomationWorkflow::where('tenant_id', $tenantId)->get();
        $activeCount = $workflows->where('is_active', true)->count();
        $logs = AutomationLog::where('tenant_id', $tenantId)->where('ran_at', '>=', now()->subDays(30))->get();
        $failedRuns = $logs->where('status', 'failed')->count();
        $emailsSentToday = $logs->where('status', 'success')->where('ran_at', '>=', now()->startOfDay())->count();
        $emailsSent7Days = $logs->where('status', 'success')->where('ran_at', '>=', now()->subDays(7))->count();

        $recentActivity = AutomationLog::where('tenant_id', $tenantId)
            ->with('workflow')
            ->orderByDesc('ran_at')
            ->limit(10)
            ->get()
            ->map(fn (AutomationLog $log) => [
                'id' => (string) $log->id,
                'name' => $log->workflow?->name ?? $log->event,
                'trigger' => $this->triggerEventLabel($log->event),
                'time' => $log->ran_at->toIso8601String(),
                'status' => $log->status,
            ])
            ->all();

        return view('automation.overview', [
            'stats' => [
                'active_automations' => $activeCount,
                'emails_sent_today' => $emailsSentToday,
                'emails_sent_7_days' => $emailsSent7Days,
                'leads_triggered' => $emailsSent7Days,
                'failed_runs' => $failedRuns,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }

    public function index(N8nWorkflowControlService $workflowControlService)
    {
        $this->authorizeAutomationAccess();

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
        $this->authorizeAutomationAccess();

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

    public function sequences(Request $request)
    {
        $tenantId = $this->tenantId();
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status', '');

        $query = AutomationSequence::where('tenant_id', $tenantId)->withCount('steps')->orderByDesc('updated_at');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'paused') {
            $query->where('is_active', false);
        }
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $sequences = $query->get();
        $sequencesForView = $sequences->map(fn (AutomationSequence $sequence) => [
            'id' => $sequence->id,
            'name' => $sequence->name,
            'steps_count' => $sequence->steps_count ?? $sequence->steps()->count(),
            'status' => $sequence->is_active ? 'active' : 'paused',
            'updated' => $sequence->updated_at->toIso8601String(),
        ])->all();

        return view('automation.sequences.index', [
            'sequences' => $sequencesForView,
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function createSequenceBuilder()
    {
        return view('automation.sequences.builder', [
            'sequence' => null,
            'steps' => [],
        ]);
    }

    public function storeSequence(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
            'steps' => 'required|string',
        ]);

        $stepsData = json_decode($validated['steps'], true);
        if (! is_array($stepsData) || count($stepsData) < 1) {
            return redirect()->back()->withInput()->withErrors(['steps' => 'At least one step is required.']);
        }
        $this->validateSequenceSteps($stepsData);

        $sequence = AutomationSequence::create([
            'tenant_id' => $this->tenantId(),
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        foreach ($stepsData as $i => $step) {
            AutomationSequenceStep::create([
                'sequence_id' => $sequence->id,
                'step_order' => $i + 1,
                'type' => $step['type'] ?? 'email',
                'config' => $step['config'] ?? [],
            ]);
        }

        return redirect()->route('automation.sequences.index')->with('success', 'Sequence saved.');
    }

    public function editSequenceBuilder(AutomationSequence $sequence)
    {
        $this->ensureTenantSequence($sequence);
        $sequence->load('steps');
        $steps = $sequence->steps->map(fn (AutomationSequenceStep $step) => [
            'type' => $step->type,
            'config' => $step->config ?? [],
            'description' => $this->stepDescriptionForView($step),
        ])->all();

        $workflowsUsing = AutomationWorkflow::where('tenant_id', $this->tenantId())
            ->where('type', AutomationWorkflow::TYPE_TENANT)
            ->where('action_type', 'start_sequence')
            ->where('action_config->sequence_id', $sequence->id)
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);

        return view('automation.sequences.builder', [
            'sequence' => $sequence,
            'steps' => $steps,
            'sequenceWorkflows' => $workflowsUsing,
        ]);
    }

    public function updateSequence(Request $request, AutomationSequence $sequence)
    {
        $this->ensureTenantSequence($sequence);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
            'steps' => 'required|string',
        ]);

        $stepsData = json_decode($validated['steps'], true);
        if (! is_array($stepsData) || count($stepsData) < 1) {
            return redirect()->back()->withInput()->withErrors(['steps' => 'At least one step is required.']);
        }
        $this->validateSequenceSteps($stepsData);

        $sequence->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $sequence->steps()->delete();
        foreach ($stepsData as $i => $step) {
            AutomationSequenceStep::create([
                'sequence_id' => $sequence->id,
                'step_order' => $i + 1,
                'type' => $step['type'] ?? 'email',
                'config' => $step['config'] ?? [],
            ]);
        }

        return redirect()->route('automation.sequences.index')->with('success', 'Sequence updated.');
    }

    public function toggleSequence(AutomationSequence $sequence)
    {
        $this->ensureTenantSequence($sequence);
        $sequence->update(['is_active' => ! $sequence->is_active]);

        return redirect()->route('automation.sequences.index')
            ->with('success', $sequence->is_active ? 'Sequence activated.' : 'Sequence paused.');
    }

    public function destroySequence(AutomationSequence $sequence)
    {
        $this->ensureTenantSequence($sequence);
        $sequence->delete();

        return redirect()->route('automation.sequences.index')->with('success', 'Sequence deleted.');
    }

    public function workflows(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status', '');

        $query = AutomationWorkflow::where('tenant_id', $this->tenantId())
            ->where('type', AutomationWorkflow::TYPE_TENANT)
            ->orderByDesc('updated_at');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'paused') {
            $query->where('is_active', false);
        }
        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $workflows = $query->get();
        $sequences = AutomationSequence::where('tenant_id', $this->tenantId())->get(['id', 'name']);
        $workflowsForView = $workflows->map(fn (AutomationWorkflow $workflow) => [
            'id' => $workflow->id,
            'name' => $workflow->name,
            'trigger' => $workflow->trigger_event,
            'trigger_label' => $this->triggerEventLabel($workflow->trigger_event),
            'recipient' => $workflow->action_config['recipient'] ?? 'lead.email',
            'recipient_label' => $this->recipientLabel($workflow->action_config['recipient'] ?? null),
            'action_label' => $this->workflowActionDisplayLabel($workflow, $sequences),
            'status' => $workflow->is_active ? 'active' : 'paused',
            'updated' => $workflow->updated_at->toIso8601String(),
            'type' => $workflow->type,
        ])->all();

        return view('automation.workflows.index', [
            'workflows' => $workflowsForView,
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function createWorkflow()
    {
        return view('automation.workflows.create', [
            'workflow' => null,
            'sequences' => AutomationSequence::where('tenant_id', $this->tenantId())->orderBy('name')->get(['id', 'name']),
            'funnels' => Funnel::where('tenant_id', $this->tenantId())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeWorkflow(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'trigger' => ['required', Rule::in(['lead.created', 'lead.status_changed', 'funnel.opt_in', 'payment.paid', 'payment.failed'])],
            'conditions_note' => 'nullable|string|max:500',
            'funnel_id' => 'nullable|integer',
            'action_type' => ['required', Rule::in(['send_email', 'notify_sales', 'start_sequence'])],
            'recipient' => ['nullable', Rule::in(['lead.email', 'assigned_agent.email'])],
            'sequence_id' => ['nullable', 'required_if:action_type,start_sequence', 'integer', Rule::exists('automation_sequences', 'id')->where('tenant_id', $this->tenantId())],
            'email_subject' => 'nullable|string|max:255',
            'email_body' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        $triggerFilters = [];
        if (! empty($validated['conditions_note'])) {
            $triggerFilters['note'] = $validated['conditions_note'];
        }
        if ($validated['trigger'] === 'funnel.opt_in' && ! empty($validated['funnel_id'])) {
            $triggerFilters['funnel_id'] = (int) $validated['funnel_id'];
        }

        $actionConfig = [];
        if ($validated['action_type'] === 'send_email') {
            $actionConfig['recipient'] = $validated['recipient'] ?? 'lead.email';
            $actionConfig['subject'] = $validated['email_subject'] ?? '';
            $actionConfig['body'] = $validated['email_body'] ?? '';
        } elseif ($validated['action_type'] === 'start_sequence' && ! empty($validated['sequence_id'])) {
            $actionConfig['sequence_id'] = (int) $validated['sequence_id'];
        }

        AutomationWorkflow::create([
            'tenant_id' => $this->tenantId(),
            'name' => $validated['name'],
            'type' => AutomationWorkflow::TYPE_TENANT,
            'trigger_event' => $validated['trigger'],
            'trigger_filters' => $triggerFilters ?: null,
            'action_type' => $validated['action_type'],
            'action_config' => $actionConfig ?: null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('automation.workflows.index')->with('success', 'Workflow saved.');
    }

    public function editWorkflow(AutomationWorkflow $workflow)
    {
        $this->ensureTenantWorkflow($workflow);
        if (! $workflow->isTenant()) {
            abort(403, 'System workflows cannot be edited.');
        }

        return view('automation.workflows.edit', [
            'workflow' => $workflow,
            'sequences' => AutomationSequence::where('tenant_id', $this->tenantId())->orderBy('name')->get(['id', 'name']),
            'funnels' => Funnel::where('tenant_id', $this->tenantId())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function updateWorkflow(Request $request, AutomationWorkflow $workflow)
    {
        $this->ensureTenantWorkflow($workflow);
        if (! $workflow->isTenant()) {
            abort(403, 'System workflows cannot be updated.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'trigger' => ['required', Rule::in(['lead.created', 'lead.status_changed', 'funnel.opt_in', 'payment.paid', 'payment.failed'])],
            'conditions_note' => 'nullable|string|max:500',
            'funnel_id' => 'nullable|integer',
            'action_type' => ['required', Rule::in(['send_email', 'notify_sales', 'start_sequence'])],
            'recipient' => ['nullable', Rule::in(['lead.email', 'assigned_agent.email'])],
            'sequence_id' => ['nullable', 'required_if:action_type,start_sequence', 'integer', Rule::exists('automation_sequences', 'id')->where('tenant_id', $this->tenantId())],
            'email_subject' => 'nullable|string|max:255',
            'email_body' => 'nullable|string|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        $triggerFilters = [];
        if (! empty($validated['conditions_note'])) {
            $triggerFilters['note'] = $validated['conditions_note'];
        }
        if ($validated['trigger'] === 'funnel.opt_in' && ! empty($validated['funnel_id'])) {
            $triggerFilters['funnel_id'] = (int) $validated['funnel_id'];
        }

        $actionConfig = [];
        if ($validated['action_type'] === 'send_email') {
            $actionConfig['recipient'] = $validated['recipient'] ?? 'lead.email';
            $actionConfig['subject'] = $validated['email_subject'] ?? '';
            $actionConfig['body'] = $validated['email_body'] ?? '';
        } elseif ($validated['action_type'] === 'start_sequence' && ! empty($validated['sequence_id'])) {
            $actionConfig['sequence_id'] = (int) $validated['sequence_id'];
        }

        $workflow->update([
            'name' => $validated['name'],
            'trigger_event' => $validated['trigger'],
            'trigger_filters' => $triggerFilters ?: null,
            'action_type' => $validated['action_type'],
            'action_config' => $actionConfig ?: null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('automation.workflows.index')->with('success', 'Workflow updated.');
    }

    public function toggleWorkflow(AutomationWorkflow $workflow)
    {
        $this->ensureTenantWorkflow($workflow);
        if (! $workflow->isTenant()) {
            abort(403, 'System workflows cannot be toggled.');
        }

        $workflow->update(['is_active' => ! $workflow->is_active]);

        return redirect()->route('automation.workflows.index')
            ->with('success', $workflow->is_active ? 'Workflow activated.' : 'Workflow paused.');
    }

    public function duplicateWorkflow(AutomationWorkflow $workflow)
    {
        $this->ensureTenantWorkflow($workflow);
        if (! $workflow->isTenant()) {
            abort(403, 'System workflows cannot be duplicated.');
        }

        AutomationWorkflow::create([
            'tenant_id' => $this->tenantId(),
            'name' => $workflow->name . ' (Copy)',
            'type' => AutomationWorkflow::TYPE_TENANT,
            'trigger_event' => $workflow->trigger_event,
            'trigger_filters' => $workflow->trigger_filters,
            'action_type' => $workflow->action_type,
            'action_config' => $workflow->action_config,
            'is_active' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('automation.workflows.index')->with('success', 'Workflow duplicated.');
    }

    public function destroyWorkflow(AutomationWorkflow $workflow)
    {
        $this->ensureTenantWorkflow($workflow);
        if (! $workflow->isTenant()) {
            abort(403, 'System workflows cannot be deleted.');
        }

        $workflow->delete();

        return redirect()->route('automation.workflows.index')->with('success', 'Workflow deleted.');
    }

    public function logs(Request $request)
    {
        $dateRange = $request->get('date_range', '7');
        $type = $request->get('type', '');
        $resultStatus = $request->get('result', '');

        $query = AutomationLog::where('tenant_id', $this->tenantId())->with('workflow')->orderByDesc('ran_at');
        if ($dateRange === 'today') {
            $query->where('ran_at', '>=', now()->startOfDay());
        } elseif ($dateRange === '30') {
            $query->where('ran_at', '>=', now()->subDays(30));
        } else {
            $query->where('ran_at', '>=', now()->subDays(7));
        }
        if ($resultStatus === 'success') {
            $query->where('status', 'success');
        } elseif ($resultStatus === 'failed') {
            $query->where('status', 'failed');
        }

        $logsForView = $query->get()->map(function (AutomationLog $log) use ($type) {
            $logType = $log->workflow_id ? 'workflow' : 'sequence';
            if ($type !== '' && $logType !== $type) {
                return null;
            }

            return [
                'id' => (string) $log->id,
                'time' => $log->ran_at->toIso8601String(),
                'automation_name' => $log->workflow?->name ?? $log->event,
                'trigger' => $this->triggerEventLabel($log->event),
                'result' => $log->status,
                'type' => $logType,
                'details' => array_filter([
                    'event' => $log->event,
                    'error_message' => $log->error_message,
                    'payload' => $log->payload,
                ]),
            ];
        })->filter()->values()->all();

        return view('automation.logs.index', [
            'logs' => $logsForView,
            'filters' => ['date_range' => $dateRange, 'type' => $type, 'result' => $resultStatus],
        ]);
    }

    public function showLog(string $id)
    {
        $log = AutomationLog::where('tenant_id', $this->tenantId())->where('id', $id)->with('workflow')->first();
        if (! $log) {
            abort(404);
        }

        $logType = $log->workflow_id ? 'workflow' : 'sequence';
        $logForView = [
            'id' => (string) $log->id,
            'time' => $log->ran_at->toIso8601String(),
            'automation_name' => $log->workflow?->name ?? $log->event,
            'trigger' => $this->triggerEventLabel($log->event),
            'result' => $log->status,
            'type' => $logType,
            'details' => array_filter([
                'event' => $log->event,
                'status' => $log->status,
                'error_message' => $log->error_message,
                'payload' => $log->payload,
            ]),
        ];

        return view('automation.logs.show', ['log' => $logForView]);
    }

    private function ensureTenantSequence(AutomationSequence $sequence): void
    {
        if ($sequence->tenant_id !== $this->tenantId()) {
            abort(403, 'Unauthorized.');
        }
    }

    private function validateSequenceSteps(array $stepsData): void
    {
        $allowedTypes = ['email', 'delay'];
        foreach ($stepsData as $i => $step) {
            $type = $step['type'] ?? 'email';
            if (! in_array($type, $allowedTypes, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'steps' => 'Step ' . ($i + 1) . ': only email and delay steps are allowed.',
                ]);
            }
            if ($i === 0 && $type === 'delay') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'steps' => 'The first step cannot be a delay.',
                ]);
            }
        }
    }

    private function stepDescriptionForView(AutomationSequenceStep $step): string
    {
        $config = $step->config ?? [];
        if ($step->type === 'email') {
            return $config['subject'] ?? 'Send email';
        }
        if ($step->type === 'delay') {
            return 'Wait ' . ($config['duration'] ?? 1) . ' ' . ($config['unit'] ?? 'days');
        }

        return 'Step';
    }

    private function ensureTenantWorkflow(AutomationWorkflow $workflow): void
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(403, 'Unauthorized.');
        }
    }

    private function triggerEventLabel(?string $event): string
    {
        if ($event === null || $event === '') {
            return '-';
        }

        return match ($event) {
            'lead.created' => 'Lead created',
            'lead.status_changed' => 'Lead status changed',
            'funnel.opt_in' => 'Funnel opt-in',
            'payment.paid' => 'Payment paid',
            'payment.failed' => 'Payment failed',
            default => $event,
        };
    }

    private function actionTypeLabel(?string $action): string
    {
        if ($action === null || $action === '') {
            return '-';
        }

        return match ($action) {
            'send_email' => 'Send Email',
            'start_sequence' => 'Start Sequence',
            'notify_sales' => 'Notify Sales Agent',
            default => $action,
        };
    }

    private function recipientLabel(?string $recipient): string
    {
        if ($recipient === null || $recipient === '') {
            return '-';
        }

        return match ($recipient) {
            'lead.email' => 'Lead email',
            'assigned_agent.email' => 'Assigned agent email',
            default => $recipient,
        };
    }

    private function workflowActionDisplayLabel(AutomationWorkflow $workflow, \Illuminate\Support\Collection $sequences): string
    {
        $action = $workflow->action_type ?? '';
        $config = $workflow->action_config ?? [];
        if ($action === 'send_email') {
            return 'Send Email (' . $this->recipientLabel($config['recipient'] ?? 'lead.email') . ')';
        }
        if ($action === 'start_sequence') {
            $seqId = $config['sequence_id'] ?? null;
            $name = $seqId ? $sequences->firstWhere('id', $seqId)?->name : null;
            return 'Start Sequence' . ($name ? ' (' . $name . ')' : '');
        }
        if ($action === 'notify_sales') {
            return 'Notify Sales Agent';
        }

        return $this->actionTypeLabel($action);
    }

    private function canBypassPlanEnforcement(): bool
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
}
