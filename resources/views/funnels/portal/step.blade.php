<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $funnel->name }} - {{ $step->title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { margin: 0; background: #f8fafc; color: #0f172a; }
        .wrap { width: min(900px, 92vw); margin: 36px auto; }
        .card { background: #fff; border: 1px solid #dbeafe; border-radius: 14px; padding: 22px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08); }
        .muted { color: #64748b; font-weight: 600; font-size: 12px; }
        h1 { margin: 0 0 8px; font-size: 34px; }
        h2 { margin: 10px 0 12px; font-size: 24px; color: #1e40af; }
        .content { line-height: 1.5; color: #334155; margin-bottom: 18px; white-space: pre-wrap; }
        .btn { display: inline-flex; align-items: center; gap: 8px; border: none; border-radius: 10px; padding: 10px 16px; background: #2563eb; color: #fff; text-decoration: none; font-weight: 700; cursor: pointer; }
        .btn.secondary { background: #1e40af; }
        .btn.gray { background: #64748b; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 10px; }
        label { font-weight: 700; font-size: 13px; margin-bottom: 6px; display: block; }
        .price { font-size: 34px; font-weight: 800; color: #047857; margin: 0 0 12px; }
        .row { display: flex; gap: 10px; flex-wrap: wrap; }
    </style>
</head>
<body>
    <div class="wrap">
        <div style="margin-bottom: 10px;">
            <span class="muted">{{ $funnel->tenant->company_name ?? 'Company' }} • {{ strtoupper(str_replace('_', '-', $step->type)) }}</span>
            <h1>{{ $funnel->name }}</h1>
        </div>

        <div class="card">
            <h2>{{ $step->title }}</h2>
            <div class="content">{{ $step->content ?: 'No content configured for this step yet.' }}</div>

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
        </div>
    </div>
</body>
</html>
