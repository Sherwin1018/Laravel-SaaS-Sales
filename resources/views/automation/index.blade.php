@extends('layouts.admin')

@section('title', 'Automation')

@section('content')
    @php
        $toggleRoute = auth()->user()->hasRole('super-admin')
            ? route('admin.automation.toggle')
            : route('automation.toggle');
        $isActive = ($status['active'] ?? null) === true;
        $nextActive = $isActive ? 0 : 1;
        $toggleLabel = $isActive ? 'Pause' : 'Play';
        $toggleIcon = $isActive ? 'fa-pause' : 'fa-play';
        $toggleBg = $isActive ? '#991b1b' : '#166534';
    @endphp
    <div class="top-header">
        <h1>Automation</h1>
    </div>

    <div class="card" style="max-width: 760px; margin: 0 auto;">
        <h3 style="margin-top: 0;">n8n Workflow Control</h3>
        <p style="color: #475569; margin-bottom: 20px;">
            Use this panel to monitor and toggle your n8n workflow state without leaving the app.
        </p>

        @if(!($status['configured'] ?? false))
            <div style="padding: 12px 14px; border-radius: 10px; background: rgba(217, 119, 6, 0.08); color: #92400E; border: 1px solid rgba(217, 119, 6, 0.18); margin-bottom: 18px;">
                n8n control is not fully configured. Set <code>N8N_BASE_URL</code>, <code>N8N_API_KEY</code>, and <code>N8N_WORKFLOW_ID</code> in <code>.env</code>.
            </div>
        @endif

        @if(!empty($statusError))
            <div style="padding: 12px 14px; border-radius: 10px; background: rgba(153, 27, 27, 0.08); color: #991B1B; border: 1px solid rgba(153, 27, 27, 0.18); margin-bottom: 18px;">
                Automation status could not be refreshed: {{ $statusError }}
            </div>
        @endif

        <div style="display: grid; grid-template-columns: 170px 1fr; gap: 8px 16px; margin-bottom: 24px; font-size: 14px;">
            <div style="font-weight: 700; color: #1f2937;">Workflow Name</div>
            <div>{{ $status['name'] ?? 'N/A' }}</div>

            <div style="font-weight: 700; color: #1f2937;">Current State</div>
            <div>
                @if(($status['active'] ?? null) === true)
                    <span style="color: #166534; font-weight: 700;">Active</span>
                @elseif(($status['active'] ?? null) === false)
                    <span style="color: #991b1b; font-weight: 700;">Inactive</span>
                @else
                    <span style="color: #475569; font-weight: 700;">Unknown</span>
                @endif
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
            <div style="padding:12px;border-radius:10px;background:rgba(22,163,74,0.08);border:1px solid rgba(22,163,74,0.2);">
                <div style="font-size:12px;font-weight:700;color:#166534;">Emails Sent (24h)</div>
                <div style="font-size:24px;font-weight:800;color:#166534;">{{ (int) ($deliverySummary['sent_last_24h'] ?? 0) }}</div>
            </div>
            <div style="padding:12px;border-radius:10px;background:rgba(153,27,27,0.08);border:1px solid rgba(153,27,27,0.2);">
                <div style="font-size:12px;font-weight:700;color:#991b1b;">Errors (24h)</div>
                <div style="font-size:24px;font-weight:800;color:#991b1b;">{{ (int) ($deliverySummary['failed_last_24h'] ?? 0) }}</div>
            </div>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form method="POST" action="{{ $toggleRoute }}">
                @csrf
                <input type="hidden" name="active" value="{{ $nextActive }}">
                <button type="submit"
                    style="padding: 10px 18px; background: {{ $toggleBg }}; color: #fff; border: 0; border-radius: 8px; font-weight: 700; cursor: pointer;"
                    {{ !($status['configured'] ?? false) || ($status['active'] ?? null) === null ? 'disabled' : '' }}>
                    <i class="fas {{ $toggleIcon }}" style="margin-right: 6px;"></i> {{ $toggleLabel }}
                </button>
            </form>
        </div>

        @if(($recentFailures ?? collect())->isNotEmpty())
            <div style="margin-top:22px;">
                <h4 style="margin:0 0 8px;">Recent Automation Failures</h4>
                <div style="max-height:260px;overflow:auto;border:1px solid #E5E7EB;border-radius:8px;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead style="background:#F8FAFC;">
                            <tr>
                                <th style="text-align:left;padding:8px;border-bottom:1px solid #E5E7EB;">Time</th>
                                <th style="text-align:left;padding:8px;border-bottom:1px solid #E5E7EB;">Event</th>
                                <th style="text-align:left;padding:8px;border-bottom:1px solid #E5E7EB;">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentFailures as $log)
                                <tr>
                                    <td style="padding:8px;border-bottom:1px solid #F1F5F9;">{{ optional($log->occurred_at)->format('Y-m-d H:i:s') }}</td>
                                    <td style="padding:8px;border-bottom:1px solid #F1F5F9;">{{ $log->event_type }}</td>
                                    <td style="padding:8px;border-bottom:1px solid #F1F5F9;">{{ $log->message ?: 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
