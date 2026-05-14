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
        <td>
            <div class="funnels-actions">
                <button
                    type="button"
                    class="funnels-action funnels-action--rename"
                    data-funnel-rename
                    data-funnel-rename-url="{{ route('funnels.update', $funnel) }}"
                    data-funnel-name="{{ $funnel->name }}"
                    data-funnel-status="{{ $funnel->status }}"
                    data-tooltip="Rename"
                    aria-label="Rename funnel"
                >
                    <i class="fas fa-pen-to-square"></i>
                </button>
                <a
                    href="{{ route('funnels.edit', $funnel) }}"
                    class="funnels-action funnels-action--builder"
                    data-tooltip="Builder"
                    aria-label="Open funnel builder"
                >
                    <i class="fas fa-pen"></i>
                </a>
                <a
                    href="{{ route('funnels.analytics', $funnel) }}"
                    class="funnels-action funnels-action--analytics"
                    data-tooltip="Analytics"
                    aria-label="Open funnel analytics"
                >
                    <i class="fas fa-chart-line"></i>
                </a>
                <button
                    type="button"
                    class="funnels-action funnels-action--reviews"
                    data-reviews-modal-url="{{ route('funnels.reviews.index', ['funnel' => $funnel, 'modal' => 1]) }}"
                    data-reviews-modal-title="{{ $funnel->name }} Reviews"
                    data-tooltip="Reviews"
                    aria-label="Open funnel reviews"
                >
                    <i class="fas fa-star-half-alt"></i>
                </button>
                <form method="POST" action="{{ route('funnels.destroy', $funnel) }}" data-confirm-message="Delete this funnel?">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="funnels-action-btn funnels-action-btn--delete"
                        data-tooltip="Delete"
                        aria-label="Delete funnel"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align:center;">No funnels found.</td>
    </tr>
@endforelse
