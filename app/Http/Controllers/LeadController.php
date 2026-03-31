<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStageHistory;
use App\Models\TenantCustomField;
use App\Models\User;
use App\Support\TenantPlanEnforcer;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $leads = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return view('admin.leads._rows', compact('leads'))->render();
        }

        return view('admin.leads.index', compact('leads'));
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lead::where('tenant_id', $user->tenant_id)->with('assignedAgent');
        $pipelineSearch = trim((string) $request->get('pipeline_search', ''));
        $tagFilter = trim((string) $request->get('tag', ''));
        $pipelineTagFilter = trim((string) $request->get('pipeline_tag', ''));

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

        if ($tagFilter !== '') {
            $this->applyTagFilter($query, $tagFilter);
        }

        $leads = $query->latest()->paginate(10);
        
        // Create separate query for pipeline (independent of main list search/pagination)
        $pipelineQuery = Lead::where('tenant_id', $user->tenant_id)->with('assignedAgent');
        if ($user->hasRole('sales-agent')) {
            $pipelineQuery->where('assigned_to', $user->id);
        }
        $pipelineLeads = $this->buildPipelineLeads($pipelineQuery, $pipelineSearch, $pipelineTagFilter);
        $reportQuery = Lead::where('tenant_id', $user->tenant_id)->with('stageHistories');
        if ($user->hasRole('sales-agent')) {
            $reportQuery->where('assigned_to', $user->id);
        }
        $pipelineReports = $this->buildPipelineReports($reportQuery->get());
        $assignableAgents = $this->getAssignableAgents($user->tenant_id);
        $availableTags = $this->extractTenantTags($user->tenant_id);
        $planUsage = app(TenantPlanEnforcer::class)->usageSummary($user->tenant);

        // For Assign Lead dropdown: all leads (up to 500) so page 2+ leads appear
        $leadsForDropdown = (clone $query)->latest()->take(500)->get();

        // AJAX: pipeline-only (View Lead Pipeline modal search – searches all data, shows up to 12 per stage)
        if ($request->ajax() && $request->has('pipeline_only')) {
            $pipeSearch = trim((string) $request->get('pipeline_search', ''));
            $pipeTag = trim((string) $request->get('pipeline_tag', ''));
            $pipeQuery = Lead::where('tenant_id', $user->tenant_id)->with('assignedAgent');
            if ($user->hasRole('sales-agent')) {
                $pipeQuery->where('assigned_to', $user->id);
            }
            $pipeLeads = $this->buildPipelineLeads($pipeQuery, $pipeSearch, $pipeTag);
            $pipeHtml = view('leads._pipeline_grid', [
                'pipelineStatuses' => Lead::PIPELINE_STATUSES,
                'pipelineLeads' => $pipeLeads,
            ])->render();
            $totals = [];
            foreach (array_keys(Lead::PIPELINE_STATUSES) as $status) {
                $totals[$status] = $pipeLeads[$status]['total'];
            }
            return response()->json(['pipelineHtml' => $pipeHtml, 'totals' => $totals]);
        }

        if ($request->ajax()) {
            $rowsHtml = view('leads._rows', compact('leads'))->render();
            $leadOptions = $leadsForDropdown->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'assigned' => $lead->assignedAgent->name ?? 'Unassigned',
                ];
            })->values()->all();
            return response()->json(['rows' => $rowsHtml, 'leads' => $leadOptions]);
        }

        return view('leads.index', [
            'leads' => $leads,
            'leadsForDropdown' => $leadsForDropdown,
            'pipelineStatuses' => Lead::PIPELINE_STATUSES,
            'pipelineLeads' => $pipelineLeads,
            'assignableAgents' => $assignableAgents,
            'pipelineSearch' => $pipelineSearch,
            'tagFilter' => $tagFilter,
            'pipelineTagFilter' => $pipelineTagFilter,
            'pipelineReports' => $pipelineReports,
            'planUsage' => $planUsage,
            'availableTags' => $availableTags,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $this->ensureCanCreateLead();
        try {
            app(TenantPlanEnforcer::class)->ensureCanCreateLead($user->tenant);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('leads.index')->with('error', $e->getMessage());
        }

        return view('leads.create', [
            'statuses' => Lead::PIPELINE_STATUSES,
            'assignableAgents' => $this->getAssignableAgents($user->tenant_id),
            'customFields' => $this->tenantCustomFields($user->tenant_id),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $this->ensureCanCreateLead();

        $customFields = $this->tenantCustomFields($user->tenant_id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'source_campaign' => 'required|string|max:100',
            'status' => ['required', Rule::in(array_keys(Lead::PIPELINE_STATUSES))],
            'assigned_to' => 'required|integer',
            'tags' => 'nullable|string|max:500',
            ...$this->customFieldValidationRules($customFields),
        ], [
            'phone.regex' => 'Phone number must be a valid Philippine mobile number (09XXXXXXXXX).',
        ]);

        try {
            app(TenantPlanEnforcer::class)->ensureCanCreateLead($user->tenant);
            $assignedTo = $this->normalizeAssignee($validated['assigned_to'] ?? null, $user);

            $lead = Lead::create([
                'tenant_id' => $user->tenant_id,
                'assigned_to' => $assignedTo,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'source_campaign' => $validated['source_campaign'],
                'tags' => $this->parseTagsInput($validated['tags'] ?? null),
                'status' => $validated['status'],
                'score' => 0,
            ]);

            $this->syncCustomFieldValues($lead, $customFields, $validated['custom_fields'] ?? []);
            $this->recordStageHistory($lead, null, $lead->status, $user->id, ['source' => 'created']);
            $this->applySourceCampaignScore($lead, $lead->source_campaign);
            $this->applyStageScore($lead, null, $lead->status);

            return redirect()->route('leads.index')->with('success', 'Added Successfully');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Added Failed');
        }
    }

    public function edit(Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);

        return view('leads.edit', [
            'lead' => $lead->load(['activities', 'customFieldValues.customField', 'stageHistories.changedByUser']),
            'statuses' => Lead::PIPELINE_STATUSES,
            'assignableAgents' => $this->getAssignableAgents(auth()->user()->tenant_id),
            'canEditTags' => $this->canEditLeadTags(auth()->user()),
            'customFields' => $this->tenantCustomFields(auth()->user()->tenant_id),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);
        $user = auth()->user();

        $customFields = $this->tenantCustomFields($user->tenant_id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'source_campaign' => 'required|string|max:100',
            'status' => ['required', Rule::in(array_keys(Lead::PIPELINE_STATUSES))],
            'score' => 'nullable|integer|min:0',
            'assigned_to' => 'required|integer',
            'tags' => 'nullable|string|max:500',
            ...$this->customFieldValidationRules($customFields),
        ], [
            'phone.regex' => 'Phone number must be a valid Philippine mobile number (09XXXXXXXXX).',
        ]);

        try {
            $previousStatus = $lead->status;
            $validated['assigned_to'] = $this->normalizeAssignee($validated['assigned_to'] ?? null, $user, $lead->assigned_to);
            if ($this->canEditLeadTags($user)) {
                $validated['tags'] = $this->parseTagsInput($validated['tags'] ?? null);
            } else {
                unset($validated['tags']);
            }
            $lead->update($validated);
            $this->syncCustomFieldValues($lead, $customFields, $validated['custom_fields'] ?? []);

            if ($previousStatus !== $lead->status) {
                $this->recordStageHistory($lead, $previousStatus, $lead->status, $user->id, ['source' => 'updated']);
                $this->applyStageScore($lead, $previousStatus, $lead->status);
            }

            return redirect()->route('leads.index')->with('success', 'Edited Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Edited Failed');
        }
    }

    public function destroy(Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);

        try {
            $lead->delete();
            return redirect()->back()->with('success', 'Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Deleted Failed');
        }
    }

    public function assign(Request $request, Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);
        $this->ensureCanManageAssignment();

        $validated = $request->validate([
            'assigned_to' => 'nullable|integer',
        ]);

        try {
            $lead->update([
                'assigned_to' => $this->normalizeAssignee($validated['assigned_to'] ?? null, auth()->user(), null, true),
            ]);

            return redirect()->back()->with('success', 'Edited Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Edited Failed');
        }
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

        return redirect()->back()->with('success', 'Added Successfully');
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

        return redirect()->back()->with('success', 'Added Successfully');
    }

    public function applyScoreEvent(Request $request, Lead $lead)
    {
        $this->ensureTenantLeadAccess($lead);

        $validated = $request->validate([
            'event' => ['required', Rule::in(array_keys(config('lead_scoring.manual_events', [])))],
        ]);

        $config = config('lead_scoring.manual_events.' . $validated['event']);
        $points = (int) ($config['points'] ?? 0);
        $label = (string) ($config['label'] ?? Str::headline($validated['event']));
        $lead->increment('score', $points);
        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => $label . " (+{$points} points)",
        ]);

        return redirect()->back()->with('success', 'Edited Successfully');
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

    private function buildPipelineLeads($baseQuery, string $pipelineSearch = '', string $tagFilter = ''): array
    {
        $queryForCounts = clone $baseQuery;
        if ($pipelineSearch !== '') {
            $search = trim($pipelineSearch);
            $queryForCounts->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('assignedAgent', function ($aq) use ($search) {
                        $aq->where('name', 'like', "%{$search}%");
                    });
            });
            $baseQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('assignedAgent', function ($aq) use ($search) {
                        $aq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($tagFilter !== '') {
            $this->applyTagFilter($queryForCounts, $tagFilter);
            $this->applyTagFilter($baseQuery, $tagFilter);
        }

        // Get total counts for each status (before limiting to 12)
        $totalCounts = [];
        foreach (array_keys(Lead::PIPELINE_STATUSES) as $status) {
            $totalCounts[$status] = (clone $queryForCounts)->where('status', $status)->count();
        }

        // Get leads and limit to 12 per stage for display (search checks all data)
        $leads = $baseQuery->orderByDesc('updated_at')->get();
        $grouped = [];

        foreach (array_keys(Lead::PIPELINE_STATUSES) as $status) {
            $grouped[$status] = [
                'leads' => $leads->where('status', $status)->take(12)->values(),
                'total' => $totalCounts[$status] ?? 0,
            ];
        }

        return $grouped;
    }

    private function parseTagsInput(?string $raw): array
    {
        if ($raw === null) {
            return [];
        }

        return collect(explode(',', $raw))
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

    private function canEditLeadTags(User $user): bool
    {
        return $user->hasRole('account-owner') || $user->hasRole('marketing-manager');
    }

    private function applyTagFilter($query, string $tag): void
    {
        $needle = mb_strtolower(trim($tag));
        if ($needle === '') {
            return;
        }

        $query->where('tags', 'like', '%"' . $needle . '"%');
    }

    private function extractTenantTags(int $tenantId): array
    {
        $tags = Lead::where('tenant_id', $tenantId)
            ->get(['tags'])
            ->pluck('tags')
            ->filter(fn ($value) => is_array($value))
            ->flatten(1)
            ->map(fn ($tag) => mb_strtolower(trim((string) $tag)))
            ->filter(fn ($tag) => $tag !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $tags;
    }

    private function tenantCustomFields(int $tenantId)
    {
        return TenantCustomField::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    private function customFieldValidationRules($fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $ruleKey = 'custom_fields.' . $field->id;
            $fieldRules = [$field->is_required ? 'required' : 'nullable'];

            switch ($field->field_type) {
                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:2000';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'select':
                    $fieldRules[] = Rule::in($field->options ?? []);
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                case 'text':
                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:255';
                    break;
            }

            $rules[$ruleKey] = $fieldRules;
        }

        return $rules;
    }

    private function syncCustomFieldValues(Lead $lead, $fields, array $payload): void
    {
        foreach ($fields as $field) {
            $rawValue = $payload[$field->id] ?? null;
            $value = $this->normalizeCustomFieldValue($field, $rawValue);

            if ($value === null) {
                $lead->customFieldValues()->where('tenant_custom_field_id', $field->id)->delete();
                continue;
            }

            $lead->customFieldValues()->updateOrCreate(
                ['tenant_custom_field_id' => $field->id],
                ['value' => $value]
            );
        }
    }

    private function normalizeCustomFieldValue(TenantCustomField $field, mixed $value): ?string
    {
        if ($field->field_type === 'checkbox') {
            return $value ? '1' : null;
        }

        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return $normalized;
    }

    private function recordStageHistory(Lead $lead, ?string $fromStatus, string $toStatus, ?int $changedBy, array $metadata = []): void
    {
        LeadStageHistory::create([
            'lead_id' => $lead->id,
            'tenant_id' => $lead->tenant_id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $changedBy,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }

    private function applyStageScore(Lead $lead, ?string $fromStatus, string $toStatus): void
    {
        if ($fromStatus === $toStatus) {
            return;
        }

        $config = config('lead_scoring.stage_events.' . $toStatus);
        $points = (int) ($config['points'] ?? 0);

        if ($points <= 0) {
            return;
        }

        $label = (string) ($config['label'] ?? ('Moved to ' . (Lead::PIPELINE_STATUSES[$toStatus] ?? Str::headline($toStatus))));
        $lead->increment('score', $points);
        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => $label . " (+{$points} points)",
        ]);
    }

    private function applySourceCampaignScore(Lead $lead, ?string $sourceCampaign): void
    {
        $normalizedKey = Str::of((string) $sourceCampaign)->lower()->snake()->toString();
        $config = config('lead_scoring.source_campaign_events.' . $normalizedKey);
        $points = (int) ($config['points'] ?? 0);

        if ($points <= 0) {
            return;
        }

        $label = (string) ($config['label'] ?? ((string) $sourceCampaign . ' Lead'));
        $lead->increment('score', $points);
        $lead->activities()->create([
            'activity_type' => 'Scoring',
            'notes' => $label . " (+{$points} points)",
        ]);
    }

    private function buildPipelineReports(Collection $leads): array
    {
        $statuses = array_keys(Lead::PIPELINE_STATUSES);
        $stageCounts = [];
        $stageAging = [];
        $conversionSeeds = [
            ['from' => 'new', 'to' => 'contacted'],
            ['from' => 'contacted', 'to' => 'proposal_sent'],
            ['from' => 'proposal_sent', 'to' => 'closed_won'],
            ['from' => 'proposal_sent', 'to' => 'closed_lost'],
        ];
        $transitionEligible = [];
        $transitionConverted = [];

        foreach ($statuses as $status) {
            $count = $leads->where('status', $status)->count();
            $stageCounts[$status] = [
                'label' => Lead::PIPELINE_STATUSES[$status],
                'count' => $count,
            ];
            $stageAging[$status] = [
                'label' => Lead::PIPELINE_STATUSES[$status],
                'lead_count' => $count,
                'average_days' => 0,
                'older_than_7_days' => 0,
                'older_than_14_days' => 0,
                'older_than_30_days' => 0,
            ];
        }

        foreach ($conversionSeeds as $seed) {
            $key = $seed['from'] . '->' . $seed['to'];
            $transitionEligible[$key] = [];
            $transitionConverted[$key] = [];
        }

        foreach ($leads as $lead) {
            $history = $lead->stageHistories->sortBy('created_at')->values();
            $enteredAt = optional($history->where('to_status', $lead->status)->last())->created_at ?? $lead->created_at;
            $ageInDays = round(($enteredAt?->diffInMinutes(now()) ?? 0) / 1440, 1);

            if (isset($stageAging[$lead->status])) {
                $stageAging[$lead->status]['average_days'] += $ageInDays;
                $stageAging[$lead->status]['older_than_7_days'] += $ageInDays >= 7 ? 1 : 0;
                $stageAging[$lead->status]['older_than_14_days'] += $ageInDays >= 14 ? 1 : 0;
                $stageAging[$lead->status]['older_than_30_days'] += $ageInDays >= 30 ? 1 : 0;
            }

            $seenStatuses = collect([$lead->status])
                ->merge($history->pluck('from_status'))
                ->merge($history->pluck('to_status'))
                ->filter()
                ->unique()
                ->values();

            foreach ($conversionSeeds as $seed) {
                $key = $seed['from'] . '->' . $seed['to'];
                if ($seenStatuses->contains($seed['from'])) {
                    $transitionEligible[$key][$lead->id] = true;
                }

                if ($history->contains(function ($entry) use ($seed) {
                    return $entry->from_status === $seed['from'] && $entry->to_status === $seed['to'];
                })) {
                    $transitionConverted[$key][$lead->id] = true;
                }
            }
        }

        foreach ($stageAging as $status => $metrics) {
            $leadCount = max(1, $metrics['lead_count']);
            $stageAging[$status]['average_days'] = $metrics['lead_count'] > 0
                ? round($metrics['average_days'] / $leadCount, 1)
                : 0;
        }

        $stageConversions = collect($conversionSeeds)->map(function ($seed) use ($transitionEligible, $transitionConverted) {
            $key = $seed['from'] . '->' . $seed['to'];
            $eligible = count($transitionEligible[$key]);
            $converted = count($transitionConverted[$key]);

            return [
                'from' => $seed['from'],
                'to' => $seed['to'],
                'label' => (Lead::PIPELINE_STATUSES[$seed['from']] ?? Str::headline($seed['from']))
                    . ' to '
                    . (Lead::PIPELINE_STATUSES[$seed['to']] ?? Str::headline($seed['to'])),
                'eligible' => $eligible,
                'converted' => $converted,
                'rate' => $eligible > 0 ? round(($converted / $eligible) * 100, 1) : 0,
            ];
        })->all();

        $won = $stageCounts['closed_won']['count'] ?? 0;
        $lost = $stageCounts['closed_lost']['count'] ?? 0;
        $closed = $won + $lost;

        return [
            'summary' => [
                'total_leads' => $leads->count(),
                'open_leads' => $leads->whereNotIn('status', Lead::closedStatusValues())->count(),
                'won_count' => $won,
                'lost_count' => $lost,
                'closed_count' => $closed,
                'win_rate' => $closed > 0 ? round(($won / $closed) * 100, 1) : 0,
            ],
            'stage_counts' => $stageCounts,
            'stage_conversions' => $stageConversions,
            'stage_aging' => $stageAging,
        ];
    }
}
