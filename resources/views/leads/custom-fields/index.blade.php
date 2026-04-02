@extends('layouts.admin')

@section('title', 'Lead Custom Fields')

@section('content')
    <div class="top-header">
        <h1>Lead Custom Fields</h1>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0, 360px) minmax(0, 1fr);gap:20px;">
        <div class="card">
            <h3 style="margin-top:0;">Add Custom Field</h3>
            <form method="POST" action="{{ route('crm.custom-fields.store') }}">
                @csrf
                <div style="margin-bottom:16px;">
                    <label for="label" style="display:block;margin-bottom:8px;font-weight:700;">Field Label</label>
                    <input type="text" name="label" id="label" required
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
                        value="{{ old('label') }}">
                    @error('label')
                        <span style="display:block;margin-top:6px;color:#B91C1C;font-size:12px;font-weight:600;">{{ $message }}</span>
                    @enderror
                </div>
                <div style="margin-bottom:16px;">
                    <label for="field_type" style="display:block;margin-bottom:8px;font-weight:700;">Field Type</label>
                    <select name="field_type" id="field_type" required
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                        @foreach($fieldTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('field_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('field_type')
                        <span style="display:block;margin-top:6px;color:#B91C1C;font-size:12px;font-weight:600;">{{ $message }}</span>
                    @enderror
                </div>
                <div style="margin-bottom:16px;">
                    <label for="options" style="display:block;margin-bottom:8px;font-weight:700;">Options</label>
                    <textarea name="options" id="options" rows="3"
                        style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
                        placeholder="For select fields only. Separate options with commas or new lines.">{{ old('options') }}</textarea>
                    @error('options')
                        <span style="display:block;margin-top:6px;color:#B91C1C;font-size:12px;font-weight:600;">{{ $message }}</span>
                    @enderror
                </div>
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }}>
                    Required
                </label>
                <label style="display:flex;align-items:center;gap:8px;margin-bottom:18px;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    Active
                </label>
                <button type="submit"
                    style="padding:10px 16px;background:var(--theme-primary, #240E35);color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer;">
                    Save Field
                </button>
            </form>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">Current Field Definitions</h3>
            <p style="margin-top:0;color:var(--theme-muted, #6B7280);font-size:13px;">
                These fields appear on lead create/edit forms for this tenant only.
            </p>

            <div style="display:grid;gap:16px;">
                @forelse($fields as $field)
                    <form method="POST" action="{{ route('crm.custom-fields.update', $field) }}"
                        style="border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;padding:16px;background:var(--theme-surface-softer, #F7F7FB);">
                        @csrf
                        @method('PUT')
                        <div style="display:grid;grid-template-columns:repeat(2, minmax(0, 1fr));gap:12px;">
                            <div>
                                <label style="display:block;margin-bottom:6px;font-weight:700;">Label</label>
                                <input type="text" name="label" value="{{ old('label', $field->label) }}"
                                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block;margin-bottom:6px;font-weight:700;">Type</label>
                                <select name="field_type"
                                    style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
                                    @foreach($fieldTypes as $value => $label)
                                        <option value="{{ $value }}" {{ $field->field_type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:12px;">
                            <label style="display:block;margin-bottom:6px;font-weight:700;">Key</label>
                            <input type="text" value="{{ $field->key }}" disabled
                                style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;background:#F3F4F6;color:#6B7280;">
                        </div>
                        <div style="margin-top:12px;">
                            <label style="display:block;margin-bottom:6px;font-weight:700;">Options</label>
                            <textarea name="options" rows="2"
                                style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">{{ implode(PHP_EOL, $field->options ?? []) }}</textarea>
                        </div>
                        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:12px;">
                            <label style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" name="is_required" value="1" {{ $field->is_required ? 'checked' : '' }}>
                                Required
                            </label>
                            <label style="display:flex;align-items:center;gap:8px;">
                                <input type="checkbox" name="is_active" value="1" {{ $field->is_active ? 'checked' : '' }}>
                                Active
                            </label>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;">
                            <button type="submit"
                                style="padding:9px 14px;background:var(--theme-primary, #240E35);color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer;">
                                Update Field
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('crm.custom-fields.destroy', $field) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            style="padding:8px 14px;background:#7F1D1D;color:#fff;border:none;border-radius:6px;font-weight:700;cursor:pointer;">
                            Delete Field
                        </button>
                    </form>
                @empty
                    <div style="padding:18px;border:1px dashed var(--theme-border, #E6E1EF);border-radius:8px;color:var(--theme-muted, #6B7280);">
                        No custom fields created yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
