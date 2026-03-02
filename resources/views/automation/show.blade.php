@extends('layouts.admin')

@section('title', $workflow->name)

@section('content')
<div class="auto-page">
    <header class="auto-page-header">
        <div class="auto-page-header-text">
            <h1 class="auto-page-title">{{ $workflow->name }}</h1>
            <p class="auto-page-subtitle">View details and flow. Automation runs in the background when the trigger fires.</p>
        </div>
        <div class="auto-btn-group">
            <a href="{{ route('automation.edit', $workflow) }}" class="auto-btn-primary"><i class="fas fa-pen"></i> Edit</a>
            <a href="{{ route('automation.index') }}" class="auto-btn-secondary">Back to Automation</a>
        </div>
    </header>

    @if($workflow->triggers->isNotEmpty() || $workflow->sequenceSteps->isNotEmpty())
    <div class="auto-flow-block">
        <span class="auto-flow-label">Flow</span>
        <div class="auto-flow-steps">
            @php $firstTrigger = $workflow->triggers->first(); @endphp
            @if($firstTrigger)
                <span class="auto-flow-node auto-flow-trigger">{{ \App\Models\AutomationTrigger::EVENTS[$firstTrigger->event] ?? $firstTrigger->event }}</span>
                @if($workflow->sequenceSteps->isNotEmpty())
                    <span class="auto-flow-arrow" aria-hidden="true">→</span>
                @endif
            @endif
            @foreach($workflow->sequenceSteps as $step)
                <span class="auto-flow-node auto-flow-step">{{ $step->channel === 'email' ? 'Email' : 'SMS' }}: {{ Str::limit($step->subject ?? 'Step ' . $step->position, 30) }}</span>
                @if(!$loop->last)<span class="auto-flow-arrow" aria-hidden="true">→</span>@endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="auto-card">
        <h2 class="auto-section-heading">Details</h2>
        <dl class="auto-dl">
            <div class="auto-dl-row">
                <dt class="auto-dl-dt">Status</dt>
                <dd class="auto-dl-dd">
                    @php
                        $ds = $workflow->display_status;
                        $badgeClass = $ds === 'active' ? 'auto-badge-active' : ($ds === 'draft' ? 'auto-badge-draft' : 'auto-badge-inactive');
                        $statusLabel = \App\Models\AutomationWorkflow::STATUSES[$ds] ?? ucfirst($ds);
                    @endphp
                    <span class="auto-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                </dd>
            </div>
        </dl>
        @if($workflow->n8n_workflow_id)
            <details class="auto-reference-details" style="margin-top: var(--auto-space-md);">
                <summary class="auto-reference-summary">Integration details (Advanced)</summary>
                <div class="auto-reference-details-body">
                    <p class="auto-dl-row auto-dl-row-muted" style="margin: 0;"><strong>Workflow ID:</strong> <code>{{ $workflow->n8n_workflow_id }}</code></p>
                </div>
            </details>
        @endif
    </div>

    <div class="auto-card">
        <h2 class="auto-section-heading">When it runs</h2>
        <p class="auto-section-desc">When this event happens, the automation service runs this automation.</p>
        @if($workflow->triggers->isEmpty())
            <p class="auto-card-desc" style="color: #64748B;">No trigger. Add one in Edit or create a new automation with a trigger.</p>
        @else
            <ul class="auto-triggers-list">
                @foreach($workflow->triggers as $trigger)
                    <li class="auto-triggers-item">
                        <strong>{{ \App\Models\AutomationTrigger::EVENTS[$trigger->event] ?? $trigger->event }}</strong>
                        @if($trigger->funnel)
                            <span class="auto-triggers-funnel">Funnel: {{ $trigger->funnel->name }}</span>
                        @else
                            <span class="auto-triggers-funnel">Any funnel</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="auto-card">
        <h2 class="auto-section-heading">Steps</h2>
        <p class="auto-section-desc">Email and SMS steps run in order when the workflow is triggered.</p>
        @if($workflow->sequenceSteps->isEmpty())
            <p class="auto-card-desc" style="color: #64748B;">No steps yet. Add steps in Edit.</p>
        @else
            <div class="auto-table-wrap">
                <table class="auto-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Channel</th>
                            <th>Subject</th>
                            <th>Delay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workflow->sequenceSteps as $step)
                            <tr>
                                <td>{{ $step->position }}</td>
                                <td>{{ $step->channel === 'email' ? 'Email' : 'SMS' }}</td>
                                <td>{{ $step->subject ?? '—' }}</td>
                                <td>{{ $step->delay_minutes }} min</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if(!$n8nConfigured)
        <div class="auto-alert auto-alert-warning">
            <div class="auto-alert-title"><i class="fas fa-plug" aria-hidden="true"></i> Automation service not connected</div>
            <p class="auto-alert-body">Automation runs in the background. Connect the automation service to enable this automation.</p>
        </div>
    @endif
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/automation.css') }}">
@endsection
