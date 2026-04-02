@php
    $resourceUsage = data_get($planUsage ?? [], $resourceKey ?? '');
    $planName = data_get($planUsage ?? [], 'plan.name', 'Current Plan');
    $automationEnabled = (bool) data_get($planUsage ?? [], 'automation_enabled', true);
@endphp

@if($resourceUsage)
    <div class="card" style="margin-bottom: 20px; overflow: visible;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
            <div>
                <h3 style="margin-bottom: 6px;">{{ $title ?? 'Plan Usage' }}</h3>
                <p style="margin: 0; color: var(--theme-muted, #6B7280);">
                    {{ $planName }}
                    @if($resourceUsage['is_unlimited'])
                        includes unlimited {{ $resourceUsage['label'] }}.
                    @else
                        allows up to {{ $resourceUsage['limit'] }} {{ $resourceUsage['label'] }}.
                    @endif
                </p>
            </div>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div style="min-width: 140px; padding: 14px 16px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 10px; background: var(--theme-surface-softer, #F7F7FB);">
                    <span style="display: block; font-size: 12px; color: var(--theme-muted, #6B7280); font-weight: 700; text-transform: uppercase; letter-spacing: .04em;">Used</span>
                    <strong style="display: block; margin-top: 6px; font-size: 28px; color: var(--theme-primary-dark, #2E1244);">{{ $resourceUsage['used'] }}</strong>
                </div>
                <div style="min-width: 140px; padding: 14px 16px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 10px; background: var(--theme-surface-softer, #F7F7FB);">
                    <span style="display: block; font-size: 12px; color: var(--theme-muted, #6B7280); font-weight: 700; text-transform: uppercase; letter-spacing: .04em;">Limit</span>
                    <strong style="display: block; margin-top: 6px; font-size: 28px; color: var(--theme-primary-dark, #2E1244);">{{ $resourceUsage['is_unlimited'] ? 'Unlimited' : $resourceUsage['limit'] }}</strong>
                </div>
                <div style="min-width: 140px; padding: 14px 16px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 10px; background: var(--theme-surface-softer, #F7F7FB);">
                    <span style="display: block; font-size: 12px; color: var(--theme-muted, #6B7280); font-weight: 700; text-transform: uppercase; letter-spacing: .04em;">Remaining</span>
                    <strong style="display: block; margin-top: 6px; font-size: 28px; color: var(--theme-primary-dark, #2E1244);">{{ $resourceUsage['is_unlimited'] ? 'Unlimited' : $resourceUsage['remaining'] }}</strong>
                </div>
            </div>
        </div>

        @if(array_key_exists('automation_enabled', $planUsage ?? []))
            <div style="margin-top: 12px; padding: 12px 14px; border-radius: 10px; background: {{ $automationEnabled ? 'rgba(22, 163, 74, 0.08)' : 'rgba(217, 119, 6, 0.08)' }}; color: {{ $automationEnabled ? '#166534' : '#92400E' }}; border: 1px solid {{ $automationEnabled ? 'rgba(22, 163, 74, 0.18)' : 'rgba(217, 119, 6, 0.18)' }};">
                <strong style="display: block; margin-bottom: 4px;">Automation access</strong>
                <span style="font-size: 14px;">
                    {{ $automationEnabled ? 'Enabled for this plan.' : 'Disabled on this plan. Upgrade before turning on automation workflows or outbound message usage.' }}
                </span>
            </div>
        @endif
    </div>
@endif
