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
        .wrap { width: min(1100px, 94vw); margin: 36px auto; }
        .card {
            background: {{ $step->background_color ?: '#ffffff' }};
            border: 1px solid #dbeafe;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
            overflow: hidden;
        }
        .muted { color: #64748b; font-weight: 600; font-size: 12px; }
        h1 { margin: 0 0 8px; font-size: 32px; }
        h2 { margin: 10px 0 6px; font-size: 26px; color: #0f172a; }
        h3.subtitle { margin: 0 0 14px; font-size: 16px; color: #64748b; font-weight: 600; }
        .content { line-height: 1.5; color: #334155; margin-bottom: 18px; white-space: pre-wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            border-radius: 999px;
            padding: 10px 20px;
            background: {{ $step->button_color ?: 'var(--theme-primary, #2563eb)' }};
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
        }
        .btn.secondary { background: #1e40af; }
        .btn.gray { background: #64748b; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 10px; }
        label { font-weight: 700; font-size: 13px; margin-bottom: 6px; display: block; }
        .price { font-size: 34px; font-weight: 800; color: #047857; margin: 0 0 12px; }
        .row { display: flex; gap: 10px; flex-wrap: wrap; }

        .layout-split {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            align-items: flex-start;
        }
        .layout-split .col-text { flex: 1 1 280px; min-width: 0; }
        .layout-split .col-image { flex: 0 0 320px; max-width: 360px; }
        .layout-split .col-image img {
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.6);
        }
        .layout-centered-hero {
            margin-bottom: 18px;
        }
        .layout-centered-hero img {
            width: 100%;
            max-height: 320px;
            object-fit: cover;
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.6);
        }

        /* Templates */
        .section { margin-top: 18px; }
        .section-title { font-weight: 900; font-size: 18px; color:#0f172a; margin: 0 0 10px; }
        .features { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .feature { background:#fff; border:1px solid rgba(219,234,254,0.95); border-radius: 14px; padding: 14px; }
        .feature h4 { margin: 0 0 6px; font-size: 14px; font-weight: 900; color:#0f172a; }
        .feature p { margin: 0; color:#475569; font-size: 13px; line-height:1.5; }
        .bullets { margin: 0; padding-left: 18px; color:#334155; }
        .bullets li { margin: 6px 0; }
        .faq details { background:#fff; border:1px solid rgba(219,234,254,0.95); border-radius: 14px; padding: 12px 14px; }
        .faq details + details { margin-top: 10px; }
        .faq summary { cursor:pointer; font-weight:900; color:#0f172a; }
        .faq .ans { margin-top: 8px; color:#475569; white-space: pre-wrap; }
        .testimonials { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .quote { background:#fff; border:1px solid rgba(219,234,254,0.95); border-radius: 14px; padding: 14px; }
        .quote p { margin: 0 0 10px; color:#334155; line-height:1.6; }
        .quote .by { font-size: 12px; font-weight: 900; color:#0f172a; }
        .next-steps { margin: 0; padding-left: 18px; color:#334155; }
        @media (max-width: 900px) { .features { grid-template-columns: 1fr; } .testimonials { grid-template-columns: 1fr; } }

    </style>
</head>
<body>
    <div class="wrap">
        <div style="margin-bottom: 10px;">
            <span class="muted">{{ $funnel->tenant->company_name ?? 'Company' }} • {{ strtoupper(str_replace('_', '-', $step->type)) }}</span>
            <h1>{{ $funnel->name }}</h1>
        </div>

        <div class="card">
            @php
                $layout = $step->layout_style ?: 'centered';
                $hero = trim((string) $step->hero_image_url);
                $template = $step->template ?: 'simple';
                $td = $step->template_data ?: [];
            @endphp

            @if($layout === 'split_left' || $layout === 'split_right')
                <div class="layout-split" style="flex-direction: {{ $layout === 'split_right' ? 'row-reverse' : 'row' }};">
                    @if($hero)
                        <div class="col-image">
                            <img src="{{ $hero }}" alt="Hero">
                        </div>
                    @endif
                    <div class="col-text">
                        <h2>{{ $step->title }}</h2>
                        @if($step->subtitle)
                            <h3 class="subtitle">{{ $step->subtitle }}</h3>
                        @endif
                        <div class="content">{{ $step->content ?: 'No content configured for this step yet.' }}</div>

                        @if($template === 'hero_features' || $template === 'sales_long')
                            <div class="section">
                                <div class="section-title">Features</div>
                                <div class="features">
                                    @foreach(($td['features'] ?? []) as $f)
                                        @if(!empty($f['title']) || !empty($f['body']))
                                            <div class="feature">
                                                <h4>{{ $f['title'] ?? '' }}</h4>
                                                <p>{{ $f['body'] ?? '' }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @elseif($template === 'lead_capture')
                            <div class="section">
                                <div class="section-title">{{ $td['form_title'] ?? 'What you’ll get' }}</div>
                                <ul class="bullets">
                                    @foreach(($td['bullets'] ?? []) as $b)
                                        @if($b) <li>{{ $b }}</li> @endif
                                    @endforeach
                                </ul>
                            </div>
                        @elseif($template === 'thank_you_next')
                            <div class="section">
                                <div class="section-title">Next steps</div>
                                <ol class="next-steps">
                                    @foreach(($td['next_steps'] ?? []) as $ns)
                                        @if($ns) <li>{{ $ns }}</li> @endif
                                    @endforeach
                                </ol>
                            </div>
                        @endif

                        @if($template === 'sales_long')
                            <div class="section faq">
                                <div class="section-title">FAQ</div>
                                @foreach(($td['faq'] ?? []) as $it)
                                    @if(!empty($it['q']))
                                        <details>
                                            <summary>{{ $it['q'] }}</summary>
                                            <div class="ans">{{ $it['a'] ?? '' }}</div>
                                        </details>
                                    @endif
                                @endforeach
                            </div>

                            <div class="section">
                                <div class="section-title">Testimonials</div>
                                <div class="testimonials">
                                    @foreach(($td['testimonials'] ?? []) as $t)
                                        @if(!empty($t['quote']))
                                            <div class="quote">
                                                <p>“{{ $t['quote'] }}”</p>
                                                <div class="by">{{ $t['name'] ?? 'Customer' }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @include('funnels.portal._step-actions', ['funnel' => $funnel, 'step' => $step, 'nextStep' => $nextStep])
                    </div>
                </div>
            @else
                @if($hero)
                    <div class="layout-centered-hero">
                        <img src="{{ $hero }}" alt="Hero">
                    </div>
                @endif
                <h2>{{ $step->title }}</h2>
                @if($step->subtitle)
                    <h3 class="subtitle">{{ $step->subtitle }}</h3>
                @endif
                <div class="content">{{ $step->content ?: 'No content configured for this step yet.' }}</div>

                @if($template === 'hero_features' || $template === 'sales_long')
                    <div class="section">
                        <div class="section-title">Features</div>
                        <div class="features">
                            @foreach(($td['features'] ?? []) as $f)
                                @if(!empty($f['title']) || !empty($f['body']))
                                    <div class="feature">
                                        <h4>{{ $f['title'] ?? '' }}</h4>
                                        <p>{{ $f['body'] ?? '' }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @elseif($template === 'lead_capture')
                    <div class="section">
                        <div class="section-title">{{ $td['form_title'] ?? 'What you’ll get' }}</div>
                        <ul class="bullets">
                            @foreach(($td['bullets'] ?? []) as $b)
                                @if($b) <li>{{ $b }}</li> @endif
                            @endforeach
                        </ul>
                    </div>
                @elseif($template === 'thank_you_next')
                    <div class="section">
                        <div class="section-title">Next steps</div>
                        <ol class="next-steps">
                            @foreach(($td['next_steps'] ?? []) as $ns)
                                @if($ns) <li>{{ $ns }}</li> @endif
                            @endforeach
                        </ol>
                    </div>
                @endif

                @if($template === 'sales_long')
                    <div class="section faq">
                        <div class="section-title">FAQ</div>
                        @foreach(($td['faq'] ?? []) as $it)
                            @if(!empty($it['q']))
                                <details>
                                    <summary>{{ $it['q'] }}</summary>
                                    <div class="ans">{{ $it['a'] ?? '' }}</div>
                                </details>
                            @endif
                        @endforeach
                    </div>

                    <div class="section">
                        <div class="section-title">Testimonials</div>
                        <div class="testimonials">
                            @foreach(($td['testimonials'] ?? []) as $t)
                                @if(!empty($t['quote']))
                                    <div class="quote">
                                        <p>“{{ $t['quote'] }}”</p>
                                        <div class="by">{{ $t['name'] ?? 'Customer' }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @include('funnels.portal._step-actions', ['funnel' => $funnel, 'step' => $step, 'nextStep' => $nextStep])
            @endif
        </div>
    </div>
</body>
</html>
