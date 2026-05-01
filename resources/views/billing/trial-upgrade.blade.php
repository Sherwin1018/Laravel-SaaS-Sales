@extends('layouts.admin')

@section('title', 'Trial Billing')
@php
    $emptyDash = $emptyDash ?? '—';
@endphp

@section('content')
    <div class="top-header">
        <h1>{{ $tenant && $tenant->isTrialExpired() ? 'Trial Expired' : 'Complete Your Upgrade' }}</h1>
    </div>

        <link rel="stylesheet" href="{{ asset('css/extracted/billing-trial-upgrade-style1.css') }}">

    <div class="card" style="margin-bottom: 20px;">
        <h3 style="margin-top: 0;">{{ $tenant?->company_name ?? 'Your Workspace' }}</h3>
        <p style="margin: 10px 0 0; color: var(--theme-muted, #6B7280); line-height: 1.7;">
            @if($tenant && $tenant->isTrialExpired())
                Your 7-day free trial ended on {{ optional($tenant->trial_ends_at)->format('F j, Y g:i A') }}.
                Choose a plan below to reactivate your Account Owner workspace through PayMongo.
            @else
                Your 7-day free trial is active until {{ optional($tenant?->trial_ends_at)->format('F j, Y g:i A') }}.
                You can upgrade anytime to keep uninterrupted access after the trial window ends.
            @endif
        </p>
        @if($paymentCancelled)
            <p style="margin: 14px 0 0; color: #B45309; font-weight: 600;">Payment was cancelled. You can restart checkout anytime.</p>
        @endif
    </div>

    <div class="payment-summary-grid" style="margin-bottom: 20px;">
        <div class="card payment-summary-card">
            <h3>Billing State</h3>
            <p class="payment-summary-card__value">{{ $billingStateLabel }}</p>
        </div>
        <div class="card payment-summary-card">
            <h3>Trial Days Remaining</h3>
            <p class="payment-summary-card__value">{{ number_format((int) ($tenant?->trialDaysRemaining() ?? 0)) }}</p>
        </div>
        <div class="card payment-summary-card">
            <h3>Grace Days Remaining</h3>
            <p class="payment-summary-card__value">{{ number_format((int) ($tenant?->billingGraceDaysRemaining() ?? 0)) }}</p>
        </div>
        <div class="card payment-summary-card">
            <h3>Active Since</h3>
            <p class="payment-summary-card__value payment-summary-card__value--compact">{{ optional($tenant?->subscription_activated_at)->format('Y-m-d H:i') ?? $emptyDash }}</p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3 style="margin-top: 0;">Current Usage and Limits</h3>
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

    <div class="trial-plan-grid">
        @foreach($plans as $plan)
            <form method="POST" action="{{ route('trial.billing.checkout') }}" class="card trial-plan-card">
                @csrf
                <input type="hidden" name="plan" value="{{ $plan['code'] }}">

                @if($plan['spotlight'])
                    <span style="position:absolute;top:-12px;left:24px;padding:8px 12px;border-radius:999px;background:var(--theme-primary, #240E35);color:#fff;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;">
                        {{ $plan['spotlight'] }}
                    </span>
                @endif

                <h3 style="margin: 8px 0 8px;">{{ $plan['name'] }}</h3>
                <p class="trial-plan-summary">{{ $plan['summary'] }}</p>

                <div style="margin:22px 0 18px;">
                    <strong style="display:block;font-size:32px;color:var(--theme-primary, #240E35);line-height:1.1;">PHP {{ number_format($plan['price'], 0) }}</strong>
                    <span style="display:block;margin-top:6px;color:var(--theme-muted, #6B7280);font-weight:600;">{{ $plan['period'] }}</span>
                </div>

                <ul class="trial-plan-features">
                    @foreach($plan['features'] as $feature)
                        <li style="position:relative;padding-left:18px;line-height:1.6;color:var(--theme-body-text, #111827);">
                            <span style="position:absolute;left:0;top:0;color:var(--theme-accent, #6B4A7A);font-weight:800;">+</span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <button type="submit" class="trial-plan-button">
                    Pay with PayMongo
                </button>
            </form>
        @endforeach
    </div>
@endsection

