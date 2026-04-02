@extends('layouts.admin')

@section('title', 'Payment Tracking')

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

    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:20px;">
        <div class="card" style="margin:0;">
            <h3 style="margin-bottom:8px;">Workspace Status</h3>
            <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">{{ ucfirst($tenant->status ?? 'active') }}</p>
        </div>
        <div class="card" style="margin:0;">
            <h3 style="margin-bottom:8px;">Billing State</h3>
            <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">{{ $billingStateLabel }}</p>
        </div>
        <div class="card" style="margin:0;">
            <h3 style="margin-bottom:8px;">Current Plan</h3>
            <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">{{ $tenant->subscription_plan ?? 'N/A' }}</p>
        </div>
        <div class="card" style="margin:0;">
            <h3 style="margin-bottom:8px;">Grace Ends</h3>
            <p style="margin:0;font-size:18px;font-weight:700;color:var(--theme-primary,#240E35);">{{ optional($tenant->billing_grace_ends_at)->format('Y-m-d H:i') ?? 'N/A' }}</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
        <div class="card">
            <h3>Record Payment</h3>
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 12px;">
                    <label for="lead_id" style="display: block; margin-bottom: 6px;">Lead</label>
                    <select name="lead_id" id="lead_id" style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        <option value="">No lead linked</option>
                        @foreach($leadOptions as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="amount" style="display: block; margin-bottom: 6px;">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" required
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="status" style="display: block; margin-bottom: 6px;">Status</label>
                    <select name="status" id="status" required style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="payment_date" style="display: block; margin-bottom: 6px;">Date</label>
                    <input type="date" name="payment_date" id="payment_date" required
                        value="{{ now()->toDateString() }}"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="provider" style="display: block; margin-bottom: 6px;">Provider</label>
                    <input type="text" name="provider" id="provider"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        placeholder="e.g. paymongo">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="payment_method" style="display: block; margin-bottom: 6px;">Payment Method</label>
                    <input type="text" name="payment_method" id="payment_method"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        placeholder="e.g. gcash">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="provider_reference" style="display: block; margin-bottom: 6px;">Reference</label>
                    <input type="text" name="provider_reference" id="provider_reference"
                        style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                        placeholder="Invoice or provider reference">
                </div>

                <button type="submit"
                    style="padding: 10px 18px; background-color: var(--theme-primary, #240E35); color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Save Payment
                </button>
            </form>
        </div>

        <div class="card">
            <h3>Recent Payments</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Lead</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Provider</th>
                        <th>Method</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td>{{ $payment->lead->name ?? 'N/A' }}</td>
                            <td>PHP {{ number_format($payment->amount, 2) }}</td>
                            <td>{{ ucfirst($payment->status) }}</td>
                            <td>{{ $payment->provider ?? 'N/A' }}</td>
                            <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                            <td>{{ $payment->provider_reference ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No payment records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top: 14px;">
                {{ $payments->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection
