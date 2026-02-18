@forelse($users as $user)
    <tr>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
            @foreach($user->roles as $role)
                <span style="background-color: #EFF6FF; color: #1E40AF; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-right: 4px;">
                    {{ $role->name }}
                </span>
            @endforeach
        </td>
        <td>{{ $user->created_at->format('Y-m-d') }}</td>
        <td>
            @if($user->id !== auth()->id())
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </form>
            @else
                <span style="color: #9CA3AF; font-size: 12px;">(You)</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" style="text-align: center;">No team members found.</td>
    </tr>
@endforelse
