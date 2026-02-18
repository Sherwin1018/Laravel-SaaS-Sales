@php
    $statusStyles = [
        'new' => 'background-color: #DBEAFE; color: #1E40AF;',
        'contacted' => 'background-color: #FEF3C7; color: #92400E;',
        'proposal_sent' => 'background-color: #EDE9FE; color: #5B21B6;',
        'closed_won' => 'background-color: #D1FAE5; color: #065F46;',
        'closed_lost' => 'background-color: #FEE2E2; color: #991B1B;',
    ];
@endphp

@forelse($leads as $lead)
    <tr>
        <td>{{ $lead->name }}</td>
        <td>{{ $lead->email }}</td>
        <td>
            @if($lead->tenant)
                <span style="background-color: #F3F4F6; color: #374151; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                    {{ $lead->tenant->company_name }}
                </span>
            @else
                <span style="color: #9CA3AF; font-size: 12px;">N/A</span>
            @endif
        </td>
        <td>{{ $lead->assignedAgent->name ?? 'Unassigned' }}</td>
        <td>
            <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; {{ $statusStyles[$lead->status] ?? 'background-color: #E5E7EB; color: #374151;' }}">
                {{ ucwords(str_replace('_', ' ', $lead->status)) }}
            </span>
        </td>
        <td>{{ $lead->score }}</td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align: center;">No leads found.</td>
    </tr>
@endforelse
