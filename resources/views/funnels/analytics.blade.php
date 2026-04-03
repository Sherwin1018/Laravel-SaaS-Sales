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
        .analytics-toggle-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:9px 14px; border-radius:10px; border:1px solid var(--theme-border, #E6E1EF); background:var(--theme-primary, #240E35); color:#fff; font-weight:800; cursor:pointer; }
        .analytics-filters { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }
        .analytics-field { display:grid; gap:6px; min-width:180px; }
        .analytics-field label { font-size:12px; font-weight:800; color: var(--theme-muted, #6B7280); text-transform:uppercase; letter-spacing:.04em; }
        .analytics-field input,
        .analytics-field select { padding:10px 12px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff; }
        .analytics-kpis { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; }
        .analytics-kpi { background:#fff; border:1px solid var(--theme-border, #E6E1EF); border-radius:16px; padding:18px; box-shadow:0 10px 30px rgba(15,23,42,.04); }
        .analytics-kpi-label { font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color: var(--theme-muted, #6B7280); }
        .analytics-kpi-value { margin-top:10px; font-size:30px; font-weight:900; color: var(--theme-primary, #240E35); }
        .analytics-kpi-sub { margin-top:8px; color: var(--theme-muted, #6B7280); font-size:13px; }
        .analytics-grid { display:grid; grid-template-columns:2fr 1fr; gap:18px; }
        .analytics-grid.analytics-grid--summary { grid-template-columns:minmax(0, 1.7fr) minmax(320px, .95fr) minmax(300px, .85fr); align-items:stretch; }
        .analytics-card { background:#fff; border:1px solid var(--theme-border, #E6E1EF); border-radius:18px; padding:18px; box-shadow:0 10px 30px rgba(15,23,42,.04); }
        .analytics-card h3 { margin:0 0 14px; color: var(--theme-primary, #240E35); }
        .analytics-chart-wrap { position:relative; min-height:280px; }
        .analytics-chart-wrap canvas { width:100% !important; height:100% !important; display:block; }
        .analytics-card.analytics-card--step-visits { width:100%; }
        .analytics-card.analytics-card--step-visits .analytics-chart-wrap { min-height: 260px; }
        .analytics-card.analytics-card--offer-rates { width:100%; }
        .analytics-card.analytics-card--offer-rates .analytics-chart-wrap { min-height: 260px; max-height: 260px; }
        .analytics-card.analytics-card--offer-counts { width:100%; }
        .analytics-chart-empty { min-height:280px; display:grid; place-items:center; text-align:center; padding:20px; border-radius:14px; background: var(--theme-surface-softer, #F7F7FB); color: var(--theme-muted, #6B7280); }
        .analytics-table-wrap { overflow:auto; }
        .analytics-table { width:100%; border-collapse:collapse; min-width:640px; }
        .analytics-table th, .analytics-table td { padding:12px 10px; border-bottom:1px solid var(--theme-border, #E6E1EF); text-align:left; vertical-align:top; }
        .analytics-table th { font-size:12px; text-transform:uppercase; letter-spacing:.05em; color: var(--theme-muted, #6B7280); }
        .analytics-pill { display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; background: var(--theme-surface-soft, #F3EEF7); color: var(--theme-primary, #240E35); font-size:12px; font-weight:800; }
        .analytics-mini-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .analytics-mini-stat { border:1px solid var(--theme-border, #E6E1EF); border-radius:14px; padding:14px; background:linear-gradient(180deg,#fff,#fcfbfe); }
        .analytics-mini-stat span { display:block; font-size:12px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color: var(--theme-muted, #6B7280); }
        .analytics-mini-stat strong { display:block; margin-top:8px; font-size:24px; color: var(--theme-primary, #240E35); }
        .analytics-events { display:grid; gap:12px; }
        .analytics-event { border:1px solid var(--theme-border, #E6E1EF); border-radius:14px; padding:14px; background:linear-gradient(180deg,#fff,#fcfbfe); }
        .analytics-event-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px; }
        .analytics-event-meta { color: var(--theme-muted, #6B7280); font-size:13px; line-height:1.5; }
        .analytics-empty { padding:18px; border-radius:14px; background: var(--theme-surface-softer, #F7F7FB); color: var(--theme-muted, #6B7280); }
        @media (max-width: 1200px) {
            .analytics-grid.analytics-grid--summary { grid-template-columns:1fr; }
        }
        @media (max-width: 960px) {
            .analytics-grid { grid-template-columns:1fr; }
        }
    </style>
@endsection

@section('content')
    @php
        $totals = $analytics['totals'] ?? [];
        $rates = $analytics['rates'] ?? [];
        $offerCounts = $analytics['offer_counts'] ?? [];
        $stepVisits = collect($analytics['step_visits'] ?? []);
        $dropOff = collect($analytics['drop_off'] ?? []);
        $eventBreakdown = collect($analytics['step_event_breakdown'] ?? []);
        $dailySeries = collect($analytics['daily_series'] ?? []);
        $conversionFunnel = collect($analytics['conversion_funnel'] ?? []);
        $stepLabels = $stepVisits->map(fn ($row) => $row['step_title'])->values()->all();
        $stepValues = $stepVisits->map(fn ($row) => (int) $row['visits'])->values()->all();
        $stepDropOffValues = $dropOff->map(fn ($row) => (int) ($row['drop_off'] ?? 0))->values()->all();
        $dailyLabels = $dailySeries->pluck('date')->values()->all();
        $dailyVisitValues = $dailySeries->pluck('entry_visits')->map(fn ($value) => (int) $value)->values()->all();
        $dailyOptInValues = $dailySeries->pluck('opt_ins')->map(fn ($value) => (int) $value)->values()->all();
        $dailyCheckoutValues = $dailySeries->pluck('checkout_starts')->map(fn ($value) => (int) $value)->values()->all();
        $dailyPaidValues = $dailySeries->pluck('paid')->map(fn ($value) => (int) $value)->values()->all();
        $conversionLabels = $conversionFunnel->pluck('label')->values()->all();
        $conversionValues = $conversionFunnel->pluck('count')->map(fn ($value) => (int) $value)->values()->all();
        $offerRateValues = [
            (float) ($rates['upsell_acceptance_rate'] ?? 0),
            (float) ($rates['downsell_acceptance_rate'] ?? 0),
            (float) ($rates['abandoned_checkout_rate'] ?? 0),
        ];
        $hasOfferData = ((int) ($offerCounts['upsell_accepted'] ?? 0) > 0)
            || ((int) ($offerCounts['upsell_declined'] ?? 0) > 0)
            || ((int) ($offerCounts['downsell_accepted'] ?? 0) > 0)
            || ((int) ($offerCounts['downsell_declined'] ?? 0) > 0)
            || ((float) ($rates['abandoned_checkout_rate'] ?? 0) > 0);
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
                <a href="{{ route('funnels.analytics.export', array_merge(['funnel' => $funnel], request()->query())) }}" class="analytics-btn"><i class="fas fa-file-export"></i> Export CSV</a>
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
                <div class="analytics-field">
                    <label for="step_id">Step</label>
                    <select id="step_id" name="step_id">
                        <option value="">All steps</option>
                        @foreach($funnel->steps->sortBy('position') as $step)
                            <option value="{{ $step->id }}" {{ (string) ($filters['step_id'] ?? '') === (string) $step->id ? 'selected' : '' }}>
                                {{ $step->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="analytics-field">
                    <label for="event_name">Event</label>
                    <select id="event_name" name="event_name">
                        <option value="">All events</option>
                        @foreach($supportedEvents as $eventName)
                            <option value="{{ $eventName }}" {{ (string) ($filters['event_name'] ?? '') === (string) $eventName ? 'selected' : '' }}>
                                {{ $eventName }}
                            </option>
                        @endforeach
                    </select>
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
            <div class="analytics-kpi">
                <div class="analytics-kpi-label">Average Order Value</div>
                <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['average_order_value'] ?? 0), 2) }}</div>
                <div class="analytics-kpi-sub">Average paid order amount</div>
            </div>
            <div class="analytics-kpi">
                <div class="analytics-kpi-label">Revenue Per Visit</div>
                <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['revenue_per_visit'] ?? 0), 2) }}</div>
                <div class="analytics-kpi-sub">Revenue divided by entry visits</div>
            </div>
        </div>

        {{-- UTM Source Performance Analysis --}}
        <div class="analytics-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid var(--theme-primary, #240E35);">
                <h3 style="margin: 0; color: var(--theme-primary, #240E35); font-size: 18px; font-weight: 600;">Source Performance Analysis for {{ $funnel->name }}</h3>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 8px; height: 8px; background: #059669; border-radius: 50%;"></div>
                    <span style="font-size: 12px; color: var(--theme-muted, #6B7280);">≥15%</span>
                    <div style="width: 8px; height: 8px; background: #D97706; border-radius: 50%;"></div>
                    <span style="font-size: 12px; color: var(--theme-muted, #6B7280);">8-14%</span>
                    <div style="width: 8px; height: 8px; background: #DC2626; border-radius: 50%;"></div>
                    <span style="font-size: 12px; color: var(--theme-muted, #6B7280);">&lt;8%</span>
                </div>
            </div>
            <div style="margin-bottom: 15px; color: var(--theme-muted, #6B7280); font-size: 14px;">
                Complete journey from visit to conversion by UTM source for this specific funnel
            </div>
            <div class="analytics-table-wrap">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th style="text-align: center;">Visits</th>
                            <th style="text-align: center;">Leads</th>
                            <th style="text-align: center;">Contacted</th>
                            <th style="text-align: center;">Proposal</th>
                            <th style="text-align: center;">Won</th>
                            <th style="text-align: center;">Conv%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sourcePerformance as $source)
                            <tr>
                                <td style="font-weight: 600;">{{ $source['source'] }}</td>
                                <td style="text-align: center;">{{ $source['visits'] }}</td>
                                <td style="text-align: center;">{{ $source['leads'] }}</td>
                                <td style="text-align: center;">{{ $source['contacted'] }}</td>
                                <td style="text-align: center;">{{ $source['proposal'] }}</td>
                                <td style="text-align: center;">{{ $source['won'] }}</td>
                                <td style="text-align: center; font-weight: bold; color: {{ $source['conversion_rate'] >= 15 ? '#059669' : ($source['conversion_rate'] >= 8 ? '#D97706' : '#DC2626') }};">
                                    {{ $source['conversion_rate'] }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 20px; text-align: center; color: var(--theme-muted, #6B7280);">
                                    No UTM data available for this funnel yet. Start driving traffic with UTM parameters to see performance analytics.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="analytics-grid analytics-grid--summary">
            <div class="analytics-card analytics-card--step-visits">
                <h3>Step Visits</h3>
                <div class="analytics-chart-wrap">
                    <canvas id="stepVisitsChart"></canvas>
                </div>
            </div>
            <div class="analytics-card analytics-card--offer-rates">
                <h3>Offer Rates</h3>
                @if($hasOfferData)
                    <div class="analytics-chart-wrap">
                        <canvas id="offerRatesChart"></canvas>
                    </div>
                @else
                    <div class="analytics-chart-empty">
                        <div>
                            <strong style="display:block; margin-bottom:8px; color:var(--theme-primary, #240E35);">No offer data yet</strong>
                            Complete checkout in the public funnel and click the upsell or downsell accept/decline buttons to populate this section.
                        </div>
                    </div>
                @endif
            </div>

            <div class="analytics-card analytics-card--offer-counts">
                <h3>Offer Counts</h3>
                <div class="analytics-mini-grid">
                    <div class="analytics-mini-stat">
                        <span>Upsell Accepted</span>
                        <strong>{{ number_format((int) ($offerCounts['upsell_accepted'] ?? 0)) }}</strong>
                    </div>
                    <div class="analytics-mini-stat">
                        <span>Upsell Declined</span>
                        <strong>{{ number_format((int) ($offerCounts['upsell_declined'] ?? 0)) }}</strong>
                    </div>
                    <div class="analytics-mini-stat">
                        <span>Downsell Accepted</span>
                        <strong>{{ number_format((int) ($offerCounts['downsell_accepted'] ?? 0)) }}</strong>
                    </div>
                    <div class="analytics-mini-stat">
                        <span>Downsell Declined</span>
                        <strong>{{ number_format((int) ($offerCounts['downsell_declined'] ?? 0)) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="analytics-grid">
            <div class="analytics-card">
                <h3>Daily Funnel Trend</h3>
                <div class="analytics-chart-wrap">
                    <canvas id="dailyTrendChart"></canvas>
                </div>
            </div>

            <div class="analytics-card">
                <h3>Conversion Path</h3>
                <div class="analytics-chart-wrap">
                    <canvas id="conversionPathChart"></canvas>
                </div>
            </div>
        </div>

        <div class="analytics-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                <h3 style="margin:0;">Step Performance</h3>
                <button type="button" id="toggleStepPerformanceBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="stepPerformanceContent">Show</button>
            </div>
            <div id="stepPerformanceContent" style="display:none;">
                <div class="analytics-table-wrap">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Step</th>
                                <th>Type</th>
                                <th>Visits</th>
                                <th>Drop-off</th>
                                <th>Drop-off Rate</th>
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
                                    <td>{{ number_format((float) ($rowDropOff['drop_off_rate'] ?? 0), 2) }}%</td>
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
                                    <td colspan="6">No step analytics yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="analytics-card">
            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px;">
                <h3 style="margin:0;">Recent Funnel Events</h3>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <a href="{{ route('funnels.events', $funnel) }}" class="analytics-btn">Open Raw Events JSON</a>
                    <button type="button" id="toggleRecentEventsBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="recentEventsContent">Show</button>
                </div>
            </div>

            <div id="recentEventsContent" style="display:none;">
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
                                    Step Type: {{ ucwords(str_replace('_', ' ', data_get($event->meta, 'step_type', $event->step->type ?? 'n/a'))) }}<br>
                                    Step Slug: {{ data_get($event->meta, 'step_slug', $event->step->slug ?? 'N/A') }}<br>
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
    </div>
@endsection

@section('scripts')
    <script>
        const stepLabels = @json($stepLabels);
        const stepValues = @json($stepValues);
        const stepDropOff = @json($stepDropOffValues);
        const offerRateLabels = ['Upsell Acceptance', 'Downsell Acceptance', 'Abandoned Checkout'];
        const offerRateValues = @json($offerRateValues);
        const dailyLabels = @json($dailyLabels);
        const dailyVisitValues = @json($dailyVisitValues);
        const dailyOptInValues = @json($dailyOptInValues);
        const dailyCheckoutValues = @json($dailyCheckoutValues);
        const dailyPaidValues = @json($dailyPaidValues);
        const conversionLabels = @json($conversionLabels);
        const conversionValues = @json($conversionValues);
        const toggleStepPerformanceBtn = document.getElementById('toggleStepPerformanceBtn');
        const stepPerformanceContent = document.getElementById('stepPerformanceContent');
        const toggleRecentEventsBtn = document.getElementById('toggleRecentEventsBtn');
        const recentEventsContent = document.getElementById('recentEventsContent');

        function bindCollapsibleSection(button, content) {
            if (!button || !content) {
                return;
            }

            button.addEventListener('click', function() {
                const isHidden = content.style.display === 'none';
                content.style.display = isHidden ? 'block' : 'none';
                button.textContent = isHidden ? 'Hide' : 'Show';
                button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            });
        }

        bindCollapsibleSection(toggleStepPerformanceBtn, stepPerformanceContent);
        bindCollapsibleSection(toggleRecentEventsBtn, recentEventsContent);

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
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = Number(context.raw || 0);
                                    return context.label + ': ' + value.toFixed(2) + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        const dailyTrendCanvas = document.getElementById('dailyTrendChart');
        if (dailyTrendCanvas) {
            new Chart(dailyTrendCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [
                        { label: 'Visits', data: dailyVisitValues, borderColor: '#240E35', backgroundColor: 'rgba(36,14,53,0.10)', tension: 0.35, fill: false },
                        { label: 'Opt-ins', data: dailyOptInValues, borderColor: '#0F766E', backgroundColor: 'rgba(15,118,110,0.10)', tension: 0.35, fill: false },
                        { label: 'Checkout Starts', data: dailyCheckoutValues, borderColor: '#F97316', backgroundColor: 'rgba(249,115,22,0.10)', tension: 0.35, fill: false },
                        { label: 'Paid', data: dailyPaidValues, borderColor: '#7C3AED', backgroundColor: 'rgba(124,58,237,0.10)', tension: 0.35, fill: false },
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

        const conversionPathCanvas = document.getElementById('conversionPathChart');
        if (conversionPathCanvas) {
            new Chart(conversionPathCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: conversionLabels,
                    datasets: [{
                        label: 'Count',
                        data: conversionValues,
                        backgroundColor: ['#240E35', '#6B4A7A', '#0F766E', '#F97316'],
                        borderRadius: 10,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    </script>
@endsection
