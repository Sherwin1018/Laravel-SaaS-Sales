@foreach($pipelineStatuses as $status => $label)
    <div class="pipeline-column" data-status="{{ $status }}">
        <h4 style="margin: 0 0 8px; font-size: 13px; color: #1E3A8A;">
            {{ $label }} (<span class="pipeline-column-count">{{ $pipelineLeads[$status]['total'] }}</span>)
        </h4>
        @forelse($pipelineLeads[$status]['leads'] as $pipelineLead)
            <div class="pipeline-lead-card" data-lead-name="{{ strtolower($pipelineLead->name) }}" data-lead-agent="{{ strtolower($pipelineLead->assignedAgent->name ?? 'unassigned') }}">
                <strong style="display: block; font-size: 13px;">{{ $pipelineLead->name }}</strong>
                <small style="color: var(--theme-muted, #6B7280); font-weight: 700;">{{ $pipelineLead->assignedAgent->name ?? 'Unassigned' }}</small>
                @php($pipeTags = is_array($pipelineLead->tags) ? $pipelineLead->tags : [])
                @if(count($pipeTags))
                    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:6px;">
                        @foreach($pipeTags as $tag)
                            <span style="padding:2px 8px;border-radius:999px;background:#EEF2FF;color:#3730A3;font-size:10px;font-weight:700;">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="pipeline-empty">No leads</p>
        @endforelse
    </div>
@endforeach
