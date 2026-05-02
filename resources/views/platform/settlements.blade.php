@extends('layouts.admin')

@section('title', 'Platform Settlements')

@section('content')
    <div class="top-header">
        <h1>Platform Settlements</h1>
        <p style="margin:6px 0 0;color:var(--theme-muted, #6B7280);font-weight:600;">
            Track confirmed funnel earnings per workspace and record payouts to approved destinations.
        </p>
    </div>

    @php
        $m = is_array($metrics ?? null) ? $metrics : [];
        $money = fn ($value) => '₱ ' . number_format((float) $value, 2);
    @endphp

    <div class="app-grid app-grid--4" style="gap:12px;margin: 14px 0 16px;">
        <div class="card">
            <h3 style="margin:0;">Paid funnel sales</h3>
            <p style="margin:10px 0 0;font-size:20px;font-weight:900;color:#0F172A;">
                {{ $money(data_get($m, 'paid_funnel_total', 0)) }}
            </p>
            <p style="margin:6px 0 0;color:var(--theme-muted, #6B7280);font-weight:700;font-size:12px;">
                Gross collected for tenants (lifetime)
            </p>
        </div>
        <div class="card">
            <h3 style="margin:0;">Paid subscriptions</h3>
            <p style="margin:10px 0 0;font-size:20px;font-weight:900;color:#0F172A;">
                {{ $money(data_get($m, 'paid_subscription_total', 0)) }}
            </p>
            <p style="margin:6px 0 0;color:var(--theme-muted, #6B7280);font-weight:700;font-size:12px;">
                Platform revenue (lifetime)
            </p>
        </div>
        <div class="card">
            <h3 style="margin:0;">Unpaid liability</h3>
            <p style="margin:10px 0 0;font-size:20px;font-weight:900;color:#0F172A;">
                {{ $money(data_get($m, 'unpaid_liability_total', 0)) }}
            </p>
            <p style="margin:6px 0 0;color:var(--theme-muted, #6B7280);font-weight:700;font-size:12px;">
                Paid funnel sales not yet paid out
            </p>
        </div>
        <div class="card">
            <h3 style="margin:0;">Pending payments</h3>
            <p style="margin:10px 0 0;font-size:20px;font-weight:900;color:#0F172A;">
                {{ $money(data_get($m, 'pending_total', 0)) }}
            </p>
            <p style="margin:6px 0 0;color:var(--theme-muted, #6B7280);font-weight:700;font-size:12px;">
                Checkouts awaiting confirmation
            </p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 16px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <h3 style="margin:0;">Month-to-date snapshot</h3>
                <p style="margin:6px 0 0;color:var(--theme-muted, #6B7280);font-weight:700;">
                    {{ data_get($m, 'month_label', '') }}
                </p>
            </div>
            <div style="display:flex;gap:14px;flex-wrap:wrap;">
                <div style="min-width:220px;">
                    <div style="color:var(--theme-muted, #6B7280);font-weight:800;font-size:12px;">Collected (paid)</div>
                    <div style="font-weight:900;font-size:18px;color:#0F172A;">{{ $money(data_get($m, 'collected_this_month_total', 0)) }}</div>
                </div>
                <div style="min-width:220px;">
                    <div style="color:var(--theme-muted, #6B7280);font-weight:800;font-size:12px;">Payouts recorded</div>
                    <div style="font-weight:900;font-size:18px;color:#0F172A;">{{ $money(data_get($m, 'payouts_this_month_total', 0)) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 16px;">
        <form method="GET" action="{{ route('platform.settlements.index') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <label for="status" style="font-weight:800;color:#0F172A;">View</label>
            <select id="status" name="status" style="padding:10px 12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;">
                @foreach(['unpaid' => 'Unpaid earnings', 'paid' => 'Recent paid payouts', 'all' => 'All'] as $value => $label)
                    <option value="{{ $value }}" {{ $statusFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" style="padding:10px 14px;border:none;border-radius:10px;background:var(--theme-primary, #240E35);color:#fff;font-weight:800;cursor:pointer;">
                Apply
            </button>
        </form>
    </div>

    <div class="app-grid app-grid--2" style="gap:16px;">
        <div class="card">
            <h3 style="margin-top:0;">Unpaid confirmed earnings</h3>
            <p style="margin-top:6px;color:var(--theme-muted, #6B7280);font-weight:600;">
                Only includes funnel sales marked <strong>paid</strong> that have not been assigned to a payout.
            </p>

            <div style="overflow:auto;">
                <table style="width:100%;border-collapse:separate;border-spacing:0 10px;margin-top:12px;">
                    <thead>
                        <tr style="text-align:left;color:var(--theme-muted, #6B7280);font-size:12px;">
                            <th style="padding:0 10px;">Workspace</th>
                            <th style="padding:0 10px;">Unpaid count</th>
                            <th style="padding:0 10px;">Unpaid total</th>
                            <th style="padding:0 10px;">Destination</th>
                            <th style="padding:0 10px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            @php
                                $totals = $unpaidTotals[$tenant->id] ?? null;
                                $count = (int) data_get($totals, 'paid_count', 0);
                                $total = (float) data_get($totals, 'paid_total', 0);
                                $payoutAccount = $tenant->defaultPayoutAccount;
                                $destination = $payoutAccount?->masked_destination ?? '-';
                                $destinationType = $payoutAccount?->destination_type ?? null;
                                $approved = $payoutAccount?->isApproved();
                            @endphp
                            <tr style="background:var(--theme-surface, #fff);border:1px solid var(--theme-border, #E6E1EF);">
                                <td style="padding:12px 10px;font-weight:800;color:#0F172A;">
                                    {{ $tenant->company_name ?? ('Tenant #' . $tenant->id) }}
                                </td>
                                <td style="padding:12px 10px;font-weight:800;color:#0F172A;">{{ number_format($count) }}</td>
                                <td style="padding:12px 10px;font-weight:900;color:#0F172A;">₱ {{ number_format($total, 2) }}</td>
                                <td style="padding:12px 10px;">
                                    <div style="font-weight:800;color:#0F172A;">
                                        {{ $destination }}
                                    </div>
                                    <div style="color:var(--theme-muted, #6B7280);font-weight:700;font-size:12px;margin-top:4px;">
                                        {{ $destinationType ? ucwords(str_replace('_', ' ', $destinationType)) : 'No destination' }}
                                        @if($payoutAccount)
                                            · {{ $payoutAccount->reviewStatusLabel() }}
                                        @endif
                                    </div>
                                </td>
                                <td style="padding:12px 10px;">
                                    @if($count < 1)
                                        <span style="color:var(--theme-muted, #6B7280);font-weight:700;">No unpaid</span>
                                    @elseif(! $approved)
                                        <span style="color:#B91C1C;font-weight:900;">Destination not approved</span>
                                    @else
                                        <form method="POST" action="{{ route('platform.settlements.store', ['tenant' => $tenant->id]) }}" style="display:flex;gap:8px;flex-wrap:wrap;">
                                            @csrf
                                            <input type="text" name="payment_reference" placeholder="Transfer ref (optional)"
                                                style="padding:10px 12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;min-width:190px;">
                                            <button type="submit"
                                                style="padding:10px 14px;border:none;border-radius:10px;background:#166534;color:#fff;font-weight:900;cursor:pointer;">
                                                Mark paid out
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="padding:12px 10px;color:var(--theme-muted, #6B7280);font-weight:700;">No tenants found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 10px;">
                {{ $tenants->links() }}
            </div>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Recent payouts</h3>
            <p style="margin-top:6px;color:var(--theme-muted, #6B7280);font-weight:600;">
                These are payout records created by the platform finance admin.
            </p>

            <div style="overflow:auto;">
                <table style="width:100%;border-collapse:separate;border-spacing:0 10px;margin-top:12px;">
                    <thead>
                        <tr style="text-align:left;color:var(--theme-muted, #6B7280);font-size:12px;">
                            <th style="padding:0 10px;">Workspace</th>
                            <th style="padding:0 10px;">Amount</th>
                            <th style="padding:0 10px;">Destination</th>
                            <th style="padding:0 10px;">Ref</th>
                            <th style="padding:0 10px;">Paid at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayouts as $payout)
                            <tr style="background:var(--theme-surface, #fff);border:1px solid var(--theme-border, #E6E1EF);">
                                <td style="padding:12px 10px;font-weight:800;color:#0F172A;">
                                    {{ optional($payout->tenant)->company_name ?? ('Tenant #' . $payout->tenant_id) }}
                                </td>
                                <td style="padding:12px 10px;font-weight:900;color:#0F172A;">₱ {{ number_format((float) $payout->amount, 2) }}</td>
                                <td style="padding:12px 10px;">
                                    <div style="font-weight:800;color:#0F172A;">{{ $payout->masked_destination ?? '-' }}</div>
                                    <div style="color:var(--theme-muted, #6B7280);font-weight:700;font-size:12px;margin-top:4px;">
                                        {{ $payout->destination_type ? ucwords(str_replace('_', ' ', $payout->destination_type)) : '-' }}
                                        · {{ strtoupper($payout->status) }}
                                    </div>
                                </td>
                                <td style="padding:12px 10px;font-weight:800;color:#0F172A;">{{ $payout->payment_reference ?? '-' }}</td>
                                <td style="padding:12px 10px;color:var(--theme-muted, #6B7280);font-weight:800;">
                                    {{ optional($payout->paid_at)->format('Y-m-d H:i') ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="padding:12px 10px;color:var(--theme-muted, #6B7280);font-weight:700;">No payouts recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 10px;">
                {{ $recentPayouts->links() }}
            </div>
        </div>
    </div>
@endsection

