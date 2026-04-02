<?php

namespace App\Http\Controllers;

use App\Models\TenantCustomField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadCustomFieldController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $this->ensureCanManageCustomFields();

        return view('leads.custom-fields.index', [
            'fields' => TenantCustomField::query()
                ->where('tenant_id', $user->tenant_id)
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
            'fieldTypes' => TenantCustomField::FIELD_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $this->ensureCanManageCustomFields();

        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'field_type' => ['required', Rule::in(array_keys(TenantCustomField::FIELD_TYPES))],
            'options' => 'nullable|string|max:1000',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $fieldType = $validated['field_type'];
        $options = $this->parseOptions($validated['options'] ?? null, $fieldType);
        $key = TenantCustomField::normalizeKey($validated['label']);

        if (TenantCustomField::where('tenant_id', $user->tenant_id)->where('key', $key)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A custom field with this label already exists for your workspace.')
                ->withErrors(['label' => 'A custom field with this label already exists for your workspace.']);
        }

        TenantCustomField::create([
            'tenant_id' => $user->tenant_id,
            'label' => $validated['label'],
            'key' => $key,
            'field_type' => $fieldType,
            'options' => $options,
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
            'sort_order' => (TenantCustomField::where('tenant_id', $user->tenant_id)->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('crm.custom-fields.index')->with('success', 'Custom field created successfully.');
    }

    public function update(Request $request, TenantCustomField $field)
    {
        $this->ensureTenantFieldAccess($field);

        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'field_type' => ['required', Rule::in(array_keys(TenantCustomField::FIELD_TYPES))],
            'options' => 'nullable|string|max:1000',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        $key = TenantCustomField::normalizeKey($validated['label']);

        if (TenantCustomField::where('tenant_id', $field->tenant_id)->where('key', $key)->whereKeyNot($field->id)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A custom field with this label already exists for your workspace.')
                ->withErrors(['label' => 'A custom field with this label already exists for your workspace.']);
        }

        $field->update([
            'label' => $validated['label'],
            'key' => $key,
            'field_type' => $validated['field_type'],
            'options' => $this->parseOptions($validated['options'] ?? null, $validated['field_type']),
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : false,
        ]);

        return redirect()->route('crm.custom-fields.index')->with('success', 'Custom field updated successfully.');
    }

    public function destroy(TenantCustomField $field)
    {
        $this->ensureTenantFieldAccess($field);
        $field->delete();

        return redirect()->route('crm.custom-fields.index')->with('success', 'Custom field deleted successfully.');
    }

    private function ensureCanManageCustomFields(): void
    {
        $user = auth()->user();

        if (! ($user->hasRole('account-owner') || $user->hasRole('marketing-manager'))) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function ensureTenantFieldAccess(TenantCustomField $field): void
    {
        $this->ensureCanManageCustomFields();

        if ($field->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function parseOptions(?string $raw, string $fieldType): array
    {
        if ($fieldType !== 'select') {
            return [];
        }

        return collect(preg_split('/[\r\n,]+/', (string) $raw) ?: [])
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
