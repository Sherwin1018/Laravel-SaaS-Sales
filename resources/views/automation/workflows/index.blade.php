@extends('automation.layout')

@section('title', 'Workflows')

@section('automation_content')
    <div class="automation-page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <h2 style="font-size: 18px; margin: 0 0 6px 0; color: var(--theme-sidebar-text, #1E40AF);">Workflows</h2>
            <p style="font-size: 14px; color: #64748B; margin: 0;">Event-based automations (e.g. when lead is created or status changes). Tenant workflows only; system automations are read-only.</p>
        </div>
        <a href="{{ route('automation.workflows.create') }}" class="btn-create"><i class="fas fa-plus"></i> Create Workflow</a>
    </div>

    @if(session('success'))
        <p class="success-message" style="margin-bottom: 16px; padding: 10px 14px; background: #DCFCE7; color: #166534; border-radius: 6px; font-size: 14px;">{{ session('success') }}</p>
    @endif

    <div class="actions" style="margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <form method="GET" action="{{ route('automation.workflows.index') }}" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search workflows..."
                style="padding: 8px 12px; border: 1px solid #DBEAFE; border-radius: 6px; min-width: 200px;">
            <select name="status" style="padding: 8px 12px; border: 1px solid #DBEAFE; border-radius: 6px;">
                <option value="">All statuses</option>
                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="paused" {{ ($filters['status'] ?? '') === 'paused' ? 'selected' : '' }}>Paused</option>
            </select>
            <button type="submit" class="btn-create" style="padding: 8px 14px;"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <div class="card">
        @if(count($workflows) > 0)
            <div class="table-responsive-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Trigger</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workflows as $wf)
                            <tr>
                                <td>{{ $wf['name'] ?? '—' }}</td>
                                <td>{{ $wf['trigger_label'] ?? $wf['trigger'] ?? '—' }}</td>
                                <td>{{ $wf['action_label'] ?? '—' }}</td>
                                <td>
                                    @if(($wf['status'] ?? '') === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-paused">Paused</span>
                                    @endif
                                </td>
                                <td>{{ isset($wf['updated']) ? \Carbon\Carbon::parse($wf['updated'])->format('M j, Y') : '—' }}</td>
                                <td class="automation-actions-cell">
                                    @if(($wf['type'] ?? 'tenant') === 'tenant')
                                        <a href="{{ route('automation.workflows.edit', $wf['id']) }}" class="icon-btn" title="Edit"><i class="fas fa-pen"></i></a>
                                        <form method="POST" action="{{ route('automation.workflows.toggle', $wf['id']) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="icon-btn" title="{{ ($wf['status'] ?? '') === 'active' ? 'Pause' : 'Activate' }}"><i class="fas {{ ($wf['status'] ?? '') === 'active' ? 'fa-pause' : 'fa-play' }}"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('automation.workflows.duplicate', $wf['id']) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="icon-btn" title="Duplicate"><i class="fas fa-copy"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('automation.workflows.destroy', $wf['id']) }}" style="display: inline;" onsubmit="return confirm('Delete this workflow?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn icon-btn-danger" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    @else
                                        <span style="color: #64748B; font-size: 13px;">System (read-only)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-project-diagram"></i></div>
                <h3>No workflows created yet.</h3>
                <p>Create a workflow to run actions when events like lead created or funnel opt-in occur.</p>
                <a href="{{ route('automation.workflows.create') }}" class="btn-create"><i class="fas fa-plus"></i> Create Workflow</a>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #DCFCE7; color: #166534; }
        .badge-paused { background: #FEF3C7; color: #92400E; }
        .empty-state { text-align: center; padding: 48px 24px; }
        .empty-state-icon { font-size: 48px; color: #CBD5E1; margin-bottom: 16px; }
        .empty-state h3 { margin: 0 0 8px 0; font-size: 18px; color: var(--theme-sidebar-text, #1E40AF); }
        .empty-state p { color: #64748B; margin: 0 0 20px 0; font-size: 14px; }
        .automation-actions-cell { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border: none; border-radius: 6px; background: transparent; color: var(--theme-primary, #2563EB); cursor: pointer; font-size: 14px; text-decoration: none; transition: background 0.15s, color 0.15s; }
        .icon-btn:hover { background: #EFF6FF; color: var(--theme-primary, #2563EB); }
        .icon-btn-danger { color: #DC2626; }
        .icon-btn-danger:hover { background: #FEE2E2; color: #B91C1C; }
        @media (max-width: 768px) { .table-responsive-wrap { overflow-x: auto; } table { min-width: 640px; } }
    </style>
    @endpush
@endsection
