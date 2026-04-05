@forelse($funnels as $funnel)
    <tr>
        <td>{{ $funnel->name }}</td>
        <td>{{ method_exists($funnel, 'purposeLabel') ? $funnel->purposeLabel() : ucfirst(str_replace('_', ' ', $funnel->purpose ?? 'service')) }}</td>
        <td>{{ ucfirst($funnel->status) }}</td>
        <td>{{ $funnel->steps_count }}</td>
        <td>
            @if($funnel->status === 'published')
                <a href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}" target="_blank">
                    {{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}
                </a>
            @else
                <span style="color: var(--theme-muted, #6B7280);">Publish to enable</span>
            @endif
        </td>
        <td style="display:flex; gap: 10px;">
            <a href="{{ route('funnels.edit', $funnel) }}" style="color:var(--theme-primary, #240E35); text-decoration:none; font-weight:700;">
                <i class="fas fa-pen"></i> Builder
            </a>
            <a href="{{ route('funnels.analytics', $funnel) }}" style="color:#0F766E; text-decoration:none; font-weight:700;">
                <i class="fas fa-chart-line"></i> Analytics
            </a>
            <form method="POST" action="{{ route('funnels.destroy', $funnel) }}" data-confirm-message="Delete this funnel?">
                @csrf
                @method('DELETE')
                <button type="submit" style="background:none;border:none;color:#DC2626;cursor:pointer;font-weight:700;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align:center;">No funnels found.</td>
    </tr>
@endforelse
