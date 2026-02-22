@php $isPreview = $isPreview ?? false; @endphp

@if($step->type === 'opt_in')
    {{-- No fixed form: opt-in form is only the Form component you add in the builder (same functionality, your layout). --}}
@elseif($step->type === 'checkout')
    <p class="price">PHP {{ number_format((float) ($step->price ?? 0), 2) }}</p>
    @if($isPreview)
        <button type="button" class="btn" disabled style="opacity:0.7; cursor:not-allowed;">{{ $step->cta_label ?: 'Complete Checkout' }} (preview)</button>
    @else
        <form method="POST" action="{{ route('funnels.portal.checkout', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}">
            @csrf
            <input type="hidden" name="amount" value="{{ (float) ($step->price ?? 0) }}">
            <button type="submit" class="btn">{{ $step->cta_label ?: 'Complete Checkout' }}</button>
        </form>
    @endif
@elseif(in_array($step->type, ['upsell', 'downsell'], true))
    <p class="price">Additional Offer: PHP {{ number_format((float) ($step->price ?? 0), 2) }}</p>
    @if($isPreview)
        <div class="row">
            <button type="button" class="btn" disabled style="opacity:0.7; cursor:not-allowed;">Yes, Add This Offer (preview)</button>
            <button type="button" class="btn gray" disabled style="opacity:0.7; cursor:not-allowed;">No Thanks (preview)</button>
        </div>
    @else
        <div class="row">
            <form method="POST" action="{{ route('funnels.portal.offer', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}">
                @csrf
                <input type="hidden" name="decision" value="accept">
                <button type="submit" class="btn">{{ $step->cta_label ?: 'Yes, Add This Offer' }}</button>
            </form>
            <form method="POST" action="{{ route('funnels.portal.offer', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}">
                @csrf
                <input type="hidden" name="decision" value="decline">
                <button type="submit" class="btn gray">No Thanks</button>
            </form>
        </div>
    @endif
@elseif($step->type === 'thank_you')
    <p style="font-weight: 700; color: #065f46;">Flow completed successfully.</p>
    <a class="btn secondary" href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}">
        <i class="fas fa-rotate-left"></i> Restart Funnel
    </a>
@else
    @if($nextStep)
        <a class="btn" href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $nextStep->slug]) }}">
            {{ $step->cta_label ?: 'Continue' }}
        </a>
    @else
        <a class="btn secondary" href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}">Back to Start</a>
    @endif
@endif
