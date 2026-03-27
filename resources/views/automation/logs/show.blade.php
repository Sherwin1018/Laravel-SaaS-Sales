@extends('automation.layout')

@section('title', 'Log Details')

@section('automation_content')
    <div class="automation-page-header" style="margin-bottom: 24px;">
        <a href="{{ route('automation.logs.index') }}" style="font-size: 14px; color: var(--theme-primary); text-decoration: none; margin-bottom: 8px; display: inline-block;">&larr; Back to Logs</a>
        <h2 style="font-size: 18px; margin: 0 0 6px 0; color: var(--theme-sidebar-text, #1E40AF);">Log Details</h2>
        <p style="font-size: 14px; color: #64748B; margin: 0;">Run at {{ \Carbon\Carbon::parse($log['time'])->format('M j, Y H:i:s') }}</p>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Summary</h3>
        <table>
            <tr><td style="width: 160px; font-weight: 600;">Automation</td><td>{{ $log['automation_name'] ?? '—' }}</td></tr>
            <tr><td style="font-weight: 600;">Trigger</td><td>{{ $log['trigger'] ?? '—' }}</td></tr>
            <tr><td style="font-weight: 600;">Result</td><td>@if(($log['result'] ?? '') === 'success')<span class="badge badge-success">Success</span>@else<span class="badge badge-failed">Failed</span>@endif</td></tr>
            <tr><td style="font-weight: 600;">Type</td><td>{{ ucfirst($log['type'] ?? '—') }}</td></tr>
        </table>
    </div>

    <div class="card">
        <h3>Details (payload / metadata)</h3>
        <details>
            <summary style="cursor: pointer; font-weight: 600;">View payload</summary>
        <pre style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 16px; overflow-x: auto; font-size: 12px; margin-top: 12px;">{{ json_encode($log['details'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </details>
    </div>

    @push('styles')
    <style>
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #DCFCE7; color: #166534; }
        .badge-failed { background: #FEE2E2; color: #B91C1C; }
    </style>
    @endpush
@endsection
