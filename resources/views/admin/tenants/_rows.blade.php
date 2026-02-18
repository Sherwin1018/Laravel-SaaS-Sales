@forelse($tenants as $tenant)
    <tr>
        <td>{{ $tenant->id }}</td>
        <td>{{ $tenant->company_name }}</td>
        <td>{{ $tenant->subscription_plan }}</td>
        <td>
            @if($tenant->status == 'active')
                <span style="color: green; font-weight: bold;">Active</span>
            @elseif($tenant->status == 'inactive')
                <span style="color: red; font-weight: bold;">Inactive</span>
            @else
                <span style="color: orange; font-weight: bold;">Trial</span>
            @endif
        </td>
        <td>{{ $tenant->created_at->format('Y-m-d') }}</td>
        <td style="display: flex; gap: 10px;">
            <a href="{{ route('admin.tenants.edit', $tenant->id) }}" style="color: #2563EB; text-decoration: none;">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('admin.tenants.destroy', $tenant->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align: center;">No tenants found.</td>
    </tr>
@endforelse
