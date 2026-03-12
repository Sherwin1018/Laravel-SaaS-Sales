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
        html { scroll-behavior: smooth; }
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
            overflow-x: auto;
            overflow-y: visible;
        }
        body.is-published .step-content--full { padding-top: 0; }
        body.is-preview .step-content--full { padding: 10px; overflow-x: hidden; }
        body.is-preview .builder-section--freeform { margin: 0; width: 100%; }
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
            box-shadow: none;
        }
        .btn.secondary { background: #1e40af; }
        .btn.gray { background: #64748b; }
        .btn { overflow-wrap: break-word; word-break: break-word; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 10px; }
        label { font-weight: 700; font-size: 13px; margin-bottom: 6px; display: block; }
        .builder-form-input::placeholder { color: var(--fb-placeholder-color, #94a3b8); opacity: 1; }
        .price { font-size: 34px; font-weight: 800; color: #047857; margin: 0 0 12px; }
        .row { display: flex; gap: 10px; flex-wrap: wrap; }
        .builder-section { border-radius: 14px; margin-bottom: 14px; border: none; }
        .builder-section--freeform { border-radius: 0; padding: 0; margin: 0 -2rem; background: transparent; border: none; width: calc(100% + 4rem); max-width: none; }
        .builder-section--freeform .builder-row { padding: 0; gap: 0; margin: 0; display: block; }
        .builder-section--freeform .builder-row-inner { display: block; gap: 0; }
        .builder-section--freeform .builder-col { overflow: visible; background: transparent; min-width: 0; min-height: 0; padding: 0; margin: 0 auto; }
        .builder-section--freeform .builder-col-inner { overflow: visible; position: relative; }
        .builder-section--freeform .builder-el { margin-top: 0 !important; }
        .builder-section-inner { width: 100%; box-sizing: border-box; position: relative; }
        .builder-row-inner { width: 100%; box-sizing: border-box; display: flex; flex-wrap: wrap; gap: 8px; }
        .builder-col-inner { width: 100%; box-sizing: border-box; max-width: 100%; overflow: hidden; position: relative; }
        .builder-row { display: flex; gap: 8px; flex-wrap: wrap; padding: 6px; }
        .builder-col { min-width: 240px; min-height: 24px; flex: 1 1 0; position: relative; overflow: hidden; background: #ffffff; }
        .builder-col > .builder-col-inner > .builder-el { max-width: 100%; overflow: hidden; }
        .builder-el + .builder-el { margin-top: 10px; }
        .builder-heading { margin: 0; font-size: 32px; line-height: 1.2; overflow-wrap: break-word; word-break: break-word; }
        .builder-text { margin: 0; color: #334155; line-height: normal; white-space: pre-wrap; overflow-wrap: break-word; word-break: break-word; }
        .builder-text p,
        .builder-text div,
        .builder-text ul,
        .builder-text ol,
        .builder-text li { margin: 0; }
        .builder-img { display: block; max-width: 100%; height: auto; border-radius: 10px; object-fit: contain; object-position: top center; }
        .builder-menu { width: 100%; }
        .builder-menu-list { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; }
        .builder-menu-link { text-decoration: none; text-underline-offset: 3px; font: inherit; }
        .builder-carousel-wrap { position: relative; min-height: 180px; border-radius: 10px; overflow: hidden; color: #fff; background: linear-gradient(135deg, #0ea5e9, #0284c7); border: 0; }
        .builder-carousel-track { display: flex; width: 100%; height: 100%; transition: transform .35s ease; }
        .builder-carousel-slide { width: 100%; height: 100%; flex: 0 0 100%; padding: 0; box-sizing: border-box; display: flex; flex-direction: column; }
        .builder-carousel-title { font-size: 22px; font-weight: 700; text-align: center; line-height: 1.2; margin-bottom: 8px; }
        .builder-carousel-content-row { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
        .builder-carousel-content-col { min-width: 200px; flex: 1 1 0; }
        .builder-carousel-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border-radius: 999px; display: flex; align-items: center; justify-content: center; padding: 0; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.26); border: 1px solid rgba(255,255,255,0.28); cursor: pointer; z-index: 3; transition: transform .16s ease, box-shadow .2s ease, opacity .2s ease; }
        .builder-carousel-arrow i { font-size: 16px; line-height: 1; display: block; }
        .builder-carousel-arrow:hover { transform: translateY(-50%) scale(1.05); box-shadow: 0 12px 28px rgba(15, 23, 42, 0.30); }
        .builder-carousel-arrow:active { transform: translateY(-50%) scale(0.97); }
        .builder-carousel-arrow:focus-visible { outline: 2px solid #dbeafe; outline-offset: 2px; }
        .builder-carousel-arrow.is-left { left: 16px; }
        .builder-carousel-arrow.is-right { right: 16px; }
        .builder-carousel-dots {
            position: absolute;
            left: 50%;
            bottom: 10px;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 4;
        }
        .builder-carousel-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            padding: 0;
            border: 1px solid rgba(255,255,255,0.8);
            background: rgba(255,255,255,0.45);
            cursor: pointer;
        }
        .builder-carousel-dot.is-active {
            background: #ffffff;
            border-color: #ffffff;
            box-shadow: 0 0 0 2px rgba(15,23,42,0.15);
        }
        @media (max-width: 640px) {
            .builder-carousel-arrow { width: 36px; height: 36px; font-size: 20px; }
            .builder-carousel-arrow.is-left { left: 10px; }
            .builder-carousel-arrow.is-right { right: 10px; }
        }
        .builder-video-wrap { position: relative; width: 100%; padding-top: 56.25%; min-height: 200px; border-radius: 10px; overflow: hidden; background: #0f172a; box-sizing: border-box; }
        .builder-video-wrap iframe, .builder-video-wrap video { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; object-fit: contain; }
        .builder-video-wrap .video-fallback-link { position: absolute; top: 8px; right: 8px; z-index: 2; font-size: 11px; color: rgba(255,255,255,0.8); background: rgba(0,0,0,0.5); padding: 4px 8px; border-radius: 6px; text-decoration: none; }
        .builder-video-wrap video { z-index: 1; }
        .preview-badge {
            display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px;
            border-radius: 999px; font-size: 12px; font-weight: 800; color: #1d4ed8; background: #dbeafe;
        }
        .preview-toolbar {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            z-index: 10;
            margin: 12px;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
    </style>
</head>
<body class="{{ ($isPreview ?? false) ? 'is-preview' : 'is-published' }}">
    @php
        $isPreview = $isPreview ?? false;
        $layout = is_array($step->layout_json ?? null) ? $step->layout_json : [];
        $rootItems = is_array($layout['root'] ?? null) ? $layout['root'] : [];
        if (count($rootItems) === 0 && is_array($layout['sections'] ?? null)) {
            foreach ($layout['sections'] as $legacySection) {
                if (!is_array($legacySection)) {
                    continue;
                }
                $rootItems[] = array_merge(['kind' => 'section'], $legacySection);
            }
        }
        $renderSections = [];
        $freeformEls = [];
        foreach ($rootItems as $ri => $rootItem) {
            if (!is_array($rootItem)) {
                continue;
            }
            $kind = strtolower((string) ($rootItem['kind'] ?? 'section'));
            if ($kind === 'section') {
                $renderSections[] = $rootItem;
                continue;
            }
            if ($kind === 'row') {
                $renderSections[] = [
                    'id' => 'sec_root_row_' . $ri,
                    'style' => [],
                    'settings' => ['contentWidth' => 'full'],
                    'elements' => [],
                    'rows' => [$rootItem],
                ];
                continue;
            }
            if ($kind === 'column' || $kind === 'col') {
                $renderSections[] = [
                    'id' => 'sec_root_col_' . $ri,
                    'style' => [],
                    'settings' => ['contentWidth' => 'full'],
                    'elements' => [],
                    'rows' => [[
                        'id' => 'row_root_col_' . $ri,
                        'style' => ['gap' => '8px'],
                        'settings' => ['contentWidth' => 'full'],
                        'columns' => [$rootItem],
                    ]],
                ];
                continue;
            }
            $freeformEls[] = $rootItem;
        }
        if (count($freeformEls) > 0) {
            $renderSections[] = [
                'id' => 'sec_freeform_canvas',
                'style' => [],
                'settings' => ['contentWidth' => 'full'],
                'elements' => $freeformEls,
                'rows' => [],
                'isFreeformCanvas' => true,
            ];
        }
        $editorMeta = is_array($layout['__editor'] ?? null) ? $layout['__editor'] : [];
        $canvasWidthRaw = (int) ($editorMeta['canvasWidth'] ?? 0);
        $canvasInnerWidthRaw = (int) ($editorMeta['canvasInnerWidth'] ?? 0);
        $editorCanvasWidth = $canvasInnerWidthRaw > 0 ? $canvasInnerWidthRaw : ($canvasWidthRaw > 0 ? max(0, $canvasWidthRaw - 22) : 0);
        $derivedCanvasWidth = 0;
        foreach ($freeformEls as $ffEl) {
            if (!is_array($ffEl)) continue;
            $ffStyle = is_array($ffEl['style'] ?? null) ? $ffEl['style'] : [];
            $ffSettings = is_array($ffEl['settings'] ?? null) ? $ffEl['settings'] : [];
            $ffLeft = (int) ($ffSettings['freeX'] ?? 0);
            if ($ffLeft <= 0) $ffLeft = (int) str_replace('px', '', (string) ($ffStyle['left'] ?? '0'));
            $ffWidth = (int) str_replace('px', '', (string) ($ffStyle['width'] ?? '0'));
            if ($ffWidth <= 0) $ffWidth = (int) ($ffSettings['fixedWidth'] ?? 0);
            if ($ffWidth <= 0) $ffWidth = 120;
            $ffRight = $ffLeft + $ffWidth + 20;
            if ($ffRight > $derivedCanvasWidth) $derivedCanvasWidth = $ffRight;
        }
        if ($derivedCanvasWidth > $editorCanvasWidth) $editorCanvasWidth = $derivedCanvasWidth;
        $hasBuilderLayout = count($renderSections) > 0;
        $activeSteps = collect($allSteps ?? [])->values()->filter(fn ($s) => isset($s->id, $s->slug));
        $activeStepsBySlug = $activeSteps->keyBy(fn ($s) => strtolower(trim((string) $s->slug)));
        $resolveButtonAction = function (array $settings) use ($funnel, $step, $nextStep, $isPreview, $activeStepsBySlug) {
            $link = trim((string) ($settings['link'] ?? '#'));
            $actionType = strtolower(trim((string) ($settings['actionType'] ?? '')));
            if ($actionType === '') {
                $actionType = ($link !== '' && $link !== '#') ? 'link' : 'next_step';
            }

            if ($actionType === 'next_step') {
                if (!$nextStep) {
                    return ['kind' => 'link', 'href' => '#'];
                }
                if ($isPreview) {
                    return ['kind' => 'link', 'href' => route('funnels.preview', ['funnel' => $funnel, 'step' => $nextStep->id])];
                }
                return ['kind' => 'link', 'href' => route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $nextStep->slug])];
            }

            if ($actionType === 'step') {
                $targetSlug = strtolower(trim((string) ($settings['actionStepSlug'] ?? '')));
                $target = $targetSlug !== '' ? $activeStepsBySlug->get($targetSlug) : null;
                if (!$target) {
                    return ['kind' => 'link', 'href' => '#'];
                }
                if ($isPreview) {
                    return ['kind' => 'link', 'href' => route('funnels.preview', ['funnel' => $funnel, 'step' => $target->id])];
                }
                return ['kind' => 'link', 'href' => route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $target->slug])];
            }

            if ($actionType === 'checkout') {
                if (strtolower(trim((string) ($step->type ?? ''))) !== 'checkout') {
                    return ['kind' => 'link', 'href' => '#'];
                }
                if ($isPreview) {
                    return ['kind' => 'disabled'];
                }
                return [
                    'kind' => 'post',
                    'action' => route('funnels.portal.checkout', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]),
                    'fields' => ['amount' => (float) ($step->price ?? 0)],
                ];
            }

            if ($actionType === 'offer_accept' || $actionType === 'offer_decline') {
                if (!in_array(strtolower(trim((string) ($step->type ?? ''))), ['upsell', 'downsell'], true)) {
                    return ['kind' => 'link', 'href' => '#'];
                }
                if ($isPreview) {
                    return ['kind' => 'disabled'];
                }
                return [
                    'kind' => 'post',
                    'action' => route('funnels.portal.offer', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]),
                    'fields' => ['decision' => $actionType === 'offer_accept' ? 'accept' : 'decline'],
                ];
            }

            return ['kind' => 'link', 'href' => ($link !== '' ? $link : '#')];
        };

        $styleToString = function (array $style): string {
            $allowed = [
                'backgroundColor' => 'background-color',
                'background-color' => 'background-color',
                'background' => 'background',
                'color' => 'color',
                'fontSize' => 'font-size',
                'font-size' => 'font-size',
                'fontWeight' => 'font-weight',
                'font-weight' => 'font-weight',
                'fontFamily' => 'font-family',
                'font-family' => 'font-family',
                'padding' => 'padding',
                'margin' => 'margin',
                'textAlign' => 'text-align',
                'text-align' => 'text-align',
                'borderRadius' => 'border-radius',
                'border-radius' => 'border-radius',
                'border' => 'border',
                'boxShadow' => 'box-shadow',
                'box-shadow' => 'box-shadow',
                'width' => 'width',
                'height' => 'height',
                'maxWidth' => 'max-width',
                'max-width' => 'max-width',
                'minWidth' => 'min-width',
                'min-width' => 'min-width',
                'maxHeight' => 'max-height',
                'max-height' => 'max-height',
                'minHeight' => 'min-height',
                'min-height' => 'min-height',
                'backgroundImage' => 'background-image',
                'background-image' => 'background-image',
                'backgroundSize' => 'background-size',
                'background-size' => 'background-size',
                'backgroundPosition' => 'background-position',
                'background-position' => 'background-position',
                'backgroundRepeat' => 'background-repeat',
                'background-repeat' => 'background-repeat',
                'backgroundAttachment' => 'background-attachment',
                'background-attachment' => 'background-attachment',
                'justifyContent' => 'justify-content',
                'justify-content' => 'justify-content',
                'alignItems' => 'align-items',
                'align-items' => 'align-items',
                'flex' => 'flex',
                'gap' => 'gap',
                'lineHeight' => 'line-height',
                'line-height' => 'line-height',
                'letterSpacing' => 'letter-spacing',
                'letter-spacing' => 'letter-spacing',
                'textDecorationColor' => 'text-decoration-color',
                'text-decoration-color' => 'text-decoration-color',
                'textDecoration' => 'text-decoration',
                'text-decoration' => 'text-decoration',
                'position' => 'position',
                'left' => 'left',
                'top' => 'top',
                'zIndex' => 'z-index',
                'z-index' => 'z-index',
            ];

            $out = [];
            foreach ($allowed as $key => $cssProp) {
                $value = trim((string) ($style[$key] ?? ''));
                if ($value === '') {
                    continue;
                }
                if ($key === 'position') {
                    if (in_array($value, ['absolute', 'relative'], true)) {
                        $out[] = $cssProp . ':' . $value;
                    }
                    continue;
                }
                if ($key === 'zIndex' || $key === 'z-index') {
                    $n = (int) $value;
                    if ($n >= 0 && $n <= 9999) {
                        $out[] = 'z-index:' . $n;
                    }
                    continue;
                }
                if ($key === 'backgroundImage') {
                    if (!preg_match('/^url\(((https?:\/\/|\/)[^\s)]+)\)$/i', $value)) {
                        continue;
                    }
                    $out[] = $cssProp . ':' . $value;
                    continue;
                }
                if (!preg_match('/^[#(),.%:\-\/\sA-Za-z0-9]+$/u', $value)) {
                    continue;
                }
                $out[] = $cssProp . ':' . $value;
            }

            $result = implode(';', $out);
            return $result !== '' ? $result . ';' : '';
        };

        $layoutKeys = ['position','left','top','right','bottom','width','height','minWidth','min-width','maxWidth','max-width','minHeight','min-height','maxHeight','max-height','zIndex','z-index','margin','marginTop','marginRight','marginBottom','marginLeft','flex'];
        $contentStyleToString = function (array $style) use ($styleToString, $layoutKeys): string {
            $filtered = [];
            foreach ($style as $k => $v) {
                if (!in_array($k, $layoutKeys, true)) {
                    $filtered[$k] = $v;
                }
            }
            return $styleToString($filtered);
        };
    @endphp

    <div class="wrap">
        @if($isPreview)
        <div class="preview-toolbar">
            <a class="btn secondary" href="{{ route('funnels.edit', $funnel) }}" style="padding:8px 14px; box-shadow:none;">
                <i class="fas fa-arrow-left"></i> Back to Builder
            </a>
            <span class="preview-badge"><i class="fas fa-eye"></i> Preview Mode</span>
        </div>
        @endif

        <div class="step-content--full">
            @if($hasBuilderLayout)
                @foreach($renderSections as $section)
                    @php
                        $sectionStyle = $styleToString(is_array($section['style'] ?? null) ? $section['style'] : []);
                        $sectionSettings = is_array($section['settings'] ?? null) ? $section['settings'] : [];
                        $sectionAnchorId = ltrim(trim((string) ($sectionSettings['anchorId'] ?? '')), '#');
                        if ($sectionAnchorId !== '') {
                            $sectionAnchorId = preg_replace('/[^a-zA-Z0-9\-_]/', '', $sectionAnchorId) ?: '';
                            $sectionAnchorId = mb_substr($sectionAnchorId, 0, 80);
                        }
                        $isBareCarouselWrap = (bool) ($section['isBareCarouselWrap'] ?? false);
                        $isFreeformCanvas = (bool) ($section['isFreeformCanvas'] ?? false);
                        $contentWidth = trim((string) ($sectionSettings['contentWidth'] ?? 'full'));
                        $widthMap = ['full' => '', 'wide' => '1200px', 'medium' => '992px', 'small' => '768px', 'xsmall' => '576px'];
                        $innerMax = $widthMap[$contentWidth] ?? '';
                        $sectionElements = is_array($section['elements'] ?? null) ? $section['elements'] : [];
                        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                        if (!$isBareCarouselWrap && !$isFreeformCanvas && count($rows) === 0 && count($sectionElements) === 1) {
                            $onlyType = strtolower((string) (($sectionElements[0]['type'] ?? '')));
                            if ($onlyType === 'carousel') {
                                $isBareCarouselWrap = true;
                            }
                        }
                        if (count($sectionElements) > 0) {
                            array_unshift($rows, [
                                'id' => 'sec_el_row_' . md5((string) ($section['id'] ?? uniqid('', true))),
                                'style' => ['gap' => '8px'],
                                'settings' => ['contentWidth' => 'full'],
                                'columns' => [[
                                    'id' => 'sec_el_col_' . md5((string) ($section['id'] ?? uniqid('', true))),
                                    'style' => ['flex' => '1 1 240px'],
                                    'settings' => [],
                                    'elements' => $sectionElements,
                                ]],
                            ]);
                        }
                        $sectionInlineStyle = $sectionStyle;
                        if ($isBareCarouselWrap) {
                            $sectionInlineStyle .= ($sectionInlineStyle !== '' ? '; ' : '') . 'border:none;';
                        }
                        $sectionInnerStyle = [];
                        if ($innerMax !== '' && !$isBareCarouselWrap) {
                            $sectionInnerStyle[] = 'max-width: ' . $innerMax;
                            $sectionInnerStyle[] = 'margin: 0 auto';
                        }
                        if ($isBareCarouselWrap) {
                            $sectionInnerStyle[] = 'width:100%';
                        }
                        $sectionInnerStyleString = implode('; ', $sectionInnerStyle);
                    @endphp
                    <section class="builder-section{{ $isFreeformCanvas ? ' builder-section--freeform' : '' }}" @if($sectionAnchorId !== '') id="{{ $sectionAnchorId }}" @endif style="{{ $sectionInlineStyle }}">
                        <div class="builder-section-inner" @if($sectionInnerStyleString !== '') style="{{ $sectionInnerStyleString }}" @endif>
                        @foreach($rows as $row)
                            @php
                                $rowStyle = $styleToString(is_array($row['style'] ?? null) ? $row['style'] : []);
                                $rowStyleArr = is_array($row['style'] ?? null) ? $row['style'] : [];
                                $rowSettings = is_array($row['settings'] ?? null) ? $row['settings'] : [];
                                $rowContentWidth = trim((string) ($rowSettings['contentWidth'] ?? 'full'));
                                $rowInnerMax = $widthMap[$rowContentWidth] ?? '';
                                $rowGap = trim((string) ($rowStyleArr['gap'] ?? ''));
                                $rowInnerStyle = [];
                                if ($rowInnerMax !== '') {
                                    $rowInnerStyle[] = 'max-width: ' . $rowInnerMax;
                                    $rowInnerStyle[] = 'margin: 0 auto';
                                }
                                if ($rowGap !== '' && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $rowGap)) {
                                    $rowInnerStyle[] = 'gap: ' . $rowGap;
                                }
                                $rowInnerStyleString = implode('; ', $rowInnerStyle);
                                $columns = is_array($row['columns'] ?? null) ? $row['columns'] : [];
                            @endphp
                            <div class="builder-row" style="{{ $rowStyle }}">
                                <div class="builder-row-inner" @if($rowInnerStyleString !== '') style="{{ $rowInnerStyleString }}" @endif>
                                @foreach($columns as $column)
                                    @php
                                        $colStyleArr = is_array($column['style'] ?? null) ? $column['style'] : [];
                                        $legacyColBg = strtolower(trim((string) ($colStyleArr['backgroundColor'] ?? '')));
                                        if ($legacyColBg === '#f8fafc' || $legacyColBg === 'rgb(248, 250, 252)' || $legacyColBg === 'rgba(248, 250, 252, 1)') {
                                            $colStyleArr['backgroundColor'] = '#ffffff';
                                        }
                                        $colStyle = $styleToString($colStyleArr);
                                        $colSettings = is_array($column['settings'] ?? null) ? $column['settings'] : [];
                                        $colContentWidth = trim((string) ($colSettings['contentWidth'] ?? 'full'));
                                        $colInnerMax = $widthMap[$colContentWidth] ?? '';
                                        $elements = is_array($column['elements'] ?? null) ? $column['elements'] : [];
                                        $colMinHeight = 0;
                                        $colMinWidth = 0;
                                        foreach ($elements as $_el) {
                                            $_elSettings = is_array($_el['settings'] ?? null) ? $_el['settings'] : [];
                                            $_elStyle = is_array($_el['style'] ?? null) ? $_el['style'] : [];
                                            if (trim((string) ($_elSettings['positionMode'] ?? '')) === 'absolute' || trim((string) ($_elStyle['position'] ?? '')) === 'absolute') {
                                                $_ey = (int) ($_elSettings['freeY'] ?? 0);
                                                if ($_ey <= 0) $_ey = (int) str_replace('px', '', (string) ($_elStyle['top'] ?? '0'));
                                                $_eh = (int) str_replace('px', '', (string) ($_elStyle['height'] ?? '0'));
                                                if ($_eh <= 0) $_eh = (int) ($_elSettings['fixedHeight'] ?? 80);
                                                $_colBot = $_ey + max(40, $_eh) + 20;
                                                if ($_colBot > $colMinHeight) $colMinHeight = $_colBot;
                                                $_ex = (int) ($_elSettings['freeX'] ?? 0);
                                                if ($_ex <= 0) $_ex = (int) str_replace('px', '', (string) ($_elStyle['left'] ?? '0'));
                                                $_ew = (int) str_replace('px', '', (string) ($_elStyle['width'] ?? '0'));
                                                if ($_ew <= 0) $_ew = (int) ($_elSettings['fixedWidth'] ?? 120);
                                                $_colRight = $_ex + $_ew + 20;
                                                if ($_colRight > $colMinWidth) $colMinWidth = $_colRight;
                                            }
                                        }
                                        $colHeightStyle = $colMinHeight > 0 ? 'min-height:' . $colMinHeight . 'px;' : '';
                                        $freeformWidth = ($isFreeformCanvas && $editorCanvasWidth > 0) ? $editorCanvasWidth : (($isFreeformCanvas && $colMinWidth > 0) ? $colMinWidth : 0);
                                        $colWidthStyle = $freeformWidth > 0 ? 'width:' . $freeformWidth . 'px;' : '';
                                    @endphp
                                    <div class="builder-col" style="{{ $colStyle }}{{ $colHeightStyle }}{{ $colWidthStyle }}">
                                        <div class="builder-col-inner" @if($colInnerMax !== '') style="max-width: {{ $colInnerMax }}; margin: 0 auto;" @endif>
                                        @foreach($elements as $element)
                                            @php
                                                $type = $element['type'] ?? 'text';
                                                $content = (string) ($element['content'] ?? '');
                                                $rawStyle = is_array($element['style'] ?? null) ? $element['style'] : [];
                                                $style = $styleToString($rawStyle);
                                                $contentStyle = $contentStyleToString($rawStyle);
                                                $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                                                $link = trim((string) ($settings['link'] ?? '#'));
                                                $src = trim((string) ($settings['src'] ?? ''));
                                                $alt = trim((string) ($settings['alt'] ?? 'Image'));
                                                $alignment = trim((string) ($settings['alignment'] ?? ''));
                                                if (!in_array($alignment, ['left', 'center', 'right'], true)) {
                                                    $fallbackAlign = strtolower(trim((string) ($rawStyle['textAlign'] ?? '')));
                                                    $defaultAlign = in_array($type, ['image', 'video', 'form', 'menu'], true) ? 'left' : 'center';
                                                    $alignment = in_array($fallbackAlign, ['left', 'center', 'right'], true)
                                                        ? $fallbackAlign
                                                        : $defaultAlign;
                                                }
                                                $alignStyle = 'display:flex;justify-content:' . ($alignment === 'right' ? 'flex-end' : ($alignment === 'center' ? 'center' : 'flex-start')) . ';margin-left:' . ($alignment === 'left' ? '0' : 'auto') . ';margin-right:' . ($alignment === 'right' ? '0' : 'auto') . ';';
                                                $menuAlign = $settings['menuAlign'] ?? 'left';
                                                $menuAlignStyle = 'display:flex;justify-content:' . ($menuAlign === 'right' ? 'flex-end' : ($menuAlign === 'center' ? 'center' : 'flex-start')) . ';';
                                                $widthBehavior = $settings['widthBehavior'] ?? 'fluid';
                                                $buttonContainerBg = trim((string) ($settings['containerBgColor'] ?? ''));
                                                if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $buttonContainerBg)) {
                                                    $buttonContainerBg = '';
                                                }
                                                $btnWrapStyle = ($type === 'button' ? ($alignStyle . ($buttonContainerBg !== '' ? 'background-color:' . $buttonContainerBg . ';' : '')) : '');
                                                $iconWrapStyle = ($type === 'icon' ? $alignStyle : '');
                                                $mediaWrapStyle = ($style !== '' ? ($style . ';') : '') . $alignStyle;
                                                $btnInnerStyle = $contentStyle . ($type === 'button' && $widthBehavior === 'fill' ? (($contentStyle !== '' ? ';' : '') . ' width:100%;display:block;box-sizing:border-box;text-align:center;') : '');
                                                $offsetX = (int) ($settings['offsetX'] ?? 0);
                                                if ($offsetX !== 0) {
                                                    $mediaWrapStyle .= 'transform: translateX(' . $offsetX . 'px);';
                                                }
                                                $cropTop = max(0, (int) ($settings['cropTop'] ?? 0));
                                                $cropRight = max(0, (int) ($settings['cropRight'] ?? 0));
                                                $cropBottom = max(0, (int) ($settings['cropBottom'] ?? 0));
                                                $cropLeft = max(0, (int) ($settings['cropLeft'] ?? 0));
                                                $mediaClipStyle = ($cropTop || $cropRight || $cropBottom || $cropLeft)
                                                    ? ('clip-path: inset(' . $cropTop . 'px ' . $cropRight . 'px ' . $cropBottom . 'px ' . $cropLeft . 'px);')
                                                    : '';
                                                $hasFixedHeight = !empty(trim((string) ($rawStyle['height'] ?? '')));
                                                $isAbsPos = (trim((string) ($settings['positionMode'] ?? '')) === 'absolute') || (trim((string) ($rawStyle['position'] ?? '')) === 'absolute');
                                                $absPosStyle = '';
                                                if ($isAbsPos) {
                                                    $absFreeX = (int) ($settings['freeX'] ?? 0);
                                                    $absFreeY = (int) ($settings['freeY'] ?? 0);
                                                    $absLeft = trim((string) ($rawStyle['left'] ?? ''));
                                                    $absTop = trim((string) ($rawStyle['top'] ?? ''));
                                                    if ($absLeft === '' && $absFreeX > 0) $absLeft = $absFreeX . 'px';
                                                    if ($absTop === '' && $absFreeY > 0) $absTop = $absFreeY . 'px';
                                                    $absWidth = trim((string) ($rawStyle['width'] ?? ''));
                                                    $absHeight = trim((string) ($rawStyle['height'] ?? ''));
                                                    $absPosStyle = 'position:absolute;left:' . ($absLeft !== '' ? $absLeft : '0px') . ';top:' . ($absTop !== '' ? $absTop : '0px') . ';margin:0;box-sizing:border-box;';
                                                    if ($absWidth !== '') $absPosStyle .= 'width:' . preg_replace('/[^#(),.%\-\sA-Za-z0-9]/', '', $absWidth) . ';';
                                                    if ($absHeight !== '') $absPosStyle .= 'height:' . preg_replace('/[^#(),.%\-\sA-Za-z0-9]/', '', $absHeight) . ';';
                                                    $hasZIndex = array_key_exists('zIndex', $rawStyle) || array_key_exists('z-index', $rawStyle);
                                                    if ($hasZIndex) {
                                                        $absZIndex = max(0, (int) ($rawStyle['zIndex'] ?? ($rawStyle['z-index'] ?? 0)));
                                                        $absPosStyle .= 'z-index:' . $absZIndex . ';';
                                                    }
                                                }
                                            @endphp

                                            @php
                                                $elWrapStyle = '';
                                                if ($isAbsPos) {
                                                    $elWrapStyle = $absPosStyle;
                                                } else {
                                                    if ($type === 'button') { $elWrapStyle .= $btnWrapStyle; }
                                                    elseif ($type === 'icon') { $elWrapStyle .= $iconWrapStyle; }
                                                    elseif ($type === 'image' || $type === 'video') { $elWrapStyle .= $mediaWrapStyle; }
                                                    elseif ($type === 'form') { $elWrapStyle .= $alignStyle; }
                                                }
                                            @endphp
                                            <div class="builder-el" @if($elWrapStyle !== '') style="{{ $elWrapStyle }}" @endif>
                                                @if($type === 'heading')
                                                    <h2 class="builder-heading" style="{{ $contentStyle }}">{!! $content !!}</h2>
                                                @elseif($type === 'text')
                                                    <div class="builder-text" style="{{ $contentStyle }}">{!! $content !!}</div>
                                                @elseif($type === 'image')
                                                    @if($src !== '')
                                                        @php
                                                            $imgStyle = $mediaClipStyle;
                                                            if ($hasFixedHeight) {
                                                                $imgStyle .= ($imgStyle !== '' ? ';' : '') . 'width:100%;height:100%;object-fit:cover;';
                                                            }
                                                        @endphp
                                                        <img class="builder-img" src="{{ $src }}" alt="{{ $alt !== '' ? $alt : 'Image' }}" @if($imgStyle !== '') style="{{ $imgStyle }}" @endif>
                                                    @else
                                                        <div style="padding:12px;border:1px dashed #94a3b8;border-radius:8px;text-align:center;color:#94a3b8;font-size:13px;min-height:60px;display:flex;align-items:center;justify-content:center;width:100%;box-sizing:border-box;">Image placeholder</div>
                                                    @endif
                                                @elseif($type === 'button')
                                                    @php
                                                        $buttonAction = $resolveButtonAction($settings);
                                                        $buttonLabel = $content !== '' ? $content : 'Button';
                                                    @endphp
                                                    @if(($buttonAction['kind'] ?? 'link') === 'post')
                                                        <form method="POST" action="{{ $buttonAction['action'] }}" style="margin:0;">
                                                            @csrf
                                                            @foreach(($buttonAction['fields'] ?? []) as $fieldName => $fieldValue)
                                                                <input type="hidden" name="{{ $fieldName }}" value="{{ $fieldValue }}">
                                                            @endforeach
                                                            <button type="submit" class="btn" style="{{ $btnInnerStyle }}">{!! $buttonLabel !!}</button>
                                                        </form>
                                                    @elseif(($buttonAction['kind'] ?? 'link') === 'disabled')
                                                        <button type="button" class="btn" style="{{ $btnInnerStyle }}{{ $btnInnerStyle !== '' ? ';' : '' }}opacity:0.7;cursor:not-allowed;" disabled>{!! $buttonLabel !!}</button>
                                                    @else
                                                        <a class="btn" href="{{ $buttonAction['href'] ?? '#' }}" style="{{ $btnInnerStyle }}">{!! $buttonLabel !!}</a>
                                                    @endif
                                                @elseif($type === 'icon')
                                                    @php
                                                        $iconNameRaw = strtolower(trim((string) ($settings['iconName'] ?? 'star')));
                                                        $iconName = preg_match('/^[a-z0-9-]{1,40}$/', $iconNameRaw) ? $iconNameRaw : 'star';
                                                        $iconStyle = strtolower(trim((string) ($settings['iconStyle'] ?? 'solid')));
                                                        if (!in_array($iconStyle, ['solid', 'regular', 'brands'], true)) {
                                                            $iconStyle = 'solid';
                                                        }
                                                        $iconPrefix = $iconStyle === 'brands' ? 'fa-brands' : ($iconStyle === 'regular' ? 'fa-regular' : 'fa-solid');
                                                        $iconClass = $iconPrefix . ' fa-' . $iconName;
                                                        $iconLink = trim((string) ($settings['link'] ?? ''));
                                                    @endphp
                                                    @if($iconLink !== '')
                                                        <a href="{{ $iconLink }}" style="{{ $style !== '' ? $style : 'font-size:36px;color:#1d4ed8;' }}"><i class="{{ $iconClass }}" aria-hidden="true"></i></a>
                                                    @else
                                                        <span style="{{ $style !== '' ? $style : 'font-size:36px;color:#1d4ed8;' }}"><i class="{{ $iconClass }}" aria-hidden="true"></i></span>
                                                    @endif
                                                @elseif($type === 'video')
                                                    @php
                                                        $videoSrcRaw = trim((string) ($settings['src'] ?? ''));
                                                        $elStyle = is_array($element['style'] ?? null) ? $element['style'] : [];
                                                        $elSettings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                                                        $videoWrapStyle = $contentStyle;
                                                        $widthVal = !empty($elStyle['width']) ? trim((string) $elStyle['width']) : (!empty($elSettings['width']) ? trim((string) $elSettings['width']) : '');
                                                        if ($widthVal !== '' && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $widthVal)) {
                                                            $videoWrapStyle .= ($videoWrapStyle !== '' ? '; ' : '') . 'width: ' . $widthVal . ' !important';
                                                        }
                                                        if (!empty($elStyle['height']) && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', trim((string) $elStyle['height']))) {
                                                            $videoWrapStyle .= ($videoWrapStyle !== '' ? '; ' : '') . 'height: ' . trim((string) $elStyle['height']) . ' !important';
                                                            $videoWrapStyle .= '; padding-top: 0 !important; min-height: 0 !important';
                                                        }
                                                        if (!empty($elStyle['maxWidth']) && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', trim((string) $elStyle['maxWidth']))) {
                                                            $videoWrapStyle .= ($videoWrapStyle !== '' ? '; ' : '') . 'max-width: ' . trim((string) $elStyle['maxWidth']) . ' !important';
                                                        }
                                                        if ($mediaClipStyle !== '') {
                                                            $videoWrapStyle .= ($videoWrapStyle !== '' ? '; ' : '') . $mediaClipStyle;
                                                        }
                                                    @endphp
                                                    @if($videoSrcRaw !== '')
                                                        @php
                                                            $vSrc = $videoSrcRaw;
                                                            if (!str_starts_with($vSrc, 'http')) {
                                                                $vSrc = 'https://' . ltrim($vSrc, '/');
                                                            }
                                                            $videoEmbedUrl = $vSrc;
                                                            $isYoutubeVimeo = str_contains($vSrc, 'youtube.com') || str_contains($vSrc, 'youtu.be') || str_contains($vSrc, 'vimeo.com');
                                                            if (str_contains($vSrc, 'youtube.com/watch')) {
                                                                parse_str(parse_url($vSrc, PHP_URL_QUERY) ?: '', $yt);
                                                                $videoEmbedUrl = isset($yt['v']) ? 'https://www.youtube.com/embed/' . $yt['v'] : $vSrc;
                                                            } elseif (preg_match('#youtu\.be/([a-zA-Z0-9_-]+)#', $vSrc, $m)) {
                                                                $videoEmbedUrl = 'https://www.youtube.com/embed/' . $m[1];
                                                            } elseif (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $vSrc, $m)) {
                                                                $videoEmbedUrl = 'https://player.vimeo.com/video/' . $m[1];
                                                            }
                                                            $videoFinalSrc = $isYoutubeVimeo ? $videoEmbedUrl : (str_starts_with($vSrc, 'http') ? $vSrc : asset(ltrim($vSrc, '/')));
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
                                                                <video src="{{ $videoFinalSrc }}" @if(($settings['controls'] ?? true) !== false) controls @endif @if(($settings['autoplay'] ?? false) === true) autoplay muted @endif playsinline preload="metadata"></video>
                                                            @endif
                                                            <a href="{{ $videoFinalSrc }}" target="_blank" rel="noopener" class="video-fallback-link">Open video</a>
                                                        </div>
                                                    @else
                                                        <div class="builder-video-wrap" style="{{ $videoWrapStyle }}">
                                                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.8);padding:12px;">
                                                                <span style="font-size:28px;margin-bottom:6px;">&#9654;</span>
                                                                <span style="font-size:12px;">Video placeholder</span>
                                                            </div>
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
                                                        $menuText = trim((string) ($settings['textColor'] ?? '#374151'));
                                                        $menuUnderline = trim((string) ($settings['underlineColor'] ?? ''));
                                                    @endphp
                                                    <nav class="builder-menu" style="{{ $menuAlignStyle }}{{ $style !== '' ? $style : '' }}">
                                                        <ul class="builder-menu-list" style="gap: {{ $itemGap }}px;">
                                                            @foreach($menuItems as $i => $menuItem)
                                                                @php
                                                                    $menuLabel = trim((string) ($menuItem['label'] ?? 'Menu item ' . ($i + 1)));
                                                                    $menuHref = trim((string) ($menuItem['url'] ?? '#'));
                                                                    $menuNew = (bool) ($menuItem['newWindow'] ?? false);
                                                                    $linkColor = $menuText;
                                                                    $decoStyle = $menuUnderline !== '' ? 'text-decoration:underline;text-decoration-color:' . $menuUnderline . ';' : 'text-decoration:none;';
                                                                @endphp
                                                                <li>
                                                                    <a class="builder-menu-link" href="{{ $menuHref !== '' ? $menuHref : '#' }}" @if($menuNew) target="_blank" rel="noopener" @endif style="color: {{ $linkColor }}; {{ $decoStyle }} font-family:inherit; font-size:inherit; line-height:inherit; letter-spacing:inherit; font-weight:inherit; font-style:inherit;">{{ $menuLabel !== '' ? $menuLabel : ('Menu item ' . ($i + 1)) }}</a>
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
                                                            if ($carouselType === 'icon') {
                                                                return trim((string) ($carouselSettings['iconName'] ?? '')) !== '';
                                                            }
                                                            if ($carouselType === 'heading' || $carouselType === 'text' || $carouselType === 'button') {
                                                                return trim(strip_tags((string) ($carouselElement['content'] ?? ''))) !== '';
                                                            }
                                                            if ($carouselType === 'spacer' || $carouselType === 'menu' || $carouselType === 'form' || $carouselType === 'carousel') {
                                                                return true;
                                                            }
                                                            return true;
                                                        };
                                                        $slides = is_array($settings['slides'] ?? null) ? $settings['slides'] : [];
                                                        if (count($slides) === 0) {
                                                            $slides = [['label' => 'Slide #1']];
                                                        }
                                                        $isCarouselEmpty = true;
                                                        foreach ($slides as $slideCheck) {
                                                            $slideImgCheck = is_array($slideCheck['image'] ?? null) ? $slideCheck['image'] : [];
                                                            if (trim((string) ($slideImgCheck['src'] ?? '')) !== '') {
                                                                $isCarouselEmpty = false;
                                                                break;
                                                            }
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
                                                        $slideshowMode = strtolower(trim((string) ($settings['slideshowMode'] ?? 'manual')));
                                                        if (!in_array($slideshowMode, ['manual', 'auto'], true)) {
                                                            $slideshowMode = 'manual';
                                                        }
                                                        $isAutoSlideshow = $slideshowMode === 'auto';
                                                        $showArrows = !$isAutoSlideshow && (($settings['showArrows'] ?? true) !== false);
                                                        $controlsColor = trim((string) ($settings['controlsColor'] ?? '#64748b'));
                                                        $arrowColor = trim((string) ($settings['arrowColor'] ?? '#ffffff'));
                                                        $bodyBgColor = trim((string) ($settings['bodyBgColor'] ?? ''));
                                                        $fixedWidth = (int) ($settings['fixedWidth'] ?? 200);
                                                        $fixedHeight = (int) ($settings['fixedHeight'] ?? 200);
                                                        $carouselAlign = trim((string) ($settings['alignment'] ?? 'left'));
                                                        if (!in_array($carouselAlign, ['left', 'center', 'right'], true)) {
                                                            $carouselAlign = 'left';
                                                        }
                                                        if ($fixedWidth < 50 || $fixedWidth > 2400) {
                                                            $fixedWidth = 200;
                                                        }
                                                        if ($fixedHeight < 50 || $fixedHeight > 1600) {
                                                            $fixedHeight = 200;
                                                        }
                                                        $carouselSizeStyle = '';
                                                        $carouselSizeStyle .= 'display:block !important;box-sizing:border-box !important;';
                                                        $carouselSizeStyle .= 'width:' . $fixedWidth . 'px !important;max-width:100% !important;';
                                                        if ($carouselAlign === 'center') {
                                                            $carouselSizeStyle .= 'margin-left:auto !important;margin-right:auto !important;';
                                                        } elseif ($carouselAlign === 'right') {
                                                            $carouselSizeStyle .= 'margin-left:auto !important;margin-right:0 !important;';
                                                        } else {
                                                            $carouselSizeStyle .= 'margin-left:0 !important;margin-right:auto !important;';
                                                        }
                                                        $carouselSizeStyle .= 'aspect-ratio:' . $fixedWidth . ' / ' . $fixedHeight . ' !important;';
                                                        $carouselSizeStyle .= 'height:auto !important;min-height:0 !important;';
                                                        $carouselId = 'car_' . md5((string) ($element['id'] ?? uniqid('', true)));
                                                    @endphp
                                                    <div class="builder-carousel-wrap" data-carousel id="{{ $carouselId }}" data-active="{{ $activeSlide }}" data-mode="{{ $slideshowMode }}" style="{{ $carouselSizeStyle }} background:#ffffff !important; background-image:none !important; color:#0f172a;">
                                                        <div class="builder-carousel-track" data-carousel-track style="transform: translateX(-{{ $activeSlide * 100 }}%);">
                                                            @foreach($slides as $si => $slide)
                                                                @php
                                                                    $slideTitle = trim((string) ($slide['label'] ?? ('Slide #' . ($si + 1))));
                                                                    $showSlideTitle = $slideTitle !== '' && !preg_match('/^Slide\s*#\s*\d+$/i', $slideTitle);
                                                                    $slideImage = is_array($slide['image'] ?? null) ? $slide['image'] : [];
                                                                    $slideImageSrc = trim((string) ($slideImage['src'] ?? ''));
                                                                    $slideImageAlt = trim((string) ($slideImage['alt'] ?? 'Image'));
                                                                    $slideRows = is_array($slide['rows'] ?? null) ? $slide['rows'] : [];
                                                                    $isSlideEmpty = $slideImageSrc === '';
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
                                                                <div class="builder-carousel-slide" style="justify-content: {{ $aItems }}; background:#ffffff !important;">
                                                                    @if($isSlideEmpty)
                                                                        <div style="min-height:140px; width:100%; display:flex; align-items:center; justify-content:center;">
                                                                            <span style="font-size:13px; font-weight:700; color:#64748b;">Carousel is Empty</span>
                                                                        </div>
                                                                    @else
                                                                        @if($showSlideTitle)
                                                                            <div class="builder-carousel-title">{{ $slideTitle }}</div>
                                                                        @endif
                                                                        @if($slideImageSrc !== '')
                                                                            <div style="width:100%;height:100%;display:flex;justify-content:center;align-items:center;">
                                                                                <img class="builder-img" src="{{ $slideImageSrc }}" alt="{{ $slideImageAlt !== '' ? $slideImageAlt : 'Image' }}" style="width:100%;height:100%;object-fit:cover;display:block;border-radius:8px;">
                                                                            </div>
                                                                        @elseif(count($slideRows) > 0)
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
                                                                                                        $cropTop = max(0, (int) ($ssc['cropTop'] ?? 0));
                                                                                                        $cropRight = max(0, (int) ($ssc['cropRight'] ?? 0));
                                                                                                        $cropBottom = max(0, (int) ($ssc['cropBottom'] ?? 0));
                                                                                                        $cropLeft = max(0, (int) ($ssc['cropLeft'] ?? 0));
                                                                                                        $mediaClipStyle = ($cropTop || $cropRight || $cropBottom || $cropLeft)
                                                                                                            ? ('clip-path: inset(' . $cropTop . 'px ' . $cropRight . 'px ' . $cropBottom . 'px ' . $cropLeft . 'px);')
                                                                                                            : '';
                                                                                                    @endphp
                                                                                                    @if($img !== '')
                                                                                                        <img class="builder-img" src="{{ $img }}" alt="{{ $alt }}" style="{{ $ss }}{{ $ss !== '' && $mediaClipStyle !== '' ? ';' : '' }}{{ $mediaClipStyle }}">
                                                                                                    @endif
                                                                                                @elseif($st === 'video')
                                                                                                    @php
                                                                                                        $videoSrc = trim((string) ($ssc['src'] ?? ''));
                                                                                                        $cropTop = max(0, (int) ($ssc['cropTop'] ?? 0));
                                                                                                        $cropRight = max(0, (int) ($ssc['cropRight'] ?? 0));
                                                                                                        $cropBottom = max(0, (int) ($ssc['cropBottom'] ?? 0));
                                                                                                        $cropLeft = max(0, (int) ($ssc['cropLeft'] ?? 0));
                                                                                                        $mediaClipStyle = ($cropTop || $cropRight || $cropBottom || $cropLeft)
                                                                                                            ? ('clip-path: inset(' . $cropTop . 'px ' . $cropRight . 'px ' . $cropBottom . 'px ' . $cropLeft . 'px);')
                                                                                                            : '';
                                                                                                    @endphp
                                                                                                    @if($videoSrc !== '')
                                                                                                        <div class="builder-video-wrap" style="{{ $ss }}{{ $ss !== '' && $mediaClipStyle !== '' ? ';' : '' }}{{ $mediaClipStyle }}">
                                                                                                            <video src="{{ $videoSrc }}" controls playsinline preload="metadata"></video>
                                                                                                            <a href="{{ $videoSrc }}" target="_blank" rel="noopener" class="video-fallback-link">Open video</a>
                                                                                                        </div>
                                                                                                    @endif
                                                                                                @elseif($st === 'button')
                                                                                                    @php
                                                                                                        $buttonAction = $resolveButtonAction($ssc);
                                                                                                        $buttonLabel = strip_tags($scontent) !== '' ? strip_tags($scontent) : 'Click';
                                                                                                    @endphp
                                                                                                    @if(($buttonAction['kind'] ?? 'link') === 'post')
                                                                                                        <form method="POST" action="{{ $buttonAction['action'] }}" style="margin:0;">
                    @csrf
                                                                                                            @foreach(($buttonAction['fields'] ?? []) as $fieldName => $fieldValue)
                                                                                                                <input type="hidden" name="{{ $fieldName }}" value="{{ $fieldValue }}">
                                                                                                            @endforeach
                                                                                                            <button type="submit" class="btn" style="{{ $ss }}">{{ $buttonLabel }}</button>
                                                                                                        </form>
                                                                                                    @elseif(($buttonAction['kind'] ?? 'link') === 'disabled')
                                                                                                        <button type="button" class="btn" style="{{ $ss }}{{ $ss !== '' ? ';' : '' }}opacity:0.7;cursor:not-allowed;" disabled>{{ $buttonLabel }}</button>
                                                                                                    @else
                                                                                                        <a href="{{ $buttonAction['href'] ?? '#' }}" class="btn" style="{{ $ss }}">{{ $buttonLabel }}</a>
                                                                                                    @endif
                                                                                                @elseif($st === 'icon')
                                                                                                    @php
                                                                                                        $iconNameRaw = strtolower(trim((string) ($ssc['iconName'] ?? 'star')));
                                                                                                        $iconName = preg_match('/^[a-z0-9-]{1,40}$/', $iconNameRaw) ? $iconNameRaw : 'star';
                                                                                                        $iconStyle = strtolower(trim((string) ($ssc['iconStyle'] ?? 'solid')));
                                                                                                        if (!in_array($iconStyle, ['solid', 'regular', 'brands'], true)) {
                                                                                                            $iconStyle = 'solid';
                                                                                                        }
                                                                                                        $iconPrefix = $iconStyle === 'brands' ? 'fa-brands' : ($iconStyle === 'regular' ? 'fa-regular' : 'fa-solid');
                                                                                                        $iconClass = $iconPrefix . ' fa-' . $iconName;
                                                                                                        $iconLink = trim((string) ($ssc['link'] ?? ''));
                                                                                                    @endphp
                                                                                                    @if($iconLink !== '')
                                                                                                        <a href="{{ $iconLink }}" style="{{ $ss !== '' ? $ss : 'font-size:36px;color:#1d4ed8;' }}"><i class="{{ $iconClass }}" aria-hidden="true"></i></a>
                                                                                                    @else
                                                                                                        <span style="{{ $ss !== '' ? $ss : 'font-size:36px;color:#1d4ed8;' }}"><i class="{{ $iconClass }}" aria-hidden="true"></i></span>
                                                                                                    @endif
                                                                                                @elseif($st === 'spacer')
                                                                                                    <div style="{{ $ss !== '' ? $ss : 'height:24px' }}"></div>
                                                                                                @elseif($st === 'menu')
                                                                                                    @php
                                                                                                        $menuItems = is_array($ssc['items'] ?? null) ? $ssc['items'] : [];
                                                                                                        if (count($menuItems) === 0) {
                                                                                                            $menuItems = [['label' => 'Menu item', 'url' => '#', 'newWindow' => false]];
                                                                                                        }
                                                                                                        $itemGap = (int) ($ssc['itemGap'] ?? 13);
                                                                                                        $itemGap = max(0, min(64, $itemGap));
                                                                                                        $menuAlign = $ssc['menuAlign'] ?? 'left';
                                                                                                        $menuAlignStyle = 'display:flex;justify-content:' . ($menuAlign === 'right' ? 'flex-end' : ($menuAlign === 'center' ? 'center' : 'flex-start')) . ';';
                                                                                                        $menuText = trim((string) ($ssc['textColor'] ?? '#374151'));
                                                                                                        $menuUnderline = trim((string) ($ssc['underlineColor'] ?? ''));
                                                                                                    @endphp
                                                                                                    <nav class="builder-menu" style="{{ $menuAlignStyle }}{{ $ss !== '' ? $ss : '' }}">
                                                                                                        <ul class="builder-menu-list" style="gap: {{ $itemGap }}px;">
                                                                                                            @foreach($menuItems as $i => $menuItem)
                                                                                                                @php
                                                                                                                    $menuLabel = trim((string) ($menuItem['label'] ?? 'Menu item ' . ($i + 1)));
                                                                                                                    $menuHref = trim((string) ($menuItem['url'] ?? '#'));
                                                                                                                    $menuNew = (bool) ($menuItem['newWindow'] ?? false);
                                                                                                                    $linkColor = $menuText;
                                                                                                                    $decoStyle = $menuUnderline !== '' ? 'text-decoration:underline;text-decoration-color:' . $menuUnderline . ';' : 'text-decoration:none;';
                                                                                                                @endphp
                                                                                                                <li>
                                                                                                                    <a class="builder-menu-link" href="{{ $menuHref !== '' ? $menuHref : '#' }}" @if($menuNew) target="_blank" rel="noopener" @endif style="color: {{ $linkColor }}; {{ $decoStyle }} font-family:inherit; font-size:inherit; line-height:inherit; letter-spacing:inherit; font-weight:inherit; font-style:inherit;">{{ $menuLabel !== '' ? $menuLabel : ('Menu item ' . ($i + 1)) }}</a>
                                                                                                                </li>
                                                                                                            @endforeach
                                                                                                        </ul>
                                                                                                    </nav>
                                                                                                @elseif($st === 'form')
                                                                                                    <form onsubmit="return false;" style="{{ $ss }}">
                    <label>Name</label>
                                                                                                        <input type="text" placeholder="Your name">
                    <label>Email</label>
                                                                                                        <input type="email" placeholder="you@email.com">
                                                                                                        <button type="button" class="btn">{{ $scontent !== '' ? $scontent : 'Submit' }}</button>
                </form>
                                                                                                @elseif($st === 'carousel')
                                                                                                    <div style="padding:12px;border:1px dashed #93c5fd;border-radius:8px;color:#1e40af;font-weight:700;">Nested Carousel</div>
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
                                                        @if($showArrows && !$isCarouselEmpty)
                                                            <button type="button" class="builder-carousel-arrow is-left" data-carousel-prev style="background: {{ $controlsColor }}; color: {{ $arrowColor }};"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                                                            <button type="button" class="builder-carousel-arrow is-right" data-carousel-next style="background: {{ $controlsColor }}; color: {{ $arrowColor }};"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                                                        @endif
                                                        @if(count($slides) > 1 && !$isCarouselEmpty)
                                                            <div class="builder-carousel-dots" data-carousel-dots>
                                                                @foreach($slides as $si => $unused)
                                                                    <button type="button" class="builder-carousel-dot{{ $si === $activeSlide ? ' is-active' : '' }}" data-carousel-dot="{{ $si }}" aria-label="Go to slide {{ $si + 1 }}"></button>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @elseif($type === 'spacer')
                                                    <div style="{{ $style ?: 'height:24px' }}"></div>
                                                @elseif($type === 'countdown')
                                                    {{-- Countdown component removed --}}
                                                @elseif($type === 'form')
                                                    @php
                                                        $formWidth = trim((string) ($settings['width'] ?? ($settings['formWidth'] ?? ($rawStyle['width'] ?? '100%'))));
                                                        if ($formWidth === '' || !preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $formWidth)) {
                                                            $formWidth = '100%';
                                                        }
                                                        $formLabelColor = trim((string) ($settings['labelColor'] ?? '#0f172a'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $formLabelColor)) {
                                                            $formLabelColor = '#0f172a';
                                                        }
                                                        $formPlaceholderColor = trim((string) ($settings['placeholderColor'] ?? '#94a3b8'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $formPlaceholderColor)) {
                                                            $formPlaceholderColor = '#94a3b8';
                                                        }
                                                        $formButtonBgColor = trim((string) ($settings['buttonBgColor'] ?? '#2563eb'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $formButtonBgColor)) {
                                                            $formButtonBgColor = '#2563eb';
                                                        }
                                                        $formButtonTextColor = trim((string) ($settings['buttonTextColor'] ?? '#ffffff'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $formButtonTextColor)) {
                                                            $formButtonTextColor = '#ffffff';
                                                        }
                                                        $formButtonAlign = strtolower(trim((string) ($settings['buttonAlign'] ?? 'left')));
                                                        if (!in_array($formButtonAlign, ['left', 'center', 'right'], true)) {
                                                            $formButtonAlign = 'left';
                                                        }
                                                        $formButtonJustify = $formButtonAlign === 'right' ? 'flex-end' : ($formButtonAlign === 'center' ? 'center' : 'flex-start');
                                                        $formButtonBold = (bool) ($settings['buttonBold'] ?? false);
                                                        $formButtonItalic = (bool) ($settings['buttonItalic'] ?? false);
                                                        $formButtonFontWeight = $formButtonBold ? '700' : '400';
                                                        $formButtonFontStyle = $formButtonItalic ? 'italic' : 'normal';
                                                        $formInlineStyle = 'display:block;width:' . $formWidth . ';max-width:100%;box-sizing:border-box;overflow:auto;';
                                                        if ($alignment === 'center') {
                                                            $formInlineStyle .= 'margin-left:auto;margin-right:auto;';
                                                        } elseif ($alignment === 'right') {
                                                            $formInlineStyle .= 'margin-left:auto;margin-right:0;';
                                                        } else {
                                                            $formInlineStyle .= 'margin-left:0;margin-right:auto;';
                                                        }
                                                        $rawFormFields = is_array($settings['fields'] ?? null) ? $settings['fields'] : [];
                                                        $formFields = [];
                                                        foreach ($rawFormFields as $fi => $rf) {
                                                            if (!is_array($rf)) {
                                                                continue;
                                                            }
                                                            $ft = strtolower(trim((string) ($rf['type'] ?? 'text')));
                                                            if ($ft === '') {
                                                                $ft = 'text';
                                                            }
                                                            $lbl = trim((string) ($rf['label'] ?? ''));
                                                            if ($lbl === '') {
                                                                if ($ft === 'email') {
                                                                    $lbl = 'Email';
                                                                } elseif ($ft === 'phone_number') {
                                                                    $lbl = 'Phone';
                                                                } else {
                                                                    $lbl = 'Field ' . ($fi + 1);
                                                                }
                                                            }
                                                            $ph = trim((string) ($rf['placeholder'] ?? ''));
                                                            if ($ph === '') {
                                                                if ($ft === 'phone_number') {
                                                                    $ph = '09XXXXXXXXX';
                                                                } elseif ($ft === 'email') {
                                                                    $ph = 'Email address';
                                                                } else {
                                                                    $ph = $lbl;
                                                                }
                                                            }
                                                            $formFields[] = [
                                                                'type' => $ft,
                                                                'label' => $lbl,
                                                                'placeholder' => $ph,
                                                                'required' => (bool) ($rf['required'] ?? false),
                                                            ];
                                                        }
                                                        if (count($formFields) === 0) {
                                                            if ($step->type === 'opt_in') {
                                                                $formFields[] = ['type' => 'email', 'label' => 'Email', 'placeholder' => 'Email address', 'required' => true];
                                                            } else {
                                                                $formFields[] = ['type' => 'text', 'label' => 'First name', 'placeholder' => 'First name', 'required' => false];
                                                            }
                                                        }
                                                    @endphp
                                                    @if($step->type === 'opt_in' && !$isPreview)
                                                        <form method="POST" action="{{ route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}" style="{{ $formInlineStyle }}">
                    @csrf
                                                            @foreach($formFields as $f)
                                                                @php
                                                                    $ft = strtolower(trim((string) ($f['type'] ?? 'text')));
                                                                    $lbl = trim((string) ($f['label'] ?? '')) !== '' ? $f['label'] : $ft;
                                                                    $labelKey = strtolower(trim($lbl));
                                                                    if ($ft === 'custom') {
                                                                        if (str_contains($labelKey, 'email')) {
                                                                            $ft = 'email';
                                                                        } elseif (str_contains($labelKey, 'phone') || str_contains($labelKey, 'mobile')) {
                                                                            $ft = 'phone_number';
                                                                        } elseif ($labelKey === 'name' || $labelKey === 'full name') {
                                                                            $ft = 'name';
                                                                        } elseif (str_contains($labelKey, 'first') && str_contains($labelKey, 'name')) {
                                                                            $ft = 'first_name';
                                                                        } elseif (str_contains($labelKey, 'last') && str_contains($labelKey, 'name')) {
                                                                            $ft = 'last_name';
                                                                        }
                                                                    }
                                                                    $nm = in_array($ft, ['name', 'first_name', 'last_name', 'email', 'phone_number', 'phone', 'province', 'city_municipality', 'barangay', 'street'], true) ? $ft : 'custom_' . $loop->index;
                                                                    $req = (bool) ($f['required'] ?? false);
                                                                    if ($ft === 'email') {
                                                                        $req = true;
                                                                    }
                                                                    $inputType = $ft === 'email' ? 'email' : ($ft === 'phone_number' ? 'tel' : 'text');
                                                                    $pat = $ft === 'phone_number' ? 'pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"' : '';
                                                                    $ph = trim((string) ($f['placeholder'] ?? ''));
                                                                    if ($ph === '') {
                                                                        $ph = $ft === 'phone_number' ? '09XXXXXXXXX' : $lbl;
                                                                    }
                                                                @endphp
                                                                <label style="display:block;margin-bottom:4px;color:{{ $formLabelColor }};">{{ $lbl }}</label>
                                                                <input class="builder-form-input" type="{{ $inputType }}" name="{{ $nm }}" {{ $req ? 'required' : '' }} {!! $pat !!} placeholder="{{ $ph }}" style="--fb-placeholder-color:{{ $formPlaceholderColor }};width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:8px;box-sizing:border-box;">
                                                            @endforeach
                                                            <div style="display:flex;justify-content:{{ $formButtonJustify }};">
                                                                <button type="submit" class="btn" style="margin-top:2px;background:{{ $formButtonBgColor }};color:{{ $formButtonTextColor }};font-weight:{{ $formButtonFontWeight }};font-style:{{ $formButtonFontStyle }};">{{ $content !== '' ? $content : 'Submit' }}</button>
                                                            </div>
                </form>
                                                    @else
                                                        <form onsubmit="return false;" style="{{ $formInlineStyle }}">
                                                            @foreach($formFields as $f)
                                                                @php
                                                                    $ft = strtolower(trim((string) ($f['type'] ?? 'text')));
                                                                    $lbl = trim((string) ($f['label'] ?? '')) !== '' ? $f['label'] : ($ft ?: 'Field');
                                                                    $ph = trim((string) ($f['placeholder'] ?? ''));
                                                                    if ($ph === '') {
                                                                        $ph = $ft === 'phone_number' ? '09XXXXXXXXX' : $lbl;
                                                                    }
                                                                @endphp
                                                                <label style="display:block;margin-bottom:4px;color:{{ $formLabelColor }};">{{ $lbl }}</label>
                                                                <input class="builder-form-input" type="text" placeholder="{{ $ph }}" style="--fb-placeholder-color:{{ $formPlaceholderColor }};width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:8px;box-sizing:border-box;" @if($isPreview) disabled @endif>
                                                            @endforeach
                                                            <div style="display:flex;justify-content:{{ $formButtonJustify }};">
                                                                <button type="button" class="btn" style="margin-top:2px;background:{{ $formButtonBgColor }};color:{{ $formButtonTextColor }};font-weight:{{ $formButtonFontWeight }};font-style:{{ $formButtonFontStyle }};" @if($isPreview) disabled @endif>{{ $content !== '' ? $content : 'Submit' }}</button>
                                                            </div>
                    </form>
                                                    @endif
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

            @else
                <h2>{{ $step->title }}</h2>
                @if($step->subtitle)
                    <h3 class="subtitle">{{ $step->subtitle }}</h3>
                @endif
                @if(!$isPreview || trim((string) ($step->content ?? '')) !== '')
                    <div class="content">{{ $step->content }}</div>
                @endif
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
            var mode=(car.getAttribute("data-mode")||"manual").toLowerCase();
            if(mode!=="auto"&&mode!=="manual")mode="manual";
            var dotsWrap=car.querySelector("[data-carousel-dots]");
            var dots=dotsWrap?dotsWrap.querySelectorAll("[data-carousel-dot]"):[];
            var autoTimer=null;
            function paint(){
                track.style.transform="translateX(" + (-index*100) + "%)";
                if(dots && dots.length){
                    dots.forEach(function(dot,di){
                        if(di===index)dot.classList.add("is-active");
                        else dot.classList.remove("is-active");
                    });
                }
            }
            function restartAuto(){
                if(autoTimer){clearInterval(autoTimer);autoTimer=null;}
                if(mode==="auto"&&total>1){
                    autoTimer=setInterval(function(){
                        index=(index+1)%total;
                        paint();
                    },3000);
                }
            }
            var prev=car.querySelector("[data-carousel-prev]");
            var next=car.querySelector("[data-carousel-next]");
            if(prev)prev.addEventListener("click",function(e){e.preventDefault();index=(index-1+total)%total;paint();restartAuto();});
            if(next)next.addEventListener("click",function(e){e.preventDefault();index=(index+1)%total;paint();restartAuto();});
            if(dots && dots.length){
                dots.forEach(function(dot){
                    dot.addEventListener("click",function(e){
                        e.preventDefault();
                        var target=parseInt(dot.getAttribute("data-carousel-dot")||"0",10);
                        if(isNaN(target)||target<0||target>=total)return;
                        index=target;
                        paint();
                        restartAuto();
                    });
                });
            }
            paint();
            restartAuto();
        });
        var isPreview={{ ($isPreview ?? false) ? 'true' : 'false' }};
        var editorCanvasWidth={{ (int) ($editorCanvasWidth ?? 0) }};
        if(isPreview&&editorCanvasWidth>0){
            var applyPreviewScale=function(){
                var content=document.querySelector(".step-content--full");
                if(!content)return;
                content.style.transform="none";
                content.style.width=editorCanvasWidth+"px";
                content.style.maxWidth="none";
                var targetPad=10;
                var vw=document.documentElement?document.documentElement.clientWidth:window.innerWidth;
                var availW=vw-(targetPad*2);
                if(availW<200)availW=window.innerWidth;
                var scale=availW/editorCanvasWidth;
                if(scale<=0)scale=1;
                content.style.padding=(targetPad/scale)+"px";
                var h=content.scrollHeight||content.offsetHeight||0;
                content.style.transformOrigin="top left";
                content.style.transform="scale("+scale+")";
                content.style.height=(h*scale)+"px";
                document.body.style.overflowX="hidden";
            };
            applyPreviewScale();
            window.addEventListener("resize",function(){applyPreviewScale();});
        }
    })();
    </script>
</body>
</html>
