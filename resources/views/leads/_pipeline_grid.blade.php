@foreach($pipelineStatuses as $status => $label)
    <div class="pipeline-column" data-status="{{ $status }}">
        <h4 style="margin: 0 0 8px; font-size: 13px; color: #1E3A8A;">
            {{ $label }} (<span class="pipeline-column-count">{{ $pipelineLeads[$status]['total'] }}</span>)
        </h4>
        @forelse($pipelineLeads[$status]['leads'] as $pipelineLead)
            <div class="pipeline-lead-card" data-lead-name="{{ strtolower($pipelineLead->name) }}" data-lead-agent="{{ strtolower($pipelineLead->assignedAgent->name ?? 'unassigned') }}">
                <strong style="display: block; font-size: 13px;">{{ $pipelineLead->name }}</strong>
                <small style="color: #64748B; font-weight: 700;">{{ $pipelineLead->assignedAgent->name ?? 'Unassigned' }}</small>
            </div>
        @empty
            <p class="pipeline-empty">No leads</p>
        @endforelse
    </div>
@endforeach
