@extends('layouts.admin')

@section('title', 'Payment Tracking')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/extracted/payments-index-style1.css') }}">
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
@endphp

@section('content')
    <div class="payment-page">
        <div class="top-header">
            <h1>Payment Tracking</h1>
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

        <div class="payment-overview">
            <div class="card payment-stat-card">
                <h3>Workspace Status</h3>
                <p class="payment-stat-card__value">{{ ucfirst($tenant->status ?? 'active') }}</p>
            </div>
            <div class="card payment-stat-card">
                <h3>Billing State</h3>
                <p class="payment-stat-card__value">{{ $billingStateLabel }}</p>
            </div>
            <div class="card payment-stat-card">
                <h3>Current Plan</h3>
                <p class="payment-stat-card__value">{{ $tenant->subscription_plan ?? $emptyDash }}</p>
            </div>
            <div class="card payment-stat-card">
                <h3>Grace Ends</h3>
                <p class="payment-stat-card__value payment-stat-card__value--compact">{{ optional($tenant->billing_grace_ends_at)->format('Y-m-d H:i') ?? $emptyDash }}</p>
            </div>
        </div>

        <div class="payment-summary-grid" style="margin: 20px 0;">
            <div class="card payment-summary-card">
                <h3>Trial Days Remaining</h3>
                <p class="payment-summary-card__value">{{ number_format((int) $tenant->trialDaysRemaining()) }}</p>
            </div>
            <div class="card payment-summary-card">
                <h3>Grace Days Remaining</h3>
                <p class="payment-summary-card__value">{{ number_format((int) $tenant->billingGraceDaysRemaining()) }}</p>
            </div>
            <div class="card payment-summary-card">
                <h3>Active Since</h3>
                <p class="payment-summary-card__value payment-summary-card__value--compact">{{ optional($tenant->subscription_activated_at)->format('Y-m-d H:i') ?? $emptyDash }}</p>
            </div>
            <div class="card payment-summary-card">
                <h3>Shared Automation Access</h3>
                <p class="payment-summary-card__value">{{ data_get($planUsage, 'automation_enabled') ? 'Included' : 'Not Included' }}</p>
            </div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <h3>Plan Limits</h3>
            <div class="app-grid app-grid--4" style="gap:12px;">
                @foreach(['users' => 'Users', 'funnels' => 'Funnels', 'workflows' => 'Workflows', 'leads' => 'Leads', 'messages' => 'Messages'] as $key => $label)
                    <div style="padding:14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:12px;background:var(--theme-surface-softer, #F7F7FB);">
                        <div style="font-size:12px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--theme-muted, #6B7280);">{{ $label }}</div>
                        <div style="margin-top:8px;font-size:22px;font-weight:800;color:var(--theme-primary, #240E35);">
                            {{ number_format((int) data_get($planUsage, $key . '.used', 0)) }}
                            /
                            {{ data_get($planUsage, $key . '.is_unlimited') ? 'Unlimited' : number_format((int) data_get($planUsage, $key . '.limit', 0)) }}
                        </div>
                        <div style="margin-top:6px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">
                            Remaining:
                            {{ data_get($planUsage, $key . '.is_unlimited') ? 'Unlimited' : number_format((int) data_get($planUsage, $key . '.remaining', 0)) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="payment-toolbar">
            <button type="button" data-record-payment-open class="payment-primary-btn">
                Record Payment
            </button>
        </div>

        <div class="payment-summary-grid" style="margin-bottom: 20px;">
            <div class="card payment-summary-card">
                <h3>Receipt Review Queue</h3>
                <p class="payment-summary-card__value">{{ number_format((int) ($receiptStats['pending'] ?? 0)) }}</p>
            </div>
            <div class="card payment-summary-card">
                <h3>Auto Approved Receipts</h3>
                <p class="payment-summary-card__value">{{ number_format((int) ($receiptStats['auto_approved'] ?? 0)) }}</p>
            </div>
            <div class="card payment-summary-card">
                <h3>Approved Receipts</h3>
                <p class="payment-summary-card__value">{{ number_format((int) ($receiptStats['approved'] ?? 0)) }}</p>
            </div>
            <div class="card payment-summary-card">
                <h3>Payable Commissions</h3>
                <p class="payment-summary-card__value">PHP {{ number_format((float) ($commissionSummary['payable_total'] ?? 0), 2) }}</p>
            </div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <h3>Upload Payment Receipt</h3>
            <p style="margin: 0 0 14px; color: var(--theme-muted, #6B7280);">
                Upload e-receipts or proof of payment. Matching amount, provider, reference, tenant, and payment type can auto-approve the receipt.
            </p>
            <form action="{{ route('payments.receipts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="app-form-grid app-form-grid--3" style="gap:12px;">
                    <div>
                        <label for="receipt_payment_id" style="display:block;margin-bottom:6px;">Payment</label>
                        <select id="receipt_payment_id" name="payment_id" required style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                            <option value="">Select payment</option>
                            @foreach($receiptOptions as $option)
                                <option value="{{ $option->id }}">
                                    #{{ $option->id }} | {{ ucfirst(str_replace('_', ' ', $option->payment_type)) }} | PHP {{ number_format((float) $option->amount, 2) }} | {{ optional($option->payment_date)->format('Y-m-d') ?? $emptyDash }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="receipt_amount" style="display:block;margin-bottom:6px;">Receipt Amount</label>
                        <input type="number" step="0.01" min="0.01" name="receipt_amount" id="receipt_amount"
                            value="{{ old('receipt_amount') }}"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                    </div>
                    <div>
                        <label for="receipt_date" style="display:block;margin-bottom:6px;">Receipt Date</label>
                        <input type="date" name="receipt_date" id="receipt_date"
                            value="{{ old('receipt_date', now()->toDateString()) }}"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                    </div>
                    <div>
                        <label for="receipt_provider" style="display:block;margin-bottom:6px;">Provider</label>
                        <input type="text" name="provider" id="receipt_provider" value="{{ old('provider') }}"
                            placeholder="e.g. paymongo / gcash"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                    </div>
                    <div>
                        <label for="reference_number" style="display:block;margin-bottom:6px;">Reference Number</label>
                        <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number') }}"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                    </div>
                    <div>
                        <label for="receipt_file" style="display:block;margin-bottom:6px;">Receipt File</label>
                        <input type="file" name="receipt_file" id="receipt_file" required
                            accept=".jpg,.jpeg,.png,.pdf"
                            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;background:#fff;">
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <label for="receipt_notes" style="display:block;margin-bottom:6px;">Notes</label>
                    <textarea name="notes" id="receipt_notes" rows="3"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">{{ old('notes') }}</textarea>
                </div>
                <div style="margin-top: 14px;">
                    <button type="submit"
                        style="padding: 10px 18px; background-color: var(--theme-primary, #240E35); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight:700;">
                        Upload Receipt
                    </button>
                </div>
            </form>
        </div>

    <div
        data-record-payment-modal
        class="payment-modal-shell"
        aria-hidden="{{ $errors->any() ? 'false' : 'true' }}"
        style="display:{{ $errors->any() ? 'flex' : 'none' }};">
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="recordPaymentTitle"
            class="payment-modal-card">
            <div class="payment-modal-header">
                <div>
                    <h3 id="recordPaymentTitle" style="margin:0 0 6px;">Record Payment</h3>
                    <p style="margin:0;color:var(--theme-muted,#6B7280);">Save either a workspace subscription payment or a funnel sale.</p>
                </div>
                <button
                    type="button"
                    data-record-payment-close
                    aria-label="Close payment form"
                    class="payment-modal-close">
                    &times;
                </button>
            </div>

            <form action="{{ route('payments.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 12px;">
                    <label for="payment_type" style="display: block; margin-bottom: 6px;">Payment Type</label>
                    <select name="payment_type" id="payment_type" required data-payment-type-select style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        @foreach(\App\Models\Payment::TYPES as $type => $label)
                            <option value="{{ $type }}" {{ old('payment_type', \App\Models\Payment::TYPE_PLATFORM_SUBSCRIPTION) === $type ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 12px;" data-funnel-fields hidden>
                    <label for="funnel_id" style="display: block; margin-bottom: 6px;">Funnel</label>
                    <select name="funnel_id" id="funnel_id" data-funnel-select style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        <option value="">Select funnel</option>
                        @foreach($funnelOptions as $funnel)
                            @php
                                $funnelSteps = $funnel->steps->map(function ($step) {
                                    return [
                                        'id' => $step->id,
                                        'title' => $step->title,
                                        'type' => ucfirst(str_replace('_', ' ', $step->type)),
                                    ];
                                })->values();
                            @endphp
                            <option value="{{ $funnel->id }}" data-steps='@json($funnelSteps)' {{ (string) old('funnel_id') === (string) $funnel->id ? 'selected' : '' }}>
                                {{ $funnel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 12px;" data-funnel-fields hidden>
                    <label for="funnel_step_id" style="display: block; margin-bottom: 6px;">Funnel Step</label>
                    <select name="funnel_step_id" id="funnel_step_id" data-step-select style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        <option value="">Select funnel step</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="lead_id" style="display: block; margin-bottom: 6px;">Lead</label>
                    <select name="lead_id" id="lead_id" style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        <option value="">No lead linked</option>
                        @foreach($leadOptions as $lead)
                            <option value="{{ $lead->id }}" {{ (string) old('lead_id') === (string) $lead->id ? 'selected' : '' }}>{{ $lead->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="amount" style="display: block; margin-bottom: 6px;">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" required
                        value="{{ old('amount') }}"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="status" style="display: block; margin-bottom: 6px;">Status</label>
                    <select name="status" id="status" required style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        @foreach(\App\Models\Payment::STATUSES as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ old('status', 'pending') === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="payment_date" style="display: block; margin-bottom: 6px;">Date</label>
                    <input type="date" name="payment_date" id="payment_date" required
                        value="{{ old('payment_date', now()->toDateString()) }}"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="provider" style="display: block; margin-bottom: 6px;">Provider</label>
                    <input type="text" name="provider" id="provider"
                        value="{{ old('provider') }}"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        placeholder="e.g. paymongo">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="payment_method" style="display: block; margin-bottom: 6px;">Payment Method</label>
                    <input type="text" name="payment_method" id="payment_method"
                        value="{{ old('payment_method') }}"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        placeholder="e.g. gcash">
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="provider_reference" style="display: block; margin-bottom: 6px;">Reference</label>
                    <input type="text" name="provider_reference" id="provider_reference"
                        value="{{ old('provider_reference') }}"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        placeholder="Invoice or provider reference">
                </div>

                <div class="payment-form-actions">
                    <button
                        type="button"
                        data-record-payment-close
                        style="padding:10px 18px;background:#F3F4F6;color:#111827;border:none;border-radius:10px;cursor:pointer;font-weight:600;">
                        Cancel
                    </button>
                    <button type="submit"
                        style="padding: 10px 18px; background-color: var(--theme-primary, #240E35); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight:700;">
                        Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

        <div class="payment-sections">
            <div class="card">
                <div class="payment-section-header">
                    <h3 class="payment-section-title">Platform Subscriptions</h3>
                    <div class="payment-section-actions">
                        <button
                            type="button"
                            data-section-toggle
                            data-target="platform-subscriptions-content"
                            aria-expanded="false"
                            class="payment-toggle-btn ui-show-hide-toggle">
                            Show
                        </button>
                    </div>
                </div>

                <div id="platform-subscriptions-content" hidden class="payment-section-content">
                    <div class="payment-summary-grid">
                        <div class="card payment-summary-card" style="background:#fff;">
                            <h3>Paid Total</h3>
                            <p class="payment-summary-card__value">PHP {{ number_format((float) $platformStats['paid_total'], 2) }}</p>
                        </div>
                        <div class="card payment-summary-card" style="background:#fff;">
                            <h3>Pending Total</h3>
                            <p class="payment-summary-card__value">PHP {{ number_format((float) $platformStats['pending_total'], 2) }}</p>
                        </div>
                        <div class="card payment-summary-card" style="background:#fff;">
                            <h3>Failed Total</h3>
                            <p class="payment-summary-card__value">PHP {{ number_format((float) $platformStats['failed_total'], 2) }}</p>
                        </div>
                        <div class="card payment-summary-card" style="background:#fff;">
                            <h3>Outstanding Invoices</h3>
                            <p class="payment-summary-card__value">
                                {{ (int) $platformStats['outstanding_count'] }} (PHP {{ number_format((float) $platformStats['outstanding_amount'], 2) }})
                            </p>
                        </div>
                    </div>

                    <div class="team-table-scroll">
                        <table class="sa-table team-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Provider</th>
                                <th>Method</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($platformSubscriptions as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date?->format('Y-m-d') ?? $emptyDash }}</td>
                                    <td>PHP {{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ \App\Models\Payment::STATUSES[$payment->status] ?? ucfirst($payment->status) }}</td>
                                    <td>{{ $payment->provider ?? $emptyDash }}</td>
                                    <td>{{ $payment->payment_method ?? $emptyDash }}</td>
                                    <td>{{ $payment->provider_reference ?? $emptyDash }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No platform subscription records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 14px;">
                        {{ $platformSubscriptions->appends(['sales_page' => request('sales_page')])->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="payment-section-header">
                    <h3 class="payment-section-title">Funnel Sales</h3>
                    <button
                        type="button"
                        data-section-toggle
                        data-target="funnel-sales-content"
                        aria-expanded="false"
                        class="payment-toggle-btn ui-show-hide-toggle">
                        Show
                    </button>
                </div>

                <div id="funnel-sales-content" hidden class="payment-section-content">
                    <div class="payment-summary-grid">
                        <div class="card payment-summary-card" style="background:#fff;">
                            <h3>Paid Total</h3>
                            <p class="payment-summary-card__value">PHP {{ number_format((float) $funnelStats['paid_total'], 2) }}</p>
                        </div>
                        <div class="card payment-summary-card" style="background:#fff;">
                            <h3>Pending Total</h3>
                            <p class="payment-summary-card__value">PHP {{ number_format((float) $funnelStats['pending_total'], 2) }}</p>
                        </div>
                        <div class="card payment-summary-card" style="background:#fff;">
                            <h3>Failed Total</h3>
                            <p class="payment-summary-card__value">PHP {{ number_format((float) $funnelStats['failed_total'], 2) }}</p>
                        </div>
                        <div class="card payment-summary-card" style="background:#fff;">
                            <h3>Outstanding Invoices</h3>
                            <p class="payment-summary-card__value">
                                {{ (int) $funnelStats['outstanding_count'] }} (PHP {{ number_format((float) $funnelStats['outstanding_amount'], 2) }})
                            </p>
                        </div>
                    </div>

                    <div class="team-table-scroll">
                        <table class="sa-table team-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Funnel</th>
                                <th>Step</th>
                                <th>Lead</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Provider</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($funnelSales as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date?->format('Y-m-d') ?? $emptyDash }}</td>
                                    <td>{{ $payment->funnel->name ?? $emptyDash }}</td>
                                    <td>{{ $payment->step->title ?? $emptyDash }}</td>
                                    <td>{{ $payment->lead->name ?? $emptyDash }}</td>
                                    <td>PHP {{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ \App\Models\Payment::STATUSES[$payment->status] ?? ucfirst($payment->status) }}</td>
                                    <td>{{ $payment->provider ?? $emptyDash }}</td>
                                    <td>{{ $payment->provider_reference ?? $emptyDash }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">No funnel sales records yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 14px;">
                        {{ $funnelSales->appends(['subscriptions_page' => request('subscriptions_page')])->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="payment-section-header">
                <h3 class="payment-section-title">Receipt Review</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="{{ route('payments.index') }}"
                        style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:10px;border:1px solid var(--theme-border, #E6E1EF);background:#fff;color:#0F172A;text-decoration:none;font-weight:800;">
                        All receipts
                    </a>
                    <a href="{{ route('payments.index', ['receipts_filter' => 'manual_pending']) }}"
                        style="display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:10px;border:1px solid var(--theme-border, #E6E1EF);background:#0F172A;color:#fff;text-decoration:none;font-weight:900;">
                        Manual pending
                        <span style="margin-left:8px;padding:3px 10px;border-radius:999px;background:#fff;color:#0F172A;font-weight:900;">
                            {{ number_format((int) ($receiptStats['manual_pending'] ?? 0)) }}
                        </span>
                    </a>
                </div>
            </div>
            <div class="team-table-scroll">
                <table class="sa-table team-table">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Provider</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Automation</th>
                            <th>File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td>#{{ $receipt->id }}</td>
                                <td>#{{ $receipt->payment_id }} / {{ ucfirst(str_replace('_', ' ', $receipt->payment->payment_type ?? 'payment')) }}</td>
                                <td>PHP {{ number_format((float) ($receipt->receipt_amount ?? 0), 2) }}</td>
                                <td>{{ $receipt->provider ?? $emptyDash }}</td>
                                <td>{{ $receipt->reference_number ?? $emptyDash }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $receipt->status)) }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $receipt->automation_status)) }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $receipt->receipt_path) }}" target="_blank" rel="noopener">
                                        View
                                    </a>
                                </td>
                                <td>
                                    @if(auth()->user()->hasRole('finance') || auth()->user()->hasRole('account-owner'))
                                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                            <form action="{{ route('payments.receipts.review', $receipt) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="decision" value="approve">
                                                <button type="submit" style="padding:8px 10px;border:none;border-radius:8px;background:#166534;color:#fff;cursor:pointer;font-weight:700;">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('payments.receipts.review', $receipt) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="decision" value="reject">
                                                <button type="submit" style="padding:8px 10px;border:none;border-radius:8px;background:#B91C1C;color:#fff;cursor:pointer;font-weight:700;">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        {{ $emptyDash }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">No receipts uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 14px;">
                {{ $receipts->appends(['subscriptions_page' => request('subscriptions_page'), 'sales_page' => request('sales_page')])->links('pagination::bootstrap-4') }}
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h3>Recent Finance Audit Events</h3>
            <div class="team-table-scroll">
                <table class="sa-table team-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Actor</th>
                            <th>Event</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAuditLogs as $log)
                            <tr>
                                <td>{{ optional($log->occurred_at)->format('Y-m-d H:i') ?? $emptyDash }}</td>
                                <td>{{ $log->actor->name ?? $emptyDash }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $log->event_type)) }}</td>
                                <td>{{ $log->message ?? $emptyDash }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No finance audit events yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const modal = document.querySelector('[data-record-payment-modal]');
            const openButton = document.querySelector('[data-record-payment-open]');
            const closeButtons = document.querySelectorAll('[data-record-payment-close]');
            const sectionToggles = document.querySelectorAll('[data-section-toggle]');
            const typeSelect = document.querySelector('[data-payment-type-select]');
            const funnelContainers = document.querySelectorAll('[data-funnel-fields]');
            const funnelSelect = document.querySelector('[data-funnel-select]');
            const stepSelect = document.querySelector('[data-step-select]');
            const oldStepId = @json((string) old('funnel_step_id', ''));
            const funnelType = @json(\App\Models\Payment::TYPE_FUNNEL_CHECKOUT);

            if (!modal || !openButton || !typeSelect || !funnelSelect || !stepSelect) {
                return;
            }

            function openModal() {
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }

            function selectedFunnelOption() {
                return funnelSelect.options[funnelSelect.selectedIndex] || null;
            }

            function fillStepOptions() {
                const option = selectedFunnelOption();
                const raw = option ? option.getAttribute('data-steps') : '[]';
                let steps = [];

                try {
                    steps = JSON.parse(raw || '[]');
                } catch (error) {
                    steps = [];
                }

                stepSelect.innerHTML = '<option value="">Select funnel step</option>';

                steps.forEach((step) => {
                    const nextOption = document.createElement('option');
                    nextOption.value = String(step.id || '');
                    nextOption.textContent = step.title + (step.type ? ' (' + step.type + ')' : '');
                    if (oldStepId !== '' && oldStepId === String(step.id || '')) {
                        nextOption.selected = true;
                    }
                    stepSelect.appendChild(nextOption);
                });
            }

            function syncTypeFields() {
                const isFunnelSale = typeSelect.value === funnelType;

                funnelContainers.forEach((node) => {
                    node.hidden = !isFunnelSale;
                });

                funnelSelect.required = isFunnelSale;
                stepSelect.required = isFunnelSale;

                if (isFunnelSale) {
                    fillStepOptions();
                    return;
                }

                funnelSelect.value = '';
                stepSelect.innerHTML = '<option value="">Select funnel step</option>';
            }

            openButton.addEventListener('click', openModal);

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                    closeModal();
                }
            });

            sectionToggles.forEach((button) => {
                button.addEventListener('click', function () {
                    const targetId = button.getAttribute('data-target');
                    const target = targetId ? document.getElementById(targetId) : null;
                    if (!target) {
                        return;
                    }

                    const isHidden = target.hasAttribute('hidden');
                    if (isHidden) {
                        target.removeAttribute('hidden');
                        button.textContent = 'Hide';
                        button.setAttribute('aria-expanded', 'true');
                        return;
                    }

                    target.setAttribute('hidden', 'hidden');
                    button.textContent = 'Show';
                    button.setAttribute('aria-expanded', 'false');
                });
            });

            funnelSelect.addEventListener('change', function () {
                stepSelect.innerHTML = '<option value="">Select funnel step</option>';
                fillStepOptions();
            });

            typeSelect.addEventListener('change', syncTypeFields);
            fillStepOptions();
            syncTypeFields();
        })();
    </script>
@endsection
