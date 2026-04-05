@extends('layouts.admin')

@section('title', 'Import Funnel Template')

@section('content')
    <div class="top-header">
        <h1>Import Funnel Template</h1>
    </div>

    <div class="card" style="max-width: 920px; margin: 0 auto;">
        <form method="POST" action="{{ route('admin.funnel-templates.import.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:16px;">
                <div>
                    <label for="name" style="display:block; margin-bottom:8px; font-weight:700;">Template Name Override</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}"
                        placeholder="Leave blank to use the JSON name"
                        style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                    @error('name')
                        <span style="color:red; font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="template_tags" style="display:block; margin-bottom:8px; font-weight:700;">Card Chips</label>
                    <input id="template_tags" name="template_tags" type="text" value="{{ old('template_tags') }}"
                        placeholder="5 Pages, Landing, Opt In"
                        style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                    @error('template_tags')
                        <span style="color:red; font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="template_type" style="display:block; margin-bottom:8px; font-weight:700;">Template Purpose</label>
                    <select id="template_type" name="template_type" required
                        style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fff;">
                        <option value="" disabled {{ old('template_type') ? '' : 'selected' }}>Select template purpose</option>
                        @foreach(($templateTypeOptions ?? []) as $value => $label)
                            <option value="{{ $value }}" {{ old('template_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('template_type')
                        <span style="color:red; font-size:12px;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="description" style="display:block; margin-bottom:8px; font-weight:700;">Description Override</label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Leave blank to use the JSON description"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">{{ old('description') }}</textarea>
                @error('description')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="import_json" style="display:block; margin-bottom:8px; font-weight:700;">Template JSON</label>
                <textarea id="import_json" name="import_json" rows="20"
                    style="width:100%; padding:12px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; font-family:Consolas, 'Courier New', monospace; font-size:13px; line-height:1.5;">{{ old('import_json', '{
  "name": "Consulting Authority",
  "description": "Imported full-funnel template",
  "template_tags": ["5 Pages", "Landing", "Opt In"],
  "steps": [
    {
      "title": "Landing",
      "slug": "landing",
      "type": "landing",
      "layout_json": {
        "root": [],
        "sections": []
      }
    },
    {
      "title": "Opt-in",
      "slug": "opt-in",
      "type": "opt_in",
      "layout_json": {
        "root": [],
        "sections": []
      }
    }
  ]
}') }}</textarea>
                @error('import_json')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <label style="display:flex; align-items:center; gap:8px; margin-bottom:16px; font-weight:700; color:#240E35;">
                <input type="checkbox" name="publish_now" value="1" {{ old('publish_now') ? 'checked' : '' }}>
                Publish immediately after import
            </label>

            <div style="margin-bottom:16px; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; font-size:13px; line-height:1.6;">
                Supported shapes:
                <br>1. Full template JSON with <code>steps</code>
                <br>2. Full template JSON with <code>pages</code>
                <br>3. Single-page JSON with one <code>layout_json</code> object
                <br><br>Each step can include fields like <code>title</code>, <code>slug</code>, <code>type</code>, <code>description</code>, <code>template</code>, <code>step_tags</code>, and <code>layout_json</code>.
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" class="btn-create">Import Template</button>
                <a href="{{ route('admin.funnel-templates.index') }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
