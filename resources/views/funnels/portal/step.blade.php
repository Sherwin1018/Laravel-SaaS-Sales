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
        label { font-weight: 700; font-size: 13px; margin-bottom: 6px; display: block; }
        .price { font-size: 34px; font-weight: 800; color: #047857; margin: 0 0 12px; }
        .row { display: flex; gap: 10px; flex-wrap: wrap; }
        .builder-section { border-radius: 14px; margin-bottom: 14px; border: none; }
        .builder-section-inner { width: 100%; box-sizing: border-box; }
        .builder-row-inner { width: 100%; box-sizing: border-box; }
        .builder-col-inner { width: 100%; box-sizing: border-box; }
        .builder-row { display: flex; gap: 12px; flex-wrap: wrap; padding: 6px; }
        .builder-col { min-width: 240px; min-height: 24px; flex: 1 1 0; }
        .builder-el + .builder-el { margin-top: 10px; }
        .builder-heading { margin: 0; font-size: 32px; line-height: 1.2; overflow-wrap: break-word; word-break: break-word; }
        .builder-text { margin: 0; color: #334155; line-height: 1.6; white-space: pre-wrap; overflow-wrap: break-word; word-break: break-word; }
        .builder-img { display: block; max-width: 100%; height: auto; border-radius: 10px; }
        .builder-menu { width: 100%; }
        .builder-menu-list { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; }
        .builder-menu-link { text-decoration: none; text-underline-offset: 3px; font: inherit; }
        .builder-carousel-wrap { position: relative; min-height: 180px; border-radius: 10px; overflow: hidden; color: #fff; background: linear-gradient(135deg, #0ea5e9, #0284c7); border: 1px solid #000; }
        .builder-carousel-track { display: flex; width: 100%; transition: transform .35s ease; }
        .builder-carousel-slide { width: 100%; flex: 0 0 100%; padding: 16px; box-sizing: border-box; display: flex; flex-direction: column; }
        .builder-carousel-title { font-size: 22px; font-weight: 700; text-align: center; line-height: 1.2; margin-bottom: 8px; }
        .builder-carousel-content-row { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
        .builder-carousel-content-col { min-width: 200px; flex: 1 1 0; }
        .builder-carousel-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; line-height: 1; box-shadow: 0 6px 14px rgba(2, 6, 23, 0.18); border: 0; cursor: pointer; z-index: 3; }
        .builder-carousel-arrow.is-left { left: 12px; }
        .builder-carousel-arrow.is-right { right: 12px; }
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
                'backgroundImage' => 'background-image',
                'backgroundSize' => 'background-size',
                'backgroundPosition' => 'background-position',
                'backgroundRepeat' => 'background-repeat',
                'backgroundAttachment' => 'background-attachment',
                'justifyContent' => 'justify-content',
                'alignItems' => 'align-items',
                'gap' => 'gap',
                'lineHeight' => 'line-height',
                'letterSpacing' => 'letter-spacing',
                'textDecorationColor' => 'text-decoration-color',
                'textDecoration' => 'text-decoration',
            ];

            $out = [];
            foreach ($allowed as $key => $cssProp) {
                $value = trim((string) ($style[$key] ?? ''));
                if ($value === '') {
                    continue;
                }
                if ($key === 'backgroundImage') {
                    if (!preg_match('/^url\(((https?:\/\/|\/)[^\s)]+)\)$/i', $value)) {
                        continue;
                    }
                    $out[] = $cssProp . ':' . $value;
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
                        $sectionSettings = is_array($section['settings'] ?? null) ? $section['settings'] : [];
                        $contentWidth = trim((string) ($sectionSettings['contentWidth'] ?? 'full'));
                        $widthMap = ['full' => '', 'wide' => '1200px', 'medium' => '992px', 'small' => '768px', 'xsmall' => '576px'];
                        $innerMax = $widthMap[$contentWidth] ?? '';
                        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                    @endphp
                    <section class="builder-section" style="{{ $sectionStyle }}">
                        <div class="builder-section-inner" @if($innerMax !== '') style="max-width: {{ $innerMax }}; margin: 0 auto;" @endif>
                        @foreach($rows as $row)
                            @php
                                $rowStyle = $styleToString(is_array($row['style'] ?? null) ? $row['style'] : []);
                                $rowSettings = is_array($row['settings'] ?? null) ? $row['settings'] : [];
                                $rowContentWidth = trim((string) ($rowSettings['contentWidth'] ?? 'full'));
                                $rowInnerMax = $widthMap[$rowContentWidth] ?? '';
                                $columns = is_array($row['columns'] ?? null) ? $row['columns'] : [];
                            @endphp
                            <div class="builder-row" style="{{ $rowStyle }}">
                                <div class="builder-row-inner" @if($rowInnerMax !== '') style="max-width: {{ $rowInnerMax }}; margin: 0 auto;" @endif>
                                @foreach($columns as $column)
                                    @php
                                        $colStyle = $styleToString(is_array($column['style'] ?? null) ? $column['style'] : []);
                                        $colSettings = is_array($column['settings'] ?? null) ? $column['settings'] : [];
                                        $colContentWidth = trim((string) ($colSettings['contentWidth'] ?? 'full'));
                                        $colInnerMax = $widthMap[$colContentWidth] ?? '';
                                        $elements = is_array($column['elements'] ?? null) ? $column['elements'] : [];
                                    @endphp
                                    <div class="builder-col" style="{{ $colStyle }}">
                                        <div class="builder-col-inner" @if($colInnerMax !== '') style="max-width: {{ $colInnerMax }}; margin: 0 auto;" @endif>
                                        @foreach($elements as $element)
                                            @php
                                                $type = $element['type'] ?? 'text';
                                                $content = (string) ($element['content'] ?? '');
                                                $style = $styleToString(is_array($element['style'] ?? null) ? $element['style'] : []);
                                                $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                                                $link = trim((string) ($settings['link'] ?? '#'));
                                                $src = trim((string) ($settings['src'] ?? ''));
                                                $alt = trim((string) ($settings['alt'] ?? 'Image'));
                                                $alignment = $settings['alignment'] ?? 'left';
                                                $alignStyle = 'display:flex;justify-content:' . ($alignment === 'right' ? 'flex-end' : ($alignment === 'center' ? 'center' : 'flex-start')) . ';';
                                                $menuAlign = $settings['menuAlign'] ?? 'left';
                                                $menuAlignStyle = 'display:flex;justify-content:' . ($menuAlign === 'right' ? 'flex-end' : ($menuAlign === 'center' ? 'center' : 'flex-start')) . ';';
                                                $widthBehavior = $settings['widthBehavior'] ?? 'fluid';
                                                $btnWrapStyle = ($type === 'button' ? $alignStyle : '');
                                                $btnInnerStyle = $style . ($type === 'button' && $widthBehavior === 'fill' ? (($style !== '' ? ';' : '') . ' width:100%;display:block;box-sizing:border-box;text-align:center;') : '');
                                            @endphp

                                            <div class="builder-el" @if($type === 'image' || $type === 'video' || $type === 'button') style="{{ $type === 'button' ? $btnWrapStyle : $alignStyle }}" @endif>
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
                                                                @php
                                                                    $qs = [];
                                                                    if (($settings['autoplay'] ?? false) === true) {
                                                                        $qs[] = 'autoplay=1';
                                                                    }
                                                                    if (($settings['controls'] ?? true) === false) {
                                                                        $qs[] = 'controls=0';
                                                                    }
                                                                    $embedSrc = $videoEmbedUrl . (str_contains($videoEmbedUrl, '?') ? '&' : '?') . implode('&', $qs);
                                                                    $embedSrc = rtrim($embedSrc, '?&');
                                                                @endphp
                                                                <iframe src="{{ $embedSrc }}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" loading="lazy"></iframe>
                                                            @else
                                                                <video src="{{ $videoSrc }}" @if(($settings['controls'] ?? true) !== false) controls @endif @if(($settings['autoplay'] ?? false) === true) autoplay muted @endif playsinline preload="metadata"></video>
                                                            @endif
                                                            <a href="{{ $videoSrc }}" target="_blank" rel="noopener" class="video-fallback-link">Open video</a>
                                                        </div>
                                                    @endif
                                                @elseif($type === 'menu')
                                                    @php
                                                        $menuItems = is_array($settings['items'] ?? null) ? $settings['items'] : [];
                                                        if (count($menuItems) === 0) {
                                                            $menuItems = [
                                                                ['label' => 'Home', 'url' => '#', 'newWindow' => false],
                                                                ['label' => 'Contact', 'url' => '/contact', 'newWindow' => false],
                                                            ];
                                                        }
                                                        $itemGap = max(0, min(300, (int) ($settings['itemGap'] ?? 13)));
                                                        $activeIndex = max(0, min(500, (int) ($settings['activeIndex'] ?? 0)));
                                                        $menuText = trim((string) ($settings['textColor'] ?? '#374151'));
                                                        $menuActive = trim((string) ($settings['activeColor'] ?? '#a89c76'));
                                                        $menuUnderline = trim((string) ($settings['underlineColor'] ?? ''));
                                                    @endphp
                                                    <nav class="builder-menu" style="{{ $menuAlignStyle }}{{ $style !== '' ? $style : '' }}">
                                                        <ul class="builder-menu-list" style="gap: {{ $itemGap }}px;">
                                                            @foreach($menuItems as $i => $menuItem)
                                                                @php
                                                                    $menuLabel = trim((string) ($menuItem['label'] ?? 'Menu item ' . ($i + 1)));
                                                                    $menuHref = trim((string) ($menuItem['url'] ?? '#'));
                                                                    $menuNew = (bool) ($menuItem['newWindow'] ?? false);
                                                                    $linkColor = $i === $activeIndex ? $menuActive : $menuText;
                                                                    $decoStyle = $menuUnderline !== '' ? 'text-decoration:underline;text-decoration-color:' . $menuUnderline . ';' : 'text-decoration:none;';
                                                                @endphp
                                                                <li>
                                                                    <a class="builder-menu-link" href="{{ $menuHref !== '' ? $menuHref : '#' }}" @if($menuNew) target="_blank" rel="noopener" @endif style="color: {{ $linkColor }}; {{ $decoStyle }}">{{ $menuLabel !== '' ? $menuLabel : ('Menu item ' . ($i + 1)) }}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </nav>
                                                @elseif($type === 'carousel')
                                                    @php
                                                        $hasRenderableCarouselElement = function (array $carouselElement): bool {
                                                            $carouselType = (string) ($carouselElement['type'] ?? 'text');
                                                            $carouselSettings = is_array($carouselElement['settings'] ?? null) ? $carouselElement['settings'] : [];
                                                            if ($carouselType === 'image' || $carouselType === 'video') {
                                                                return trim((string) ($carouselSettings['src'] ?? '')) !== '';
                                                            }
                                                            if ($carouselType === 'heading' || $carouselType === 'text' || $carouselType === 'button') {
                                                                return trim(strip_tags((string) ($carouselElement['content'] ?? ''))) !== '';
                                                            }
                                                            return true;
                                                        };
                                                        $slides = is_array($settings['slides'] ?? null) ? $settings['slides'] : [];
                                                        if (count($slides) === 0) {
                                                            $slides = [['label' => 'Slide #1']];
                                                        }
                                                        $isCarouselEmpty = true;
                                                        foreach ($slides as $slideCheck) {
                                                            $checkRows = is_array($slideCheck['rows'] ?? null) ? $slideCheck['rows'] : [];
                                                            foreach ($checkRows as $checkRow) {
                                                                $checkCols = is_array($checkRow['columns'] ?? null) ? $checkRow['columns'] : [];
                                                                foreach ($checkCols as $checkCol) {
                                                                    $checkEls = is_array($checkCol['elements'] ?? null) ? $checkCol['elements'] : [];
                                                                    foreach ($checkEls as $checkEl) {
                                                                        if ($hasRenderableCarouselElement(is_array($checkEl) ? $checkEl : [])) {
                                                                            $isCarouselEmpty = false;
                                                                            break 4;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        $activeSlide = max(0, min(count($slides) - 1, (int) ($settings['activeSlide'] ?? 0)));
                                                        $vAlign = $settings['vAlign'] ?? 'center';
                                                        $aItems = $vAlign === 'top' ? 'flex-start' : ($vAlign === 'bottom' ? 'flex-end' : 'center');
                                                        $showArrows = ($settings['showArrows'] ?? true) !== false;
                                                        $controlsColor = trim((string) ($settings['controlsColor'] ?? '#64748b'));
                                                        $arrowColor = trim((string) ($settings['arrowColor'] ?? '#ffffff'));
                                                        $bodyBgColor = trim((string) ($settings['bodyBgColor'] ?? ''));
                                                        $carouselId = 'car_' . md5((string) ($element['id'] ?? uniqid('', true)));
                                                    @endphp
                                                    <div class="builder-carousel-wrap" data-carousel id="{{ $carouselId }}" data-active="{{ $activeSlide }}" style="{{ $style }} background:#ffffff !important; background-image:none !important; color:#0f172a;">
                                                        <div class="builder-carousel-track" data-carousel-track>
                                                            @foreach($slides as $si => $slide)
                                                                @php
                                                                    $slideTitle = trim((string) ($slide['label'] ?? ('Slide #' . ($si + 1))));
                                                                    $showSlideTitle = $slideTitle !== '' && !preg_match('/^Slide\s*#\s*\d+$/i', $slideTitle);
                                                                    $slideRows = is_array($slide['rows'] ?? null) ? $slide['rows'] : [];
                                                                    $isSlideEmpty = true;
                                                                    foreach ($slideRows as $slideRowCheck) {
                                                                        $slideColsCheck = is_array($slideRowCheck['columns'] ?? null) ? $slideRowCheck['columns'] : [];
                                                                        foreach ($slideColsCheck as $slideColCheck) {
                                                                            $slideElsCheck = is_array($slideColCheck['elements'] ?? null) ? $slideColCheck['elements'] : [];
                                                                            foreach ($slideElsCheck as $slideElCheck) {
                                                                                if ($hasRenderableCarouselElement(is_array($slideElCheck) ? $slideElCheck : [])) {
                                                                                    $isSlideEmpty = false;
                                                                                    break 3;
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <div class="builder-carousel-slide" style="align-items: {{ $aItems }}; background:#ffffff !important;">
                                                                    @if($isSlideEmpty)
                                                                        <div style="min-height:140px; width:100%; display:flex; align-items:center; justify-content:center;">
                                                                            <span style="font-size:13px; font-weight:700; color:#64748b;">Carousel is Empty</span>
                                                                        </div>
                                                                    @else
                                                                        @if($showSlideTitle)
                                                                            <div class="builder-carousel-title">{{ $slideTitle }}</div>
                                                                        @endif
                                                                        @if(count($slideRows) > 0)
                                                                            @foreach($slideRows as $srow)
                                                                                @php
                                                                                    $srowStyle = $styleToString(is_array($srow['style'] ?? null) ? $srow['style'] : []);
                                                                                    $scols = is_array($srow['columns'] ?? null) ? $srow['columns'] : [];
                                                                                @endphp
                                                                                <div class="builder-carousel-content-row" style="{{ $srowStyle }}">
                                                                                    @foreach($scols as $scol)
                                                                                        @php
                                                                                            $scolStyle = $styleToString(is_array($scol['style'] ?? null) ? $scol['style'] : []);
                                                                                            $sels = is_array($scol['elements'] ?? null) ? $scol['elements'] : [];
                                                                                        @endphp
                                                                                        <div class="builder-carousel-content-col" style="{{ $scolStyle }}">
                                                                                            @foreach($sels as $sel)
                                                                                                @php
                                                                                                    $st = (string) ($sel['type'] ?? 'text');
                                                                                                    $ssc = is_array($sel['settings'] ?? null) ? $sel['settings'] : [];
                                                                                                    $ss = $styleToString(is_array($sel['style'] ?? null) ? $sel['style'] : []);
                                                                                                    $scontent = (string) ($sel['content'] ?? '');
                                                                                                @endphp
                                                                                                @if($st === 'heading')
                                                                                                    <h3 class="builder-heading" style="{{ $ss }}">{{ strip_tags($scontent) }}</h3>
                                                                                                @elseif($st === 'text')
                                                                                                    <p class="builder-text" style="{{ $ss }}">{{ $scontent }}</p>
                                                                                                @elseif($st === 'image')
                                                                                                    @php
                                                                                                        $img = trim((string) ($ssc['src'] ?? ''));
                                                                                                        $alt = trim((string) ($ssc['alt'] ?? 'Image'));
                                                                                                    @endphp
                                                                                                    @if($img !== '')
                                                                                                        <img class="builder-img" src="{{ $img }}" alt="{{ $alt }}" style="{{ $ss }}">
                                                                                                    @endif
                                                                                                @elseif($st === 'video')
                                                                                                    @php
                                                                                                        $videoSrc = trim((string) ($ssc['src'] ?? ''));
                                                                                                    @endphp
                                                                                                    @if($videoSrc !== '')
                                                                                                        <div class="builder-video-wrap" style="{{ $ss }}">
                                                                                                            <video src="{{ $videoSrc }}" controls playsinline preload="metadata"></video>
                                                                                                            <a href="{{ $videoSrc }}" target="_blank" rel="noopener" class="video-fallback-link">Open video</a>
                                                                                                        </div>
                                                                                                    @endif
                                                                                                @elseif($st === 'button')
                                                                                                    @php
                                                                                                        $href = trim((string) ($ssc['link'] ?? '#'));
                                                                                                    @endphp
                                                                                                    <a href="{{ $href !== '' ? $href : '#' }}" class="btn" style="{{ $ss }}">{{ strip_tags($scontent) !== '' ? strip_tags($scontent) : 'Click' }}</a>
                                                                                                @endif
                                                                                            @endforeach
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            @endforeach
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @if($showArrows)
                                                            <button type="button" class="builder-carousel-arrow is-left" data-carousel-prev style="background: {{ $controlsColor }}; color: {{ $arrowColor }};">&lsaquo;</button>
                                                            <button type="button" class="builder-carousel-arrow is-right" data-carousel-next style="background: {{ $controlsColor }}; color: {{ $arrowColor }};">&rsaquo;</button>
                                                        @endif
                                                    </div>
                                                @elseif($type === 'spacer')
                                                    <div style="{{ $style ?: 'height:24px' }}"></div>
                                                @elseif($type === 'countdown')
                                                    {{-- Countdown component removed --}}
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
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        @endforeach
                        </div>
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
    <script>
    (function(){
        var carousels=document.querySelectorAll("[data-carousel]");
        carousels.forEach(function(car){
            var track=car.querySelector("[data-carousel-track]");
            if(!track)return;
            var slides=track.children;
            var total=slides.length||1;
            var index=parseInt(car.getAttribute("data-active")||"0",10);
            if(isNaN(index)||index<0||index>=total)index=0;
            function paint(){
                track.style.transform="translateX(" + (-index*100) + "%)";
            }
            var prev=car.querySelector("[data-carousel-prev]");
            var next=car.querySelector("[data-carousel-next]");
            if(prev)prev.addEventListener("click",function(e){e.preventDefault();index=(index-1+total)%total;paint();});
            if(next)next.addEventListener("click",function(e){e.preventDefault();index=(index+1)%total;paint();});
            paint();
        });
    })();
    </script>
</body>
</html>
