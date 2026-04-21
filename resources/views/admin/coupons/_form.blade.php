<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
    <div style="margin-bottom:20px;">
        <label for="code" style="display:block;margin-bottom:8px;font-weight:bold;">Coupon Code</label>
        @php $selectedCodeMode = old('code_mode', $codeMode ?? 'manual'); @endphp
        <div style="padding:14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:12px;background:#f8fafc;margin-bottom:12px;">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <label style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#ffffff;border:1px solid #dbe2ea;cursor:pointer;">
                    <input type="radio" name="code_mode" value="manual" {{ $selectedCodeMode === 'manual' ? 'checked' : '' }}>
                    <span style="font-weight:700;color:#0f172a;">Manual</span>
                </label>
                <label style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#ffffff;border:1px solid #dbe2ea;cursor:pointer;">
                    <input type="radio" name="code_mode" value="auto" {{ $selectedCodeMode === 'auto' ? 'checked' : '' }}>
                    <span style="font-weight:700;color:#0f172a;">Auto Generate</span>
                </label>
                <button type="button" id="generateCouponCodeBtn" class="btn-create" style="padding:8px 14px;display:none;">Generate Code</button>
            </div>
            <p id="couponCodeModeHint" style="margin:10px 0 0;color:#475569;font-size:12px;"></p>
        </div>
            <input type="text" name="code" id="code" required maxlength="40"
                style="width:100%;padding:12px 14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:10px;text-transform:uppercase;font-weight:700;letter-spacing:.08em;"
                value="{{ old('code', $coupon->code ?? '') }}">
        @error('code_mode')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
        @error('code')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>

    <div style="margin-bottom:20px;">
        <label for="title" style="display:block;margin-bottom:8px;font-weight:bold;">Title</label>
            <input type="text" name="title" id="title" required maxlength="120"
                style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
                value="{{ old('title', $coupon->title ?? '') }}">
        @error('title')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>
</div>

<div style="margin-bottom:20px;">
    <label for="description" style="display:block;margin-bottom:8px;font-weight:bold;">Description</label>
        <textarea name="description" id="description" rows="3" required
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">{{ old('description', $coupon->description ?? '') }}</textarea>
    @error('description')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
</div>

<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;">
    <div style="margin-bottom:20px;">
        <label for="discount_type" style="display:block;margin-bottom:8px;font-weight:bold;">Discount Type</label>
        <select name="discount_type" id="discount_type" required style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
            <option value="fixed" {{ old('discount_type', $coupon->discount_type ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
            <option value="percent" {{ old('discount_type', $coupon->discount_type ?? 'fixed') === 'percent' ? 'selected' : '' }}>Percentage</option>
        </select>
        @error('discount_type')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>

    <div style="margin-bottom:20px;">
        <label for="discount_value" style="display:block;margin-bottom:8px;font-weight:bold;">Discount Value</label>
        <input type="number" step="0.01" min="0.01" name="discount_value" id="discount_value" required
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('discount_value', isset($coupon->discount_value) ? number_format((float) $coupon->discount_value, 2, '.', '') : '') }}">
        @error('discount_value')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>

    <div style="margin-bottom:20px;">
        <label for="usage_mode" style="display:block;margin-bottom:8px;font-weight:bold;">Usage Mode</label>
        <select name="usage_mode" id="usage_mode" required style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
            <option value="single_use" {{ old('usage_mode', $coupon->usage_mode ?? 'single_use') === 'single_use' ? 'selected' : '' }}>Single Use</option>
            <option value="multi_use" {{ old('usage_mode', $coupon->usage_mode ?? 'single_use') === 'multi_use' ? 'selected' : '' }}>Multi Use</option>
        </select>
        <p style="margin:6px 0 0;color:#475569;font-size:12px;">
            <strong>Single Use</strong> = redeemable once total (and once per customer). <strong>Multi Use</strong> = set limits below.
        </p>
        @error('usage_mode')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
    <div style="margin-bottom:20px;">
        <label for="max_total_uses" style="display:block;margin-bottom:8px;font-weight:bold;">Total redemptions (all customers)</label>
        <input type="number" min="1" name="max_total_uses" id="max_total_uses"
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('max_total_uses', $coupon->max_total_uses ?? '') }}">
        <p style="margin:6px 0 0;color:#475569;font-size:12px;">How many times this coupon can be redeemed <strong>in total</strong>. Leave blank for unlimited.</p>
        @error('max_total_uses')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>

    <div style="margin-bottom:20px;">
        <label for="max_uses_per_user" style="display:block;margin-bottom:8px;font-weight:bold;">Redemptions per customer</label>
        <input type="number" min="1" name="max_uses_per_user" id="max_uses_per_user"
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('max_uses_per_user', $coupon->max_uses_per_user ?? '') }}">
        <p style="margin:6px 0 0;color:#475569;font-size:12px;">How many times <strong>one customer</strong> can redeem this coupon. Leave blank for unlimited.</p>
        @error('max_uses_per_user')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
    <div style="margin-bottom:20px;">
        <label for="starts_at" style="display:block;margin-bottom:8px;font-weight:bold;">Starts At</label>
        <input type="datetime-local" name="starts_at" id="starts_at"
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}">
        @error('starts_at')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>

    <div style="margin-bottom:20px;">
        <label for="ends_at" style="display:block;margin-bottom:8px;font-weight:bold;">Ends At</label>
        <input type="datetime-local" name="ends_at" id="ends_at"
            style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;"
            value="{{ old('ends_at', optional($coupon->ends_at)->format('Y-m-d\TH:i')) }}">
        @error('ends_at')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>
