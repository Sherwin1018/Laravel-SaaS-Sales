@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-super-dashboard.css') }}">
@endsection

@section('content')
    <div class="top-header top-header--with-tools">
        <h1>Welcome, Super Admin</h1>
        <button type="button" class="landing-video-trigger" id="landingVideoTrigger" aria-label="View/Upload Promotional Video">
            <i class="fas fa-film"></i>
            <span class="landing-video-trigger-tooltip" role="tooltip">View/Upload Promotional<br>Video</span>
        </button>
    </div>

    <div class="landing-video-modal {{ $errors->has('hero_video') || $errors->has('video_width') || $errors->has('video_height') ? 'open' : '' }}" id="landingVideoModal" aria-hidden="{{ $errors->has('hero_video') || $errors->has('video_width') || $errors->has('video_height') ? 'false' : 'true' }}">
        <div class="landing-video-dialog" role="dialog" aria-modal="true" aria-labelledby="landingVideoModalTitle">
            <div class="landing-video-dialog-top">
                <h3 id="landingVideoModalTitle">Landing Hero Video</h3>
                <button type="button" class="landing-video-close" id="landingVideoClose" aria-label="Close landing hero video settings">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="landing-video-modal-copy">
                Upload an MP4 (max 25 MB) to show in the landing hero demo card. If empty, the fallback card UI is shown.
            </p>
            <div class="landing-video-grid" style="margin-top: 14px;">
                <div class="landing-video-preview">
                    @if($landingHeroVideoUrl)
                        <video controls preload="metadata">
                            <source src="{{ $landingHeroVideoUrl }}" type="video/mp4">
                        </video>
                    @else
                        <div class="landing-video-fallback">
                            <button type="button" aria-hidden="true">&#9658;</button>
                            <h4 style="margin: 0 0 6px;">Watch Product Demo</h4>
                            <p style="margin: 0; color: #15D6A2; font-weight: 700;">3 minutes</p>
                        </div>
                    @endif
                </div>

                <div class="landing-video-form">
                    <form id="landingVideoUploadForm" method="POST" action="{{ route('admin.landing-video.update') }}" enctype="multipart/form-data">
                        @csrf
                        <label for="hero_video">MP4 Video File</label>
                        <input type="file" id="hero_video" name="hero_video" accept="video/mp4" required>

                        <div class="landing-video-dimensions">
                            <div>
                                <label for="video_width">Video Width</label>
                                <input type="number" id="video_width" name="video_width" min="320" max="3840" value="{{ old('video_width', $landingHeroVideoWidth) }}" required>
                            </div>
                            <div>
                                <label for="video_height">Video Height</label>
                                <input type="number" id="video_height" name="video_height" min="180" max="2160" value="{{ old('video_height', $landingHeroVideoHeight) }}" required>
                            </div>
                        </div>

                        @if($errors->has('hero_video') || $errors->has('video_width') || $errors->has('video_height'))
                            <p style="color: #B91C1C; margin: 0 0 8px;">
                                {{ $errors->first('hero_video') ?: ($errors->first('video_width') ?: $errors->first('video_height')) }}
                            </p>
                        @endif
                    </form>

                    <div class="landing-video-actions">
                        <button type="submit" form="landingVideoUploadForm" class="upload">Upload / Replace</button>
                        @if($landingHeroVideoUrl)
                            <form method="POST" action="{{ route('admin.landing-video.delete') }}" id="landingVideoDeleteForm">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete">Delete Video</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="landing-confirm-backdrop" id="landingDeleteConfirm" aria-hidden="true">
        <div class="landing-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="landingDeleteConfirmTitle">
            <h3 class="landing-confirm-title" id="landingDeleteConfirmTitle">Delete video</h3>
            <p class="landing-confirm-copy">Delete the current landing hero video?</p>
            <div class="landing-confirm-actions">
                <button type="button" class="cancel" id="landingDeleteCancel">Cancel</button>
                <button type="button" class="confirm" id="landingDeleteProceed">OK</button>
            </div>
        </div>
    </div>

    @php
        $activeTenantShare = $tenantCount > 0 ? ($activeTenantCount / $tenantCount) * 100 : 0;
        $trialTenantShare = $tenantCount > 0 ? ($trialTenantCount / $tenantCount) * 100 : 0;
        $inactiveTenantShare = $tenantCount > 0 ? ($inactiveTenantCount / $tenantCount) * 100 : 0;
        $payingTenantShare = $tenantCount > 0 ? ($payingTenantCount / $tenantCount) * 100 : 0;
        $usersPerTenant = $tenantCount > 0 ? $userCount / $tenantCount : 0;
        $leadsPerTenant = $tenantCount > 0 ? $leadCount / $tenantCount : 0;
        $mrrDelta = $mrr - $previousMonthMrr;
        $mrrGrowthTone = $mrrGrowthRate >= 0 ? 'positive' : 'danger';
        $churnTone = $churnRate >= 10 ? 'danger' : ($churnRate >= 5 ? 'warning' : 'positive');
    @endphp

    <div class="admin-kpi-board">
        <section class="admin-kpi-group" aria-labelledby="tenantOverviewHeading">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Tenant Overview</span>
            </div>
            <div class="admin-kpi-grid">
                <a href="{{ route('admin.tenants.index') }}" class="admin-kpi-card admin-kpi-card--link admin-kpi-card--featured">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Total Tenants</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($tenantCount) }}</div>
                    <div class="admin-kpi-card__meta">Platform accounts across all statuses</div>
                </a>
                <a href="{{ route('admin.tenants.index') }}" class="admin-kpi-card admin-kpi-card--link admin-kpi-card--success">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Active Tenants</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-check-circle" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($activeTenantCount) }}</div>
                    <div class="admin-kpi-card__meta">{{ number_format($activeTenantShare, 1) }}% of all tenants are live</div>
                </a>
                <a href="{{ route('admin.tenants.index') }}" class="admin-kpi-card admin-kpi-card--link admin-kpi-card--warning">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Trial Tenants</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($trialTenantCount) }}</div>
                    <div class="admin-kpi-card__meta">{{ number_format($trialTenantShare, 1) }}% are still evaluating the platform</div>
                </a>
                <a href="{{ route('admin.tenants.index') }}" class="admin-kpi-card admin-kpi-card--link admin-kpi-card--danger">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Inactive Tenants</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($inactiveTenantCount) }}</div>
                    <div class="admin-kpi-card__meta">{{ number_format($inactiveTenantShare, 1) }}% may need recovery or cleanup</div>
                </a>
            </div>
        </section>

        <section class="admin-kpi-group" aria-labelledby="revenueSnapshotHeading">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Revenue Snapshot</span>
            </div>
            <div class="admin-kpi-grid">
                <article class="admin-kpi-card admin-kpi-card--featured admin-kpi-card--primary">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Current MRR</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-coins" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format($mrr, 2) }}</div>
                    <div class="admin-kpi-card__meta">{{ $mrrDelta >= 0 ? '+' : '-' }}PHP {{ number_format(abs($mrrDelta), 2) }} vs last month</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Last Month MRR</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-history" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format($previousMonthMrr, 2) }}</div>
                    <div class="admin-kpi-card__meta">Previous benchmark for this month's change</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--{{ $mrrGrowthTone }}">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">MRR Growth</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($mrrGrowthRate, 2) }}<span class="admin-kpi-card__suffix">%</span></div>
                    <div class="admin-kpi-card__meta">{{ $mrrGrowthRate >= 0 ? 'Revenue is trending up month over month' : 'Revenue is down from the previous month' }}</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">ARPU</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-wallet" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format($arpu, 2) }}</div>
                    <div class="admin-kpi-card__meta">Average recurring revenue per paying tenant</div>
                </article>
            </div>
        </section>

        <section class="admin-kpi-group" aria-labelledby="platformHealthHeading">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Platform Health</span>
            </div>
            <div class="admin-kpi-grid">
                <article class="admin-kpi-card admin-kpi-card--featured">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Paying Tenants</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-credit-card" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($payingTenantCount) }}</div>
                    <div class="admin-kpi-card__meta">{{ number_format($payingTenantShare, 1) }}% of all tenants are currently monetized</div>
                </article>
                <a href="{{ route('admin.users.index') }}" class="admin-kpi-card admin-kpi-card--link">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Total Users</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-users" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($userCount) }}</div>
                    <div class="admin-kpi-card__meta">{{ number_format($usersPerTenant, 1) }} users per tenant on average</div>
                </a>
                <a href="{{ route('admin.leads.index') }}" class="admin-kpi-card admin-kpi-card--link">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Total Leads</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-bullseye" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($leadCount) }}</div>
                    <div class="admin-kpi-card__meta">{{ number_format($leadsPerTenant, 1) }} leads captured per tenant</div>
                </a>
                <article class="admin-kpi-card admin-kpi-card--{{ $churnTone }}">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Churn Rate</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-user-minus" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($churnRate, 2) }}<span class="admin-kpi-card__suffix">%</span></div>
                    <div class="admin-kpi-card__meta">{{ $churnRate >= 10 ? 'High churn signal. Review retention and failed renewals.' : ($churnRate >= 5 ? 'Moderate churn. Keep an eye on plan downgrades.' : 'Healthy churn range based on current thresholds.') }}</div>
                </article>
            </div>
        </section>
    </div>

    <div class="charts">
        <div class="chart">
            <div class="chart-heading">
                <h3>Platform Lead Volume Trend</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Lead volume trend help">?</span>
                    <span class="chart-help-tip">Shows how total leads are trending over time across all tenants.</span>
                </span>
            </div>
            <canvas id="leadTrendChart"></canvas>
        </div>
        <div class="chart">
            <div class="chart-heading">
                <h3>Total Users by Role</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Users by role help">?</span>
                    <span class="chart-help-tip">Shows how users are distributed across platform roles.</span>
                </span>
            </div>
            <canvas id="usersByRoleChart"></canvas>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <div class="chart-heading">
                <h3>Tenant Growth (Last 6 Months)</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Tenant growth help">?</span>
                    <span class="chart-help-tip">Shows new tenant signups month by month for the last six months.</span>
                </span>
            </div>
            <canvas id="tenantGrowthChart"></canvas>
        </div>
        <div class="chart">
            <div class="chart-heading">
                <h3>Platform Usage Snapshot</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Usage snapshot help">?</span>
                    <span class="chart-help-tip">Shows current counts for key platform activity metrics.</span>
                </span>
            </div>
            <canvas id="usageMetricsChart"></canvas>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Payment Status Totals</h3>
        <div class="sa-table-scroll">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Transactions</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['paid', 'pending', 'failed'] as $status)
                        @php
                            $row = $paymentStatusTotals->get($status);
                        @endphp
                        <tr>
                            <td>{{ ucfirst($status) }}</td>
                            <td>{{ (int) ($row->count ?? 0) }}</td>
                            <td>{{ number_format((float) ($row->total ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Metric Calculation Basis</h3>
        <div class="sa-table-scroll">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Inputs</th>
                        <th>Formula</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>MRR</td>
                        <td>{{ (int) data_get($mrrBreakdown, 'current_month_paid_subscriptions', 0) }} paid subscription payments this month</td>
                        <td>Current month paid platform subscriptions total = PHP {{ number_format((float) data_get($mrrBreakdown, 'current_month_mrr', 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td>Churn</td>
                        <td>{{ (int) data_get($churnBreakdown, 'cancelled_this_month', 0) }} cancelled tenants, {{ (int) data_get($churnBreakdown, 'previous_active_tenants', 0) }} previous active tenants</td>
                        <td>{{ data_get($churnBreakdown, 'formula', '-') }}</td>
                    </tr>
                    <tr>
                        <td>ARPU</td>
                        <td>MRR PHP {{ number_format((float) data_get($arpuBreakdown, 'current_month_mrr', 0), 2) }}, {{ (int) data_get($arpuBreakdown, 'paying_tenants', 0) }} paying tenants</td>
                        <td>{{ data_get($arpuBreakdown, 'formula', '-') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3>Needs Action Now</h3>
        <div class="sa-table-scroll">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Plan</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actionableTenants as $tenant)
                        <tr>
                            <td>{{ $tenant->company_name }}</td>
                            <td>{{ ucfirst($tenant->status) }}</td>
                            <td>{{ $tenant->subscription_plan }}</td>
                            <td>{{ $tenant->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No trial/inactive tenants found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 16px;">
            {{ $actionableTenants->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('scripts')
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

        const landingVideoModal = document.getElementById('landingVideoModal');
        const landingVideoTrigger = document.getElementById('landingVideoTrigger');
        const landingVideoClose = document.getElementById('landingVideoClose');
        const landingVideoDeleteForm = document.getElementById('landingVideoDeleteForm');
        const landingDeleteConfirm = document.getElementById('landingDeleteConfirm');
        const landingDeleteCancel = document.getElementById('landingDeleteCancel');
        const landingDeleteProceed = document.getElementById('landingDeleteProceed');

        if (landingVideoModal && landingVideoTrigger && landingVideoClose) {
            const openLandingVideoModal = () => {
                landingVideoModal.classList.add('open');
                landingVideoModal.setAttribute('aria-hidden', 'false');
            };

            const closeLandingVideoModal = () => {
                landingVideoModal.classList.remove('open');
                landingVideoModal.setAttribute('aria-hidden', 'true');
            };

            landingVideoTrigger.addEventListener('click', openLandingVideoModal);
            landingVideoClose.addEventListener('click', closeLandingVideoModal);

            landingVideoModal.addEventListener('click', (event) => {
                if (event.target === landingVideoModal) {
                    closeLandingVideoModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && landingVideoModal.classList.contains('open')) {
                    closeLandingVideoModal();
                }
            });
        }

        if (landingVideoDeleteForm && landingDeleteConfirm && landingDeleteCancel && landingDeleteProceed) {
            const openDeleteConfirm = () => {
                landingDeleteConfirm.classList.add('open');
                landingDeleteConfirm.setAttribute('aria-hidden', 'false');
            };

            const closeDeleteConfirm = () => {
                landingDeleteConfirm.classList.remove('open');
                landingDeleteConfirm.setAttribute('aria-hidden', 'true');
            };

            landingVideoDeleteForm.addEventListener('submit', (event) => {
                event.preventDefault();
                openDeleteConfirm();
            });

            landingDeleteCancel.addEventListener('click', closeDeleteConfirm);
            landingDeleteProceed.addEventListener('click', () => {
                landingVideoDeleteForm.submit();
            });

            landingDeleteConfirm.addEventListener('click', (event) => {
                if (event.target === landingDeleteConfirm) {
                    closeDeleteConfirm();
                }
            });
        }

        const leadTrendCtx = document.getElementById('leadTrendChart').getContext('2d');
        new Chart(leadTrendCtx, {
            type: 'line',
            data: {
                labels: @json($leadTrendLabels),
                datasets: [{
                    label: 'Leads',
                    data: @json($leadTrendValues),
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

        const roleCtx = document.getElementById('usersByRoleChart').getContext('2d');
        new Chart(roleCtx, {
            type: 'bar',
            data: {
                labels: @json($usersByRole->pluck('name')->values()),
                datasets: [{
                    label: 'Users',
                    data: @json($usersByRole->pluck('users_count')->values()),
                    backgroundColor: '#6B4A7A'
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

        const tenantGrowthCtx = document.getElementById('tenantGrowthChart').getContext('2d');
        new Chart(tenantGrowthCtx, {
            type: 'line',
            data: {
                labels: @json($tenantGrowth['labels'] ?? []),
                datasets: [{
                    label: 'New Tenants',
                    data: @json($tenantGrowth['values'] ?? []),
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

        const usageMetricsCtx = document.getElementById('usageMetricsChart').getContext('2d');
        new Chart(usageMetricsCtx, {
            type: 'bar',
            data: {
                labels: ['Users', 'Leads', 'Funnels', 'Payments'],
                datasets: [{
                    label: 'Count',
                    data: [
                        {{ (int) ($usageMetrics['users'] ?? 0) }},
                        {{ (int) ($usageMetrics['leads'] ?? 0) }},
                        {{ (int) ($usageMetrics['funnels'] ?? 0) }},
                        {{ (int) ($usageMetrics['payments'] ?? 0) }},
                    ],
                    backgroundColor: ['#240E35', '#6B4A7A', '#15D6A2', '#F59E0B']
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
    </script>
@endsection

