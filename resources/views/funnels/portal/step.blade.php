<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $funnel->name }} - {{ $step->title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Manrope:wght@400;600;700;800&family=Montserrat:wght@400;600;700;800&family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600;700;800&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;600;700;800&family=Raleway:wght@400;600;700;800&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { margin: 0; background: #f8fafc; color: #0f172a; }
        .wrap { width: min(1100px, 94vw); margin: 32px auto; }
        .card {
            background: {{ $step->background_color ?: '#ffffff' }};
            border: 1px solid #dbeafe;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
            overflow: hidden;
        }
        .muted { color: #64748b; font-weight: 600; font-size: 12px; }
        h1 { margin: 0 0 8px; font-size: 32px; }
        h2 { margin: 0 0 10px; font-size: 28px; color: #0f172a; }
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
        .builder-section { border-radius: 14px; margin-bottom: 14px; border: none; }
        .builder-row { display: flex; gap: 12px; flex-wrap: wrap; padding: 6px; }
        .builder-col { min-width: 240px; min-height: 24px; flex: 1 1 0; }
        .builder-el + .builder-el { margin-top: 10px; }
        .builder-heading { margin: 0; font-size: 32px; line-height: 1.2; }
        .builder-text { margin: 0; color: #334155; line-height: 1.6; white-space: pre-wrap; }
        .builder-img { display: block; max-width: 100%; height: auto; border-radius: 10px; }
        .builder-video-wrap { position: relative; padding-top: 56.25%; border-radius: 10px; overflow: hidden; background: #0f172a; }
        .builder-video-wrap iframe, .builder-video-wrap video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
        .builder-countdown { display: inline-flex; padding: 10px 14px; border-radius: 999px; border: 1px solid #cbd5e1; font-weight: 800; font-size: 14px; }
        .preview-badge {
            display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px;
            border-radius: 999px; font-size: 12px; font-weight: 800; color: #1d4ed8; background: #dbeafe;
        }
    </style>
</head>
<body>
    @php
        $isPreview = $isPreview ?? false;
        $layout = is_array($step->layout_json ?? null) ? $step->layout_json : [];
        $hasBuilderLayout = !empty($layout['sections']) && is_array($layout['sections']);

        $styleToString = function (array $style): string {
            $allowed = [
                'backgroundColor' => 'background-color',
                'color' => 'color',
                'fontSize' => 'font-size',
                'fontWeight' => 'font-weight',
                'fontFamily' => 'font-family',
                'padding' => 'padding',
                'margin' => 'margin',
                'textAlign' => 'text-align',
                'borderRadius' => 'border-radius',
                'border' => 'border',
                'boxShadow' => 'box-shadow',
                'width' => 'width',
                'height' => 'height',
                'justifyContent' => 'justify-content',
                'alignItems' => 'align-items',
                'gap' => 'gap',
            ];

            $out = [];
            foreach ($allowed as $key => $cssProp) {
                $value = trim((string) ($style[$key] ?? ''));
                if ($value === '') {
                    continue;
                }
                if (!preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/', $value)) {
                    continue;
                }
                $out[] = $cssProp . ':' . $value;
            }

            return implode(';', $out);
        };
    @endphp

    <div class="wrap">
        <div style="margin-bottom: 10px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <div>
                @if($isPreview)
                    <a class="btn secondary" href="{{ route('funnels.edit', $funnel) }}" style="padding:8px 14px; box-shadow:none;">
                        <i class="fas fa-arrow-left"></i> Back to Builder
                    </a>
                @elseif(!$isFirstStep)
                    <button type="button" onclick="history.back()"
                        style="border:none; background:none; color:#1e40af; font-size:13px; display:inline-flex; align-items:center; gap:6px; cursor:pointer; padding:4px 0;">
                        <i class="fas fa-arrow-left"></i>
                        <span style="font-weight:600;">Back</span>
                    </button>
                @endif
            </div>
            <div style="margin-left:auto; text-align:right;">
                @if($isPreview)
                    <span class="preview-badge"><i class="fas fa-eye"></i> Preview Mode</span>
                @endif
                <span class="muted" style="display:block; margin-top:4px;">{{ $funnel->tenant->company_name ?? 'Company' }} • {{ strtoupper(str_replace('_', '-', $step->type)) }}</span>
                <h1 style="margin-top:4px;">{{ $funnel->name }}</h1>
            </div>
        </div>

        <div class="card">
            @if($hasBuilderLayout)
                @foreach($layout['sections'] as $section)
                    @php
                        $sectionStyle = $styleToString(is_array($section['style'] ?? null) ? $section['style'] : []);
                        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                    @endphp
                    <section class="builder-section" style="{{ $sectionStyle }}">
                        @foreach($rows as $row)
                            @php
                                $rowStyle = $styleToString(is_array($row['style'] ?? null) ? $row['style'] : []);
                                $columns = is_array($row['columns'] ?? null) ? $row['columns'] : [];
                            @endphp
                            <div class="builder-row" style="{{ $rowStyle }}">
                                @foreach($columns as $column)
                                    @php
                                        $colStyle = $styleToString(is_array($column['style'] ?? null) ? $column['style'] : []);
                                        $elements = is_array($column['elements'] ?? null) ? $column['elements'] : [];
                                    @endphp
                                    <div class="builder-col" style="{{ $colStyle }}">
                                        @foreach($elements as $element)
                                            @php
                                                $type = $element['type'] ?? 'text';
                                                $content = (string) ($element['content'] ?? '');
                                                $style = $styleToString(is_array($element['style'] ?? null) ? $element['style'] : []);
                                                $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                                                $link = trim((string) ($settings['link'] ?? '#'));
                                                $src = trim((string) ($settings['src'] ?? ''));
                                                $alt = trim((string) ($settings['alt'] ?? 'Image'));
                                                $targetDate = trim((string) ($settings['targetDate'] ?? ''));
                                            @endphp

                                            <div class="builder-el">
                                                @if($type === 'heading')
                                                    <h2 class="builder-heading" style="{{ $style }}">{!! $content !!}</h2>
                                                @elseif($type === 'text')
                                                    <p class="builder-text" style="{{ $style }}">{!! $content !!}</p>
                                                @elseif($type === 'image')
                                                    @if($src !== '')
                                                        <img class="builder-img" src="{{ $src }}" alt="{{ $alt !== '' ? $alt : 'Image' }}" style="{{ $style }}">
                                                    @endif
                                                @elseif($type === 'button')
                                                    <a class="btn" href="{{ $link !== '' ? $link : '#' }}" style="{{ $style }}">{!! $content !== '' ? $content : 'Button' !!}</a>
                                                @elseif($type === 'video')
                                                    @if($src !== '')
                                                        <div class="builder-video-wrap" style="{{ $style }}">
                                                            @if(\Illuminate\Support\Str::contains($src, ['youtube.com', 'youtu.be', 'vimeo.com']))
                                                                <iframe src="{{ $src }}" allowfullscreen></iframe>
                                                            @else
                                                                <video src="{{ $src }}" controls></video>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @elseif($type === 'spacer')
                                                    <div style="{{ $style ?: 'height:24px' }}"></div>
                                                @elseif($type === 'countdown')
                                                    <div class="builder-countdown" style="{{ $style }}">
                                                        Countdown{{ $targetDate ? ': ' . $targetDate : '' }}
                                                    </div>
                                                @elseif($type === 'form')
                                                    <form onsubmit="return false;" style="{{ $style }}">
                                                        <label>Name</label>
                                                        <input type="text" placeholder="Your name">
                                                        <label>Email</label>
                                                        <input type="email" placeholder="you@email.com">
                                                        <button type="button" class="btn">{{ $content !== '' ? $content : 'Submit' }}</button>
                                                    </form>
                                                @else
                                                    <p class="builder-text" style="{{ $style }}">{{ $content }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </section>
                @endforeach

                <div style="margin-top: 14px;">
                    @include('funnels.portal._step-actions', ['funnel' => $funnel, 'step' => $step, 'nextStep' => $nextStep, 'isPreview' => $isPreview])
                </div>
            @else
                <h2>{{ $step->title }}</h2>
                @if($step->subtitle)
                    <h3 class="subtitle">{{ $step->subtitle }}</h3>
                @endif
                <div class="content">{{ $step->content ?: 'No content configured for this step yet.' }}</div>
                @include('funnels.portal._step-actions', ['funnel' => $funnel, 'step' => $step, 'nextStep' => $nextStep, 'isPreview' => $isPreview])
            @endif
        </div>
    </div>
</body>
</html>
