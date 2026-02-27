@extends('layouts.admin')

@section('title', 'Automation')

@section('content')
<div class="auto-page">
    <header class="auto-page-header">
        <div class="auto-page-header-text">
            <h1 class="auto-page-title">Automation</h1>
            <p class="auto-page-subtitle">
                When something happens (e.g. new lead), run steps like emails or SMS.
                @if($n8nReachable)
                    <span class="auto-badge auto-badge-active auto-badge-sm"><i class="fas fa-check-circle" aria-hidden="true"></i> Automation service connected</span>
                @elseif($n8nConfigured)
                    <span class="auto-badge auto-badge-warning auto-badge-sm"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Service unreachable</span>
                @else
                    <span class="auto-badge auto-badge-inactive auto-badge-sm"><i class="fas fa-plug" aria-hidden="true"></i> Not connected</span>
                @endif
            </p>
        </div>
        <a href="{{ route('automation.create') }}" class="auto-btn-primary"><i class="fas fa-plus"></i> Create automation</a>
    </header>

    <div class="auto-controls">
        <form method="GET" action="{{ route('automation.index') }}" class="auto-controls-search" role="search">
            @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
            <label for="auto-search" class="auto-sr-only">Search automations</label>
            <input id="auto-search" type="search" name="search" value="{{ request('search') }}" class="auto-input auto-input-search" placeholder="Search automations…" aria-label="Search automations">
            <button type="submit" class="auto-btn-icon auto-btn-search-submit" aria-label="Search"><i class="fas fa-search"></i></button>
        </form>
        <div class="auto-controls-filters">
            <form method="GET" action="{{ route('automation.index') }}" class="auto-filter-form">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                <label for="auto-status" class="auto-sr-only">Filter by status</label>
                <select id="auto-status" name="status" class="auto-select auto-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </form>
            <form method="GET" action="{{ route('automation.index') }}" class="auto-sort-form">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <label for="auto-sort" class="auto-sr-only">Sort by</label>
                <select id="auto-sort" name="sort" class="auto-select auto-select-sm" onchange="this.form.submit()">
                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest first</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name A–Z</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name Z–A</option>
                </select>
            </form>
        </div>
    </div>

    @if(!$n8nConfigured)
        <div class="auto-alert auto-alert-warning auto-alert-slim">
            <span>Automation runs in the background. Set the automation service URL in your environment to enable automations.</span>
        </div>
    @elseif(!$n8nReachable)
        <div class="auto-alert auto-alert-warning auto-alert-slim">
            <span>Automation service is not responding. Start n8n or check that the URL is correct.</span>
        </div>
    @endif

    <section class="auto-section">
        @if($workflows->count() > 0)
            <div class="auto-table-card">
                <div class="auto-table-wrap">
                    <table class="auto-table auto-table-workflows">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>When it runs</th>
                                <th class="auto-table-num">Steps</th>
                                <th>Status</th>
                                <th class="auto-table-actions-col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workflows as $workflow)
                                @php
                                    $firstTrigger = $workflow->triggers->first();
                                    $triggerLabel = $firstTrigger ? (\App\Models\AutomationTrigger::EVENTS[$firstTrigger->event] ?? $firstTrigger->event) : '—';
                                    $stepsCount = (int) $workflow->sequence_steps_count;
                                    $stepsText = $stepsCount === 1 ? '1 step' : $stepsCount . ' steps';
                                    $displayStatus = $workflow->display_status;
                                    $statusBadgeClass = $displayStatus === 'active' ? 'auto-badge-active' : ($displayStatus === 'draft' ? 'auto-badge-draft' : ($displayStatus === 'inactive' ? 'auto-badge-paused' : 'auto-badge-inactive'));
                                    $statusLabel = $displayStatus === 'inactive' ? 'Paused' : (\App\Models\AutomationWorkflow::STATUSES[$displayStatus] ?? ucfirst($displayStatus));
                                @endphp
                                <tr class="auto-table-row-card">
                                    <td class="auto-table-name" data-label="Name"><a href="{{ route('automation.show', $workflow) }}" class="auto-link-name">{{ $workflow->name }}</a></td>
                                    <td class="{{ $triggerLabel === '—' ? 'auto-table-muted' : '' }}" data-label="When it runs">{{ $triggerLabel }}</td>
                                    <td class="auto-table-num" data-label="Steps">{{ $stepsText }}</td>
                                    <td data-label="Status"><span class="auto-badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span></td>
                                    <td class="auto-table-actions-col" data-label="Actions">
                                        <div class="auto-table-actions">
                                            <a href="{{ route('automation.show', $workflow) }}" class="auto-btn-action" title="View" aria-label="View"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('automation.edit', $workflow) }}" class="auto-btn-action" title="Edit" aria-label="Edit"><i class="fas fa-pen"></i></a>
                                            <form method="POST" action="{{ route('automation.duplicate', $workflow) }}" class="auto-form-inline" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="auto-btn-action" title="Duplicate" aria-label="Duplicate"><i class="fas fa-copy"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('automation.toggle-status', $workflow) }}" class="auto-form-inline" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="auto-btn-action" title="{{ $displayStatus === 'active' ? 'Pause' : 'Activate' }}" aria-label="{{ $displayStatus === 'active' ? 'Pause' : 'Activate' }}"><i class="fas fa-{{ $displayStatus === 'active' ? 'pause' : 'play' }}"></i></button>
                                            </form>
                                            <button type="button" class="auto-btn-action auto-btn-action-delete" title="Delete" aria-label="Delete" data-auto-delete data-workflow-name="{{ e($workflow->name) }}" data-delete-url="{{ route('automation.destroy', $workflow) }}"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if($workflows->hasPages())
                <div class="auto-pagination">
                    {{ $workflows->links('pagination::bootstrap-4') }}
                </div>
            @endif
        @else
            <div class="auto-empty">
                <div class="auto-empty-icon"><i class="fas fa-magic"></i></div>
                <p class="auto-empty-title">No automations yet</p>
                <p class="auto-empty-desc">Create one to send emails or SMS when things happen (e.g. when a new lead is added).</p>
                <a href="{{ route('automation.create') }}" class="auto-btn-primary">Create automation</a>
            </div>
        @endif
    </section>

    {{-- Separate expandable cards --}}
    <section class="auto-section">
        <div class="auto-reference-card">
            <details class="auto-reference-details auto-reference-details-card">
                <summary class="auto-reference-summary"><span class="auto-details-chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span> How it works</summary>
                <div class="auto-reference-details-body">
                <p class="auto-reference-lead">Four steps to get started:</p>
                <ol class="auto-how-steps">
                    <li><strong>Create</strong> an automation and give it a name (e.g. “Welcome new leads”).</li>
                    <li><strong>Choose when it runs</strong> — pick a trigger like Lead created or Funnel opt-in.</li>
                    <li><strong>Add steps</strong> — write your email or SMS; use <code>@verbatim{{ name }}@endverbatim</code> for the lead’s name).</li>
                    <li><strong>Turn it on</strong> — set status to Active so it runs automatically.</li>
                </ol>
                <p class="auto-reference-heading-sm">Ideas you can try</p>
                <ul class="auto-ideas-list">
                    <li><span class="auto-idea-dot" aria-hidden="true"></span> <strong>Welcome email</strong> when someone becomes a new lead.</li>
                    <li><span class="auto-idea-dot" aria-hidden="true"></span> <strong>Follow-up email or SMS</strong> a few minutes or hours later (add a delay step).</li>
                    <li><span class="auto-idea-dot" aria-hidden="true"></span> Automation when a lead <strong>opts in via a funnel form</strong>.</li>
                    <li><span class="auto-idea-dot" aria-hidden="true"></span> React when a lead’s <strong>pipeline status changes</strong>.</li>
                </ul>
                </div>
            </details>

            <details class="auto-reference-details auto-reference-details-card">
                <summary class="auto-reference-summary"><span class="auto-details-chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span> When a lead is added</summary>
                <div class="auto-reference-details-body">
                <p class="auto-reference-lead">Automation runs in the background. Each automation follows this path:</p>
                <div class="auto-flow-strip" role="list" aria-label="Automation flow">
                    <div class="auto-flow-item" role="listitem">
                        <span class="auto-flow-icon auto-flow-icon-1" aria-hidden="true"><i class="fas fa-inbox"></i></span>
                        <span class="auto-flow-label">Receive</span>
                        <span class="auto-flow-desc">Lead data is sent to the automation service.</span>
                    </div>
                    <span class="auto-flow-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    <div class="auto-flow-item" role="listitem">
                        <span class="auto-flow-icon auto-flow-icon-2" aria-hidden="true"><i class="fas fa-clock"></i></span>
                        <span class="auto-flow-label">Wait</span>
                        <span class="auto-flow-desc">Optional pause (e.g. 1–15 min).</span>
                    </div>
                    <span class="auto-flow-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    <div class="auto-flow-item" role="listitem">
                        <span class="auto-flow-icon auto-flow-icon-3" aria-hidden="true"><i class="fas fa-filter"></i></span>
                        <span class="auto-flow-label">Condition</span>
                        <span class="auto-flow-desc">If conditions match, send email or SMS.</span>
                    </div>
                    <span class="auto-flow-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                    <div class="auto-flow-item" role="listitem">
                        <span class="auto-flow-icon auto-flow-icon-4" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                        <span class="auto-flow-label">Complete</span>
                        <span class="auto-flow-desc">Automation finishes.</span>
                    </div>
                </div>
                <p class="auto-reference-note">You control wait time, conditions, and email/SMS content in each automation — open <strong>Edit</strong> on any automation to change them.</p>
                </div>
            </details>

            <details class="auto-reference-details auto-reference-details-card">
                <summary class="auto-reference-summary"><span class="auto-details-chevron" aria-hidden="true"><i class="fas fa-chevron-right"></i></span> What starts an automation</summary>
                <div class="auto-reference-details-body">
                <p class="auto-reference-lead">Choose one trigger when you create or edit an automation:</p>
                <ul class="auto-trigger-cards">
                    <li class="auto-trigger-card">
                        <span class="auto-trigger-icon" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
                        <div class="auto-trigger-text">
                            <strong>Lead created</strong> — New lead from CRM or funnel opt-in.
                        </div>
                    </li>
                    <li class="auto-trigger-card">
                        <span class="auto-trigger-icon" aria-hidden="true"><i class="fas fa-envelope-open-text"></i></span>
                        <div class="auto-trigger-text">
                            <strong>Funnel opt-in</strong> — Someone submitted an opt-in form.
                        </div>
                    </li>
                    <li class="auto-trigger-card">
                        <span class="auto-trigger-icon" aria-hidden="true"><i class="fas fa-exchange-alt"></i></span>
                        <div class="auto-trigger-text">
                            <strong>Lead status changed</strong> — Pipeline status updated (e.g. New → Contacted).
                        </div>
                    </li>
                </ul>
                </div>
            </details>
        </div>
    </section>

    {{-- Delete confirmation modal --}}
    <div id="auto-delete-modal" class="auto-modal" role="dialog" aria-labelledby="auto-delete-modal-title" aria-modal="true" hidden>
        <div class="auto-modal-backdrop" data-auto-modal-close></div>
        <div class="auto-modal-dialog">
            <div class="auto-modal-content">
                <h3 id="auto-delete-modal-title" class="auto-modal-title">Delete automation?</h3>
                <p class="auto-modal-body">You are about to delete <strong id="auto-delete-modal-name"></strong>. This cannot be undone.</p>
                <form id="auto-delete-form" method="POST" action="" class="auto-form-inline">
                    @csrf
                    @method('DELETE')
                    <div class="auto-modal-actions">
                        <button type="button" class="auto-btn-secondary" data-auto-modal-close>Cancel</button>
                        <button type="submit" class="auto-btn-danger"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/automation.css') }}">
@endsection

@section('scripts')
<script>
(function() {
    var modal = document.getElementById('auto-delete-modal');
    var form = document.getElementById('auto-delete-form');
    var nameEl = document.getElementById('auto-delete-modal-name');
    if (!modal || !form) return;

    function openModal(workflowName, deleteUrl) {
        nameEl.textContent = workflowName;
        form.action = deleteUrl;
        modal.hidden = false;
        modal.classList.add('auto-modal-open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.hidden = true;
        modal.classList.remove('auto-modal-open');
        document.body.style.overflow = '';
    }

    function bindDelete(el) {
        el.addEventListener('click', function() {
            openModal(this.getAttribute('data-workflow-name'), this.getAttribute('data-delete-url'));
        });
    }
    document.querySelectorAll('[data-auto-delete]').forEach(bindDelete);

    modal.querySelectorAll('[data-auto-modal-close]').forEach(function(el) {
        el.addEventListener('click', closeModal);
    });
    modal.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
})();
</script>
@endsection
