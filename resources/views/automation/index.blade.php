@extends('layouts.admin')

@section('title', 'Automation')

@section('content')
    @php
        $isSuperAdmin = auth()->user()->hasRole('super-admin');
        $toggleRoute = route('admin.automation.toggle');
        $isActive = ($status['active'] ?? null) === true;
        $nextActive = $isActive ? 0 : 1;
        $toggleLabel = $isActive ? 'Pause' : 'Play';
        $toggleIcon = $isActive ? 'fa-pause' : 'fa-play';
        $toggleBg = $isActive ? '#991b1b' : '#166534';
        $toggleDisabled = !($status['configured'] ?? false);
        $statusPalette = static function (string $tone): array {
            return match ($tone) {
                'positive' => ['bg' => 'rgba(22, 163, 74, 0.08)', 'border' => 'rgba(22, 163, 74, 0.18)', 'text' => '#166534'],
                'warning' => ['bg' => 'rgba(217, 119, 6, 0.08)', 'border' => 'rgba(217, 119, 6, 0.18)', 'text' => '#92400E'],
                'danger' => ['bg' => 'rgba(153, 27, 27, 0.08)', 'border' => 'rgba(153, 27, 27, 0.18)', 'text' => '#991B1B'],
                default => ['bg' => 'rgba(71, 85, 105, 0.08)', 'border' => 'rgba(71, 85, 105, 0.18)', 'text' => '#475569'],
            };
        };
    @endphp
    <div class="top-header">
        <h1>Automation</h1>
    </div>

    <div class="card" style="max-width: 760px; margin: 0 auto;">
        <h3 style="margin-top: 0;">{{ $isSuperAdmin ? 'Shared n8n Workflow Control' : 'Shared Automation Status' }}</h3>

        @if(!($status['configured'] ?? false))
            <div style="padding: 12px 14px; border-radius: 10px; background: rgba(217, 119, 6, 0.08); color: #92400E; border: 1px solid rgba(217, 119, 6, 0.18); margin-bottom: 18px;">
                n8n control is not fully configured. Set <code>N8N_BASE_URL</code>, <code>N8N_API_KEY</code>, and <code>N8N_WORKFLOW_ID</code> in <code>.env</code>.
            </div>
        @endif

        @if(!empty($statusError))
            <div style="padding: 12px 14px; border-radius: 10px; background: rgba(153, 27, 27, 0.08); color: #991B1B; border: 1px solid rgba(153, 27, 27, 0.18); margin-bottom: 18px;">
                Automation status could not be refreshed: {{ $statusError }}
                @if($isSuperAdmin && ($status['configured'] ?? false))
                    <div style="margin-top:6px;font-size:12px;line-height:1.5;color:#7F1D1D;">
                        The Play/Pause control is still available below if n8n is configured, but status will stay unavailable until the API access is fixed.
                    </div>
                @endif
            </div>
        @endif

        @if(!$isSuperAdmin && $tenantAutomation)
            <div style="padding: 12px 14px; border-radius: 10px; background: rgba(71, 85, 105, 0.08); color: #334155; border: 1px solid rgba(71, 85, 105, 0.18); margin-bottom: 18px; line-height: 1.6;">
                Shared workflow controls are managed by the platform team. This page shows your plan access, current billing state, and whether the shared automation engine is available for your workspace.
            </div>

            <div class="app-grid app-grid--2" style="gap:12px;margin-bottom:24px;">
                @foreach([
                    'Platform Automation' => $tenantAutomation['platform'],
                    'Plan Access' => $tenantAutomation['plan_access'],
                    'Billing Status' => $tenantAutomation['billing'],
                    'Your Tenant Automation' => $tenantAutomation['tenant'],
                ] as $title => $item)
                    @php($palette = $statusPalette((string) ($item['tone'] ?? 'neutral')))
                    <div style="padding:14px 16px;border-radius:12px;background:{{ $palette['bg'] }};border:1px solid {{ $palette['border'] }};">
                        <div style="font-size:12px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:{{ $palette['text'] }};">{{ $title }}</div>
                        <div style="margin-top:8px;font-size:22px;font-weight:800;color:{{ $palette['text'] }};">{{ $item['label'] ?? $emptyDash }}</div>
                        <div style="margin-top:8px;font-size:13px;line-height:1.6;color:#334155;">{{ $item['summary'] ?? $emptyDash }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="app-detail-grid" style="margin-bottom: 24px; font-size: 14px;">
            <div style="font-weight: 700; color: #1f2937;">Workflow Name</div>
            <div>{{ $status['name'] ?? $emptyDash }}</div>

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

        @if($canControlWorkflow ?? false)
            <div class="app-grid app-grid--2" style="gap:10px;margin-bottom:20px;">
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
                        {{ $toggleDisabled ? 'disabled' : '' }}>
                        <i class="fas {{ $toggleIcon }}" style="margin-right: 6px;"></i> {{ $toggleLabel }}
                    </button>
                </form>
            </div>
        @endif

        @if(($canControlWorkflow ?? false) && ($recentFailures ?? collect())->isNotEmpty())
            <div style="margin-top:22px;">
                <h4 style="margin:0 0 8px;">Recent Automation Failures</h4>
                <div style="margin:0 0 10px;font-size:12px;color:#64748B;">This list shows the most recent failures overall and is not limited to the last 24 hours.</div>
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
                                    <td style="padding:8px;border-bottom:1px solid #F1F5F9;">{{ $log->message ?: $emptyDash }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
