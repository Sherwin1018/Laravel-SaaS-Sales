<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function adminIndex(Request $request)
    {
        $query = Lead::withoutGlobalScope('tenant')
            ->with(['tenant', 'assignedAgent']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($tq) use ($search) {
                        $tq->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $leads = $query->latest()->paginate(15);

        if ($request->ajax()) {
            return view('admin.leads._rows', compact('leads'))->render();
        }

        return view('admin.leads.index', compact('leads'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lead::where('tenant_id', $user->tenant_id)->with('assignedAgent');

        if ($user->hasRole('sales-agent')) {
            $query->where('assigned_to', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(10);
        $pipelineLeads = $this->buildPipelineLeads(clone $query);
        $assignableAgents = $this->getAssignableAgents($user->tenant_id);

        if ($request->ajax()) {
            return view('leads._rows', compact('leads'))->render();
        }

        return view('leads.index', [
            'leads' => $leads,
            'pipelineStatuses' => Lead::PIPELINE_STATUSES,
            'pipelineLeads' => $pipelineLeads,
            'assignableAgents' => $assignableAgents,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $this->ensureCanCreateLead();

        return view('leads.create', [
            'statuses' => Lead::PIPELINE_STATUSES,
            'assignableAgents' => $this->getAssignableAgents($user->tenant_id),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $this->ensureCanCreateLead();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'status' => ['required', Rule::in(array_keys(Lead::PIPELINE_STATUSES))],
            'assigned_to' => 'nullable|integer',
        ]);

        $assignedTo = $this->normalizeAssignee($validated['assigned_to'] ?? null, $user);

        Lead::create([
            'tenant_id' => $user->tenant_id,
            'assigned_to' => $assignedTo,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'score' => 0,
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function edit(Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);

        return view('leads.edit', [
            'lead' => $lead->load('activities'),
            'statuses' => Lead::PIPELINE_STATUSES,
            'assignableAgents' => $this->getAssignableAgents(auth()->user()->tenant_id),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'status' => ['required', Rule::in(array_keys(Lead::PIPELINE_STATUSES))],
            'score' => 'nullable|integer|min:0',
            'assigned_to' => 'nullable|integer',
        ]);

        $validated['assigned_to'] = $this->normalizeAssignee($validated['assigned_to'] ?? null, $user, $lead->assigned_to);
        $lead->update($validated);

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);

        $lead->delete();

        return redirect()->back()->with('success', 'Lead deleted successfully.');
    }

    public function assign(Request $request, Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);
        $this->ensureCanManageAssignment();

        $validated = $request->validate([
            'assigned_to' => 'nullable|integer',
        ]);

        $lead->update([
            'assigned_to' => $this->normalizeAssignee($validated['assigned_to'] ?? null, auth()->user(), null, true),
        ]);

        return redirect()->back()->with('success', 'Lead assignment updated.');
    }

    public function storeActivity(Request $request, Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);

        $request->validate([
            'notes' => 'required|string',
            'activity_type' => 'required|string|max:100',
        ]);

        $lead->activities()->create([
            'activity_type' => $request->activity_type,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Activity added.');
    }

    public function logEmail(Request $request, Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);

        $request->validate([
            'subject' => 'required|string|max:150',
            'message' => 'required|string',
        ]);

        $lead->activities()->create([
            'activity_type' => 'Email Sent',
            'notes' => 'Subject: ' . $request->subject,
        ]);

        return redirect()->back()->with('success', 'Email activity logged.');
    }

    public function applyScoreEvent(Request $request, Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);

        $validated = $request->validate([
            'event' => ['required', Rule::in(['email_opened', 'link_clicked', 'form_submitted'])],
        ]);

        $pointsMap = [
            'email_opened' => 5,
            'link_clicked' => 10,
            'form_submitted' => 20,
        ];

        $eventLabels = [
            'email_opened' => 'Email Opened',
            'link_clicked' => 'Link Clicked',
            'form_submitted' => 'Form Submitted',
        ];

        $points = $pointsMap[$validated['event']];
        $lead->increment('score', $points);
        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => $eventLabels[$validated['event']] . " (+{$points} points)",
        ]);

        return redirect()->back()->with('success', 'Lead score updated.');
    }

    private function ensureTenantLeadAccess(Lead $lead): void
    {
        $user = auth()->user();

        if ($lead->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->hasRole('sales-agent') && $lead->assigned_to !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function ensureCanManageAssignment(): void
    {
        $user = auth()->user();

        if (!($user->hasRole('account-owner') || $user->hasRole('marketing-manager'))) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function ensureCanCreateLead(): void
    {
        $user = auth()->user();

        if (!($user->hasRole('account-owner') || $user->hasRole('marketing-manager'))) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function getAssignableAgents(int $tenantId)
    {
        return User::where('tenant_id', $tenantId)
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'sales-agent');
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function normalizeAssignee(?int $requestedAssignee, User $user, ?int $fallbackAssignee = null, bool $strictManager = false): ?int
    {
        $isManager = $user->hasRole('account-owner') || $user->hasRole('marketing-manager');
        if ($requestedAssignee === null) {
            return $isManager ? null : $fallbackAssignee;
        }

        if (!$isManager) {
            if ($user->hasRole('sales-agent')) {
                return $user->id;
            }

            if ($strictManager) {
                abort(403, 'Unauthorized action.');
            }

            return $fallbackAssignee;
        }

        $isValidAssignee = User::where('id', $requestedAssignee)
            ->where('tenant_id', $user->tenant_id)
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'sales-agent');
            })
            ->exists();

        if (!$isValidAssignee) {
            abort(422, 'Selected assignee is invalid.');
        }

        return $requestedAssignee;
    }

    private function buildPipelineLeads($baseQuery): array
    {
        $leads = $baseQuery->orderByDesc('updated_at')->get();
        $grouped = [];

        foreach (array_keys(Lead::PIPELINE_STATUSES) as $status) {
            $grouped[$status] = $leads->where('status', $status)->take(8)->values();
        }

        return $grouped;
    }
}
