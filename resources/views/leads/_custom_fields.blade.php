@foreach($customFields as $field)
    @php
        $fieldName = 'custom_fields[' . $field->id . ']';
        $fieldId = 'custom_field_' . $field->id;
        $value = old('custom_fields.' . $field->id, $values[$field->id] ?? ($field->field_type === 'checkbox' ? '0' : ''));
    @endphp

    <div style="margin-bottom: 20px;">
        <label for="{{ $fieldId }}" style="display: block; margin-bottom: 8px; font-weight: bold;">
            {{ $field->label }}
            @if($field->is_required)
                <span style="color: #DC2626;">*</span>
            @endif
        </label>

        @if($field->field_type === 'textarea')
            <textarea name="{{ $fieldName }}" id="{{ $fieldId }}" rows="3"
                style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">{{ $value }}</textarea>
        @elseif($field->field_type === 'select')
            <select name="{{ $fieldName }}" id="{{ $fieldId }}"
                style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                <option value="">Select an option</option>
                @foreach($field->options ?? [] as $option)
                    <option value="{{ $option }}" {{ (string) $value === (string) $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        @elseif($field->field_type === 'checkbox')
            <input type="hidden" name="{{ $fieldName }}" value="0">
            <label style="display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
                <input type="checkbox" name="{{ $fieldName }}" id="{{ $fieldId }}" value="1" {{ (string) $value === '1' ? 'checked' : '' }}>
                Yes
            </label>
        @else
            <input type="{{ $field->field_type === 'number' ? 'number' : ($field->field_type === 'date' ? 'date' : 'text') }}"
                name="{{ $fieldName }}" id="{{ $fieldId }}"
                style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                value="{{ $value }}">
        @endif

        @error('custom_fields.' . $field->id)
            <span style="color: red; font-size: 12px;">{{ $message }}</span>
        @enderror
    </div>
@endforeach
