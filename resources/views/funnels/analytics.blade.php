@extends('layouts.admin')

@section('title', 'Funnel Analytics')

@section('styles')
    <style>
        .analytics-shell { display: grid; gap: 18px; }
        .analytics-topbar { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px; }
        .analytics-topbar h1 { margin: 0; color: var(--theme-primary, #240E35); }
        .analytics-topbar p { margin: 6px 0 0; color: var(--theme-muted, #6B7280); }
        .analytics-actions { display:flex; flex-wrap:wrap; gap:10px; }
        .analytics-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 14px; border-radius:10px; border:1px solid var(--theme-border, #E6E1EF); background:#fff; color:var(--theme-primary, #240E35); text-decoration:none; font-weight:700; }
        .analytics-btn.primary { background: var(--theme-primary, #240E35); color:#fff; border-color: var(--theme-primary, #240E35); }
        .analytics-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
        .analytics-field { display:grid; gap:6px; min-width:180px; }
        .analytics-field label { font-size:12px; font-weight:800; color: var(--theme-muted, #6B7280); text-transform:uppercase; letter-spacing:.04em; }
        .analytics-field input { padding:10px 12px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff; }
        .analytics-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; }
        .analytics-kpi { background:#fff; border:1px solid var(--theme-border, #E6E1EF); border-radius:16px; padding:18px; box-shadow:0 10px 30px rgba(15,23,42,.04); }
        .analytics-kpi-label { font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color: var(--theme-muted, #6B7280); }
        .analytics-kpi-value { margin-top:10px; font-size:30px; font-weight:900; color: var(--theme-primary, #240E35); }
        .analytics-kpi-sub { margin-top:8px; color: var(--theme-muted, #6B7280); font-size:13px; }
        .analytics-grid { display:grid; grid-template-columns:2fr 1fr; gap:18px; }
        .analytics-card { background:#fff; border:1px solid var(--theme-border, #E6E1EF); border-radius:18px; padding:18px; box-shadow:0 10px 30px rgba(15,23,42,.04); }
        .analytics-card h3 { margin:0 0 14px; color: var(--theme-primary, #240E35); }
        .analytics-chart-wrap { position:relative; min-height:280px; }
        .analytics-table-wrap { overflow:auto; }
        .analytics-table { width:100%; border-collapse:collapse; min-width:640px; }
        .analytics-table th, .analytics-table td { padding:12px 10px; border-bottom:1px solid var(--theme-border, #E6E1EF); text-align:left; vertical-align:top; }
        .analytics-table th { font-size:12px; text-transform:uppercase; letter-spacing:.05em; color: var(--theme-muted, #6B7280); }
        .analytics-pill { display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; background: var(--theme-surface-soft, #F3EEF7); color: var(--theme-primary, #240E35); font-size:12px; font-weight:800; }
        .analytics-events { display:grid; gap:12px; }
        .analytics-event { border:1px solid var(--theme-border, #E6E1EF); border-radius:14px; padding:14px; background:linear-gradient(180deg,#fff,#fcfbfe); }
        .analytics-event-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px; }
        .analytics-event-meta { color: var(--theme-muted, #6B7280); font-size:13px; line-height:1.5; }
        .analytics-empty { padding:18px; border-radius:14px; background: var(--theme-surface-softer, #F7F7FB); color: var(--theme-muted, #6B7280); }
        @media (max-width: 960px) {
            .analytics-grid { grid-template-columns:1fr; }
        }
    </style>
@endsection

@section('content')
    @php
        $totals = $analytics['totals'] ?? [];
        $rates = $analytics['rates'] ?? [];
        $stepVisits = collect($analytics['step_visits'] ?? []);
        $dropOff = collect($analytics['drop_off'] ?? []);
        $eventBreakdown = collect($analytics['step_event_breakdown'] ?? []);
        $stepLabels = $stepVisits->map(fn ($row) => $row['step_title'])->values()->all();
        $stepValues = $stepVisits->map(fn ($row) => (int) $row['visits'])->values()->all();
        $stepDropOffValues = $dropOff->map(fn ($row) => (int) ($row['drop_off'] ?? 0))->values()->all();
        $offerRateValues = [
            (float) ($rates['upsell_acceptance_rate'] ?? 0),
            (float) ($rates['downsell_acceptance_rate'] ?? 0),
            (float) ($rates['abandoned_checkout_rate'] ?? 0),
        ];
    @endphp

    <div class="analytics-shell">
        <div class="analytics-topbar">
            <div>
                <h1>{{ $funnel->name }} Analytics</h1>
                <p>Track views, opt-ins, checkout starts, revenue, drop-off, and recent funnel events in one place.</p>
            </div>
            <div class="analytics-actions">
                <a href="{{ route('funnels.index') }}" class="analytics-btn"><i class="fas fa-arrow-left"></i> Back to Funnels</a>
                <a href="{{ route('funnels.edit', $funnel) }}" class="analytics-btn primary"><i class="fas fa-pen"></i> Open Builder</a>
            </div>
        </div>

        <div class="analytics-card">
            <form method="GET" action="{{ route('funnels.analytics', $funnel) }}" class="analytics-filters">
                <div class="analytics-field">
                    <label for="from">From</label>
                    <input id="from" type="date" name="from" value="{{ $filters['from'] }}">
                </div>
                <div class="analytics-field">
                    <label for="to">To</label>
                    <input id="to" type="date" name="to" value="{{ $filters['to'] }}">
                </div>
                <button type="submit" class="analytics-btn primary"><i class="fas fa-filter"></i> Apply Filter</button>
                <a href="{{ route('funnels.analytics', $funnel) }}" class="analytics-btn">Clear</a>
            </form>
        </div>

        <div class="analytics-kpis">
            <div class="analytics-kpi">
                <div class="analytics-kpi-label">Entry Visits</div>
                <div class="analytics-kpi-value">{{ number_format((int) ($totals['entry_visits'] ?? 0)) }}</div>
                <div class="analytics-kpi-sub">Unique first-step visits</div>
            </div>
            <div class="analytics-kpi">
                <div class="analytics-kpi-label">Opt-ins</div>
                <div class="analytics-kpi-value">{{ number_format((int) ($totals['opt_in_count'] ?? 0)) }}</div>
                <div class="analytics-kpi-sub">{{ number_format((float) ($rates['opt_in_conversion_rate'] ?? 0), 2) }}% conversion</div>
            </div>
            <div class="analytics-kpi">
                <div class="analytics-kpi-label">Checkout Starts</div>
                <div class="analytics-kpi-value">{{ number_format((int) ($totals['checkout_start_count'] ?? 0)) }}</div>
                <div class="analytics-kpi-sub">{{ number_format((float) ($rates['checkout_conversion_rate'] ?? 0), 2) }}% conversion</div>
            </div>
            <div class="analytics-kpi">
                <div class="analytics-kpi-label">Paid</div>
                <div class="analytics-kpi-value">{{ number_format((int) ($totals['paid_count'] ?? 0)) }}</div>
                <div class="analytics-kpi-sub">{{ number_format((float) ($rates['paid_conversion_rate'] ?? 0), 2) }}% conversion</div>
            </div>
            <div class="analytics-kpi">
                <div class="analytics-kpi-label">Revenue</div>
                <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['revenue'] ?? 0), 2) }}</div>
                <div class="analytics-kpi-sub">Paid revenue tied to this funnel</div>
            </div>
            <div class="analytics-kpi">
                <div class="analytics-kpi-label">Abandoned Checkout</div>
                <div class="analytics-kpi-value">{{ number_format((int) ($totals['abandoned_checkout_count'] ?? 0)) }}</div>
                <div class="analytics-kpi-sub">{{ number_format((float) ($rates['abandoned_checkout_rate'] ?? 0), 2) }}% abandonment</div>
            </div>
        </div>

        <div class="analytics-grid">
            <div class="analytics-card">
                <h3>Step Visits</h3>
                <div class="analytics-chart-wrap">
                    <canvas id="stepVisitsChart"></canvas>
                </div>
            </div>

            <div class="analytics-card">
                <h3>Offer Rates</h3>
                <div class="analytics-chart-wrap">
                    <canvas id="offerRatesChart"></canvas>
                </div>
            </div>
        </div>

        <div class="analytics-card">
            <h3>Step Performance</h3>
            <div class="analytics-table-wrap">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Step</th>
                            <th>Type</th>
                            <th>Visits</th>
                            <th>Drop-off</th>
                            <th>Event Mix</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stepVisits as $row)
                            @php
                                $rowDropOff = $dropOff->firstWhere('step_id', $row['step_id']);
                                $rowEvents = $eventBreakdown->firstWhere('step_id', $row['step_id']);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $row['step_title'] }}</strong><br>
                                    <span style="color:var(--theme-muted, #6B7280); font-size:13px;">/{{ $row['step_slug'] }}</span>
                                </td>
                                <td><span class="analytics-pill">{{ ucwords(str_replace('_', ' ', $row['step_type'])) }}</span></td>
                                <td>{{ number_format((int) $row['visits']) }}</td>
                                <td>{{ number_format((int) ($rowDropOff['drop_off'] ?? 0)) }}</td>
                                <td>
                                    @if(!empty($rowEvents['events']))
                                        @foreach($rowEvents['events'] as $eventName => $count)
                                            <div style="margin-bottom:4px;">{{ $eventName }}: {{ $count }}</div>
                                        @endforeach
                                    @else
                                        <span style="color:var(--theme-muted, #6B7280);">No tracked events yet</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No step analytics yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="analytics-card">
            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px;">
                <h3 style="margin:0;">Recent Funnel Events</h3>
                <a href="{{ route('funnels.events', $funnel) }}" class="analytics-btn">Open Raw Events JSON</a>
            </div>

            @if($events->count() > 0)
                <div class="analytics-events">
                    @foreach($events as $event)
                        <div class="analytics-event">
                            <div class="analytics-event-head">
                                <span class="analytics-pill">{{ $event->event_name }}</span>
                                <strong>{{ optional($event->occurred_at)->format('M j, Y g:i A') }}</strong>
                            </div>
                            <div class="analytics-event-meta">
                                Step: {{ $event->step->title ?? 'N/A' }}<br>
                                Session: {{ $event->session_identifier ?? 'N/A' }}<br>
                                Lead: {{ $event->lead->email ?? ($event->lead->name ?? 'N/A') }}<br>
                                Payment: {{ $event->payment ? ('PHP ' . number_format((float) $event->payment->amount, 2) . ' / ' . $event->payment->status) : 'N/A' }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top:16px;">
                    {{ $events->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="analytics-empty">No funnel events have been recorded yet for the selected date range.</div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const stepLabels = @json($stepLabels);
        const stepValues = @json($stepValues);
        const stepDropOff = @json($stepDropOffValues);
        const offerRateLabels = ['Upsell Acceptance', 'Downsell Acceptance', 'Abandoned Checkout'];
        const offerRateValues = @json($offerRateValues);

        const stepVisitsCanvas = document.getElementById('stepVisitsChart');
        if (stepVisitsCanvas) {
            new Chart(stepVisitsCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: stepLabels,
                    datasets: [
                        {
                            label: 'Visits',
                            data: stepValues,
                            backgroundColor: '#240E35',
                            borderRadius: 10,
                        },
                        {
                            label: 'Drop-off',
                            data: stepDropOff,
                            backgroundColor: '#C084FC',
                            borderRadius: 10,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        const offerRatesCanvas = document.getElementById('offerRatesChart');
        if (offerRatesCanvas) {
            new Chart(offerRatesCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: offerRateLabels,
                    datasets: [{
                        data: offerRateValues,
                        backgroundColor: ['#240E35', '#6B4A7A', '#F97316'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        }
    </script>
@endsection
