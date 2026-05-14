@extends('layouts.admin')

@section('title', 'Template Analytics')

@section('styles')
    <style>
        .template-analytics-back-wrap {
            margin-left: auto;
            padding-right: 0;
            flex: 0 0 auto;
        }

        .template-analytics-header {
            justify-content: flex-start !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            gap: 10px !important;
        }

        .template-analytics-header > div:first-child {
            flex: 1 1 auto;
            min-width: 0;
        }

        .template-analytics-header .top-header-inline-actions {
            margin-left: 0 !important;
            gap: 8px !important;
            flex: 0 0 auto;
        }

        .template-analytics-header .global-utility-bar {
            margin-left: 0 !important;
        }

        .template-analytics-header h1 {
            margin: 0;
            line-height: 1.15;
        }

        .template-analytics-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            border: 1px solid var(--theme-border, #E6E1EF);
            background: #fff;
            color: var(--theme-primary, #240E35);
            text-decoration: none;
            position: relative;
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .template-analytics-back:hover,
        .template-analytics-back:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
            border-color: #BFC9D8;
            outline: none;
        }

        .template-analytics-back::after {
            content: attr(data-tooltip);
            position: absolute;
            right: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%) translateX(4px);
            padding: 7px 10px;
            border-radius: 8px;
            background: #240E35;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity .14s ease, transform .14s ease;
            z-index: 5;
        }

        .template-analytics-back::before {
            content: "";
            position: absolute;
            right: calc(100% + 6px);
            top: 50%;
            transform: translateY(-50%);
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            border-left: 6px solid #240E35;
            opacity: 0;
            pointer-events: none;
            transition: opacity .14s ease;
            z-index: 5;
        }

        .template-analytics-back:hover::after,
        .template-analytics-back:hover::before,
        .template-analytics-back:focus-visible::after,
        .template-analytics-back:focus-visible::before {
            opacity: 1;
        }

        .template-analytics-back:hover::after,
        .template-analytics-back:focus-visible::after {
            transform: translateY(-50%) translateX(0);
        }

        .template-filter-meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding: 6px 12px;
            border: 1px solid #D7E3F8;
            border-radius: 999px;
            background: #FFFFFF;
            color: #5B6B84;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            flex-wrap: wrap;
        }

        .template-filter-meta-pill__divider {
            width: 4px;
            height: 4px;
            border-radius: 999px;
            background: #A8B8D4;
            flex: 0 0 auto;
        }

        .analytics-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--theme-border, #E6E1EF);
            background: #fff;
            color: var(--theme-primary, #240E35);
            text-decoration: none;
            font-weight: 700;
        }

        .analytics-btn--icon-only {
            position: relative;
            display: grid;
            place-items: center;
            width: 46px;
            height: 46px;
            padding: 0;
            gap: 0;
            border-radius: 12px;
            flex: 0 0 46px;
        }

        .analytics-btn-icon {
            display: grid;
            place-items: center;
            width: 20px;
            height: 20px;
        }

        .analytics-btn--icon-only i {
            display: block;
            margin: 0;
            width: 18px;
            text-align: center;
            font-size: 18px;
            line-height: 1;
        }

        .analytics-btn-tooltip {
            position: absolute;
            left: 50%;
            top: calc(100% + 10px);
            transform: translateX(-50%) translateY(-6px);
            padding: 7px 10px;
            border-radius: 10px;
            background: var(--theme-primary, #240E35);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
            box-shadow: 0 10px 24px rgba(15,23,42,.16);
            z-index: 12;
        }

        .analytics-btn-tooltip::after {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: transparent transparent var(--theme-primary, #240E35) transparent;
        }

        .analytics-btn--icon-only:hover .analytics-btn-tooltip,
        .analytics-btn--icon-only:focus-visible .analytics-btn-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }

        .template-analytics-section-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--theme-border, #E6E1EF);
        }

        .template-analytics-section-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-left: auto;
        }

        .template-analytics-section-actions h3 {
            margin: 0;
            padding: 0;
            border: 0;
        }

        .template-analytics-table {
            width: 100%;
        }

        .template-analytics-table-empty {
            text-align: center;
        }

        @media (max-width: 768px) {
            .template-analytics-header {
                justify-content: flex-start !important;
                align-items: center !important;
                flex-wrap: nowrap !important;
                gap: 8px !important;
            }

            .template-analytics-header.top-header--has-compact-actions {
                padding-right: 0 !important;
                min-height: 0 !important;
            }

            .template-analytics-back-wrap {
                margin-left: auto;
                padding-right: 0;
            }

            .template-analytics-header .top-header-inline-actions {
                position: static !important;
                margin-left: 0 !important;
                justify-content: flex-start !important;
                gap: 8px !important;
            }

            .template-analytics-header .global-utility-bar {
                margin-left: 0 !important;
            }

            .template-analytics-header h1 {
                font-size: 20px;
            }

            .template-filter-meta-pill {
                padding: 6px 10px;
                gap: 6px;
                font-size: 11px;
            }

            .template-analytics-section-actions {
                align-items: flex-start;
                gap: 10px;
            }

            .template-analytics-section-controls {
                width: auto;
                justify-content: flex-end;
            }

            .template-analytics-table-wrap {
                overflow: visible !important;
            }

            .template-analytics-table {
                min-width: 0 !important;
                border-collapse: separate;
                border-spacing: 0;
            }

            .template-analytics-table thead {
                display: none;
            }

            .template-analytics-table tbody {
                display: grid;
                gap: 12px;
            }

            .template-analytics-table tbody tr {
                display: block;
                border: 1px solid var(--theme-border, #E6E1EF);
                border-radius: 16px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
                overflow: hidden;
            }

            .template-analytics-table tbody td {
                display: grid;
                grid-template-columns: minmax(112px, 124px) minmax(0, 1fr);
                gap: 10px 14px;
                padding: 12px 14px;
                border-bottom: 1px solid var(--theme-border, #E6E1EF);
                white-space: normal;
                text-align: left;
                vertical-align: top;
            }

            .template-analytics-table tbody td:last-child {
                border-bottom: none;
            }

            .template-analytics-table tbody td::before {
                content: attr(data-label);
                font-size: 11px;
                font-weight: 800;
                letter-spacing: .05em;
                text-transform: uppercase;
                color: var(--theme-muted, #6B7280);
                line-height: 1.35;
            }

            .template-analytics-table tbody td > * {
                min-width: 0;
            }

            .template-analytics-table-empty {
                display: block !important;
                grid-template-columns: 1fr !important;
                text-align: left;
                padding: 16px 14px !important;
            }

            .template-analytics-table-empty::before {
                content: none !important;
            }

            .template-analytics-header .notification-shell,
            .template-analytics-header .global-utility-bar {
                flex: 0 0 auto;
            }
        }
    </style>
@endsection

@php
    $totals = data_get($report, 'totals', []);
    $templateRows = data_get($report, 'template_rows', collect());
    $topTenants = data_get($report, 'top_tenants', collect());
    $topSources = data_get($report, 'top_sources', collect());
    $templatePerformanceExcelUrl = route('admin.funnel-templates.analytics.export', request()->query());
    /** @var \App\Models\FunnelTemplate|null $selectedTemplate */
@endphp

@section('content')
    <div class="top-header template-analytics-header" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:nowrap;">
        <div>
            <h1>Template Marketplace Analytics</h1>
        </div>
        <div class="template-analytics-back-wrap">
            <a href="{{ route('admin.funnel-templates.index') }}"
                class="template-analytics-back"
                data-tooltip="Back to Templates"
                aria-label="Back to Templates">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </div>

    @if($selectedTemplate)
        <div class="app-card" style="margin-top:16px;padding:16px 18px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;border:1px solid #DBEAFE;background:#F8FBFF;">
            <div>
                <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#2563EB;">Filtered View</div>
                <div style="margin-top:4px;font-size:18px;font-weight:800;color:var(--theme-primary, #240E35);">{{ $selectedTemplate->name }}</div>
                <div class="template-filter-meta-pill">
                    <span>Status: {{ ucfirst((string) $selectedTemplate->status) }}</span>
                    @if($selectedTemplate->slug)
                        <span class="template-filter-meta-pill__divider" aria-hidden="true"></span>
                        <span>Slug: {{ $selectedTemplate->slug }}</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.funnel-templates.analytics') }}" class="btn-create btn-create--icon-expand" style="background:#fff;color:#1D4ED8;border:1px solid #BFDBFE;">
                <i class="fas fa-layer-group"></i>
                <span class="btn-create__label">View All Templates</span>
            </a>
        </div>
    @endif

    <div class="admin-kpi-board" style="margin-top: 20px;">
        <section class="admin-kpi-group" aria-label="Marketplace Totals">
            <div class="admin-kpi-group__header">
                <span class="admin-kpi-group__eyebrow">Marketplace Totals</span>
            </div>
            <div class="admin-kpi-grid admin-kpi-grid--4">
                <article class="admin-kpi-card admin-kpi-card--primary">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Published Templates</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format((int) ($totals['published_templates'] ?? 0)) }}</div>
                    <div class="admin-kpi-card__meta">Published step-by-step templates currently available to workspaces</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Cloned Funnels</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-copy" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value">{{ number_format((int) ($totals['cloned_funnels'] ?? 0)) }}</div>
                    <div class="admin-kpi-card__meta">Tenant funnels that still point back to a published source template</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--success">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Gross Revenue</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-coins" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) ($totals['gross_revenue'] ?? 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Paid funnel revenue tied back to published templates</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Net Revenue</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-wallet" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) ($totals['net_revenue'] ?? 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Gross revenue after stored refund deductions</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--warning">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Template Royalties</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-hand-holding-usd" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) ($totals['template_royalty_total'] ?? 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Super Admin royalty earnings generated by cloned-template sales</div>
                </article>
                <article class="admin-kpi-card">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Affiliate Commissions</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-link" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) ($totals['affiliate_commission_total'] ?? 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Tracked referral payouts connected to template-driven orders</div>
                </article>
                <article class="admin-kpi-card admin-kpi-card--danger">
                    <div class="admin-kpi-card__topline">
                        <span class="admin-kpi-card__label">Payout Liabilities</span>
                        <span class="admin-kpi-card__icon"><i class="fas fa-file-invoice-dollar" aria-hidden="true"></i></span>
                    </div>
                    <div class="admin-kpi-card__value"><span class="admin-kpi-card__unit">PHP</span>{{ number_format((float) ($totals['payout_liabilities'] ?? 0), 2) }}</div>
                    <div class="admin-kpi-card__meta">Held, payable, and approved template or referral commissions still outstanding</div>
                </article>
            </div>
        </section>
    </div>

    <div class="charts" style="margin-top: 20px;">
        <div class="chart">
            <div class="chart-heading template-analytics-section-actions">
                <h3>Template Performance</h3>
                <div class="template-analytics-section-controls">
                    <a
                        href="{{ $templatePerformanceExcelUrl }}"
                        class="analytics-btn analytics-btn--icon-only"
                        aria-label="Download to Excel"
                    >
                        <span class="analytics-btn-icon" aria-hidden="true">
                            <i class="fas fa-file-excel" aria-hidden="true"></i>
                        </span>
                        <span class="analytics-btn-tooltip" role="tooltip">Download to Excel</span>
                    </a>
                </div>
            </div>
            <div class="app-table-scroll app-table-scroll--wide template-analytics-table-wrap">
                <table class="template-analytics-table">
                    <thead>
                        <tr>
                            <th>Template</th>
                            <th>Royalty Rate</th>
                            <th>Tenants</th>
                            <th>Cloned Funnels</th>
                            <th>Paid Orders</th>
                            <th>Gross Revenue</th>
                            <th>Net Revenue</th>
                            <th>Royalty Total</th>
                            <th>Affiliate Total</th>
                            <th>Avg Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templateRows as $row)
                            <tr>
                                <td data-label="Template">
                                    <strong>{{ $row->name }}</strong><br>
                                    <span style="color:var(--theme-muted, #6B7280);font-size:12px;">{{ $row->slug }}</span>
                                </td>
                                <td data-label="Royalty Rate">{{ number_format((float) ($row->royalty_rate ?? 0), 2) }}%</td>
                                <td data-label="Tenants">{{ number_format((int) ($row->tenant_count ?? 0)) }}</td>
                                <td data-label="Cloned Funnels">{{ number_format((int) ($row->cloned_funnels_count ?? 0)) }}</td>
                                <td data-label="Paid Orders">{{ number_format((int) ($row->paid_orders_count ?? 0)) }}</td>
                                <td data-label="Gross Revenue">PHP {{ number_format((float) ($row->gross_revenue ?? 0), 2) }}</td>
                                <td data-label="Net Revenue">PHP {{ number_format((float) ($row->net_revenue ?? 0), 2) }}</td>
                                <td data-label="Royalty Total">PHP {{ number_format((float) ($row->template_royalty_total ?? 0), 2) }}</td>
                                <td data-label="Affiliate Total">PHP {{ number_format((float) ($row->affiliate_commission_total ?? 0), 2) }}</td>
                                <td data-label="Avg Order">PHP {{ number_format((float) ($row->average_order_value ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="template-analytics-table-empty">No published-template revenue has been recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="charts" style="margin-top: 20px;">
        <div class="chart">
            <div class="chart-heading">
                <h3>Top Tenant Workspaces</h3>
            </div>
            <div class="app-table-scroll app-table-scroll--wide template-analytics-table-wrap">
                <table class="template-analytics-table">
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Paid Orders</th>
                            <th>Gross Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topTenants as $row)
                            <tr>
                                <td data-label="Tenant">{{ $row->company_name }}</td>
                                <td data-label="Paid Orders">{{ number_format((int) ($row->paid_orders ?? 0)) }}</td>
                                <td data-label="Gross Revenue">PHP {{ number_format((float) ($row->gross_revenue ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="template-analytics-table-empty">No tenant activity found yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="chart">
            <div class="chart-heading">
                <h3>Top Source Platforms</h3>
            </div>
            <div class="app-table-scroll app-table-scroll--wide template-analytics-table-wrap">
                <table class="template-analytics-table">
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Paid Orders</th>
                            <th>Gross Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSources as $row)
                            <tr>
                                <td data-label="Platform">{{ $row->source_label }}</td>
                                <td data-label="Paid Orders">{{ number_format((int) ($row->paid_orders ?? 0)) }}</td>
                                <td data-label="Gross Revenue">PHP {{ number_format((float) ($row->gross_revenue ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="template-analytics-table-empty">No traffic-source attribution has been captured yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
