@extends('automation.layout')

@section('title', 'Automation Logs')

@section('automation_content')
    <div class="automation-page-header" style="margin-bottom: 24px;">
        <h2 style="font-size: 18px; margin: 0 0 6px 0; color: var(--theme-sidebar-text, #1E40AF);">Logs</h2>
        <p style="font-size: 14px; color: #64748B; margin: 0;">View automation run history and details.</p>
    </div>

    <div class="actions" style="margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <form method="GET" action="{{ route('automation.logs.index') }}" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <select name="date_range" style="padding: 8px 12px; border: 1px solid #DBEAFE; border-radius: 6px;">
                <option value="today" {{ ($filters['date_range'] ?? '') === 'today' ? 'selected' : '' }}>Today</option>
                <option value="7" {{ ($filters['date_range'] ?? '7') === '7' ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ ($filters['date_range'] ?? '') === '30' ? 'selected' : '' }}>Last 30 days</option>
            </select>
            <select name="type" style="padding: 8px 12px; border: 1px solid #DBEAFE; border-radius: 6px;">
                <option value="">All types</option>
                <option value="sequence" {{ ($filters['type'] ?? '') === 'sequence' ? 'selected' : '' }}>Sequence</option>
                <option value="workflow" {{ ($filters['type'] ?? '') === 'workflow' ? 'selected' : '' }}>Workflow</option>
            </select>
            <select name="result" style="padding: 8px 12px; border: 1px solid #DBEAFE; border-radius: 6px;">
                <option value="">All statuses</option>
                <option value="success" {{ ($filters['result'] ?? '') === 'success' ? 'selected' : '' }}>Success</option>
                <option value="failed" {{ ($filters['result'] ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
            <button type="submit" class="btn-create" style="padding: 8px 14px;"><i class="fas fa-filter"></i> Apply</button>
        </form>
    </div>

    <div class="card">
        @if(count($logs) > 0)
            <div class="table-responsive-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Automation Name</th>
                            <th>Trigger</th>
                            <th>Result</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log['time'])->format('M j, Y H:i') }}</td>
                                <td>{{ $log['automation_name'] ?? '—' }}</td>
                                <td>{{ $log['trigger'] ?? '—' }}</td>
                                <td>
                                    @if(($log['result'] ?? '') === 'success')
                                        <span class="badge badge-success">Success</span>
                                    @else
                                        <span class="badge badge-failed">Failed</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('automation.logs.show', $log['id']) }}" class="icon-btn" title="View details"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-history"></i></div>
                <h3>No logs yet</h3>
                <p>Logs will appear here when sequences or workflows run.</p>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #DCFCE7; color: #166534; }
        .badge-failed { background: #FEE2E2; color: #B91C1C; }
        .empty-state { text-align: center; padding: 48px 24px; }
        .empty-state-icon { font-size: 48px; color: #CBD5E1; margin-bottom: 16px; }
        .empty-state h3 { margin: 0 0 8px 0; font-size: 18px; color: var(--theme-sidebar-text, #1E40AF); }
        .empty-state p { color: #64748B; margin: 0; font-size: 14px; }
        .icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border: none; border-radius: 6px; background: transparent; color: var(--theme-primary, #2563EB); cursor: pointer; font-size: 14px; text-decoration: none; transition: background 0.15s, color 0.15s; }
        .icon-btn:hover { background: #EFF6FF; color: var(--theme-primary, #2563EB); }
        @media (max-width: 768px) { .table-responsive-wrap { overflow-x: auto; } table { min-width: 520px; } }
    </style>
    @endpush
@endsection
