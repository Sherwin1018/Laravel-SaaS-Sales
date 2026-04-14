@php
    $publishedFreeformCanvasWidth = 0;
    $previewFreeformRightInset = 0;
    $builderCanvasPaddingWidth = 20;
    $builderCanvasBorderWidth = 2;
    $resolveSavedCanvasWidth = function (array $editorMeta) use ($builderCanvasPaddingWidth, $builderCanvasBorderWidth): int {
        $canvasContentWidthRaw = (int) ($editorMeta['canvasContentWidth'] ?? 0);
        if ($canvasContentWidthRaw > 0) {
            return max(0, $canvasContentWidthRaw);
        }
        $canvasInnerWidthRaw = (int) ($editorMeta['canvasInnerWidth'] ?? 0);
        if ($canvasInnerWidthRaw > 0) {
            return max(0, $canvasInnerWidthRaw - $builderCanvasPaddingWidth);
        }
        $canvasWidthRaw = (int) ($editorMeta['canvasWidth'] ?? 0);
        if ($canvasWidthRaw > 0) {
            return max(0, $canvasWidthRaw - ($builderCanvasPaddingWidth + $builderCanvasBorderWidth));
        }
        return 0;
    };
    $resolveSavedStageWidth = function (array $editorMeta) use ($builderCanvasPaddingWidth, $builderCanvasBorderWidth): int {
        $canvasInnerWidthRaw = (int) ($editorMeta['canvasInnerWidth'] ?? 0);
        if ($canvasInnerWidthRaw > 0) {
            return max(0, $canvasInnerWidthRaw);
        }
        $canvasWidthRaw = (int) ($editorMeta['canvasWidth'] ?? 0);
        if ($canvasWidthRaw > 0) {
            return max(0, $canvasWidthRaw - $builderCanvasBorderWidth);
        }
        $canvasContentWidthRaw = (int) ($editorMeta['canvasContentWidth'] ?? 0);
        if ($canvasContentWidthRaw > 0) {
            return max(0, $canvasContentWidthRaw + $builderCanvasPaddingWidth);
        }
        return 0;
    };
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
    $publishedFreeformCanvasWidth = $resolveSavedCanvasWidth($initialEditorMeta);
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
    if ($initialDerivedCanvasWidth > $publishedFreeformCanvasWidth) {
        $publishedFreeformCanvasWidth = $initialDerivedCanvasWidth;
    }

    $portalHasFreeformCanvas = count($initialFreeformEls) > 0;

    $previewIframeMode = (string) request()->query('preview_iframe') === '1';
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
            padding: 0;
            background: {{ $step->background_color ?: '#ffffff' }};
            border: none;
            border-radius: 0;
            box-shadow: none;
            overflow-x: hidden;
            overflow-y: visible;
        }
        body.is-published:not(.portal-has-freeform-canvas) .step-content--full { padding-top: 0; }
        /* Published freeform: keep canvas anchored to the top so menu/header groups stay close to the next section. */
        body.is-published.portal-has-freeform-canvas .step-content--full {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: stretch;
            padding: 10px 1.5rem 24px;
            box-sizing: border-box;
        }
        body.is-preview .step-content--full { padding: 0; overflow-x: auto; overflow-y: visible; }
        body.portal-has-freeform-canvas .step-content--full { opacity: 0; }
        body.portal-has-freeform-canvas .step-content--full.is-scale-ready { opacity: 1; }
        body.is-preview .builder-section--freeform {
            margin: 0 auto;
            width: {{ $publishedFreeformCanvasWidth > 0 ? ($publishedFreeformCanvasWidth + $previewFreeformRightInset) . 'px' : '100%' }};
            max-width: none;
        }
        body.is-published .builder-section--freeform {
            margin-left: auto;
            margin-right: auto;
            width: {{ $publishedFreeformCanvasWidth > 0 ? $publishedFreeformCanvasWidth . 'px' : '100%' }};
            max-width: 100%;
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
        .builder-section { border-radius: 0; margin-bottom: 0; border: none; padding: 8px; }
        .builder-section:last-child { margin-bottom: 0; }
        .builder-section--freeform { border-radius: 0; padding: 0; margin: 0 -2rem; background: transparent; border: none; width: calc(100% + 4rem); max-width: none; }
        .builder-section--freeform .builder-row { padding: 0; gap: 0; margin: 0; display: block; }
        .builder-section--freeform .builder-row-inner { display: block; gap: 0; }
        .builder-section--freeform .builder-col { overflow: visible; background: transparent; min-width: 0; min-height: 0; padding: 0; margin: 0; }
        .builder-section--freeform .builder-col-inner { overflow: visible; position: relative; }
        .builder-section--freeform .builder-el { margin-top: 0 !important; }
        .builder-row.builder-row--section-elements,
        .builder-row--section-elements > .builder-row-inner,
        .builder-col--section-elements,
        .builder-col--section-elements > .builder-col-inner--section-elements { display: contents; }
        .builder-section-inner { width: 100%; box-sizing: border-box; position: relative; }
        .builder-row-inner { width: 100%; box-sizing: border-box; display: flex; flex-wrap: wrap; gap: 8px; position: relative; align-items: stretch; }
        .builder-col-inner { width: 100%; box-sizing: border-box; max-width: 100%; overflow: hidden; position: relative; min-height: 100%; }
        .builder-row { display: flex; gap: 8px; flex-wrap: wrap; padding: 6px; }
        .builder-col { min-width: 0; min-height: 0; flex: 1 1 0; position: relative; overflow: hidden; background: transparent; padding: 6px; box-sizing: border-box; text-align: center; }
        .builder-col > .builder-col-inner > .builder-el { max-width: 100%; overflow: hidden; box-sizing: border-box; }
        .builder-col > .builder-col-inner > .builder-el[data-element-type="image"],
        .builder-col > .builder-col-inner > .builder-el[data-element-type="video"] { max-width: 100%; }
        .builder-col.builder-col--abs { overflow: visible; }
        .builder-col.builder-col--abs > .builder-col-inner { overflow: visible; position: relative; }
        .builder-col.builder-col--abs > .builder-col-inner > .builder-el { max-width: none; overflow: visible; }
        .builder-col.builder-col--abs .builder-el + .builder-el { margin-top: 0; }
        .builder-el + .builder-el { margin-top: 8px; }
        .builder-heading { margin: 0; font-size: 32px; line-height: 1.2; overflow-wrap: break-word; word-break: break-word; color: #000000; text-align: inherit; }
        .builder-text { margin: 0; color: #334155; line-height: 1.6; white-space: pre-wrap; overflow-wrap: break-word; word-break: break-word; text-align: inherit; }
        .builder-text p,
        .builder-text div,
        .builder-text ul,
        .builder-text ol,
        .builder-text li { margin: 0; }
        .builder-img { display: block; max-width: 100%; height: auto; border-radius: 10px; object-fit: contain; object-position: top center; }
        .builder-image-placeholder { width: 100%; min-height: 140px; border: 2px solid #6ea0ff; border-radius: 12px; background: linear-gradient(180deg, #ffffff, #fbfcff); display: flex; align-items: center; justify-content: center; box-sizing: border-box; }
        .builder-image-placeholder__inner { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 18px; text-align: center; }
        .builder-image-placeholder__plus { width: 58px; height: 58px; border-radius: 999px; background: #d9d9d9; color: #5f6368; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 300; line-height: 1; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.16); }
        .builder-image-placeholder__label { font-size: 14px; font-weight: 600; color: #5f6368; letter-spacing: 0.01em; }
        .builder-menu { width: 100%; }
        .builder-menu-shell { width: 100%; display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 12px; }
        .builder-menu-left { justify-self: start; min-width: 0; }
        .builder-menu-right { justify-self: end; min-width: 0; }
        .builder-menu-center { min-width: 0; justify-self: center; }
        .builder-menu-list { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; justify-content: center; }
        .builder-menu-link { text-decoration: none; text-underline-offset: 3px; font: inherit; }
        .builder-menu-edit-btn { display: inline-block; padding: 8px 14px; border-radius: 999px; text-decoration: none; font-weight: 600; }
        .builder-menu-logo { display: block; max-height: 42px; width: auto; max-width: 180px; object-fit: contain; }
        .builder-menu-logo-placeholder { padding: 8px 12px; border: 1px dashed #cbd5e1; border-radius: 10px; font-size: 12px; color: #64748b; }
        .builder-menu-toggle { display: none; width: 42px; height: 42px; border: 1px solid #cbd5e1; background: #fff; border-radius: 10px; align-items: center; justify-content: center; cursor: pointer; color: #0f172a; }
        .builder-menu-toggle i { font-size: 16px; }
        .builder-menu-mobile-overlay { position: fixed; inset: 0; display: none; z-index: 90; background: rgba(15, 23, 42, 0.4); }
        .builder-menu-mobile-overlay.is-open { display: block; }
        .builder-menu-mobile-drawer { position: absolute; top: 0; right: 0; width: min(84vw, 360px); height: 100%; background: #fff; box-shadow: -18px 0 36px rgba(15, 23, 42, 0.25); transform: translateX(100%); transition: transform 220ms ease; display: flex; flex-direction: column; }
        .builder-menu-mobile-overlay.is-open .builder-menu-mobile-drawer { transform: translateX(0); }
        .builder-menu-mobile-head { display: flex; align-items: center; justify-content: flex-start; padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .builder-menu-mobile-close { width: 40px; height: 40px; border: 1px solid #cbd5e1; background: #fff; border-radius: 0; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #0f172a; }
        .builder-menu-mobile-panel { padding: 12px; overflow-y: auto; }
        .builder-menu-mobile-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
        .builder-menu-mobile-link { display: block; text-decoration: none; color: inherit; font: inherit; padding: 8px 0; }
        .builder-menu-mobile-cta { margin-top: 12px; display: inline-flex; text-decoration: none; }
        .builder-testimonial { display: grid; gap: 10px; }
        .builder-testimonial-quote { font-style: italic; line-height: 1.5; color: #334155; }
        .builder-testimonial-author { display: flex; align-items: center; gap: 10px; }
        .builder-testimonial-avatar { width: 42px; height: 42px; border-radius: 999px; object-fit: cover; background: #e2e8f0; flex-shrink: 0; }
        .builder-testimonial-name { font-weight: 800; color: #0f172a; }
        .builder-testimonial-role { font-size: 12px; color: #64748b; }
        .builder-review-form { display:grid; gap:12px; }
        .builder-review-title { font-size:18px; font-weight:900; color:#0f172a; }
        .builder-review-subtitle { font-size:13px; line-height:1.55; color:#64748b; }
        .builder-review-stars { display:flex; gap:6px; color:#f59e0b; font-size:20px; }
        .builder-review-rating { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
        .builder-review-star { appearance:none; border:0; background:transparent; padding:0; margin:0; cursor:pointer; font-size:28px; line-height:1; color:#d1d5db; transition:transform .12s ease,color .16s ease; }
        .builder-review-star:hover,
        .builder-review-star:focus-visible { color:#f59e0b; transform:scale(1.08); outline:none; }
        .builder-review-star.is-active { color:#f59e0b; }
        .builder-review-rating-note { font-size:12px; font-weight:700; color:#64748b; margin-left:4px; }
        .builder-review-stars.is-interactive { align-items:center; gap:8px; }
        .builder-review-stars.is-interactive label { display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
        .builder-review-stars.is-interactive input { position:absolute; opacity:0; pointer-events:none; }
        .builder-review-stars.is-interactive .builder-review-star-glyph { font-size:28px; line-height:1; color:#d1d5db; transition:transform .12s ease,color .16s ease; }
        .builder-review-stars.is-interactive label.is-active .builder-review-star-glyph { color:#f59e0b; }
        .builder-review-stars.is-interactive label:hover .builder-review-star-glyph { transform:scale(1.08); }
        .builder-review-input, .builder-review-textarea { width:100%; box-sizing:border-box; padding:10px 12px; border:1px solid #E2E8F0; border-radius:10px; background:#fff; }
        .builder-review-textarea { min-height:100px; resize:vertical; }
        .builder-review-check { display:grid; grid-template-columns:auto 1fr; align-items:start; column-gap:12px; row-gap:0; font-size:13px; line-height:1.6; color:#475569; font-weight:600; }
        .builder-review-check input[type="hidden"] { display:none; }
        .builder-review-check input[type="checkbox"] { width:18px; height:18px; margin:2px 0 0; accent-color:#1d4ed8; }
        .builder-review-check span { display:block; min-width:0; }
        .builder-review-note { font-size:12px; color:#64748b; }
        .builder-review-form { width:100%; min-width:0; }
        .builder-review-list { display:grid; gap:12px; width:100%; min-width:0; }
        .builder-review-list.grid { grid-template-columns:repeat(auto-fit,minmax(min(280px,100%),1fr)); }
        .builder-review-card { display:grid; gap:8px; width:100%; min-width:0; box-sizing:border-box; padding:14px; border:1px solid #E2E8F0; border-radius:14px; background:#fff; }
        .builder-review-card-head { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; }
        .builder-review-card-name { font-weight:800; color:#0f172a; }
        .builder-review-card-date { font-size:11px; color:#64748b; }
        .builder-review-card-stars { color:#f59e0b; font-size:14px; letter-spacing:.04em; }
        .builder-review-card-text { font-size:13px; line-height:1.6; color:#334155; white-space:pre-wrap; }
        .builder-review-toggle { display:inline-flex; align-items:center; justify-content:center; padding:9px 14px; border-radius:999px; border:1px solid #d7cdea; background:#ffffff; color:#240E35; font-size:12px; font-weight:800; cursor:pointer; width:max-content; }
        .builder-review-hidden { display:none !important; }
        .builder-faq { display: grid; gap: 10px; }
        .builder-faq-item { border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
        .builder-faq-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .builder-faq-question { font-weight: 800; color: #0f172a; }
        .builder-faq-answer { color: #475569; font-size: 13px; margin-top: 4px; white-space: pre-wrap; }
        .builder-pricing { display: grid; gap: 12px; }
        .builder-product-offer { display: grid; gap: 4px; }
        .builder-product-offer .builder-pricing-badge { padding: 2px 6px; font-size: 9px; }
        .builder-product-offer .builder-pricing-title { font-size: 13px; line-height: 1.25; }
        .builder-product-offer .builder-pricing-price { font-size: 20px; line-height: 1; }
        .builder-product-offer .builder-pricing-period { font-size: 10px; }
        .builder-product-offer .builder-pricing-subtitle { font-size: 10px; line-height: 1.3; }
        .builder-product-offer .builder-pricing-features { gap: 4px; }
        .builder-product-offer .builder-pricing-features li { font-size: 10px; gap: 4px; }
        .builder-product-offer .builder-product-actions { display: grid; gap: 6px; }
        .builder-product-offer .builder-product-utility { display: grid; grid-template-columns: minmax(0, 1fr) 32px; gap: 6px; }
        .builder-product-offer .builder-pricing-cta { width: 100%; padding: 7px 8px; font-size: 11px; }
        .builder-product-offer .builder-product-secondary { display:inline-flex; align-items:center; justify-content:center; width:100%; padding: 6px 8px; font-size:10px; border-radius:999px; border:1px solid #d7cdea; background:#ffffff; color:#240E35; font-weight:700; cursor:pointer; text-decoration:none; }
        .builder-product-offer .builder-product-cart { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:999px; border:1px solid #d7cdea; background:#ffffff; color:#240E35; font-size:12px; cursor:pointer; transition: transform .16s ease, background-color .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease; }
        .builder-product-offer .builder-product-cart.is-in-cart { background:#16a34a; color:#ffffff; border-color:#16a34a; box-shadow:0 8px 18px rgba(22,163,74,.22); }
        .builder-product-offer .builder-product-cart.is-bump { transform: scale(1.12); }
        @keyframes cartPop {
            0% { transform: scale(1); }
            45% { transform: scale(1.16); }
            100% { transform: scale(1); }
        }
        .builder-product-media { position: relative; border-radius: 14px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0; }
        .builder-product-media .builder-carousel-wrap { min-height: 88px; border-radius: 0; }
        .builder-product-media .builder-carousel-slide { background: #ffffff !important; }
        .builder-product-media .builder-carousel-dots { padding-bottom: 6px; }
        .builder-product-media .builder-carousel-arrow { width: 24px !important; height: 24px !important; min-width: 24px; min-height: 24px; }
        .builder-product-media .builder-carousel-arrow i { font-size: 10px !important; }
        .builder-product-media .builder-carousel-arrow.is-left { left: 8px !important; }
        .builder-product-media .builder-carousel-arrow.is-right { right: 8px !important; }
        .builder-product-media .builder-carousel-dot { width: 6px; height: 6px; }
        .builder-product-media .builder-carousel-dots { gap: 5px; bottom: 5px; }
        @media (hover: hover) and (pointer: fine) {
            .builder-product-media .builder-carousel-arrow {
                opacity: 0;
                pointer-events: none;
                transform: translateY(-50%) scale(.92);
            }
            .builder-product-media:hover .builder-carousel-arrow,
            .builder-product-media:focus-within .builder-carousel-arrow {
                opacity: 1;
                pointer-events: auto;
                transform: translateY(-50%) scale(1);
            }
        }
        .builder-product-media__placeholder { width: 100%; min-height: 88px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; color: #64748b; text-align: center; padding: 8px; font-size: 10px; background: linear-gradient(180deg, #ffffff, #f8fafc); }
        .builder-product-media__placeholder i { font-size: 16px; color: #94a3b8; }
        .product-quick-view-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.58); z-index: 2000; display: none; align-items: center; justify-content: center; padding: 20px; }
        .product-quick-view-backdrop.is-open { display: flex; }
        .product-quick-view-modal { width: min(920px, 100%); max-height: min(88vh, 920px); overflow: auto; background: #ffffff; border-radius: 24px; box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28); padding: 22px; position: relative; }
        .product-quick-view-close { position: sticky; top: 0; margin-left: auto; width: 38px; height: 38px; border-radius: 999px; border: 1px solid #e2e8f0; background: #ffffff; color: #0f172a; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; z-index: 2; }
        .product-quick-view-layout { display: grid; grid-template-columns: minmax(260px, 360px) minmax(0, 1fr); gap: 24px; align-items: start; }
        .product-quick-view-media { border: 1px solid #e2e8f0; border-radius: 20px; overflow: hidden; background: #f8fafc; }
        .product-quick-view-media .builder-carousel-wrap { min-height: 280px; border-radius: 0; }
        .product-quick-view-copy { display: grid; gap: 14px; color: #0f172a; }
        .product-quick-view-copy h3 { margin: 0; font-size: 28px; line-height: 1.15; }
        .product-quick-view-price { display:flex; align-items:flex-end; gap:10px; flex-wrap:wrap; }
        .product-quick-view-price .builder-pricing-price { font-size: 34px; }
        .product-quick-view-price .builder-pricing-period { font-size: 14px; margin-left: 0; }
        .product-quick-view-description { white-space: pre-wrap; color: #475569; line-height: 1.6; font-size: 14px; }
        .product-quick-view-features { list-style:none; margin:0; padding:0; display:grid; gap:8px; }
        .product-quick-view-features li { display:flex; gap:8px; align-items:flex-start; color:#334155; font-size:14px; }
        .product-quick-view-features li i { color:#16a34a; margin-top: 2px; }
        .portal-cart-fab { position: fixed; right: 20px; bottom: 20px; z-index: 1900; width: 52px; height: 52px; border-radius: 999px; border: none; background: #240E35; color: #ffffff; display: none; align-items: center; justify-content: center; box-shadow: 0 16px 36px rgba(15, 23, 42, 0.26); cursor: pointer; }
        .portal-cart-fab.is-visible { display: inline-flex; }
        .portal-cart-count { position: absolute; top: -4px; right: -4px; min-width: 20px; height: 20px; border-radius: 999px; background: #ef4444; color: #ffffff; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; padding: 0 5px; }
        .portal-cart-fly { position: fixed; z-index: 1995; width: 22px; height: 22px; border-radius: 999px; background: radial-gradient(circle at 30% 30%, #a78bfa 0%, #6d28d9 45%, #240E35 100%); box-shadow: 0 10px 22px rgba(36, 14, 53, 0.28); pointer-events: none; will-change: transform, opacity; }
        .portal-cart-backdrop { position: fixed; inset: 0; z-index: 1950; background: rgba(15, 23, 42, 0.42); display: none; }
        .portal-cart-backdrop.is-open { display: block; }
        .portal-cart-drawer { position: fixed; top: 0; right: 0; width: min(380px, 100%); height: 100vh; height: 100dvh; z-index: 1960; background: #ffffff; box-shadow: -18px 0 40px rgba(15, 23, 42, 0.18); transform: translateX(100%); transition: transform .24s ease; display: grid; grid-template-rows: auto 1fr auto; }
        .portal-cart-drawer.is-open { transform: translateX(0); }
        .portal-cart-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 18px; border-bottom:1px solid #e2e8f0; }
        .portal-cart-head h3 { margin:0; font-size:18px; }
        .portal-cart-close { width:36px; height:36px; border-radius:999px; border:1px solid #e2e8f0; background:#fff; cursor:pointer; }
        .portal-cart-items { overflow:auto; padding:14px 16px; display:flex; flex-direction:column; gap:12px; align-items:stretch; }
        .portal-cart-item { display:grid; grid-template-columns:64px minmax(0,1fr) auto; gap:10px; padding:10px; border:1px solid #e2e8f0; border-radius:16px; background:#fff; align-items:start; }
        .portal-cart-thumb { width:64px; height:64px; border-radius:12px; overflow:hidden; background:#f8fafc; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; }
        .portal-cart-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
        .portal-cart-thumb i { color:#94a3b8; font-size:18px; }
        .portal-cart-meta { min-width:0; display:grid; grid-template-columns:minmax(0,1fr); gap:4px; align-content:start; }
        .portal-cart-title { font-size:13px; font-weight:800; color:#0f172a; line-height:1.3; }
        .portal-cart-price { font-size:13px; color:#16a34a; font-weight:800; }
        .portal-cart-sub { font-size:11px; color:#64748b; }
        .portal-cart-qty { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#475569; margin-top:4px; }
        .portal-cart-qty-btn { width:26px; height:26px; border-radius:999px; border:1px solid #d7cdea; background:#fff; color:#240E35; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
        .portal-cart-qty-num { min-width:18px; text-align:center; font-weight:800; color:#0f172a; }
        .portal-cart-remove { align-self:start; width:30px; height:30px; border:none; background:#fff1f2; color:#e11d48; border-radius:999px; cursor:pointer; }
        .portal-cart-empty { padding:26px 12px; text-align:center; color:#64748b; font-size:13px; }
        .portal-cart-foot { border-top:1px solid #e2e8f0; padding:16px 18px; display:grid; gap:10px; }
        .portal-cart-total { display:flex; align-items:center; justify-content:space-between; font-weight:800; color:#0f172a; }
        .portal-cart-actions { display:grid; gap:8px; }
        .portal-cart-btn { display:inline-flex; align-items:center; justify-content:center; width:100%; padding:10px 12px; border-radius:999px; border:none; text-decoration:none; font-weight:800; cursor:pointer; }
        .portal-cart-btn.primary { background:#240E35; color:#ffffff; }
        .portal-cart-btn.secondary { background:#ffffff; color:#240E35; border:1px solid #d7cdea; }
        .builder-checkout-summary { position: relative; }
        .checkout-cart-lines { display:grid; gap: var(--checkout-physical-lines-gap, 8px); }
        .checkout-cart-line { display:grid; grid-template-columns: var(--checkout-physical-line-cols, 40px 1fr auto); gap: var(--checkout-physical-line-gap, 10px); align-items:center; padding: var(--checkout-physical-line-pad, 8px) 0; border-bottom:1px solid #eef2f7; }
        .checkout-cart-line:last-child { border-bottom:0; padding-bottom:0; }
        .checkout-cart-line-thumb { width: var(--checkout-physical-line-thumb-size, 40px); height: var(--checkout-physical-line-thumb-size, 40px); border-radius: var(--checkout-physical-line-thumb-radius, 12px); overflow:hidden; background:#f8fafc; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; }
        .checkout-cart-line-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
        .checkout-cart-line-thumb i { font-size: var(--checkout-physical-line-thumb-icon, 14px); color:#94a3b8; }
        .checkout-cart-line-meta { min-width:0; display:grid; gap:2px; }
        .checkout-cart-line-title { font-size: var(--checkout-physical-line-title-size, 12px); font-weight:800; color:#0f172a; line-height:1.3; }
        .checkout-cart-line-sub { font-size: var(--checkout-physical-line-sub-size, 11px); color:#64748b; }
        .checkout-cart-line-total { font-size: var(--checkout-physical-line-total-size, 12px); font-weight:800; color:#0f172a; }
        .builder-checkout-summary--physical { gap: var(--checkout-physical-gap, 12px); padding: var(--checkout-physical-pad, 16px); }
        .builder-checkout-summary--physical .builder-pricing-badge { background:#eaf2ff; color:#1d4ed8; }
        .checkout-physical-head { display:grid; gap:4px; }
        .checkout-physical-label { font-size: var(--checkout-physical-label-size, 11px); font-weight:800; letter-spacing:0.08em; text-transform:uppercase; color:#64748b; }
        .checkout-physical-product { display:grid; grid-template-columns: var(--checkout-physical-product-cols, 64px minmax(0,1fr)); gap: var(--checkout-physical-product-gap, 12px); align-items:center; padding: var(--checkout-physical-product-pad, 12px); border:1px solid #e6eaf2; border-radius: var(--checkout-physical-product-radius, 16px); background:linear-gradient(180deg,#ffffff,#faf8fd); }
        .checkout-physical-thumb { width: var(--checkout-physical-thumb-size, 64px); height: var(--checkout-physical-thumb-size, 64px); border-radius: var(--checkout-physical-thumb-radius, 16px); overflow:hidden; background:#f8fafc; border:1px solid #e2e8f0; display:flex; align-items:center; justify-content:center; }
        .checkout-physical-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
        .checkout-physical-thumb i { font-size: var(--checkout-physical-thumb-icon, 22px); color:#94a3b8; }
        .checkout-physical-meta { min-width:0; display:grid; gap:4px; }
        .checkout-physical-meta .builder-pricing-title { font-size: var(--checkout-physical-title-size, 16px); line-height:1.2; }
        .checkout-physical-price { display:flex; align-items:baseline; gap:8px; flex-wrap:wrap; }
        .checkout-physical-price .builder-pricing-price { font-size: var(--checkout-physical-price-size, 24px); }
        .checkout-physical-price .builder-pricing-period { margin-left:0; }
        .checkout-physical-rows { display:grid; gap: var(--checkout-physical-rows-gap, 8px); padding: var(--checkout-physical-rows-pad, 10px) 0; border-top:1px solid #eef2f7; border-bottom:1px solid #eef2f7; }
        .checkout-physical-row { display:flex; align-items:center; justify-content:space-between; gap:10px; font-size: var(--checkout-physical-row-size, 12px); color:#475569; }
        .checkout-physical-row strong { color:#0f172a; font-size: var(--checkout-physical-row-strong-size, 13px); }
        .checkout-physical-row--total strong:last-child { font-size: var(--checkout-physical-row-total-size, 18px); color:#16a34a; }
        .builder-checkout-summary--physical .builder-pricing-features { gap: var(--checkout-physical-features-gap, 5px); }
        .builder-checkout-summary--physical .builder-pricing-features li { font-size: var(--checkout-physical-feature-size, 11px); }
        .builder-checkout-summary--physical .builder-pricing-cta { width:100%; padding: var(--checkout-physical-cta-pad-y, 10px) var(--checkout-physical-cta-pad-x, 14px); border-radius: var(--checkout-physical-cta-radius, 12px); }
        .checkout-shipping-modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.54); z-index: 1950; display: none; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 18px; }
        .checkout-shipping-modal-backdrop.is-open { display: flex; }
        .checkout-shipping-modal { width: min(500px, 100%); max-height: calc(100vh - 36px); overflow: auto; margin: auto 0; background: #ffffff; border-radius: 22px; box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28); padding: 18px; display: grid; gap: 12px; }
        .checkout-shipping-modal-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .checkout-shipping-modal-title { margin:0; font-size:24px; line-height:1.1; color:#240E35; }
        .checkout-shipping-modal-copy { margin:0; color:#64748b; font-size:13px; line-height:1.5; }
        .checkout-shipping-modal-close { width:36px; height:36px; border-radius:999px; border:1px solid #e2e8f0; background:#ffffff; color:#0f172a; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; flex:0 0 auto; }
        .checkout-shipping-modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 12px; }
        .checkout-shipping-modal-field { display:grid; gap:4px; min-width:0; }
        .checkout-shipping-modal-field.is-full { grid-column: 1 / -1; }
        .checkout-shipping-modal-field label { margin:0; font-size:11px; font-weight:700; color:#334155; }
        .checkout-shipping-modal-field input { margin:0; width:100%; min-width:0; padding:10px 12px; border:1px solid #E6E1EF; border-radius:12px; box-sizing:border-box; }
        .checkout-shipping-modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:4px; }
        .checkout-shipping-modal-cancel { display:inline-flex; align-items:center; justify-content:center; min-width:120px; padding:11px 16px; border-radius:999px; border:1px solid #d7cdea; background:#ffffff; color:#240E35; font-weight:700; cursor:pointer; }
        .checkout-shipping-modal-submit { min-width:160px; }
        /* Must be above portal loading overlay (z-index:2100) */
        .coupon-prompt-modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.54); z-index: 2210; display: none; align-items: center; justify-content: center; padding: 18px; pointer-events: auto; }
        .coupon-prompt-modal-backdrop.is-open { display: flex; }
        .coupon-prompt-modal { width:min(460px,100%); background:#ffffff; border-radius:22px; box-shadow:0 28px 70px rgba(15,23,42,.28); padding:20px; display:grid; gap:14px; position:relative; z-index: 2211; pointer-events: auto; }
        .coupon-prompt-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }
        .coupon-prompt-title { margin:0; font-size:24px; line-height:1.1; color:#240E35; }
        .coupon-prompt-copy { margin:4px 0 0; color:#64748b; font-size:13px; line-height:1.5; }
        .coupon-prompt-close { width:36px; height:36px; border-radius:999px; border:1px solid #e2e8f0; background:#ffffff; color:#0f172a; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; flex:0 0 auto; }
        .coupon-prompt-field { display:grid; gap:6px; }
        .coupon-prompt-field label { font-size:11px; font-weight:700; color:#334155; text-transform:uppercase; letter-spacing:.08em; }
        .coupon-prompt-field input { width:100%; padding:12px 14px; border:1px solid #E6E1EF; border-radius:12px; box-sizing:border-box; text-transform:uppercase; font-weight:700; letter-spacing:.08em; }
        .coupon-prompt-preview { padding:12px 14px; border-radius:14px; background:#f8fafc; border:1px solid #e2e8f0; display:grid; gap:6px; }
        .coupon-prompt-preview-row { display:flex; justify-content:space-between; gap:10px; color:#475569; font-size:13px; }
        .coupon-prompt-preview-row strong { color:#0f172a; }
        .coupon-prompt-preview-row--total strong:last-child { color:#16a34a; font-size:16px; }
        .coupon-prompt-available { padding:12px 14px; border-radius:14px; background:#fbf9fd; border:1px solid #ece2f5; display:grid; gap:10px; }
        .coupon-prompt-available-title { font-size:11px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:.08em; }
        .coupon-prompt-available-list { display:grid; gap:8px; max-height:220px; overflow:auto; padding-right:4px; }
        .coupon-prompt-available-list::-webkit-scrollbar{width:8px}
        .coupon-prompt-available-list::-webkit-scrollbar-track{background:#f1f5f9;border-radius:8px}
        .coupon-prompt-available-list::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:8px}
        .coupon-prompt-available-item { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 12px; border-radius:12px; background:#ffffff; border:1px solid #E6E1EF; }
        .coupon-prompt-available-code { font-weight:900; letter-spacing:.08em; color:#240E35; text-transform:uppercase; }
        .coupon-prompt-available-meta { font-size:12px; color:#64748b; margin-top:2px; }
        .coupon-prompt-available-actions { display:flex; gap:8px; flex:0 0 auto; }
        .coupon-prompt-available-btn { padding:8px 10px; border-radius:999px; border:1px solid #d7cdea; background:#ffffff; color:#240E35; font-weight:800; cursor:pointer; font-size:12px; }
        .coupon-prompt-available-btn.primary { background:#240E35; color:#ffffff; border-color:#240E35; }
        .coupon-prompt-message { min-height:18px; font-size:12px; color:#64748b; }
        .coupon-prompt-message.is-success { color:#166534; }
        .coupon-prompt-message.is-error { color:#b91c1c; }
        .coupon-prompt-actions { display:flex; justify-content:flex-end; gap:10px; }
        .coupon-prompt-skip { display:inline-flex; align-items:center; justify-content:center; min-width:120px; padding:11px 16px; border-radius:999px; border:1px solid #d7cdea; background:#ffffff; color:#240E35; font-weight:700; cursor:pointer; }
        .funnel-coupon-pop { position:fixed; right:18px; bottom:18px; z-index:1890; width:min(340px,calc(100vw - 28px)); transition:transform .24s ease, opacity .24s ease; }
        .funnel-coupon-pop.is-hidden { opacity:0; pointer-events:none; transform:translateY(14px); }
        .funnel-coupon-card { background:linear-gradient(160deg,#240E35 0%,#3d195c 58%,#5b2a86 100%); color:#fff; border-radius:24px; box-shadow:0 22px 60px rgba(36,14,53,.34); overflow:hidden; }
        .funnel-coupon-card-body { padding:18px; display:grid; gap:12px; }
        .funnel-coupon-kicker { font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; opacity:.76; }
        .funnel-coupon-title { margin:0; font-size:24px; line-height:1.05; }
        .funnel-coupon-text { margin:0; font-size:13px; line-height:1.55; color:rgba(255,255,255,.82); }
        .funnel-coupon-code-row { display:flex; gap:10px; align-items:center; }
        .funnel-coupon-code { flex:1 1 auto; padding:12px 14px; border-radius:14px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); font-size:20px; font-weight:900; letter-spacing:.1em; text-align:center; }
        .funnel-coupon-copy { display:inline-flex; align-items:center; justify-content:center; min-width:124px; padding:12px 14px; border:none; border-radius:14px; background:#ffffff; color:#240E35; font-weight:900; cursor:pointer; }
        .funnel-coupon-meta { display:flex; justify-content:space-between; align-items:center; gap:10px; font-size:12px; color:rgba(255,255,255,.8); }
        .funnel-coupon-progress { height:5px; background:rgba(255,255,255,.14); }
        .funnel-coupon-progress > span { display:block; width:100%; height:100%; background:#facc15; transform-origin:left center; }
        .funnel-coupon-minitab { margin-top:10px; margin-left:auto; display:none; align-items:center; gap:8px; padding:10px 14px; border:none; border-radius:999px; background:#240E35; color:#fff; box-shadow:0 16px 34px rgba(36,14,53,.26); cursor:pointer; font-weight:800; }
        .funnel-coupon-minitab.is-visible { display:inline-flex; }
        .portal-loading-overlay { position:fixed; inset:0; z-index:2100; display:none; align-items:center; justify-content:center; background:rgba(248,250,252,.86); backdrop-filter:blur(4px); }
        .portal-loading-overlay.is-active { display:flex; }
        .portal-loading-card { width:min(280px,calc(100vw - 32px)); padding:24px 22px; border-radius:24px; background:rgba(255,255,255,.96); border:1px solid rgba(226,232,240,.95); box-shadow:0 28px 70px rgba(15,23,42,.16); display:grid; justify-items:center; gap:12px; text-align:center; }
        .portal-loading-spinner { width:44px; height:44px; border-radius:999px; border:4px solid #dbe4f0; border-top-color:#240E35; animation:portal-loading-spin .82s linear infinite; }
        .portal-loading-title { font-size:18px; font-weight:900; color:#240E35; line-height:1.1; }
        .portal-loading-copy { font-size:13px; line-height:1.55; color:#64748b; max-width:24ch; }
        @keyframes portal-loading-spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
        @media (max-width: 720px) {
            .product-quick-view-modal { padding: 16px; border-radius: 18px; }
            .product-quick-view-layout { grid-template-columns: 1fr; gap: 16px; }
            .product-quick-view-media .builder-carousel-wrap { min-height: 220px; }
            .product-quick-view-copy h3 { font-size: 22px; }
            .product-quick-view-price .builder-pricing-price { font-size: 28px; }
            .portal-cart-fab { right: 14px; bottom: 14px; }
            .checkout-physical-product { grid-template-columns:60px minmax(0,1fr); gap:10px; padding:10px; }
            .checkout-physical-thumb { width:60px; height:60px; border-radius:16px; }
            .checkout-shipping-modal { padding: 18px; border-radius: 20px; }
            .checkout-shipping-modal-title { font-size:24px; }
            .checkout-shipping-modal-grid { grid-template-columns:1fr; }
            .checkout-shipping-modal-actions { flex-direction:column-reverse; }
            .checkout-shipping-modal-cancel,
            .checkout-shipping-modal-submit { width:100%; }
            .coupon-prompt-actions { flex-direction:column-reverse; }
            .coupon-prompt-skip,
            .coupon-prompt-actions .builder-pricing-cta { width:100%; }
            .funnel-coupon-pop { right:14px; left:14px; width:auto; bottom:14px; }
            .funnel-coupon-code-row { flex-direction:column; }
            .funnel-coupon-copy { width:100%; }
        }
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
        .preview-toolbar-left{
            display:flex;
            align-items:center;
            gap:12px;
            min-width: 0;
            pointer-events:auto;
        }
        .preview-toolbar-right{
            margin-left:auto;
            pointer-events:auto;
        }
        .preview-back-btn{
            width:44px;
            height:44px;
            border-radius:12px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            text-decoration:none;
            background:#240E35;
            border:1px solid #240E35;
            color:#fff;
            box-shadow:none;
        }
        .preview-back-btn i{ font-size:16px; line-height:1; }
        .preview-mode-chip{
            height:44px;
            min-width:44px;
            padding:0 12px;
            border-radius:12px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:0;
            color:#fff;
            background:#240E35;
            border:1px solid #240E35;
            overflow:hidden;
            white-space:nowrap;
            box-shadow:none;
            transition:min-width 180ms ease,padding 180ms ease,gap 180ms ease;
        }
        .preview-mode-chip .preview-mode-label{
            max-width:0;
            opacity:0;
            overflow:hidden;
            margin-left:0;
            transform:translateX(-4px);
            transition:max-width 180ms ease,opacity 180ms ease,margin-left 180ms ease,transform 180ms ease;
            font-size:12px;
            font-weight:800;
        }
        .preview-mode-chip:hover,
        .preview-mode-chip:focus-within{
            min-width:150px;
            padding:0 14px;
            gap:8px;
        }
        .preview-mode-chip:hover .preview-mode-label,
        .preview-mode-chip:focus-within .preview-mode-label{
            max-width:120px;
            opacity:1;
            margin-left:2px;
            transform:translateX(0);
        }
        .preview-device-switcher{
            display:flex;
            align-items:center;
            gap:6px;
            justify-content:flex-end;
            position:fixed;
            right:16px;
            bottom:16px;
            z-index:35;
            padding:6px;
            border-radius:999px;
            background:rgba(255,255,255,0.96);
            border:1px solid #e2e8f0;
            box-shadow:0 12px 26px rgba(15,23,42,0.16);
        }
        .preview-device-btn{
            appearance:none;
            border:1px solid #e2e8f0;
            background:#ffffff;
            color:#0f172a;
            border-radius:999px;
            width:36px;
            height:36px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0;
            font-size:13px;
            font-weight:800;
            cursor:pointer;
            box-shadow:none;
            user-select:none;
            line-height:1;
        }
        .preview-device-btn.is-active{
            background:#240E35;
            border-color:#240E35;
            color:#fff;
        }
        .preview-iframe-shell{
            width:100%;
            display:flex;
            justify-content:center;
            align-items:flex-start;
            margin:0;
            padding:0 12px 24px;
            background:#f8fafc;
        }
        .preview-iframe-shell iframe{
            height:900px;
            width:100%;
            border:1px solid #e2e8f0;
            border-radius:12px;
            background:#ffffff;
        }
        /* Preview device buttons should emulate viewport width only.
           Structural layout must stay on the same render path as publish mode. */
        /* ═══════════════════════════════════════════════════════════
           Device-aware layout overrides for Preview Mode.
           We key off `body[data-preview-device="..."]` so it works
           regardless of the actual browser viewport width.
           ═══════════════════════════════════════════════════════════ */

        /* ── TABLET (768px viewport) ── */
        body[data-preview-device="tablet"] .builder-row-inner{
            flex-wrap: wrap !important;
        }
        body[data-preview-device="tablet"] .builder-col{
            flex: 1 1 45% !important;
            min-width: 200px !important;
            min-height: 0 !important;
            height: auto !important;
        }
        body[data-preview-device="tablet"] .builder-el{
            position: static !important;
            left: auto !important;
            top: auto !important;
            max-width: 100% !important;
        }
        body[data-preview-device="tablet"] .builder-col-inner--section-elements > .builder-el,
        body[data-preview-device="mobile"] .builder-col-inner--section-elements > .builder-el{
            width: 100% !important;
            text-align: center !important;
        }
        body[data-preview-device="tablet"] .builder-carousel-content-row{
            flex-direction: column !important;
        }
        body[data-preview-device="tablet"] .builder-carousel-content-col{
            min-width: 0 !important;
            width: 100% !important;
        }
        body[data-preview-device="tablet"] .step-content--full{
            padding: 10px 1rem 24px !important;
        }
        body[data-preview-device="tablet"] .builder-section{
            padding: 0 !important;
            min-height: 0 !important;
            height: auto !important;
        }
        body[data-preview-device="tablet"] .builder-section-inner{
            min-height: 0 !important;
            height: auto !important;
        }
        body[data-preview-device="tablet"] .builder-heading{
            font-size: clamp(18px, 4vw, 28px) !important;
        }
        body[data-preview-device="tablet"] .builder-pricing{
            max-width: 100% !important;
        }
        body[data-preview-device="tablet"] .builder-pricing-grid{
            grid-template-columns: 1fr !important;
        }
        body[data-preview-device="tablet"] .builder-el[data-element-type="image"]{
            max-width: 100% !important;
            height: auto !important;
        }
        body[data-preview-device="tablet"] .builder-el[data-element-type="image"] img{
            max-width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
        }
        body[data-preview-device="tablet"] .builder-video-wrap{
            max-width: 100% !important;
        }
        body[data-preview-device="tablet"] .builder-video-wrap iframe,
        body[data-preview-device="tablet"] .builder-video-wrap video{
            max-width: 100% !important;
        }
        body[data-preview-device="tablet"] .builder-section--freeform{
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            overflow-x: auto !important;
        }
        body[data-preview-device="tablet"] .builder-menu-list{
            flex-wrap: wrap !important;
            gap: 6px !important;
        }

        /* ── MOBILE (375px viewport) ── */
        body[data-preview-device="mobile"] .builder-row-inner{
            flex-direction: column !important;
        }
        body[data-preview-device="mobile"] .builder-col{
            flex: 0 0 auto !important;
            width: 100% !important;
            min-height: 0 !important;
            height: auto !important;
            padding: 6px !important;
        }
        body[data-preview-device="mobile"] .builder-el{
            position: static !important;
            left: auto !important;
            top: auto !important;
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
        }
        body[data-preview-device="mobile"] .builder-col:empty,
        body[data-preview-device="mobile"] .builder-col:not(:has(.builder-el)){
            padding: 0 !important;
            display: none !important;
        }
        body[data-preview-device="mobile"] .builder-carousel-content-row{
            flex-direction: column !important;
        }
        body[data-preview-device="mobile"] .builder-carousel-content-col{
            min-width: 0 !important;
            width: 100% !important;
        }
        body[data-preview-device="mobile"] .step-content--full{
            padding: 0 !important;
        }
        body[data-preview-device="mobile"] .builder-section{
            padding: 0 !important;
            margin-bottom: 0 !important;
            border-radius: 0 !important;
            min-height: 0 !important;
            height: auto !important;
        }
        body[data-preview-device="mobile"] .builder-section-inner{
            min-height: 0 !important;
            height: auto !important;
        }
        body[data-preview-device="mobile"] .builder-row{
            padding: 0 !important;
            gap: 0 !important;
        }
        body[data-preview-device="mobile"] .builder-heading{
            font-size: clamp(16px, 5vw, 22px) !important;
            line-height: 1.3 !important;
        }
        body[data-preview-device="mobile"] .builder-text{
            font-size: clamp(12px, 3.5vw, 15px) !important;
            line-height: 1.5 !important;
        }
        body[data-preview-device="mobile"] .btn{
            padding: 8px 14px !important;
            font-size: clamp(12px, 3.5vw, 15px) !important;
        }
        body[data-preview-device="mobile"] .builder-pricing{
            max-width: 100% !important;
        }
        body[data-preview-device="mobile"] .builder-pricing-grid{
            grid-template-columns: 1fr !important;
        }
        body[data-preview-device="mobile"] .builder-pricing-card{
            padding: 12px !important;
        }
        body[data-preview-device="mobile"] .builder-pricing-price{
            font-size: clamp(22px, 6vw, 32px) !important;
        }
        body[data-preview-device="mobile"] .builder-el[data-element-type="image"]{
            max-width: 100% !important;
            height: auto !important;
        }
        body[data-preview-device="mobile"] .builder-el[data-element-type="image"] img{
            max-width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
        }
        body[data-preview-device="mobile"] .builder-video-wrap{
            max-width: 100% !important;
        }
        body[data-preview-device="mobile"] .builder-video-wrap iframe,
        body[data-preview-device="mobile"] .builder-video-wrap video{
            max-width: 100% !important;
            height: auto !important;
        }
        body[data-preview-device="mobile"] .builder-section--freeform{
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            overflow-x: auto !important;
        }
        body[data-preview-device="mobile"] .builder-faq-question{
            font-size: 14px !important;
        }
        body[data-preview-device="mobile"] .builder-faq-answer{
            font-size: 12px !important;
        }
        body[data-preview-device="mobile"] .builder-testimonial-quote{
            font-size: 13px !important;
        }
        body[data-preview-device="mobile"] .builder-form-input{
            padding: 8px !important;
            font-size: 14px !important;
        }
        body[data-preview-device="mobile"] .builder-menu-center,
        body[data-preview-device="mobile"] .builder-menu-right{
            display:none !important;
        }
        body[data-preview-device="mobile"] .builder-menu-shell{
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            min-height: 0 !important;
            padding: 8px 0 !important;
            gap: 12px !important;
            border-bottom: 1px solid #e2e8f0 !important;
            width: 100% !important;
        }
        body[data-preview-device="mobile"] .builder-menu{
            width: 100% !important;
            max-width: none !important;
        }
        body[data-preview-device="mobile"] .builder-el[data-element-type="menu"]{
            width: 100vw !important;
            max-width: 100vw !important;
            margin-left: calc(50% - 50vw) !important;
            margin-right: calc(50% - 50vw) !important;
            padding: 0 12px !important;
            box-sizing: border-box !important;
        }
        body[data-preview-device="mobile"] .builder-menu-toggle{
            display: inline-flex !important;
            width: 36px !important;
            height: 36px !important;
            flex-shrink: 0 !important;
        }
        body[data-preview-device="mobile"] .builder-menu-left{
            flex: 1 !important;
            min-width: 0 !important;
        }
        body[data-preview-device="mobile"] .builder-menu-left .builder-menu-logo{
            max-height: 36px !important;
            max-width: 140px !important;
        }
        body[data-preview-device="mobile"] .builder-menu-left .builder-menu-logo-placeholder{
            min-height: 36px !important;
            display: inline-flex !important;
            align-items: center !important;
            font-size: 11px !important;
        }
        body[data-preview-device="mobile"] .builder-countdown{
            gap: 6px !important;
        }
        body[data-preview-device="mobile"] .builder-countdown-unit{
            min-width: 50px !important;
            padding: 8px 4px !important;
        }
        body[data-preview-device="mobile"] .builder-countdown-number{
            font-size: clamp(18px, 5vw, 26px) !important;
        }
        body[data-preview-device="mobile"] .builder-el + .builder-el{
            margin-top: 4px !important;
        }
        body[data-preview-device="mobile"] .builder-review-card{
            padding: 10px !important;
        }
        body[data-preview-device="mobile"] .builder-review-card-text{
            font-size: 12px !important;
        }
        body[data-preview-device="mobile"] .builder-product-offer{
            gap: 3px !important;
        }

        /* ── REAL VIEWPORT responsive (published mode) ── */
        @media (max-width: 768px) {
            body.is-published .builder-row-inner{
                flex-wrap: wrap;
            }
            body.is-published .builder-col{
                flex: 1 1 45%;
                min-width: 200px;
                min-height: 0 !important;
                height: auto !important;
            }
            body.is-published .builder-el{
                position: static !important;
                left: auto !important;
                top: auto !important;
                max-width: 100% !important;
            }
            body.is-published .builder-col-inner--section-elements > .builder-el{
                width: 100% !important;
                text-align: center !important;
            }
            body.is-published .builder-carousel-content-row{
                flex-direction: column;
            }
            body.is-published .builder-carousel-content-col{
                min-width: 0;
                width: 100%;
            }
            body.is-published .step-content--full{
                padding: 0;
            }
            body.is-published .builder-section{
                padding: 0;
                min-height: 0 !important;
                height: auto !important;
            }
            body.is-published .builder-section-inner{
                min-height: 0 !important;
                height: auto !important;
            }
            body.is-published .builder-heading{
                font-size: clamp(18px, 5vw, 28px);
            }
            body.is-published .builder-pricing-grid{
                grid-template-columns: 1fr;
            }
            body.is-published .builder-section--freeform{
                width: 100% !important;
                margin-left: 0;
                margin-right: 0;
                overflow-x: auto;
            }
            body.is-published .builder-menu-list{
                flex-wrap: wrap;
                gap: 6px;
            }
            body.is-published .builder-el[data-element-type="image"]{
                max-width: 100%;
                height: auto;
            }
            body.is-published .builder-el[data-element-type="image"] img{
                max-width: 100%;
                height: auto;
                object-fit: contain;
            }
        }
        @media (max-width: 480px) {
            body.is-published .builder-row-inner{
                flex-direction: column;
            }
            body.is-published .builder-col{
                flex: 0 0 auto;
                width: 100%;
                min-height: 0 !important;
                height: auto !important;
                padding: 6px;
            }
            body.is-published .builder-el{
                position: static !important;
                left: auto !important;
                top: auto !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
            }
            body.is-published .builder-col-inner--section-elements > .builder-el{
                text-align: center !important;
            }
            body.is-published .builder-col:empty,
            body.is-published .builder-col:not(:has(.builder-el)){
                padding: 0;
                display: none;
            }
            body.is-published .step-content--full{
                padding: 0;
            }
            body.is-published .builder-section{
                padding: 0;
                margin-bottom: 0;
                min-height: 0 !important;
                height: auto !important;
            }
            body.is-published .builder-section-inner{
                min-height: 0 !important;
                height: auto !important;
            }
            body.is-published .builder-row{
                padding: 0;
                gap: 0;
            }
            body.is-published .builder-heading{
                font-size: clamp(16px, 5vw, 22px);
                line-height: 1.3;
            }
            body.is-published .builder-text{
                font-size: clamp(12px, 3.5vw, 15px);
                line-height: 1.5;
            }
            body.is-published .builder-el[data-element-type="image"]{
                max-width: 100% !important;
                height: auto !important;
            }
            body.is-published .builder-el[data-element-type="image"] img{
                max-width: 100% !important;
                height: auto !important;
                object-fit: contain !important;
            }
            body.is-published .btn{
                padding: 8px 14px;
                font-size: clamp(12px, 3.5vw, 15px);
            }
            body.is-published .builder-pricing-card{
                padding: 12px;
            }
            body.is-published .builder-pricing-price{
                font-size: clamp(22px, 6vw, 32px);
            }
            body.is-published .builder-faq-question{
                font-size: 14px;
            }
            body.is-published .builder-faq-answer{
                font-size: 12px;
            }
            body.is-published .builder-testimonial-quote{
                font-size: 13px;
            }
            body.is-published .builder-form-input{
                padding: 8px;
                font-size: 14px;
            }
            body.is-published .builder-menu-center,
            body.is-published .builder-menu-right{
                display:none;
            }
            body.is-published .builder-menu-shell{
                display: flex;
                justify-content: space-between;
                align-items: center;
                min-height: 0;
                padding: 8px 0;
                gap: 12px;
                border-bottom: 1px solid #e2e8f0;
                width: 100%;
            }
            body.is-published .builder-menu{
                width: 100%;
                max-width: none;
            }
            body.is-published .builder-el[data-element-type="menu"]{
                width: 100vw;
                max-width: 100vw;
                margin-left: calc(50% - 50vw);
                margin-right: calc(50% - 50vw);
                padding: 0 12px;
                box-sizing: border-box;
            }
            body.is-published .builder-menu-toggle{
                display: inline-flex;
                width: 36px;
                height: 36px;
                flex-shrink: 0;
            }
            body.is-published .builder-menu-left{
                flex: 1;
                min-width: 0;
            }
            body.is-published .builder-menu-left .builder-menu-logo{
                max-height: 36px;
                max-width: 140px;
            }
            body.is-published .builder-menu-left .builder-menu-logo-placeholder{
                min-height: 36px;
                display: inline-flex;
                align-items: center;
                font-size: 11px;
            }
            body.is-published .builder-el + .builder-el{
                margin-top: 4px;
            }
        }
        .preview-toolbar {
            position: fixed;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            z-index: 30;
            left: 12px;
            right: 12px;
            top: 12px;
            margin: 0;
            padding: 0;
            background: transparent;
            border: 0;
            pointer-events: none;
        }
        .preview-device-switcher{ pointer-events:auto; }
    </style>
</head>
<body class="{{ ($isPreview ?? false) ? 'is-preview' : 'is-published' }}{{ ($portalHasFreeformCanvas ?? false) ? ' portal-has-freeform-canvas' : '' }}" data-funnel-slug="{{ $funnel->slug }}">
    <div class="portal-loading-overlay" id="portalLoadingOverlay" aria-hidden="true">
        <div class="portal-loading-card" role="status" aria-live="polite" aria-label="Loading next page">
            <div class="portal-loading-spinner" aria-hidden="true"></div>
            <div class="portal-loading-title">Loading</div>
            <div class="portal-loading-copy">Please wait while the next page is opening.</div>
        </div>
    </div>
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
        $freeformGroups = [];
        $freeformIndex = 0;
        $flushFreeformGroup = function () use (&$renderSections, &$freeformEls, &$freeformGroups, &$freeformIndex) {
            if (count($freeformEls) === 0) {
                return;
            }
            $group = $freeformEls;
            $freeformGroups[] = $group;
            $renderSections[] = [
                'id' => 'sec_freeform_canvas_' . $freeformIndex++,
                'style' => [],
                'settings' => ['contentWidth' => 'full'],
                'elements' => $group,
                'rows' => [],
                'isFreeformCanvas' => true,
            ];
            $freeformEls = [];
        };
        foreach ($rootItems as $ri => $rootItem) {
            if (!is_array($rootItem)) {
                continue;
            }
            $kind = strtolower((string) ($rootItem['kind'] ?? 'section'));
            if ($kind === 'section') {
                $flushFreeformGroup();
                $renderSections[] = $rootItem;
                continue;
            }
            if ($kind === 'row') {
                $flushFreeformGroup();
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
                $flushFreeformGroup();
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
        $flushFreeformGroup();
        $editorMeta = is_array($layout['__editor'] ?? null) ? $layout['__editor'] : [];
        $editorCanvasWidth = count($freeformGroups) > 0
            ? $resolveSavedCanvasWidth($editorMeta)
            : $resolveSavedStageWidth($editorMeta);
        $derivedCanvasWidth = 0;
        foreach ($freeformGroups as $freeformGroup) {
            foreach ($freeformGroup as $ffEl) {
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
        }
        if ($derivedCanvasWidth > $editorCanvasWidth) {
            $editorCanvasWidth = $derivedCanvasWidth;
        }
        $hasBuilderLayout = count($renderSections) > 0;
        $activeSteps = collect($allSteps ?? [])->values()->filter(fn ($s) => isset($s->id, $s->slug));
        $activeStepsBySlug = $activeSteps->keyBy(fn ($s) => strtolower(trim((string) $s->slug)));
        $isTemplateTest = (bool) ($isTemplateTest ?? false);
        $reviewPrefill = is_array($reviewPrefill ?? null) ? $reviewPrefill : ['name' => '', 'email' => ''];
        $reviewAlreadySubmitted = (bool) ($reviewAlreadySubmitted ?? false);
        $approvedReviews = collect($approvedReviews ?? []);
        $productInventory = is_array($productInventory ?? null) ? $productInventory : [];
        $normalizeStepType = function (?string $type): string {
            $type = strtolower(trim((string) $type));
            if (in_array($type, ['upsell', 'downsell'], true)) {
                return 'sales';
            }
            return $type !== '' ? $type : 'custom';
        };
        $currentStepType = $normalizeStepType($step->type ?? '');
        $effectiveFunnelPurpose = strtolower(trim((string) (($funnel->purpose ?? null) ?: ($funnel->template_type ?? 'service'))));
        if (! in_array($effectiveFunnelPurpose, ['service', 'single_page', 'digital_product', 'physical_product', 'hybrid'], true)) {
            $effectiveFunnelPurpose = 'service';
        }
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
        $isAdminTemplatePreview = request()->routeIs('admin.funnel-templates.preview');
        $stepRoute = function ($targetStep) use ($funnel, $isPreview, $isTemplateTest, $isAdminTemplatePreview) {
            if ($isPreview) {
                if ($isAdminTemplatePreview || $isTemplateTest) {
                    return route('admin.funnel-templates.preview', ['funnel_template' => $funnel, 'step' => $targetStep]);
                }
                return route('funnels.preview', ['funnel' => $funnel, 'step' => $targetStep]);
            }
            if ($isTemplateTest) {
                return route('admin.funnel-templates.test', ['funnel_template' => $funnel, 'step' => $targetStep]);
            }
            return route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $targetStep->slug]);
        };
        $checkoutActionUrl = $isTemplateTest
            ? route('admin.funnel-templates.test.checkout', ['funnel_template' => $funnel, 'step' => $step])
            : route('funnels.portal.checkout', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]);
        $offerActionUrl = $isTemplateTest
            ? route('admin.funnel-templates.test.offer', ['funnel_template' => $funnel, 'step' => $step])
            : route('funnels.portal.offer', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]);
        $optInActionUrl = $isTemplateTest
            ? route('admin.funnel-templates.test.optin', ['funnel_template' => $funnel, 'step' => $step])
            : route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]);
        $restartStepUrl = $stepRoute($activeSteps->first());
        $cartCheckoutStep = $findStepByTypes(['checkout']);
        $cartCheckoutUrl = $cartCheckoutStep ? $stepRoute($cartCheckoutStep) : null;
        $resolveButtonAction = function (array $settings) use ($step, $nextStep, $isPreview, $activeStepsBySlug, $stepRoute, $checkoutActionUrl, $offerActionUrl) {
            $link = trim((string) ($settings['link'] ?? '#'));
            $actionType = strtolower(trim((string) ($settings['actionType'] ?? '')));
            if ($actionType === '') {
                $actionType = ($link !== '' && $link !== '#') ? 'link' : 'next_step';
            }

            if ($actionType === 'next_step') {
                if (!$nextStep) {
                    return ['kind' => 'link', 'href' => '#'];
                }
                return ['kind' => 'link', 'href' => $stepRoute($nextStep)];
            }

            if ($actionType === 'step') {
                $targetSlug = strtolower(trim((string) ($settings['actionStepSlug'] ?? '')));
                $target = $targetSlug !== '' ? $activeStepsBySlug->get($targetSlug) : null;
                if (!$target) {
                    return ['kind' => 'link', 'href' => '#'];
                }
                return ['kind' => 'link', 'href' => $stepRoute($target)];
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
                    'action' => $checkoutActionUrl,
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
                    'action' => $offerActionUrl,
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
        @if($isPreview && !$previewIframeMode)
        <div class="preview-toolbar">
            <div class="preview-toolbar-left">
                <a
                    class="preview-back-btn"
                    href="{{ request()->routeIs('admin.funnel-templates.preview') ? route('admin.funnel-templates.edit', $funnel) : route('funnels.edit', $funnel) }}"
                    aria-label="Back to Builder"
                    title="Back to Builder"
                >
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
            <div class="preview-toolbar-right">
                <span class="preview-mode-chip" aria-label="Preview Mode" title="Preview Mode">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    <span class="preview-mode-label">Preview Mode</span>
                </span>
            </div>
            <div class="preview-device-switcher" role="group" aria-label="Preview device">
                <button type="button" class="preview-device-btn is-active" data-preview-device="desktop" title="Desktop"><i class="fas fa-desktop" aria-hidden="true"></i><span style="position:absolute;left:-9999px;">Desktop</span></button>
                <button type="button" class="preview-device-btn" data-preview-device="tablet" title="Tablet"><i class="fas fa-tablet-alt" aria-hidden="true"></i><span style="position:absolute;left:-9999px;">Tablet</span></button>
                <button type="button" class="preview-device-btn" data-preview-device="mobile" title="Mobile"><i class="fas fa-mobile-alt" aria-hidden="true"></i><span style="position:absolute;left:-9999px;">Mobile</span></button>
            </div>
        </div>
        @endif

        @if(session('error'))
            <div class="wrap" style="padding: 12px 2rem 0;">
                <div style="padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; color: #991b1b; font-size: 14px;">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="wrap" style="padding: 12px 2rem 0;">
                <div style="padding: 12px 16px; background: #fefce8; border: 1px solid #fde68a; border-radius: 10px; color: #92400e; font-size: 14px;">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif

        @php
            // Buyer-facing coupon UX should only show AO-created (tenant-owned) coupons.
            $promoCoupons = collect($availableCoupons ?? [])
                ->filter(fn ($coupon) => ($coupon->scope_type ?? null) === \App\Models\Coupon::SCOPE_TENANT)
                ->values();
            $featuredPromoCoupon = $promoCoupons->first();
        @endphp
        @if(!$isPreview && $featuredPromoCoupon)
            <div class="funnel-coupon-pop" id="funnelCouponPop"
                data-coupon-code="{{ $featuredPromoCoupon->code }}"
                data-coupon-title="{{ $featuredPromoCoupon->title ?: 'Special offer' }}"
                data-coupon-seconds="60">
                <div class="funnel-coupon-card">
                    <div class="funnel-coupon-card-body">
                        <div class="funnel-coupon-kicker">Limited-Time Coupon</div>
                        <h3 class="funnel-coupon-title">{{ $featuredPromoCoupon->title ?: 'Special discount for this funnel' }}</h3>
                        <p class="funnel-coupon-text">
                            Use this code within the next 60 seconds. Copy it now and apply it during checkout before payment.
                        </p>
                        <div class="funnel-coupon-code-row">
                            <div class="funnel-coupon-code" data-funnel-coupon-code>{{ $featuredPromoCoupon->code }}</div>
                            <button type="button" class="funnel-coupon-copy" data-claim-funnel-coupon>Claim Coupon</button>
                        </div>
                        <div class="funnel-coupon-meta">
                            <span data-funnel-coupon-timer>60s left</span>
                            <button type="button" data-hide-funnel-coupon style="border:none;background:transparent;color:#fff;font-weight:700;cursor:pointer;">Hide</button>
                        </div>
                    </div>
                    <div class="funnel-coupon-progress"><span data-funnel-coupon-progress></span></div>
                </div>
                <button type="button" class="funnel-coupon-minitab" data-show-funnel-coupon>Coupon Available</button>
            </div>
        @endif

        @if(!$isPreview || $previewIframeMode)
        <div class="step-content--full">
            @if($hasBuilderLayout)
                @foreach($renderSections as $section)
                    @php
                        $sectionStyleArr = is_array($section['style'] ?? null) ? $section['style'] : [];
                        $sectionRows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                        $sectionElsRaw = is_array($section['elements'] ?? null) ? $section['elements'] : [];
                        $sectionHasSectionElements = count($sectionElsRaw) > 0;
                        if (count($sectionRows) > 0 && !$sectionHasSectionElements) {
                            $secMH = strtolower(trim((string) ($sectionStyleArr['minHeight'] ?? ($sectionStyleArr['min-height'] ?? ''))));
                            if ($secMH !== '') {
                                unset($sectionStyleArr['minHeight'], $sectionStyleArr['min-height']);
                            }
                            $secH = strtolower(trim((string) ($sectionStyleArr['height'] ?? '')));
                            if ($secH !== '') {
                                unset($sectionStyleArr['height']);
                            }
                        }
                        $sectionStyle = $styleToString($sectionStyleArr);
                        $sectionSettings = is_array($section['settings'] ?? null) ? $section['settings'] : [];
                        $sectionAnchorId = ltrim(trim((string) ($sectionSettings['anchorId'] ?? '')), '#');
                        if ($sectionAnchorId !== '') {
                            $sectionAnchorId = preg_replace('/[^a-zA-Z0-9\-_]/', '', $sectionAnchorId) ?: '';
                            $sectionAnchorId = mb_substr($sectionAnchorId, 0, 80);
                        }
                        $isBareCarouselWrap = (bool) ($section['isBareCarouselWrap'] ?? false);
                        $isFreeformCanvas = (bool) ($section['isFreeformCanvas'] ?? false);
                        $contentWidth = trim((string) ($sectionSettings['contentWidth'] ?? 'full'));
                        $sectionStageWidth = max(0, (int) ($sectionSettings['stageWidth'] ?? 0));
                        $widthMap = ['full' => '', 'wide' => '1200px', 'medium' => '992px', 'small' => '768px', 'xsmall' => '576px'];
                        $innerMax = $widthMap[$contentWidth] ?? '';
                        $sectionElements = is_array($section['elements'] ?? null) ? $section['elements'] : [];
                        $hasAbsoluteSectionElement = false;
                        foreach ($sectionElements as $_sectionEl) {
                            $_sectionElStyle = is_array($_sectionEl['style'] ?? null) ? $_sectionEl['style'] : [];
                            $_sectionElSettings = is_array($_sectionEl['settings'] ?? null) ? $_sectionEl['settings'] : [];
                            if (trim((string) ($_sectionElSettings['positionMode'] ?? '')) === 'absolute' || trim((string) ($_sectionElStyle['position'] ?? '')) === 'absolute') {
                                $hasAbsoluteSectionElement = true;
                                break;
                            }
                        }
                        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
                        if (!$isBareCarouselWrap && !$isFreeformCanvas && count($rows) === 0 && count($sectionElements) === 1) {
                            $onlyType = strtolower((string) (($sectionElements[0]['type'] ?? '')));
                            if ($onlyType === 'carousel') {
                                $isBareCarouselWrap = true;
                            }
                        }
                        $carrierMinHeight = 0;
                        if (count($sectionElements) > 0) {
                            $carrierHasAbsElements = false;
                            $carrierMaxRight = 0;
                            foreach ($sectionElements as $_cEl) {
                                $_cElS = is_array($_cEl['settings'] ?? null) ? $_cEl['settings'] : [];
                                $_cElSt = is_array($_cEl['style'] ?? null) ? $_cEl['style'] : [];
                                $_cIsAbs = (trim((string) ($_cElS['positionMode'] ?? '')) === 'absolute') || (trim((string) ($_cElSt['position'] ?? '')) === 'absolute');
                                if ($_cIsAbs) {
                                    $carrierHasAbsElements = true;
                                    $_cY = (int) ($_cElS['freeY'] ?? 0);
                                    if ($_cY <= 0) $_cY = (int) str_replace('px', '', (string) ($_cElSt['top'] ?? '0'));
                                    $_cH = (int) str_replace('px', '', (string) ($_cElSt['height'] ?? '0'));
                                    if ($_cH <= 0) $_cH = 80;
                                    $_cBot = $_cY + $_cH + 20;
                                    if ($_cBot > $carrierMinHeight) $carrierMinHeight = $_cBot;
                                    $_cX = (int) ($_cElS['freeX'] ?? 0);
                                    if ($_cX <= 0) $_cX = (int) str_replace('px', '', (string) ($_cElSt['left'] ?? '0'));
                                    $_cW = (int) str_replace('px', '', (string) ($_cElSt['width'] ?? '0'));
                                    if ($_cW <= 0) $_cW = 120;
                                    $_cRight = $_cX + $_cW;
                                    if ($_cRight > $carrierMaxRight) $carrierMaxRight = $_cRight;
                                }
                            }
                            $carrierColStyle = ['flex' => '1 1 auto', 'backgroundColor' => 'transparent', 'padding' => '0', 'minHeight' => '0'];
                            $carrierConstrainWidth = 0;
                            if ($carrierHasAbsElements) {
                                if ($sectionStageWidth > 0) {
                                    $carrierConstrainWidth = $sectionStageWidth;
                                } elseif ($editorCanvasWidth > 0) {
                                    $carrierConstrainWidth = $editorCanvasWidth;
                                } elseif ($carrierMaxRight > 0) {
                                    $carrierConstrainWidth = $carrierMaxRight + 20;
                                }
                            }
                            $carrierRowStyle = ['gap' => '0', 'padding' => '0', 'backgroundColor' => 'transparent'];
                            if ($carrierConstrainWidth > 0) {
                                $carrierColStyle['width'] = $carrierConstrainWidth . 'px';
                                $carrierColStyle['maxWidth'] = '100%';
                                $carrierColStyle['margin'] = '0 auto';
                            }
                            array_unshift($rows, [
                                'id' => 'sec_el_row_' . md5((string) ($section['id'] ?? uniqid('', true))),
                                'style' => $carrierRowStyle,
                                'settings' => ['contentWidth' => 'full'],
                                'isSectionElementCarrier' => true,
                                'columns' => [[
                                    'id' => 'sec_el_col_' . md5((string) ($section['id'] ?? uniqid('', true))),
                                    'style' => $carrierColStyle,
                                    'settings' => [],
                                    'elements' => $sectionElements,
                                    'isSectionElementCarrier' => true,
                                ]],
                            ]);
                        }
                        $sectionInlineStyle = $sectionStyle;
                        if ($isFreeformCanvas && $editorCanvasWidth > 0) {
                            $sectionInlineStyle .= ($sectionInlineStyle !== '' ? '; ' : '') . 'width:' . $editorCanvasWidth . 'px;max-width:none;margin-left:auto;margin-right:auto;';
                        }
                        if ($isBareCarouselWrap) {
                            $sectionInlineStyle .= ($sectionInlineStyle !== '' ? '; ' : '') . 'border:none;';
                        }
                        $sectionInnerStyle = [];
                        if ($carrierMinHeight > 0) {
                            $sectionInnerStyle[] = 'min-height:' . $carrierMinHeight . 'px';
                        }
                        if ($sectionStageWidth > 0 && $hasAbsoluteSectionElement && !$isBareCarouselWrap && !$isFreeformCanvas) {
                            $sectionInnerStyle[] = 'width:100%';
                            $sectionInnerStyle[] = 'max-width:' . $sectionStageWidth . 'px';
                            if ($innerMax !== '') {
                                $sectionInnerStyle[] = 'margin: 0 auto';
                            }
                        } elseif ($innerMax !== '' && !$isBareCarouselWrap) {
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
                                $isSectionElementCarrierRow = (bool) ($row['isSectionElementCarrier'] ?? false);
                            @endphp
                            <div class="builder-row{{ $isSectionElementCarrierRow ? ' builder-row--section-elements' : '' }}" style="{{ $rowStyle }}">
                                <div class="builder-row-inner" @if($rowInnerStyleString !== '') style="{{ $rowInnerStyleString }}" @endif>
                                @foreach($columns as $column)
                                    @php
                                        $colStyleArr = is_array($column['style'] ?? null) ? $column['style'] : [];
                                        $legacyColBg = strtolower(trim((string) ($colStyleArr['backgroundColor'] ?? '')));
                                        if ($legacyColBg === '#f8fafc' || $legacyColBg === 'rgb(248, 250, 252)' || $legacyColBg === 'rgba(248, 250, 252, 1)') {
                                            $colStyleArr['backgroundColor'] = '#ffffff';
                                        }
                                        $colHasExplicitBg = isset($colStyleArr['backgroundColor']) && trim((string) $colStyleArr['backgroundColor']) !== '' && strtolower(trim((string) $colStyleArr['backgroundColor'])) !== '#ffffff';
                                        if (!$colHasExplicitBg) {
                                            unset($colStyleArr['backgroundColor']);
                                        }
                                        foreach (['height', 'minHeight', 'min-height'] as $_hk) {
                                            $hv = trim((string) ($colStyleArr[$_hk] ?? ''));
                                            if ($hv !== '' && preg_match('/^\d+px$/', $hv) && (int) $hv <= 140) {
                                                unset($colStyleArr[$_hk]);
                                            }
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
                                        $colWidthStyle = $isFreeformCanvas ? 'width:100%;margin-left:0;margin-right:0;' : '';
                                        $isSectionElementCarrierCol = (bool) ($column['isSectionElementCarrier'] ?? false);
                                        $colHeightStyle = ($colMinHeight > 0 && !$isFreeformCanvas && $isSectionElementCarrierCol) ? 'min-height:' . $colMinHeight . 'px;' : '';

                                        if ($hasAbsEl && !$isFreeformCanvas && !$isSectionElementCarrierCol) {
                                            usort($elements, function ($a, $b) {
                                                $aS = is_array($a['settings'] ?? null) ? $a['settings'] : [];
                                                $bS = is_array($b['settings'] ?? null) ? $b['settings'] : [];
                                                $aSt = is_array($a['style'] ?? null) ? $a['style'] : [];
                                                $bSt = is_array($b['style'] ?? null) ? $b['style'] : [];
                                                $aY = (int) ($aS['freeY'] ?? 0);
                                                if ($aY <= 0) $aY = (int) str_replace('px', '', (string) ($aSt['top'] ?? '0'));
                                                $bY = (int) ($bS['freeY'] ?? 0);
                                                if ($bY <= 0) $bY = (int) str_replace('px', '', (string) ($bSt['top'] ?? '0'));
                                                return $aY <=> $bY;
                                            });
                                        }
                                    @endphp
                                    <div class="builder-col{{ $hasAbsEl ? ' builder-col--abs' : '' }}{{ $isSectionElementCarrierCol ? ' builder-col--section-elements' : '' }}" style="{{ $colStyle }}{{ $colHeightStyle }}{{ $colWidthStyle }}">
                                        <div class="builder-col-inner{{ $isSectionElementCarrierCol ? ' builder-col-inner--section-elements' : '' }}" @if($colInnerMax !== '') style="max-width: {{ $colInnerMax }}; margin: 0 auto;" @endif>
                                        @foreach($elements as $element)
                                            @php
                                                $elId = trim((string) ($element['id'] ?? ''));
                                                $type = $element['type'] ?? 'text';
                                                $content = (string) ($element['content'] ?? '');
                                                $rawStyle = is_array($element['style'] ?? null) ? $element['style'] : [];
                                                $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                                                $allowAbsPos = $isFreeformCanvas || $isSectionElementCarrierCol;
                                                $flowStyle = $rawStyle;
                                                if (!$allowAbsPos) {
                                                    $wasAbsolute = (trim((string) ($rawStyle['position'] ?? '')) === 'absolute')
                                                        || (trim((string) ($settings['positionMode'] ?? '')) === 'absolute');
                                                    foreach (['position','left','top','right','bottom','zIndex','z-index'] as $k) {
                                                        if (array_key_exists($k, $flowStyle)) {
                                                            unset($flowStyle[$k]);
                                                        }
                                                    }
                                                    if ($wasAbsolute) {
                                                        $origW = trim((string) ($rawStyle['width'] ?? ''));
                                                        if ($origW !== '') {
                                                            $flowStyle['maxWidth'] = $origW;
                                                        }
                                                        unset($flowStyle['width'], $flowStyle['height'], $flowStyle['margin']);
                                                    }
                                                }
                                                $style = $styleToString($flowStyle);
                                                $contentStyle = $contentStyleToString($flowStyle);
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
                                                $menuAlignStyle = 'width:100%;';
                                                $widthBehavior = $settings['widthBehavior'] ?? 'fluid';
                                                $buttonContainerBg = trim((string) ($settings['containerBgColor'] ?? ''));
                                                if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $buttonContainerBg)) {
                                                    $buttonContainerBg = '';
                                                }
                                                $hasButtonBoxSizing = $type === 'button' && (
                                                    !empty(trim((string) ($rawStyle['width'] ?? '')))
                                                    || !empty(trim((string) ($rawStyle['height'] ?? '')))
                                                );
                                                $btnWrapMargin = trim((string) ($rawStyle['margin'] ?? ''));
                                                $btnWrapWidth = trim((string) ($rawStyle['width'] ?? ''));
                                                $btnWrapHeight = trim((string) ($rawStyle['height'] ?? ''));
                                                $btnWrapLayoutStyle = '';
                                                if ($btnWrapMargin !== '' && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $btnWrapMargin)) {
                                                    $btnWrapLayoutStyle .= 'margin:' . $btnWrapMargin . ';';
                                                }
                                                if ($btnWrapWidth !== '' && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $btnWrapWidth)) {
                                                    $btnWrapLayoutStyle .= 'width:' . $btnWrapWidth . ';';
                                                }
                                                if ($btnWrapHeight !== '' && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $btnWrapHeight)) {
                                                    $btnWrapLayoutStyle .= 'height:' . $btnWrapHeight . ';align-items:stretch;';
                                                }
                                                $btnWrapStyle = ($type === 'button' ? ($btnWrapLayoutStyle . $alignStyle . ($buttonContainerBg !== '' ? 'background-color:' . $buttonContainerBg . ';' : '')) : '');
                                                $iconWrapStyle = ($type === 'icon' ? $alignStyle : '');
                                                $imageWrapStyle = ($style !== '' ? ($style . ';') : '') . $alignStyle;
                                                $mediaWrapStyle = ($style !== '' ? ($style . ';') : '') . $alignStyle;
                                                $btnInnerStyle = $contentStyle;
                                                if ($type === 'button') {
                                                    $btnHasBg = !empty(trim((string) ($rawStyle['backgroundColor'] ?? ($rawStyle['background-color'] ?? ''))));
                                                    $btnHasColor = !empty(trim((string) ($rawStyle['color'] ?? '')));
                                                    $btnHasPadding = !empty(trim((string) ($rawStyle['padding'] ?? '')));
                                                    $btnHasRadius = !empty(trim((string) ($rawStyle['borderRadius'] ?? ($rawStyle['border-radius'] ?? ''))));
                                                    if (!$btnHasBg) { $btnInnerStyle .= ($btnInnerStyle !== '' ? ';' : '') . 'background-color:#240E35'; }
                                                    if (!$btnHasColor) { $btnInnerStyle .= ($btnInnerStyle !== '' ? ';' : '') . 'color:#fff'; }
                                                    if (!$btnHasPadding) { $btnInnerStyle .= ($btnInnerStyle !== '' ? ';' : '') . 'padding:10px 18px'; }
                                                    if (!$btnHasRadius) { $btnInnerStyle .= ($btnInnerStyle !== '' ? ';' : '') . 'border-radius:999px'; }
                                                }
                                                if ($type === 'button' && ($widthBehavior === 'fill' || $hasButtonBoxSizing)) {
                                                    $btnInnerStyle .= ($btnInnerStyle !== '' ? ';' : '') . 'width:100%;display:flex;align-items:center;justify-content:center;box-sizing:border-box;text-align:center;';
                                                }
                                                if ($type === 'button' && $hasButtonBoxSizing) {
                                                    $btnInnerStyle .= 'height:100%;';
                                                }
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
                                                $hasFixedWidth = !empty(trim((string) ($rawStyle['width'] ?? '')));
                                                $hasFixedHeight = !empty(trim((string) ($rawStyle['height'] ?? '')));
                                                $hasSizedImageBox = $type === 'image' && $hasFixedHeight && $isAbsPos;
                                                $isAbsPos = $allowAbsPos && (
                                                    (trim((string) ($settings['positionMode'] ?? '')) === 'absolute')
                                                    || (trim((string) ($rawStyle['position'] ?? '')) === 'absolute')
                                                );
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
                                                    elseif ($type === 'image') {
                                                        $elWrapStyle .= $imageWrapStyle;
                                                        if ($hasSizedImageBox) {
                                                            $elWrapStyle .= ($elWrapStyle !== '' ? ';' : '') . 'overflow:hidden;';
                                                        }
                                                    }
                                                    elseif ($type === 'video') { $elWrapStyle .= $mediaWrapStyle; }
                                                    elseif ($type === 'form') {
                                                        $formWidth = trim((string) ($rawStyle['width'] ?? ''));
                                                        $formFormWidth = trim((string) ($settings['formWidth'] ?? ($settings['width'] ?? '')));
                                                        $formEffectiveWidth = $formWidth !== '' ? $formWidth : ($formFormWidth !== '' ? $formFormWidth : '');
                                                        $formWrapLayoutStyle = '';
                                                        if ($formEffectiveWidth !== '' && preg_match('/^[#(),.%\-\sA-Za-z0-9]+$/u', $formEffectiveWidth)) {
                                                            $formWrapLayoutStyle .= 'width:' . $formEffectiveWidth . ';';
                                                        }
                                                        $elWrapStyle .= $formWrapLayoutStyle . $alignStyle;
                                                    }
                                                    elseif (in_array($type, ['heading', 'text', 'spacer', 'divider', 'testimonial', 'faq', 'pricing', 'countdown', 'product_offer', 'review_form', 'review_list', 'reviews', 'carousel', 'menu', 'checkout_summary', 'physical_checkout_summary', 'shipping_details'], true)) {
                                                        $elWrapStyle .= $style;
                                                    }
                                                }
                                            @endphp
                                            <div class="builder-el" data-element-type="{{ $type }}" @if($elWrapStyle !== '') style="{{ $elWrapStyle }}" @endif>
                                                @if($type === 'heading')
                                                    <h2 class="builder-heading" style="{{ $contentStyle }}">{!! $content !!}</h2>
                                                @elseif($type === 'text')
                                                    <div class="builder-text" style="{{ $contentStyle }}">{!! $content !!}</div>
                                                @elseif($type === 'image')
                                                    @if($src !== '')
                                                        @php
                                                            $imgStyle = $mediaClipStyle;
                                                            if ($hasSizedImageBox) {
                                                                $imgStyle .= ($imgStyle !== '' ? ';' : '') . 'width:100%;height:100%;object-fit:cover;';
                                                            } else {
                                                                $imgStyle .= ($imgStyle !== '' ? ';' : '') . 'width:100%;height:auto;max-width:100%;object-fit:contain;object-position:top center;display:block;';
                                                            }
                                                        @endphp
                                                        <img class="builder-img" src="{{ $src }}" alt="{{ $alt !== '' ? $alt : 'Image' }}" style="{{ $imgStyle }}">
                                                    @else
                                                        <div class="builder-image-placeholder">
                                                            <div class="builder-image-placeholder__inner">
                                                                <div class="builder-image-placeholder__plus">+</div>
                                                                <div class="builder-image-placeholder__label">Click to upload image</div>
                                                            </div>
                                                        </div>
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
                                                    @php
                                                        $iconFontSize = trim((string) ($rawStyle['fontSize'] ?? ($rawStyle['font-size'] ?? '')));
                                                        $iconColor = trim((string) ($rawStyle['color'] ?? ''));
                                                        $iconComputedStyle = '';
                                                        $iconComputedStyle .= 'font-size:' . ($iconFontSize !== '' ? $iconFontSize : '36px') . ';';
                                                        $iconComputedStyle .= 'color:' . ($iconColor !== '' ? $iconColor : '#2E1244') . ';';
                                                    @endphp
                                                    @if($iconLink !== '')
                                                        <a href="{{ $iconLink }}" style="{{ $iconComputedStyle }}"><i class="{{ $iconClass }}" aria-hidden="true"></i></a>
                                                    @else
                                                        <span style="{{ $iconComputedStyle }}"><i class="{{ $iconClass }}" aria-hidden="true"></i></span>
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
                                                        $ctaButtonLabel = trim((string) ($settings['leftButtonLabel'] ?? 'Get Started'));
                                                        if ($ctaButtonLabel === '') $ctaButtonLabel = 'Get Started';
                                                        $ctaButtonUrl = trim((string) ($settings['leftButtonUrl'] ?? '#'));
                                                        if ($ctaButtonUrl === '') $ctaButtonUrl = '#';
                                                        $ctaButtonBg = trim((string) ($settings['leftButtonBgColor'] ?? '#240E35'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $ctaButtonBg)) $ctaButtonBg = '#240E35';
                                                        $ctaButtonText = trim((string) ($settings['leftButtonTextColor'] ?? '#ffffff'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $ctaButtonText)) $ctaButtonText = '#ffffff';
                                                        $ctaButtonTextSize = (int) ($settings['leftButtonTextSize'] ?? 14);
                                                        $ctaButtonTextSize = max(10, min(48, $ctaButtonTextSize));
                                                        $ctaButtonRadius = (int) ($settings['leftButtonBorderRadius'] ?? 999);
                                                        $ctaButtonRadius = max(0, min(80, $ctaButtonRadius));
                                                        $ctaButtonPadY = (int) ($settings['leftButtonPaddingY'] ?? 8);
                                                        $ctaButtonPadY = max(4, min(40, $ctaButtonPadY));
                                                        $ctaButtonPadX = (int) ($settings['leftButtonPaddingX'] ?? 14);
                                                        $ctaButtonPadX = max(8, min(80, $ctaButtonPadX));
                                                        $ctaButtonBold = (bool) ($settings['leftButtonBold'] ?? false);
                                                        $ctaButtonItalic = (bool) ($settings['leftButtonItalic'] ?? false);
                                                        $menuLogoUrl = trim((string) ($settings['rightLogoUrl'] ?? ''));
                                                        $menuLogoAlt = trim((string) ($settings['rightLogoAlt'] ?? 'Logo'));
                                                        if ($menuLogoAlt === '') $menuLogoAlt = 'Logo';
                                                        $menuUid = $elId !== '' ? $elId : ('menu_' . mt_rand(1000, 999999));
                                                    @endphp
                                                    <nav class="builder-menu" style="{{ $menuAlignStyle }}{{ $style !== '' ? $style : '' }}">
                                                        <div class="builder-menu-shell">
                                                            <div class="builder-menu-left">
                                                                @if($menuLogoUrl !== '')
                                                                    <img class="builder-menu-logo" src="{{ $menuLogoUrl }}" alt="{{ $menuLogoAlt }}">
                                                                @else
                                                                    <div class="builder-menu-logo-placeholder">Logo</div>
                                                                @endif
                                                            </div>
                                                            <div class="builder-menu-center">
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
                                                            </div>
                                                            <div class="builder-menu-right">
                                                                <a class="builder-menu-edit-btn" href="{{ $ctaButtonUrl }}" style="background-color: {{ $ctaButtonBg }}; color: {{ $ctaButtonText }}; font-size: {{ $ctaButtonTextSize }}px; border-radius: {{ $ctaButtonRadius }}px; padding: {{ $ctaButtonPadY }}px {{ $ctaButtonPadX }}px; font-weight: {{ $ctaButtonBold ? '700' : '600' }}; font-style: {{ $ctaButtonItalic ? 'italic' : 'normal' }};">{{ $ctaButtonLabel }}</a>
                                                            </div>
                                                            <button type="button" class="builder-menu-toggle" data-menu-toggle="{{ $menuUid }}" aria-expanded="false" aria-controls="menu-mobile-{{ $menuUid }}">
                                                                <i class="fas fa-bars" aria-hidden="true"></i>
                                                                <span style="position:absolute;left:-9999px;">Open menu</span>
                                                            </button>
                                                        </div>
                                                        <div class="builder-menu-mobile-overlay" id="menu-mobile-{{ $menuUid }}" data-menu-panel="{{ $menuUid }}" aria-hidden="true">
                                                            <div class="builder-menu-mobile-drawer">
                                                                <div class="builder-menu-mobile-head">
                                                                    <button type="button" class="builder-menu-mobile-close" data-menu-close="{{ $menuUid }}" aria-label="Close menu">
                                                                        <i class="fas fa-times" aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="builder-menu-mobile-panel">
                                                                    <ul class="builder-menu-mobile-list">
                                                                        @foreach($menuItems as $i => $menuItem)
                                                                            @php
                                                                                $menuLabel = trim((string) ($menuItem['label'] ?? 'Menu item ' . ($i + 1)));
                                                                                $menuHref = trim((string) ($menuItem['url'] ?? '#'));
                                                                                $menuNew = (bool) ($menuItem['newWindow'] ?? false);
                                                                                $linkColor = $menuText;
                                                                                $decoStyle = $menuUnderline !== '' ? 'text-decoration:underline;text-decoration-color:' . $menuUnderline . ';' : 'text-decoration:none;';
                                                                            @endphp
                                                                            <li>
                                                                                <a class="builder-menu-mobile-link" href="{{ $menuHref !== '' ? $menuHref : '#' }}" @if($menuNew) target="_blank" rel="noopener" @endif style="color: {{ $linkColor }}; {{ $decoStyle }} font-family:inherit; font-size:inherit; line-height:inherit; letter-spacing:inherit; font-weight:inherit; font-style:inherit;">{{ $menuLabel !== '' ? $menuLabel : ('Menu item ' . ($i + 1)) }}</a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                    <a class="builder-menu-edit-btn builder-menu-mobile-cta" href="{{ $ctaButtonUrl }}" style="background-color: {{ $ctaButtonBg }}; color: {{ $ctaButtonText }}; font-size: {{ $ctaButtonTextSize }}px; border-radius: {{ $ctaButtonRadius }}px; padding: {{ $ctaButtonPadY }}px {{ $ctaButtonPadX }}px; font-weight: {{ $ctaButtonBold ? '700' : '600' }}; font-style: {{ $ctaButtonItalic ? 'italic' : 'normal' }};">{{ $ctaButtonLabel }}</a>
                                                                </div>
                                                            </div>
                                                        </div>
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
                                                                                                        $menuAlignStyle = 'width:100%;';
                                                                                                        $menuText = trim((string) ($ssc['textColor'] ?? '#374151'));
                                                                                                        $menuUnderline = trim((string) ($ssc['underlineColor'] ?? ''));
                                                                                                        $ctaButtonLabel = trim((string) ($ssc['leftButtonLabel'] ?? 'Get Started'));
                                                                                                        if ($ctaButtonLabel === '') $ctaButtonLabel = 'Get Started';
                                                                                                        $ctaButtonUrl = trim((string) ($ssc['leftButtonUrl'] ?? '#'));
                                                                                                        if ($ctaButtonUrl === '') $ctaButtonUrl = '#';
                                                                                                        $ctaButtonBg = trim((string) ($ssc['leftButtonBgColor'] ?? '#240E35'));
                                                                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $ctaButtonBg)) $ctaButtonBg = '#240E35';
                                                                                                        $ctaButtonText = trim((string) ($ssc['leftButtonTextColor'] ?? '#ffffff'));
                                                                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $ctaButtonText)) $ctaButtonText = '#ffffff';
                                                                                                        $ctaButtonTextSize = (int) ($ssc['leftButtonTextSize'] ?? 14);
                                                                                                        $ctaButtonTextSize = max(10, min(48, $ctaButtonTextSize));
                                                                                                        $ctaButtonRadius = (int) ($ssc['leftButtonBorderRadius'] ?? 999);
                                                                                                        $ctaButtonRadius = max(0, min(80, $ctaButtonRadius));
                                                                                                        $ctaButtonPadY = (int) ($ssc['leftButtonPaddingY'] ?? 8);
                                                                                                        $ctaButtonPadY = max(4, min(40, $ctaButtonPadY));
                                                                                                        $ctaButtonPadX = (int) ($ssc['leftButtonPaddingX'] ?? 14);
                                                                                                        $ctaButtonPadX = max(8, min(80, $ctaButtonPadX));
                                                                                                        $ctaButtonBold = (bool) ($ssc['leftButtonBold'] ?? false);
                                                                                                        $ctaButtonItalic = (bool) ($ssc['leftButtonItalic'] ?? false);
                                                                                                        $menuLogoUrl = trim((string) ($ssc['rightLogoUrl'] ?? ''));
                                                                                                        $menuLogoAlt = trim((string) ($ssc['rightLogoAlt'] ?? 'Logo'));
                                                                                                        if ($menuLogoAlt === '') $menuLogoAlt = 'Logo';
                                                                                                        $menuUid = trim((string) ($sel['id'] ?? '')) !== '' ? trim((string) $sel['id']) : ('menu_' . mt_rand(1000, 999999));
                                                                                                    @endphp
                                                                                                    <nav class="builder-menu" style="{{ $menuAlignStyle }}{{ $ss !== '' ? $ss : '' }}">
                                                                                                        <div class="builder-menu-shell">
                                                                                                            <div class="builder-menu-left">
                                                                                                                @if($menuLogoUrl !== '')
                                                                                                                    <img class="builder-menu-logo" src="{{ $menuLogoUrl }}" alt="{{ $menuLogoAlt }}">
                                                                                                                @else
                                                                                                                    <div class="builder-menu-logo-placeholder">Logo</div>
                                                                                                                @endif
                                                                                                            </div>
                                                                                                            <div class="builder-menu-center">
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
                                                                                                            </div>
                                                                                                            <div class="builder-menu-right">
                                                                                                                <a class="builder-menu-edit-btn" href="{{ $ctaButtonUrl }}" style="background-color: {{ $ctaButtonBg }}; color: {{ $ctaButtonText }}; font-size: {{ $ctaButtonTextSize }}px; border-radius: {{ $ctaButtonRadius }}px; padding: {{ $ctaButtonPadY }}px {{ $ctaButtonPadX }}px; font-weight: {{ $ctaButtonBold ? '700' : '600' }}; font-style: {{ $ctaButtonItalic ? 'italic' : 'normal' }};">{{ $ctaButtonLabel }}</a>
                                                                                                            </div>
                                                                                                            <button type="button" class="builder-menu-toggle" data-menu-toggle="{{ $menuUid }}" aria-expanded="false" aria-controls="menu-mobile-{{ $menuUid }}">
                                                                                                                <i class="fas fa-bars" aria-hidden="true"></i>
                                                                                                                <span style="position:absolute;left:-9999px;">Open menu</span>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                        <div class="builder-menu-mobile-overlay" id="menu-mobile-{{ $menuUid }}" data-menu-panel="{{ $menuUid }}" aria-hidden="true">
                                                                                                            <div class="builder-menu-mobile-drawer">
                                                                                                                <div class="builder-menu-mobile-head">
                                                                                                                    <button type="button" class="builder-menu-mobile-close" data-menu-close="{{ $menuUid }}" aria-label="Close menu">
                                                                                                                        <i class="fas fa-times" aria-hidden="true"></i>
                                                                                                                    </button>
                                                                                                                </div>
                                                                                                                <div class="builder-menu-mobile-panel">
                                                                                                                    <ul class="builder-menu-mobile-list">
                                                                                                                        @foreach($menuItems as $i => $menuItem)
                                                                                                                            @php
                                                                                                                                $menuLabel = trim((string) ($menuItem['label'] ?? 'Menu item ' . ($i + 1)));
                                                                                                                                $menuHref = trim((string) ($menuItem['url'] ?? '#'));
                                                                                                                                $menuNew = (bool) ($menuItem['newWindow'] ?? false);
                                                                                                                                $linkColor = $menuText;
                                                                                                                                $decoStyle = $menuUnderline !== '' ? 'text-decoration:underline;text-decoration-color:' . $menuUnderline . ';' : 'text-decoration:none;';
                                                                                                                            @endphp
                                                                                                                            <li>
                                                                                                                                <a class="builder-menu-mobile-link" href="{{ $menuHref !== '' ? $menuHref : '#' }}" @if($menuNew) target="_blank" rel="noopener" @endif style="color: {{ $linkColor }}; {{ $decoStyle }} font-family:inherit; font-size:inherit; line-height:inherit; letter-spacing:inherit; font-weight:inherit; font-style:inherit;">{{ $menuLabel !== '' ? $menuLabel : ('Menu item ' . ($i + 1)) }}</a>
                                                                                                                            </li>
                                                                                                                        @endforeach
                                                                                                                    </ul>
                                                                                                                    <a class="builder-menu-edit-btn builder-menu-mobile-cta" href="{{ $ctaButtonUrl }}" style="background-color: {{ $ctaButtonBg }}; color: {{ $ctaButtonText }}; font-size: {{ $ctaButtonTextSize }}px; border-radius: {{ $ctaButtonRadius }}px; padding: {{ $ctaButtonPadY }}px {{ $ctaButtonPadX }}px; font-weight: {{ $ctaButtonBold ? '700' : '600' }}; font-style: {{ $ctaButtonItalic ? 'italic' : 'normal' }};">{{ $ctaButtonLabel }}</a>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
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
                                                @elseif($type === 'review_form')
                                                    @php
                                                        $funnelPurpose = strtolower(trim((string) (($funnel->purpose ?? null) ?: ($funnel->template_type ?? 'service'))));
                                                        $isPhysicalReviewFlow = $funnelPurpose === 'physical_product';
                                                        $defaultReviewHeading = $isPhysicalReviewFlow ? 'How was your order experience?' : 'How was your experience?';
                                                        $defaultReviewSubtitle = $isPhysicalReviewFlow
                                                            ? 'Tell us how the ordering and checkout experience felt while your item is on the way.'
                                                            : 'Share a quick review after your order or service experience.';
                                                        $configuredHeading = trim((string) ($settings['heading'] ?? ''));
                                                        $configuredSubtitle = trim((string) ($settings['subtitle'] ?? ''));
                                                        $reviewHeading = $configuredHeading !== '' ? $configuredHeading : $defaultReviewHeading;
                                                        $reviewSubtitle = $configuredSubtitle !== '' ? $configuredSubtitle : $defaultReviewSubtitle;
                                                        $physicalHeading = trim((string) ($settings['physicalHeading'] ?? ''));
                                                        $physicalSubtitle = trim((string) ($settings['physicalSubtitle'] ?? ''));
                                                        if ($isPhysicalReviewFlow) {
                                                            if ($physicalHeading !== '') $reviewHeading = $physicalHeading;
                                                            if ($physicalSubtitle !== '') $reviewSubtitle = $physicalSubtitle;
                                                        }
                                                        $reviewButton = trim((string) ($settings['buttonLabel'] ?? 'Submit Review'));
                                                        if ($reviewButton === '') $reviewButton = 'Submit Review';
                                                        $reviewPublicLabel = trim((string) ($settings['publicLabel'] ?? 'I am okay with showing this review publicly.'));
                                                        if ($reviewPublicLabel === '') $reviewPublicLabel = 'I am okay with showing this review publicly.';
                                                        $reviewSuccess = trim((string) ($settings['successMessage'] ?? 'Thanks for the review. It is now waiting for approval.'));
                                                        $reviewColor = trim((string) ($rawStyle['color'] ?? ''));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $reviewColor)) $reviewColor = '';
                                                        $reviewCtaBg = trim((string) ($settings['ctaBgColor'] ?? '#240E35'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $reviewCtaBg)) $reviewCtaBg = '#240E35';
                                                        $reviewCtaText = trim((string) ($settings['ctaTextColor'] ?? '#ffffff'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $reviewCtaText)) $reviewCtaText = '#ffffff';
                                                        $prefillName = trim((string) ($reviewPrefill['name'] ?? ''));
                                                        $prefillEmail = trim((string) ($reviewPrefill['email'] ?? ''));
                                                    @endphp
                                                    <div class="builder-review-form" style="{{ $contentStyle }}">
                                                        <div class="builder-review-title" @if($reviewColor !== '') style="color: {{ $reviewColor }};" @endif>{{ $reviewHeading }}</div>
                                                        @if($reviewSubtitle !== '')
                                                            <div class="builder-review-subtitle" @if($reviewColor !== '') style="color: {{ $reviewColor }}; opacity: .78;" @endif>{{ $reviewSubtitle }}</div>
                                                        @endif
                                                        @if($isPreview || $isTemplateTest)
                                                            <div class="builder-review-stars" aria-hidden="true"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                                                            <input class="builder-review-input" type="text" value="{{ $prefillName !== '' ? $prefillName : 'Your name' }}" disabled>
                                                            <input class="builder-review-input" type="email" value="{{ $prefillEmail !== '' ? $prefillEmail : 'Email address' }}" disabled>
                                                            <textarea class="builder-review-textarea" disabled>Write a quick review...</textarea>
                                                            <label class="builder-review-check"><input type="checkbox" disabled> <span>{{ $reviewPublicLabel }}</span></label>
                                                            <button type="button" class="builder-pricing-cta" style="background: {{ $reviewCtaBg }}; color: {{ $reviewCtaText }}; opacity:.72; cursor:not-allowed;" disabled>{{ $reviewButton }}</button>
                                                        @elseif($reviewAlreadySubmitted)
                                                            <div class="builder-review-note">{{ session('review_status', $reviewSuccess) }}</div>
                                                        @else
                                                            <form method="POST" action="{{ route('funnels.portal.review', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}" class="builder-review-form">
                                                                @csrf
                                                                <div class="builder-review-stars" aria-label="Rating">
                                                                    @for($star = 5; $star >= 1; $star--)
                                                                        <label style="cursor:pointer;">
                                                                            <input type="radio" name="rating" value="{{ $star }}" style="display:none;" @checked((int) old('rating', 5) === $star)>
                                                                            <span>★</span>
                                                                        </label>
                                                                    @endfor
                                                                </div>
                                                                <input class="builder-review-input" type="text" name="customer_name" value="{{ old('customer_name', $prefillName) }}" placeholder="Your name" required>
                                                                <input class="builder-review-input" type="email" name="customer_email" value="{{ old('customer_email', $prefillEmail) }}" placeholder="Email address">
                                                                <textarea class="builder-review-textarea" name="review_text" placeholder="Write a quick review..." required>{{ old('review_text') }}</textarea>
                                                                <label class="builder-review-check">
                                                                    <input type="hidden" name="is_public" value="0">
                                                                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', '1') === '1')>
                                                                    <span>{{ $reviewPublicLabel }}</span>
                                                                </label>
                                                                <button type="submit" class="builder-pricing-cta" style="background: {{ $reviewCtaBg }}; color: {{ $reviewCtaText }};">{{ $reviewButton }}</button>
                                                                @if(session('review_status'))
                                                                    <div class="builder-review-note">{{ session('review_status') }}</div>
                                                                @endif
                                                                @error('review')
                                                                    <div class="builder-review-note" style="color:#b91c1c;">{{ $message }}</div>
                                                                @enderror
                                                            </form>
                                                        @endif
                                                    </div>
                                                @elseif($type === 'reviews')
                                                    @php
                                                        $reviewsHeading = trim((string) ($settings['heading'] ?? 'What customers are saying'));
                                                        $reviewsSubtitle = trim((string) ($settings['subtitle'] ?? 'Approved reviews from this funnel appear here automatically.'));
                                                        $reviewsEmpty = trim((string) ($settings['emptyText'] ?? 'Approved reviews will appear here after customers submit them.'));
                                                        $reviewsLayout = strtolower(trim((string) ($settings['layout'] ?? 'list'))) === 'grid' ? 'grid' : 'list';
                                                        $reviewsMax = max(1, min(24, (int) ($settings['maxItems'] ?? 3)));
                                                        $reviewsFilterRating = max(0, min(5, (int) ($settings['filterRating'] ?? ($settings['minRating'] ?? 0))));
                                                        $reviewsShowRating = ($settings['showRating'] ?? true) !== false;
                                                        $reviewsShowDate = ($settings['showDate'] ?? false) === true;
                                                        $reviewsCollapsible = ($settings['collapsible'] ?? true) !== false;
                                                        $reviewsCollapsedCount = max(1, min(24, (int) ($settings['collapsedCount'] ?? 3)));
                                                        $reviewsExpandLabel = trim((string) ($settings['expandLabel'] ?? 'Show all reviews'));
                                                        if ($reviewsExpandLabel === '') $reviewsExpandLabel = 'Show all reviews';
                                                        $reviewsCollapseLabel = trim((string) ($settings['collapseLabel'] ?? 'Show fewer reviews'));
                                                        if ($reviewsCollapseLabel === '') $reviewsCollapseLabel = 'Show fewer reviews';
                                                        $reviewsColor = trim((string) ($rawStyle['color'] ?? ''));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $reviewsColor)) $reviewsColor = '';
                                                        $reviewsData = collect($approvedReviews ?? []);
                                                        if ($reviewsFilterRating > 0) {
                                                            $reviewsData = $reviewsData->filter(fn ($review) => (int) ($review->rating ?? 0) === $reviewsFilterRating);
                                                        }
                                                        $reviewsData = $reviewsData->take($reviewsMax);
                                                        if ($isPreview && $reviewsData->isEmpty()) {
                                                            $reviewsData = collect([
                                                                (object) ['customer_name' => 'Maria Dela Cruz', 'rating' => 5, 'review_text' => 'Fast checkout and a very smooth overall experience.', 'approved_at' => now()],
                                                                (object) ['customer_name' => 'John Reyes', 'rating' => 4, 'review_text' => 'Everything was clear and easy to follow from start to finish.', 'approved_at' => now()],
                                                            ]);
                                                        }
                                                    @endphp
                                                    <div class="builder-review-form" style="{{ $contentStyle }}">
                                                        <div class="builder-review-title" @if($reviewsColor !== '') style="color: {{ $reviewsColor }};" @endif>{{ $reviewsHeading !== '' ? $reviewsHeading : 'What customers are saying' }}</div>
                                                        @if($reviewsSubtitle !== '')
                                                            <div class="builder-review-subtitle" @if($reviewsColor !== '') style="color: {{ $reviewsColor }}; opacity: .78;" @endif>{{ $reviewsSubtitle }}</div>
                                                        @endif
                                                        @if($reviewsData->isEmpty())
                                                            <div class="builder-review-note">{{ $reviewsEmpty !== '' ? $reviewsEmpty : 'Approved reviews will appear here after customers submit them.' }}</div>
                                                        @else
                                                            <div class="builder-review-list {{ $reviewsLayout }}" data-review-list>
                                                                @foreach($reviewsData as $reviewIndex => $review)
                                                                    @php
                                                                        $reviewName = trim((string) ($review->customer_name ?? 'Customer'));
                                                                        if ($reviewName === '') $reviewName = 'Customer';
                                                                        $reviewText = trim((string) ($review->review_text ?? ''));
                                                                        $reviewStars = max(1, min(5, (int) ($review->rating ?? 5)));
                                                                        $reviewApprovedAt = $review->approved_at ?? null;
                                                                        $reviewHidden = $reviewsCollapsible && $reviewIndex >= $reviewsCollapsedCount;
                                                                    @endphp
                                                                    <div class="builder-review-card{{ $reviewHidden ? ' builder-review-hidden' : '' }}" @if($reviewHidden) data-review-extra hidden @endif>
                                                                        <div class="builder-review-card-head">
                                                                            <div>
                                                                                <div class="builder-review-card-name" @if($reviewsColor !== '') style="color: {{ $reviewsColor }};" @endif>{{ $reviewName }}</div>
                                                                                @if($reviewsShowDate && $reviewApprovedAt)
                                                                                    <div class="builder-review-card-date">{{ \Illuminate\Support\Carbon::parse($reviewApprovedAt)->format('M d, Y') }}</div>
                                                                                @endif
                                                                            </div>
                                                                            @if($reviewsShowRating)
                                                                                <div class="builder-review-card-stars">{{ str_repeat('★', $reviewStars) }}{{ str_repeat('☆', 5 - $reviewStars) }}</div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="builder-review-card-text" @if($reviewsColor !== '') style="color: {{ $reviewsColor }}; opacity: .88;" @endif>{{ $reviewText !== '' ? $reviewText : 'Customer review.' }}</div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            @if($reviewsCollapsible && $reviewsData->count() > $reviewsCollapsedCount)
                                                                <button
                                                                    type="button"
                                                                    class="builder-review-toggle"
                                                                    data-review-toggle
                                                                    data-expand-label="{{ $reviewsExpandLabel }}"
                                                                    data-collapse-label="{{ $reviewsCollapseLabel }}"
                                                                >{{ $reviewsExpandLabel }}</button>
                                                            @endif
                                                        @endif
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
                                                            $selectedImage = trim((string) ($selectedCheckoutPricing['image'] ?? ''));
                                                            $selectedFeatures = is_array($selectedCheckoutPricing['features'] ?? null) ? $selectedCheckoutPricing['features'] : [];
                                                            if ($selectedPlan !== '') $plan = $selectedPlan;
                                                            if ($selectedPrice !== '') $priceVal = $selectedPrice;
                                                            if ($selectedRegularPrice !== '') $regularPrice = $selectedRegularPrice;
                                                            if ($selectedPeriod !== '') $period = $selectedPeriod;
                                                            if ($selectedSubtitle !== '') $subtitle = $selectedSubtitle;
                                                            if ($selectedBadge !== '') $badge = $selectedBadge;
                                                            if (count($selectedFeatures) > 0) $features = $selectedFeatures;
                                                        } else {
                                                            $selectedImage = '';
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
                                                @elseif($type === 'product_offer')
                                                    @php
                                                        $plan = trim((string) ($settings['plan'] ?? 'Product'));
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
                                                        $description = trim((string) ($settings['description'] ?? ''));
                                                        $badge = trim((string) ($settings['badge'] ?? ''));
                                                        $quickViewEnabled = (bool) ($settings['quickViewEnabled'] ?? true);
                                                        $cartEnabled = (bool) ($settings['cartEnabled'] ?? true);
                                                        $quickViewLabel = trim((string) ($settings['quickViewLabel'] ?? 'Details'));
                                                        if ($quickViewLabel === '') $quickViewLabel = 'Details';
                                                        $features = is_array($settings['features'] ?? null) ? $settings['features'] : [];
                                                        if (count($features) === 0) $features = ['Feature one', 'Feature two', 'Feature three'];
                                                        $productMediaRaw = is_array($settings['media'] ?? null) ? $settings['media'] : [];
                                                        $productMedia = [];
                                                        foreach ($productMediaRaw as $mi => $mediaItem) {
                                                            if (is_string($mediaItem)) {
                                                                $mediaItem = ['type' => 'image', 'src' => $mediaItem];
                                                            }
                                                            $mediaItem = is_array($mediaItem) ? $mediaItem : [];
                                                            $mediaType = strtolower(trim((string) ($mediaItem['type'] ?? 'image')));
                                                            if (!in_array($mediaType, ['image', 'video'], true)) $mediaType = 'image';
                                                            $mediaSrc = trim((string) ($mediaItem['src'] ?? ''));
                                                            $mediaAlt = trim((string) ($mediaItem['alt'] ?? ('Media ' . ($mi + 1))));
                                                            $mediaPoster = trim((string) ($mediaItem['poster'] ?? ''));
                                                            $productMedia[] = ['type' => $mediaType, 'src' => $mediaSrc, 'alt' => $mediaAlt, 'poster' => $mediaPoster];
                                                        }
                                                        if (count($productMedia) === 0) {
                                                            $productMedia[] = ['type' => 'image', 'src' => '', 'alt' => 'Product image', 'poster' => ''];
                                                        }
                                                        $activeMedia = (int) ($settings['activeMedia'] ?? 0);
                                                        if ($activeMedia < 0) $activeMedia = 0;
                                                        if ($activeMedia >= count($productMedia)) $activeMedia = count($productMedia) - 1;
                                                        $pricingTextColor = trim((string) ($rawStyle['color'] ?? ''));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $pricingTextColor)) $pricingTextColor = '';
                                                        $pricingCtaAction = $resolvePricingCtaAction($settings);
                                                        $ctaLabelRaw = array_key_exists('ctaLabel', $settings) ? trim((string) $settings['ctaLabel']) : '';
                                                        $ctaLabel = $currentStepType === 'checkout'
                                                            ? 'Pay Now'
                                                            : ($ctaLabelRaw !== '' ? $ctaLabelRaw : ($currentStepType === 'sales' && $plan !== '' ? 'Buy ' . $plan : 'Buy Now'));
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
                                                        $pricingFeaturesJson = json_encode(array_values($features), JSON_UNESCAPED_UNICODE);
                                                        $cartImage = '';
                                                        foreach ($productMedia as $cartMediaItem) {
                                                            $candidateSrc = trim((string) ($cartMediaItem['src'] ?? ''));
                                                            if ($candidateSrc !== '' && strtolower(trim((string) ($cartMediaItem['type'] ?? 'image'))) === 'image') {
                                                                $cartImage = $candidateSrc;
                                                                break;
                                                            }
                                                        }
                                                        $scale = $clampScale($settings['contentScale'] ?? 1);
                                                        $scaledContentStyle = $contentStyle;
                                                        if ($scaledContentStyle !== '' && substr($scaledContentStyle, -1) !== ';') $scaledContentStyle .= ';';
                                                        $scaledContentStyle .= 'gap:' . (int) round(4 * $scale) . 'px;';
                                                        $pad = trim((string) ($rawStyle['padding'] ?? ''));
                                                        if ($pad !== '') $scaledContentStyle .= 'padding:' . $scalePaddingValue($pad, $scale) . ';';
                                                        $radius = trim((string) ($rawStyle['borderRadius'] ?? ''));
                                                        if ($radius !== '') $scaledContentStyle .= 'border-radius:' . $scalePxValue($radius, $scale) . ';';
                                                        $badgeStyle = 'font-size:' . (int) round(9 * $scale) . 'px;padding:' . (int) round(2 * $scale) . 'px ' . (int) round(6 * $scale) . 'px;';
                                                        $titleStyle = 'font-size:' . (int) round(13 * $scale) . 'px;line-height:1.25;';
                                                        $priceStyle = 'font-size:' . (int) round(20 * $scale) . 'px;line-height:1;';
                                                        $periodStyle = 'font-size:' . (int) round(10 * $scale) . 'px;';
                                                        $subtitleStyle = 'font-size:' . (int) round(10 * $scale) . 'px;line-height:1.3;';
                                                        $featureStyle = 'font-size:' . (int) round(10 * $scale) . 'px;gap:' . (int) round(4 * $scale) . 'px;';
                                                        $featureGapStyle = 'gap:' . (int) round(4 * $scale) . 'px;';
                                                        $ctaStyle = 'font-size:' . (int) round(11 * $scale) . 'px;padding:' . (int) round(7 * $scale) . 'px ' . (int) round(8 * $scale) . 'px;';
                                                        $productCarouselId = 'prod_' . md5((string) ($element['id'] ?? uniqid('', true)));
                                                        $productModalId = 'product_quick_view_' . md5((string) ($element['id'] ?? uniqid('', true)));
                                                        $stockInfo = $productInventory[$elId] ?? null;
                                                        $hasLimitedStock = is_array($stockInfo) && (int) ($stockInfo['stock_quantity'] ?? 0) > 0;
                                                        $remainingStock = $hasLimitedStock ? (int) ($stockInfo['remaining_stock'] ?? 0) : null;
                                                        $isOutOfStock = $hasLimitedStock && $remainingStock <= 0;
                                                    @endphp
                                                    <div class="builder-pricing builder-product-offer" data-pricing-id="{{ $elId }}" data-pricing-plan="{{ $plan }}" data-pricing-sale="{{ $priceVal }}" data-pricing-regular="{{ $regularPrice }}" data-pricing-period="{{ $period }}" data-pricing-subtitle="{{ $subtitle }}" data-pricing-badge="{{ $badge }}" data-pricing-image="{{ $cartImage }}" data-pricing-features="{{ $pricingFeaturesJson }}" style="{{ $scaledContentStyle }}">
                                                        <div class="builder-product-media">
                                                            @php $renderableMedia = collect($productMedia)->filter(fn($m) => trim((string) ($m['src'] ?? '')) !== '')->values(); @endphp
                                                            @if($renderableMedia->count() > 0)
                                                                <div class="builder-carousel-wrap" data-carousel id="{{ $productCarouselId }}" data-active="{{ $activeMedia }}" data-mode="manual" style="background:#ffffff !important; color:#0f172a;">
                                                                    <div class="builder-carousel-track" data-carousel-track style="transform: translateX(-{{ $activeMedia * 100 }}%);">
                                                                        @foreach($productMedia as $media)
                                                                            <div class="builder-carousel-slide" style="justify-content:center;background:#ffffff !important;">
                                                                                @if(trim((string) ($media['src'] ?? '')) !== '')
                                                                                    @if(($media['type'] ?? 'image') === 'video')
                                                                                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#ffffff;">
                                                                                            <video controls preload="metadata" playsinline @if(trim((string) ($media['poster'] ?? '')) !== '') poster="{{ $media['poster'] }}" @endif style="width:100%;height:100%;object-fit:cover;display:block;background:#ffffff;">
                                                                                                <source src="{{ $media['src'] }}">
                                                                                            </video>
                                                                                        </div>
                                                                                    @else
                                                                                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#ffffff;">
                                                                                            <img class="builder-img" src="{{ $media['src'] }}" alt="{{ trim((string) ($media['alt'] ?? 'Product media')) }}" style="width:100%;height:100%;object-fit:cover;display:block;border-radius:0;">
                                                                                        </div>
                                                                                    @endif
                                                                                @else
                                                                                    <div class="builder-product-media__placeholder"><i class="fas fa-images"></i><div>Add product image or video</div></div>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                    @if(count($productMedia) > 1)
                                                                        <button type="button" class="builder-carousel-arrow is-left" data-carousel-prev style="background:#64748b; color:#ffffff;"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                                                                        <button type="button" class="builder-carousel-arrow is-right" data-carousel-next style="background:#64748b; color:#ffffff;"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                                                                        <div class="builder-carousel-dots" data-carousel-dots>
                                                                            @foreach($productMedia as $si => $unused)
                                                                                <button type="button" class="builder-carousel-dot{{ $si === $activeMedia ? ' is-active' : '' }}" data-carousel-dot="{{ $si }}" aria-label="Go to media {{ $si + 1 }}"></button>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="builder-product-media__placeholder"><i class="fas fa-images"></i><div>Add product image or video</div></div>
                                                            @endif
                                                        </div>
                                                        @if($badge !== '')
                                                            <div class="builder-pricing-badge" style="{{ $badgeStyle }}">{{ $badge }}</div>
                                                        @endif
                                                        <div class="builder-pricing-title" style="{{ $titleStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }};@endif">{{ $plan !== '' ? $plan : 'Product' }}</div>
                                                        <div>
                                                            <span class="builder-pricing-price" data-pricing-price style="{{ $priceStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }};@endif">{{ $priceVal !== '' ? $priceVal : '₱0' }}</span>
                                                            @if($regularPrice !== '' && $regularPrice !== $priceVal)
                                                                <span class="builder-pricing-period" style="{{ $periodStyle }}text-decoration:line-through;margin-left:8px;@if($pricingTextColor !== '')color: {{ $pricingTextColor }}; opacity: 0.55;@endif">{{ $regularPrice }}</span>
                                                            @endif
                                                            @if($period !== '')
                                                                <span class="builder-pricing-period" style="{{ $periodStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }}; opacity: 0.7;@endif">{{ $period }}</span>
                                                            @endif
                                                        </div>
                                                        @if($subtitle !== '')
                                                            <div class="builder-pricing-subtitle" style="{{ $subtitleStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }}; opacity: 0.7;@endif">{{ $subtitle }}</div>
                                                        @endif
                                                        @if($hasLimitedStock)
                                                            <div class="builder-pricing-subtitle" style="{{ $subtitleStyle }}font-weight:700;@if($isOutOfStock)color:#b91c1c;@elseif($pricingTextColor !== '')color: {{ $pricingTextColor }};@else color:#0f766e;@endif">
                                                                {{ $isOutOfStock ? 'Out of stock' : ($remainingStock . ' left in stock') }}
                                                            </div>
                                                        @endif
                                                        <ul class="builder-pricing-features" style="{{ $featureGapStyle }}">
                                                            @foreach($features as $fi => $feat)
                                                                @php $featText = trim((string) $feat); if ($featText === '') $featText = 'Feature ' . ($fi + 1); @endphp
                                                                <li style="{{ $featureStyle }}@if($pricingTextColor !== '')color: {{ $pricingTextColor }};@endif"><i class="fas fa-check" aria-hidden="true"></i> {{ $featText }}</li>
                                                            @endforeach
                                                        </ul>
                                                        <div class="builder-product-actions">
                                                            @if($ctaLabel !== '')
                                                                @if($isOutOfStock)
                                                                    <button type="button" class="builder-pricing-cta" style="{{ $ctaStyle }}background: #94a3b8; color: #ffffff; opacity:0.8;cursor:not-allowed;" disabled>Out of Stock</button>
                                                                @elseif(($pricingCtaAction['kind'] ?? 'link') === 'post')
                                                                    <form method="POST" action="{{ $pricingCtaAction['action'] }}" style="margin:0;">
                                                                        @csrf
                                                                        @foreach(($pricingCtaAction['fields'] ?? []) as $fieldName => $fieldValue)
                                                                            <input type="hidden" name="{{ $fieldName }}" value="{{ $fieldName === 'amount' && $pricingPostedAmount > 0 ? $pricingPostedAmount : $fieldValue }}">
                                                                        @endforeach
                                                                        <input type="hidden" name="website" value="">
                                                                        <input type="hidden" name="checkout_pricing_id" value="{{ $elId }}">
                                                                        <input type="hidden" name="checkout_pricing_source_step" value="{{ $step->slug }}">
                                                                        <input type="hidden" name="checkout_pricing_plan" value="{{ $plan }}">
                                                                        <input type="hidden" name="checkout_pricing_price" value="{{ $priceVal }}">
                                                                        <input type="hidden" name="checkout_pricing_regular_price" value="{{ $regularPrice }}">
                                                                        <input type="hidden" name="checkout_pricing_period" value="{{ $period }}">
                                                                        <input type="hidden" name="checkout_pricing_subtitle" value="{{ $subtitle }}">
                                                                        <input type="hidden" name="checkout_pricing_badge" value="{{ $badge }}">
                                                                        <input type="hidden" name="checkout_pricing_image" value="{{ $cartImage }}">
                                                                        <input type="hidden" name="checkout_pricing_features" value="{{ $pricingFeaturesJson }}">
                                                                        <button type="submit" class="builder-pricing-cta" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }};">{{ $ctaLabel }}</button>
                                                                    </form>
                                                                @elseif(($pricingCtaAction['kind'] ?? 'link') === 'disabled')
                                                                    <button type="button" class="builder-pricing-cta" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }}; opacity:0.7;cursor:not-allowed;" disabled>{{ $ctaLabel }}</button>
                                                                @else
                                                                    <a class="builder-pricing-cta" href="{{ $pricingCtaHref }}" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }};">{{ $ctaLabel }}</a>
                                                                @endif
                                                            @endif
                                                            @if($quickViewEnabled || $cartEnabled)
                                                                <div class="builder-product-utility">
                                                                    @if($quickViewEnabled)
                                                                        <button type="button" class="builder-product-secondary" data-product-modal-target="{{ $productModalId }}">{{ $quickViewLabel }}</button>
                                                                    @else
                                                                        <span></span>
                                                                    @endif
                                                                    @if($cartEnabled)
                                                                        <button type="button"
                                                                            class="builder-product-cart"
                                                                            data-product-add-to-cart
                                                                            data-product-id="{{ $elId }}"
                                                                            data-product-name="{{ $plan }}"
                                                                            data-product-price="{{ $priceVal }}"
                                                                            data-product-regular-price="{{ $regularPrice }}"
                                                                            data-product-period="{{ $period }}"
                                                                            data-product-badge="{{ $badge }}"
                                                                            data-product-image="{{ $cartImage }}"
                                                                            data-product-step="{{ $step->slug }}"
                                                                            data-product-stock-remaining="{{ $hasLimitedStock ? $remainingStock : '' }}"
                                                                            aria-label="{{ $isOutOfStock ? (($plan !== '' ? $plan : 'Product') . ' is out of stock') : ('Add ' . ($plan !== '' ? $plan : 'product') . ' to cart') }}"
                                                                            title="{{ $isOutOfStock ? 'Out of stock' : 'Add to cart' }}"
                                                                            @if($isOutOfStock) disabled @endif>
                                                                            <i class="fas fa-cart-shopping" aria-hidden="true"></i>
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if($quickViewEnabled)
                                                        <template id="{{ $productModalId }}">
                                                            <div class="product-quick-view-layout">
                                                                <div class="product-quick-view-media">
                                                                    @php $modalActiveMedia = $activeMedia; @endphp
                                                                    <div class="builder-carousel-wrap" data-carousel data-active="{{ $modalActiveMedia }}" data-mode="manual" style="background:#ffffff !important; color:#0f172a;">
                                                                        <div class="builder-carousel-track" data-carousel-track style="transform: translateX(-{{ $modalActiveMedia * 100 }}%);">
                                                                            @foreach($productMedia as $media)
                                                                                <div class="builder-carousel-slide" style="justify-content:center;background:#ffffff !important;">
                                                                                    @if(trim((string) ($media['src'] ?? '')) !== '')
                                                                                        @if(($media['type'] ?? 'image') === 'video')
                                                                                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#ffffff;">
                                                                                                <video controls preload="metadata" playsinline @if(trim((string) ($media['poster'] ?? '')) !== '') poster="{{ $media['poster'] }}" @endif style="width:100%;height:100%;object-fit:cover;display:block;background:#ffffff;">
                                                                                                    <source src="{{ $media['src'] }}">
                                                                                                </video>
                                                                                            </div>
                                                                                        @else
                                                                                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#ffffff;">
                                                                                                <img class="builder-img" src="{{ $media['src'] }}" alt="{{ trim((string) ($media['alt'] ?? 'Product media')) }}" style="width:100%;height:100%;object-fit:cover;display:block;border-radius:0;">
                                                                                            </div>
                                                                                        @endif
                                                                                    @else
                                                                                        <div class="builder-product-media__placeholder"><i class="fas fa-images"></i><div>Add product image or video</div></div>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                        @if(count($productMedia) > 1)
                                                                            <button type="button" class="builder-carousel-arrow is-left" data-carousel-prev style="background:#64748b; color:#ffffff;"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                                                                            <button type="button" class="builder-carousel-arrow is-right" data-carousel-next style="background:#64748b; color:#ffffff;"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                                                                            <div class="builder-carousel-dots" data-carousel-dots>
                                                                                @foreach($productMedia as $si => $unused)
                                                                                    <button type="button" class="builder-carousel-dot{{ $si === $modalActiveMedia ? ' is-active' : '' }}" data-carousel-dot="{{ $si }}" aria-label="Go to media {{ $si + 1 }}"></button>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="product-quick-view-copy">
                                                                    @if($badge !== '')
                                                                        <div class="builder-pricing-badge" data-checkout-badge>{{ $badge }}</div>
                                                                    @endif
                                                                    <h3>{{ $plan !== '' ? $plan : 'Product' }}</h3>
                                                                    <div class="product-quick-view-price">
                                                                        <span class="builder-pricing-price">{{ $priceVal !== '' ? $priceVal : '₱0' }}</span>
                                                                        @if($regularPrice !== '' && $regularPrice !== $priceVal)
                                                                            <span class="builder-pricing-period" style="text-decoration:line-through;opacity:.55;">{{ $regularPrice }}</span>
                                                                        @endif
                                                                        @if($period !== '')
                                                                            <span class="builder-pricing-period" style="opacity:.7;">{{ $period }}</span>
                                                                        @endif
                                                                    </div>
                                                                    @if($subtitle !== '')
                                                                        <div class="builder-pricing-subtitle">{{ $subtitle }}</div>
                                                                    @endif
                                                                    @if($description !== '')
                                                                        <div class="product-quick-view-description">{{ $description }}</div>
                                                                    @endif
                                                                    @if(count($features) > 0)
                                                                        <ul class="product-quick-view-features">
                                                                            @foreach($features as $fi => $feat)
                                                                                @php $featText = trim((string) $feat); if ($featText === '') $featText = 'Feature ' . ($fi + 1); @endphp
                                                                                <li><i class="fas fa-check" aria-hidden="true"></i><span>{{ $featText }}</span></li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </template>
                                                    @endif
                                                @elseif($type === 'checkout_summary' || $type === 'physical_checkout_summary')
                                                    @php
                                                        $isPhysicalCheckoutSummary = $type === 'physical_checkout_summary';
                                                        $stepLayoutForShippingCheck = is_array($step->layout_json ?? null) ? $step->layout_json : [];
                                                        $hideLegacyCheckoutSummary = false;
                                                        $hideDuplicatePhysicalCheckoutSummary = false;
                                                        $physicalCheckoutSummaryIds = [];
                                                        if ($currentStepType === 'checkout' && in_array($effectiveFunnelPurpose, ['physical_product', 'hybrid'], true)) {
                                                            $entryRoots = is_array($stepLayoutForShippingCheck['root'] ?? null)
                                                                ? $stepLayoutForShippingCheck['root']
                                                                : (is_array($stepLayoutForShippingCheck['sections'] ?? null) ? $stepLayoutForShippingCheck['sections'] : []);
                                                            $collectPhysicalCheckoutSummaries = function ($node) use (&$collectPhysicalCheckoutSummaries, &$physicalCheckoutSummaryIds) {
                                                                if (! is_array($node)) {
                                                                    return;
                                                                }
                                                                if (isset($node['type']) && strtolower(trim((string) ($node['type'] ?? ''))) === 'physical_checkout_summary') {
                                                                    $nodeId = trim((string) ($node['id'] ?? ''));
                                                                    if ($nodeId !== '' && ! in_array($nodeId, $physicalCheckoutSummaryIds, true)) {
                                                                        $physicalCheckoutSummaryIds[] = $nodeId;
                                                                    }
                                                                }
                                                                foreach (['elements', 'rows', 'columns', 'sections'] as $childrenKey) {
                                                                    $children = $node[$childrenKey] ?? null;
                                                                    if (! is_array($children)) {
                                                                        continue;
                                                                    }
                                                                    foreach ($children as $child) {
                                                                        $collectPhysicalCheckoutSummaries($child);
                                                                    }
                                                                }
                                                            };
                                                            foreach ($entryRoots as $entryRoot) {
                                                                $collectPhysicalCheckoutSummaries($entryRoot);
                                                            }
                                                            $hideLegacyCheckoutSummary = ! $isPhysicalCheckoutSummary && count($physicalCheckoutSummaryIds) > 0;
                                                            if ($isPhysicalCheckoutSummary && count($physicalCheckoutSummaryIds) > 1) {
                                                                $primaryPhysicalCheckoutSummaryId = $physicalCheckoutSummaryIds[0] ?? null;
                                                                $hideDuplicatePhysicalCheckoutSummary = $primaryPhysicalCheckoutSummaryId && $elId !== $primaryPhysicalCheckoutSummaryId;
                                                            }
                                                        }
                                                        $hasShippingDetailsComponent = false;
                                                        $walkShippingDetails = function ($node) use (&$walkShippingDetails, &$hasShippingDetailsComponent) {
                                                            if ($hasShippingDetailsComponent || ! is_array($node)) {
                                                                return;
                                                            }
                                                            if (isset($node['type']) && strtolower(trim((string) $node['type'])) === 'shipping_details') {
                                                                $hasShippingDetailsComponent = true;
                                                                return;
                                                            }
                                                            foreach (['root', 'sections', 'rows', 'columns', 'elements'] as $childrenKey) {
                                                                $children = $node[$childrenKey] ?? null;
                                                                if (! is_array($children)) {
                                                                    continue;
                                                                }
                                                                foreach ($children as $child) {
                                                                    $walkShippingDetails($child);
                                                                    if ($hasShippingDetailsComponent) {
                                                                        return;
                                                                    }
                                                                }
                                                            }
                                                        };
                                                        $walkShippingDetails($stepLayoutForShippingCheck);
                                                        $summaryHeading = trim((string) ($settings['heading'] ?? ($isPhysicalCheckoutSummary ? 'Review Your Order' : 'Order Summary')));
                                                        $plan = trim((string) ($settings['plan'] ?? ($isPhysicalCheckoutSummary ? 'Selected Product' : 'Starter')));
                                                        $priceVal = trim((string) ($settings['price'] ?? '₱0'));
                                                        $regularPrice = trim((string) ($settings['regularPrice'] ?? ''));
                                                        $period = trim((string) ($settings['period'] ?? ''));
                                                        $subtitle = trim((string) ($settings['subtitle'] ?? ($isPhysicalCheckoutSummary ? 'Selected products, cart items, and delivery details update here before payment.' : '')));
                                                        $badge = trim((string) ($settings['badge'] ?? ($isPhysicalCheckoutSummary ? 'Order Summary' : 'Selected Plan')));
                                                        $features = is_array($settings['features'] ?? null) ? $settings['features'] : [];
                                                        $selectedImage = '';
                                                        $selectedCheckoutPricing = ($currentStepType === 'checkout' && is_array($selectedPricing ?? null)) ? $selectedPricing : null;
                                                        if (is_array($selectedCheckoutPricing)) {
                                                            $selectedPlan = trim((string) ($selectedCheckoutPricing['plan'] ?? ''));
                                                            $selectedPrice = trim((string) ($selectedCheckoutPricing['price'] ?? ''));
                                                            $selectedRegularPrice = trim((string) ($selectedCheckoutPricing['regularPrice'] ?? ''));
                                                            $selectedPeriod = trim((string) ($selectedCheckoutPricing['period'] ?? ''));
                                                            $selectedSubtitle = trim((string) ($selectedCheckoutPricing['subtitle'] ?? ''));
                                                            $selectedBadge = trim((string) ($selectedCheckoutPricing['badge'] ?? ''));
                                                            $selectedImage = trim((string) ($selectedCheckoutPricing['image'] ?? ''));
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
                                                            $features = $isPhysicalCheckoutSummary
                                                                ? ['Product subtotal updates automatically', 'Cart items show here before payment', 'Shipping details are completed below']
                                                                : ['Unlimited steps', 'Custom domains', 'Email support'];
                                                        }
                                                        $summaryTextColor = trim((string) ($rawStyle['color'] ?? ''));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $summaryTextColor)) $summaryTextColor = '';
                                                        $ctaLabel = trim((string) ($settings['ctaLabel'] ?? ($isPhysicalCheckoutSummary ? 'Place Order' : 'Pay Now')));
                                                        if ($ctaLabel === '') $ctaLabel = $isPhysicalCheckoutSummary ? 'Place Order' : 'Pay Now';
                                                        $ctaBg = trim((string) ($settings['ctaBgColor'] ?? '#240E35'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $ctaBg)) $ctaBg = '#240E35';
                                                        $ctaText = trim((string) ($settings['ctaTextColor'] ?? '#ffffff'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $ctaText)) $ctaText = '#ffffff';
                                                        $summaryAmountSource = $priceVal !== '' ? $priceVal : $regularPrice;
                                                        $summaryAmount = 0.0;
                                                        if ($summaryAmountSource !== '') {
                                                            $summaryAmountClean = preg_replace('/[^0-9,.\-]/', '', $summaryAmountSource);
                                                            if (is_string($summaryAmountClean) && $summaryAmountClean !== '') {
                                                                $summaryAmount = (float) str_replace(',', '', $summaryAmountClean);
                                                            }
                                                        }
                                                        $summaryScale = $clampScale($settings['contentScale'] ?? 1);
                                                        $summaryStyle = $contentStyle;
                                                        if ($summaryStyle !== '' && substr($summaryStyle, -1) !== ';') {
                                                            $summaryStyle .= ';';
                                                        }
                                                        $summaryStyle .= 'gap:' . (int) round(($isPhysicalCheckoutSummary ? 12 : 10) * $summaryScale) . 'px;';
                                                        $summaryPad = trim((string) ($rawStyle['padding'] ?? ''));
                                                        if ($summaryPad !== '') {
                                                            $summaryStyle .= 'padding:' . $scalePaddingValue($summaryPad, $summaryScale) . ';';
                                                        }
                                                        $summaryRadius = trim((string) ($rawStyle['borderRadius'] ?? ''));
                                                        if ($summaryRadius !== '') {
                                                            $summaryStyle .= 'border-radius:' . $scalePxValue($summaryRadius, $summaryScale) . ';';
                                                        }
                                                        if ($isPhysicalCheckoutSummary) {
                                                            $physicalThumbSize = max(44, (int) round(64 * $summaryScale));
                                                            $physicalLineThumbSize = max(30, (int) round(40 * $summaryScale));
                                                            $summaryStyle .= '--checkout-physical-gap:' . max(6, (int) round(12 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-pad:' . max(8, (int) round(16 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-label-size:' . max(8, (int) round(11 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-product-cols:' . $physicalThumbSize . 'px minmax(0,1fr);';
                                                            $summaryStyle .= '--checkout-physical-product-gap:' . max(8, (int) round(12 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-product-pad:' . max(8, (int) round(12 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-product-radius:' . max(10, (int) round(16 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-thumb-size:' . $physicalThumbSize . 'px;';
                                                            $summaryStyle .= '--checkout-physical-thumb-radius:' . max(10, (int) round(16 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-thumb-icon:' . max(14, (int) round(22 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-title-size:' . max(12, (int) round(16 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-price-size:' . max(18, (int) round(24 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-rows-gap:' . max(5, (int) round(8 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-rows-pad:' . max(6, (int) round(10 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-row-size:' . max(9, (int) round(12 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-row-strong-size:' . max(10, (int) round(13 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-row-total-size:' . max(14, (int) round(18 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-lines-gap:' . max(5, (int) round(8 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-line-cols:' . $physicalLineThumbSize . 'px 1fr auto;';
                                                            $summaryStyle .= '--checkout-physical-line-gap:' . max(6, (int) round(10 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-line-pad:' . max(5, (int) round(8 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-line-thumb-size:' . $physicalLineThumbSize . 'px;';
                                                            $summaryStyle .= '--checkout-physical-line-thumb-radius:' . max(8, (int) round(12 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-line-thumb-icon:' . max(10, (int) round(14 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-line-title-size:' . max(9, (int) round(12 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-line-sub-size:' . max(8, (int) round(11 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-line-total-size:' . max(9, (int) round(12 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-features-gap:' . max(3, (int) round(5 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-feature-size:' . max(9, (int) round(11 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-cta-pad-y:' . max(7, (int) round(10 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-cta-pad-x:' . max(10, (int) round(14 * $summaryScale)) . 'px;';
                                                            $summaryStyle .= '--checkout-physical-cta-radius:' . max(10, (int) round(12 * $summaryScale)) . 'px;';
                                                        }
                                                        $headingStyle = 'font-size:' . (int) round(11 * $summaryScale) . 'px;font-weight:800;letter-spacing:0.08em;text-transform:uppercase;';
                                                        $titleStyle = 'font-size:' . (int) round(($isPhysicalCheckoutSummary ? 16 : 18) * $summaryScale) . 'px;';
                                                        $priceStyle = 'font-size:' . (int) round(($isPhysicalCheckoutSummary ? 24 : 32) * $summaryScale) . 'px;';
                                                        $periodStyle = 'font-size:' . (int) round(12 * $summaryScale) . 'px;';
                                                        $subtitleStyle = 'font-size:' . (int) round(12 * $summaryScale) . 'px;';
                                                        $featureStyle = 'font-size:' . (int) round(($isPhysicalCheckoutSummary ? 11 : 12) * $summaryScale) . 'px;gap:' . (int) round(($isPhysicalCheckoutSummary ? 5 : 6) * $summaryScale) . 'px;';
                                                        $featureGapStyle = 'gap:' . (int) round(($isPhysicalCheckoutSummary ? 5 : 6) * $summaryScale) . 'px;';
                                                        $ctaStyle = 'font-size:' . (int) round(16 * $summaryScale) . 'px;padding:' . (int) round(($isPhysicalCheckoutSummary ? 10 : 8) * $summaryScale) . 'px ' . (int) round(($isPhysicalCheckoutSummary ? 14 : 12) * $summaryScale) . 'px;';
                                                        $summaryFeaturesJson = json_encode(array_values($features), JSON_UNESCAPED_UNICODE);
                                                        $summaryPricingId = trim((string) ($selectedCheckoutPricing['pricingId'] ?? ''));
                                                        $summarySourceStep = trim((string) ($selectedCheckoutPricing['sourceStepSlug'] ?? ''));
                                                        $physicalSummaryUsesModal = $isPhysicalCheckoutSummary && in_array($effectiveFunnelPurpose, ['physical_product', 'hybrid'], true);
                                                    @endphp
                                                    @if(! $hideLegacyCheckoutSummary && ! $hideDuplicatePhysicalCheckoutSummary)
                                                    <div class="builder-pricing builder-checkout-summary {{ $isPhysicalCheckoutSummary ? 'builder-checkout-summary--physical' : '' }}" data-checkout-summary style="{{ $summaryStyle }}">
                                                        @if($badge !== '')
                                                            <div class="builder-pricing-badge" data-checkout-badge>{{ $badge }}</div>
                                                        @endif
                                                        @if($isPhysicalCheckoutSummary)
                                                            <div class="checkout-physical-head">
                                                                <div class="checkout-physical-label" data-checkout-heading style="{{ $headingStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }}; opacity: 0.7;@endif">{{ $summaryHeading }}</div>
                                                            </div>
                                                            <div class="checkout-physical-product">
                                                                <div class="checkout-physical-thumb" data-checkout-thumb>
                                                                    @if($selectedImage !== '')
                                                                        <img src="{{ $selectedImage }}" alt="{{ $plan !== '' ? $plan : 'Selected product' }}">
                                                                    @else
                                                                        <i class="fas fa-box-open" aria-hidden="true"></i>
                                                                    @endif
                                                                </div>
                                                                <div class="checkout-physical-meta">
                                                                    <div class="builder-pricing-title" data-checkout-plan style="{{ $titleStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }};@endif">{{ $plan !== '' ? $plan : 'Selected product' }}</div>
                                                                    @if($subtitle !== '')
                                                                        <div class="builder-pricing-subtitle" data-checkout-subtitle style="{{ $subtitleStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }}; opacity: 0.7;@endif">{{ $subtitle }}</div>
                                                                    @endif
                                                                    <div class="checkout-physical-price">
                                                                        <span class="builder-pricing-price" data-checkout-price style="{{ $priceStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }};@endif">{{ $priceVal !== '' ? $priceVal : '₱0' }}</span>
                                                                        @if($period !== '')
                                                                            <span class="builder-pricing-period" data-checkout-period style="{{ $periodStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }}; opacity: 0.7;@endif">{{ $period }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="checkout-physical-rows">
                                                                <div class="checkout-physical-row">
                                                                    <span>Items subtotal</span>
                                                                    <strong data-checkout-row-subtotal>{{ $priceVal !== '' ? $priceVal : '₱0' }}</strong>
                                                                </div>
                                                                <div class="checkout-physical-row">
                                                                    <span>Shipping</span>
                                                                    <strong data-checkout-row-shipping>Calculated at checkout</strong>
                                                                </div>
                                                                <div class="checkout-physical-row checkout-physical-row--total">
                                                                    <strong>Order total</strong>
                                                                    <strong data-checkout-row-total>{{ $priceVal !== '' ? $priceVal : '₱0' }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="checkout-cart-lines" data-checkout-cart-lines style="{{ $selectedImage !== '' ? '' : 'display:none;' }}">
                                                                @if($selectedImage !== '')
                                                                    <div class="checkout-cart-line">
                                                                        <div class="checkout-cart-line-thumb"><img src="{{ $selectedImage }}" alt="{{ $plan !== '' ? $plan : 'Selected product' }}"></div>
                                                                        <div class="checkout-cart-line-meta">
                                                                            <div class="checkout-cart-line-title">{{ $plan !== '' ? $plan : 'Selected product' }}</div>
                                                                            <div class="checkout-cart-line-sub">{{ $badge !== '' ? $badge : 'Selected item' }}</div>
                                                                        </div>
                                                                        <div class="checkout-cart-line-total">{{ $priceVal !== '' ? $priceVal : '₱0' }}</div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <ul class="builder-pricing-features" data-checkout-features style="{{ $featureGapStyle }}{{ $selectedImage !== '' ? 'display:none;' : '' }}">
                                                                @foreach($features as $fi => $feat)
                                                                    @php
                                                                        $featText = trim((string) $feat);
                                                                        if ($featText === '') $featText = 'Feature ' . ($fi + 1);
                                                                    @endphp
                                                                    <li style="{{ $featureStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }};@endif"><i class="fas fa-check" aria-hidden="true"></i> {{ $featText }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <div class="builder-pricing-subtitle" data-checkout-heading style="{{ $headingStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }}; opacity: 0.7;@endif">{{ $summaryHeading }}</div>
                                                            <div class="builder-pricing-title" data-checkout-plan style="{{ $titleStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }};@endif">{{ $plan !== '' ? $plan : 'Plan' }}</div>
                                                            <div>
                                                                <span class="builder-pricing-price" data-checkout-price style="{{ $priceStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }};@endif">{{ $priceVal !== '' ? $priceVal : '₱0' }}</span>
                                                                @if($period !== '')
                                                                    <span class="builder-pricing-period" data-checkout-period style="{{ $periodStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }}; opacity: 0.7;@endif">{{ $period }}</span>
                                                                @endif
                                                            </div>
                                                            @if($subtitle !== '')
                                                                <div class="builder-pricing-subtitle" data-checkout-subtitle style="{{ $subtitleStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }}; opacity: 0.7;@endif">{{ $subtitle }}</div>
                                                            @endif
                                                            <ul class="builder-pricing-features" data-checkout-features style="{{ $featureGapStyle }}">
                                                                @foreach($features as $fi => $feat)
                                                                    @php
                                                                        $featText = trim((string) $feat);
                                                                        if ($featText === '') $featText = 'Feature ' . ($fi + 1);
                                                                    @endphp
                                                                    <li style="{{ $featureStyle }}@if($summaryTextColor !== '')color: {{ $summaryTextColor }};@endif"><i class="fas fa-check" aria-hidden="true"></i> {{ $featText }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                        @if($currentStepType === 'checkout')
                                                            @if($isPreview)
                                                                <button type="button" class="builder-pricing-cta" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }}; opacity:0.7;cursor:not-allowed;" disabled>{{ $ctaLabel }}</button>
                                                            @else
                                                                <form method="POST" action="{{ $checkoutActionUrl }}" data-checkout-summary-form style="margin:0;">
                                                                    @csrf
                                                                    <input type="hidden" name="amount" value="{{ $summaryAmount > 0 ? $summaryAmount : (float) ($step->price ?? 0) }}">
                                                                    <input type="hidden" name="website" value="">
                                                                    <input type="hidden" name="checkout_pricing_id" value="{{ $summaryPricingId }}">
                                                                    <input type="hidden" name="checkout_pricing_source_step" value="{{ $summarySourceStep }}">
                                                                    <input type="hidden" name="checkout_pricing_plan" value="{{ $plan }}">
                                                                    <input type="hidden" name="checkout_pricing_price" value="{{ $priceVal }}">
                                                                    <input type="hidden" name="checkout_pricing_regular_price" value="{{ $regularPrice }}">
                                                                    <input type="hidden" name="checkout_pricing_period" value="{{ $period }}">
                                                                    <input type="hidden" name="checkout_pricing_subtitle" value="{{ $subtitle }}">
                                                                    <input type="hidden" name="checkout_pricing_badge" value="{{ $badge }}">
                                                                    <input type="hidden" name="checkout_pricing_image" value="{{ $selectedImage }}">
                                                                    <input type="hidden" name="checkout_pricing_features" value="{{ $summaryFeaturesJson }}">
                                                                    <input type="hidden" name="checkout_cart_items" value="">
                                                                    <input type="hidden" name="coupon_code" value="{{ strtoupper(trim((string) old('coupon_code', ''))) }}">
                                                                    @if($physicalSummaryUsesModal)
                                                                        <button type="button" class="builder-pricing-cta" data-open-shipping-modal style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }};">{{ $ctaLabel }}</button>
                                                                        <div class="checkout-shipping-modal-backdrop" data-shipping-modal aria-hidden="true">
                                                                            <div class="checkout-shipping-modal" role="dialog" aria-modal="true" aria-label="Shipping details">
                                                                                <div class="checkout-shipping-modal-head">
                                                                                    <div>
                                                                                        <h3 class="checkout-shipping-modal-title">Shipping Details</h3>
                                                                                        <p class="checkout-shipping-modal-copy">Enter your delivery and contact information before continuing to payment.</p>
                                                                                    </div>
                                                                                    <button type="button" class="checkout-shipping-modal-close" data-close-shipping-modal aria-label="Close"><i class="fas fa-times"></i></button>
                                                                                </div>
                                                                                <div class="checkout-shipping-modal-grid" data-checkout-customer-form>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_first_name_{{ $elId }}">First name</label>
                                                                                        <input id="shipping_first_name_{{ $elId }}" class="builder-form-input" type="text" name="first_name" value="{{ old('first_name') }}" required placeholder="First name">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_last_name_{{ $elId }}">Last name</label>
                                                                                        <input id="shipping_last_name_{{ $elId }}" class="builder-form-input" type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="Last name">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_email_{{ $elId }}">Email</label>
                                                                                        <input id="shipping_email_{{ $elId }}" class="builder-form-input" type="email" name="email" value="{{ old('email') }}" required placeholder="Email address">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_phone_{{ $elId }}">Phone number</label>
                                                                                        <input id="shipping_phone_{{ $elId }}" class="builder-form-input" type="tel" name="phone_number" value="{{ old('phone_number') }}" required pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric" placeholder="09XXXXXXXXX">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_province_{{ $elId }}">Province</label>
                                                                                        <input id="shipping_province_{{ $elId }}" class="builder-form-input" type="text" name="province" value="{{ old('province') }}" required placeholder="Province">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_city_{{ $elId }}">City / Municipality</label>
                                                                                        <input id="shipping_city_{{ $elId }}" class="builder-form-input" type="text" name="city_municipality" value="{{ old('city_municipality') }}" required placeholder="City / Municipality">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_barangay_{{ $elId }}">Barangay</label>
                                                                                        <input id="shipping_barangay_{{ $elId }}" class="builder-form-input" type="text" name="barangay" value="{{ old('barangay') }}" required placeholder="Barangay">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field is-full">
                                                                                        <label for="shipping_street_{{ $elId }}">Street address</label>
                                                                                        <input id="shipping_street_{{ $elId }}" class="builder-form-input" type="text" name="street" value="{{ old('street') }}" required placeholder="House no., street, building">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_postal_{{ $elId }}">Postal code</label>
                                                                                        <input id="shipping_postal_{{ $elId }}" class="builder-form-input" type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="Postal code">
                                                                                    </div>
                                                                                    <div class="checkout-shipping-modal-field">
                                                                                        <label for="shipping_notes_{{ $elId }}">Order notes</label>
                                                                                        <input id="shipping_notes_{{ $elId }}" class="builder-form-input" type="text" name="notes" value="{{ old('notes') }}" placeholder="Optional notes for delivery">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="checkout-shipping-modal-actions">
                                                                                    <button type="button" class="checkout-shipping-modal-cancel" data-close-shipping-modal>Cancel</button>
                                                                                    <button type="button" class="builder-pricing-cta checkout-shipping-modal-submit" data-open-coupon-prompt style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }};">Continue</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @php
                                                                            // Only show AO-created (tenant-owned) coupons in the checkout coupon picker.
                                                                            $checkoutCouponOptions = collect($availableCoupons ?? [])
                                                                                ->filter(fn ($coupon) => ($coupon->scope_type ?? null) === \App\Models\Coupon::SCOPE_TENANT)
                                                                                ->map(function ($coupon) {
                                                                                    return [
                                                                                        'code' => (string) $coupon->code,
                                                                                        'title' => (string) ($coupon->title ?: 'Special coupon'),
                                                                                        'discount_type' => (string) $coupon->discount_type,
                                                                                        'discount_value' => (float) $coupon->discount_value,
                                                                                    ];
                                                                                })
                                                                                ->values()
                                                                                ->all();
                                                                        @endphp
                                                                        <div class="coupon-prompt-modal-backdrop" data-coupon-prompt aria-hidden="true">
                                                                            <div class="coupon-prompt-modal" role="dialog" aria-modal="true" aria-label="Coupon code">
                                                                                <div class="coupon-prompt-head">
                                                                                    <div>
                                                                                        <h3 class="coupon-prompt-title">Do you have a coupon code?</h3>
                                                                                        <p class="coupon-prompt-copy">Paste a code if you have one. We will apply the discount before payment.</p>
                                                                                    </div>
                                                                                    <button type="button" class="coupon-prompt-close" data-close-coupon-prompt aria-label="Close"><i class="fas fa-times"></i></button>
                                                                                </div>
                                                                                <div class="coupon-prompt-field">
                                                                                    <label for="coupon_prompt_{{ $elId }}">Coupon code</label>
                                                                                    <input id="coupon_prompt_{{ $elId }}" type="text" data-coupon-prompt-input maxlength="40" placeholder="ENTER COUPON CODE" value="{{ strtoupper(trim((string) old('coupon_code', ''))) }}">
                                                                                </div>
                                                                                @if(count($checkoutCouponOptions) > 0)
                                                                                <div class="coupon-prompt-available" data-coupon-available>
                                                                                    <div class="coupon-prompt-available-title">Available coupons</div>
                                                                                    <div class="coupon-prompt-available-list">
                                                                                        @foreach($checkoutCouponOptions as $couponOpt)
                                                                                            @php
                                                                                                $optCode = strtoupper(trim((string) ($couponOpt['code'] ?? '')));
                                                                                                $optTitle = trim((string) ($couponOpt['title'] ?? ''));
                                                                                                $optType = trim((string) ($couponOpt['discount_type'] ?? ''));
                                                                                                $optVal = (float) ($couponOpt['discount_value'] ?? 0);
                                                                                                $optLabel = $optType === 'percent'
                                                                                                    ? ($optVal > 0 ? rtrim(rtrim(number_format($optVal, 2), '0'), '.') . '% off' : '')
                                                                                                    : ($optVal > 0 ? ('PHP ' . number_format($optVal, 2) . ' off') : '');
                                                                                            @endphp
                                                                                            @if($optCode !== '')
                                                                                            <div class="coupon-prompt-available-item" data-coupon-item data-coupon-code="{{ $optCode }}">
                                                                                                <div style="min-width:0;">
                                                                                                    <div class="coupon-prompt-available-code">{{ $optCode }}</div>
                                                                                                    <div class="coupon-prompt-available-meta">
                                                                                                        {{ $optTitle !== '' ? $optTitle : 'Coupon' }}{!! $optLabel !== '' ? ' · <strong>'.$optLabel.'</strong>' : '' !!}
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="coupon-prompt-available-actions">
                                                                                                    <button type="button" class="coupon-prompt-available-btn primary" data-coupon-use="{{ $optCode }}">Use</button>
                                                                                                </div>
                                                                                            </div>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                                @endif
                                                                                <div class="coupon-prompt-preview">
                                                                                    <div class="coupon-prompt-preview-row">
                                                                                        <span>Order subtotal</span>
                                                                                        <strong data-coupon-subtotal>PHP 0.00</strong>
                                                                                    </div>
                                                                                    <div class="coupon-prompt-preview-row">
                                                                                        <span>Estimated discount</span>
                                                                                        <strong data-coupon-discount>PHP 0.00</strong>
                                                                                    </div>
                                                                                    <div class="coupon-prompt-preview-row coupon-prompt-preview-row--total">
                                                                                        <strong>Total after coupon</strong>
                                                                                        <strong data-coupon-total>PHP 0.00</strong>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="coupon-prompt-message" data-coupon-prompt-message></div>
                                                                                <div class="coupon-prompt-actions">
                                                                                    <button type="button" class="coupon-prompt-skip" data-coupon-skip>Skip for now</button>
                                                                                    <button type="button" class="builder-pricing-cta" data-coupon-apply style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }};" data-coupon-options='@json($checkoutCouponOptions)'>Apply & Continue</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <button type="submit" class="builder-pricing-cta" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }};">{{ $ctaLabel }}</button>
                                                                    @endif
                                                                </form>
                                                            @endif
                                                        @else
                                                            <button type="button" class="builder-pricing-cta" style="{{ $ctaStyle }}background: {{ $ctaBg }}; color: {{ $ctaText }}; opacity:0.7;cursor:not-allowed;" disabled>{{ $ctaLabel }}</button>
                                                        @endif
                                                    </div>
                                                    @endif
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
                                                        <form method="POST" action="{{ $optInActionUrl }}" style="{{ $formInlineStyle }}">
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
                                                    @elseif($step->type === 'checkout' && !$isPreview)
                                                        <form data-checkout-customer-form onsubmit="return false;" style="{{ $formInlineStyle }}">
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
                                                                        } elseif (str_contains($labelKey, 'province')) {
                                                                            $ft = 'province';
                                                                        } elseif (str_contains($labelKey, 'city') || str_contains($labelKey, 'municipality')) {
                                                                            $ft = 'city_municipality';
                                                                        } elseif (str_contains($labelKey, 'barangay')) {
                                                                            $ft = 'barangay';
                                                                        } elseif (str_contains($labelKey, 'street') || str_contains($labelKey, 'address')) {
                                                                            $ft = 'street';
                                                                        } elseif (str_contains($labelKey, 'postal') || str_contains($labelKey, 'zip')) {
                                                                            $ft = 'postal_code';
                                                                        } elseif (str_contains($labelKey, 'note')) {
                                                                            $ft = 'notes';
                                                                        }
                                                                    }
                                                                    $nm = in_array($ft, ['name', 'first_name', 'last_name', 'email', 'phone_number', 'phone', 'province', 'city_municipality', 'barangay', 'street', 'postal_code', 'notes'], true) ? $ft : 'custom_' . $loop->index;
                                                                    $req = (bool) ($f['required'] ?? false);
                                                                    $inputType = $ft === 'email' ? 'email' : ($ft === 'phone_number' ? 'tel' : 'text');
                                                                    $pat = $ft === 'phone_number' ? 'pattern="^09\\d{9}$" maxlength="11" minlength="11" inputmode="numeric"' : '';
                                                                    $ph = trim((string) ($f['placeholder'] ?? ''));
                                                                    if ($ph === '') {
                                                                        if ($ft === 'phone_number') {
                                                                            $ph = '09XXXXXXXXX';
                                                                        } elseif ($ft === 'email') {
                                                                            $ph = 'Email address';
                                                                        } else {
                                                                            $ph = $lbl;
                                                                        }
                                                                    }
                                                                @endphp
                                                                <label style="display:block;margin-bottom:4px;color:{{ $formLabelColor }};text-align:left;">{{ $lbl }}</label>
                                                                <input class="builder-form-input" type="{{ $inputType }}" name="{{ $nm }}" {{ $req ? 'required' : '' }} {!! $pat !!} placeholder="{{ $ph }}" style="--fb-placeholder-color:{{ $formPlaceholderColor }};width:100%;padding:8px;border:1px solid #E6E1EF;border-radius:8px;margin-bottom:8px;box-sizing:border-box;text-align:left;">
                                                            @endforeach
                                                            <div style="display:flex;justify-content:{{ $formButtonJustify }};">
                                                                <button type="button" class="builder-form-btn" style="margin-top:2px;background:{{ $formButtonBgColor }};color:{{ $formButtonTextColor }};font-weight:{{ $formButtonFontWeight }};font-style:{{ $formButtonFontStyle }};border-radius:8px;padding:8px 12px;border:1px solid {{ $formButtonBgColor }};line-height:1;opacity:.75;cursor:default;">{{ $content !== '' ? $content : 'Fill out details above' }}</button>
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
                                                @elseif($type === 'shipping_details')
                                                    @php
                                                        $hideLegacyShippingBlock = false;
                                                        if ($currentStepType === 'checkout' && in_array($effectiveFunnelPurpose, ['physical_product', 'hybrid'], true)) {
                                                            $stepLayoutForPhysicalSummaryCheck = is_array($step->layout_json ?? null) ? $step->layout_json : [];
                                                            $hasPhysicalCheckoutSummary = false;
                                                            $walkPhysicalCheckoutSummary = function ($node) use (&$walkPhysicalCheckoutSummary, &$hasPhysicalCheckoutSummary) {
                                                                if ($hasPhysicalCheckoutSummary || ! is_array($node)) {
                                                                    return;
                                                                }
                                                                if (isset($node['type']) && strtolower(trim((string) $node['type'])) === 'physical_checkout_summary') {
                                                                    $hasPhysicalCheckoutSummary = true;
                                                                    return;
                                                                }
                                                                foreach (['root', 'sections', 'rows', 'columns', 'elements'] as $childrenKey) {
                                                                    $children = $node[$childrenKey] ?? null;
                                                                    if (! is_array($children)) {
                                                                        continue;
                                                                    }
                                                                    foreach ($children as $child) {
                                                                        $walkPhysicalCheckoutSummary($child);
                                                                        if ($hasPhysicalCheckoutSummary) {
                                                                            return;
                                                                        }
                                                                    }
                                                                }
                                                            };
                                                            $walkPhysicalCheckoutSummary($stepLayoutForPhysicalSummaryCheck);
                                                            $hideLegacyShippingBlock = $hasPhysicalCheckoutSummary;
                                                        }
                                                        $shippingHeading = trim((string) ($settings['heading'] ?? 'Shipping Details'));
                                                        $shippingSubtitle = trim((string) ($settings['subtitle'] ?? 'Enter your delivery and contact information before placing the order.'));
                                                        $shippingLabelColor = trim((string) ($settings['labelColor'] ?? '#240E35'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $shippingLabelColor)) $shippingLabelColor = '#240E35';
                                                        $shippingPlaceholderColor = trim((string) ($settings['placeholderColor'] ?? '#94a3b8'));
                                                        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $shippingPlaceholderColor)) $shippingPlaceholderColor = '#94a3b8';
                                                        $shippingFields = is_array($settings['fields'] ?? null) ? $settings['fields'] : [];
                                                        if (count($shippingFields) === 0) {
                                                            $shippingFields = [
                                                                ['type' => 'first_name', 'label' => 'First name', 'placeholder' => 'First name', 'required' => true],
                                                                ['type' => 'last_name', 'label' => 'Last name', 'placeholder' => 'Last name', 'required' => true],
                                                                ['type' => 'email', 'label' => 'Email', 'placeholder' => 'Email address', 'required' => true],
                                                                ['type' => 'phone_number', 'label' => 'Phone number', 'placeholder' => '09XXXXXXXXX', 'required' => true],
                                                                ['type' => 'province', 'label' => 'Province', 'placeholder' => 'Province', 'required' => true],
                                                                ['type' => 'city_municipality', 'label' => 'City / Municipality', 'placeholder' => 'City / Municipality', 'required' => true],
                                                                ['type' => 'barangay', 'label' => 'Barangay', 'placeholder' => 'Barangay', 'required' => true],
                                                                ['type' => 'street', 'label' => 'Street address', 'placeholder' => 'House no., street, building', 'required' => true],
                                                                ['type' => 'postal_code', 'label' => 'Postal code', 'placeholder' => 'Postal code', 'required' => false],
                                                                ['type' => 'notes', 'label' => 'Order notes', 'placeholder' => 'Optional notes for delivery', 'required' => false],
                                                            ];
                                                        }
                                                    @endphp
                                                    @if(! $hideLegacyShippingBlock)
                                                        <div class="builder-form" data-checkout-customer-form style="{{ $style }}">
                                                            @if($shippingHeading !== '')
                                                                <div style="font-size:20px;font-weight:800;color:#240E35;margin-bottom:6px;">{{ $shippingHeading }}</div>
                                                            @endif
                                                            @if($shippingSubtitle !== '')
                                                                <div style="font-size:12px;line-height:1.5;color:#64748b;margin-bottom:10px;">{{ $shippingSubtitle }}</div>
                                                            @endif
                                                            @foreach($shippingFields as $f)
                                                                @php
                                                                    $ft = strtolower(trim((string) ($f['type'] ?? 'text')));
                                                                    $lbl = trim((string) ($f['label'] ?? '')) !== '' ? trim((string) $f['label']) : 'Field';
                                                                    $nm = in_array($ft, ['name', 'first_name', 'last_name', 'email', 'phone_number', 'phone', 'province', 'city_municipality', 'barangay', 'street', 'postal_code', 'notes'], true) ? $ft : 'custom_' . $loop->index;
                                                                    $req = (bool) ($f['required'] ?? false);
                                                                    $inputType = $ft === 'email' ? 'email' : ($ft === 'phone_number' ? 'tel' : 'text');
                                                                    $pat = $ft === 'phone_number' ? 'pattern="^09\d{9}$" maxlength="11" minlength="11" inputmode="numeric"' : '';
                                                                    $ph = trim((string) ($f['placeholder'] ?? ''));
                                                                    if ($ph === '') {
                                                                        $ph = $ft === 'phone_number' ? '09XXXXXXXXX' : $lbl;
                                                                    }
                                                                @endphp
                                                                <label style="display:block;margin-bottom:4px;color:{{ $shippingLabelColor }};text-align:left;">{{ $lbl }}</label>
                                                                <input class="builder-form-input" type="{{ $inputType }}" name="{{ $nm }}" {{ $req ? 'required' : '' }} {!! $pat !!} value="{{ old($nm) }}" placeholder="{{ $ph }}" style="--fb-placeholder-color:{{ $shippingPlaceholderColor }};width:100%;padding:8px;border:1px solid #E6E1EF;border-radius:8px;margin-bottom:8px;box-sizing:border-box;text-align:left;">
                                                            @endforeach
                                                        </div>
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
                @include('funnels.portal._step-actions', ['funnel' => $funnel, 'step' => $step, 'nextStep' => $nextStep, 'isPreview' => $isPreview, 'isTemplateTest' => $isTemplateTest, 'optInActionUrl' => $optInActionUrl, 'checkoutActionUrl' => $checkoutActionUrl, 'offerActionUrl' => $offerActionUrl, 'restartStepUrl' => $restartStepUrl, 'nextStepUrl' => $nextStep ? $stepRoute($nextStep) : null])
            @endif
        </div>
        @else
        <div class="preview-iframe-shell">
            <iframe id="previewDeviceFrame" title="Preview device viewport" scrolling="auto" loading="lazy"></iframe>
        </div>
        @endif
</div>
<div class="product-quick-view-backdrop" id="productQuickViewBackdrop" aria-hidden="true">
    <div class="product-quick-view-modal" role="dialog" aria-modal="true" aria-label="Product details">
        <button type="button" class="product-quick-view-close" id="productQuickViewClose" aria-label="Close product details"><i class="fas fa-times" aria-hidden="true"></i></button>
        <div id="productQuickViewBody"></div>
    </div>
</div>
<button type="button" class="portal-cart-fab" id="portalCartFab" aria-label="Open cart">
    <i class="fas fa-cart-shopping" aria-hidden="true"></i>
    <span class="portal-cart-count" id="portalCartCount">0</span>
</button>
<div class="portal-cart-backdrop" id="portalCartBackdrop" aria-hidden="true"></div>
<aside class="portal-cart-drawer" id="portalCartDrawer" aria-hidden="true">
    <div class="portal-cart-head">
        <h3>Your Cart</h3>
        <button type="button" class="portal-cart-close" id="portalCartClose" aria-label="Close cart"><i class="fas fa-times" aria-hidden="true"></i></button>
    </div>
    <div class="portal-cart-items" id="portalCartItems"></div>
    <div class="portal-cart-foot">
        <div class="portal-cart-total"><span>Total</span><span id="portalCartTotal">₱0</span></div>
        <div class="portal-cart-actions">
            @if($cartCheckoutUrl)
                <a href="{{ $cartCheckoutUrl }}" data-base-href="{{ $cartCheckoutUrl }}" class="portal-cart-btn primary" id="portalCartCheckoutBtn">Go to Checkout</a>
            @endif
            <button type="button" class="portal-cart-btn secondary" id="portalCartClearBtn">Clear Cart</button>
        </div>
    </div>
</aside>
<script>
    (function(){
        var pricingStorageKey="funnel_pricing_selection:"+@json((string) ($funnel->slug ?? ''));
        var currentStepSlug=@json((string) ($step->slug ?? ''));
        var currentStepType=@json((string) ($currentStepType ?? 'custom'));
        var hasServerSelectedPricing={{ is_array($selectedPricing ?? null) ? 'true' : 'false' }};
        var isFirstStep={{ ($isFirstStep ?? false) ? 'true' : 'false' }};
        var shouldClearPortalCart={{ session('clear_portal_cart') ? 'true' : 'false' }};
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
                image:String(card.getAttribute("data-pricing-image")||"").trim(),
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
                url.searchParams.set("offer_image",String(selection.image||"").trim());
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
                    +"&offer_image="+encodeURIComponent(String(selection.image||"").trim())
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
        var quickViewBackdrop=document.getElementById("productQuickViewBackdrop");
        var quickViewBody=document.getElementById("productQuickViewBody");
        var quickViewClose=document.getElementById("productQuickViewClose");
        function closeProductQuickView(){
            if(!quickViewBackdrop)return;
            quickViewBackdrop.classList.remove("is-open");
            quickViewBackdrop.setAttribute("aria-hidden","true");
            if(quickViewBody)quickViewBody.innerHTML="";
            document.body.style.overflow="";
        }
        function openProductQuickView(templateId){
            if(!quickViewBackdrop||!quickViewBody)return;
            var tpl=document.getElementById(templateId);
            if(!tpl)return;
            quickViewBody.innerHTML=tpl.innerHTML;
            quickViewBackdrop.classList.add("is-open");
            quickViewBackdrop.setAttribute("aria-hidden","false");
            document.body.style.overflow="hidden";
            quickViewBody.querySelectorAll("[data-carousel]").forEach(function(car){
                var track=car.querySelector("[data-carousel-track]");
                if(!track)return;
                var slides=track.children;
                var total=slides.length||1;
                var index=parseInt(car.getAttribute("data-active")||"0",10);
                if(isNaN(index)||index<0||index>=total)index=0;
                var dotsWrap=car.querySelector("[data-carousel-dots]");
                var dots=dotsWrap?dotsWrap.querySelectorAll("[data-carousel-dot]"):[];
                function paintModalCarousel(){
                    track.style.transform="translateX(" + (-index*100) + "%)";
                    if(dots&&dots.length){
                        dots.forEach(function(dot,di){
                            if(di===index)dot.classList.add("is-active");
                            else dot.classList.remove("is-active");
                        });
                    }
                }
                var prev=car.querySelector("[data-carousel-prev]");
                var next=car.querySelector("[data-carousel-next]");
                if(prev)prev.addEventListener("click",function(e){e.preventDefault();index=(index-1+total)%total;paintModalCarousel();});
                if(next)next.addEventListener("click",function(e){e.preventDefault();index=(index+1)%total;paintModalCarousel();});
                if(dots&&dots.length){
                    dots.forEach(function(dot){
                        dot.addEventListener("click",function(e){
                            e.preventDefault();
                            var target=parseInt(dot.getAttribute("data-carousel-dot")||"0",10);
                            if(isNaN(target)||target<0||target>=total)return;
                            index=target;
                            paintModalCarousel();
                        });
                    });
                }
                paintModalCarousel();
            });
        }
        if(quickViewClose)quickViewClose.addEventListener("click",closeProductQuickView);
        if(quickViewBackdrop){
            quickViewBackdrop.addEventListener("click",function(e){
                if(e.target===quickViewBackdrop)closeProductQuickView();
            });
        }
        document.addEventListener("keydown",function(e){
            if(e.key==="Escape"&&quickViewBackdrop&&quickViewBackdrop.classList.contains("is-open")){
                closeProductQuickView();
            }
        });
        var portalLoadingOverlay=document.getElementById("portalLoadingOverlay");
        function portalShowLoading(){
            if(!portalLoadingOverlay)return;
            portalLoadingOverlay.classList.add("is-active");
            portalLoadingOverlay.setAttribute("aria-hidden","false");
        }
        function portalHideLoading(){
            if(!portalLoadingOverlay)return;
            portalLoadingOverlay.classList.remove("is-active");
            portalLoadingOverlay.setAttribute("aria-hidden","true");
        }
        window.addEventListener("pageshow",portalHideLoading);
        window.addEventListener("load",portalHideLoading);
        document.addEventListener("click",function(e){
            if({{ ($isPreview ?? false) ? 'true' : 'false' }})return;
            var link=e.target&&e.target.closest?e.target.closest("a[href]"):null;
            if(!link)return;
            if(link.hasAttribute("download"))return;
            if(String(link.getAttribute("target")||"").toLowerCase()==="_blank")return;
            if(link.closest("[data-product-modal-target]"))return;
            if(link.closest("[data-open-shipping-modal]"))return;
            var rawHref=String(link.getAttribute("href")||"").trim();
            if(rawHref===""||rawHref==="#")return;
            try{
                var url=new URL(link.href,window.location.href);
                if(url.origin!==window.location.origin)return;
                if(url.hash&&url.pathname===window.location.pathname&&url.search===window.location.search)return;
            }catch(_err){
                return;
            }
            portalShowLoading();
        });
        document.addEventListener("submit",function(e){
            if({{ ($isPreview ?? false) ? 'true' : 'false' }})return;
            var form=e.target;
            if(!form||!form.action)return;
            portalShowLoading();
        });
        document.addEventListener("click",function(e){
            var trigger=e.target&&e.target.closest?e.target.closest("[data-product-modal-target]"):null;
            if(!trigger)return;
            e.preventDefault();
            var templateId=trigger.getAttribute("data-product-modal-target")||"";
            if(templateId!=="")openProductQuickView(templateId);
        });
        var cartStorageKey="funnel_product_cart:"+@json((string) ($funnel->slug ?? ''));
        var portalCartFab=document.getElementById("portalCartFab");
        var portalCartCount=document.getElementById("portalCartCount");
        var portalCartDrawer=document.getElementById("portalCartDrawer");
        var portalCartBackdrop=document.getElementById("portalCartBackdrop");
        var portalCartItems=document.getElementById("portalCartItems");
        var portalCartTotal=document.getElementById("portalCartTotal");
        var portalCartClose=document.getElementById("portalCartClose");
        var portalCartClear=document.getElementById("portalCartClearBtn");
        var portalCartCheckout=document.getElementById("portalCartCheckoutBtn");
        var cartButtons=document.querySelectorAll("[data-product-add-to-cart]");
        var portalCartHistoryOpen=false;
        function parseCartMoney(raw){
            var s=String(raw||"").trim().replace(/[^0-9,.\-]/g,"").replace(/,/g,"");
            if(!s)return 0;
            var n=parseFloat(s);
            return isNaN(n)||!isFinite(n)?0:n;
        }
        function formatCartMoney(amount){
            var n=Number(amount||0);
            if(!isFinite(n))n=0;
            return "₱"+n.toLocaleString(undefined,{minimumFractionDigits:n % 1===0 ? 0 : 2,maximumFractionDigits:2});
        }
        function readPortalCart(){
            try{
                var raw=window.localStorage.getItem(cartStorageKey);
                var parsed=raw?JSON.parse(raw):[];
                return Array.isArray(parsed)?parsed:[];
            }catch(_err){
                return [];
            }
        }
        function writePortalCart(items){
            try{ window.localStorage.setItem(cartStorageKey,JSON.stringify(Array.isArray(items)?items:[])); }catch(_err){}
        }
        function clearPortalCartStorage(){
            try{
                if(window.localStorage)window.localStorage.removeItem(cartStorageKey);
            }catch(_err){}
            try{
                if(window.sessionStorage)window.sessionStorage.removeItem(pricingStorageKey);
            }catch(_err){}
        }
        function productStockLimitFromValue(value){
            var raw=String(value==null?"":value).trim();
            if(raw==="")return null;
            var limit=parseInt(raw,10);
            if(isNaN(limit))return null;
            return Math.max(0,limit);
        }
        function productStockLimitForButton(btn){
            if(!btn)return null;
            return productStockLimitFromValue(btn.getAttribute("data-product-stock-remaining"));
        }
        function cartQuantityForProduct(items,id){
            return (Array.isArray(items)?items:[]).reduce(function(sum,item){
                return String(item&&item.id||"")===String(id||"").trim() ? sum + Math.max(1,Number(item.quantity||1)) : sum;
            },0);
        }
        function normalizeCartStock(items){
            var list=Array.isArray(items)?items.slice():[];
            var changed=false;
            list=list.reduce(function(out,item){
                if(!item||typeof item!=="object")return out;
                var next=Object.assign({},item);
                var limit=productStockLimitFromValue(next.stockRemaining);
                var qty=Math.max(1,Number(next.quantity||1));
                if(limit!==null){
                    next.stockRemaining=limit;
                    if(limit<=0){
                        changed=true;
                        return out;
                    }
                    if(qty>limit){
                        qty=limit;
                        changed=true;
                    }
                }
                next.quantity=qty;
                out.push(next);
                return out;
            },[]);
            return { items:list, changed:changed };
        }
        function summarizeCartItems(items){
            var list=Array.isArray(items)?items:[];
            var quantity=0;
            var total=0;
            var regularTotal=0;
            list.forEach(function(item){
                var qty=Math.max(1,Number(item.quantity||1));
                quantity+=qty;
                total+=parseCartMoney(item.price||item.regularPrice||0)*qty;
                regularTotal+=parseCartMoney(item.regularPrice||item.price||0)*qty;
            });
            return { quantity:quantity, total:total, regularTotal:regularTotal };
        }
        function buildCartCheckoutSelection(itemOrItems){
            var items=Array.isArray(itemOrItems)?itemOrItems:(itemOrItems?[itemOrItems]:[]);
            if(!items.length)return null;
            var summary=summarizeCartItems(items);
            if(items.length===1 && summary.quantity<=1){
                var item=items[0];
                return {
                    pricingId:String(item.id||"").trim(),
                    sourceStepSlug:String(item.stepSlug||"").trim(),
                    plan:String(item.name||"").trim(),
                    price:String(item.price||"").trim(),
                    regularPrice:String(item.regularPrice||"").trim(),
                    period:String(item.period||"").trim(),
                    subtitle:"",
                    badge:String(item.badge||"").trim(),
                    image:String(item.image||"").trim(),
                    features:[]
                };
            }
            return {
                pricingId:"cart",
                sourceStepSlug:currentStepSlug,
                plan:"Cart Order",
                price:formatCartMoney(summary.total),
                regularPrice:summary.regularTotal>summary.total?formatCartMoney(summary.regularTotal):"",
                period:"",
                subtitle:summary.quantity+" item"+(summary.quantity===1?"":"s")+" ready for checkout",
                badge:"Cart",
                image:String((items[0]&&items[0].image)||"").trim(),
                features:items.map(function(item){
                    var qty=Math.max(1,Number(item.quantity||1));
                    return String(item.name||"Product")+" x"+qty;
                })
            };
        }
        function serializeCheckoutItems(itemOrItems){
            var items=Array.isArray(itemOrItems)?itemOrItems:(itemOrItems?[itemOrItems]:[]);
            return items.map(function(item){
                if(!item||typeof item!=="object")return null;
                var name=String(item.name||"").trim();
                var price=String(item.price||"").trim();
                var regularPrice=String(item.regularPrice||"").trim();
                var period=String(item.period||"").trim();
                var badge=String(item.badge||"").trim();
                var image=String(item.image||"").trim();
                var quantity=Math.max(1,parseInt(item.quantity||"1",10)||1);
                if(!name && !price && !regularPrice && !badge && !image)return null;
                return {
                    id:String(item.id||"").trim(),
                    stepSlug:String(item.stepSlug||"").trim(),
                    name:name||"Product",
                    price:price,
                    regularPrice:regularPrice,
                    period:period,
                    badge:badge,
                    image:image,
                    quantity:quantity
                };
            }).filter(function(item){ return !!item; });
        }
        function serializeSelectionAsCheckoutItems(selection){
            if(!selection||typeof selection!=="object")return [];
            var name=String(selection.plan||"").trim();
            var price=String(selection.price||"").trim();
            var regularPrice=String(selection.regularPrice||"").trim();
            var period=String(selection.period||"").trim();
            var badge=String(selection.badge||"").trim();
            var image=String(selection.image||"").trim();
            if(!name && !price && !regularPrice && !badge && !image)return [];
            return serializeCheckoutItems([{
                id:String(selection.pricingId||"").trim(),
                stepSlug:String(selection.sourceStepSlug||"").trim(),
                name:name||"Selected item",
                price:price,
                regularPrice:regularPrice,
                period:period,
                badge:badge,
                image:image,
                quantity:1
            }]);
        }
        function renderCheckoutSummaryFromCart(){
            if(currentStepType!=="checkout")return;
            var summaryNode=document.querySelector("[data-checkout-summary]");
            if(!summaryNode)return;
            var items=readPortalCart();
            if(!items.length)return;
            var summary=summarizeCartItems(items);
            var selection=buildCartCheckoutSelection(items);
            var heading=summaryNode.querySelector("[data-checkout-heading]");
            var badge=summaryNode.querySelector("[data-checkout-badge]");
            var plan=summaryNode.querySelector("[data-checkout-plan]");
            var price=summaryNode.querySelector("[data-checkout-price]");
            var period=summaryNode.querySelector("[data-checkout-period]");
            var subtitle=summaryNode.querySelector("[data-checkout-subtitle]");
            var features=summaryNode.querySelector("[data-checkout-features]");
            var lines=summaryNode.querySelector("[data-checkout-cart-lines]");
            var thumb=summaryNode.querySelector("[data-checkout-thumb]");
            var rowSubtotal=summaryNode.querySelector("[data-checkout-row-subtotal]");
            var rowShipping=summaryNode.querySelector("[data-checkout-row-shipping]");
            var rowTotal=summaryNode.querySelector("[data-checkout-row-total]");
            if(heading)heading.textContent="Cart Summary";
            if(badge)badge.textContent="Cart";
            if(plan)plan.textContent=summary.quantity+" item"+(summary.quantity===1?"":"s");
            if(price)price.textContent=formatCartMoney(summary.total);
            if(period)period.textContent="";
            if(subtitle)subtitle.textContent="Review the products in your cart before paying.";
            if(rowSubtotal)rowSubtotal.textContent=formatCartMoney(summary.total);
            if(rowShipping)rowShipping.textContent="Calculated at checkout";
            if(rowTotal)rowTotal.textContent=formatCartMoney(summary.total);
            if(features)features.style.display="none";
            if(thumb){
                var firstItem=items[0]||null;
                if(firstItem && String(firstItem.image||"").trim()!==""){
                    thumb.innerHTML='<img src="'+escapeHtml(String(firstItem.image||""))+'" alt="'+escapeHtml(String(firstItem.name||"Product"))+'">';
                }else{
                    thumb.innerHTML='<i class="fas fa-box-open" aria-hidden="true"></i>';
                }
            }
            if(lines){
                lines.style.display="";
                lines.innerHTML="";
                items.forEach(function(item){
                    var qty=Math.max(1,Number(item.quantity||1));
                    var unit=parseCartMoney(item.price||item.regularPrice||0);
                    var row=document.createElement("div");
                    row.className="checkout-cart-line";
                    var thumb=document.createElement("div");
                    thumb.className="checkout-cart-line-thumb";
                    if(String(item.image||"").trim()!==""){
                        var img=document.createElement("img");
                        img.src=String(item.image||"");
                        img.alt=String(item.name||"Product");
                        thumb.appendChild(img);
                    }else{
                        thumb.innerHTML='<i class="fas fa-box-open"></i>';
                    }
                    var meta=document.createElement("div");
                    meta.className="checkout-cart-line-meta";
                    meta.innerHTML='<div class="checkout-cart-line-title"></div><div class="checkout-cart-line-sub"></div>';
                    meta.querySelector(".checkout-cart-line-title").textContent=String(item.name||"Product");
                    meta.querySelector(".checkout-cart-line-sub").textContent="Qty: "+qty+(String(item.badge||"").trim()!==""?" • "+String(item.badge||"").trim():"");
                    var totalNode=document.createElement("div");
                    totalNode.className="checkout-cart-line-total";
                    totalNode.textContent=formatCartMoney(unit*qty);
                    row.appendChild(thumb);
                    row.appendChild(meta);
                    row.appendChild(totalNode);
                    lines.appendChild(row);
                });
            }
            var form=summaryNode.querySelector("[data-checkout-summary-form]");
            if(form && selection){
                var amountInput=form.querySelector('input[name="amount"]');
                if(amountInput)amountInput.value=String(summary.total);
                var map={
                    checkout_pricing_id:selection.pricingId,
                    checkout_pricing_source_step:selection.sourceStepSlug,
                    checkout_pricing_plan:selection.plan,
                    checkout_pricing_price:selection.price,
                    checkout_pricing_regular_price:selection.regularPrice,
                    checkout_pricing_period:selection.period,
                    checkout_pricing_subtitle:selection.subtitle,
                    checkout_pricing_badge:selection.badge,
                    checkout_pricing_image:String(selection.image||"").trim(),
                    checkout_pricing_features:JSON.stringify(selection.features||[]),
                    checkout_cart_items:JSON.stringify(serializeCheckoutItems(items))
                };
                Object.keys(map).forEach(function(name){
                    var input=form.querySelector('input[name="'+name+'"]');
                    if(input)input.value=map[name];
                });
            }
            if(typeof scheduleAbsoluteLayoutSync==="function"){
                scheduleAbsoluteLayoutSync();
            }
            if(typeof window.__fbSchedulePreviewScale==="function"){
                window.__fbSchedulePreviewScale();
            }else if(typeof window.__fbSchedulePublishedScale==="function"){
                window.__fbSchedulePublishedScale();
            }
        }
        function updateCartButtonsState(animateId){
            var items=readPortalCart();
            cartButtons.forEach(function(btn){
                var id=String(btn.getAttribute("data-product-id")||"").trim();
                var stockLimit=productStockLimitForButton(btn);
                var cartQty=cartQuantityForProduct(items,id);
                var inCart=items.some(function(item){ return String(item.id||"")===id && Math.max(0,Number(item.quantity||0))>0; });
                var icon=btn.querySelector("i");
                var outOfStock=stockLimit!==null && stockLimit<=0;
                var stockMaxed=!outOfStock && stockLimit!==null && cartQty>=stockLimit;
                btn.classList.toggle("is-in-cart",inCart);
                btn.disabled=outOfStock || stockMaxed;
                btn.setAttribute("title",outOfStock?"Out of stock":(stockMaxed?"Stock limit reached":(inCart?"Added to cart":"Add to cart")));
                btn.setAttribute("aria-label",outOfStock?"Out of stock":(stockMaxed?"Stock limit reached":(inCart?"Added to cart":"Add to cart")));
                if(icon){
                    icon.className=(inCart || stockMaxed)?"fas fa-check":"fas fa-cart-shopping";
                }
                if(animateId && animateId===id){
                    btn.style.animation="cartPop .26s ease";
                    setTimeout(function(){ btn.style.animation=""; },280);
                }
            });
        }
        function animateAddToCart(sourceBtn){
            if(!sourceBtn || !portalCartFab || !portalCartFab.classList.contains("is-visible"))return;
            var startRect=sourceBtn.getBoundingClientRect();
            var endRect=portalCartFab.getBoundingClientRect();
            if(!startRect || !endRect)return;
            var fly=document.createElement("div");
            fly.className="portal-cart-fly";
            var startX=startRect.left + (startRect.width / 2) - 11;
            var startY=startRect.top + (startRect.height / 2) - 11;
            var endX=endRect.left + (endRect.width / 2) - 11;
            var endY=endRect.top + (endRect.height / 2) - 11;
            fly.style.left=startX+"px";
            fly.style.top=startY+"px";
            document.body.appendChild(fly);
            var dx=endX-startX;
            var dy=endY-startY;
            var anim=fly.animate([
                { transform:"translate(0px,0px) scale(1)", opacity:0.95 },
                { transform:"translate("+(dx*0.52)+"px,"+(dy*0.42-34)+"px) scale(0.92)", opacity:0.92, offset:0.55 },
                { transform:"translate("+dx+"px,"+dy+"px) scale(0.42)", opacity:0.05 }
            ],{
                duration:780,
                easing:"cubic-bezier(.2,.8,.2,1)",
                fill:"forwards"
            });
            anim.onfinish=function(){
                if(fly && fly.parentNode)fly.parentNode.removeChild(fly);
                portalCartFab.style.animation="cartPop .28s ease";
                setTimeout(function(){ if(portalCartFab)portalCartFab.style.animation=""; },300);
            };
        }
        function openPortalCart(){
            if(!portalCartDrawer||!portalCartBackdrop)return;
            portalCartDrawer.classList.add("is-open");
            portalCartBackdrop.classList.add("is-open");
            portalCartDrawer.setAttribute("aria-hidden","false");
            portalCartBackdrop.setAttribute("aria-hidden","false");
            document.body.style.overflow="hidden";
            if(!portalCartHistoryOpen && window.history && typeof window.history.pushState==="function"){
                try{
                    window.history.pushState(Object.assign({}, window.history.state||{}, { portalCartOpen:true }),"",window.location.href);
                    portalCartHistoryOpen=true;
                }catch(_err){}
            }
        }
        function closePortalCart(fromHistory){
            if(!portalCartDrawer||!portalCartBackdrop)return;
            portalCartDrawer.classList.remove("is-open");
            portalCartBackdrop.classList.remove("is-open");
            portalCartDrawer.setAttribute("aria-hidden","true");
            portalCartBackdrop.setAttribute("aria-hidden","true");
            document.body.style.overflow="";
            if(portalCartHistoryOpen){
                if(fromHistory){
                    portalCartHistoryOpen=false;
                }else if(window.history && typeof window.history.back==="function"){
                    portalCartHistoryOpen=false;
                    window.history.back();
                }
            }
        }
        function renderPortalCart(){
            if(!portalCartFab||!portalCartItems||!portalCartTotal)return;
            var normalizedCart=normalizeCartStock(readPortalCart());
            var items=normalizedCart.items;
            if(normalizedCart.changed){
                writePortalCart(items);
            }
            var quantity=items.reduce(function(sum,item){ return sum + Math.max(1,Number(item.quantity||1)); },0);
            var shouldShowCartFab=quantity>0 && currentStepType!=="checkout";
            if(shouldShowCartFab){
                portalCartFab.classList.add("is-visible");
                portalCartCount.textContent=String(quantity);
            }else{
                portalCartFab.classList.remove("is-visible");
                portalCartCount.textContent=String(quantity>0?quantity:0);
                closePortalCart();
            }
            portalCartItems.innerHTML="";
            if(!items.length){
                portalCartItems.innerHTML='<div class="portal-cart-empty">Your cart is empty.</div>';
                portalCartTotal.textContent=formatCartMoney(0);
                if(portalCartCheckout)portalCartCheckout.setAttribute("aria-disabled","true");
                updateCartButtonsState();
                renderCheckoutSummaryFromCart();
                return;
            }
            var total=0;
            items.forEach(function(item,idx){
                var qty=Math.max(1,Number(item.quantity||1));
                var unit=parseCartMoney(item.price||item.regularPrice||0);
                total+=unit*qty;
                var row=document.createElement("div");
                row.className="portal-cart-item";
                var thumb=document.createElement("div");
                thumb.className="portal-cart-thumb";
                if(String(item.image||"").trim()!==""){
                    var img=document.createElement("img");
                    img.src=String(item.image||"");
                    img.alt=String(item.name||"Product");
                    thumb.appendChild(img);
                }else{
                    thumb.innerHTML='<i class="fas fa-box-open"></i>';
                }
                var meta=document.createElement("div");
                meta.className="portal-cart-meta";
                meta.innerHTML='<div class="portal-cart-title"></div><div class="portal-cart-price"></div><div class="portal-cart-sub"></div><div class="portal-cart-qty"></div>';
                meta.querySelector(".portal-cart-title").textContent=String(item.name||"Product");
                meta.querySelector(".portal-cart-price").textContent=String(item.price||item.regularPrice||"₱0");
                meta.querySelector(".portal-cart-sub").textContent=String(item.period||item.badge||"");
                meta.querySelector(".portal-cart-qty").innerHTML='<button type="button" class="portal-cart-qty-btn" data-cart-decrease="'+idx+'" aria-label="Decrease quantity"><i class="fas fa-minus"></i></button><span class="portal-cart-qty-num">'+qty+'</span><button type="button" class="portal-cart-qty-btn" data-cart-increase="'+idx+'" aria-label="Increase quantity"><i class="fas fa-plus"></i></button>';
                var remove=document.createElement("button");
                remove.type="button";
                remove.className="portal-cart-remove";
                remove.innerHTML='<i class="fas fa-trash"></i>';
                remove.setAttribute("data-cart-remove",String(idx));
                row.appendChild(thumb);
                row.appendChild(meta);
                row.appendChild(remove);
                portalCartItems.appendChild(row);
            });
            portalCartTotal.textContent=formatCartMoney(total);
            if(portalCartCheckout){
                var baseHref=portalCartCheckout.getAttribute("data-base-href")||portalCartCheckout.getAttribute("href")||"";
                if(baseHref){
                    portalCartCheckout.setAttribute("href",baseHref);
                    portalCartCheckout.removeAttribute("aria-disabled");
                }
            }
            updateCartButtonsState();
            renderCheckoutSummaryFromCart();
        }
        if(portalCartFab)portalCartFab.addEventListener("click",function(){ renderPortalCart(); openPortalCart(); });
        if(portalCartClose)portalCartClose.addEventListener("click",closePortalCart);
        if(portalCartBackdrop)portalCartBackdrop.addEventListener("click",closePortalCart);
        window.addEventListener("popstate",function(){
            if(portalCartDrawer && portalCartDrawer.classList.contains("is-open")){
                closePortalCart(true);
            }
        });
        if(portalCartClear)portalCartClear.addEventListener("click",function(){
            writePortalCart([]);
            renderPortalCart();
        });
        if(portalCartItems){
            portalCartItems.addEventListener("click",function(e){
                var remove=e.target&&e.target.closest?e.target.closest("[data-cart-remove]"):null;
                var items=readPortalCart();
                if(remove){
                    var idx=parseInt(remove.getAttribute("data-cart-remove")||"-1",10);
                    if(isNaN(idx)||idx<0)return;
                    items.splice(idx,1);
                    writePortalCart(items);
                    renderPortalCart();
                    return;
                }
                var increase=e.target&&e.target.closest?e.target.closest("[data-cart-increase]"):null;
                if(increase){
                    var incIdx=parseInt(increase.getAttribute("data-cart-increase")||"-1",10);
                    if(isNaN(incIdx)||incIdx<0||!items[incIdx])return;
                    var incLimit=productStockLimitFromValue(items[incIdx].stockRemaining);
                    var nextQty=Math.max(1,Number(items[incIdx].quantity||1))+1;
                    if(incLimit!==null && nextQty>incLimit)return;
                    items[incIdx].quantity=nextQty;
                    writePortalCart(items);
                    renderPortalCart();
                    return;
                }
                var decrease=e.target&&e.target.closest?e.target.closest("[data-cart-decrease]"):null;
                if(decrease){
                    var decIdx=parseInt(decrease.getAttribute("data-cart-decrease")||"-1",10);
                    if(isNaN(decIdx)||decIdx<0||!items[decIdx])return;
                    items[decIdx].quantity=Math.max(1,Number(items[decIdx].quantity||1))-1;
                    if(items[decIdx].quantity<=0){
                        items.splice(decIdx,1);
                    }
                    writePortalCart(items);
                    renderPortalCart();
                }
            });
        }
        cartButtons.forEach(function(btn){
            btn.addEventListener("click",function(e){
                e.preventDefault();
                var id=String(btn.getAttribute("data-product-id")||"").trim();
                if(id==="")return;
                var items=readPortalCart();
                var existing=items.find(function(item){ return String(item.id||"")===id; });
                var stockLimit=productStockLimitForButton(btn);
                if(stockLimit!==null && stockLimit<=0)return;
                if(existing){
                    var nextQty=Math.max(1,Number(existing.quantity||1))+1;
                    if(stockLimit!==null && nextQty>stockLimit)return;
                    existing.quantity=nextQty;
                }else{
                    items.push({
                        id:id,
                        name:String(btn.getAttribute("data-product-name")||"Product").trim(),
                        price:String(btn.getAttribute("data-product-price")||"").trim(),
                        regularPrice:String(btn.getAttribute("data-product-regular-price")||"").trim(),
                        period:String(btn.getAttribute("data-product-period")||"").trim(),
                        badge:String(btn.getAttribute("data-product-badge")||"").trim(),
                        image:String(btn.getAttribute("data-product-image")||"").trim(),
                        stepSlug:String(btn.getAttribute("data-product-step")||"").trim(),
                        stockRemaining:stockLimit,
                        quantity:1
                    });
                }
                writePortalCart(items);
                renderPortalCart();
                updateCartButtonsState(id);
                animateAddToCart(btn);
            });
        });
        if(shouldClearPortalCart){
            clearPortalCartStorage();
        }
        renderPortalCart();
        updateCartButtonsState();
        Array.from(document.querySelectorAll("[data-review-toggle]")||[]).forEach(function(btn){
            btn.addEventListener("click",function(){
                var parent=btn.parentNode;
                if(!parent)return;
                var list=parent.querySelector("[data-review-list]");
                if(!list)return;
                var extras=Array.from(list.querySelectorAll("[data-review-extra]")||[]);
                if(!extras.length)return;
                var expanded=btn.getAttribute("data-expanded")==="1";
                extras.forEach(function(card){
                    if(expanded){
                        card.classList.add("builder-review-hidden");
                        card.setAttribute("hidden","hidden");
                    }else{
                        card.classList.remove("builder-review-hidden");
                        card.removeAttribute("hidden");
                    }
                });
                expanded=!expanded;
                btn.setAttribute("data-expanded",expanded?"1":"0");
                btn.textContent=expanded
                    ? String(btn.getAttribute("data-collapse-label")||"Show fewer reviews")
                    : String(btn.getAttribute("data-expand-label")||"Show all reviews");
            });
        });
        Array.from(document.querySelectorAll('.builder-review-stars[aria-label="Rating"]')||[]).forEach(function(group){
            var labels=Array.from(group.querySelectorAll("label")||[]);
            if(!labels.length)return;
            group.classList.add("is-interactive");
            function syncReviewStars(selected){
                labels.forEach(function(label,idx){
                    var input=label.querySelector('input[type="radio"]');
                    var value=input?parseInt(input.value||"0",10):0;
                    var glyph=label.querySelector(".builder-review-star-glyph");
                    if(!glyph){
                        glyph=document.createElement("span");
                        glyph.className="builder-review-star-glyph";
                        glyph.innerHTML="&#9733;";
                        label.innerHTML="";
                        if(input)label.appendChild(input);
                        label.appendChild(glyph);
                    }
                    label.classList.toggle("is-active", value >= selected);
                });
            }
            labels.forEach(function(label){
                var input=label.querySelector('input[type="radio"]');
                if(!input)return;
                var selected=parseInt(input.checked?input.value:"0",10)||0;
                var glyph=label.querySelector(".builder-review-star-glyph");
                if(!glyph){
                    glyph=document.createElement("span");
                    glyph.className="builder-review-star-glyph";
                    glyph.innerHTML="&#9733;";
                    label.innerHTML="";
                    label.appendChild(input);
                    label.appendChild(glyph);
                }
                label.addEventListener("click",function(){
                    input.checked=true;
                    syncReviewStars(parseInt(input.value||"0",10)||0);
                });
                if(selected>0)syncReviewStars(selected);
            });
            if(!labels.some(function(label){ var input=label.querySelector('input[type="radio"]'); return !!(input&&input.checked); })){
                var fallbackInput=labels[labels.length-1]?labels[labels.length-1].querySelector('input[type="radio"]'):null;
                if(fallbackInput){
                    fallbackInput.checked=true;
                    syncReviewStars(parseInt(fallbackInput.value||"5",10)||5);
                }
            }
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
            if(currentStepType==="checkout"){
                var cartItems=readPortalCart();
                if(cartItems.length){
                    var cartSummary=summarizeCartItems(cartItems);
                    var cartSelection=buildCartCheckoutSelection(cartItems);
                    if(cartSelection){
                        var cartMap={
                            checkout_pricing_id:String(cartSelection.pricingId||"").trim(),
                            checkout_pricing_source_step:String(cartSelection.sourceStepSlug||"").trim(),
                            checkout_pricing_plan:String(cartSelection.plan||"").trim(),
                            checkout_pricing_price:String(cartSelection.price||"").trim(),
                            checkout_pricing_regular_price:String(cartSelection.regularPrice||"").trim(),
                            checkout_pricing_period:String(cartSelection.period||"").trim(),
                            checkout_pricing_subtitle:String(cartSelection.subtitle||"").trim(),
                            checkout_pricing_badge:String(cartSelection.badge||"").trim(),
                            checkout_pricing_image:String(cartSelection.image||"").trim(),
                            checkout_pricing_features:JSON.stringify(Array.isArray(cartSelection.features)?cartSelection.features:[]),
                            checkout_cart_items:JSON.stringify(serializeCheckoutItems(cartItems))
                        };
                        Object.keys(cartMap).forEach(function(name){
                            var input=form.querySelector('input[name="'+name+'"]');
                            if(input)input.value=cartMap[name];
                        });
                        var cartAmountInput=form.querySelector('input[name="amount"]');
                        if(cartAmountInput && cartSummary.total>0){
                            cartAmountInput.value=String(cartSummary.total);
                        }
                        return;
                    }
                }
            }
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
                checkout_pricing_image:String(selection.image||"").trim(),
                checkout_pricing_features:JSON.stringify(Array.isArray(selection.features)?selection.features:[]),
                checkout_cart_items:JSON.stringify(serializeSelectionAsCheckoutItems(selection))
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
        function collectCheckoutCustomerInputs(summaryForm){
            if(!summaryForm)return [];
            var forms=Array.from(summaryForm.querySelectorAll("[data-checkout-customer-form]")||[]);
            var inputs=[];
            forms.forEach(function(form){
                Array.from(form.querySelectorAll("input, textarea, select")||[]).forEach(function(field){
                    if(!field || !field.name || field.disabled)return;
                    inputs.push(field);
                });
            });
            return inputs;
        }
        function syncCheckoutCustomerForm(summaryForm){
            if(!summaryForm)return {ok:true};
            var physicalPurpose={{ in_array($effectiveFunnelPurpose, ['physical_product','hybrid'], true) ? 'true' : 'false' }};
            var customerInputs=collectCheckoutCustomerInputs(summaryForm);
            if(!customerInputs.length)return {ok:true};
            var requiredNames={
                email:true,
                phone_number:physicalPurpose,
                province:physicalPurpose,
                city_municipality:physicalPurpose,
                barangay:physicalPurpose,
                street:physicalPurpose
            };
            var hasName=false;
            customerInputs.forEach(function(field){
                var fieldName=String(field.name||"").trim();
                var fieldValue=String(field.value||"").trim();
                if(fieldName==="name"||fieldName==="first_name"||fieldName==="last_name"){
                    if(fieldValue!=="")hasName=true;
                }
            });
            for(var i=0;i<customerInputs.length;i++){
                var field=customerInputs[i];
                var fieldName=String(field.name||"").trim();
                if(!fieldName)continue;
                var fieldValue=String(field.value||"").trim();
                if(typeof field.reportValidity==="function" && !field.reportValidity()){
                    field.focus();
                    return {ok:false};
                }
                if(requiredNames[fieldName] && fieldValue===""){
                    if(typeof field.reportValidity==="function")field.reportValidity();
                    field.focus();
                    return {ok:false};
                }
                var hidden=summaryForm.querySelector('input[name="'+fieldName+'"]');
                if(!hidden){
                    hidden=document.createElement("input");
                    hidden.type="hidden";
                    hidden.name=fieldName;
                    summaryForm.appendChild(hidden);
                }
                hidden.value=fieldValue;
            }
            if(physicalPurpose && !hasName){
                var nameField=customerInputs.find(function(field){
                    var nm=String(field.name||"").trim();
                    return nm==="name"||nm==="first_name"||nm==="last_name";
                });
                if(nameField){
                    if(typeof nameField.reportValidity==="function")nameField.reportValidity();
                    nameField.focus();
                }
                return {ok:false};
            }
            return {ok:true};
        }
        document.addEventListener("submit",function(e){
            var form=e.target;
            if(!form||form.tagName!=="FORM")return;
            var method=String(form.getAttribute("method")||"").toLowerCase();
            if(method!=="post")return;
            if(form.hasAttribute("data-checkout-summary-form")){
                syncCheckoutPricingForm(form);
                var syncedCustomer=syncCheckoutCustomerForm(form);
                if(!syncedCustomer.ok){
                    e.preventDefault();
                    if(typeof portalHideLoading==="function")portalHideLoading();
                    return;
                }
            }
            if(form.getAttribute("data-submitting")==="1"){
                e.preventDefault();
                if(typeof portalHideLoading==="function")portalHideLoading();
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
            if(action.indexOf("/checkout")>=0 || action.indexOf("/offer")>=0){
                syncCheckoutPricingForm(form);
            }
        },true);
        function setShippingModalOpen(backdrop,open){
            if(!backdrop)return;
            backdrop.classList.toggle("is-open",!!open);
            backdrop.setAttribute("aria-hidden",open?"false":"true");
            if(open){
                document.body.style.overflow="hidden";
                var firstField=backdrop.querySelector("input, textarea, select, button");
                if(firstField && typeof firstField.focus==="function"){
                    setTimeout(function(){ try{ firstField.focus(); }catch(_e){} },20);
                }
            }else{
                document.body.style.overflow="";
            }
        }
        function bindShippingModals(){
            document.querySelectorAll("[data-checkout-summary-form]").forEach(function(form){
                if(form.getAttribute("data-shipping-modal-bound")==="1")return;
                form.setAttribute("data-shipping-modal-bound","1");
                var openBtn=form.querySelector("[data-open-shipping-modal]");
                var continueBtn=form.querySelector("[data-open-coupon-prompt]");
                var backdrop=form.querySelector("[data-shipping-modal]");
                if(!openBtn||!backdrop)return;
                openBtn.addEventListener("click",function(e){
                    e.preventDefault();
                    setShippingModalOpen(backdrop,true);
                });
                if(continueBtn){
                    continueBtn.addEventListener("click",function(e){
                        e.preventDefault();
                        syncCheckoutPricingForm(form);
                        var syncedCustomer=syncCheckoutCustomerForm(form);
                        if(!syncedCustomer.ok){
                            if(typeof portalHideLoading==="function")portalHideLoading();
                            return;
                        }
                        setShippingModalOpen(backdrop,false);
                        if(typeof window.__openCouponPrompt==="function"){
                            window.__openCouponPrompt(form);
                            var prompt=form.querySelector("[data-coupon-prompt]");
                            window.setTimeout(function(){
                                if(prompt && !prompt.classList.contains("is-open") && form.getAttribute("data-coupon-prompt-open")!=="1"){
                                    form.setAttribute("data-coupon-confirmed","1");
                                    if(typeof portalShowLoading==="function")portalShowLoading();
                                    try{
                                        HTMLFormElement.prototype.submit.call(form);
                                    }catch(_e){
                                        form.submit();
                                    }
                                }
                            },350);
                        }else{
                            form.setAttribute("data-coupon-confirmed","1");
                            if(typeof portalShowLoading==="function")portalShowLoading();
                            try{
                                HTMLFormElement.prototype.submit.call(form);
                            }catch(_e){
                                form.submit();
                            }
                        }
                    });
                }
                backdrop.addEventListener("click",function(e){
                    if(e.target===backdrop){
                        setShippingModalOpen(backdrop,false);
                    }
                });
                backdrop.querySelectorAll("[data-close-shipping-modal]").forEach(function(btn){
                    btn.addEventListener("click",function(e){
                        e.preventDefault();
                        setShippingModalOpen(backdrop,false);
                    });
                });
                form.addEventListener("submit",function(){
                    setShippingModalOpen(backdrop,false);
                });
            });
        }
        bindShippingModals();
        document.addEventListener("click",function(e){
            var openBtn=e.target&&e.target.closest?e.target.closest("[data-open-shipping-modal]"):null;
            if(openBtn){
                e.preventDefault();
                var form=openBtn.closest("form");
                var backdrop=form?form.querySelector("[data-shipping-modal]"):null;
                setShippingModalOpen(backdrop,true);
                return;
            }
            var closeBtn=e.target&&e.target.closest?e.target.closest("[data-close-shipping-modal]"):null;
            if(closeBtn){
                e.preventDefault();
                var closeBackdrop=closeBtn.closest("[data-shipping-modal]");
                setShippingModalOpen(closeBackdrop,false);
                return;
            }
            var backdrop=e.target&&e.target.closest?e.target.closest("[data-shipping-modal]"):null;
            if(backdrop && e.target===backdrop){
                e.preventDefault();
                setShippingModalOpen(backdrop,false);
            }
        },true);
        function estimateRightShadowBleed(node){
            if(!node||!window.getComputedStyle)return 0;
            try{
                var shadow=String(window.getComputedStyle(node).boxShadow||"").trim();
                if(!shadow||shadow==="none")return 0;
                var nums=(shadow.match(/-?\d+(?:\.\d+)?px/g)||[]).map(function(v){ return parseFloat(v)||0; });
                if(!nums.length)return 0;
                var offsetX=nums[0]||0;
                var blur=nums.length>2?(nums[2]||0):0;
                var spread=nums.length>3?(nums[3]||0):0;
                return Math.max(0,Math.ceil(Math.max(0,offsetX)+(blur*0.5)+Math.max(0,spread)));
            }catch(_e){}
            return 0;
        }
        function syncAbsoluteColumnHeights(){
            var absNodes=Array.from(document.querySelectorAll(".builder-el")).filter(function(node){
                if(!node||!node.classList||!node.classList.contains("builder-el"))return false;
                try{
                    return String(window.getComputedStyle(node).position||"")==="absolute";
                }catch(_e){}
                return false;
            });
            var hostMetrics=new Map();
            absNodes.forEach(function(node){
                var host=node.offsetParent||node.parentElement;
                if(!host)return;
                var hostWidth=Math.max(
                    host.clientWidth||0,
                    host.getBoundingClientRect?Math.ceil(host.getBoundingClientRect().width||0):0,
                    parseFloat(host.style.width||"0")||0
                );
                try{
                    var nodeStyle=window.getComputedStyle(node);
                    var currentLeft=parseFloat(nodeStyle.left||node.style.left||"0")||0;
                    var primary=node.firstElementChild||node;
                    var visualWidth=Math.max(
                        node.offsetWidth||0,
                        node.scrollWidth||0,
                        primary&&primary.scrollWidth?primary.scrollWidth:0,
                        primary&&primary.getBoundingClientRect?Math.ceil(primary.getBoundingClientRect().width||0):0
                    );
                    if(hostWidth>0){
                        var maxLeft=Math.max(0,hostWidth-visualWidth);
                        var clampedLeft=Math.min(Math.max(0,currentLeft),maxLeft);
                        if(Math.abs(clampedLeft-currentLeft)>0.5){
                            node.style.left=Math.round(clampedLeft)+"px";
                        }
                    }
                    var mb=parseFloat(nodeStyle.marginBottom||"0")||0;
                    var mr=parseFloat(nodeStyle.marginRight||"0")||0;
                    var info=hostMetrics.get(host)||{maxBottom:0,maxRight:0};
                    var nodeBottom=(node.offsetTop||0)+Math.max(node.offsetHeight||0,node.scrollHeight||0)+mb+12;
                    var nodeRight=(node.offsetLeft||0)+Math.max(node.offsetWidth||0,node.scrollWidth||0)+mr+12;
                    if(nodeBottom>info.maxBottom)info.maxBottom=nodeBottom;
                    if(nodeRight>info.maxRight)info.maxRight=nodeRight;
                    hostMetrics.set(host,info);
                }catch(_e){}
            });
            hostMetrics.forEach(function(info,host){
                if(!host)return;
                if(info.maxBottom>0){
                    if(host.classList&&host.classList.contains("builder-col-inner")){
                        var col=host.closest(".builder-col");
                        if(col){
                            var currentColMinHeight=parseFloat(col.style.minHeight||"0")||0;
                            if(info.maxBottom>currentColMinHeight)col.style.minHeight=Math.ceil(info.maxBottom)+"px";
                        }
                    }else if(host.classList&&host.classList.contains("builder-section-inner")){
                        var section=host.closest(".builder-section");
                        if(section){
                            var currentSectionMinHeight=parseFloat(section.style.minHeight||"0")||0;
                            if(info.maxBottom>currentSectionMinHeight)section.style.minHeight=Math.ceil(info.maxBottom)+"px";
                        }
                    }
                }
                if(info.maxRight>0){
                    if(host.closest&&host.closest(".builder-section--freeform")){
                        var freeformSection=host.closest(".builder-section--freeform");
                        var currentSectionWidth=parseFloat(freeformSection.style.width||"0")||0;
                        if(currentSectionWidth<=0)freeformSection.style.width=Math.ceil(info.maxRight)+"px";
                    }
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
        function scaleSectionElementCarriers(){}
        scheduleAbsoluteLayoutSync();
        window.addEventListener("resize",function(){scheduleAbsoluteLayoutSync();scaleSectionElementCarriers();});
        window.addEventListener("load",function(){scheduleAbsoluteLayoutSync();scaleSectionElementCarriers();});
        if(document.fonts&&document.fonts.ready&&typeof document.fonts.ready.then==="function"){
            document.fonts.ready.then(function(){scheduleAbsoluteLayoutSync();}).catch(function(){});
        }
        Array.from(document.images||[]).forEach(function(img){
            if(!img||img.complete)return;
            img.addEventListener("load",function(){scheduleAbsoluteLayoutSync();scaleSectionElementCarriers();},{once:true});
            img.addEventListener("error",function(){scheduleAbsoluteLayoutSync();scaleSectionElementCarriers();},{once:true});
        });
        setTimeout(function(){scaleSectionElementCarriers();},200);
        var isPreview={{ ($isPreview ?? false) ? 'true' : 'false' }};
        var editorCanvasWidth={{ (int) (($editorCanvasWidth ?? 0) + (($isPreview ?? false) ? $previewFreeformRightInset : 0)) }};
        var previewDeviceWidths={desktop:null,tablet:768,mobile:375};
        var previewDevice="desktop";
        if(isPreview){
            var hasDeviceParam=false;
            try{
                var allowed={desktop:1,tablet:1,mobile:1};
                var sp=new URLSearchParams(window.location.search||"");
                var q=sp.get("preview_device")||sp.get("previewDevice")||sp.get("device")||"";
                q=String(q||"").toLowerCase();
                if(allowed[q]){previewDevice=q;hasDeviceParam=true;}
            }catch(_e){}
            if(!hasDeviceParam){
                try{
                    var stored=localStorage.getItem("fbPreviewDevice");
                    stored=String(stored||"").toLowerCase();
                    if(stored==="tablet"||stored==="mobile"||stored==="desktop")previewDevice=stored;
                }catch(_e){}
            }
            if(!hasDeviceParam){
                previewDevice="desktop";
                try{localStorage.setItem("fbPreviewDevice","desktop");}catch(_e){}
            }

            document.body.setAttribute("data-preview-device", previewDevice);
            // Outer-mode only: keep the iframe synced with the selected device.
            var maybeIframe=document.getElementById("previewDeviceFrame");
            if(maybeIframe){
                var deviceWidthMap={desktop:"100%",tablet:"768px",mobile:"375px"};
                maybeIframe.style.width=deviceWidthMap[previewDevice]||"100%";
                var frameParams=new URLSearchParams(window.location.search||"");
                frameParams.set("preview_iframe","1");
                frameParams.set("preview_device",previewDevice);
                maybeIframe.src=window.location.pathname+"?"+frameParams.toString();
            }

            var deviceBtns=Array.from(document.querySelectorAll("[data-preview-device]")||[]);
            var setActiveDeviceUI=function(){
                deviceBtns.forEach(function(btn){
                    var d=String(btn.getAttribute("data-preview-device")||"desktop");
                    if(d===previewDevice)btn.classList.add("is-active");
                    else btn.classList.remove("is-active");
                });
            };
            if(deviceBtns.length){
                setActiveDeviceUI();
                deviceBtns.forEach(function(btn){
                    btn.addEventListener("click",function(){
                        var d=String(btn.getAttribute("data-preview-device")||"desktop").toLowerCase();
                        if(!(d==="desktop"||d==="tablet"||d==="mobile"))d="desktop";
                        previewDevice=d;
                        try{localStorage.setItem("fbPreviewDevice",previewDevice);}catch(_e){}
                        document.body.setAttribute("data-preview-device", previewDevice);
                        setActiveDeviceUI();
                        var iframe=document.getElementById("previewDeviceFrame");
                        if(iframe){
                            var deviceWidthMap={desktop:"100%",tablet:"768px",mobile:"375px"};
                            iframe.style.width=deviceWidthMap[previewDevice]||"100%";
                            var frameParams=new URLSearchParams(window.location.search||"");
                            frameParams.set("preview_iframe","1");
                            frameParams.set("preview_device",previewDevice);
                            iframe.src=window.location.pathname+"?"+frameParams.toString();
                        }
                        if(typeof window.__fbSchedulePreviewScale==="function"){
                            window.__fbSchedulePreviewScale();
                        }
                        setTimeout(function(){scaleSectionElementCarriers();},100);
                        if(typeof window.__fbSyncResponsiveMenus==="function"){
                            window.__fbSyncResponsiveMenus();
                        }
                    });
                });
            }
        }
        function initResponsiveMenus(){
            var toggles=Array.from(document.querySelectorAll("[data-menu-toggle]")||[]);
            var panels=Array.from(document.querySelectorAll("[data-menu-panel]")||[]);
            var closeButtons=Array.from(document.querySelectorAll("[data-menu-close]")||[]);
            if(!toggles.length||!panels.length)return;
            var closeAll=function(){
                toggles.forEach(function(toggle){
                    var key=String(toggle.getAttribute("data-menu-toggle")||"").trim();
                    var panel=document.querySelector('[data-menu-panel="'+key+'"]');
                    if(!panel)return;
                    panel.classList.remove("is-open");
                    panel.setAttribute("aria-hidden","true");
                    toggle.setAttribute("aria-expanded","false");
                });
            };
            toggles.forEach(function(toggle){
                if(toggle.getAttribute("data-menu-bound")==="1")return;
                toggle.setAttribute("data-menu-bound","1");
                toggle.addEventListener("click",function(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                    var key=String(toggle.getAttribute("data-menu-toggle")||"").trim();
                    var panel=document.querySelector('[data-menu-panel="'+key+'"]');
                    if(!panel)return;
                    var isOpen=panel.classList.contains("is-open");
                    closeAll();
                    if(!isOpen){
                        panel.classList.add("is-open");
                        panel.setAttribute("aria-hidden","false");
                        toggle.setAttribute("aria-expanded","true");
                    }
                });
            });
            closeButtons.forEach(function(btn){
                if(btn.getAttribute("data-menu-close-bound")==="1")return;
                btn.setAttribute("data-menu-close-bound","1");
                btn.addEventListener("click",function(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                    closeAll();
                });
            });
            document.addEventListener("click",function(){
                closeAll();
            });
            panels.forEach(function(panel){
                panel.addEventListener("click",function(ev){
                    if(ev.target===panel){
                        closeAll();
                        return;
                    }
                    var drawer=panel.querySelector(".builder-menu-mobile-drawer");
                    if(drawer&&drawer.contains(ev.target)){
                        ev.stopPropagation();
                    }
                });
            });
            Array.from(document.querySelectorAll(".builder-menu-mobile-link,.builder-menu-mobile-cta")||[]).forEach(function(link){
                if(link.getAttribute("data-menu-link-bound")==="1")return;
                link.setAttribute("data-menu-link-bound","1");
                link.addEventListener("click",function(){ closeAll(); });
            });
            window.__fbSyncResponsiveMenus=closeAll;
        }
        initResponsiveMenus();
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
        var measurePreviewContentWidth=function(content){
            if(!content||!content.getBoundingClientRect)return 0;
            var rootRect=content.getBoundingClientRect();
            var maxRight=Math.max(content.scrollWidth||0,content.offsetWidth||0);
            Array.from(content.querySelectorAll("*")||[]).forEach(function(node){
                if(!node||!node.getBoundingClientRect)return;
                var tag=String(node.tagName||"").toLowerCase();
                if(tag==="script"||tag==="style")return;
                var rect=node.getBoundingClientRect();
                if((rect.width<=0&&rect.height<=0)||!isFinite(rect.right))return;
                var right=rect.right-rootRect.left;
                if(right>maxRight)maxRight=right;
            });
            return Math.ceil(maxRight);
        };
        var useZoom=function(){
            try{
                var test=document.createElement("div");
                test.style.zoom="1";
                return test.style.zoom!=="";
            }catch(_e){}
            return false;
        }();
       function applyCanvasScalePublished(){
            var content=document.querySelector(".step-content--full");
            if(!content)return;
            syncAbsoluteColumnHeights();
            document.body.style.margin="0";
            document.body.style.display="block";
            document.body.style.flexDirection="";
            document.body.style.alignItems="";
            document.body.style.minHeight="";
            content.style.zoom="";
            content.style.transform="none";
            content.style.height="auto";
            content.style.width=editorCanvasWidth+"px";
            content.style.maxWidth="none";
            content.style.boxSizing="border-box";
            content.style.marginLeft="0";
            content.style.marginRight="0";
            content.style.display="block";
            content.style.position="relative";
            content.style.left="0";
            var targetPad=10;
            content.style.padding=targetPad+"px";
            var viewportW=document.documentElement?document.documentElement.clientWidth:window.innerWidth;
            var measuredW=measurePreviewContentWidth(content);
            var baseCanvasWidth=Math.max(editorCanvasWidth||0,measuredW||0);
            if(baseCanvasWidth>0){
                content.style.width=baseCanvasWidth+"px";
            }
            var availW=viewportW-(targetPad*2);
            if(availW<200)availW=viewportW;
            var scale=availW/baseCanvasWidth;
            if(scale<=0)scale=1;
            // Let desktop preview/test fill the available viewport again so
            // edge-aligned builder layouts do not look artificially centered.
            if(scale>3.0)scale=3.0;
            content.style.padding=(targetPad/scale)+"px";
            if(useZoom){
        content.style.zoom=String(scale);
        content.style.transform="none";
        content.style.transformOrigin="top left";
        content.style.height="auto";
    }else{
        content.style.zoom="";
        var h=measurePreviewContentHeight(content);
        content.style.transformOrigin="top left";
        content.style.transform="scale("+scale+")";
        content.style.height=(h*scale)+"px";
    }
    document.body.style.overflowX="hidden";
    content.classList.add("is-scale-ready");
}
        if(isPreview&&editorCanvasWidth>0){
            var applyCanvasScale=function(){
                var content=document.querySelector(".step-content--full");
                if(!content)return;
                syncAbsoluteColumnHeights();
                content.style.zoom="";
                content.style.transform="none";
                content.style.height="auto";
                content.style.width=editorCanvasWidth+"px";
                content.style.maxWidth="none";
                content.style.marginLeft="0";
                content.style.marginRight="0";
                content.style.display="block";
                content.style.position="relative";
                content.style.left="0";
                var targetPad=10;
                content.style.padding=targetPad+"px";
                var viewportW=document.documentElement?document.documentElement.clientWidth:window.innerWidth;
                var deviceW=previewDeviceWidths[previewDevice];
                var baseW=(deviceW&&deviceW>0)?deviceW:viewportW;
                var measuredW=measurePreviewContentWidth(content);
                var baseCanvasWidth=Math.max(editorCanvasWidth||0,measuredW||0);
                if(previewDevice==="tablet"||previewDevice==="mobile"){
                    baseCanvasWidth=(deviceW&&deviceW>0)?deviceW:baseCanvasWidth;
                }
                if(baseCanvasWidth>0){
                    content.style.width=baseCanvasWidth+"px";
                }
                var availW=baseW-(targetPad*2);
                if(availW<200)availW=viewportW-(targetPad*2);
                if(availW<200)availW=viewportW;
                var scale=availW/baseCanvasWidth;
                if(scale<=0)scale=1;
                // Let desktop preview/test fill the available viewport again so
                // edge-aligned builder layouts do not look artificially centered.
                if(scale>3.0)scale=3.0;
                content.style.padding=(targetPad/scale)+"px";
                // Using `zoom` makes the browser reflow based on scaled layout, which is
                // what we need for tablet/mobile "layout adjustment" in preview.
                // Fallback to `transform` when zoom is not supported.
                if(useZoom){
                    content.style.zoom=String(scale);
                    content.style.transform="none";
                    content.style.height="auto";
                }else{
                    content.style.zoom="";
                    var h=measurePreviewContentHeight(content);
                    content.style.transformOrigin="top left";
                    content.style.transform="scale("+scale+")";
                    content.style.height=(h*scale)+"px";
                }
                document.body.style.overflowX="hidden";
                content.classList.add("is-scale-ready");
            };
            var scheduleCanvasScale=function(){
                window.requestAnimationFrame(function(){
                    window.requestAnimationFrame(function(){
                        applyCanvasScale();
                    });
                });
            };
            window.__fbSchedulePreviewScale=scheduleCanvasScale;
            scheduleCanvasScale();
            window.addEventListener("resize",function(){scheduleCanvasScale();});
            window.addEventListener("load",function(){scheduleCanvasScale();});
            if(document.fonts&&document.fonts.ready&&typeof document.fonts.ready.then==="function"){
                document.fonts.ready.then(function(){scheduleCanvasScale();}).catch(function(){});
            }
            Array.from(document.images||[]).forEach(function(img){
                if(!img||img.complete)return;
                img.addEventListener("load",function(){scheduleCanvasScale();},{once:true});
                img.addEventListener("error",function(){scheduleCanvasScale();},{once:true});
            });
        }else if(!isPreview&&editorCanvasWidth>0){
            var schedulePublishedScale=function(){
                window.requestAnimationFrame(function(){
                    window.requestAnimationFrame(function(){
                        applyCanvasScalePublished();
                    });
                });
            };
            window.__fbSchedulePublishedScale=schedulePublishedScale;
            schedulePublishedScale();
            window.addEventListener("resize",function(){schedulePublishedScale();});
            window.addEventListener("load",function(){schedulePublishedScale();});
            if(document.fonts&&document.fonts.ready&&typeof document.fonts.ready.then==="function"){
                document.fonts.ready.then(function(){schedulePublishedScale();}).catch(function(){});
            }
            Array.from(document.images||[]).forEach(function(img){
                if(!img||img.complete)return;
                img.addEventListener("load",function(){schedulePublishedScale();},{once:true});
                img.addEventListener("error",function(){schedulePublishedScale();},{once:true});
            });
        }
    })();

    (function(){
        var sanitize=function(value){
            return String(value||"").toUpperCase().replace(/[^A-Z0-9]/g,"").slice(0,40);
        };
        var copiedCouponCode="";
        var funnelSlug=(function(){
            var body=document.body;
            return body ? String(body.getAttribute("data-funnel-slug")||"").trim() : "";
        })();
        var claimedKey=function(){
            return "claimed_coupon:"+(funnelSlug||"global");
        };
        var getClaimedCoupon=function(){
            try{
                var raw=window.localStorage ? window.localStorage.getItem(claimedKey()) : "";
                return sanitize(raw||"");
            }catch(_e){
                return "";
            }
        };
        var setClaimedCoupon=function(code){
            var normalized=sanitize(code);
            try{
                if(window.localStorage){
                    if(normalized){
                        window.localStorage.setItem(claimedKey(), normalized);
                    }else{
                        window.localStorage.removeItem(claimedKey());
                    }
                }
            }catch(_e){}
            return normalized;
        };
        var applyClaimFilter=function(prompt){
            if(!prompt)return;
            var claimed=getClaimedCoupon();
            var items=Array.from(prompt.querySelectorAll("[data-coupon-item]"));
            var visibleCount=0;
            items.forEach(function(item){
                var code=sanitize(item.getAttribute("data-coupon-code")||"");
                var show=claimed!=="" && code===claimed;
                item.style.display=show?"":"none";
                if(show)visibleCount++;
            });
            var wrapper=prompt.querySelector("[data-coupon-available]");
            if(wrapper){
                wrapper.style.display=visibleCount>0?"":"none";
            }
        };
        var setCouponCodeOnForm=function(form,value){
            if(!form)return;
            var hidden=form.querySelector('input[name="coupon_code"]');
            if(!hidden){
                hidden=document.createElement("input");
                hidden.type="hidden";
                hidden.name="coupon_code";
                form.appendChild(hidden);
            }
            hidden.value=sanitize(value);
        };
        var moneyText=function(amount){
            var n=Number(amount||0);
            if(!isFinite(n))n=0;
            return "PHP "+n.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
        };
        var parseMoney=function(value){
            var text=String(value||"").replace(/[^0-9.\-]/g,"");
            var parsed=parseFloat(text);
            return isFinite(parsed)?parsed:0;
        };
        var previewCoupon=function(options,code,subtotal){
            var normalized=sanitize(code);
            var match=(Array.isArray(options)?options:[]).find(function(item){
                return sanitize(item&&item.code)===normalized;
            })||null;
            var discount=0;
            if(match){
                if(String(match.discount_type||"")==="percent"){
                    discount=Math.max(0,Math.min(subtotal,subtotal*(Number(match.discount_value||0)/100)));
                }else{
                    discount=Math.max(0,Math.min(subtotal,Number(match.discount_value||0)));
                }
            }
            return {
                match:match,
                discount:Math.round(discount*100)/100,
                total:Math.round(Math.max(0,subtotal-discount)*100)/100
            };
        };

        var promoPop=document.getElementById("funnelCouponPop");
        if(promoPop){
            var code=String(promoPop.getAttribute("data-coupon-code")||"").trim();
            var timerNode=promoPop.querySelector("[data-funnel-coupon-timer]");
            var progressNode=promoPop.querySelector("[data-funnel-coupon-progress]");
            var minitab=promoPop.querySelector("[data-show-funnel-coupon]");
            var totalSeconds=parseInt(promoPop.getAttribute("data-coupon-seconds")||"60",10);
            if(!isFinite(totalSeconds)||totalSeconds<1)totalSeconds=60;
            var alreadyClaimed=getClaimedCoupon();
            copiedCouponCode=sanitize(alreadyClaimed||code);
            // If a coupon was already claimed for this funnel, never show the popup again.
            if(alreadyClaimed!==""){
                promoPop.style.display="none";
                if(window.__funnelCouponTimer)clearInterval(window.__funnelCouponTimer);
            }else{
            var collapsePromo=function(){
                promoPop.classList.add("is-hidden");
                if(minitab)minitab.classList.add("is-visible");
            };
            var expandPromo=function(){
                promoPop.classList.remove("is-hidden");
                if(minitab)minitab.classList.remove("is-visible");
            };
            var remaining=totalSeconds;
            var tick=function(){
                remaining=Math.max(0,remaining-1);
                if(timerNode)timerNode.textContent=remaining+"s left";
                if(progressNode)progressNode.style.transform="scaleX("+(remaining/totalSeconds)+")";
                if(remaining<=0){
                    clearInterval(window.__funnelCouponTimer);
                    collapsePromo();
                }
            };
            if(timerNode)timerNode.textContent=remaining+"s left";
            window.__funnelCouponTimer=window.setInterval(tick,1000);
            var claimBtn=promoPop.querySelector("[data-claim-funnel-coupon]");
            if(claimBtn){
                claimBtn.addEventListener("click",function(){
                    var normalized=setClaimedCoupon(code);
                    if(normalized){
                        copiedCouponCode=normalized;
                    }
                    claimBtn.textContent="Claimed";
                    window.setTimeout(function(){ claimBtn.textContent="Claim Coupon"; },1400);
                    // After claiming, hide the popup permanently for this funnel.
                    promoPop.style.display="none";
                    if(window.__funnelCouponTimer)clearInterval(window.__funnelCouponTimer);
                });
            }
            var hideBtn=promoPop.querySelector("[data-hide-funnel-coupon]");
            if(hideBtn)hideBtn.addEventListener("click",collapsePromo);
            if(minitab)minitab.addEventListener("click",expandPromo);
            }
        }

        var setPromptOpen=function(backdrop,open){
            if(!backdrop)return;
            backdrop.classList.toggle("is-open",!!open);
            backdrop.setAttribute("aria-hidden",open?"false":"true");
            document.body.style.overflow=open?"hidden":"";
            backdrop.style.pointerEvents=open?"auto":"";
            if(open && typeof portalHideLoading==="function"){
                // Ensure no global loading overlay blocks modal clicks.
                portalHideLoading();
            }
            if(open){
                var input=backdrop.querySelector("[data-coupon-prompt-input]");
                if(input){
                    if(sanitize(input.value)==="" && copiedCouponCode!=="")input.value=copiedCouponCode;
                    try{ input.focus(); }catch(_e){}
                }
            }
        };
        var openCouponPrompt=function(form){
            if(!form)return;
            var prompt=form.querySelector("[data-coupon-prompt]");
            if(!prompt){
                try{
                    HTMLFormElement.prototype.submit.call(form);
                }catch(_e){
                    form.submit();
                }
                return;
            }
            // Always open the modal first; never auto-submit on JS errors.
            setPromptOpen(prompt,true);
            form.setAttribute("data-coupon-prompt-open","1");
            try{
                syncCheckoutPricingForm(form);
                var amountInput=form.querySelector('input[name="amount"]');
                var subtotal=parseMoney(amountInput?amountInput.value:"0");
                prompt.setAttribute("data-coupon-subtotal-value",String(subtotal));
                var input=prompt.querySelector("[data-coupon-prompt-input]");
                var hidden=form.querySelector('input[name="coupon_code"]');
                if(input){
                    applyClaimFilter(prompt);
                    var claimed=getClaimedCoupon();
                    var nextValue=sanitize((hidden&&hidden.value)||claimed||copiedCouponCode||input.value);
                    if(nextValue===""){
                        var applyBtn0=prompt.querySelector("[data-coupon-apply]");
                        var opts0=[];
                        try{ opts0=JSON.parse(applyBtn0&&applyBtn0.getAttribute("data-coupon-options")||"[]"); }catch(_e){}
                        if(Array.isArray(opts0) && opts0.length && opts0[0] && opts0[0].code){
                            nextValue=sanitize(opts0[0].code);
                        }
                    }
                    input.value=nextValue;
                }
                var previewBtn=prompt.querySelector("[data-coupon-apply]");
                var options=[];
                try{ options=JSON.parse(previewBtn&&previewBtn.getAttribute("data-coupon-options")||"[]"); }catch(_e){}
                var preview=previewCoupon(options,input?input.value:"",subtotal);
                var subtotalNode=prompt.querySelector("[data-coupon-subtotal]");
                var discountNode=prompt.querySelector("[data-coupon-discount]");
                var totalNode=prompt.querySelector("[data-coupon-total]");
                var messageNode=prompt.querySelector("[data-coupon-prompt-message]");
                if(subtotalNode)subtotalNode.textContent=moneyText(subtotal);
                if(discountNode)discountNode.textContent=moneyText(preview.discount);
                if(totalNode)totalNode.textContent=moneyText(preview.total);
                if(messageNode){
                    messageNode.className="coupon-prompt-message";
                    messageNode.textContent=preview.match ? (preview.match.title||"Coupon detected. Discount will be applied at payment.") : "No coupon applied yet. You can continue without one.";
                }
            }catch(_e){}
        };
        window.__openCouponPrompt=openCouponPrompt;

        // Coupon quick actions inside the prompt (Copy/Use)
        document.querySelectorAll("[data-coupon-prompt]").forEach(function(prompt){
            if(prompt.getAttribute("data-coupon-actions-bound")==="1")return;
            prompt.setAttribute("data-coupon-actions-bound","1");
            var input=prompt.querySelector("[data-coupon-prompt-input]");
            var applyBtn=prompt.querySelector("[data-coupon-apply]");
            prompt.querySelectorAll("[data-coupon-use]").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var code=sanitize(btn.getAttribute("data-coupon-use")||"");
                    if(code==="")return;
                    if(input)input.value=code;
                    copiedCouponCode=code;
                    // trigger preview refresh quickly
                    try{
                        if(applyBtn){
                            var subtotal=parseMoney(prompt.getAttribute("data-coupon-subtotal-value")||"0");
                            var options=[];
                            try{ options=JSON.parse(applyBtn.getAttribute("data-coupon-options")||"[]"); }catch(_e){}
                            var preview=previewCoupon(options,code,subtotal);
                            var discountNode=prompt.querySelector("[data-coupon-discount]");
                            var totalNode=prompt.querySelector("[data-coupon-total]");
                            var messageNode=prompt.querySelector("[data-coupon-prompt-message]");
                            if(discountNode)discountNode.textContent=moneyText(preview.discount);
                            if(totalNode)totalNode.textContent=moneyText(preview.total);
                            if(messageNode){
                                messageNode.className="coupon-prompt-message"+(preview.match?" is-success":"");
                                messageNode.textContent=preview.match ? ((preview.match.title||"Coupon applied.")+" Discount will be applied at payment.") : "Coupon selected.";
                            }
                        }
                    }catch(_e){}
                });
            });
        });
        var bindPromptPreview=function(prompt,form){
            if(!prompt||!form||prompt.getAttribute("data-coupon-prompt-bound")==="1")return;
            prompt.setAttribute("data-coupon-prompt-bound","1");
            var input=prompt.querySelector("[data-coupon-prompt-input]");
            var applyBtn=prompt.querySelector("[data-coupon-apply]");
            var messageNode=prompt.querySelector("[data-coupon-prompt-message]");
            var subtotalNode=prompt.querySelector("[data-coupon-subtotal]");
            var discountNode=prompt.querySelector("[data-coupon-discount]");
            var totalNode=prompt.querySelector("[data-coupon-total]");
            var forceCheckoutSubmit=function(){
                syncCheckoutPricingForm(form);
                var syncedCustomer=syncCheckoutCustomerForm(form);
                if(!syncedCustomer.ok){
                    if(messageNode){
                        messageNode.className="coupon-prompt-message is-error";
                        messageNode.textContent="Please complete your shipping details before continuing.";
                    }
                    var shippingModal=form.querySelector("[data-shipping-modal]");
                    if(shippingModal)setShippingModalOpen(shippingModal,true);
                    return;
                }
                form.setAttribute("data-coupon-confirmed","1");
                if(typeof portalShowLoading==="function")portalShowLoading();
                // Submit immediately; also keep a 0ms fallback in case browser blocks sync submit.
                try{ HTMLFormElement.prototype.submit.call(form); }catch(_e){ try{ form.submit(); }catch(_e2){} }
                window.setTimeout(function(){
                    if(form.getAttribute("data-submitting")==="1")return;
                    try{ HTMLFormElement.prototype.submit.call(form); }catch(_e){ try{ form.submit(); }catch(_e2){} }
                },0);
            };
            var recalc=function(){
                var subtotal=parseMoney(prompt.getAttribute("data-coupon-subtotal-value")||"0");
                var options=[];
                try{ options=JSON.parse(applyBtn&&applyBtn.getAttribute("data-coupon-options")||"[]"); }catch(_e){}
                var preview=previewCoupon(options,input?input.value:"",subtotal);
                if(subtotalNode)subtotalNode.textContent=moneyText(subtotal);
                if(discountNode)discountNode.textContent=moneyText(preview.discount);
                if(totalNode)totalNode.textContent=moneyText(preview.total);
                if(messageNode){
                    messageNode.className="coupon-prompt-message"+(preview.match?" is-success":"");
                    messageNode.textContent=preview.match
                        ? "Coupon found: "+String(preview.match.title||preview.match.code||"Discount")+""
                        : "We will still verify the code securely before payment.";
                }
            };
            if(input){
                input.addEventListener("input",function(){
                    input.value=sanitize(input.value);
                    recalc();
                });
            }
            prompt.querySelectorAll("[data-close-coupon-prompt]").forEach(function(btn){
                btn.addEventListener("click",function(){ setPromptOpen(prompt,false); });
            });
            var skipBtn=prompt.querySelector("[data-coupon-skip]");
            if(skipBtn){
                skipBtn.addEventListener("click",function(){
                    try{ event && event.preventDefault && event.preventDefault(); }catch(_e){}
                    setCouponCodeOnForm(form,"");
                    setPromptOpen(prompt,false);
                    forceCheckoutSubmit();
                });
            }
            if(applyBtn){
                applyBtn.addEventListener("click",function(){
                    try{ event && event.preventDefault && event.preventDefault(); }catch(_e){}
                    setCouponCodeOnForm(form,input?input.value:"");
                    copiedCouponCode=sanitize(input?input.value:"");
                    setPromptOpen(prompt,false);
                    forceCheckoutSubmit();
                });
            }
            recalc();
        };

        // Extra safety: global click delegation so the buttons always work even if
        // the modal DOM is re-rendered or bound handlers fail.
        document.addEventListener("click",function(e){
            var btn=e.target&&e.target.closest?e.target.closest("[data-coupon-apply],[data-coupon-skip]"):null;
            if(!btn)return;
            var prompt=btn.closest("[data-coupon-prompt]");
            var form=btn.closest("form[data-checkout-summary-form]");
            if(!prompt||!form)return;
            // If the prompt is bound, its handlers will run; this delegation is just a fallback.
            if(prompt.getAttribute("data-coupon-prompt-bound")==="1")return;
            e.preventDefault();
            var input=prompt.querySelector("[data-coupon-prompt-input]");
            if(btn.hasAttribute("data-coupon-skip")){
                setCouponCodeOnForm(form,"");
            }else{
                setCouponCodeOnForm(form,input?input.value:"");
            }
            setPromptOpen(prompt,false);
            syncCheckoutPricingForm(form);
            var syncedCustomer=syncCheckoutCustomerForm(form);
            if(!syncedCustomer.ok){
                var shippingModal=form.querySelector("[data-shipping-modal]");
                if(shippingModal)setShippingModalOpen(shippingModal,true);
                return;
            }
            form.setAttribute("data-coupon-confirmed","1");
            if(typeof portalShowLoading==="function")portalShowLoading();
            try{ HTMLFormElement.prototype.submit.call(form); }catch(_e){ try{ form.submit(); }catch(_e2){} }
        },true);

        document.querySelectorAll("[data-checkout-summary-form]").forEach(function(form){
            setCouponCodeOnForm(form,"");
            var prompt=form.querySelector("[data-coupon-prompt]");
            bindPromptPreview(prompt,form);
        });

        bindShippingModals();
    })();
    </script>
</body>
</html>
