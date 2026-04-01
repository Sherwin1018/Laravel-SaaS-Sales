@extends('layouts.admin')

@section('title', 'Marketing Dashboard')

@php
    $companyName = optional(auth()->user()->tenant)->company_name ?? 'No Company';
    $companyInitials = collect(preg_split('/\s+/', trim($companyName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
    $companyInitials = $companyInitials !== '' ? $companyInitials : 'NC';
    $companyHue = abs(crc32($companyName ?: 'company')) % 360;
    $companyBg = "hsl({$companyHue}, 60%, 42%)";
@endphp

@section('content')
    <div class="top-header">
        <h1>Welcome, {{ auth()->user()->name }}</h1>
        <div class="company-chip">
            <div class="company-chip-avatar" style="background: {{ $companyBg }};">
                @if(optional(auth()->user()->tenant)->logo_path)
                    <img src="{{ asset('storage/' . auth()->user()->tenant->logo_path) }}" alt="Company Logo">
                @else
                    {{ $companyInitials }}
                @endif
            </div>
            <div class="company-chip-content">
                <span class="company-chip-label">Company</span>
                <span class="company-chip-name">{{ $companyName }}</span>
            </div>
        </div>
    </div>

    <div class="kpi-cards">
        <div class="card">
            <h3>Pipeline Value</h3>
            <p>₱{{ number_format($kpiMetrics['pipeline_value'], 0) }}</p>
        </div>
        <div class="card">
            <h3>Total Deals</h3>
            <p>{{ $kpiMetrics['total_deals'] }}</p>
        </div>
        <div class="card">
            <h3>Closed Won</h3>
            <p>{{ $kpiMetrics['closed_won'] }}</p>
        </div>
        <div class="card">
            <h3>Conversion Rate</h3>
            <p>{{ $kpiMetrics['conversion_rate'] }}%</p>
        </div>
    </div>

    <div class="widgets">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #6B4A7A;">
                <h3 style="margin: 0; color: #6B4A7A; font-size: 18px; font-weight: 600;">Link Performance Intelligence</h3>
            </div>
            <div style="margin-bottom: 15px; color: var(--theme-muted, #6B7280); font-size: 14px;">
                Which CTAs convert and generate revenue
            </div>
            <table style="width: 100%; font-size: 14px;">
                <thead>
                    <tr style="background: var(--theme-bg-light, #F9FAFB);">
                        <th style="padding: 12px; text-align: left;">Link Name</th>
                        <th style="padding: 12px; text-align: center;">Clicks</th>
                        <th style="padding: 12px; text-align: center;">Leads</th>
                        <th style="padding: 12px; text-align: center;">Won</th>
                        <th style="padding: 12px; text-align: center;">Conv%</th>
                        <th style="padding: 12px; text-align: center;">Revenue</th>
                        <th style="padding: 12px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($linkPerformance as $link)
                        <tr style="border-bottom: 1px solid var(--theme-border, #E6E1EF);">
                            <td style="padding: 12px; font-weight: 600;">
                                {{ $link['link_name'] }}
                            </td>
                            <td style="padding: 12px; text-align: center;">{{ $link['clicks'] }}</td>
                            <td style="padding: 12px; text-align: center;">{{ $link['leads'] }}</td>
                            <td style="padding: 12px; text-align: center;">{{ $link['won'] }}</td>
                            <td style="padding: 12px; text-align: center; font-weight: bold; color: {{ $link['click_to_lead_rate'] >= 30 ? '#059669' : ($link['click_to_lead_rate'] >= 15 ? '#D97706' : '#DC2626') }};">
                                {{ $link['click_to_lead_rate'] }}%
                            </td>
                            <td style="padding: 12px; text-align: center; font-weight: 600;">
                                ₱{{ number_format($link['estimated_revenue'], 0) }}
                            </td>
                            <td style="padding: 12px; text-align: center; font-size: 12px;">
                                @php
                                    $indicator = $link['performance_indicator'];
                                    $bgColors = [
                                        '🚀' => '#D1FAE5',
                                        '📈' => '#FEF3C7', 
                                        '➡️' => '#E0E7FF',
                                        '❌' => '#FEE2E2'
                                    ];
                                    $textColors = [
                                        '🚀' => '#059669',
                                        '📈' => '#D97706',
                                        '➡️' => '#4F46E5', 
                                        '❌' => '#DC2626'
                                    ];
                                    $bgColor = $bgColors[$indicator] ?? '#F3F4F6';
                                    $textColor = $textColors[$indicator] ?? '#6B7280';
                                @endphp
                                <span style="background: {{ $bgColor }}; padding: 4px 8px; border-radius: 4px; color: {{ $textColor }};">
                                    {{ $link['action_recommendation'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center; color: var(--theme-muted, #6B7280);">
                                No link performance data yet. Start sending tracked links in emails and automations.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="charts" style="margin-top: 40px;">
        <div class="chart">
            <h3>MQL Trend (Score >= {{ $mqlThreshold }})</h3>
            <canvas id="mqlTrendChart"></canvas>
        </div>
        <div class="chart">
            <h3>Leads by Source/Campaign</h3>
            <canvas id="sourceChart"></canvas>
        </div>
    </div>

    {{-- UTM Pipeline Analytics Section --}}
    <div class="utm-analytics-section" style="margin-top: 30px;">
        <div class="utm-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding: 20px; background: linear-gradient(135deg, #4A3258, #6B4A7A); border-radius: 12px; color: white;">
            <div>
                <h2 style="margin: 0; font-size: 24px; font-weight: 600;">UTM Pipeline Performance</h2>
                <p style="margin: 8px 0 0 0; opacity: 0.9; font-size: 14px;">Complete customer journey from visit to conversion</p>
            </div>
            <div class="utm-summary" style="text-align: right;">
                <div style="font-size: 12px; opacity: 0.8; margin-bottom: 4px;">Total Pipeline Value</div>
                <div style="font-size: 20px; font-weight: bold;">₱{{ number_format($kpiMetrics['pipeline_value'], 0) }}</div>
            </div>
        </div>
        
        <div class="utm-analytics-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            {{-- General Funnel Overview --}}
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #6B4A7A;">
                    <h3 style="margin: 0; color: #6B4A7A; font-size: 18px; font-weight: 600;">General Funnel Overview</h3>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; background: #4A3258; border-radius: 50%;"></div>
                        <div style="width: 12px; height: 12px; background: #6B4A7A; border-radius: 50%;"></div>
                        <div style="width: 12px; height: 12px; background: #8B6B9A; border-radius: 50%;"></div>
                        <div style="width: 12px; height: 12px; background: #AB8CBA; border-radius: 50%;"></div>
                        <div style="width: 12px; height: 12px; background: #CBADDA; border-radius: 50%;"></div>
                    </div>
                </div>
                
                <div class="funnel-container">
                    @php
                        $stages = [
                            ['name' => 'Visits', 'count' => $visits ?? 0],
                            ['name' => 'Leads', 'count' => $optIns ?? 0],
                            ['name' => 'Contacted', 'count' => $inPipeline ?? 0],
                            ['name' => 'Proposal', 'count' => $pipelineFlow['flow']['proposal_sent'] ?? 0],
                            ['name' => 'Won', 'count' => $closedWon ?? 0]
                        ];
                        $stageColors = ['funnel-stage-1', 'funnel-stage-2', 'funnel-stage-3', 'funnel-stage-4', 'funnel-stage-5'];
                        $stageIndex = 0;
                    @endphp
                    
                    @foreach($stages as $stage)
                        @if($stage['count'] > 0)
                            <div class="funnel-stage {{ $stageColors[$stageIndex] }}">
                                <div class="funnel-stage-content" style="
                                    position: relative;
                                    cursor: pointer;
                                    transition: all 0.3s ease;
                                " onmouseover="this.querySelector('.stage-label').style.opacity='1';" onmouseout="this.querySelector('.stage-label').style.opacity='0';">
                                    <!-- Label (hidden by default, shows on hover) -->
                                    <div class="stage-label" style="
                                        font-size: 12px;
                                        color: white;
                                        font-weight: 600;
                                        opacity: 0;
                                        transition: opacity 0.3s ease;
                                        position: absolute;
                                        top: 8px;
                                        left: 50%;
                                        transform: translateX(-50%);
                                        background: rgba(255,255,255,0.2);
                                        padding: 2px 8px;
                                        border-radius: 4px;
                                        backdrop-filter: blur(10px);
                                    ">
                                        {{ $stage['name'] }}
                                    </div>
                                    
                                    <!-- Number -->
                                    <div class="funnel-stage-number">{{ number_format($stage['count']) }}</div>
                                </div>
                            </div>
                            
                            @php($stageIndex++)
                        @endif
                    @endforeach
                </div>
                    
                    <!-- Conversion Summary -->
                    @if(!empty($pipelineFlow['conversion_rates']))
                        <div style="margin-top: 20px; padding: 15px; background: var(--theme-bg-light, #F9FAFB); border-radius: 8px;">
                            <h4 style="margin: 0 0 15px; font-size: 14px; color: #6B4A7A; font-weight: 600;">Overall Conversion Summary:</h4>
                            @foreach($pipelineFlow['conversion_rates'] as $rate => $value)
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                                        <span style="color: var(--theme-muted, #6B7280);">{{ str_replace('_', ' → ', $rate) }}</span>
                                        <strong style="color: #6B4A7A;">{{ $value }}%</strong>
                                    </div>
                                    <div style="width: 100%; height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden;">
                                        <div style="height: 100%; background: linear-gradient(90deg, #4A3258, #6B4A7A); border-radius: 4px; width: {{ min($value, 100) }}%; transition: width 0.8s ease;"></div>
                                    </div>
                                </div>
                            @endforeach
                            <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid var(--theme-border, #E6E1EF); font-size: 12px; color: var(--theme-muted, #6B7280); display: flex; justify-content: space-between; align-items: center;">
                                <span>Total leads in pipeline: {{ $pipelineFlow['total_leads'] ?? 0 }}</span>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 8px; height: 8px; background: #4A3258; border-radius: 50%;"></div>
                                    <span>High Performance</span>
                                    <div style="width: 8px; height: 8px; background: #8B6B9A; border-radius: 50%;"></div>
                                    <span>Medium Performance</span>
                                    <div style="width: 8px; height: 8px; background: #CBADDA; border-radius: 50%;"></div>
                                    <span>Low Performance</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Priority Leads --}}
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #6B4A7A;">
                    <h3 style="margin: 0; color: #6B4A7A; font-size: 18px; font-weight: 600;">Priority Leads</h3>
                    <div style="background: #6B4A7A; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        {{ $hotLeads->count() }} Active
                    </div>
                </div>
                <table style="width: 100%; font-size: 14px;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Source</th>
                            <th>Score</th>
                            <th>Clicks</th>
                            <th>Stage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hotLeads as $lead)
                            <tr>
                                <td>{{ $lead['name'] }}</td>
                                <td>{{ $lead['source'] }}</td>
                                <td>{{ $lead['score'] }}</td>
                                <td>{{ $lead['clicks'] }}</td>
                                <td>{{ $lead['status'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--theme-muted, #6B7280);">
                                    No active leads found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Source Performance Table (MOST IMPORTANT) --}}
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #6B4A7A;">
                <h3 style="margin: 0; color: #6B4A7A; font-size: 18px; font-weight: 600;">Source Performance Analysis</h3>
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
                Complete journey from visit to conversion by UTM source
            </div>
            <table style="width: 100%; font-size: 14px;">
                <thead>
                    <tr style="background: var(--theme-bg-light, #F9FAFB);">
                        <th style="padding: 12px; text-align: left;">Source</th>
                        <th style="padding: 12px; text-align: center;">Visits</th>
                        <th style="padding: 12px; text-align: center;">Leads</th>
                        <th style="padding: 12px; text-align: center;">Contacted</th>
                        <th style="padding: 12px; text-align: center;">Proposal</th>
                        <th style="padding: 12px; text-align: center;">Won</th>
                        <th style="padding: 12px; text-align: center;">Conv%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sourcePerformance as $source)
                        <tr style="border-bottom: 1px solid var(--theme-border, #E6E1EF);">
                            <td style="padding: 12px; font-weight: 600;">
                                {{ $source['source'] }}
                            </td>
                            <td style="padding: 12px; text-align: center;">{{ $source['visits'] }}</td>
                            <td style="padding: 12px; text-align: center;">{{ $source['leads'] }}</td>
                            <td style="padding: 12px; text-align: center;">{{ $source['contacted'] }}</td>
                            <td style="padding: 12px; text-align: center;">{{ $source['proposal'] }}</td>
                            <td style="padding: 12px; text-align: center;">{{ $source['won'] }}</td>
                            <td style="padding: 12px; text-align: center; font-weight: bold; color: {{ $source['conversion_rate'] >= 15 ? '#059669' : ($source['conversion_rate'] >= 8 ? '#D97706' : '#DC2626') }};">
                                {{ $source['conversion_rate'] }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center; color: var(--theme-muted, #6B7280);">
                                No UTM data available yet. Start driving traffic with UTM parameters to see performance analytics.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @endsection

@section('scripts')
    <script>
        const mqlTrendCtx = document.getElementById('mqlTrendChart').getContext('2d');
        new Chart(mqlTrendCtx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'MQL Leads',
                    data: @json($trendValues),
                    borderColor: '#240E35',
                    backgroundColor: 'rgba(36, 14, 53, 0.15)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        new Chart(sourceCtx, {
            type: 'bar',
            data: {
                labels: @json($sourceBreakdownChart->pluck('source_label')->values()),
                datasets: [{
                    label: 'Leads',
                    data: @json($sourceBreakdownChart->pluck('total')->values()),
                    backgroundColor: '#6B4A7A'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
@endsection
