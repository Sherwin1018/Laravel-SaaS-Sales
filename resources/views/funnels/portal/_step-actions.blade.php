@if($step->type === 'opt_in')
    <form method="POST" action="{{ route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}">
        @csrf
        <label>Name</label>
        <input type="text" name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Phone (PH 09XXXXXXXXX)</label>
        <input type="text" name="phone" required pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric">
        <button type="submit" class="btn">{{ $step->cta_label ?: 'Submit and Continue' }}</button>
    </form>
@elseif($step->type === 'checkout')
    <p class="price">PHP {{ number_format((float) ($step->price ?? 0), 2) }}</p>
    <form method="POST" action="{{ route('funnels.portal.checkout', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}">
        @csrf
        <input type="hidden" name="amount" value="{{ (float) ($step->price ?? 0) }}">
        <button type="submit" class="btn">{{ $step->cta_label ?: 'Complete Checkout' }}</button>
    </form>
@elseif(in_array($step->type, ['upsell', 'downsell'], true))
    <p class="price">Additional Offer: PHP {{ number_format((float) ($step->price ?? 0), 2) }}</p>
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

