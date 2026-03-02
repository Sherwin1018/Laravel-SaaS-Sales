<?php

namespace App\Http\Controllers;

use App\Models\AutomationSequenceStep;
use App\Models\AutomationTrigger;
use App\Models\AutomationWorkflow;
use App\Models\Funnel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AutomationController extends Controller
{
    private function n8nConfigured(): bool
    {
        return rtrim(Config::get('n8n.webhook_base_url', ''), '/') !== '';
    }

    /**
     * Check if n8n is actually reachable (cached 60s to avoid slowing every page load).
     * Returns false if not configured or if HTTP request fails/timeouts.
     */
    private function n8nReachable(): bool
    {
        $baseUrl = rtrim(Config::get('n8n.webhook_base_url', ''), '/');
        if ($baseUrl === '') {
            return false;
        }

        $cacheKey = 'n8n_reachable_' . md5($baseUrl);

        return Cache::remember($cacheKey, 60, function () use ($baseUrl) {
            try {
                $response = Http::timeout(3)->get($baseUrl);
                return $response->successful() || $response->redirect();
            } catch (\Throwable $e) {
                return false;
            }
        });
    }

    private function tenantId(): int
    {
        return (int) auth()->user()->tenant_id;
    }

    /**
     * Show the automation list (all automations; one unified list).
     */
    public function index(Request $request): View
    {
        $tenantId = $this->tenantId();

        $query = AutomationWorkflow::where('tenant_id', $tenantId)
            ->with(['triggers' => fn ($q) => $q->with('funnel'), 'sequenceSteps'])
            ->withCount(['triggers', 'sequenceSteps']);

        $search = $request->get('search');
        if ($search && trim($search) !== '') {
            $query->where('name', 'like', '%' . trim($search) . '%');
        }

        $status = $request->get('status');
        if (in_array($status, ['active', 'draft', 'inactive'], true) && Schema::hasColumn('automation_workflows', 'status')) {
            $query->where('status', $status);
        } elseif (in_array($status, ['active', 'inactive'], true)) {
            $query->where('is_active', $status === 'active');
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            default => $query->latest(),
        };

        $workflows = $query->paginate(10)->withQueryString();

        return view('automation.index', [
            'workflows' => $workflows,
            'n8nConfigured' => $this->n8nConfigured(),
            'n8nReachable' => $this->n8nReachable(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Show the form for creating a new automation.
     */
    public function create(Request $request): View
    {
        $tenantId = $this->tenantId();
        $funnels = Funnel::where('tenant_id', $tenantId)->orderBy('name')->get();
        $view = $request->get('view', 'workflow');
        if (!in_array($view, ['sequences', 'workflows'], true)) {
            $view = 'workflow';
        }

        return view('automation.create', [
            'triggerEvents' => AutomationTrigger::EVENTS,
            'funnels' => $funnels,
            'view' => $view,
        ]);
    }

    /**
     * Store a newly created workflow (and optional trigger).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'type' => 'required|string|in:sequence,workflow',
            'status' => 'nullable|string|in:active,draft,inactive',
            'trigger_event' => 'nullable|string|in:lead.created,funnel.opt_in,lead.status_changed',
            'trigger_webhook_path' => 'nullable|string|max:120',
            'trigger_funnel_id' => 'nullable|exists:funnels,id',
            'steps' => 'nullable|array',
            'steps.*.channel' => 'nullable|string|in:email,sms',
            'steps.*.subject' => 'nullable|string|max:255',
            'steps.*.body' => 'nullable|string',
            'steps.*.delay_minutes' => 'nullable|integer|min:0|max:10080',
        ]);

        $tenantId = $this->tenantId();
        if (isset($validated['trigger_funnel_id'])) {
            $funnel = Funnel::where('id', $validated['trigger_funnel_id'])->where('tenant_id', $tenantId)->first();
            if (! $funnel) {
                return back()->withInput()->with('error', 'Invalid funnel.');
            }
        }

        $status = $validated['status'] ?? 'draft';
        $isActive = $status === 'active';
        $data = [
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => $isActive,
        ];
        if (Schema::hasColumn('automation_workflows', 'status')) {
            $data['status'] = $status;
        }
        $workflow = AutomationWorkflow::create($data);

        if (! empty($validated['trigger_event'])) {
            $path = $validated['trigger_webhook_path'] ?? null;
            if (empty($path)) {
                $pathKey = match ($validated['trigger_event']) {
                    'lead.created' => 'lead_created',
                    'funnel.opt_in' => 'funnel_opt_in',
                    'lead.status_changed' => 'lead_status_changed',
                    default => null,
                };
                $path = $pathKey ? config("n8n.paths.{$pathKey}", '') : null;
            }
            AutomationTrigger::create([
                'automation_workflow_id' => $workflow->id,
                'event' => $validated['trigger_event'],
                'n8n_webhook_path' => $path ?: null,
                'funnel_id' => $validated['trigger_funnel_id'] ?? null,
            ]);
        }

        $steps = $request->input('steps', []);
        $position = 0;
        foreach ($steps as $step) {
            if (empty($step['channel'] ?? null) || (string) ($step['body'] ?? '') === '') {
                continue;
            }
            $position++;
            $workflow->sequenceSteps()->create([
                'position' => $position,
                'channel' => $step['channel'],
                'subject' => $step['subject'] ?? null,
                'body' => $step['body'] ?? '',
                'delay_minutes' => (int) ($step['delay_minutes'] ?? 0),
            ]);
        }

        return redirect()->route('automation.edit', $workflow)->with('success', 'Automation created. Add steps below.');
    }

    /**
     * Show a single workflow.
     */
    public function show(AutomationWorkflow $workflow): View|RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(404);
        }
        $workflow->load(['triggers' => fn ($q) => $q->with('funnel'), 'sequenceSteps']);

        return view('automation.show', [
            'workflow' => $workflow,
            'n8nConfigured' => $this->n8nConfigured(),
        ]);
    }

    /**
     * Show the form for editing the workflow.
     */
    public function edit(Request $request, AutomationWorkflow $workflow): View|RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(404);
        }
        $workflow->load(['triggers' => fn ($q) => $q->with('funnel'), 'sequenceSteps']);
        $stepId = $request->get('step');
        $selectedStep = $stepId ? $workflow->sequenceSteps->firstWhere('id', (int) $stepId) : null;

        return view('automation.edit', [
            'workflow' => $workflow,
            'triggerEvents' => AutomationTrigger::EVENTS,
            'selectedStep' => $selectedStep,
            'n8nConfigured' => $this->n8nConfigured(),
        ]);
    }

    /**
     * Update the workflow.
     */
    public function update(Request $request, AutomationWorkflow $workflow): RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'type' => 'required|string|in:sequence,workflow',
            'status' => 'nullable|string|in:active,draft,inactive',
            'is_active' => 'nullable',
            'trigger_tag' => 'nullable|string|max:120',
            'trigger_event' => 'nullable|string|in:lead.created,funnel.opt_in,lead.status_changed',
        ]);

        $status = $validated['status'] ?? ($request->boolean('is_active') ? 'active' : 'inactive');
        $update = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => $status === 'active',
        ];
        if (Schema::hasColumn('automation_workflows', 'status')) {
            $update['status'] = $status;
        }
        if (Schema::hasColumn('automation_workflows', 'trigger_tag') && array_key_exists('trigger_tag', $validated)) {
            $update['trigger_tag'] = $validated['trigger_tag'];
        }
        $workflow->update($update);

        if (!empty($validated['trigger_event'])) {
            $trigger = $workflow->triggers->first();
            if ($trigger) {
                $trigger->update(['event' => $validated['trigger_event']]);
            }
        }

        return redirect()->route('automation.edit', $workflow)->with('success', 'Settings saved.');
    }

    /**
     * Duplicate a workflow (clone with triggers and sequence steps).
     */
    public function duplicate(AutomationWorkflow $workflow): RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(404);
        }

        $new = $workflow->replicate(['n8n_workflow_id']);
        $new->name = $workflow->name . ' (Copy)';
        $new->is_active = false;
        if (Schema::hasColumn('automation_workflows', 'status')) {
            $new->status = 'draft';
        }
        $new->save();

        foreach ($workflow->triggers as $trigger) {
            $new->triggers()->create($trigger->only(['event', 'n8n_webhook_path', 'funnel_id']));
        }
        foreach ($workflow->sequenceSteps as $step) {
            $new->sequenceSteps()->create($step->only(['position', 'channel', 'sender_name', 'subject', 'body', 'delay_minutes']));
        }

        return redirect()->route('automation.edit', $new)->with('success', 'Sequence duplicated.');
    }

    /**
     * Toggle workflow status between active and inactive (pause/play).
     */
    public function toggleStatus(AutomationWorkflow $workflow): RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(404);
        }

        $current = $workflow->display_status;
        $next = $current === 'active' ? 'inactive' : 'active';
        $workflow->update(['is_active' => $next === 'active']);
        if (Schema::hasColumn('automation_workflows', 'status')) {
            $workflow->update(['status' => $next]);
        }

        $message = $next === 'active' ? 'Sequence activated.' : 'Sequence paused.';
        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove the workflow.
     */
    public function destroy(AutomationWorkflow $workflow): RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(404);
        }
        $workflow->delete();

        return redirect()->route('automation.index')->with('success', 'Workflow deleted.');
    }

    /**
     * Add a sequence step to the workflow.
     */
    public function sequenceStore(Request $request, AutomationWorkflow $workflow): RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId()) {
            abort(404);
        }

        $validated = $request->validate([
            'channel' => 'required|string|in:email,sms',
            'sender_name' => 'nullable|string|max:120',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'delay_minutes' => 'nullable|integer|min:0|max:10080',
        ]);

        $position = $workflow->sequenceSteps()->max('position') + 1;

        $workflow->sequenceSteps()->create([
            'position' => $position,
            'channel' => $validated['channel'],
            'sender_name' => $validated['sender_name'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
            'delay_minutes' => $validated['delay_minutes'] ?? 0,
        ]);

        return redirect()->route('automation.edit', $workflow)->with('success', 'Step added.');
    }

    /**
     * Update a sequence step (subject, body, channel, delay).
     */
    public function sequenceUpdate(Request $request, AutomationWorkflow $workflow, AutomationSequenceStep $step): RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId() || $step->automation_workflow_id !== $workflow->id) {
            abort(404);
        }

        $validated = $request->validate([
            'channel' => 'required|string|in:email,sms',
            'sender_name' => 'nullable|string|max:120',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'delay_minutes' => 'nullable|integer|min:0|max:10080',
        ]);

        $step->update([
            'channel' => $validated['channel'],
            'sender_name' => $validated['sender_name'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
            'delay_minutes' => (int) ($validated['delay_minutes'] ?? 0),
        ]);

        return redirect()->to(route('automation.edit', $workflow) . '?step=' . $step->id)
            ->with('success', 'Step saved.');
    }

    /**
     * Remove a sequence step.
     */
    public function sequenceDestroy(AutomationWorkflow $workflow, AutomationSequenceStep $step): RedirectResponse
    {
        if ($workflow->tenant_id !== $this->tenantId() || $step->automation_workflow_id !== $workflow->id) {
            abort(404);
        }
        $step->delete();

        return redirect()->route('automation.edit', $workflow)->with('success', 'Step removed.');
    }
}
