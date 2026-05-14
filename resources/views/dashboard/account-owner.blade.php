@extends('layouts.admin')

@section('title', 'Account Owner Dashboard')

@section('styles')
        <link rel="stylesheet" href="{{ asset('css/extracted/dashboard-account-owner-style1.css') }}">
@endsection

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
    $servicePaidRevenue = (float) ($serviceSalesTotal ?? 0);
    $physicalPaidRevenue = (float) ($physicalProductSalesTotal ?? 0);
    $teamActivityOpen = request()->has('activity_page');
    $usageUsers = (int) data_get($analyticsSummary, 'usage.users.used', 0);
    $usageFunnels = (int) data_get($analyticsSummary, 'usage.funnels.used', 0);
    $usageWorkflows = (int) data_get($analyticsSummary, 'usage.workflows.used', 0);
    $usageLeads = (int) data_get($analyticsSummary, 'usage.leads.used', 0);
    $usageMessages = (int) data_get($analyticsSummary, 'usage.messages.used', 0);
    $availableCoupons = (int) (($activeCouponCount ?? 0) + ($platformCouponCount ?? 0));
    $conversionTone = $conversionRate >= 20 ? 'positive' : ($conversionRate >= 10 ? 'warning' : 'danger');
    $subscriptionDeadlineAt = null;
    $subscriptionDeadlineTag = null;
    $subscriptionBannerTitle = 'Current Subscription';
    $subscriptionBannerSummary = 'Your workspace access is active.';
    $subscriptionCtaLabel = null;
    $subscriptionCtaRoute = null;
    $subscriptionTitleMain = $subscriptionBannerTitle;
    $subscriptionTitleState = null;

    if ($trialActive && $trialEndsAt) {
        $subscriptionDeadlineAt = $trialEndsAt;
        $subscriptionDeadlineTag = 'Trial Deadline';
        $subscriptionBannerTitle = '7-Day Free Trial Active';
        $subscriptionBannerSummary = $trialDaysRemaining . ' day' . ($trialDaysRemaining === 1 ? '' : 's') . ' remaining. Your trial ends on ' . optional($trialEndsAt)->format('F j, Y g:i A') . '.';
        $subscriptionCtaLabel = 'Upgrade with PayMongo';
        $subscriptionCtaRoute = route('trial.billing.show');
    } elseif ($tenant?->status === 'active' && $tenant?->billing_status === 'current' && $tenant?->subscription_renews_at?->isFuture()) {
        $subscriptionDeadlineAt = $tenant->subscription_renews_at;
        $subscriptionDeadlineTag = 'Monthly Renewal';
        $renewalDaysRemaining = $tenant->subscriptionRenewalDaysRemaining();
        $subscriptionBannerTitle = 'Monthly Subscription Active';
        $subscriptionBannerSummary = $renewalDaysRemaining . ' day' . ($renewalDaysRemaining === 1 ? '' : 's') . ' remaining before your next billing deadline on ' . $tenant->subscription_renews_at->format('F j, Y g:i A') . '.';
        $subscriptionCtaLabel = 'Manage Billing';
        $subscriptionCtaRoute = route('payments.index');
    } elseif ($tenant?->status === 'active' && $tenant?->billing_status === 'current' && $tenant?->subscription_renews_at) {
        $subscriptionDeadlineTag = 'Renewal Pending';
        $subscriptionBannerTitle = 'Monthly Renewal Pending';
        $subscriptionBannerSummary = 'Your last billing deadline passed on ' . $tenant->subscription_renews_at->format('F j, Y g:i A') . '. Refresh or update billing so the next renewal schedule can be applied.';
        $subscriptionCtaLabel = 'Manage Billing';
        $subscriptionCtaRoute = route('payments.index');
    } elseif ($tenant?->isOverdue() && $tenant?->billing_grace_ends_at) {
        $subscriptionDeadlineAt = $tenant->billing_grace_ends_at;
        $subscriptionDeadlineTag = 'Grace Deadline';
        $subscriptionBannerTitle = 'Payment Grace Period';
        $subscriptionBannerSummary = 'Complete payment before ' . optional($tenant->billing_grace_ends_at)->format('F j, Y g:i A') . ' to avoid service interruption for your workspace and team.';
        $subscriptionCtaLabel = 'Complete Payment';
        $subscriptionCtaRoute = route('payments.index');
    } elseif ($tenant?->subscription_plan) {
        $subscriptionBannerSummary = 'Your workspace is active under the ' . $tenant->subscription_plan . ' plan.';
        if ($tenant?->subscription_activated_at) {
            $subscriptionBannerSummary .= ' Activated on ' . $tenant->subscription_activated_at->format('F j, Y g:i A') . '.';
        }
    }

    if (str_ends_with($subscriptionBannerTitle, ' Active')) {
        $subscriptionTitleMain = preg_replace('/\s+Active$/', '', $subscriptionBannerTitle) ?: $subscriptionBannerTitle;
        $subscriptionTitleState = 'Active';
    } else {
        $subscriptionTitleMain = $subscriptionBannerTitle;
    }

    $payoutStatus = $payoutAccount?->reviewStatus() ?? 'setup_required';
    $payoutPillText = $payoutAccount ? $payoutAccount->reviewStatusLabel() : 'Setup Required';
    $payoutPillTone = match ($payoutStatus) {
        \App\Models\TenantPayoutAccount::STATUS_APPROVED => ['bg' => '#DCFCE7', 'text' => '#166534'],
        \App\Models\TenantPayoutAccount::STATUS_REJECTED => ['bg' => '#FEE2E2', 'text' => '#B91C1C'],
        \App\Models\TenantPayoutAccount::STATUS_PENDING_PLATFORM_REVIEW => ['bg' => '#FFEDD5', 'text' => '#C2410C'],
        default => ['bg' => '#E2E8F0', 'text' => '#334155'],
    };
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

    @if($tenant)
        <div class="card subscription-status-card">
            <div class="subscription-status-shell">
                <div class="subscription-status-main">
                    <div class="subscription-status-header">
                        <h3>
                            <span>{{ $subscriptionTitleMain }}</span>
                            @if($subscriptionTitleState)
                                <span class="subscription-status-title__state">{{ $subscriptionTitleState }}</span>
                            @endif
                        </h3>
                        @if($subscriptionDeadlineTag)
                            <span class="subscription-status-tag">
                                {{ $subscriptionDeadlineTag }}
                            </span>
                            <span class="chart-help-wrap subscription-status-tag__help" tabindex="0">
                                <span class="chart-help-dot" aria-label="Show billing deadline details">?</span>
                                <span class="chart-help-tip">{{ $subscriptionBannerSummary }}</span>
                            </span>
                        @endif
                    </div>

                    @if($subscriptionDeadlineAt)
                        <div
                            data-subscription-countdown
                            data-subscription-ends-at="{{ optional($subscriptionDeadlineAt)->toIso8601String() }}"
                            class="subscription-status-countdown"
                        >
                            <div class="subscription-status-countdown__item">
                                <strong data-subscription-days>0</strong>
                                <span>Days</span>
                            </div>
                            <div class="subscription-status-countdown__item">
                                <strong data-subscription-hours>00</strong>
                                <span>Hours</span>
                            </div>
                            <div class="subscription-status-countdown__item">
                                <strong data-subscription-minutes>00</strong>
                                <span>Minutes</span>
                            </div>
                            <div class="subscription-status-countdown__item">
                                <strong data-subscription-seconds>00</strong>
                                <span>Seconds</span>
                            </div>
                        </div>
                    @endif

                </div>

                <div class="subscription-status-side">
                    <div
                        class="subscription-status-payout"
                        style="background:{{ $payoutPillTone['bg'] }};color:#111111;">
                        <span>Payout Account Status</span>
                        <span class="subscription-status-payout__state">{{ $payoutPillText }}</span>
                    </div>
                    <div class="subscription-status-side__actions">
                        @if($subscriptionCtaLabel && $subscriptionCtaRoute)
                            <a href="{{ $subscriptionCtaRoute }}" class="subscription-status-side__action">
                                {{ $subscriptionCtaLabel }}
                            </a>
                        @endif
                        <a href="{{ route('profile.show') }}" class="subscription-status-side__action">
                            Manage Payout Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="admin-kpi-board">
        <section class="admin-kpi-group" aria-label="Pipeline Overview">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Pipeline Overview</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--4">
                <article class="admin-kpi-card admin-kpi-card--primary">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Leads This Month</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($leadsThisMonth) }}</div>
                    <div class="admin-kpi-card__meta">New leads captured in the current month</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--success">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Closed Won</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-check-circle" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($wonCount) }}</div>
                    <div class="admin-kpi-card__meta">Deals successfully converted to revenue</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--danger">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Closed Lost</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-times-circle" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($lostCount) }}</div>
                    <div class="admin-kpi-card__meta">Opportunities that did not convert</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--{{ $conversionTone }}">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Conversion Rate</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-percent" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($conversionRate, 2) }}<span class="admin-kpi-card__suffix">%</span></div>
                    <div class="admin-kpi-card__meta">Share of tracked opportunities that closed won</div>
                </article>
            </div>
        </section>

        <section class="admin-kpi-group" aria-label="Revenue Snapshot">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Revenue Snapshot</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--3">
                <article class="admin-kpi-card admin-kpi-card--primary">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Funnel Paid Revenue</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-coins" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format($revenueTotal, 2) }}</div>
                    <div class="admin-kpi-card__meta">Combined paid revenue from all funnel transactions</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Service Sales</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-briefcase" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format($servicePaidRevenue, 2) }}</div>
                    <div class="admin-kpi-card__meta">Revenue earned from service-based offers</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Physical Product Sales</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-box" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format($physicalPaidRevenue, 2) }}</div>
                    <div class="admin-kpi-card__meta">Revenue earned from physical product purchases</div>
                </article>
            </div>
        </section>

        <section class="admin-kpi-group" aria-label="Workspace Health">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Workspace Health</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--4">
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Total Users</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-users" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($usageUsers) }}</div>
                    <div class="admin-kpi-card__meta">Team members currently using this workspace</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Total Funnels</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-filter" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($usageFunnels) }}</div>
                    <div class="admin-kpi-card__meta">Funnels created across the account</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Automation Workflows</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-bolt" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($usageWorkflows) }}</div>
                    <div class="admin-kpi-card__meta">Published funnels currently using shared automation capacity</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--warning">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Outbound Messages</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-paper-plane" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format($usageMessages) }}</div>
                    <div class="admin-kpi-card__meta">Billable email and SMS sends recorded for the current month</div>
                </article>
            </div>
        </section>
    </div>

    <div class="charts">
        <div class="chart">
            <div class="chart-heading">
                <h3>Pipeline Distribution</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Pipeline distribution help">?</span>
                    <span class="chart-help-tip">Shows how many leads are in each pipeline status right now.</span>
                </span>
            </div>
            <canvas id="pipelineDistributionChart"></canvas>
        </div>
        <div class="chart">
            <div class="chart-heading">
                <h3>Pipeline Aging</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Pipeline aging help">?</span>
                    <span class="chart-help-tip">Shows how long open leads have been waiting in the pipeline.</span>
                </span>
            </div>
            <canvas id="pipelineAgingChart"></canvas>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <div class="chart-heading">
                <h3>Revenue Trend (Last 6 Months)</h3>
                <span class="chart-help-wrap">
                    <span class="chart-help-dot" tabindex="0" aria-label="Revenue trend help">?</span>
                    <span class="chart-help-tip">Shows month-by-month paid revenue for the last six months.</span>
                </span>
            </div>
            <canvas id="ownerRevenueTrendChart"></canvas>
        </div>
        <div class="chart">
            <h3>Usage Snapshot</h3>
            <div class="app-table-scroll app-table-scroll--wide">
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
                    @foreach(['users' => 'Users', 'leads' => 'Leads', 'funnels' => 'Funnels', 'workflows' => 'Workflows', 'messages' => 'Messages'] as $usageKey => $usageLabel)
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
        <div class="chart">
            <h3>Analytics Basis</h3>
            <div style="color:#475569;font-size:13px;line-height:1.7;">
                Revenue trend window: {{ (int) data_get($analyticsSummary, 'revenue_trend_breakdown.window_months', 0) }} months.
                Paid payments included: {{ (int) data_get($analyticsSummary, 'revenue_trend_breakdown.paid_payments_considered', 0) }}.
                Formula: {{ data_get($analyticsSummary, 'revenue_trend_breakdown.formula', '-') }}.
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Funnel Revenue and Payment Status</h3>
        <div class="app-table-scroll app-table-scroll--wide">
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
    </div>

    <div class="card" id="team-activity">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <h3 style="margin:0;">Team Activity Snapshot</h3>
            <button type="button" id="toggleTeamActivityBtn" class="ui-show-hide-toggle"
                style="padding:10px 16px;background:var(--theme-primary,#240E35);color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:700;min-width:88px;"
                aria-expanded="{{ $teamActivityOpen ? 'true' : 'false' }}">
                {{ $teamActivityOpen ? 'Hide' : 'Show' }}
            </button>
        </div>
        <div id="teamActivityContent" style="display:{{ $teamActivityOpen ? 'block' : 'none' }};">
            <div class="app-table-scroll app-table-scroll--wide">
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
                            <td>{{ $activity->lead->name ?? $emptyDash }}</td>
                            <td>{{ $activity->activity_type }}</td>
                            <td>{{ $activity->notes ?: $emptyDash }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No recent team activity found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <div style="margin-top: 16px;">
                {{ $teamActivity->fragment('team-activity')->links('pagination::bootstrap-4') }}
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

        const subscriptionCountdown = document.querySelector('[data-subscription-countdown]');
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

        if (subscriptionCountdown) {
            const endsAt = subscriptionCountdown.getAttribute('data-subscription-ends-at');
            const dayNode = subscriptionCountdown.querySelector('[data-subscription-days]');
            const hourNode = subscriptionCountdown.querySelector('[data-subscription-hours]');
            const minuteNode = subscriptionCountdown.querySelector('[data-subscription-minutes]');
            const secondNode = subscriptionCountdown.querySelector('[data-subscription-seconds]');
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
