@extends('layouts.admin')

@section('title', 'Complete Payout Setup')

@php
    $companyName = optional($user->tenant)->company_name ?? 'Your Company';
    $statusColor = $payoutAccount?->isApproved()
        ? '#166534'
        : ($payoutAccount?->isRejected() ? '#B91C1C' : '#92400E');
@endphp

@section('content')
    <div class="top-header">
        <h1>Complete Your Payout Setup</h1>
    </div>

    <div class="card" style="max-width: 960px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <h3 style="margin:0 0 10px;">{{ $companyName }}</h3>
                <p style="margin:0;color:var(--theme-muted, #6B7280);font-weight:600;line-height:1.7;max-width:720px;">
                    Add the payout destination where your funnel earnings should settle. We keep the destination masked in the UI and send the setup to the platform finance admin for independent review.
                </p>
            </div>
            @if($payoutAccount)
                <div style="padding:10px 14px;border-radius:999px;background:#F8FAFC;color:{{ $statusColor }};font-weight:800;">
                    {{ $payoutAccount->reviewStatusLabel() }}
                </div>
            @endif
        </div>

        @if($payoutAccount?->review_notes)
            <div style="margin-top:16px;padding:14px 16px;border-radius:12px;background:#FFF7ED;border:1px solid #FED7AA;color:#9A3412;font-weight:600;line-height:1.6;">
                Review notes: {{ $payoutAccount->review_notes }}
            </div>
        @endif

        <form action="{{ route('profile.payout.update') }}" method="POST" enctype="multipart/form-data" style="margin-top: 18px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="from_payout_setup" value="1">

            <div class="app-form-grid app-form-grid--2" style="gap:14px;">
                <div>
                    <label for="destination_type" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Destination type</label>
                    <select id="destination_type" name="destination_type"
                        style="width:100%;padding:12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;">
                        @foreach(['gcash' => 'GCash', 'bank_transfer' => 'Card / Bank'] as $value => $label)
                            <option value="{{ $value }}" {{ old('destination_type', $payoutAccount?->destination_type ?? 'gcash') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="account_name" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Account name</label>
                    <input type="text" id="account_name" name="account_name"
                        value="{{ old('account_name', $payoutAccount?->account_name ?? '') }}"
                        placeholder="Enter the destination account holder name"
                        style="width:100%;padding:12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;">
                </div>

                <div>
                    <label for="destination_value" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">GCash number / bank identifier</label>
                    <input type="text" id="destination_value" name="destination_value"
                        value="{{ old('destination_value', '') }}"
                        placeholder="Enter the payout destination you want reviewed"
                        style="width:100%;padding:12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;">
                    <div style="margin-top:6px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">
                        Current masked destination: {{ $payoutAccount?->masked_destination ?? '-' }}
                    </div>
                </div>

                <div>
                    <label for="provider_destination_reference" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Provider-managed reference</label>
                    <input type="text" id="provider_destination_reference" name="provider_destination_reference"
                        value="{{ old('provider_destination_reference', $payoutAccount?->provider_destination_reference ?? '') }}"
                        placeholder="Optional external recipient or provider reference"
                        style="width:100%;padding:12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;">
                </div>
            </div>

            <div style="margin-top:14px;">
                <label for="gcash_qr" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">GCash QR (optional)</label>
                <input type="file" id="gcash_qr" name="gcash_qr" accept=".jpg,.jpeg,.png"
                    style="width:100%;padding:12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;background:#fff;">
                <div style="margin-top:6px;color:var(--theme-muted, #6B7280);font-size:12px;font-weight:600;">
                    Upload only if you want customers to scan your QR for manual payments.
                </div>
                @if(data_get($payoutAccount, 'meta.gcash.qr_path'))
                    <div style="margin-top:10px;display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                        <img src="{{ asset('storage/' . data_get($payoutAccount, 'meta.gcash.qr_path')) }}" alt="GCash QR"
                            style="width:140px;height:140px;object-fit:contain;border-radius:12px;border:1px solid var(--theme-border, #E6E1EF);background:#fff;padding:8px;">
                        <div style="color:var(--theme-muted, #6B7280);font-size:12px;font-weight:700;line-height:1.5;max-width:520px;">
                            Current QR is stored and will be shown on the manual payment page.
                            Upload a new file to replace it.
                        </div>
                    </div>
                @endif
                @error('gcash_qr')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-top:14px;">
                <label for="payout_notes" style="display:block;margin-bottom:6px;font-weight:800;color:#0F172A;">Operations notes</label>
                <textarea id="payout_notes" name="notes" rows="4"
                    style="width:100%;padding:12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;">{{ old('notes', data_get($payoutAccount, 'meta.notes', '')) }}</textarea>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-top:18px;">
                <p style="margin:0;color:var(--theme-muted, #6B7280);font-size:13px;font-weight:600;line-height:1.6;">
                    Saving this sends the destination for platform review. You can use the app after setup while the review is pending.
                </p>
                <button type="submit"
                    style="padding:12px 18px;border:none;border-radius:10px;background:var(--theme-primary, #240E35);color:#fff;cursor:pointer;font-weight:700;">
                    Save Payout Setup
                </button>
            </div>
        </form>
    </div>
@endsection
