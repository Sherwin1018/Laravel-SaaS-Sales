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
        body { margin: 0; background: {{ $step->background_color ?: '#ffffff' }}; color: #0f172a; min-height: 100vh; }
        .wrap { width: 100%; max-width: none; margin: 0; padding: 0; }
        .step-content--full {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 32px 2rem 48px;
            background: {{ $step->background_color ?: '#ffffff' }};
            border: none;
            border-radius: 0;
            box-shadow: none;
            overflow: hidden;
        }
        .step-content--full .builder-section,
        .step-content--full .builder-row,
        .step-content--full .builder-col { max-width: none !important; }
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
        .btn { overflow-wrap: break-word; word-break: break-word; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 10px; }
        .builder-form input { margin-bottom: 0; }
        .builder-form .builder-form-field-wrap { margin-bottom: 10px; }
        label { font-weight: 700; font-size: 13px; margin-bottom: 6px; display: block; }
        .price { font-size: 34px; font-weight: 800; color: #047857; margin: 0 0 12px; }
        .row { display: flex; gap: 10px; flex-wrap: wrap; }
        .builder-section { border-radius: 14px; margin-bottom: 14px; border: none; }
        .builder-row { display: flex; gap: 12px; flex-wrap: wrap; padding: 6px; }
        .builder-col { min-width: 240px; min-height: 24px; flex: 1 1 0; }
        .builder-el + .builder-el { margin-top: 10px; }
        .builder-heading { margin: 0; font-size: 32px; line-height: 1.2; overflow-wrap: break-word; word-break: break-word; }
        .builder-text { margin: 0; color: #334155; line-height: 1.6; white-space: pre-wrap; overflow-wrap: break-word; word-break: break-word; }
        .builder-img { display: block; max-width: 100%; height: auto; border-radius: 10px; }
        .builder-video-wrap { position: relative; width: 100%; padding-top: 56.25%; min-height: 200px; border-radius: 10px; overflow: hidden; background: #0f172a; box-sizing: border-box; }
        .builder-video-wrap iframe, .builder-video-wrap video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; object-fit: contain; }
        .builder-video-wrap .video-fallback-link { position: absolute; top: 8px; right: 8px; z-index: 2; font-size: 11px; color: rgba(255,255,255,0.8); background: rgba(0,0,0,0.5); padding: 4px 8px; border-radius: 6px; text-decoration: none; }
        .builder-video-wrap video { z-index: 1; }
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
        $layoutHasForm = false;
        if ($hasBuilderLayout) {
            foreach ($layout['sections'] ?? [] as $section) {
                foreach ($section['rows'] ?? [] as $row) {
                    foreach ($row['columns'] ?? [] as $column) {
                        foreach ($column['elements'] ?? [] as $element) {
                            if (($element['type'] ?? '') === 'form') {
                                $layoutHasForm = true;
                                break 4;
                            }
                        }
                    }
                }
            }
        }

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
                'maxWidth' => 'max-width',
                'minWidth' => 'min-width',
                'maxHeight' => 'max-height',
                'minHeight' => 'min-height',
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
                if (!preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $value)) {
                    continue;
                }
                $out[] = $cssProp . ':' . $value;
            }

            return implode(';', $out);
        };
    @endphp

    <div class="wrap">
        @if($isPreview)
        <div style="margin-bottom: 10px; padding: 0 2rem; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
            <a class="btn secondary" href="{{ route('funnels.edit', $funnel) }}" style="padding:8px 14px; box-shadow:none;">
                <i class="fas fa-arrow-left"></i> Back to Builder
            </a>
            <span class="preview-badge"><i class="fas fa-eye"></i> Preview Mode</span>
        </div>
        @endif

        <div class="step-content--full">
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
                                                if ($src === '' && $type === 'video' && ($content !== '' && (str_starts_with(trim($content), 'http') || str_starts_with(trim($content), '/')))) {
                                                    $src = trim($content);
                                                }
                                                $alt = trim((string) ($settings['alt'] ?? 'Image'));
                                                $alignment = $settings['alignment'] ?? 'left';
                                                $alignStyle = 'display:flex;justify-content:' . ($alignment === 'right' ? 'flex-end' : ($alignment === 'center' ? 'center' : 'flex-start')) . ';';
                                                $widthBehavior = $settings['widthBehavior'] ?? 'fluid';
                                                $btnWrapStyle = ($type === 'button' ? $alignStyle : '');
                                                $btnInnerStyle = $style . ($type === 'button' && $widthBehavior === 'fill' ? (($style !== '' ? ';' : '') . ' width:100%;display:block;box-sizing:border-box;text-align:center;') : '');
                                            @endphp

                                            <div class="builder-el" @if($type === 'image' || $type === 'video' || $type === 'button' || $type === 'form') style="{{ $type === 'button' ? $btnWrapStyle : $alignStyle }}" @endif>
                                                @if($type === 'heading')
                                                    <h2 class="builder-heading" style="{{ $style }}">{!! $content !!}</h2>
                                                @elseif($type === 'text')
                                                    <p class="builder-text" style="{{ $style }}">{!! $content !!}</p>
                                                @elseif($type === 'image')
                                                    @if($src !== '')
                                                        <img class="builder-img" src="{{ $src }}" alt="{{ $alt !== '' ? $alt : 'Image' }}" style="{{ $style }}">
                                                    @endif
                                                @elseif($type === 'button')
                                                    <a class="btn" href="{{ $link !== '' ? $link : '#' }}" style="{{ $btnInnerStyle }}">{!! $content !== '' ? $content : 'Button' !!}</a>
                                                @elseif($type === 'video')
                                                    @if($src !== '')
                                                        @php
                                                            $src = trim($src);
                                                            if (!str_starts_with($src, 'http')) {
                                                                $src = 'https://' . ltrim($src, '/');
                                                            }
                                                            $videoEmbedUrl = $src;
                                                            $isYoutubeVimeo = str_contains($src, 'youtube.com') || str_contains($src, 'youtu.be') || str_contains($src, 'vimeo.com');
                                                            if (str_contains($src, 'youtube.com/watch')) {
                                                                parse_str(parse_url($src, PHP_URL_QUERY) ?: '', $yt);
                                                                $videoEmbedUrl = isset($yt['v']) ? 'https://www.youtube.com/embed/' . $yt['v'] : $src;
                                                            } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]+)#', $src, $m)) {
                                                                $videoEmbedUrl = 'https://www.youtube.com/embed/' . $m[1];
                                                            } elseif (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $src, $m)) {
                                                                $videoEmbedUrl = 'https://player.vimeo.com/video/' . $m[1];
                                                            }
                                                            $videoSrc = $isYoutubeVimeo ? $videoEmbedUrl : (str_starts_with($src, 'http') ? $src : asset(ltrim($src, '/')));
                                                            $videoWrapStyle = $style;
                                                            $elStyle = is_array($element['style'] ?? null) ? $element['style'] : [];
                                                            $elSettings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                                                            $widthVal = !empty($elStyle['width']) ? trim((string) $elStyle['width']) : (!empty($elSettings['width']) ? trim((string) $elSettings['width']) : '');
                                                            if ($widthVal !== '' && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $widthVal)) {
                                                                $videoWrapStyle .= ($videoWrapStyle !== '' ? '; ' : '') . 'width: ' . $widthVal . ' !important';
                                                            }
                                                            if (!empty($elStyle['height']) && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', trim((string) $elStyle['height']))) {
                                                                $videoWrapStyle .= ($videoWrapStyle !== '' ? '; ' : '') . 'height: ' . trim((string) $elStyle['height']) . ' !important';
                                                            }
                                                            if (!empty($elStyle['maxWidth']) && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', trim((string) $elStyle['maxWidth']))) {
                                                                $videoWrapStyle .= ($videoWrapStyle !== '' ? '; ' : '') . 'max-width: ' . trim((string) $elStyle['maxWidth']) . ' !important';
                                                            }
                                                        @endphp
                                                        <div class="builder-video-wrap" style="{{ $videoWrapStyle }}">
                                                            @if($isYoutubeVimeo)
                                                                <iframe src="{{ $videoEmbedUrl }}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" loading="lazy"></iframe>
                                                            @else
                                                                <video src="{{ $videoSrc }}" controls playsinline preload="metadata"></video>
                                                            @endif
                                                            <a href="{{ $videoSrc }}" target="_blank" rel="noopener" class="video-fallback-link">Open video</a>
                                                        </div>
                                                    @endif
                                                @elseif($type === 'spacer')
                                                    <div style="{{ $style ?: 'height:24px' }}"></div>
                                                @elseif($type === 'countdown')
                                                    {{-- Countdown component removed --}}
                                                @elseif($type === 'form')
                                                    @php
                                                        $formFields = is_array($element['settings']['fields'] ?? null) && count($element['settings']['fields']) > 0
                                                            ? $element['settings']['fields']
                                                            : [['type' => 'first_name', 'label' => 'First name'], ['type' => 'last_name', 'label' => 'Last name'], ['type' => 'email', 'label' => 'Email'], ['type' => 'phone_number', 'label' => 'Phone (09XXXXXXXXX)']];
                                                        $formWidthFromStyle = trim((string) ($element['style']['width'] ?? ''));
                                                        $formWidthFromSettings = trim((string) ($element['settings']['width'] ?? ''));
                                                        $resolvedFormWidth = $formWidthFromStyle !== '' ? $formWidthFromStyle : $formWidthFromSettings;
                                                        $formWrapStyle = $style . ($style !== '' ? ';' : '') . 'display:flex;flex-direction:column;' . ($resolvedFormWidth !== '' ? ';width:' . preg_replace('/[^#(),.%\-\sA-Za-z0-9]/', '', $resolvedFormWidth) . ';' : ';width:100%;');
                                                        $inputWidth = trim((string) ($element['settings']['inputWidth'] ?? ''));
                                                        $inputPadding = trim((string) ($element['settings']['inputPadding'] ?? ''));
                                                        $inputFontSize = trim((string) ($element['settings']['inputFontSize'] ?? ''));
                                                        if ($inputWidth === '') {
                                                            $inputWidth = '100%';
                                                        }
                                                        $safe = function ($v) { return preg_replace('/[^#(),.%\-\sA-Za-z0-9]/', '', $v); };
                                                        $inputStyleParts = array_filter([
                                                            'width:' . $safe($inputWidth) . ' !important',
                                                            $inputPadding !== '' ? 'padding:' . $safe($inputPadding) . ' !important' : null,
                                                            $inputFontSize !== '' ? 'font-size:' . $safe($inputFontSize) . ' !important' : null,
                                                        ]);
                                                        $inputStyleValue = implode(';', $inputStyleParts) . ';box-sizing:border-box;';
                                                    $formClass = 'builder-form';
                                                    @endphp
                                                    @if($step->type === 'opt_in')
                                                        @if($isPreview)
                                                            <div class="{{ $formClass }}" style="{{ $formWrapStyle }}">
                                                                @foreach($formFields as $f)
                                                                    <div class="builder-form-field-wrap"><label>{{ $f['label'] ?? $f['type'] ?? 'Field' }}</label>
                                                                    <input type="text" disabled placeholder="Preview" style="{{ $inputStyleValue }}"></div>
                                                                @endforeach
                                                                <button type="button" class="btn" disabled style="opacity:0.7;align-self:flex-start;width:auto;">{{ $content !== '' ? $content : ($step->cta_label ?: 'Submit') }} (preview)</button>
                                                            </div>
                                                        @else
                                                            <form class="{{ $formClass }}" method="POST" action="{{ route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}" style="{{ $formWrapStyle }}">
                                                                @csrf
                                                                @foreach($formFields as $f)
                                                                    @php
                                                                        $ft = $f['type'] ?? 'custom';
                                                                        $lbl = $f['label'] ?? $ft;
                                                                        $nm = in_array($ft, ['first_name', 'last_name', 'email', 'phone_number', 'country', 'city'], true) ? $ft : 'custom_' . $loop->index;
                                                                        $req = in_array($ft, ['email', 'phone_number'], true);
                                                                        $inputType = $ft === 'email' ? 'email' : 'text';
                                                                        $pat = $ft === 'phone_number' ? 'pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"' : '';
                                                                    @endphp
                                                                    <div class="builder-form-field-wrap"><label>{{ $lbl }}</label>
                                                                    <input type="{{ $inputType }}" name="{{ $nm }}" {{ $req ? 'required' : '' }} {!! $pat !!} placeholder="{{ $lbl }}" style="{{ $inputStyleValue }}"></div>
                                                                @endforeach
                                                                <button type="submit" class="btn" style="align-self:flex-start;width:auto;">{{ $content !== '' ? $content : ($step->cta_label ?: 'Submit and Continue') }}</button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        <form class="{{ $formClass }}" onsubmit="return false;" style="{{ $formWrapStyle }}">
                                                            @foreach($formFields as $f)
                                                                <div class="builder-form-field-wrap"><label>{{ $f['label'] ?? $f['type'] ?? 'Field' }}</label>
                                                                <input type="text" placeholder="{{ $f['label'] ?? '' }}" style="{{ $inputStyleValue }}"></div>
                                                            @endforeach
                                                            <button type="button" class="btn" style="align-self:flex-start;width:auto;">{{ $content !== '' ? $content : 'Submit' }}</button>
                                                        </form>
                                                    @endif
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
                    @include('funnels.portal._step-actions', ['funnel' => $funnel, 'step' => $step, 'nextStep' => $nextStep, 'isPreview' => $isPreview, 'showOptInForm' => !$layoutHasForm])
                </div>
            @else
                <h2>{{ $step->title }}</h2>
                @if($step->subtitle)
                    <h3 class="subtitle">{{ $step->subtitle }}</h3>
                @endif
                <div class="content">{{ $step->content ?: 'No content configured for this step yet.' }}</div>
                @include('funnels.portal._step-actions', ['funnel' => $funnel, 'step' => $step, 'nextStep' => $nextStep, 'isPreview' => $isPreview, 'showOptInForm' => true])
            @endif
        </div>
    </div>
</body>
</html>
