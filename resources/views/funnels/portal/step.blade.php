@php
    $publishedFreeformCanvasWidth = 0;
    $initialLayout = is_array($step->layout_json ?? null) ? $step->layout_json : [];
    $initialRootItems = is_array($initialLayout['root'] ?? null) ? $initialLayout['root'] : [];
    if (count($initialRootItems) === 0 && is_array($initialLayout['sections'] ?? null)) {
        foreach ($initialLayout['sections'] as $legacySection) {
            if (!is_array($legacySection)) {
                continue;
            }
            $initialRootItems[] = array_merge(['kind' => 'section'], $legacySection);
        }
    }
    $initialFreeformEls = [];
    foreach ($initialRootItems as $initialRootItem) {
        if (!is_array($initialRootItem)) {
            continue;
        }
        $initialKind = strtolower((string) ($initialRootItem['kind'] ?? 'section'));
        if (in_array($initialKind, ['section', 'row', 'column', 'col'], true)) {
            continue;
        }
        $initialFreeformEls[] = $initialRootItem;
    }
    $initialEditorMeta = is_array($initialLayout['__editor'] ?? null) ? $initialLayout['__editor'] : [];
    $initialCanvasWidthRaw = (int) ($initialEditorMeta['canvasWidth'] ?? 0);
    $initialCanvasInnerWidthRaw = (int) ($initialEditorMeta['canvasInnerWidth'] ?? 0);
    $publishedFreeformCanvasWidth = $initialCanvasWidthRaw > 0
        ? max(0, $initialCanvasWidthRaw - 22)
        : ($initialCanvasInnerWidthRaw > 0 ? max(0, $initialCanvasInnerWidthRaw - 20) : 0);
    if ($publishedFreeformCanvasWidth <= 0) {
        $initialDerivedCanvasWidth = 0;
        foreach ($initialFreeformEls as $initialFreeformEl) {
            if (!is_array($initialFreeformEl)) {
                continue;
            }
            $initialFreeformStyle = is_array($initialFreeformEl['style'] ?? null) ? $initialFreeformEl['style'] : [];
            $initialFreeformSettings = is_array($initialFreeformEl['settings'] ?? null) ? $initialFreeformEl['settings'] : [];
            $initialLeft = (int) ($initialFreeformSettings['freeX'] ?? 0);
            if ($initialLeft <= 0) {
                $initialLeft = (int) str_replace('px', '', (string) ($initialFreeformStyle['left'] ?? '0'));
            }
            $initialWidth = (int) str_replace('px', '', (string) ($initialFreeformStyle['width'] ?? '0'));
            if ($initialWidth <= 0) {
                $initialWidth = (int) ($initialFreeformSettings['fixedWidth'] ?? 0);
            }
            if ($initialWidth <= 0) {
                $initialWidth = 120;
            }
            $initialRight = $initialLeft + $initialWidth;
            if ($initialRight > $initialDerivedCanvasWidth) {
                $initialDerivedCanvasWidth = $initialRight;
            }
        }
        $publishedFreeformCanvasWidth = $initialDerivedCanvasWidth;
    }
