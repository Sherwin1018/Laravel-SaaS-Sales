@extends('layouts.admin')

@section('title', 'Create Funnel')

@section('content')
    <div class="top-header">
        <h1>Create Funnel</h1>
    </div>

    <div class="card" style="max-width: 820px; margin: 0 auto;">
        <form method="POST" action="{{ route('funnels.store') }}">
            @csrf

            <div style="margin-bottom: 16px;">
                <label for="name" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Name</label>
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

            @php
                $selectedTemplateId = (int) old('template_id', 0);
            @endphp
            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Starter Template</label>
                <div style="display:grid; gap:8px;">
                    <label style="display:flex; gap:8px; align-items:flex-start; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:8px; background:#fff;">
                        <input type="radio" name="template_id" value="" {{ $selectedTemplateId === 0 ? 'checked' : '' }}>
                        <span style="display:grid; gap:2px;">
                            <strong style="font-size:13px;">Use Built-in Starter Flow</strong>
                            <span style="font-size:12px; color:#64748b;">Choose purpose below and start from your default step sequence.</span>
                        </span>
                    </label>
                    @forelse(($publishedTemplates ?? []) as $template)
                        <label style="display:flex; gap:8px; align-items:flex-start; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:8px; background:#fff;">
                            <input type="radio" name="template_id" value="{{ $template->id }}" {{ $selectedTemplateId === (int) $template->id ? 'checked' : '' }}>
                            <span style="display:grid; gap:2px;">
                                <strong style="font-size:13px;">{{ $template->name }}</strong>
                                <span style="font-size:12px; color:#64748b;">{{ $template->description ?: 'Published shared template.' }}</span>
                                <span style="font-size:11px; color:#8b5cf6;">
                                    {{ $template->templateTypeLabel() }} | {{ $template->steps->count() }} Steps
                                </span>
                            </span>
                        </label>
                    @empty
                        <div style="font-size:12px; color:#64748b; border:1px dashed #d6cce4; border-radius:8px; padding:10px;">
                            No published shared templates yet. Create and publish one in Super Admin -> Funnel Templates.
                        </div>
                    @endforelse
                </div>
                <div style="margin-top:6px; color:#64748b; font-size:12px;">
                    If you pick a template, this funnel is cloned from that template and you can edit it in your workspace builder.
                </div>
                @error('template_id')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="purpose" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Purpose</label>
                <select id="purpose" name="purpose" {{ $selectedTemplateId === 0 ? 'required' : '' }}
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fff;">
                    <option value="" disabled {{ old('purpose') ? '' : 'selected' }}>Select funnel purpose</option>
                    @foreach(($purposeOptions ?? []) as $value => $label)
                        <option value="{{ $value }}" {{ old('purpose') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div style="margin-top:6px; color:#64748b; font-size:12px;">
                    This sets the starter flow. Single-page funnels start with one page and are designed to match super-admin shared templates.
                </div>
                @error('purpose')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="default_tags" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Tags</label>
                <input id="default_tags" name="default_tags" type="text" value="{{ old('default_tags') }}"
                    placeholder="webinar, q2-campaign, lead-magnet"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                @error('default_tags')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin:18px 0; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; font-size:13px; line-height:1.55;">
                Starter flow by purpose:
                <br><strong>Single Page</strong>: One page with sections for landing/sales/checkout
                <br><br>Template mode clones the selected published template and keeps full step layout/content.
            </div>

            <div style="display:flex; gap:10px; margin-top:18px;">
                <button type="submit" class="btn-create">Create Funnel</button>
                <a href="{{ route('funnels.index') }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        (function () {
            var purpose = document.getElementById('purpose');
            if (!purpose) return;
            var radios = Array.from(document.querySelectorAll('input[name="template_id"]'));
            var sync = function () {
                var selected = radios.find(function (node) { return node.checked; });
                var hasTemplate = !!selected && String(selected.value || '').trim() !== '';
                if (hasTemplate) {
                    purpose.removeAttribute('required');
                } else {
                    purpose.setAttribute('required', 'required');
                }
            };
            radios.forEach(function (node) {
                node.addEventListener('change', sync);
            });
            sync();
        })();
    </script>
@endsection
