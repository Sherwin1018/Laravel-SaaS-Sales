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

    <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
        <button
            type="button"
            data-record-payment-open
            style="padding:12px 18px;background:var(--theme-primary,#240E35);color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
            Record Payment
        </button>
    </div>

    <div
        data-record-payment-modal
        aria-hidden="{{ $errors->any() ? 'false' : 'true' }}"
        style="position:fixed;inset:0;background:rgba(15,23,42,.55);display:{{ $errors->any() ? 'flex' : 'none' }};align-items:center;justify-content:center;padding:24px;z-index:9999;">
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="recordPaymentTitle"
            style="width:min(100%,720px);max-height:90vh;overflow:auto;background:#fff;border-radius:20px;box-shadow:0 24px 80px rgba(15,23,42,.2);padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:18px;">
                <div>
                    <h3 id="recordPaymentTitle" style="margin:0 0 6px;">Record Payment</h3>
                    <p style="margin:0;color:var(--theme-muted,#6B7280);">Save either a workspace subscription payment or a funnel sale.</p>
                </div>
                <button
                    type="button"
                    data-record-payment-close
                    aria-label="Close payment form"
                    style="width:40px;height:40px;border:none;border-radius:999px;background:#F3F4F6;color:#111827;font-size:20px;cursor:pointer;">
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

                <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
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

    <div style="display:grid;gap:20px;">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
                <h3 style="margin:0;">Platform Subscriptions</h3>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <div style="padding:10px 14px;border-radius:12px;background:#F7F4FB;">
                        <div style="font-size:12px;color:var(--theme-muted,#6B7280);font-weight:700;">Workspace Plan</div>
                        <div style="font-size:16px;color:var(--theme-primary,#240E35);font-weight:800;">{{ $tenant->subscription_plan ?? 'N/A' }}</div>
                    </div>
                    <button
                        type="button"
                        data-section-toggle
                        data-target="platform-subscriptions-content"
                        aria-expanded="false"
                        style="padding:10px 16px;background:var(--theme-primary,#240E35);color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
                        Show
                    </button>
                </div>
            </div>

            <div id="platform-subscriptions-content" hidden style="margin-top:14px;">
                <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:18px;">
                    <div class="card" style="margin:0;background:#fff;">
                        <h3 style="margin-bottom:8px;">Paid Total</h3>
                        <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">PHP {{ number_format((float) $platformStats['paid_total'], 2) }}</p>
                    </div>
                    <div class="card" style="margin:0;background:#fff;">
                        <h3 style="margin-bottom:8px;">Pending Total</h3>
                        <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">PHP {{ number_format((float) $platformStats['pending_total'], 2) }}</p>
                    </div>
                    <div class="card" style="margin:0;background:#fff;">
                        <h3 style="margin-bottom:8px;">Failed Total</h3>
                        <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">PHP {{ number_format((float) $platformStats['failed_total'], 2) }}</p>
                    </div>
                    <div class="card" style="margin:0;background:#fff;">
                        <h3 style="margin-bottom:8px;">Outstanding Invoices</h3>
                        <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">
                            {{ (int) $platformStats['outstanding_count'] }} (PHP {{ number_format((float) $platformStats['outstanding_amount'], 2) }})
                        </p>
                    </div>
                </div>

                <div class="sa-table-scroll">
                    <table class="sa-table">
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
                                    <td>{{ $payment->payment_date?->format('Y-m-d') ?? 'N/A' }}</td>
                                    <td>PHP {{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ \App\Models\Payment::STATUSES[$payment->status] ?? ucfirst($payment->status) }}</td>
                                    <td>{{ $payment->provider ?? 'N/A' }}</td>
                                    <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                    <td>{{ $payment->provider_reference ?? 'N/A' }}</td>
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
            <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
                <h3 style="margin:0;">Funnel Sales</h3>
                <button
                    type="button"
                    data-section-toggle
                    data-target="funnel-sales-content"
                    aria-expanded="false"
                    style="padding:10px 16px;background:var(--theme-primary,#240E35);color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
                    Show
                </button>
            </div>

            <div id="funnel-sales-content" hidden style="margin-top:14px;">
                <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:18px;">
                    <div class="card" style="margin:0;background:#fff;">
                        <h3 style="margin-bottom:8px;">Paid Total</h3>
                        <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">PHP {{ number_format((float) $funnelStats['paid_total'], 2) }}</p>
                    </div>
                    <div class="card" style="margin:0;background:#fff;">
                        <h3 style="margin-bottom:8px;">Pending Total</h3>
                        <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">PHP {{ number_format((float) $funnelStats['pending_total'], 2) }}</p>
                    </div>
                    <div class="card" style="margin:0;background:#fff;">
                        <h3 style="margin-bottom:8px;">Failed Total</h3>
                        <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">PHP {{ number_format((float) $funnelStats['failed_total'], 2) }}</p>
                    </div>
                    <div class="card" style="margin:0;background:#fff;">
                        <h3 style="margin-bottom:8px;">Outstanding Invoices</h3>
                        <p style="margin:0;font-size:28px;font-weight:800;color:var(--theme-primary,#240E35);">
                            {{ (int) $funnelStats['outstanding_count'] }} (PHP {{ number_format((float) $funnelStats['outstanding_amount'], 2) }})
                        </p>
                    </div>
                </div>

                <div class="sa-table-scroll">
                    <table class="sa-table">
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
                                    <td>{{ $payment->payment_date?->format('Y-m-d') ?? 'N/A' }}</td>
                                    <td>{{ $payment->funnel->name ?? 'N/A' }}</td>
                                    <td>{{ $payment->step->title ?? 'N/A' }}</td>
                                    <td>{{ $payment->lead->name ?? 'N/A' }}</td>
                                    <td>PHP {{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ \App\Models\Payment::STATUSES[$payment->status] ?? ucfirst($payment->status) }}</td>
                                    <td>{{ $payment->provider ?? 'N/A' }}</td>
                                    <td>{{ $payment->provider_reference ?? 'N/A' }}</td>
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