@endphp
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
        body.is-published .builder-section--freeform {
            margin: 0 auto;
            width: {{ $publishedFreeformCanvasWidth > 0 ? $publishedFreeformCanvasWidth . 'px' : '100%' }};
            max-width: none;
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
            font-weight: inherit;
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
        .builder-section--freeform .builder-col { overflow: visible; background: transparent; min-width: 0; min-height: 0; padding: 0; margin: 0; }
        .builder-section--freeform .builder-col-inner { overflow: visible; position: relative; }
        .builder-section--freeform .builder-el { margin-top: 0 !important; }
        .builder-section-inner { width: 100%; box-sizing: border-box; position: relative; }
        .builder-row-inner { width: 100%; box-sizing: border-box; display: flex; flex-wrap: wrap; gap: 8px; }
        .builder-col-inner { width: 100%; box-sizing: border-box; max-width: 100%; overflow: hidden; position: relative; }
        .builder-row { display: flex; gap: 8px; flex-wrap: wrap; padding: 6px; }
        .builder-col { min-width: 240px; min-height: 24px; flex: 1 1 0; position: relative; overflow: hidden; background: #ffffff; }
        .builder-col > .builder-col-inner > .builder-el { max-width: 100%; overflow: hidden; }
        .builder-col.builder-col--abs { overflow: visible; }
        .builder-col.builder-col--abs > .builder-col-inner { overflow: visible; position: relative; }
        .builder-col.builder-col--abs > .builder-col-inner > .builder-el { max-width: none; overflow: visible; }
        .builder-col.builder-col--abs .builder-el + .builder-el { margin-top: 0; }
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
        .builder-testimonial { display: grid; gap: 10px; }
        .builder-testimonial-quote { font-style: italic; line-height: 1.5; color: #334155; }
        .builder-testimonial-author { display: flex; align-items: center; gap: 10px; }
        .builder-testimonial-avatar { width: 42px; height: 42px; border-radius: 999px; object-fit: cover; background: #e2e8f0; flex-shrink: 0; }
        .builder-testimonial-name { font-weight: 800; color: #0f172a; }
        .builder-testimonial-role { font-size: 12px; color: #64748b; }
        .builder-faq { display: grid; gap: 10px; }
        .builder-faq-item { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .builder-faq-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .builder-faq-question { font-weight: 800; color: #0f172a; }
        .builder-faq-answer { color: #475569; font-size: 13px; margin-top: 4px; white-space: pre-wrap; }
        .builder-pricing { display: grid; gap: 12px; }
        .builder-pricing-badge { align-self: flex-start; background: #e2e8f0; color: #0f172a; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; }
        .builder-pricing-title { font-size: 18px; font-weight: 900; color: #0f172a; }
        .builder-pricing-price { font-size: 32px; font-weight: 900; color: #16a34a; }
        .builder-pricing-period { font-size: 12px; color: #64748b; margin-left: 4px; }
        .builder-pricing-subtitle { font-size: 12px; color: #64748b; }
        .builder-pricing-features { list-style: none; padding: 0; margin: 0; display: grid; gap: 6px; }
        .builder-pricing-features li { display: flex; align-items: flex-start; gap: 6px; font-size: 12px; color: #334155; }
        .builder-pricing-cta { display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 8px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; }
        .builder-countdown { display: grid; gap: 10px; }
        .builder-countdown-status { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; }
        .builder-countdown-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
        .builder-countdown-box { padding: 8px; border-radius: 10px; background: rgba(15, 23, 42, 0.04); text-align: center; }
        .builder-countdown-num { font-weight: 800; font-size: 20px; color: #0f172a; }
        .builder-countdown-unit { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; }
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
        $editorCanvasWidth = $canvasWidthRaw > 0 ? max(0, $canvasWidthRaw - 22) : ($canvasInnerWidthRaw > 0 ? max(0, $canvasInnerWidthRaw - 20) : 0);
        if ($editorCanvasWidth <= 0) {
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
                $ffRight = $ffLeft + $ffWidth;
                if ($ffRight > $derivedCanvasWidth) $derivedCanvasWidth = $ffRight;
            }
            $editorCanvasWidth = $derivedCanvasWidth;
        }
        $hasBuilderLayout = count($renderSections) > 0;
        $activeSteps = collect($allSteps ?? [])->values()->filter(fn ($s) => isset($s->id, $s->slug));
        $activeStepsBySlug = $activeSteps->keyBy(fn ($s) => strtolower(trim((string) $s->slug)));
        $normalizeStepType = function (?string $type): string {
            $type = strtolower(trim((string) $type));
            if (in_array($type, ['upsell', 'downsell'], true)) {
                return 'sales';
            }
            return $type !== '' ? $type : 'custom';
        };
        $currentStepType = $normalizeStepType($step->type ?? '');
        $currentStepIndex = $activeSteps->search(fn ($s) => (int) $s->id === (int) $step->id);
        if ($currentStepIndex === false) {
            $currentStepIndex = null;
        }
        $findStepByTypes = function (array $types) use ($activeSteps, $step, $normalizeStepType, $currentStepIndex) {
            $wanted = collect($types)
                ->map(fn ($type) => $normalizeStepType((string) $type))
                ->filter()
                ->unique()
                ->values();
            if ($wanted->isEmpty()) {
                return null;
            }
            if ($currentStepIndex !== null) {
                for ($i = $currentStepIndex + 1; $i < $activeSteps->count(); $i++) {
                    $candidate = $activeSteps->get($i);
                    if (!$candidate || (int) $candidate->id === (int) $step->id) {
                        continue;
                    }
                    if ($wanted->contains($normalizeStepType($candidate->type ?? ''))) {
                        return $candidate;
                    }
                }
            }
            foreach ($activeSteps as $candidate) {
                if ((int) $candidate->id === (int) $step->id) {
                    continue;
                }
                if ($wanted->contains($normalizeStepType($candidate->type ?? ''))) {
                    return $candidate;
                }
            }
            return null;
        };
        $choosePricingTarget = function (?string $label) use ($currentStepType, $nextStep, $step, $findStepByTypes, $activeSteps) {
            $text = strtolower(trim((string) $label));
            $homeStep = $findStepByTypes(['landing']) ?: $activeSteps->first(fn ($candidate) => (int) $candidate->id !== (int) $step->id);
            $optInStep = $findStepByTypes(['opt_in']);
            $salesStep = $findStepByTypes(['sales']);
            $checkoutStep = $findStepByTypes(['checkout']);
            $thankYouStep = $findStepByTypes(['thank_you']);
            $customStep = $findStepByTypes(['custom']);
            if (preg_match('/(^|\s)(home|back)(\s|$)/', $text) === 1) {
                return $homeStep;
            }
            if (preg_match('/(checkout|purchase|buy|order|seat|spot|bundle|membership)/', $text) === 1 && $checkoutStep) {
                return $checkoutStep;
            }
            if (preg_match('/(community|resource|download|calendar)/', $text) === 1 && $customStep) {
                return $customStep;
            }
            if (in_array($currentStepType, ['landing', 'opt_in', 'custom'], true) && preg_match('/(price|pricing|offer|package|plan)/', $text) === 1 && $salesStep) {
                return $salesStep;
            }
            if ($currentStepType === 'landing') {
                return $optInStep ?: $salesStep ?: $checkoutStep ?: $nextStep ?: $thankYouStep ?: $customStep ?: $homeStep;
            }
            if ($currentStepType === 'opt_in') {
                return $salesStep ?: $checkoutStep ?: $thankYouStep ?: $customStep ?: $nextStep ?: $homeStep;
            }
            if ($currentStepType === 'sales') {
                return $checkoutStep ?: $thankYouStep ?: $customStep ?: $nextStep ?: $homeStep;
            }
            if ($currentStepType === 'custom') {
                return $checkoutStep ?: $salesStep ?: $thankYouStep ?: $nextStep ?: $homeStep ?: $customStep;
            }
            if ($currentStepType === 'checkout') {
                return $thankYouStep ?: $customStep ?: $nextStep ?: $homeStep;
            }
            if ($currentStepType === 'thank_you') {
                return $customStep ?: $homeStep ?: $nextStep ?: $activeSteps->first(fn ($candidate) => (int) $candidate->id !== (int) $step->id);
            }
            return $optInStep ?: $salesStep ?: $checkoutStep ?: $thankYouStep ?: $homeStep ?: $nextStep ?: $activeSteps->first(fn ($candidate) => (int) $candidate->id !== (int) $step->id);
        };
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
        $resolvePricingCtaAction = function (array $settings) use ($choosePricingTarget, $resolveButtonAction, $activeStepsBySlug, $step) {
            $stepType = strtolower(trim((string) ($step->type ?? '')));
            if ($stepType === 'checkout') {
                return $resolveButtonAction(['actionType' => 'checkout']);
            }
            $actionType = strtolower(trim((string) ($settings['ctaActionType'] ?? '')));
            $link = trim((string) ($settings['ctaLink'] ?? '#'));
            if ($actionType === '') {
                $actionType = ($link !== '' && $link !== '#') ? 'link' : 'next_step';
            }

            if (in_array($stepType, ['upsell', 'downsell'], true)) {
                if ($actionType === 'link' && $link !== '' && $link !== '#') {
                    return ['kind' => 'link', 'href' => $link];
                }

                if (in_array($actionType, ['offer_accept', 'offer_decline'], true)) {
                    return $resolveButtonAction(['actionType' => $actionType]);
                }

                return $resolveButtonAction(['actionType' => 'offer_accept']);
            }

            if ($actionType === 'link') {
                return ['kind' => 'link', 'href' => ($link !== '' ? $link : '#')];
            }

            if ($actionType === 'step') {
                $targetSlug = strtolower(trim((string) ($settings['ctaActionStepSlug'] ?? '')));
                if ($targetSlug !== '' && $activeStepsBySlug->has($targetSlug)) {
                    return $resolveButtonAction([
                        'actionType' => 'step',
                        'actionStepSlug' => $targetSlug,
                    ]);
                }
            }

            if ($actionType === 'checkout') {
                $target = $choosePricingTarget($settings['ctaLabel'] ?? $settings['plan'] ?? 'checkout');
                if ($target) {
                    return $resolveButtonAction([
                        'actionType' => 'step',
                        'actionStepSlug' => strtolower(trim((string) ($target->slug ?? ''))),
                    ]);
                }

                return $resolveButtonAction(['actionType' => 'next_step']);
            }

            $target = $choosePricingTarget($settings['ctaLabel'] ?? $settings['plan'] ?? '');
            if ($target) {
                return $resolveButtonAction([
                    'actionType' => 'step',
                    'actionStepSlug' => strtolower(trim((string) ($target->slug ?? ''))),
                ]);
            }

            return $resolveButtonAction(['actionType' => 'next_step']);
        };
        $appendPricingSelectionHref = function (string $href, array $settings, string $pricingId) use ($step) {
            $href = trim($href);
            $pricingId = trim($pricingId);
            if ($href === '' || $href === '#' || $pricingId === '') {
                return $href !== '' ? $href : '#';
            }

            $actionType = strtolower(trim((string) ($settings['ctaActionType'] ?? '')));
            $link = trim((string) ($settings['ctaLink'] ?? '#'));
            if ($actionType === 'link' && $link !== '' && $link !== '#') {
                return $href;
            }

            $glue = str_contains($href, '?') ? '&' : '?';
            $features = [];
            foreach ((is_array($settings['features'] ?? null) ? $settings['features'] : []) as $feature) {
                if (! is_scalar($feature)) {
                    continue;
                }
                $featureText = mb_substr(trim((string) $feature), 0, 200);
                if ($featureText !== '') {
                    $features[] = $featureText;
                }
            }

            return $href . $glue . http_build_query([
                'offer_step' => (string) ($step->slug ?? ''),
                'offer_pricing' => $pricingId,
                'offer_plan' => mb_substr(trim((string) ($settings['plan'] ?? '')), 0, 200),
                'offer_price' => trim((string) ($settings['price'] ?? '')),
                'offer_regular_price' => trim((string) ($settings['regularPrice'] ?? '')),
                'offer_period' => mb_substr(trim((string) ($settings['period'] ?? '')), 0, 60),
                'offer_subtitle' => mb_substr(trim((string) ($settings['subtitle'] ?? '')), 0, 300),
                'offer_badge' => mb_substr(trim((string) ($settings['badge'] ?? '')), 0, 80),
                'offer_features' => json_encode($features, JSON_UNESCAPED_UNICODE),
            ]);
        };
        $resolveMediaUrl = function (?string $src): string {
            $src = trim((string) $src);
            if ($src === '') {
                return '';
            }
            if (preg_match('#^(https?:)?//#i', $src) === 1) {
                return str_starts_with($src, '//') ? ('https:' . $src) : $src;
            }
            if (str_starts_with($src, 'data:') || str_starts_with($src, 'blob:')) {
                return $src;
            }
            if (str_starts_with($src, '/')) {
                return url($src);
            }
            return asset(ltrim($src, '/'));
        };
        $normalizeVideoSource = function (?string $src): string {
            $src = trim((string) $src);
            if ($src === '') {
                return '';
            }
            if (preg_match('#^(https?:)?//#i', $src) === 1) {
                return str_starts_with($src, '//') ? ('https:' . $src) : $src;
            }
            if (preg_match('#^(www\.)?(youtube\.com|youtu\.be|vimeo\.com)/#i', $src) === 1) {
                return 'https://' . ltrim($src, '/');
            }
            return $src;
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
        $clampScale = function ($scale): float {
            $n = (float) $scale;
            if ($n <= 0) return 1.0;
            if ($n < 0.5) return 0.5;
            if ($n > 3.0) return 3.0;
            return $n;
        };
        $scalePxValue = function ($val, float $scale): string {
            $raw = trim((string) $val);
            if ($raw === '') return '';
            if (!preg_match('/^-?\d+(\.\d+)?(px)?$/', $raw)) return $raw;
            $n = (float) str_replace('px', '', $raw);
            return round($n * $scale) . 'px';
        };
        $scalePaddingValue = function ($pad, float $scale) use ($scalePxValue): string {
            $raw = trim((string) $pad);
            if ($raw === '') return '';
            $parts = preg_split('/\s+/', $raw);
            if (!$parts) return '';
            foreach ($parts as $part) {
                if (!preg_match('/^-?\d+(\.\d+)?(px)?$/', $part)) {
                    return $raw;
                }
            }
            $vals = array_map(function ($part) use ($scalePxValue, $scale) {
                return $scalePxValue($part, $scale);
            }, $parts);
            $t = $vals[0] ?? '0px';
            $r = $vals[1] ?? $t;
            $b = $vals[2] ?? $t;
            $l = $vals[3] ?? $r;
            return $t . ' ' . $r . ' ' . $b . ' ' . $l;
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

        @if(session('error'))
            <div class="wrap" style="padding: 12px 2rem 0;">
                <div style="padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; color: #991b1b; font-size: 14px;">
                    {{ session('error') }}
                </div>
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
                                        $hasAbsEl = false;
                                        foreach ($elements as $_el) {
                                            $_elSettings = is_array($_el['settings'] ?? null) ? $_el['settings'] : [];
                                            $_elStyle = is_array($_el['style'] ?? null) ? $_el['style'] : [];
                                            if (trim((string) ($_elSettings['positionMode'] ?? '')) === 'absolute' || trim((string) ($_elStyle['position'] ?? '')) === 'absolute') {
                                                $hasAbsEl = true;
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
                                    <div class="builder-col{{ $hasAbsEl ? ' builder-col--abs' : '' }}" style="{{ $colStyle }}{{ $colHeightStyle }}{{ $colWidthStyle }}">
                                        <div class="builder-col-inner" @if($colInnerMax !== '') style="max-width: {{ $colInnerMax }}; margin: 0 auto;" @endif>
                                        @foreach($elements as $element)
                                            @php
                                                $elId = trim((string) ($element['id'] ?? ''));
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
                                                            $vSrc = $normalizeVideoSource($videoSrcRaw);
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
                                                            $videoFinalSrc = $isYoutubeVimeo ? $videoEmbedUrl : $resolveMediaUrl($videoSrcRaw);
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
                                                                                                            $videoSrcRaw = trim((string) ($ssc['src'] ?? ''));
                                                                                                            $videoSrc = $resolveMediaUrl($videoSrcRaw);
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
                                                @elseif($type === 'testimonial')
                                                    @php
                                                        $tQuote = trim((string) ($settings['quote'] ?? $content));
                                                        if ($tQuote === '') $tQuote = 'Testimonial';
                                                        $tName = trim((string) ($settings['name'] ?? ''));
                                                        $tRole = trim((string) ($settings['role'] ?? ''));
                                                        $tAvatar = trim((string) ($settings['avatar'] ?? ''));
                                                        $tColor = trim((string) ($rawStyle['color'] ?? ''));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $tColor)) $tColor = '';
                                                        $scale = $clampScale($settings['contentScale'] ?? 1);
                                                        $scaledContentStyle = $contentStyle;
                                                        if ($scaledContentStyle !== '' && substr($scaledContentStyle, -1) !== ';') {
                                                            $scaledContentStyle .= ';';
                                                        }
                                                        $testimonialGap = (int) round(10 * $scale);
                                                        $scaledContentStyle .= 'gap:' . $testimonialGap . 'px;';
                                                        $pad = trim((string) ($rawStyle['padding'] ?? ''));
                                                        if ($pad !== '') {
                                                            $scaledContentStyle .= 'padding:' . $scalePaddingValue($pad, $scale) . ';';
                                                        }
                                                        $radius = trim((string) ($rawStyle['borderRadius'] ?? ''));
                                                        if ($radius !== '') {
                                                            $scaledContentStyle .= 'border-radius:' . $scalePxValue($radius, $scale) . ';';
                                                        }
                                                        $authorGap = (int) round(10 * $scale);
                                                        $avatarSize = (int) round(42 * $scale);
                                                        $quoteStyle = 'font-size:' . (int) round(16 * $scale) . 'px;';
                                                        $nameStyle = 'font-size:' . (int) round(16 * $scale) . 'px;';
                                                        $roleStyle = 'font-size:' . (int) round(12 * $scale) . 'px;';
                                                        if ($tColor !== '') {
                                                            $quoteStyle .= 'color:' . $tColor . ';';
                                                            $nameStyle .= 'color:' . $tColor . ';';
                                                        }
                                                        if ($tColor !== '') {
                                                            $roleStyle .= 'color:' . $tColor . ';opacity:0.7;';
                                                        }
                                                    @endphp
                                                    <div class="builder-testimonial" style="{{ $scaledContentStyle }}">
                                                        <div class="builder-testimonial-quote" style="{{ $quoteStyle }}">{{ $tQuote }}</div>
                                                        <div class="builder-testimonial-author" style="gap: {{ $authorGap }}px;">
                                                            @if($tAvatar !== '')
                                                                <img class="builder-testimonial-avatar" style="width: {{ $avatarSize }}px; height: {{ $avatarSize }}px;" src="{{ $tAvatar }}" alt="{{ $tName !== '' ? $tName : 'Avatar' }}">
                                                            @endif
                                                            <div>
                                                                <div class="builder-testimonial-name" style="{{ $nameStyle }}">{{ $tName !== '' ? $tName : 'Customer name' }}</div>
                                                                @if($tRole !== '')
                                                                    <div class="builder-testimonial-role" style="{{ $roleStyle }}">{{ $tRole }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($type === 'faq')
                                                    @php
                                                        $faqItems = is_array($settings['items'] ?? null) ? $settings['items'] : [];
                                                        if (count($faqItems) === 0) {
                                                            $faqItems = [['q' => 'Question 1', 'a' => 'Answer 1']];
                                                        }
                                                        $faqQColor = trim((string) ($settings['questionColor'] ?? '#0f172a'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $faqQColor)) $faqQColor = '#0f172a';
                                                        $faqAColor = trim((string) ($settings['answerColor'] ?? '#475569'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $faqAColor)) $faqAColor = '#475569';
                                                        $faqGap = (int) ($settings['itemGap'] ?? 10);
                                                        if ($faqGap < 0) $faqGap = 0;
                                                        $scale = $clampScale($settings['contentScale'] ?? 1);
                                                        $scaledGap = (int) round($faqGap * $scale);
                                                        $faqGapStyle = $scaledGap > 0 ? 'gap:' . $scaledGap . 'px;' : '';
                                                        $faqInlineStyle = $contentStyle;
                                                        if ($faqInlineStyle !== '' && substr($faqInlineStyle, -1) !== ';') {
                                                            $faqInlineStyle .= ';';
                                                        }
                                                        if ($faqGapStyle !== '') {
                                                            $faqInlineStyle .= $faqGapStyle;
                                                        }
                                                        $pad = trim((string) ($rawStyle['padding'] ?? ''));
                                                        if ($pad !== '') {
                                                            $faqInlineStyle .= 'padding:' . $scalePaddingValue($pad, $scale) . ';';
                                                        }
                                                        $radius = trim((string) ($rawStyle['borderRadius'] ?? ''));
                                                        if ($radius !== '') {
                                                            $faqInlineStyle .= 'border-radius:' . $scalePxValue($radius, $scale) . ';';
                                                        }
                                                        $faqQStyle = 'font-size:' . (int) round(16 * $scale) . 'px;color:' . $faqQColor . ';';
                                                        $faqAStyle = 'font-size:' . (int) round(13 * $scale) . 'px;color:' . $faqAColor . ';';
                                                    @endphp
                                                    <div class="builder-faq" style="{{ $faqInlineStyle }}">
                                                        @foreach($faqItems as $fi => $faq)
                                                            @php
                                                                $fq = trim((string) ($faq['q'] ?? ''));
                                                                $fa = trim((string) ($faq['a'] ?? ''));
                                                                if ($fq === '') $fq = 'Question ' . ($fi + 1);
                                                                if ($fa === '') $fa = 'Answer ' . ($fi + 1);
                                                            @endphp
                                                            <div class="builder-faq-item">
                                                                <div class="builder-faq-question" style="{{ $faqQStyle }}">{{ $fq }}</div>
                                                                <div class="builder-faq-answer" style="{{ $faqAStyle }}">{{ $fa }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif($type === 'pricing')
                                                    @php
                                                        $plan = trim((string) ($settings['plan'] ?? 'Plan'));
                                                        $priceVal = trim((string) ($settings['price'] ?? '₱0'));
                                                        $regularPrice = trim((string) ($settings['regularPrice'] ?? ''));
                                                        if (preg_match('/^\s*\$/', $priceVal) === 1) {
                                                            $priceVal = preg_replace('/^\s*\$/', "\u{20B1}", $priceVal) ?? $priceVal;
                                                        }
                                                        if (preg_match('/^\s*\$/', $regularPrice) === 1) {
                                                            $regularPrice = preg_replace('/^\s*\$/', "\u{20B1}", $regularPrice) ?? $regularPrice;
                                                        }
                                                        $period = trim((string) ($settings['period'] ?? ''));
                                                        $subtitle = trim((string) ($settings['subtitle'] ?? ''));
                                                        $badge = trim((string) ($settings['badge'] ?? ''));
                                                        $features = is_array($settings['features'] ?? null) ? $settings['features'] : [];
                                                        $selectedCheckoutPricing = ($currentStepType === 'checkout' && is_array($selectedPricing ?? null)) ? $selectedPricing : null;
                                                        if (is_array($selectedCheckoutPricing)) {
                                                            $selectedPlan = trim((string) ($selectedCheckoutPricing['plan'] ?? ''));
                                                            $selectedPrice = trim((string) ($selectedCheckoutPricing['price'] ?? ''));
                                                            $selectedRegularPrice = trim((string) ($selectedCheckoutPricing['regularPrice'] ?? ''));
                                                            $selectedPeriod = trim((string) ($selectedCheckoutPricing['period'] ?? ''));
                                                            $selectedSubtitle = trim((string) ($selectedCheckoutPricing['subtitle'] ?? ''));
                                                            $selectedBadge = trim((string) ($selectedCheckoutPricing['badge'] ?? ''));
                                                            $selectedFeatures = is_array($selectedCheckoutPricing['features'] ?? null) ? $selectedCheckoutPricing['features'] : [];
                                                            if ($selectedPlan !== '') $plan = $selectedPlan;
                                                            if ($selectedPrice !== '') $priceVal = $selectedPrice;
                                                            if ($selectedRegularPrice !== '') $regularPrice = $selectedRegularPrice;
                                                            if ($selectedPeriod !== '') $period = $selectedPeriod;
                                                            if ($selectedSubtitle !== '') $subtitle = $selectedSubtitle;
                                                            if ($selectedBadge !== '') $badge = $selectedBadge;
                                                            if (count($selectedFeatures) > 0) $features = $selectedFeatures;
                                                        }
                                                        if (count($features) === 0) {
                                                            $features = ['Feature one', 'Feature two', 'Feature three'];
                                                        }
                                                        $pricingTextColor = trim((string) ($rawStyle['color'] ?? ''));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $pricingTextColor)) $pricingTextColor = '';
                                                        $pricingCtaAction = $resolvePricingCtaAction($settings);
                                                        $ctaLabelRaw = array_key_exists('ctaLabel', $settings) ? trim((string) $settings['ctaLabel']) : '';
                                                        $ctaLabel = $currentStepType === 'checkout'
                                                            ? 'Pay Now'
                                                            : ($ctaLabelRaw !== '' ? $ctaLabelRaw : ($currentStepType === 'sales' && $plan !== '' ? 'Choose ' . $plan : 'Get Started'));
                                                        $pricingCtaHref = ($pricingCtaAction['kind'] ?? 'link') === 'link'
                                                            ? $appendPricingSelectionHref((string) ($pricingCtaAction['href'] ?? '#'), $settings, (string) $elId)
                                                            : '#';
                                                        $pricingPostedAmountSource = $priceVal !== '' ? $priceVal : $regularPrice;
                                                        $pricingPostedAmount = 0.0;
                                                        if ($pricingPostedAmountSource !== '') {
                                                            $pricingPostedAmountClean = preg_replace('/[^0-9,.\-]/', '', $pricingPostedAmountSource);
                                                            if (is_string($pricingPostedAmountClean) && $pricingPostedAmountClean !== '') {
                                                                $pricingPostedAmount = (float) str_replace(',', '', $pricingPostedAmountClean);
                                                            }
                                                        }
                                                        $ctaBg = trim((string) ($settings['ctaBgColor'] ?? '#0f172a'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $ctaBg)) $ctaBg = '#0f172a';
                                                        $ctaText = trim((string) ($settings['ctaTextColor'] ?? '#ffffff'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $ctaText)) $ctaText = '#ffffff';
                                                        $promoKey = trim((string) ($settings['promoKey'] ?? ''));
                                                        $linkedPricingIdsRaw = $settings['linkedPricingIds'] ?? [];
                                                        $linkedPricingIds = [];
                                                        if (is_array($linkedPricingIdsRaw)) {
                                                            foreach ($linkedPricingIdsRaw as $lp) {
                                                                $lp = trim((string) $lp);
                                                                if ($lp !== '' && !in_array($lp, $linkedPricingIds, true)) $linkedPricingIds[] = $lp;
                                                            }
                                                        } elseif (is_string($linkedPricingIdsRaw)) {
                                                            $lp = trim($linkedPricingIdsRaw);
                                                            if ($lp !== '') $linkedPricingIds[] = $lp;
                                                        }
                                                        $legacyLinked = trim((string) ($settings['linkedPricingId'] ?? ''));
                                                        if (!$linkedPricingIds && $legacyLinked !== '') $linkedPricingIds = [$legacyLinked];
                                                        $linkedPricingId = $linkedPricingIds[0] ?? '';
                                                        $linkedPricingIdsAttr = implode(',', $linkedPricingIds);
                                                        $scale = $clampScale($settings['contentScale'] ?? 1);
                                                        $scaledContentStyle = $contentStyle;
                                                        if ($scaledContentStyle !== '' && substr($scaledContentStyle, -1) !== ';') {
                                                            $scaledContentStyle .= ';';
                                                        }
                                                        $pricingGap = (int) round(12 * $scale);
                                                        $scaledContentStyle .= 'gap:' . $pricingGap . 'px;';
                                                        $pad = trim((string) ($rawStyle['padding'] ?? ''));
                                                        if ($pad !== '') {
                                                            $scaledContentStyle .= 'padding:' . $scalePaddingValue($pad, $scale) . ';';
                                                        }
                                                        $radius = trim((string) ($rawStyle['borderRadius'] ?? ''));
                                                        if ($radius !== '') {
                                                            $scaledContentStyle .= 'border-radius:' . $scalePxValue($radius, $scale) . ';';
                                                        }
                                                        $badgeStyle = 'font-size:' . (int) round(11 * $scale) . 'px;padding:' . (int) round(4 * $scale) . 'px ' . (int) round(10 * $scale) . 'px;';
                                                        $titleStyle = 'font-size:' . (int) round(18 * $scale) . 'px;';
                                                        $priceStyle = 'font-size:' . (int) round(32 * $scale) . 'px;';
                                                        $periodStyle = 'font-size:' . (int) round(12 * $scale) . 'px;';
                                                        $subtitleStyle = 'font-size:' . (int) round(12 * $scale) . 'px;';
                                                        $featureStyle = 'font-size:' . (int) round(12 * $scale) . 'px;gap:' . (int) round(6 * $scale) . 'px;';
                                                        $featureGapStyle = 'gap:' . (int) round(6 * $scale) . 'px;';
                                                        $ctaStyle = 'font-size:' . (int) round(16 * $scale) . 'px;padding:' . (int) round(8 * $scale) . 'px ' . (int) round(12 * $scale) . 'px;';
                                                        $pricingFeaturesJson = json_encode(array_values($features), JSON_UNESCAPED_UNICODE);
                                                        $checkoutSelectionPricingId = trim((string) ($selectedCheckoutPricing['pricingId'] ?? ''));
                                                        if ($checkoutSelectionPricingId === '') {
                                                            $checkoutSelectionPricingId = (string) $elId;
                                                        }
                                                        $checkoutSelectionSourceStep = trim((string) ($selectedCheckoutPricing['sourceStepSlug'] ?? ''));
                                                    @endphp
                                                    <div class="builder-pricing" data-pricing-id="{{ $elId }}" data-pricing-key="{{ $promoKey }}" data-pricing-plan="{{ $plan }}" data-pricing-sale="{{ $priceVal }}" data-pricing-regular="{{ $regularPrice }}" data-pricing-period="{{ $period }}" data-pricing-subtitle="{{ $subtitle }}" data-pricing-badge="{{ $badge }}" data-pricing-features="{{ $pricingFeaturesJson }}" style="{{ $scaledContentStyle }}">
                                                        @if($badge !== '')
                                                            <div class="builder-pricing-badge" style="{{ $badgeStyle }}">{{ $badge }}</div>
                                                        @endif
                                                        <div class="builder-pricing-title" style="{{ $titleStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }};@endif">{{ $plan !== '' ? $plan : 'Plan' }}</div>
                                                        <div>
                                                            <span class="builder-pricing-price" data-pricing-price style="{{ $priceStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }};@endif">{{ $priceVal !== '' ? $priceVal : '₱0' }}</span>
                                                            @if($period !== '')
                                                                <span class="builder-pricing-period" style="{{ $periodStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }}; opacity: 0.7;@endif">{{ $period }}</span>
                                                            @endif
                                                        </div>
                                                        @if($subtitle !== '')
                                                            <div class="builder-pricing-subtitle" style="{{ $subtitleStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }}; opacity: 0.7;@endif">{{ $subtitle }}</div>
                                                        @endif
                                                        <ul class="builder-pricing-features" style="{{ $featureGapStyle }}">
                                                            @foreach($features as $fi => $feat)
                                                                @php
                                                                    $featText = trim((string) $feat);
                                                                    if ($featText === '') $featText = 'Feature ' . ($fi + 1);
                                                                @endphp
                                                                <li style="{{ $featureStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }};@endif"><i class="fas fa-check" aria-hidden="true"></i> {{ $featText }}</li>
                                                            @endforeach
                                                        </ul>
                                                        @if($ctaLabel !== '')
                                                            @if(($pricingCtaAction['kind'] ?? 'link') === 'post')
                                                                <form method="POST" action="{{ $pricingCtaAction['action'] }}" style="margin:0;">
                                                                    @csrf
                                                                    @foreach(($pricingCtaAction['fields'] ?? []) as $fieldName => $fieldValue)
                                                                        <input type="hidden" name="{{ $fieldName }}" value="{{ $fieldName === 'amount' && $pricingPostedAmount > 0 ? $pricingPostedAmount : $fieldValue }}">
                                                                    @endforeach
                                                                    <input type="hidden" name="website" value="">
                                                                    <input type="hidden" name="checkout_pricing_id" value="{{ $checkoutSelectionPricingId }}">
                                                                    <input type="hidden" name="checkout_pricing_source_step" value="{{ $checkoutSelectionSourceStep }}">
                                                                    <input type="hidden" name="checkout_pricing_plan" value="{{ $plan }}">
                                                                    <input type="hidden" name="checkout_pricing_price" value="{{ $priceVal }}">
                                                                    <input type="hidden" name="checkout_pricing_regular_price" value="{{ $regularPrice }}">
                                                                    <input type="hidden" name="checkout_pricing_period" value="{{ $period }}">
                                                                    <input type="hidden" name="checkout_pricing_subtitle" value="{{ $subtitle }}">
                                                                    <input type="hidden" name="checkout_pricing_badge" value="{{ $badge }}">
                                                                    <input type="hidden" name="checkout_pricing_features" value="{{ $pricingFeaturesJson }}">
                                                                    <button type="submit" class="builder-pricing-cta" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }};">{{ $ctaLabel }}</button>
                                                                </form>
                                                            @elseif(($pricingCtaAction['kind'] ?? 'link') === 'disabled')
                                                                <button type="button" class="builder-pricing-cta" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }}; opacity:0.7;cursor:not-allowed;" disabled>{{ $ctaLabel }}</button>
                                                            @else
                                                                <a class="builder-pricing-cta" href="{{ $pricingCtaHref }}" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }};">{{ $ctaLabel }}</a>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @elseif($type === 'countdown')
                                                    @php
                                                        $cdEnd = trim((string) ($settings['endAt'] ?? ''));
                                                        $cdLabel = trim((string) ($settings['label'] ?? 'Offer ends in'));
                                                        $cdExpired = trim((string) ($settings['expiredText'] ?? 'Offer ended'));
                                                        $cdNumberColor = trim((string) ($settings['numberColor'] ?? '#0f172a'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $cdNumberColor)) $cdNumberColor = '#0f172a';
                                                        $cdLabelColor = trim((string) ($settings['labelColor'] ?? '#64748b'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $cdLabelColor)) $cdLabelColor = '#64748b';
                                                        $cdGap = (int) ($settings['itemGap'] ?? 8);
                                                        if ($cdGap < 0) $cdGap = 0;
                                                        $promoKey = trim((string) ($settings['promoKey'] ?? ''));
                                                        $linkedPricingIdsRaw = $settings['linkedPricingIds'] ?? [];
                                                        $linkedPricingIds = [];
                                                        if (is_array($linkedPricingIdsRaw)) {
                                                            foreach ($linkedPricingIdsRaw as $lp) {
                                                                $lp = trim((string) $lp);
                                                                if ($lp !== '' && !in_array($lp, $linkedPricingIds, true)) $linkedPricingIds[] = $lp;
                                                            }
                                                        } elseif (is_string($linkedPricingIdsRaw)) {
                                                            $lp = trim($linkedPricingIdsRaw);
                                                            if ($lp !== '') $linkedPricingIds[] = $lp;
                                                        }
                                                        $legacyLinked = trim((string) ($settings['linkedPricingId'] ?? ''));
                                                        if (!$linkedPricingIds && $legacyLinked !== '') $linkedPricingIds = [$legacyLinked];
                                                        $linkedPricingId = $linkedPricingIds[0] ?? '';
                                                        $linkedPricingIdsAttr = implode(',', $linkedPricingIds);
                                                        $scale = $clampScale($settings['contentScale'] ?? 1);
                                                        $scaledContentStyle = $contentStyle;
                                                        if ($scaledContentStyle !== '' && substr($scaledContentStyle, -1) !== ';') {
                                                            $scaledContentStyle .= ';';
                                                        }
                                                        $cdContainerGap = (int) round(10 * $scale);
                                                        $scaledContentStyle .= 'gap:' . $cdContainerGap . 'px;';
                                                        $pad = trim((string) ($rawStyle['padding'] ?? ''));
                                                        if ($pad !== '') {
                                                            $scaledContentStyle .= 'padding:' . $scalePaddingValue($pad, $scale) . ';';
                                                        }
                                                        $radius = trim((string) ($rawStyle['borderRadius'] ?? ''));
                                                        if ($radius !== '') {
                                                            $scaledContentStyle .= 'border-radius:' . $scalePxValue($radius, $scale) . ';';
                                                        }
                                                        $scaledGap = (int) round($cdGap * $scale);
                                                        $cdGapStyle = $scaledGap > 0 ? 'gap:' . $scaledGap . 'px;' : '';
                                                        $labelStyle = 'font-size:' . (int) round(12 * $scale) . 'px;color:' . $cdLabelColor . ';';
                                                        $numStyle = 'font-size:' . (int) round(20 * $scale) . 'px;color:' . $cdNumberColor . ';';
                                                        $unitStyle = 'font-size:' . (int) round(10 * $scale) . 'px;color:' . $cdLabelColor . ';';
                                                        $boxStyle = 'padding:' . (int) round(8 * $scale) . 'px;';
                                                    @endphp
                                                    <div class="builder-countdown" data-countdown="{{ $cdEnd }}" data-expired="{{ $cdExpired }}" data-promo-key="{{ $promoKey }}" data-linked-pricing-id="{{ $linkedPricingId }}" data-linked-pricing-ids="{{ $linkedPricingIdsAttr }}" style="{{ $scaledContentStyle }}">
                                                        <div class="builder-countdown-status" data-countdown-status style="{{ $labelStyle }}">{{ $cdLabel }}</div>
                                                        <div class="builder-countdown-grid" style="{{ $cdGapStyle }}">
                                                            <div class="builder-countdown-box" style="{{ $boxStyle }}">
                                                                <div class="builder-countdown-num" data-countdown-val="days" style="{{ $numStyle }}">00</div>
                                                                <div class="builder-countdown-unit" style="{{ $unitStyle }}">Days</div>
                                                            </div>
                                                            <div class="builder-countdown-box" style="{{ $boxStyle }}">
                                                                <div class="builder-countdown-num" data-countdown-val="hours" style="{{ $numStyle }}">00</div>
                                                                <div class="builder-countdown-unit" style="{{ $unitStyle }}">Hours</div>
                                                            </div>
                                                            <div class="builder-countdown-box" style="{{ $boxStyle }}">
                                                                <div class="builder-countdown-num" data-countdown-val="minutes" style="{{ $numStyle }}">00</div>
                                                                <div class="builder-countdown-unit" style="{{ $unitStyle }}">Mins</div>
                                                            </div>
                                                            <div class="builder-countdown-box" style="{{ $boxStyle }}">
                                                                <div class="builder-countdown-num" data-countdown-val="seconds" style="{{ $numStyle }}">00</div>
                                                                <div class="builder-countdown-unit" style="{{ $unitStyle }}">Secs</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($type === 'form')
                                                    @php
                                                        $formWidth = trim((string) ($settings['width'] ?? ($settings['formWidth'] ?? ($rawStyle['width'] ?? '100%'))));
                                                        if ($formWidth === '' || !preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $formWidth)) {
                                                            $formWidth = '100%';
                                                        }
                                                        $formLabelColor = trim((string) ($settings['labelColor'] ?? '#240E35'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $formLabelColor)) {
                                                            $formLabelColor = '#240E35';
                                                        }
                                                        $formPlaceholderColor = trim((string) ($settings['placeholderColor'] ?? '#94a3b8'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $formPlaceholderColor)) {
                                                            $formPlaceholderColor = '#94a3b8';
                                                        }
                                                        $formButtonBgColor = trim((string) ($settings['buttonBgColor'] ?? '#240E35'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $formButtonBgColor)) {
                                                            $formButtonBgColor = '#240E35';
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
                                                        $formInlineStyle = 'display:block;width:' . $formWidth . ';max-width:100%;box-sizing:border-box;overflow:auto;text-align:left;';
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
                                                            <input type="hidden" name="website" value="">
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
                                                                <label style="display:block;margin-bottom:4px;color:{{ $formLabelColor }};text-align:left;">{{ $lbl }}</label>
                                                                <input class="builder-form-input" type="{{ $inputType }}" name="{{ $nm }}" {{ $req ? 'required' : '' }} {!! $pat !!} placeholder="{{ $ph }}" style="--fb-placeholder-color:{{ $formPlaceholderColor }};width:100%;padding:8px;border:1px solid #E6E1EF;border-radius:8px;margin-bottom:8px;box-sizing:border-box;text-align:left;">
                                                            @endforeach
                                                            <div style="display:flex;justify-content:{{ $formButtonJustify }};">
                                                                <button type="submit" class="builder-form-btn" style="margin-top:2px;background:{{ $formButtonBgColor }};color:{{ $formButtonTextColor }};font-weight:{{ $formButtonFontWeight }};font-style:{{ $formButtonFontStyle }};border-radius:8px;padding:8px 12px;border:1px solid {{ $formButtonBgColor }};line-height:1;">{{ $content !== '' ? $content : 'Submit' }}</button>
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
                                                                <label style="display:block;margin-bottom:4px;color:{{ $formLabelColor }};text-align:left;">{{ $lbl }}</label>
                                                                <input class="builder-form-input" type="text" placeholder="{{ $ph }}" style="--fb-placeholder-color:{{ $formPlaceholderColor }};width:100%;padding:8px;border:1px solid #E6E1EF;border-radius:8px;margin-bottom:8px;box-sizing:border-box;text-align:left;" @if($isPreview) disabled @endif>
                                                            @endforeach
                                                            <div style="display:flex;justify-content:{{ $formButtonJustify }};">
                                                                <button type="button" class="builder-form-btn" style="margin-top:2px;background:{{ $formButtonBgColor }};color:{{ $formButtonTextColor }};font-weight:{{ $formButtonFontWeight }};font-style:{{ $formButtonFontStyle }};border-radius:8px;padding:8px 12px;border:1px solid {{ $formButtonBgColor }};line-height:1;" @if($isPreview) disabled @endif>{{ $content !== '' ? $content : 'Submit' }}</button>
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
        var pricingStorageKey="funnel_pricing_selection:"+@json((string) ($funnel->slug ?? ''));
        var currentStepSlug=@json((string) ($step->slug ?? ''));
        var currentStepType=@json((string) ($currentStepType ?? 'custom'));
        var hasServerSelectedPricing={{ is_array($selectedPricing ?? null) ? 'true' : 'false' }};
        var isFirstStep={{ ($isFirstStep ?? false) ? 'true' : 'false' }};
        function escapeHtml(raw){
            return String(raw||"")
                .replace(/&/g,"&amp;")
                .replace(/</g,"&lt;")
                .replace(/>/g,"&gt;")
                .replace(/"/g,"&quot;")
                .replace(/'/g,"&#39;");
        }
        function readStoredPricingSelection(){
            try{
                var raw=window.sessionStorage?window.sessionStorage.getItem(pricingStorageKey):"";
                if(!raw)return null;
                var parsed=JSON.parse(raw);
                return parsed&&typeof parsed==="object"?parsed:null;
            }catch(_e){
                return null;
            }
        }
        function writeStoredPricingSelection(selection){
            if(!selection||typeof selection!=="object"||!window.sessionStorage)return;
            try{
                window.sessionStorage.setItem(pricingStorageKey,JSON.stringify(selection));
            }catch(_e){}
        }
        function clearStoredPricingSelection(){
            if(!window.sessionStorage)return;
            try{
                window.sessionStorage.removeItem(pricingStorageKey);
            }catch(_e){}
        }
        function parsePricingFeatures(raw){
            if(Array.isArray(raw))return raw.filter(function(v){return String(v||"").trim()!=="";}).map(function(v){return String(v||"").trim();});
            var text=String(raw||"").trim();
            if(!text)return [];
            try{
                var parsed=JSON.parse(text);
                if(Array.isArray(parsed)){
                    return parsed.filter(function(v){return String(v||"").trim()!=="";}).map(function(v){return String(v||"").trim();});
                }
            }catch(_e){}
            return [];
        }
        function extractPricingSelection(card){
            if(!card)return null;
            return {
                pricingId:String(card.getAttribute("data-pricing-id")||"").trim(),
                sourceStepSlug:currentStepSlug,
                plan:String(card.getAttribute("data-pricing-plan")||"").trim(),
                price:String(card.getAttribute("data-pricing-sale")||"").trim(),
                regularPrice:String(card.getAttribute("data-pricing-regular")||"").trim(),
                period:String(card.getAttribute("data-pricing-period")||"").trim(),
                subtitle:String(card.getAttribute("data-pricing-subtitle")||"").trim(),
                badge:String(card.getAttribute("data-pricing-badge")||"").trim(),
                features:parsePricingFeatures(card.getAttribute("data-pricing-features")||"")
            };
        }
        function buildPricingHrefWithSelection(href,selection){
            var rawHref=String(href||"").trim();
            if(!rawHref||rawHref==="#")return rawHref||"#";
            if(!selection||!selection.pricingId||!selection.sourceStepSlug)return rawHref;
            try{
                var url=new URL(rawHref,window.location.href);
                if(url.origin!==window.location.origin)return rawHref;
                url.searchParams.set("offer_step",selection.sourceStepSlug);
                url.searchParams.set("offer_pricing",selection.pricingId);
                url.searchParams.set("offer_plan",String(selection.plan||"").trim());
                url.searchParams.set("offer_price",String(selection.price||"").trim());
                url.searchParams.set("offer_regular_price",String(selection.regularPrice||"").trim());
                url.searchParams.set("offer_period",String(selection.period||"").trim());
                url.searchParams.set("offer_subtitle",String(selection.subtitle||"").trim());
                url.searchParams.set("offer_badge",String(selection.badge||"").trim());
                url.searchParams.set("offer_features",JSON.stringify(Array.isArray(selection.features)?selection.features:[]));
                if(url.origin===window.location.origin){
                    return url.pathname+url.search+url.hash;
                }
                return url.toString();
            }catch(_e){
                var glue=rawHref.indexOf("?")>=0?"&":"?";
                return rawHref+glue
                    +"offer_step="+encodeURIComponent(selection.sourceStepSlug)
                    +"&offer_pricing="+encodeURIComponent(selection.pricingId)
                    +"&offer_plan="+encodeURIComponent(String(selection.plan||"").trim())
                    +"&offer_price="+encodeURIComponent(String(selection.price||"").trim())
                    +"&offer_regular_price="+encodeURIComponent(String(selection.regularPrice||"").trim())
                    +"&offer_period="+encodeURIComponent(String(selection.period||"").trim())
                    +"&offer_subtitle="+encodeURIComponent(String(selection.subtitle||"").trim())
                    +"&offer_badge="+encodeURIComponent(String(selection.badge||"").trim())
                    +"&offer_features="+encodeURIComponent(JSON.stringify(Array.isArray(selection.features)?selection.features:[]));
            }
        }
        function ensurePricingChild(card,selector,tagName,className,beforeNode){
            var node=card.querySelector(selector);
            if(node)return node;
            node=document.createElement(tagName);
            node.className=className;
            if(beforeNode&&beforeNode.parentNode===card)card.insertBefore(node,beforeNode);
            else card.appendChild(node);
            return node;
        }
        function applyPricingSelectionToCard(card,selection){
            if(!card||!selection)return;
            var plan=String(selection.plan||"").trim();
            var price=String(selection.price||"").trim();
            var regular=String(selection.regularPrice||"").trim();
            var period=String(selection.period||"").trim();
            var subtitle=String(selection.subtitle||"").trim();
            var badge=String(selection.badge||"").trim();
            var features=parsePricingFeatures(selection.features);
            if(plan!==""){
                var title=card.querySelector(".builder-pricing-title");
                if(title)title.textContent=plan;
                card.setAttribute("data-pricing-plan",plan);
            }
            if(price!==""){
                var priceNode=card.querySelector("[data-pricing-price]");
                if(priceNode)priceNode.textContent=price;
                card.setAttribute("data-pricing-sale",price);
            }
            card.setAttribute("data-pricing-regular",regular);
            card.setAttribute("data-pricing-period",period);
            card.setAttribute("data-pricing-subtitle",subtitle);
            card.setAttribute("data-pricing-badge",badge);
            card.setAttribute("data-pricing-features",JSON.stringify(features));
            var priceNodeParent=(card.querySelector("[data-pricing-price]")||{}).parentNode||null;
            var periodNode=card.querySelector(".builder-pricing-period");
            if(period!==""){
                if(!periodNode&&priceNodeParent){
                    periodNode=document.createElement("span");
                    periodNode.className="builder-pricing-period";
                    priceNodeParent.appendChild(periodNode);
                }
                if(periodNode){
                    periodNode.textContent=period;
                    periodNode.style.display="";
                }
            }else if(periodNode){
                periodNode.style.display="none";
            }
            var titleNode=card.querySelector(".builder-pricing-title");
            var badgeNode=card.querySelector(".builder-pricing-badge");
            if(badge!==""){
                if(!badgeNode){
                    badgeNode=document.createElement("div");
                    badgeNode.className="builder-pricing-badge";
                    if(titleNode&&titleNode.parentNode===card)card.insertBefore(badgeNode,titleNode);
                    else card.insertBefore(badgeNode,card.firstChild||null);
                }
                badgeNode.textContent=badge;
                badgeNode.style.display="";
            }else if(badgeNode){
                badgeNode.style.display="none";
            }
            var featureList=card.querySelector(".builder-pricing-features");
            if(featureList&&features.length){
                var liStyle=((featureList.querySelector("li")&&featureList.querySelector("li").getAttribute("style"))||"").trim();
                featureList.innerHTML=features.map(function(feat){
                    return "<li"+(liStyle?' style="'+escapeHtml(liStyle)+'"':"")+"><i class=\"fas fa-check\" aria-hidden=\"true\"></i> "+escapeHtml(feat)+"</li>";
                }).join("");
            }
            var subtitleNode=card.querySelector(".builder-pricing-subtitle");
            if(subtitle!==""){
                if(!subtitleNode){
                    subtitleNode=document.createElement("div");
                    subtitleNode.className="builder-pricing-subtitle";
                    if(featureList&&featureList.parentNode===card)card.insertBefore(subtitleNode,featureList);
                    else card.appendChild(subtitleNode);
                }
                subtitleNode.textContent=subtitle;
                subtitleNode.style.display="";
            }else if(subtitleNode){
                subtitleNode.style.display="none";
            }
        }
        if(isFirstStep && !/[?&]offer_pricing=/.test(window.location.search||"")){
            clearStoredPricingSelection();
        }
        document.addEventListener("click",function(e){
            var target=e.target&&e.target.closest?e.target.closest(".builder-pricing-cta"):null;
            if(!target)return;
            var card=target.closest("[data-pricing-id]");
            if(!card)return;
            var selection=extractPricingSelection(card);
            if(!selection||!selection.pricingId)return;
            writeStoredPricingSelection(selection);
            if(target.tagName==="A"&&currentStepType!=="checkout"){
                target.setAttribute("href",buildPricingHrefWithSelection(target.getAttribute("href")||"",selection));
            }
        },true);
        if(currentStepType==="checkout"&&!hasServerSelectedPricing){
            var storedSelection=readStoredPricingSelection();
            if(storedSelection&&storedSelection.pricingId){
                document.querySelectorAll("[data-pricing-id]").forEach(function(card){
                    applyPricingSelectionToCard(card,storedSelection);
                });
            }
        }
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
        var countdowns=document.querySelectorAll("[data-countdown]");
        if(countdowns && countdowns.length){
            function pad2(n){return String(n).padStart(2,"0");}
            function parseMoneyToNumber(raw){
                var s=String(raw||"").trim();
                if(!s)return null;
                // Keep digits, commas, and decimal dot only.
                s=s.replace(/[^0-9,.\-]/g,"");
                if(!s)return null;
                // Remove thousand separators.
                s=s.replace(/,/g,"");
                var n=parseFloat(s);
                if(!isFinite(n)||isNaN(n))return null;
                return n;
            }
            function escapeCssIdent(v){
                var raw=String(v||"");
                if(window.CSS && typeof window.CSS.escape==="function")return window.CSS.escape(raw);
                // Minimal fallback: escape quotes/backslashes.
                return raw.replace(/\\/g,"\\\\").replace(/"/g,'\\"');
            }
            function parseCountdownDate(raw){
                var s=String(raw||"").trim();
                if(!s)return null;
                var d=new Date(s);
                if(isNaN(d.getTime()))return null;
                return d;
            }
            function collectLinkedPricingIds(node){
                var linkedRaw=(node.getAttribute("data-linked-pricing-ids")||"").trim();
                var linkedIds=linkedRaw?linkedRaw.split(",").map(function(v){return String(v||"").trim();}).filter(Boolean):[];
                if(!linkedIds.length){
                    var linked=(node.getAttribute("data-linked-pricing-id")||"").trim();
                    if(linked!=="")linkedIds=[linked];
                }
                // De-dupe while preserving order.
                var seen=new Set();
                return linkedIds.filter(function(id){
                    if(!id)return false;
                    if(seen.has(id))return false;
                    seen.add(id);
                    return true;
                });
            }
            function collectPricingTargets(node){
                var linkedIds=collectLinkedPricingIds(node);
                var targets=[];
                if(linkedIds.length){
                    // Preserve the same order as the saved linked IDs.
                    linkedIds.forEach(function(id){
                        var p=document.querySelector('[data-pricing-id="'+escapeCssIdent(id)+'"]');
                        if(p)targets.push(p);
                    });
                    return targets;
                }
                var key=(node.getAttribute("data-promo-key")||"").trim();
                if(!key)return targets;
                var pricing=document.querySelectorAll("[data-pricing-key]");
                pricing.forEach(function(p){
                    if((p.getAttribute("data-pricing-key")||"").trim()===key)targets.push(p);
                });
                return targets;
            }
            function setCountdownValue(node,key,val){
                var el=node.querySelector('[data-countdown-val="'+key+'"]');
                if(!el)return;
                if(key==="days")el.textContent=String(val);
                else el.textContent=pad2(val);
            }
            function updateCountdown(node){
                var endRaw=node.getAttribute("data-countdown")||"";
                var end=parseCountdownDate(endRaw);
                var status=node.querySelector("[data-countdown-status]");
                var expiredText=node.getAttribute("data-expired")||"Expired";
                if(status && !status.getAttribute("data-label")){
                    status.setAttribute("data-label",status.textContent||"");
                }
                if(!end){
                    setCountdownValue(node,"days",0);
                    setCountdownValue(node,"hours",0);
                    setCountdownValue(node,"minutes",0);
                    setCountdownValue(node,"seconds",0);
                    if(status)status.textContent=expiredText;
                    return {expired:true,linkedIds:collectLinkedPricingIds(node),targets:collectPricingTargets(node)};
                }
                var diff=end.getTime()-Date.now();
                if(diff<=0){
                    setCountdownValue(node,"days",0);
                    setCountdownValue(node,"hours",0);
                    setCountdownValue(node,"minutes",0);
                    setCountdownValue(node,"seconds",0);
                    if(status)status.textContent=expiredText;
                    return {expired:true,linkedIds:collectLinkedPricingIds(node),targets:collectPricingTargets(node)};
                }
                if(status)status.textContent=status.getAttribute("data-label")||"";
                var total=Math.floor(diff/1000);
                var days=Math.floor(total/86400);
                total%=86400;
                var hours=Math.floor(total/3600);
                total%=3600;
                var minutes=Math.floor(total/60);
                var seconds=total%60;
                setCountdownValue(node,"days",days);
                setCountdownValue(node,"hours",hours);
                setCountdownValue(node,"minutes",minutes);
                setCountdownValue(node,"seconds",seconds);
                return {expired:false,linkedIds:collectLinkedPricingIds(node),targets:collectPricingTargets(node)};
            }
            var tick=function(){
                var targetState=new Map();
                var visibilityState=new Map();
                countdowns.forEach(function(node){
                    var info=updateCountdown(node);
                    if(!info||!info.targets||!info.targets.length)return;
                    // If a countdown is linked to exactly 2 pricing cards, treat the first as "before expiry"
                    // and the second as "after expiry", and toggle visibility accordingly.
                    if(info.linkedIds && info.linkedIds.length===2){
                        var activeId=info.expired?info.linkedIds[1]:info.linkedIds[0];
                        info.targets.forEach(function(p){
                            var pid=(p.getAttribute("data-pricing-id")||"").trim();
                            if(!pid)return;
                            var vs=visibilityState.get(p);
                            if(!vs)vs={hasRule:true,visible:false};
                            vs.hasRule=true;
                            if(pid===activeId)vs.visible=true;
                            visibilityState.set(p,vs);
                        });
                    }
                    info.targets.forEach(function(p){
                        var st=targetState.get(p);
                        if(!st)st={hasLink:true,active:false};
                        st.hasLink=true;
                        if(!info.expired)st.active=true;
                        targetState.set(p,st);
                    });
                });
                visibilityState.forEach(function(vs,p){
                    if(!vs||!vs.hasRule)return;
                    p.style.display=vs.visible?"":"none";
                });
                targetState.forEach(function(st,p){
                    if(!st||!st.hasLink)return;
                    var sale=(p.getAttribute("data-pricing-sale")||"");
                    var regular=(p.getAttribute("data-pricing-regular")||"");
                    var target=p.querySelector("[data-pricing-price]");
                    if(!target)return;
                    if(st.active){
                        if(sale!=="")target.textContent=sale;
                        else if(regular!=="")target.textContent=regular;
                    }else{
                        if(regular!=="")target.textContent=regular;
                        else if(sale!=="")target.textContent=sale;
                    }
                });
            };
            tick();
            setInterval(tick,1000);
        }

        function findVisiblePricingCard(){
            var cards=Array.from(document.querySelectorAll("[data-pricing-id]")||[]);
            if(!cards.length)return null;
            var visible=cards.filter(function(p){
                // Consider as visible if not display:none and in DOM flow.
                if(!p||!p.getBoundingClientRect)return false;
                if(p.style && String(p.style.display).toLowerCase()==="none")return false;
                return true;
            });
            return (visible[0]||cards[0])||null;
        }
        // Ensure the posted checkout amount matches the visible pricing (sale/regular) when present.
        // This makes PayMongo charge what the customer sees on the page.
        function findVisiblePricingAmount(){
            var target=findVisiblePricingCard();
            if(!target)return null;
            var priceEl=target.querySelector("[data-pricing-price]");
            if(!priceEl)return null;
            return parseMoneyToNumber(priceEl.textContent||"");
        }
        function syncCheckoutPricingForm(form){
            if(!form||!form.querySelector)return;
            var card=findVisiblePricingCard();
            if(!card)return;
            var selection=extractPricingSelection(card)||{};
            var priceEl=card.querySelector("[data-pricing-price]");
            var titleEl=card.querySelector(".builder-pricing-title");
            var periodEl=card.querySelector(".builder-pricing-period");
            var subtitleEl=card.querySelector(".builder-pricing-subtitle");
            var badgeEl=card.querySelector(".builder-pricing-badge");
            var featureEls=Array.from(card.querySelectorAll(".builder-pricing-features li")||[]);
            if(titleEl)selection.plan=String(titleEl.textContent||selection.plan||"").trim();
            if(priceEl)selection.price=String(priceEl.textContent||selection.price||"").trim();
            selection.regularPrice=String(card.getAttribute("data-pricing-regular")||selection.regularPrice||"").trim();
            selection.period=periodEl&&String(periodEl.style.display||"").toLowerCase()!=="none"?String(periodEl.textContent||"").trim():"";
            selection.subtitle=subtitleEl&&String(subtitleEl.style.display||"").toLowerCase()!=="none"?String(subtitleEl.textContent||"").trim():"";
            selection.badge=badgeEl&&String(badgeEl.style.display||"").toLowerCase()!=="none"?String(badgeEl.textContent||"").trim():"";
            selection.features=featureEls.map(function(li){return String(li.textContent||"").trim();}).filter(Boolean);
            var amount=findVisiblePricingAmount();
            var fieldMap={
                checkout_pricing_id:String(selection.pricingId||"").trim(),
                checkout_pricing_source_step:String(selection.sourceStepSlug||"").trim(),
                checkout_pricing_plan:String(selection.plan||"").trim(),
                checkout_pricing_price:String(selection.price||"").trim(),
                checkout_pricing_regular_price:String(selection.regularPrice||"").trim(),
                checkout_pricing_period:String(selection.period||"").trim(),
                checkout_pricing_subtitle:String(selection.subtitle||"").trim(),
                checkout_pricing_badge:String(selection.badge||"").trim(),
                checkout_pricing_features:JSON.stringify(Array.isArray(selection.features)?selection.features:[])
            };
            Object.keys(fieldMap).forEach(function(name){
                var input=form.querySelector('input[name="'+name+'"]');
                if(input)input.value=fieldMap[name];
            });
            var amountInput=form.querySelector('input[name="amount"]');
            if(amountInput && typeof amount==="number" && amount>0){
                amountInput.value=String(amount);
            }
        }
        document.addEventListener("submit",function(e){
            var form=e.target;
            if(!form||form.tagName!=="FORM")return;
            var method=String(form.getAttribute("method")||"").toLowerCase();
            if(method!=="post")return;
            if(form.getAttribute("data-submitting")==="1"){
                e.preventDefault();
                return;
            }
            form.setAttribute("data-submitting","1");
            Array.from(form.querySelectorAll('button[type="submit"], input[type="submit"]')||[]).forEach(function(btn){
                btn.setAttribute("disabled","disabled");
                if(btn.tagName==="BUTTON"){
                    btn.setAttribute("data-original-text",btn.textContent||"");
                    if((btn.textContent||"").trim()!==""){
                        btn.textContent="Processing...";
                    }
                }
            });
            var action=String(form.getAttribute("action")||"");
            if(action.indexOf("/checkout")<0)return;
            syncCheckoutPricingForm(form);
        },true);
        function syncAbsoluteColumnHeights(){
            var cols=document.querySelectorAll(".builder-col.builder-col--abs, .builder-section--freeform .builder-col");
            cols.forEach(function(col){
                var inner=col.querySelector(".builder-col-inner")||col;
                if(!inner)return;
                var children=Array.from(inner.children||[]).filter(function(node){
                    return !!(node&&node.classList&&node.classList.contains("builder-el"));
                });
                if(!children.length)return;
                var maxBottom=0;
                var maxRight=0;
                children.forEach(function(node){
                    var mb=0,mr=0;
                    try{
                        var cs=window.getComputedStyle(node);
                        mb=parseFloat(cs.marginBottom||"0")||0;
                        mr=parseFloat(cs.marginRight||"0")||0;
                    }catch(_e){}
                    var nodeBottom=(node.offsetTop||0)+Math.max(node.offsetHeight||0,node.scrollHeight||0)+mb+12;
                    var nodeRight=(node.offsetLeft||0)+Math.max(node.offsetWidth||0,node.scrollWidth||0)+mr+12;
                    if(nodeBottom>maxBottom)maxBottom=nodeBottom;
                    if(nodeRight>maxRight)maxRight=nodeRight;
                });
                if(maxBottom>0){
                    var currentMinHeight=parseFloat(col.style.minHeight||"0")||0;
                    if(maxBottom>currentMinHeight)col.style.minHeight=Math.ceil(maxBottom)+"px";
                }
                if(col.closest(".builder-section--freeform")&&maxRight>0){
                    var currentWidth=parseFloat(col.style.width||"0")||0;
                    if(currentWidth<=0)col.style.width=Math.ceil(maxRight)+"px";
                }
            });
        }
        function scheduleAbsoluteLayoutSync(){
            window.requestAnimationFrame(function(){
                window.requestAnimationFrame(function(){
                    syncAbsoluteColumnHeights();
                });
            });
        }
        scheduleAbsoluteLayoutSync();
        window.addEventListener("resize",function(){scheduleAbsoluteLayoutSync();});
        window.addEventListener("load",function(){scheduleAbsoluteLayoutSync();});
        if(document.fonts&&document.fonts.ready&&typeof document.fonts.ready.then==="function"){
            document.fonts.ready.then(function(){scheduleAbsoluteLayoutSync();}).catch(function(){});
        }
        Array.from(document.images||[]).forEach(function(img){
            if(!img||img.complete)return;
            img.addEventListener("load",function(){scheduleAbsoluteLayoutSync();},{once:true});
            img.addEventListener("error",function(){scheduleAbsoluteLayoutSync();},{once:true});
        });
        var isPreview={{ ($isPreview ?? false) ? 'true' : 'false' }};
        var editorCanvasWidth={{ (int) ($editorCanvasWidth ?? 0) }};
        if(isPreview&&editorCanvasWidth>0){
            var measurePreviewContentHeight=function(content){
                if(!content||!content.getBoundingClientRect)return 0;
                var rootRect=content.getBoundingClientRect();
                var maxBottom=Math.max(content.scrollHeight||0,content.offsetHeight||0);
                Array.from(content.querySelectorAll("*")||[]).forEach(function(node){
                    if(!node||!node.getBoundingClientRect)return;
                    var tag=String(node.tagName||"").toLowerCase();
                    if(tag==="script"||tag==="style")return;
                    var rect=node.getBoundingClientRect();
                    if((rect.width<=0&&rect.height<=0)||!isFinite(rect.bottom))return;
                    var bottom=rect.bottom-rootRect.top;
                    if(bottom>maxBottom)maxBottom=bottom;
                });
                return Math.ceil(maxBottom);
            };
            var applyPreviewScale=function(){
                var content=document.querySelector(".step-content--full");
                if(!content)return;
                syncAbsoluteColumnHeights();
                content.style.transform="none";
                content.style.height="auto";
                content.style.width=editorCanvasWidth+"px";
                content.style.maxWidth="none";
                var targetPad=10;
                var vw=document.documentElement?document.documentElement.clientWidth:window.innerWidth;
                var availW=vw-(targetPad*2);
                if(availW<200)availW=window.innerWidth;
                var scale=availW/editorCanvasWidth;
                if(scale<=0)scale=1;
                content.style.padding=(targetPad/scale)+"px";
                var h=measurePreviewContentHeight(content);
                content.style.transformOrigin="top left";
                content.style.transform="scale("+scale+")";
                content.style.height=(h*scale)+"px";
                document.body.style.overflowX="hidden";
            };
            var schedulePreviewScale=function(){
                window.requestAnimationFrame(function(){
                    window.requestAnimationFrame(function(){
                        applyPreviewScale();
                    });
                });
            };
            schedulePreviewScale();
            window.addEventListener("resize",function(){schedulePreviewScale();});
            window.addEventListener("load",function(){schedulePreviewScale();});
            if(document.fonts&&document.fonts.ready&&typeof document.fonts.ready.then==="function"){
                document.fonts.ready.then(function(){schedulePreviewScale();}).catch(function(){});
            }
            Array.from(document.images||[]).forEach(function(img){
                if(!img||img.complete)return;
                img.addEventListener("load",function(){schedulePreviewScale();},{once:true});
                img.addEventListener("error",function(){schedulePreviewScale();},{once:true});
            });
        }
    })();
    </script>
</body>
</html>
