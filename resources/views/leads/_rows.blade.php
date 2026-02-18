@php
    $statusStyles = [
        'new' => 'background-color: #DBEAFE; color: #1E40AF;',
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
        <td>{{ $lead->name }}</td>
        <td>{{ $lead->email }}</td>
        <td>{{ $lead->phone }}</td>
        <td>{{ $lead->assignedAgent->name ?? 'Unassigned' }}</td>
        <td>
            <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; {{ $statusStyles[$lead->status] ?? 'background-color: #E5E7EB; color: #374151;' }}">
                {{ $statusLabels[$lead->status] ?? ucfirst($lead->status) }}
            </span>
        </td>
        <td>{{ $lead->score }}</td>
        <td style="display: flex; gap: 10px;">
            <a href="{{ route('leads.edit', $lead->id) }}" style="color: #2563EB; text-decoration: none;">
                <i class="fas fa-edit"></i> Edit
            </a>

            @if(auth()->user()->hasRole('account-owner'))
                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" style="text-align: center;">No leads found.</td>
    </tr>
@endforelse
