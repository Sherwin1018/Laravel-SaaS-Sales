@php
    $statusStyles = [
        'new' => 'background-color: var(--theme-border, #E6E1EF); color: var(--theme-primary-dark, #2E1244);',
        'contacted' => 'background-color: #FEF3C7; color: #92400E;',
        'proposal_sent' => 'background-color: #EDE9FE; color: #5B21B6;',
        'closed_won' => 'background-color: #D1FAE5; color: #065F46;',
        'closed_lost' => 'background-color: #FEE2E2; color: #991B1B;',
    ];

    $statusLabels = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'proposal_sent' => 'Proposal Sent',
        'closed_won' => 'Closed Won',
        'closed_lost' => 'Closed Lost',
    ];
@endphp

@forelse($leads as $lead)
    <tr>
        <td><span class="cell-text" title="{{ $lead->name }}">{{ $lead->name }}</span></td>
        <td><span class="cell-text" title="{{ $lead->email }}">{{ $lead->email }}</span></td>
        <td><span class="cell-text" title="{{ $lead->phone }}">{{ $lead->phone }}</span></td>
        <td><span class="cell-text" title="{{ $lead->assignedAgent->name ?? 'Unassigned' }}">{{ $lead->assignedAgent->name ?? 'Unassigned' }}</span></td>
        <td>
            @php($leadTags = is_array($lead->tags) ? $lead->tags : [])
            @if(count($leadTags))
                <div class="lead-tags" title="{{ implode(', ', $leadTags) }}">
                    @foreach($leadTags as $tag)
                        <span class="lead-tag" style="padding: 2px 8px; border-radius: 999px; background: #E7D8F0; color: #240E35; font-size: 11px; font-weight: 700;">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            @else
                <span class="lead-no-tags" style="font-size: 12px; color: var(--theme-muted, #6B7280);">No tags</span>
            @endif
        </td>
        <td>
            <span class="lead-status-badge" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; {{ $statusStyles[$lead->status] ?? 'background-color: #E5E7EB; color: #374151;' }}">
                {{ $statusLabels[$lead->status] ?? ucfirst($lead->status) }}
            </span>
        </td>
        <td><span class="cell-text">{{ $lead->score }}</span></td>
        <td>
            <div class="lead-actions">
            <a href="{{ route('leads.edit', $lead->id) }}" style="color: var(--theme-primary, #240E35); text-decoration: none;">
                <i class="fas fa-edit"></i> Edit
            </a>

            @if(auth()->user()->hasRole('account-owner'))
                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" class="lead-delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="lead-delete-trigger" data-delete-action="{{ route('leads.destroy', $lead->id) }}" data-lead-name="{{ $lead->name }}" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0; font-weight: 600;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align: center;">No leads found.</td>
    </tr>
@endforelse
