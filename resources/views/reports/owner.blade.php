@extends('layouts.admin')

@section('title', 'Owner Reports')

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
        <h1>Owner Reports</h1>
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

    <div class="card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('reports.owner') }}" class="app-form-grid app-form-grid--4" style="gap:12px;">
            <div>
                <label for="date_from" style="display:block;margin-bottom:6px;font-weight:700;">Date From</label>
                <input type="date" id="date_from" name="date_from" value="{{ data_get($report, 'filters.date_from') }}"
                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
            </div>
            <div>
                <label for="date_to" style="display:block;margin-bottom:6px;font-weight:700;">Date To</label>
                <input type="date" id="date_to" name="date_to" value="{{ data_get($report, 'filters.date_to') }}"
                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
            </div>
            <div>
                <label for="funnel_id" style="display:block;margin-bottom:6px;font-weight:700;">Funnel</label>
                <select id="funnel_id" name="funnel_id" style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
                    <option value="">All funnels</option>
                    @foreach(data_get($report, 'funnel_options', []) as $funnel)
                        <option value="{{ $funnel->id }}" {{ (int) data_get($report, 'filters.funnel_id') === (int) $funnel->id ? 'selected' : '' }}>
                            {{ $funnel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit" style="padding:10px 16px;border:none;border-radius:8px;background:var(--theme-primary, #240E35);color:#fff;font-weight:700;cursor:pointer;">
                    Apply Filters
                </button>
                <a href="{{ route('reports.owner.export', request()->query()) }}" style="display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border-radius:8px;background:var(--theme-accent, #6B4A7A);color:#fff;text-decoration:none;font-weight:700;">
                    Download Excel
                </a>
            </div>
        </form>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <h3 style="margin:0;">Commission Settings</h3>
                <p style="margin:8px 0 0;color:var(--theme-muted, #6B7280);line-height:1.6;">
                    Manage the current 2-role commission model for your workspace. Attribution is currently fixed to assigned sales lead and campaign-linked marketing manager behavior.
                </p>
            </div>
            <div style="padding:10px 12px;border-radius:12px;background:#fbf9fd;border:1px solid var(--theme-border, #E6E1EF);color:#240E35;font-weight:800;">
                Active plan: {{ data_get($report, 'plan.name', 'Default Commission Plan') }}
            </div>
        </div>

        <form method="POST" action="{{ route('reports.owner.commission-plan.update') }}" class="app-form-grid app-form-grid--4" style="gap:12px;margin-top:16px;">
            @csrf
            @method('PUT')
            <div>
                <label for="gateway_fee_rate" style="display:block;margin-bottom:6px;font-weight:700;">Gateway Fee %</label>
                <input type="number" step="0.01" min="0" max="100" id="gateway_fee_rate" name="gateway_fee_rate"
                    value="{{ old('gateway_fee_rate', data_get($report, 'plan.gateway_fee_rate', 0)) }}"
                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
            </div>
            <div>
                <label for="platform_fee_rate" style="display:block;margin-bottom:6px;font-weight:700;">Platform Fee %</label>
                <input type="number" step="0.01" min="0" max="100" id="platform_fee_rate" name="platform_fee_rate"
                    value="{{ old('platform_fee_rate', data_get($report, 'plan.platform_fee_rate', 0)) }}"
                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
            </div>
            <div>
                <label for="sales_agent_rate" style="display:block;margin-bottom:6px;font-weight:700;">Sales Agent %</label>
                <input type="number" step="0.01" min="0" max="100" id="sales_agent_rate" name="sales_agent_rate"
                    value="{{ old('sales_agent_rate', data_get($report, 'plan.sales_agent_rate', 0)) }}"
                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
            </div>
            <div>
                <label for="marketing_manager_rate" style="display:block;margin-bottom:6px;font-weight:700;">Marketing Manager %</label>
                <input type="number" step="0.01" min="0" max="100" id="marketing_manager_rate" name="marketing_manager_rate"
                    value="{{ old('marketing_manager_rate', data_get($report, 'plan.marketing_manager_rate', 0)) }}"
                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
            </div>
            <div>
                <label for="hold_days" style="display:block;margin-bottom:6px;font-weight:700;">Hold Days</label>
                <input type="number" min="0" max="365" id="hold_days" name="hold_days"
                    value="{{ old('hold_days', data_get($report, 'plan.hold_days', 0)) }}"
                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
            </div>
            <div>
                <label for="default_marketing_manager_user_id" style="display:block;margin-bottom:6px;font-weight:700;">Default Marketing Manager</label>
                <select id="default_marketing_manager_user_id" name="default_marketing_manager_user_id"
                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
                    <option value="">No default marketing manager</option>
                    @foreach(data_get($report, 'marketing_manager_options', []) as $manager)
                        <option value="{{ $manager->id }}"
                            {{ (string) old('default_marketing_manager_user_id', data_get($report, 'plan.default_marketing_manager_user_id', '')) === (string) $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;align-items:flex-end;">
                <button type="submit" style="padding:10px 16px;border:none;border-radius:8px;background:var(--theme-primary, #240E35);color:#fff;font-weight:700;cursor:pointer;">
                    Save Commission Settings
                </button>
            </div>
        </form>

        <div style="margin-top:12px;padding:12px 14px;border-radius:10px;background:#fbf9fd;border:1px solid var(--theme-border, #E6E1EF);color:var(--theme-muted, #6B7280);line-height:1.6;">
            Current attribution model:
            Sales agent commissions follow the assigned lead.
            Marketing manager commissions apply when a source campaign exists and a default marketing manager is configured.
        </div>
    </div>

    <div class="admin-kpi-board">
        <section class="admin-kpi-group" aria-label="Revenue Overview">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Revenue Overview</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--4">
                <article class="admin-kpi-card admin-kpi-card--primary">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Gross Paid Revenue</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-coins" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) data_get($report, 'totals.gross_paid_revenue', 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Paid funnel revenue before deductions</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Net Eligible Revenue</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-wallet" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) data_get($report, 'totals.net_eligible_revenue', 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">After estimated gateway and platform fees</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--warning">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Payable Commissions</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-hand-holding-usd" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) data_get($report, 'totals.payable_commissions_total', 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Ready for finance or owner payout processing</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--success">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Owner Residual</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-piggy-bank" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) data_get($report, 'totals.owner_residual_total', 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Estimated residual earnings after commissions</div>
                </article>
            </div>
        </section>
    </div>

    <div class="admin-kpi-board" style="margin-top: 20px;">
        <section class="admin-kpi-group" aria-label="Operations Overview">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Operations Overview</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--4">
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Held Commissions</span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) data_get($report, 'totals.held_commissions_total', 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Inside the configured hold period</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Pending Receipts</span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format((int) data_get($report, 'totals.pending_receipt_count', 0)) }}</div>
                    <div class="admin-kpi-card__meta">Waiting for review or stronger proof</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Auto Approved Receipts</span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format((int) data_get($report, 'totals.auto_approved_receipt_count', 0)) }}</div>
                    <div class="admin-kpi-card__meta">Validated by the automation matcher</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Default Payout</span>
                    </div>
                    <div class="admin-kpi-card__value admin-kpi-card__value--text">{{ data_get($report, 'payout_account.masked_destination', $emptyDash) }}</div>
                    <div class="admin-kpi-card__meta">{{ data_get($report, 'payout_account.is_verified') ? 'Verified destination' : 'Pending verification' }}</div>
                </article>
            </div>
        </section>
    </div>

    <div class="charts" style="margin-top: 20px;">
        <div class="chart">
            <div class="chart-heading">
                <h3>Revenue Trend (Last 6 Months)</h3>
            </div>
            <canvas id="ownerReportsTrendChart"></canvas>
        </div>
        <div class="chart">
            <div class="chart-heading">
                <h3>Subscription and Billing Health</h3>
            </div>
            <div class="app-table-scroll app-table-scroll--wide">
                <table>
                    <tbody>
                        <tr><th>Status</th><td>{{ ucfirst((string) data_get($report, 'subscription.status', 'unknown')) }}</td></tr>
                        <tr><th>Billing</th><td>{{ ucfirst((string) data_get($report, 'subscription.billing_status', 'unknown')) }}</td></tr>
                        <tr><th>Trial Days Remaining</th><td>{{ number_format((int) data_get($report, 'subscription.trial_days_remaining', 0)) }}</td></tr>
                        <tr><th>Grace Days Remaining</th><td>{{ number_format((int) data_get($report, 'subscription.grace_days_remaining', 0)) }}</td></tr>
                        <tr><th>Active Since</th><td>{{ optional(data_get($report, 'subscription.subscription_activated_at'))->format('Y-m-d H:i') ?? $emptyDash }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="charts" style="margin-top: 20px;">
        <div class="chart">
            <h3>Top Funnels</h3>
            <div class="app-table-scroll app-table-scroll--wide">
                <table>
                    <thead><tr><th>Funnel</th><th>Paid Orders</th><th>Paid Revenue</th></tr></thead>
                    <tbody>
                        @forelse(data_get($report, 'top_funnels', []) as $row)
                            <tr>
                                <td>{{ $row->funnel_name ?? $emptyDash }}</td>
                                <td>{{ number_format((int) ($row->paid_orders ?? 0)) }}</td>
                                <td>PHP {{ number_format((float) ($row->paid_revenue ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No funnel revenue found for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="chart">
            <h3>Top Campaigns</h3>
            <div class="app-table-scroll app-table-scroll--wide">
                <table>
                    <thead><tr><th>Campaign</th><th>Paid Orders</th><th>Paid Revenue</th></tr></thead>
                    <tbody>
                        @forelse(data_get($report, 'top_campaigns', []) as $row)
                            <tr>
                                <td>{{ $row->source_campaign ?? $emptyDash }}</td>
                                <td>{{ number_format((int) ($row->paid_orders ?? 0)) }}</td>
                                <td>PHP {{ number_format((float) ($row->paid_revenue ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No attributed campaign revenue found for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3>Recent Commissions</h3>
        <div class="app-table-scroll app-table-scroll--wide">
        <table>
            <thead><tr><th>Beneficiary</th><th>Role</th><th>Amount</th><th>Status</th><th>Payment</th></tr></thead>
            <tbody>
                @forelse(data_get($report, 'recent_commissions', []) as $entry)
                    <tr>
                        <td>{{ $entry->user->name ?? $emptyDash }}</td>
                        <td>{{ ucwords(str_replace('-', ' ', $entry->commission_role)) }}</td>
                        <td>PHP {{ number_format((float) $entry->commission_amount, 2) }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $entry->status)) }}</td>
                        <td>#{{ $entry->payment_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No commission entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3>Recent Receipts</h3>
        <div class="app-table-scroll app-table-scroll--wide">
        <table>
            <thead><tr><th>Receipt</th><th>Payment</th><th>Status</th><th>Automation</th><th>Uploader</th></tr></thead>
            <tbody>
                @forelse(data_get($report, 'recent_receipts', []) as $receipt)
                    <tr>
                        <td>#{{ $receipt->id }}</td>
                        <td>#{{ $receipt->payment_id }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $receipt->status)) }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $receipt->automation_status)) }}</td>
                        <td>{{ $receipt->uploader->name ?? $emptyDash }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No receipts found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3>Recent Payments</h3>
        <div class="app-table-scroll app-table-scroll--wide">
        <table>
            <thead><tr><th>Date</th><th>Funnel</th><th>Lead</th><th>Campaign</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                @forelse(data_get($report, 'recent_payments', []) as $payment)
                    <tr>
                        <td>{{ optional($payment->payment_date)->format('Y-m-d') ?? $emptyDash }}</td>
                        <td>{{ $payment->funnel->name ?? $emptyDash }}</td>
                        <td>{{ $payment->lead->name ?? $emptyDash }}</td>
                        <td>{{ $payment->lead->source_campaign ?? $emptyDash }}</td>
                        <td>PHP {{ number_format((float) $payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No payments found for the selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3>Automation Recommendations</h3>
        <ul style="margin:0;padding-left:20px;color:var(--theme-muted, #6B7280);line-height:1.7;">
            <li>Use n8n to email weekly owner report digests and commission-ready summaries.</li>
            <li>Use automation to alert finance when pending receipts or payable commissions exceed threshold.</li>
            <li>Keep Laravel as the source of truth for totals, and use n8n only for scheduled delivery and notifications.</li>
        </ul>
    </div>
@endsection

@section('scripts')
    <script>
        const ownerReportsTrendCtx = document.getElementById('ownerReportsTrendChart').getContext('2d');
        new Chart(ownerReportsTrendCtx, {
            type: 'line',
            data: {
                labels: @json(data_get($report, 'trend.labels', [])),
                datasets: [{
                    label: 'Paid Revenue',
                    data: @json(data_get($report, 'trend.values', [])),
                    borderColor: '#240E35',
                    backgroundColor: 'rgba(36, 14, 53, 0.15)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
@endsection
