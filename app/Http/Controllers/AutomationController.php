<?php

namespace App\Http\Controllers;

use App\Models\AutomationLog;
use App\Models\AutomationSequence;
use App\Models\AutomationSequenceStep;
use App\Models\AutomationWorkflow;
use App\Models\Funnel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Tenant-side Automation UI: Overview, Sequences, Workflows, Logs.
 * Workflows are tenant-scoped; only type=tenant workflows can be created/edited. System workflows are read-only.
 */
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
                'leads_triggered' => $emailsSent7Days, // placeholder; can be refined when events are detailed
                'failed_runs' => $failedRuns,
            ],
            'recentActivity' => $recentActivity,
        ]);
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

        $sequencesForView = $sequences->map(fn (AutomationSequence $s) => [
            'id' => $s->id,
            'name' => $s->name,
            'steps_count' => $s->steps_count ?? $s->steps()->count(),
            'status' => $s->is_active ? 'active' : 'paused',
            'updated' => $s->updated_at->toIso8601String(),
        ])->all();

        return view('automation.sequences.index', [
            'sequences' => $sequencesForView,
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    /**
     * Show the full-page Sequence Builder (create new sequence).
     */
    public function createSequenceBuilder()
    {
        return view('automation.sequences.builder', [
            'sequence' => null,
            'steps' => [],
        ]);
    }

    /**
     * Store sequence and steps to database.
     */
    public function storeSequence(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
            'steps' => 'required|string',
        ]);

        $stepsData = json_decode($validated['steps'], true);
        if (!is_array($stepsData) || count($stepsData) < 1) {
            return redirect()->back()->withInput()->withErrors(['steps' => 'At least one step is required.']);
        }
        $this->validateSequenceSteps($stepsData);

        $tenantId = $this->tenantId();
        $sequence = AutomationSequence::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        foreach ($stepsData as $i => $step) {
            $config = $step['config'] ?? [];
            AutomationSequenceStep::create([
                'sequence_id' => $sequence->id,
                'step_order' => $i + 1,
                'type' => $step['type'] ?? 'email',
                'config' => $config,
            ]);
        }

        return redirect()->route('automation.sequences.index')->with('success', 'Sequence saved.');
    }

    /**
     * Show the Sequence Builder in edit mode.
     */
    public function editSequenceBuilder(AutomationSequence $sequence)
    {
        $this->ensureTenantSequence($sequence);
        $sequence->load('steps');
        $steps = $sequence->steps->map(fn (AutomationSequenceStep $step) => [
            'type' => $step->type,
            'config' => $step->config ?? [],
            'description' => $this->stepDescriptionForView($step),
        ])->all();

        $tenantId = $this->tenantId();
        $workflowsUsing = AutomationWorkflow::where('tenant_id', $tenantId)
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

    /**
     * Update sequence and steps.
     */
    public function updateSequence(Request $request, AutomationSequence $sequence)
    {
        $this->ensureTenantSequence($sequence);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'is_active' => 'nullable|boolean',
            'steps' => 'required|string',
        ]);

        $stepsData = json_decode($validated['steps'], true);
        if (!is_array($stepsData) || count($stepsData) < 1) {
            return redirect()->back()->withInput()->withErrors(['steps' => 'At least one step is required.']);
        }
        $this->validateSequenceSteps($stepsData);

        $sequence->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $sequence->steps()->delete();
        foreach ($stepsData as $i => $step) {
            $config = $step['config'] ?? [];
            AutomationSequenceStep::create([
                'sequence_id' => $sequence->id,
                'step_order' => $i + 1,
                'type' => $step['type'] ?? 'email',
                'config' => $config,
            ]);
        }

        return redirect()->route('automation.sequences.index')->with('success', 'Sequence updated.');
    }

    /**
     * Toggle sequence active/paused.
     */
    public function toggleSequence(AutomationSequence $sequence)
    {
        $this->ensureTenantSequence($sequence);
        $sequence->update(['is_active' => !$sequence->is_active]);
        return redirect()->route('automation.sequences.index')
            ->with('success', $sequence->is_active ? 'Sequence activated.' : 'Sequence paused.');
    }

    /**
     * Delete sequence.
     */
    public function destroySequence(AutomationSequence $sequence)
    {
        $this->ensureTenantSequence($sequence);
        $sequence->delete();
        return redirect()->route('automation.sequences.index')->with('success', 'Sequence deleted.');
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
            if (!in_array($type, $allowedTypes, true)) {
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
            $d = $config['duration'] ?? 1;
            $u = $config['unit'] ?? 'days';
            return 'Wait ' . $d . ' ' . $u;
        }
        return 'Step';
    }

    public function workflows(Request $request)
    {
        $tenantId = $this->tenantId();
        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status', '');

        $query = AutomationWorkflow::where('tenant_id', $tenantId)
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
        $sequences = AutomationSequence::where('tenant_id', $tenantId)->get(['id', 'name']);

        $workflowsForView = $workflows->map(fn (AutomationWorkflow $w) => [
            'id' => $w->id,
            'name' => $w->name,
            'trigger' => $w->trigger_event,
            'trigger_label' => $this->triggerEventLabel($w->trigger_event),
            'recipient' => $w->action_config['recipient'] ?? 'lead.email',
            'recipient_label' => $this->recipientLabel($w->action_config['recipient'] ?? null),
            'action_label' => $this->workflowActionDisplayLabel($w, $sequences),
            'status' => $w->is_active ? 'active' : 'paused',
            'updated' => $w->updated_at->toIso8601String(),
            'type' => $w->type,
        ])->all();

        return view('automation.workflows.index', [
            'workflows' => $workflowsForView,
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function createWorkflow()
    {
        $tenantId = $this->tenantId();
        $sequences = AutomationSequence::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);
        $funnels = Funnel::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);
        return view('automation.workflows.create', [
            'workflow' => null,
            'sequences' => $sequences,
            'funnels' => $funnels,
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

        $tenantId = $this->tenantId();
        $triggerFilters = [];
        if (!empty($validated['conditions_note'])) {
            $triggerFilters['note'] = $validated['conditions_note'];
        }
        if ($validated['trigger'] === 'funnel.opt_in' && !empty($validated['funnel_id'])) {
            $triggerFilters['funnel_id'] = (int) $validated['funnel_id'];
        }
        $actionConfig = [];
        if ($validated['action_type'] === 'send_email') {
            $actionConfig['recipient'] = $validated['recipient'] ?? 'lead.email';
            $actionConfig['subject'] = $validated['email_subject'] ?? '';
            $actionConfig['body'] = $validated['email_body'] ?? '';
        } elseif ($validated['action_type'] === 'start_sequence' && !empty($validated['sequence_id'])) {
            $actionConfig['sequence_id'] = (int) $validated['sequence_id'];
        }

        AutomationWorkflow::create([
            'tenant_id' => $tenantId,
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
        if (!$workflow->isTenant()) {
            abort(403, 'System workflows cannot be edited.');
        }
        $tenantId = $this->tenantId();
        $sequences = AutomationSequence::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);
        $funnels = Funnel::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);
        return view('automation.workflows.edit', [
            'workflow' => $workflow,
            'sequences' => $sequences,
            'funnels' => $funnels,
        ]);
    }

    public function updateWorkflow(Request $request, AutomationWorkflow $workflow)
    {
        $this->ensureTenantWorkflow($workflow);
        if (!$workflow->isTenant()) {
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
        if (!empty($validated['conditions_note'])) {
            $triggerFilters['note'] = $validated['conditions_note'];
        }
        if ($validated['trigger'] === 'funnel.opt_in' && !empty($validated['funnel_id'])) {
            $triggerFilters['funnel_id'] = (int) $validated['funnel_id'];
        }
        $actionConfig = [];
        if ($validated['action_type'] === 'send_email') {
            $actionConfig['recipient'] = $validated['recipient'] ?? 'lead.email';
            $actionConfig['subject'] = $validated['email_subject'] ?? '';
            $actionConfig['body'] = $validated['email_body'] ?? '';
        } elseif ($validated['action_type'] === 'start_sequence' && !empty($validated['sequence_id'])) {
            $actionConfig['sequence_id'] = (int) $validated['sequence_id'];
        } else {
            $actionConfig = [];
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
        if (!$workflow->isTenant()) {
            abort(403, 'System workflows cannot be toggled.');
        }
        $workflow->update(['is_active' => !$workflow->is_active]);
        return redirect()->route('automation.workflows.index')
            ->with('success', $workflow->is_active ? 'Workflow activated.' : 'Workflow paused.');
    }

    public function duplicateWorkflow(AutomationWorkflow $workflow)
    {
        $this->ensureTenantWorkflow($workflow);
        if (!$workflow->isTenant()) {
            abort(403, 'System workflows cannot be duplicated.');
        }
        $tenantId = $this->tenantId();
        AutomationWorkflow::create([
            'tenant_id' => $tenantId,
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
        if (!$workflow->isTenant()) {
            abort(403, 'System workflows cannot be deleted.');
        }
        $workflow->delete();
        return redirect()->route('automation.workflows.index')->with('success', 'Workflow deleted.');
    }

    public function logs(Request $request)
    {
        $tenantId = $this->tenantId();
        $dateRange = $request->get('date_range', '7');
        $type = $request->get('type', '');
        $resultStatus = $request->get('result', '');

        $query = AutomationLog::where('tenant_id', $tenantId)->with('workflow')->orderByDesc('ran_at');
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
        $logs = $query->get();

        $logsForView = $logs->map(function (AutomationLog $log) use ($type) {
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
        $tenantId = $this->tenantId();
        $log = AutomationLog::where('tenant_id', $tenantId)->where('id', $id)->with('workflow')->first();
        if (!$log) {
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

    private function ensureTenantWorkflow(AutomationWorkflow $workflow): void
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(403, 'Unauthorized.');
        }
    }

    private function triggerEventLabel(?string $event): string
    {
        if ($event === null || $event === '') {
            return '—';
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
            return '—';
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
            return '—';
        }
        return match ($recipient) {
            'lead.email' => 'Lead email',
            'assigned_agent.email' => 'Assigned agent email',
            default => $recipient,
        };
    }

    /**
     * Human-readable action description for workflow list (e.g. "Send Email (Lead email)", "Start Sequence (Welcome)").
     */
    private function workflowActionDisplayLabel(AutomationWorkflow $w, \Illuminate\Support\Collection $sequences): string
    {
        $action = $w->action_type ?? '';
        $config = $w->action_config ?? [];
        if ($action === 'send_email') {
            $recip = $this->recipientLabel($config['recipient'] ?? 'lead.email');
            return 'Send Email (' . $recip . ')';
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

    private function placeholderSequences(): array
    {
        return [];
    }
}
