@php
    $resourceUsage = data_get($planUsage ?? [], $resourceKey ?? '');
    $planName = data_get($planUsage ?? [], 'plan.name', 'Current Plan');
    $automationEnabled = (bool) data_get($planUsage ?? [], 'automation_enabled', true);
    $automationMode = (string) data_get($planUsage ?? [], 'automation_mode', $automationEnabled ? 'shared' : 'none');
    $compact = (bool) ($compact ?? false);
@endphp

@if($resourceUsage)
    <div class="card plan-usage-card{{ $compact ? ' plan-usage-card--compact' : '' }}">
        <div class="plan-usage-shell{{ $compact ? ' plan-usage-shell--compact' : '' }}">
            <div class="plan-usage-copy">
                <span class="plan-usage-eyebrow">{{ $title ?? 'Plan Usage' }}</span>
                <p class="plan-usage-description{{ $compact ? ' plan-usage-description--compact' : '' }}">
                    {{ $planName }}
                    @if($resourceUsage['is_unlimited'])
                        includes unlimited {{ $resourceUsage['label'] }}.
                    @else
                        allows up to {{ $resourceUsage['limit'] }} {{ $resourceUsage['label'] }}.
                    @endif
                </p>

                @if(array_key_exists('automation_enabled', $planUsage ?? []))
                    <div class="plan-usage-callout{{ $compact ? ' plan-usage-callout--compact' : '' }} {{ $automationEnabled ? 'is-positive' : 'is-warning' }}">
                        <strong class="plan-usage-callout-title">Shared automation access</strong>
                        <span class="plan-usage-callout-copy">
                            @if($automationEnabled)
                                This plan includes the shared n8n automation engine for eligible workflows.
                            @elseif($automationMode === 'limited')
                                This plan includes shared n8n email automations plus limited built-in workflow automations.
                            @else
                                This plan keeps automation in built-in tracking mode only. Upgrade to Growth or Scale to unlock shared n8n automation.
                            @endif
                        </span>
                    </div>
                @endif
            </div>
            <div class="plan-usage-stat-grid{{ $compact ? ' plan-usage-stat-grid--compact' : '' }}">
                <div class="plan-usage-stat-card{{ $compact ? ' plan-usage-stat-card--compact' : '' }}">
                    <span class="plan-usage-stat-label">Used</span>
                    <strong class="plan-usage-stat-value">{{ $resourceUsage['used'] }}</strong>
                </div>
                <div class="plan-usage-stat-card{{ $compact ? ' plan-usage-stat-card--compact' : '' }}">
                    <span class="plan-usage-stat-label">Limit</span>
                    <strong class="plan-usage-stat-value">{{ $resourceUsage['is_unlimited'] ? 'Unlimited' : $resourceUsage['limit'] }}</strong>
                </div>
                <div class="plan-usage-stat-card{{ $compact ? ' plan-usage-stat-card--compact' : '' }}">
                    <span class="plan-usage-stat-label">Remaining</span>
                    <strong class="plan-usage-stat-value">{{ $resourceUsage['is_unlimited'] ? 'Unlimited' : $resourceUsage['remaining'] }}</strong>
                </div>
            </div>
        </div>
    </div>
@endif
