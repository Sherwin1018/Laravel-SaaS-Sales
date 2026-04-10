@extends('layouts.admin')

@section('title', 'Account Owner Dashboard')

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

    @if($trialActive)
        <div class="card" style="margin-bottom: 20px; border-left: 4px solid var(--theme-accent, #6B4A7A);">
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
                <div>
                    <div
                        style="display:flex;flex-wrap:wrap;align-items:center;gap:14px;margin:0 0 12px;"
                    >
                        <h3 style="margin:0;">7-Day Free Trial Active</h3>
                        <div
                            data-trial-countdown
                            data-trial-ends-at="{{ optional($trialEndsAt)?->toIso8601String() }}"
                            style="display:flex;flex-wrap:wrap;gap:8px;"
                        >
                            <div style="min-width:74px;padding:10px 10px;border-radius:14px;background:#f4f3f8;text-align:center;">
                                <strong data-trial-days style="display:block;font-size:20px;line-height:1;color:var(--theme-primary, #240E35);">0</strong>
                                <span style="display:block;margin-top:6px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);">Days</span>
                            </div>
                            <div style="min-width:74px;padding:10px 10px;border-radius:14px;background:#f4f3f8;text-align:center;">
                                <strong data-trial-hours style="display:block;font-size:20px;line-height:1;color:var(--theme-primary, #240E35);">00</strong>
                                <span style="display:block;margin-top:6px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);">Hours</span>
                            </div>
                            <div style="min-width:74px;padding:10px 10px;border-radius:14px;background:#f4f3f8;text-align:center;">
                                <strong data-trial-minutes style="display:block;font-size:20px;line-height:1;color:var(--theme-primary, #240E35);">00</strong>
                                <span style="display:block;margin-top:6px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);">Minutes</span>
                            </div>
                            <div style="min-width:74px;padding:10px 10px;border-radius:14px;background:#f4f3f8;text-align:center;">
                                <strong data-trial-seconds style="display:block;font-size:20px;line-height:1;color:var(--theme-primary, #240E35);">00</strong>
                                <span style="display:block;margin-top:6px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--theme-muted, #6B7280);">Seconds</span>
                            </div>
                        </div>
                    </div>
                    <p style="margin:0;color:var(--theme-muted, #6B7280);line-height:1.7;">
                        {{ $trialDaysRemaining }} day{{ $trialDaysRemaining === 1 ? '' : 's' }} remaining.
                        Your trial ends on {{ optional($trialEndsAt)->format('F j, Y g:i A') }}.
                    </p>
                </div>
                <a href="{{ route('trial.billing.show') }}" style="display:inline-flex;align-items:center;justify-content:center;padding:12px 18px;border-radius:12px;background:var(--theme-primary, #240E35);color:#fff;font-weight:700;text-decoration:none;">
                    Upgrade with PayMongo
                </a>
            </div>
        </div>
    @endif

    <div class="kpi-cards">
        <div class="card">
            <h3>Leads This Month</h3>
            <p>{{ $leadsThisMonth }}</p>
        </div>
        <div class="card">
            <h3>Closed Won</h3>
            <p>{{ $wonCount }}</p>
        </div>
        <div class="card">
            <h3>Closed Lost</h3>
            <p>{{ $lostCount }}</p>
        </div>
        <div class="card">
            <h3>Conversion Rate</h3>
            <p>{{ $conversionRate }}%</p>
        </div>
        <div class="card">
            <h3>Funnel Paid Revenue</h3>
            <p>{{ number_format($revenueTotal, 2) }}</p>
        </div>
        <div class="card">
            <h3>Total Users</h3>
            <p>{{ data_get($analyticsSummary, 'usage.users.used', 0) }}</p>
        </div>
        <div class="card">
            <h3>Total Funnels</h3>
            <p>{{ data_get($analyticsSummary, 'usage.funnels.used', 0) }}</p>
        </div>
        <div class="card">
            <h3>Total Leads</h3>
            <p>{{ data_get($analyticsSummary, 'usage.leads.used', 0) }}</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <h3>Pipeline Distribution</h3>
            <canvas id="pipelineDistributionChart"></canvas>
        </div>
        <div class="chart">
            <h3>Pipeline Aging</h3>
            <canvas id="pipelineAgingChart"></canvas>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <h3>Revenue Trend (Last 6 Months)</h3>
            <canvas id="ownerRevenueTrendChart"></canvas>
        </div>
        <div class="chart">
            <h3>Usage Snapshot</h3>
            <table>
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Used</th>
                        <th>Limit</th>
                        <th>Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['users' => 'Users', 'leads' => 'Leads', 'funnels' => 'Funnels'] as $usageKey => $usageLabel)
                        @php
                            $usageRow = data_get($analyticsSummary, "usage.{$usageKey}", []);
                            $limit = $usageRow['limit'] ?? 'Unlimited';
                        @endphp
                        <tr>
                            <td>{{ $usageLabel }}</td>
                            <td>{{ $usageRow['used'] ?? 0 }}</td>
                            <td>{{ $limit === null ? 'Unlimited' : $limit }}</td>
                            <td>{{ $usageRow['remaining'] ?? 'Unlimited' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Funnel Revenue and Payment Status</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['paid', 'pending', 'failed'] as $status)
                    <tr>
                        <td>{{ ucfirst($status) }}</td>
                        <td>{{ number_format((float) ($paymentStatusTotals[$status] ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <h3 style="margin:0;">Team Activity Snapshot</h3>
            <button type="button" id="toggleTeamActivityBtn"
                style="padding:10px 16px;background:var(--theme-primary,#240E35);color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:700;min-width:88px;"
                aria-expanded="false">
                Show
            </button>
        </div>
        <div id="teamActivityContent" style="display:none;">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Lead</th>
                        <th>Activity</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teamActivity as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $activity->lead->name ?? 'N/A' }}</td>
                            <td>{{ $activity->activity_type }}</td>
                            <td>{{ $activity->notes ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No recent team activity found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top: 16px;">
                {{ $teamActivity->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @php
        $pipelineStatusLabels = array_map(function ($status) {
            return ucwords(str_replace('_', ' ', $status));
        }, array_keys($pipelineDistribution));
    @endphp
    <script>
        const dashboardChartText = '#374151';
        const dashboardChartGrid = 'rgba(107, 114, 128, 0.16)';
        const dashboardChartLegend = {
            labels: {
                color: dashboardChartText,
                boxWidth: 14,
                boxHeight: 14,
                padding: 16,
                font: {
                    size: 12,
                    weight: '600'
                }
            }
        };

        const dashboardChartScales = {
            x: {
                ticks: {
                    color: dashboardChartText,
                    maxRotation: 0,
                    autoSkip: true
                },
                grid: {
                    color: dashboardChartGrid
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: dashboardChartText,
                    precision: 0
                },
                grid: {
                    color: dashboardChartGrid
                }
            }
        };

        const pipelineDistributionCtx = document.getElementById('pipelineDistributionChart').getContext('2d');
        new Chart(pipelineDistributionCtx, {
            type: 'bar',
            data: {
                labels: @json($pipelineStatusLabels),
                datasets: [{
                    label: 'Leads',
                    data: @json(array_values($pipelineDistribution)),
                    backgroundColor: '#240E35'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: dashboardChartLegend
                },
                scales: {
                    ...dashboardChartScales,
                    y: {
                        ...dashboardChartScales.y,
                        ticks: {
                            ...dashboardChartScales.y.ticks,
                            stepSize: 1
                        }
                    }
                }
            }
        });

        const pipelineAgingCtx = document.getElementById('pipelineAgingChart').getContext('2d');
        new Chart(pipelineAgingCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($pipelineAging)),
                datasets: [{
                    data: @json(array_values($pipelineAging)),
                    backgroundColor: ['#240E35', '#6B4A7A', '#9E7BB5']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: dashboardChartLegend
                }
            }
        });

        const ownerRevenueTrendCtx = document.getElementById('ownerRevenueTrendChart').getContext('2d');
        new Chart(ownerRevenueTrendCtx, {
            type: 'line',
            data: {
                labels: @json(data_get($analyticsSummary, 'revenue_trend_labels', [])),
                datasets: [{
                    label: 'Revenue',
                    data: @json(data_get($analyticsSummary, 'revenue_trend_values', [])),
                    borderColor: '#240E35',
                    backgroundColor: 'rgba(36, 14, 53, 0.18)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: dashboardChartLegend
                },
                scales: dashboardChartScales
            }
        });

        const trialCountdown = document.querySelector('[data-trial-countdown]');
        const toggleTeamActivityBtn = document.getElementById('toggleTeamActivityBtn');
        const teamActivityContent = document.getElementById('teamActivityContent');

        if (toggleTeamActivityBtn && teamActivityContent) {
            toggleTeamActivityBtn.addEventListener('click', function() {
                const isHidden = teamActivityContent.style.display === 'none';
                teamActivityContent.style.display = isHidden ? 'block' : 'none';
                toggleTeamActivityBtn.textContent = isHidden ? 'Hide' : 'Show';
                toggleTeamActivityBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            });
        }

        if (trialCountdown) {
            const endsAt = trialCountdown.getAttribute('data-trial-ends-at');
            const dayNode = trialCountdown.querySelector('[data-trial-days]');
            const hourNode = trialCountdown.querySelector('[data-trial-hours]');
            const minuteNode = trialCountdown.querySelector('[data-trial-minutes]');
            const secondNode = trialCountdown.querySelector('[data-trial-seconds]');
            const endTime = endsAt ? new Date(endsAt).getTime() : NaN;

            const pad = value => String(value).padStart(2, '0');

            let countdownTimer = null;

            const renderCountdown = () => {
                if (Number.isNaN(endTime)) {
                    return;
                }

                const remaining = Math.max(0, endTime - Date.now());
                const totalSeconds = Math.floor(remaining / 1000);
                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.floor((totalSeconds % 86400) / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                if (dayNode) dayNode.textContent = String(days);
                if (hourNode) hourNode.textContent = pad(hours);
                if (minuteNode) minuteNode.textContent = pad(minutes);
                if (secondNode) secondNode.textContent = pad(seconds);

                if (remaining <= 0 && countdownTimer) {
                    clearInterval(countdownTimer);
                }
            };

            renderCountdown();
            countdownTimer = window.setInterval(renderCountdown, 1000);
        }
    </script>
@endsection
