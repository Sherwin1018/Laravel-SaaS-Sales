@php
    $featuresText = old('features', isset($plan) ? implode(PHP_EOL, (array) $plan->features) : '');
@endphp

<div style="margin-bottom: 20px;">
    <label for="code" style="display:block;margin-bottom:8px;font-weight:bold;">Plan Code</label>
    <input type="text" name="code" id="code" required
        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
        value="{{ old('code', $plan->code ?? '') }}">
    @error('code')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
</div>

<div style="margin-bottom: 20px;">
    <label for="name" style="display:block;margin-bottom:8px;font-weight:bold;">Plan Name</label>
    <input type="text" name="name" id="name" required
        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
        value="{{ old('name', $plan->name ?? '') }}">
    @error('name')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
</div>

<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
    <div style="margin-bottom: 20px;">
        <label for="price" style="display:block;margin-bottom:8px;font-weight:bold;">Price</label>
        <input type="number" step="0.01" min="0" name="price" id="price" required
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('price', isset($plan) ? number_format((float) $plan->price, 2, '.', '') : '') }}">
        @error('price')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>

    <div style="margin-bottom: 20px;">
        <label for="period" style="display:block;margin-bottom:8px;font-weight:bold;">Period Label</label>
        <input type="text" name="period" id="period" required
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('period', $plan->period ?? 'per month') }}">
        @error('period')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>
</div>

<div style="margin-bottom: 20px;">
    <label for="summary" style="display:block;margin-bottom:8px;font-weight:bold;">Summary</label>
    <textarea name="summary" id="summary" rows="3" required
        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">{{ old('summary', $plan->summary ?? '') }}</textarea>
    @error('summary')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
</div>

<div style="margin-bottom: 20px;">
    <label for="features" style="display:block;margin-bottom:8px;font-weight:bold;">Features</label>
    <textarea name="features" id="features" rows="6" required
        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">{{ $featuresText }}</textarea>
    <p style="margin-top:6px;color:#475569;font-size:12px;">Enter one feature per line.</p>
    @error('features')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
</div>

<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
    <div style="margin-bottom: 20px;">
        <label for="spotlight" style="display:block;margin-bottom:8px;font-weight:bold;">Spotlight Badge</label>
        <input type="text" name="spotlight" id="spotlight"
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('spotlight', $plan->spotlight ?? '') }}">
        @error('spotlight')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>

    <div style="margin-bottom: 20px;">
        <label for="sort_order" style="display:block;margin-bottom:8px;font-weight:bold;">Display Order</label>
        <input type="number" min="0" name="sort_order" id="sort_order" required
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('sort_order', $plan->sort_order ?? 0) }}">
        @error('sort_order')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>
</div>

<div style="margin-bottom: 24px;">
    <label style="display:flex;align-items:center;gap:10px;font-weight:bold;">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', isset($plan) ? (int) $plan->is_active : 1) ? 'checked' : '' }}>
        Show this plan in landing page and registration
    </label>
    @error('is_active')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
</div>
