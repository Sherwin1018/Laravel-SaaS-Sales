@extends('automation.layout')

@section('title', 'Automation Overview')

@section('automation_content')
    <div class="automation-page-header" style="margin-bottom: 24px;">
        <h2 style="font-size: 18px; margin: 0 0 6px 0; color: var(--theme-sidebar-text, #1E40AF);">Overview</h2>
        <p style="font-size: 14px; color: #64748B; margin: 0;">Summary of your automations and recent activity.</p>
    </div>

    <div class="kpi-cards" style="margin-bottom: 30px;">
        <div class="card">
            <h3>Active Automations</h3>
            <p>{{ $stats['active_automations'] ?? 0 }}</p>
        </div>
        <div class="card">
            <h3>Emails Sent (Today)</h3>
            <p>{{ $stats['emails_sent_today'] ?? 0 }}</p>
        </div>
        <div class="card">
            <h3>Emails Sent (7 days)</h3>
            <p>{{ $stats['emails_sent_7_days'] ?? 0 }}</p>
        </div>
        <div class="card">
            <h3>Leads Triggered</h3>
            <p>{{ $stats['leads_triggered'] ?? 0 }}</p>
        </div>
        <div class="card">
            <h3>Failed Runs</h3>
            <p>{{ $stats['failed_runs'] ?? 0 }}</p>
        </div>
    </div>

    <div class="card">
        <h3>Recent Activity</h3>
        @if(!empty($recentActivity))
            <table>
                <thead>
                    <tr>
                        <th>Automation</th>
                        <th>Trigger</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentActivity as $item)
                        <tr>
                            <td>{{ $item['name'] ?? '—' }}</td>
                            <td>{{ $item['trigger'] ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item['time'])->diffForHumans() }}</td>
                            <td>
                                @if(($item['status'] ?? '') === 'success')
                                    <span class="badge badge-success">Success</span>
                                @else
                                    <span class="badge badge-failed">Failed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #64748B; margin: 0;">No recent activity. Create sequences or workflows to see activity here.</p>
        @endif
    </div>

    @push('styles')
    <style>
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #DCFCE7; color: #166534; }
        .badge-failed { background: #FEE2E2; color: #B91C1C; }
    </style>
    @endpush
@endsection
