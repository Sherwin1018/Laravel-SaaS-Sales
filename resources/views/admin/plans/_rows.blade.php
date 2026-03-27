@forelse($plans as $plan)
    <tr>
        <td>{{ $plan->sort_order }}</td>
        <td>{{ $plan->code }}</td>
        <td>{{ $plan->name }}</td>
        <td>PHP {{ number_format((float) $plan->price, 2) }}</td>
        <td>{{ $plan->period }}</td>
        <td>{{ $plan->spotlight ?: 'None' }}</td>
        <td>
            @if($plan->is_active)
                <span style="color: green; font-weight: bold;">Active</span>
            @else
                <span style="color: red; font-weight: bold;">Hidden</span>
            @endif
        </td>
        <td style="display:flex;gap:10px;">
            <a href="{{ route('admin.plans.edit', $plan->id) }}" style="color: var(--theme-primary, #240E35); text-decoration: none;">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this plan?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="background:none;border:none;color:#DC2626;cursor:pointer;padding:0;font-weight:600;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" style="text-align:center;">No plans found.</td>
    </tr>
@endforelse
