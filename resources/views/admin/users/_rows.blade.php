@forelse($users as $user)
    <tr>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
            @if($user->tenant)
                <span style="background-color: #F3F4F6; color: #374151; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                    {{ $user->tenant->company_name }}
                </span>
            @else
                <span style="color: #9CA3AF; font-size: 12px;">N/A</span>
            @endif
        </td>
        <td>
            @foreach($user->roles as $role)
                <span style="background-color: #EFF6FF; color: #1E40AF; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-right: 4px; font-weight: 700;">
                    {{ $role->name }}
                </span>
            @endforeach
        </td>
        <td>
            @if($user->status === 'active')
                <span style="color: #047857; font-weight: 700;">Active</span>
            @else
                <span style="color: #B91C1C; font-weight: 700;">Suspended</span>
            @endif
        </td>
        <td>{{ $user->created_at->format('Y-m-d') }}</td>
        <td>
            @if($user->hasRole('account-owner'))
                <button type="button" class="open-status-modal"
                    data-user-id="{{ $user->id }}"
                    data-user-status="{{ $user->status }}"
                    style="padding: 6px 12px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; {{ $user->status === 'active' ? 'background-color: #FEE2E2; color: #B91C1C;' : 'background-color: #D1FAE5; color: #047857;' }}">
                    <i class="fas {{ $user->status === 'active' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                    {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                </button>
            @else
                <span style="color: #64748B; font-size: 12px; font-weight: 700;">N/A</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" style="text-align: center;">No users found.</td>
    </tr>
@endforelse