</div>

@if($isEdit)
    <div style="margin-bottom:20px;">
        <label for="status" style="display:block;margin-bottom:8px;font-weight:bold;">Status</label>
        <select name="status" id="status" style="width:100%;padding:10px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;">
            @foreach([\App\Models\Coupon::STATUS_ACTIVE, \App\Models\Coupon::STATUS_INACTIVE, \App\Models\Coupon::STATUS_EXPIRED, \App\Models\Coupon::STATUS_EXHAUSTED] as $statusValue)
                <option value="{{ $statusValue }}" {{ old('status', $coupon->status ?? 'active') === $statusValue ? 'selected' : '' }}>{{ ucfirst($statusValue) }}</option>
            @endforeach
        </select>
        @error('status')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    </div>
@endif

<div style="margin-bottom:24px;">
    <label style="display:block;margin-bottom:10px;font-weight:bold;">Assigned Tenants</label>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;max-height:280px;overflow:auto;padding:14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:8px;">
        @foreach($tenants as $tenant)
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="tenant_ids[]" value="{{ $tenant->id }}"
                    {{ in_array($tenant->id, old('tenant_ids', $selectedTenantIds ?? [])) ? 'checked' : '' }}>
                <span>{{ $tenant->company_name }}</span>
            </label>
        @endforeach
    </div>
    @error('tenant_ids')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
    @error('tenant_ids.*')<span style="color:red;font-size:12px;">{{ $message }}</span>@enderror
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const codeInput = document.getElementById('code');
        const generateBtn = document.getElementById('generateCouponCodeBtn');
        const hint = document.getElementById('couponCodeModeHint');
        const modeInputs = document.querySelectorAll('input[name="code_mode"]');
        const usageMode = document.getElementById('usage_mode');
        const totalUses = document.getElementById('max_total_uses');
        const usesPerUser = document.getElementById('max_uses_per_user');

        if (!codeInput || !generateBtn || !hint || !modeInputs.length) {
            return;
        }

        const sanitize = (value) => String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 40);
        const randomCode = () => 'CPN' + Math.random().toString(36).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8);
        const currentMode = () => {
            const selected = Array.from(modeInputs).find((input) => input.checked);
            return selected ? selected.value : 'manual';
        };
        const syncState = () => {
            codeInput.value = sanitize(codeInput.value);
            const isAuto = currentMode() === 'auto';
            codeInput.readOnly = isAuto;
            codeInput.style.backgroundColor = isAuto ? '#f8fafc' : '#ffffff';
            generateBtn.style.display = isAuto ? 'inline-flex' : 'none';
            hint.textContent = isAuto
                ? 'Auto mode creates a unique uppercase code. Use "Generate Code" if you want to preview one now.'
                : 'Manual mode lets you type your own uppercase letters-and-numbers code.';
        };

        generateBtn.addEventListener('click', function () {
            codeInput.value = randomCode();
            syncState();
        });

        codeInput.addEventListener('input', syncState);
        modeInputs.forEach((input) => {
            input.addEventListener('change', function () {
                if (currentMode() === 'auto' && sanitize(codeInput.value) === '') {
                    codeInput.value = randomCode();
                }
                syncState();
            });
        });

        const syncUsageFields = () => {
            if (!usageMode || !totalUses || !usesPerUser) return;
            const mode = String(usageMode.value || '');
            const isSingle = mode === 'single_use';
            if (isSingle) {
                totalUses.value = '1';
                usesPerUser.value = '1';
            }
            totalUses.disabled = isSingle;
            usesPerUser.disabled = isSingle;
            totalUses.style.backgroundColor = isSingle ? '#f8fafc' : '#ffffff';
            usesPerUser.style.backgroundColor = isSingle ? '#f8fafc' : '#ffffff';
        };

        syncState();
        syncUsageFields();
        if (usageMode) usageMode.addEventListener('change', syncUsageFields);
    });
</script>
