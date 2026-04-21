@extends('layouts.admin')

@section('title', 'Replace Funnel Template JSON')

@section('content')
    <div class="top-header">
        <h1>Replace Template JSON</h1>
    </div>

    @php
        $resolvedPurpose = old('funnel_purpose', $template->resolvedFunnelPurpose());
    @endphp

    <div class="card" style="max-width: 920px; margin: 0 auto;">
        <div style="margin-bottom:16px; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; font-size:13px; line-height:1.6;">
            You are replacing the JSON for <strong>{{ $template->name }}</strong>. This keeps the same template record and slug, but rebuilds its steps from the JSON below.
        </div>

        <form method="POST" action="{{ route('admin.funnel-templates.replace-json.store', $template) }}">
            @csrf

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:16px;">
                <div>
                    <label for="name" style="display:block; margin-bottom:8px; font-weight:700;">Template Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $template->name) }}"
                        style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                    @error('name')
                        <span style="color:red; font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="template_tags" style="display:block; margin-bottom:8px; font-weight:700;">Card Chips</label>
                    <input id="template_tags" name="template_tags" type="text" value="{{ old('template_tags', collect($template->template_tags ?? [])->reject(fn ($tag) => str_starts_with(mb_strtolower((string) $tag), \App\Models\FunnelTemplate::PURPOSE_TAG_PREFIX))->implode(', ')) }}"
                        placeholder="5 Pages, Landing, Checkout"
                        style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                    @error('template_tags')
                        <span style="color:red; font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="template_type" style="display:block; margin-bottom:8px; font-weight:700;">Template Purpose</label>
                    <select id="template_type" name="template_type" required
                        style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fff;">
                        @foreach(($templateTypeOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ old('template_type', $template->template_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('template_type')
                        <span style="color:red; font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="funnel_purpose" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Purpose</label>
                    <select id="funnel_purpose" name="funnel_purpose" required
                        style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fff;">
                        @foreach(($templateFunnelPurposeOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ $resolvedPurpose === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('funnel_purpose')
                        <span style="color:red; font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="description" style="display:block; margin-bottom:8px; font-weight:700;">Description</label>
                <textarea id="description" name="description" rows="3"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">{{ old('description', $template->description) }}</textarea>
                @error('description')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="import_json" style="display:block; margin-bottom:8px; font-weight:700;">Template JSON</label>
                <textarea id="import_json" name="import_json" rows="22"
                    style="width:100%; padding:12px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; font-family:Consolas, 'Courier New', monospace; font-size:13px; line-height:1.5;">{{ old('import_json', $defaultJson) }}</textarea>
                @error('import_json')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px; font-weight:700; color:#240E35;">
                <input type="checkbox" name="publish_now" value="1" {{ old('publish_now', $template->status === 'published' ? '1' : null) ? 'checked' : '' }}>
                Keep template published after replace
            </label>

            <div style="margin-bottom:16px; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; font-size:13px; line-height:1.6;">
                Supported shapes:
                <br>1. Full template JSON with <code>steps</code>
                <br>2. Full template JSON with <code>pages</code>
                <br>3. Single-step JSON with one <code>layout_json</code> object
                <br><br>This replaces the existing steps for this template. Assets are left untouched.
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" class="btn-create" style="background:#0F766E;">Replace Template JSON</button>
                <a href="{{ route('admin.funnel-templates.edit', $template) }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Back to Builder</a>
            </div>
        </form>
    </div>
@endsection
