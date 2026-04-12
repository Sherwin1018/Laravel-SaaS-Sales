@extends('layouts.admin')

@section('title', 'Create Funnel Template')

@section('content')
    <div class="top-header">
        <h1>Create Funnel Template</h1>
    </div>

    <div class="card" style="max-width: 760px; margin: 0 auto;">
        <form method="POST" action="{{ route('admin.funnel-templates.store') }}">
            @csrf
            <div style="margin-bottom: 16px;">
                <label for="name" style="display:block; margin-bottom:8px; font-weight:700;">Template Name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                @error('name')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="description" style="display:block; margin-bottom:8px; font-weight:700;">Description</label>
                <textarea id="description" name="description" rows="4"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">{{ old('description') }}</textarea>
                @error('description')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="template_type" style="display:block; margin-bottom:8px; font-weight:700;">Template Purpose</label>
                <select id="template_type" name="template_type" required
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fff;">
                    <option value="" disabled {{ old('template_type') ? '' : 'selected' }}>Select template purpose</option>
                    @foreach(($templateTypeOptions ?? []) as $value => $label)
                        <option value="{{ $value }}" {{ old('template_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div style="margin-top:6px; color:#64748b; font-size:12px;">
                    Choose this first so the template is categorized correctly before you open the builder.
                </div>
                @error('template_type')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin:18px 0; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; font-size:13px; line-height:1.55;">
                Active build type:
                <br><strong>Single Page Funnel</strong>: Single page canvas with sections for full journey content.
                <br><strong>Step-by-Step templates</strong>: visible as placeholders only (no real content for now).
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-create">Create Template</button>
                <a href="{{ route('admin.funnel-templates.index') }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
