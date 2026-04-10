@extends('layouts.admin')

@section('title', 'Funnel Analytics')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/funnels-analytics.css') }}">
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
        $funnelPurpose = \App\Models\Funnel::normalizePurpose($funnel->purpose ?? ($funnel->template_type ?? 'service'));
        $isPhysicalAnalytics = in_array($funnelPurpose, ['physical_product', 'hybrid'], true);
        $summarySectionTitle = $isPhysicalAnalytics ? 'Order Directory' : 'Offer Activity Table';
        $selectedOfferLabel = $isPhysicalAnalytics ? 'Selected Product' : 'Selected Service';
        $physicalOrders = collect($analytics['physical_orders'] ?? []);
        $physicalOrderTotals = $analytics['physical_order_totals'] ?? [];
        $physicalPendingOrders = collect($analytics['physical_pending_orders'] ?? []);
        $physicalPaidOrders = collect($analytics['physical_paid_orders'] ?? []);
        $physicalProductBreakdown = collect($analytics['physical_product_breakdown'] ?? []);
        $productBreakdownPerPage = 3;
        $productBreakdownTotal = $physicalProductBreakdown->count();
        $productBreakdownLastPage = max(1, (int) ceil(max(1, $productBreakdownTotal) / $productBreakdownPerPage));
        $productBreakdownPage = max(1, (int) request()->query('product_page', 1));
        $productBreakdownPage = min($productBreakdownPage, $productBreakdownLastPage);
        $productBreakdownRows = $physicalProductBreakdown->forPage($productBreakdownPage, $productBreakdownPerPage)->values();
        $checkoutToPaidRate = (int) ($totals['checkout_start_count'] ?? 0) > 0
            ? round((((int) ($physicalOrderTotals['paid_orders'] ?? 0)) / max(1, (int) ($totals['checkout_start_count'] ?? 0))) * 100, 2)
            : 0;
        $deliveryStatusOptions = [
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
        ];
        $offerActivityGroups = [
            'upsell_accepted' => [
                'title' => 'Upsell Accepted',
                'description' => 'Customers who accepted the upsell offer.',
                'action' => 'Accepted',
                'offer_type' => 'Upsell',
                'count' => (int) ($offerCounts['upsell_accepted'] ?? 0),
                'rows' => $analytics['offer_activity']['upsell_accepted'] ?? [],
            ],
            'upsell_declined' => [
                'title' => 'Upsell Declined',
                'description' => 'Customers who declined the upsell offer.',
                'action' => 'Declined',
                'offer_type' => 'Upsell',
                'count' => (int) ($offerCounts['upsell_declined'] ?? 0),
                'rows' => $analytics['offer_activity']['upsell_declined'] ?? [],
            ],
            'downsell_accepted' => [
                'title' => 'Downsell Accepted',
                'description' => 'Customers who accepted the downsell offer.',
                'action' => 'Accepted',
                'offer_type' => 'Downsell',
                'count' => (int) ($offerCounts['downsell_accepted'] ?? 0),
                'rows' => $analytics['offer_activity']['downsell_accepted'] ?? [],
            ],
            'downsell_declined' => [
                'title' => 'Downsell Declined',
                'description' => 'Customers who declined the downsell offer.',
                'action' => 'Declined',
                'offer_type' => 'Downsell',
                'count' => (int) ($offerCounts['downsell_declined'] ?? 0),
                'rows' => $analytics['offer_activity']['downsell_declined'] ?? [],
            ],
        ];
        $offerCustomerSummary = collect($analytics['offer_customer_summary'] ?? []);
        $offerRateValues = [
            (float) ($rates['upsell_acceptance_rate'] ?? 0),
            (float) ($rates['downsell_acceptance_rate'] ?? 0),
            (float) ($rates['abandoned_checkout_rate'] ?? 0),
        ];
        $summaryRows = $isPhysicalAnalytics ? $physicalOrders : $offerCustomerSummary;
        $hasOfferData = ((int) ($offerCounts['upsell_accepted'] ?? 0) > 0)
            || ((int) ($offerCounts['upsell_declined'] ?? 0) > 0)
            || ((int) ($offerCounts['downsell_accepted'] ?? 0) > 0)
            || ((int) ($offerCounts['downsell_declined'] ?? 0) > 0)
            || ((float) ($rates['abandoned_checkout_rate'] ?? 0) > 0);
    @endphp

    <div class="analytics-shell">
        @if(session('success'))
            <div class="analytics-alert analytics-alert--success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="analytics-alert analytics-alert--error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="analytics-alert analytics-alert--error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="analytics-topbar">
            <div>
                <h1>{{ $funnel->name }} Analytics</h1>
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
                @unless($isPhysicalAnalytics)
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
                @endunless
                <button type="submit" class="analytics-btn primary"><i class="fas fa-filter"></i> Apply Filter</button>
                <a href="{{ route('funnels.analytics', $funnel) }}" class="analytics-btn">Clear</a>
            </form>
        </div>

        <div class="analytics-kpis">
            @if($isPhysicalAnalytics)
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label analytics-kpi-label-wrap">
                        <span>Revenue</span>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Revenue help">?</span>
                            <span class="analytics-help-tip">Paid revenue from completed orders.</span>
                        </span>
                    </div>
                    <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['revenue'] ?? 0), 2) }}</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Paid Orders</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($physicalOrderTotals['paid_orders'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) $checkoutToPaidRate, 2) }}% of checkout starts became paid orders</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label analytics-kpi-label-wrap">
                        <span>Pending Orders</span>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Pending orders help">?</span>
                            <span class="analytics-help-tip">Orders waiting for payment completion.</span>
                        </span>
                    </div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($physicalOrderTotals['pending_orders'] ?? 0)) }}</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label analytics-kpi-label-wrap">
                        <span>Units Ordered</span>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Units ordered help">?</span>
                            <span class="analytics-help-tip">Total item quantity across non-abandoned orders.</span>
                        </span>
                    </div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($physicalOrderTotals['units_ordered'] ?? 0)) }}</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Abandoned Checkouts</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['abandoned_checkout_count'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) ($rates['abandoned_checkout_rate'] ?? 0), 2) }}% abandonment after checkout started</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Traffic Snapshot</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['entry_visits'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((int) ($totals['checkout_start_count'] ?? 0)) }} checkout starts | PHP {{ number_format((float) ($totals['revenue_per_visit'] ?? 0), 2) }} per visit</div>
                </div>
            @else
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label analytics-kpi-label-wrap">
                        <span>Entry Visits</span>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Entry visits help">?</span>
                            <span class="analytics-help-tip">Unique first-step visits.</span>
                        </span>
                    </div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['entry_visits'] ?? 0)) }}</div>
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
                    <div class="analytics-kpi-label analytics-kpi-label-wrap">
                        <span>Revenue</span>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Revenue help">?</span>
                            <span class="analytics-help-tip">Paid revenue tied to this funnel.</span>
                        </span>
                    </div>
                    <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['revenue'] ?? 0), 2) }}</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label">Abandoned Checkout</div>
                    <div class="analytics-kpi-value">{{ number_format((int) ($totals['abandoned_checkout_count'] ?? 0)) }}</div>
                    <div class="analytics-kpi-sub">{{ number_format((float) ($rates['abandoned_checkout_rate'] ?? 0), 2) }}% abandonment</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label analytics-kpi-label-wrap">
                        <span>Average Order Value</span>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Average order value help">?</span>
                            <span class="analytics-help-tip">Average paid order amount.</span>
                        </span>
                    </div>
                    <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['average_order_value'] ?? 0), 2) }}</div>
                </div>
                <div class="analytics-kpi">
                    <div class="analytics-kpi-label analytics-kpi-label-wrap">
                        <span>Revenue Per Visit</span>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Revenue per visit help">?</span>
                            <span class="analytics-help-tip">Revenue divided by entry visits.</span>
                        </span>
                    </div>
                    <div class="analytics-kpi-value">PHP {{ number_format((float) ($totals['revenue_per_visit'] ?? 0), 2) }}</div>
                </div>
            @endif
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
            @if($isPhysicalAnalytics)
                <div class="analytics-card analytics-card--physical-overview">
                    <h3>Sales Overview</h3>
                    <ul class="analytics-list">
                        <li>
                            <div>
                                <span class="analytics-list-label">Order conversion</span>
                                <span class="analytics-list-meta">Paid orders divided by checkout starts</span>
                            </div>
                            <span class="analytics-list-value">{{ number_format((float) $checkoutToPaidRate, 2) }}%</span>
                        </li>
                        <li>
                            <div>
                                <span class="analytics-list-label">Checkout starts</span>
                                <span class="analytics-list-meta">Visitors who began the payment step</span>
                            </div>
                            <span class="analytics-list-value">{{ number_format((int) ($totals['checkout_start_count'] ?? 0)) }}</span>
                        </li>
                        <li>
                            <div>
                                <span class="analytics-list-label">Entry visits</span>
                                <span class="analytics-list-meta">Unique first-step visitors</span>
                            </div>
                            <span class="analytics-list-value">{{ number_format((int) ($totals['entry_visits'] ?? 0)) }}</span>
                        </li>
                        <li>
                            <div>
                                <span class="analytics-list-label">Revenue per visit</span>
                                <span class="analytics-list-meta">Paid revenue divided by entry visits</span>
                            </div>
                            <span class="analytics-list-value">PHP {{ number_format((float) ($totals['revenue_per_visit'] ?? 0), 2) }}</span>
                        </li>
                    </ul>
                </div>
                <div class="analytics-card analytics-card--offer-counts">
                    <h3>Order Status</h3>
                    <div class="analytics-mini-grid">
                        <div class="analytics-mini-stat">
                            <span>Pending</span>
                            <strong>{{ number_format((int) ($physicalOrderTotals['pending_orders'] ?? 0)) }}</strong>
                            <small>Orders waiting for payment confirmation</small>
                        </div>
                        <div class="analytics-mini-stat">
                            <span>Paid</span>
                            <strong>{{ number_format((int) ($physicalOrderTotals['paid_orders'] ?? 0)) }}</strong>
                            <small>Orders that completed payment</small>
                        </div>
                        <div class="analytics-mini-stat">
                            <span>Abandoned</span>
                            <strong>{{ number_format((int) ($physicalOrderTotals['abandoned_orders'] ?? 0)) }}</strong>
                            <small>Checkout starts without paid completion</small>
                        </div>
                        <div class="analytics-mini-stat">
                            <span>Units Ordered</span>
                            <strong>{{ number_format((int) ($physicalOrderTotals['units_ordered'] ?? 0)) }}</strong>
                            <small>Total quantity across active orders</small>
                        </div>
                    </div>
                </div>

                <div class="analytics-card analytics-card--offer-counts">
                    <h3>Product Breakdown</h3>
                    <div class="analytics-table-wrap analytics-table-wrap--product">
                        <table class="analytics-table">
                            <tbody>
                                @forelse($productBreakdownRows as $item)
                                    <tr>
                                        <td>
                                            <div class="analytics-product-cell">
                                                <strong>{{ $item['name'] ?? 'Product' }}</strong>
                                                <span class="analytics-info-dot" data-product-details-toggle role="button" tabindex="0" aria-expanded="false" aria-label="Show product quantity details">
                                                    <i class="fas fa-info" aria-hidden="true"></i>
                                                </span>
                                            </div>
                                            <div class="analytics-product-details" data-product-details>
                                                    <div class="analytics-tooltip-grid">
                                                        <span class="label">Units</span><span class="value">{{ number_format((int) ($item['units'] ?? 0)) }}</span>
                                                        <span class="label">Orders</span><span class="value">{{ number_format((int) ($item['orders'] ?? 0)) }}</span>
                                                        <span class="label">Paid Units</span><span class="value">{{ number_format((int) ($item['paid_units'] ?? 0)) }}</span>
                                                    </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td>No product quantities have been recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($productBreakdownTotal > $productBreakdownPerPage)
                        <div class="analytics-product-pagination">
                            <span class="analytics-product-page-note">
                                Showing {{ ($productBreakdownPage - 1) * $productBreakdownPerPage + 1 }}-{{ min($productBreakdownTotal, $productBreakdownPage * $productBreakdownPerPage) }} of {{ $productBreakdownTotal }}
                            </span>
                            @if($productBreakdownPage > 1)
                                <a
                                    href="{{ route('funnels.analytics', array_merge(['funnel' => $funnel], request()->except('product_page'), ['product_page' => $productBreakdownPage - 1])) }}"
                                    class="analytics-btn"
                                >
                                    <i class="fas fa-chevron-left" aria-hidden="true"></i> Prev
                                </a>
                            @endif
                            @if($productBreakdownPage < $productBreakdownLastPage)
                                <a
                                    href="{{ route('funnels.analytics', array_merge(['funnel' => $funnel], request()->except('product_page'), ['product_page' => $productBreakdownPage + 1])) }}"
                                    class="analytics-btn"
                                >
                                    Next <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                    <div class="analytics-product-endnote">***Nothing Follows***</div>
                </div>
            @else
                <div class="analytics-card analytics-card--step-visits">
                    <div class="analytics-chart-title">
                        <h3>Step Visits</h3>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Step visits help">?</span>
                            <span class="analytics-help-tip">Shows visits and drop-off count for each funnel step.</span>
                        </span>
                    </div>
                    <div class="analytics-chart-wrap">
                        <canvas id="stepVisitsChart"></canvas>
                    </div>
                </div>
                <div class="analytics-card analytics-card--offer-rates">
                    <div class="analytics-chart-title">
                        <h3>Offer Rates</h3>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Offer rates help">?</span>
                            <span class="analytics-help-tip">Shows acceptance and abandonment rates for offer flow decisions.</span>
                        </span>
                    </div>
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
                        @foreach($offerActivityGroups as $groupKey => $group)
                            <button
                                type="button"
                                class="analytics-mini-stat analytics-mini-stat--button"
                                data-offer-activity="{{ $groupKey }}"
                                aria-haspopup="dialog"
                                aria-controls="offerActivityModal"
                            >
                                <span>{{ $group['title'] }}</span>
                                <strong>{{ number_format($group['count']) }}</strong>
                                <small>Click to view customers</small>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @unless($isPhysicalAnalytics)
            <div class="analytics-grid">
                <div class="analytics-card">
                    <div class="analytics-chart-title">
                        <h3>Daily Funnel Trend</h3>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Daily trend help">?</span>
                            <span class="analytics-help-tip">Shows daily movement of visits, opt-ins, checkout starts, and paid events.</span>
                        </span>
                    </div>
                    <div class="analytics-chart-wrap">
                        <canvas id="dailyTrendChart"></canvas>
                    </div>
                </div>

                <div class="analytics-card">
                    <div class="analytics-chart-title">
                        <h3>Conversion Path</h3>
                        <span class="analytics-help-wrap">
                            <span class="analytics-help-dot" tabindex="0" aria-label="Conversion path help">?</span>
                            <span class="analytics-help-tip">Shows how many users reached each major conversion stage.</span>
                        </span>
                    </div>
                    <div class="analytics-chart-wrap">
                        <canvas id="conversionPathChart"></canvas>
                    </div>
                </div>
            </div>
        @endunless

        @if($isPhysicalAnalytics)
            <div class="analytics-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                    <h3 style="margin:0;">Pending Orders</h3>
                    <button type="button" id="togglePendingOrdersBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="pendingOrdersContent"><i class="fas fa-eye" aria-hidden="true"></i><span>Show</span></button>
                </div>
                <div id="pendingOrdersContent" style="display:none;">
                    <div class="analytics-table-wrap">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Order Items</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Delivery Address</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($physicalPendingOrders as $row)
                                    <tr>
                                        <td><strong>{{ $row['customer'] ?? 'Anonymous visitor' }}</strong><br><span style="color:var(--theme-muted, #6B7280);font-size:12px;">{{ $row['email'] ?? 'N/A' }}</span></td>
                                        <td>{{ $row['phone'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(!empty($row['order_items']) && is_array($row['order_items']))
                                                @foreach($row['order_items'] as $item)
                                                    <div><strong>{{ $item['name'] ?? 'Product' }}</strong> x{{ max(1, (int) ($item['quantity'] ?? 1)) }}</div>
                                                @endforeach
                                            @else
                                                {{ $row['order_items_label'] ?? ($row['selected_offer'] ?? 'N/A') }}
                                            @endif
                                        </td>
                                        <td>{{ (int) ($row['order_quantity'] ?? 0) > 0 ? (int) $row['order_quantity'] : 'N/A' }}</td>
                                        <td>PHP {{ number_format((float) ($row['checkout_amount'] ?? 0), 2) }}</td>
                                        <td>{{ $row['delivery_address'] ?? 'N/A' }}</td>
                                        <td>{{ $row['last_activity'] ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="text-align:center;">No pending physical-product orders right now.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="analytics-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                    <h3 style="margin:0;">Paid Orders</h3>
                    <button type="button" id="togglePaidOrdersBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="paidOrdersContent"><i class="fas fa-eye" aria-hidden="true"></i><span>Show</span></button>
                </div>
                <div id="paidOrdersContent" style="display:none;">
                    <div class="analytics-table-wrap">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Paid Order</th>
                                    <th>Amount</th>
                                    <th>Delivery Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($physicalPaidOrders as $row)
                                    <tr>
                                        <td>
                                            <strong>{{ $row['customer'] ?? 'Anonymous visitor' }}</strong><br>
                                            <span style="color:var(--theme-muted, #6B7280);font-size:12px;">{{ $row['email'] ?? 'N/A' }}</span><br>
                                            <span style="color:var(--theme-muted, #6B7280);font-size:12px;">{{ $row['phone'] ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($row['order_items']) && is_array($row['order_items']))
                                                @foreach($row['order_items'] as $item)
                                                    <div><strong>{{ $item['name'] ?? 'Product' }}</strong> x{{ max(1, (int) ($item['quantity'] ?? 1)) }}</div>
                                                @endforeach
                                            @else
                                                {{ $row['order_items_label'] ?? ($row['selected_offer'] ?? 'N/A') }}
                                            @endif
                                            <div style="margin-top:8px;font-size:12px;color:var(--theme-muted, #6B7280);">
                                                Qty: {{ (int) ($row['order_quantity'] ?? 0) > 0 ? (int) $row['order_quantity'] : 'N/A' }}
                                            </div>
                                            <div style="margin-top:6px;font-size:12px;color:var(--theme-muted, #6B7280);">
                                                {{ $row['delivery_address'] ?? 'No delivery address recorded' }}
                                            </div>
                                        </td>
                                        <td>
                                            <strong>PHP {{ number_format((float) ($row['checkout_amount'] ?? 0), 2) }}</strong><br>
                                            <span class="analytics-pill" style="margin-top:8px;">{{ ucwords(str_replace('_', ' ', (string) ($row['delivery_status'] ?? 'processing'))) }}</span>
                                            @if(!empty($row['delivery_updated_label']))
                                                <div style="margin-top:8px;font-size:12px;color:var(--theme-muted, #6B7280);">Last email: {{ $row['delivery_updated_label'] }}</div>
                                            @endif
                                            @if(!empty($row['tracking_url']))
                                                <div style="margin-top:6px;"><a class="analytics-link" href="{{ $row['tracking_url'] }}" target="_blank" rel="noopener">Open tracking link</a></div>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($row['email']))
                                                <button
                                                    type="button"
                                                    class="analytics-btn primary"
                                                    data-delivery-update
                                                    data-order-key="{{ $row['order_key'] ?? '' }}"
                                                    data-recipient-email="{{ $row['email'] ?? '' }}"
                                                    data-delivery-status="{{ $row['delivery_status'] ?? 'processing' }}"
                                                    data-courier-name="{{ $row['courier_name'] ?? 'LBC' }}"
                                                    data-tracking-url="{{ $row['tracking_url'] ?? '' }}"
                                                    data-custom-message="{{ $row['delivery_message'] ?? '' }}"
                                                    data-customer="{{ $row['customer'] ?? 'Anonymous visitor' }}"
                                                >
                                                    <i class="fas fa-circle-info" aria-hidden="true"></i> See Details
                                                </button>
                                            @else
                                                <span style="color:var(--theme-muted, #6B7280);">No customer email available for this order.</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No paid physical-product orders yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="analytics-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                <h3 style="margin:0;">{{ $summarySectionTitle }}</h3>
                <button type="button" id="toggleOfferActivityBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="offerActivityContent"><i class="fas fa-eye" aria-hidden="true"></i><span>Show</span></button>
            </div>
            <div id="offerActivityContent" style="display:none;">
                @unless($isPhysicalAnalytics)
                    <div class="analytics-section-filters">
                        <div class="analytics-field">
                            <label for="offerActivityUpsellFilter">Upsell</label>
                            <select id="offerActivityUpsellFilter">
                                <option value="">All upsell statuses</option>
                                <option value="Accepted">Accepted</option>
                                <option value="Declined">Declined</option>
                                <option value="Did not avail">Did not avail</option>
                            </select>
                        </div>
                        <div class="analytics-field">
                            <label for="offerActivityDownsellFilter">Downsell</label>
                            <select id="offerActivityDownsellFilter">
                                <option value="">All downsell statuses</option>
                                <option value="Accepted">Accepted</option>
                                <option value="Declined">Declined</option>
                                <option value="Did not avail">Did not avail</option>
                            </select>
                        </div>
                        <button type="button" id="clearOfferActivityFiltersBtn" class="analytics-btn">Clear</button>
                    </div>
                @endunless
                <div class="analytics-table-wrap">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                @if($isPhysicalAnalytics)
                                    <th>Phone</th>
                                    <th>Order Items</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Checkout Paid</th>
                                    <th>Delivery Address</th>
                                    <th>Order Notes</th>
                                @else
                                    <th>{{ $selectedOfferLabel }}</th>
                                    <th>Checkout Paid</th>
                                    <th>Upsell</th>
                                    <th>Downsell</th>
                                @endif
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody id="offerActivityTableBody">
                            @forelse($summaryRows as $row)
                                @if($isPhysicalAnalytics)
                                    <tr>
                                        <td><strong>{{ $row['customer'] ?? 'Anonymous visitor' }}</strong></td>
                                        <td>{{ $row['email'] ?? 'N/A' }}</td>
                                        <td>{{ $row['phone'] ?? 'N/A' }}</td>
                                        <td>
                                            @if(!empty($row['order_items']) && is_array($row['order_items']))
                                                @foreach($row['order_items'] as $item)
                                                    <div>
                                                        <strong>{{ $item['name'] ?? 'Product' }}</strong> x{{ max(1, (int) ($item['quantity'] ?? 1)) }}
                                                    </div>
                                                    @if(!empty($item['badge']) || !empty($item['price']))
                                                        <div style="font-size:12px;color:var(--theme-muted, #6B7280);margin-bottom:4px;">
                                                            {{ trim(implode(' â€¢ ', array_filter([
                                                                $item['badge'] ?? null,
                                                                $item['price'] ?? null,
                                                            ]))) }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                {{ $row['order_items_label'] ?? ($row['selected_offer'] ?? 'N/A') }}
                                            @endif
                                        </td>
                                        <td>{{ (int) ($row['order_quantity'] ?? 0) > 0 ? (int) $row['order_quantity'] : 'N/A' }}</td>
                                        <td><span class="analytics-pill">{{ strtoupper(str_replace('_', ' ', (string) ($row['order_status'] ?? 'pending'))) }}</span></td>
                                        <td>PHP {{ number_format((float) ($row['checkout_amount'] ?? 0), 2) }}</td>
                                        <td>{{ $row['delivery_address'] ?? 'N/A' }}</td>
                                        <td>{{ $row['notes'] ?? 'N/A' }}</td>
                                        <td>{{ $row['last_activity'] ?? 'N/A' }}</td>
                                    </tr>
                                @else
                                    @php
                                        $upsellStatus = (string) ($row['upsell_status'] ?? 'Did not avail');
                                        $downsellStatus = (string) ($row['downsell_status'] ?? 'Did not avail');
                                        $upsellFilterValue = str_starts_with($upsellStatus, 'Accepted') ? 'Accepted' : $upsellStatus;
                                        $downsellFilterValue = str_starts_with($downsellStatus, 'Accepted') ? 'Accepted' : $downsellStatus;
                                    @endphp
                                    <tr data-upsell-status="{{ $upsellFilterValue }}" data-downsell-status="{{ $downsellFilterValue }}">
                                        <td><strong>{{ $row['customer'] ?? 'Anonymous visitor' }}</strong></td>
                                        <td>{{ $row['email'] ?? 'N/A' }}</td>
                                        <td>{{ $row['selected_offer'] ?? 'N/A' }}</td>
                                        <td>PHP {{ number_format((float) ($row['checkout_amount'] ?? 0), 2) }}</td>
                                        <td>{{ $upsellStatus }}</td>
                                        <td>{{ $downsellStatus }}</td>
                                        <td>{{ $row['last_activity'] ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                            @empty
                                <tr id="offerActivityEmptyRow">
                                    <td colspan="{{ $isPhysicalAnalytics ? 10 : 7 }}">
                                        @if($isPhysicalAnalytics)
                                            No physical-product orders have been recorded for the current filters.
                                        @else
                                            No upsell or downsell activity has been recorded for the current filters.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                            @if(!$isPhysicalAnalytics && $offerCustomerSummary->isNotEmpty())
                                <tr id="offerActivityNoMatchRow" style="display:none;">
                                    <td colspan="7">No rows match the selected offer filters.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @unless($isPhysicalAnalytics)
            <div class="analytics-card">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
                    <h3 style="margin:0;">Step Performance</h3>
                    <button type="button" id="toggleStepPerformanceBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="stepPerformanceContent"><i class="fas fa-eye" aria-hidden="true"></i><span>Show</span></button>
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
        @endunless

        <div class="analytics-card">
            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px;">
                <h3 style="margin:0;">Recent Funnel Events</h3>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <a href="{{ route('funnels.events', $funnel) }}" class="analytics-btn">Open Raw Events JSON</a>
                    <button type="button" id="toggleRecentEventsBtn" class="analytics-toggle-btn" aria-expanded="false" aria-controls="recentEventsContent"><i class="fas fa-eye" aria-hidden="true"></i><span>Show</span></button>
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

    <div id="offerActivityModal" class="analytics-modal" hidden aria-hidden="true">
        <div class="analytics-modal-backdrop" data-offer-modal-close></div>
        <div class="analytics-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="offerActivityModalTitle">
            <div class="analytics-modal-head">
                <div>
                    <h3 id="offerActivityModalTitle">Offer Activity</h3>
                    <p id="offerActivityModalDescription">Customers tied to this offer action.</p>
                </div>
                <button type="button" class="analytics-modal-close" data-offer-modal-close aria-label="Close offer activity modal">&times;</button>
            </div>
            <div class="analytics-modal-body">
                <div class="analytics-table-wrap">
                    <table class="analytics-table" style="min-width: 720px;">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Selected Offer</th>
                                <th>Step</th>
                                <th>Paid Before Offer</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="offerActivityModalRows">
                            <tr>
                                <td colspan="8">Select an offer count card to view matching customers.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="deliveryUpdateModal" class="analytics-modal" hidden aria-hidden="true">
        <div class="analytics-modal-backdrop" data-delivery-modal-close></div>
        <div class="analytics-modal-dialog analytics-modal-dialog--compact" role="dialog" aria-modal="true" aria-labelledby="deliveryUpdateModalTitle">
            <div class="analytics-modal-head">
                <div>
                    <h3 id="deliveryUpdateModalTitle">Delivery Update</h3>
                    <p>Update status, tracking, and customer message before sending the email.</p>
                </div>
                <button type="button" class="analytics-modal-close" data-delivery-modal-close aria-label="Close delivery update modal">&times;</button>
            </div>
            <div class="analytics-modal-body">
                <div class="analytics-delivery-meta">
                    <strong id="deliveryUpdateModalCustomer">Customer: N/A</strong>
                    <span id="deliveryUpdateModalEmail" style="color:var(--theme-muted, #6B7280);">Email: N/A</span>
                </div>
                <form method="POST" action="{{ route('funnels.analytics.delivery-update', $funnel) }}" class="analytics-inline-form analytics-inline-form--compact" id="deliveryUpdateModalForm">
                    @csrf
                    <input type="hidden" name="order_key" id="deliveryUpdateOrderKey" value="">
                    <input type="hidden" name="recipient_email" id="deliveryUpdateRecipientEmail" value="">
                    <label style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);">Delivery status</label>
                    <select name="delivery_status" id="deliveryUpdateStatus">
                        @foreach($deliveryStatusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <label style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);">Courier</label>
                    <input type="text" name="courier_name" id="deliveryUpdateCourier" value="" placeholder="LBC">
                    <label style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);">Tracking link</label>
                    <input type="url" name="tracking_url" id="deliveryUpdateTracking" value="" placeholder="https://www.lbcexpress.com/...">
                    <label style="font-size:12px;font-weight:800;color:var(--theme-muted, #6B7280);">Extra message</label>
                    <textarea name="custom_message" id="deliveryUpdateMessage" placeholder="Optional note for the customer"></textarea>
                    <div class="analytics-inline-form-actions analytics-inline-form-actions--right">
                        <button type="submit" class="analytics-btn primary"><i class="fas fa-paper-plane" aria-hidden="true"></i> Send Email Update</button>
                    </div>
                </form>
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
        const offerActivityGroups = @json($offerActivityGroups);
        const toggleOfferActivityBtn = document.getElementById('toggleOfferActivityBtn');
        const offerActivityContent = document.getElementById('offerActivityContent');
        const togglePendingOrdersBtn = document.getElementById('togglePendingOrdersBtn');
        const pendingOrdersContent = document.getElementById('pendingOrdersContent');
        const togglePaidOrdersBtn = document.getElementById('togglePaidOrdersBtn');
        const paidOrdersContent = document.getElementById('paidOrdersContent');
        const offerActivityUpsellFilter = document.getElementById('offerActivityUpsellFilter');
        const offerActivityDownsellFilter = document.getElementById('offerActivityDownsellFilter');
        const clearOfferActivityFiltersBtn = document.getElementById('clearOfferActivityFiltersBtn');
        const offerActivityTableBody = document.getElementById('offerActivityTableBody');
        const offerActivityTableRows = offerActivityTableBody ? Array.from(offerActivityTableBody.querySelectorAll('tr[data-upsell-status]')) : [];
        const offerActivityNoMatchRow = document.getElementById('offerActivityNoMatchRow');
        const toggleStepPerformanceBtn = document.getElementById('toggleStepPerformanceBtn');
        const stepPerformanceContent = document.getElementById('stepPerformanceContent');
        const toggleRecentEventsBtn = document.getElementById('toggleRecentEventsBtn');
        const recentEventsContent = document.getElementById('recentEventsContent');
        const offerActivityModal = document.getElementById('offerActivityModal');
        const offerActivityModalTitle = document.getElementById('offerActivityModalTitle');
        const offerActivityModalDescription = document.getElementById('offerActivityModalDescription');
        const offerActivityModalRows = document.getElementById('offerActivityModalRows');
        const offerActivityButtons = document.querySelectorAll('[data-offer-activity]');
        const deliveryUpdateModal = document.getElementById('deliveryUpdateModal');
        const deliveryUpdateButtons = document.querySelectorAll('[data-delivery-update]');
        const deliveryUpdateOrderKey = document.getElementById('deliveryUpdateOrderKey');
        const deliveryUpdateRecipientEmail = document.getElementById('deliveryUpdateRecipientEmail');
        const deliveryUpdateStatus = document.getElementById('deliveryUpdateStatus');
        const deliveryUpdateCourier = document.getElementById('deliveryUpdateCourier');
        const deliveryUpdateTracking = document.getElementById('deliveryUpdateTracking');
        const deliveryUpdateMessage = document.getElementById('deliveryUpdateMessage');
        const deliveryUpdateModalCustomer = document.getElementById('deliveryUpdateModalCustomer');
        const deliveryUpdateModalEmail = document.getElementById('deliveryUpdateModalEmail');
        const productDetailsToggles = document.querySelectorAll('[data-product-details-toggle]');
        let lastOfferActivityTrigger = null;
        let lastDeliveryUpdateTrigger = null;

        function setToggleButtonState(button, expanded) {
            if (!button) {
                return;
            }

            const icon = button.querySelector('i');
            const label = button.querySelector('span');

            if (icon) {
                icon.classList.toggle('fa-eye', !expanded);
                icon.classList.toggle('fa-eye-slash', expanded);
            }

            if (label) {
                label.textContent = expanded ? 'Hide' : 'Show';
            } else {
                button.textContent = expanded ? 'Hide' : 'Show';
            }

            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }

        function bindCollapsibleSection(button, content) {
            if (!button || !content) {
                return;
            }

            setToggleButtonState(button, false);

            button.addEventListener('click', function() {
                const isHidden = content.style.display === 'none';
                content.style.display = isHidden ? 'block' : 'none';
                setToggleButtonState(button, isHidden);
            });
        }

        bindCollapsibleSection(toggleOfferActivityBtn, offerActivityContent);
        bindCollapsibleSection(togglePendingOrdersBtn, pendingOrdersContent);
        bindCollapsibleSection(togglePaidOrdersBtn, paidOrdersContent);
        bindCollapsibleSection(toggleStepPerformanceBtn, stepPerformanceContent);
        bindCollapsibleSection(toggleRecentEventsBtn, recentEventsContent);

        productDetailsToggles.forEach((toggleBtn) => {
            const toggleDetails = function() {
                const row = toggleBtn.closest('tr');
                const details = row ? row.querySelector('[data-product-details]') : null;
                if (!details) {
                    return;
                }

                const isOpen = details.classList.contains('is-open');
                details.classList.toggle('is-open', !isOpen);
                toggleBtn.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
                toggleBtn.setAttribute('aria-label', !isOpen ? 'Hide product quantity details' : 'Show product quantity details');
            };

            toggleBtn.addEventListener('click', toggleDetails);
            toggleBtn.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleDetails();
                }
            });
        });

        function applyOfferActivityFilters() {
            if (!offerActivityTableRows.length) {
                return;
            }

            const upsellValue = String(offerActivityUpsellFilter?.value || '').trim().toLowerCase();
            const downsellValue = String(offerActivityDownsellFilter?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            offerActivityTableRows.forEach((row) => {
                const rowUpsell = String(row.getAttribute('data-upsell-status') || '').trim().toLowerCase();
                const rowDownsell = String(row.getAttribute('data-downsell-status') || '').trim().toLowerCase();
                const visible = (upsellValue === '' || rowUpsell === upsellValue)
                    && (downsellValue === '' || rowDownsell === downsellValue);

                row.classList.toggle('analytics-table-row-hidden', !visible);
                if (visible) {
                    visibleCount += 1;
                }
            });

            if (offerActivityNoMatchRow) {
                offerActivityNoMatchRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        if (offerActivityUpsellFilter) {
            offerActivityUpsellFilter.addEventListener('change', applyOfferActivityFilters);
        }

        if (offerActivityDownsellFilter) {
            offerActivityDownsellFilter.addEventListener('change', applyOfferActivityFilters);
        }

        if (clearOfferActivityFiltersBtn) {
            clearOfferActivityFiltersBtn.addEventListener('click', function() {
                if (offerActivityUpsellFilter) {
                    offerActivityUpsellFilter.value = '';
                }
                if (offerActivityDownsellFilter) {
                    offerActivityDownsellFilter.value = '';
                }
                applyOfferActivityFilters();
            });
        }

        applyOfferActivityFilters();

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderOfferActivityRows(rows) {
            if (!offerActivityModalRows) {
                return;
            }

            if (!rows.length) {
                offerActivityModalRows.innerHTML = '<tr><td colspan="8">No customers matched this offer action for the current filters.</td></tr>';
                return;
            }

            offerActivityModalRows.innerHTML = rows.map((row) => {
                const selectedOffer = escapeHtml(row.selected_offer || 'N/A');
                const paidBeforeOffer = Number(row.paid_before_offer || 0).toFixed(2);
                const amount = Number(row.amount || 0).toFixed(2);
                const customer = escapeHtml(row.lead_name || row.lead_label || 'Anonymous visitor');
                const email = escapeHtml(row.lead_email || 'N/A');
                const step = escapeHtml(row.step_title || 'N/A');
                const payment = escapeHtml(row.payment_status || 'N/A');
                const occurredAt = escapeHtml(row.occurred_at_label || 'N/A');

                return `
                    <tr>
                        <td><strong>${customer}</strong></td>
                        <td>${email}</td>
                        <td>${selectedOffer}</td>
                        <td>${step}</td>
                        <td>PHP ${paidBeforeOffer}</td>
                        <td>PHP ${amount}</td>
                        <td>${payment}</td>
                        <td>${occurredAt}</td>
                    </tr>
                `;
            }).join('');
        }

        function closeOfferActivityModal() {
            if (!offerActivityModal) {
                return;
            }

            offerActivityModal.hidden = true;
            offerActivityModal.setAttribute('aria-hidden', 'true');

            if (lastOfferActivityTrigger) {
                lastOfferActivityTrigger.focus();
            }
        }

        function closeDeliveryUpdateModal() {
            if (!deliveryUpdateModal) {
                return;
            }

            deliveryUpdateModal.hidden = true;
            deliveryUpdateModal.setAttribute('aria-hidden', 'true');

            if (lastDeliveryUpdateTrigger) {
                lastDeliveryUpdateTrigger.focus();
            }
        }

        function openOfferActivityModal(groupKey, trigger) {
            const group = offerActivityGroups[groupKey];
            if (!group || !offerActivityModal || !offerActivityModalTitle || !offerActivityModalDescription) {
                return;
            }

            lastOfferActivityTrigger = trigger || null;
            offerActivityModalTitle.textContent = group.title;
            offerActivityModalDescription.textContent = group.description;
            renderOfferActivityRows(Array.isArray(group.rows) ? group.rows : []);
            offerActivityModal.hidden = false;
            offerActivityModal.setAttribute('aria-hidden', 'false');
        }

        function openDeliveryUpdateModal(button) {
            if (!deliveryUpdateModal || !button) {
                return;
            }

            const orderKey = String(button.getAttribute('data-order-key') || '').trim();
            const recipientEmail = String(button.getAttribute('data-recipient-email') || '').trim();
            const deliveryStatus = String(button.getAttribute('data-delivery-status') || 'processing').trim();
            const courierName = String(button.getAttribute('data-courier-name') || 'LBC').trim();
            const trackingUrl = String(button.getAttribute('data-tracking-url') || '').trim();
            const customMessage = String(button.getAttribute('data-custom-message') || '').trim();
            const customer = String(button.getAttribute('data-customer') || 'Anonymous visitor').trim();

            if (deliveryUpdateOrderKey) {
                deliveryUpdateOrderKey.value = orderKey;
            }
            if (deliveryUpdateRecipientEmail) {
                deliveryUpdateRecipientEmail.value = recipientEmail;
            }
            if (deliveryUpdateStatus) {
                deliveryUpdateStatus.value = deliveryStatus || 'processing';
            }
            if (deliveryUpdateCourier) {
                deliveryUpdateCourier.value = courierName || 'LBC';
            }
            if (deliveryUpdateTracking) {
                deliveryUpdateTracking.value = trackingUrl;
            }
            if (deliveryUpdateMessage) {
                deliveryUpdateMessage.value = customMessage;
            }
            if (deliveryUpdateModalCustomer) {
                deliveryUpdateModalCustomer.textContent = 'Customer: ' + (customer || 'Anonymous visitor');
            }
            if (deliveryUpdateModalEmail) {
                deliveryUpdateModalEmail.textContent = 'Email: ' + (recipientEmail || 'N/A');
            }

            lastDeliveryUpdateTrigger = button;
            deliveryUpdateModal.hidden = false;
            deliveryUpdateModal.setAttribute('aria-hidden', 'false');
        }

        offerActivityButtons.forEach((button) => {
            button.addEventListener('click', function() {
                openOfferActivityModal(button.getAttribute('data-offer-activity'), button);
            });
        });

        deliveryUpdateButtons.forEach((button) => {
            button.addEventListener('click', function() {
                openDeliveryUpdateModal(button);
            });
        });

        document.querySelectorAll('[data-offer-modal-close]').forEach((element) => {
            element.addEventListener('click', closeOfferActivityModal);
        });

        document.querySelectorAll('[data-delivery-modal-close]').forEach((element) => {
            element.addEventListener('click', closeDeliveryUpdateModal);
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && offerActivityModal && !offerActivityModal.hidden) {
                closeOfferActivityModal();
                return;
            }
            if (event.key === 'Escape' && deliveryUpdateModal && !deliveryUpdateModal.hidden) {
                closeDeliveryUpdateModal();
            }
        });

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
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'start',
                            labels: {
                                boxWidth: 22,
                                boxHeight: 12,
                                padding: 14,
                                color: '#6B7280',
                                font: {
                                    size: 13
                                }
                            }
                        },
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

