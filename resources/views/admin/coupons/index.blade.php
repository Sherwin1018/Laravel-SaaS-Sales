@extends('layouts.admin')

@section('title', 'Platform Coupons')

@section('styles')
    <style>
        .sa-table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sa-table {
            min-width: 980px;
        }
    </style>
@endsection

@section('content')
    @php
        $openCreateModal = $errors->any() || session('coupon_created');
        $selectedCodeMode = old('code_mode', 'manual');
    @endphp

    <div class="top-header">
        <h1>Platform Coupons</h1>
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:14px 16px;border-radius:14px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;">
            <div style="font-weight:800;">Success</div>
            <div style="margin-top:4px;">{{ session('success') }}</div>
        </div>
    @endif

    <div class="actions" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
        <button type="button" id="openAdminCouponWizard" class="btn-create btn-create--icon-expand" aria-label="Create Coupon"><i class="fas fa-plus"></i><span class="btn-create__label">Create Coupon</span></button>
        <div class="search-box">
            <input type="text" id="searchInput" value="{{ request('search') }}" placeholder="🔍 Search coupon code or title"
                style="padding:10px 12px;border:1px solid var(--theme-border, #E6E1EF);border-radius:6px;width:300px;">
        </div>
    </div>

    <div class="card">
        <h3>Assigned Platform Coupons</h3>
        <div class="sa-table-scroll">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Discount</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th>Assigned Tenants</th>
                        <th>Used</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.coupons._rows', ['coupons' => $coupons])
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;" id="paginationLinks">
            {{ $coupons->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <div id="adminCouponWizardBackdrop" style="position:fixed;inset:0;background:rgba(15,23,42,.55);display:{{ $openCreateModal ? 'flex' : 'none' }};align-items:center;justify-content:center;padding:24px;z-index:1200;">
        <div style="width:min(920px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:24px;box-shadow:0 30px 80px rgba(15,23,42,.25);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding:24px 24px 12px;border-bottom:1px solid #eef2f7;">
                <div>
                    <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Platform Coupon Wizard</div>
                    <h2 style="margin:6px 0 4px;color:#240E35;">Create a coupon step by step</h2>
                    <p style="margin:0;color:#475569;">Set the code, define the discount, then choose which tenants will receive it.</p>
                </div>
                <button type="button" id="closeAdminCouponWizard" style="border:none;background:#f8fafc;border-radius:999px;width:40px;height:40px;font-size:20px;cursor:pointer;color:#334155;">X</button>
            </div>

            <form method="POST" action="{{ route('admin.coupons.store') }}" id="adminCouponWizardForm">
                @csrf
                <div style="padding:18px 24px 0;">
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
                        <div data-admin-step-indicator="1" style="padding:12px 14px;border-radius:14px;background:#240E35;color:#fff;font-weight:700;">1. Identity</div>
                        <div data-admin-step-indicator="2" style="padding:12px 14px;border-radius:14px;background:#f8fafc;color:#64748b;font-weight:700;">2. Rules</div>
                        <div data-admin-step-indicator="3" style="padding:12px 14px;border-radius:14px;background:#f8fafc;color:#64748b;font-weight:700;">3. Assign Tenants</div>
                    </div>
                </div>

                <div data-admin-step="1" style="padding:24px;">
                    <div style="margin-bottom:18px;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Step 1</div>
                        <h3 style="margin:6px 0 4px;color:#240E35;">Create the coupon identity</h3>
                        <p style="margin:0;color:#475569;">Choose manual or auto-generated code, then give the coupon a clear title.</p>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
                        <div>
                            <label for="admin_coupon_code" style="display:block;margin-bottom:8px;font-weight:bold;">Coupon Code</label>
                            <div style="padding:14px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc;margin-bottom:12px;">
                                <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                                    <label style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#fff;border:1px solid #dbe2ea;">
                                        <input type="radio" name="code_mode" value="manual" {{ $selectedCodeMode === 'manual' ? 'checked' : '' }}>
                                        <span style="font-weight:700;">Manual</span>
                                    </label>
                                    <label style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:999px;background:#fff;border:1px solid #dbe2ea;">
                                        <input type="radio" name="code_mode" value="auto" {{ $selectedCodeMode === 'auto' ? 'checked' : '' }}>
                                        <span style="font-weight:700;">Auto Generate</span>
                                    </label>
                                    <button type="button" id="adminGenerateCouponCodeBtn" class="btn-create" style="padding:8px 14px;display:none;">Generate Code</button>
                                </div>
                                <p id="adminCouponCodeModeHint" style="margin:10px 0 0;color:#475569;font-size:12px;"></p>
                            </div>
                            <input type="text" name="code" id="admin_coupon_code" maxlength="40" required value="{{ old('code') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;text-transform:uppercase;font-weight:700;letter-spacing:.08em;">
                            @error('code_mode')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                            @error('code')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="admin_coupon_title" style="display:block;margin-bottom:8px;font-weight:bold;">Title</label>
                            <input type="text" name="title" id="admin_coupon_title" maxlength="120" required value="{{ old('title') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            @error('title')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                            <label for="admin_coupon_description" style="display:block;margin:16px 0 8px;font-weight:bold;">Description</label>
                            <textarea name="description" id="admin_coupon_description" rows="5" required
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">{{ old('description') }}</textarea>
                            @error('description')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div data-admin-step="2" style="display:none;padding:24px;">
                    <div style="margin-bottom:18px;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Step 2</div>
                        <h3 style="margin:6px 0 4px;color:#240E35;">Set the discount rules</h3>
                        <p style="margin:0;color:#475569;">Choose the discount style, usage limits, and active dates.</p>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;">
                        <div>
                            <label for="admin_discount_type" style="display:block;margin-bottom:8px;font-weight:bold;">Discount Type</label>
                            <select name="discount_type" id="admin_discount_type" required style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                                <option value="fixed" {{ old('discount_type', 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Percentage</option>
                            </select>
                            @error('discount_type')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="admin_discount_value" style="display:block;margin-bottom:8px;font-weight:bold;">Discount Value</label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" id="admin_discount_value" required value="{{ old('discount_value') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            @error('discount_value')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="admin_usage_mode" style="display:block;margin-bottom:8px;font-weight:bold;">Usage Mode</label>
                            <select name="usage_mode" id="admin_usage_mode" required style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                                <option value="single_use" {{ old('usage_mode', 'single_use') === 'single_use' ? 'selected' : '' }}>Single Use</option>
                                <option value="multi_use" {{ old('usage_mode') === 'multi_use' ? 'selected' : '' }}>Multi Use</option>
                            </select>
                            <div style="margin-top:6px;color:#475569;font-size:12px;">
                                <strong>Single Use</strong> = redeemable once total (and once per customer). <strong>Multi Use</strong> = set limits below.
                            </div>
                            @error('usage_mode')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:16px;">
                        <div>
                            <label for="admin_max_total_uses" style="display:block;margin-bottom:8px;font-weight:bold;">Total redemptions (all customers)</label>
                            <input type="number" min="1" name="max_total_uses" id="admin_max_total_uses" value="{{ old('max_total_uses') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            <div style="margin-top:6px;color:#475569;font-size:12px;">How many times this coupon can be redeemed <strong>in total</strong>. Leave blank for unlimited.</div>
                            @error('max_total_uses')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="admin_max_uses_per_user" style="display:block;margin-bottom:8px;font-weight:bold;">Redemptions per customer</label>
                            <input type="number" min="1" name="max_uses_per_user" id="admin_max_uses_per_user" value="{{ old('max_uses_per_user') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            <div style="margin-top:6px;color:#475569;font-size:12px;">How many times <strong>one customer</strong> can redeem this coupon. Leave blank for unlimited.</div>
                            @error('max_uses_per_user')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="admin_starts_at" style="display:block;margin-bottom:8px;font-weight:bold;">Starts At</label>
                            <input type="datetime-local" name="starts_at" id="admin_starts_at" value="{{ old('starts_at') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            @error('starts_at')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="admin_ends_at" style="display:block;margin-bottom:8px;font-weight:bold;">Ends At</label>
                            <input type="datetime-local" name="ends_at" id="admin_ends_at" value="{{ old('ends_at') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            @error('ends_at')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div data-admin-step="3" style="display:none;padding:24px;">
                    <div style="margin-bottom:18px;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Step 3</div>
                        <h3 style="margin:6px 0 4px;color:#240E35;">Assign the tenants</h3>
                        <p style="margin:0;color:#475569;">Select which tenants should see this platform coupon inside their workspace.</p>
                    </div>
                    <div style="padding:14px;border:1px solid #e2e8f0;border-radius:14px;max-height:320px;overflow:auto;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;">
                        @foreach(\App\Models\Tenant::query()->orderBy('company_name')->get(['id','company_name']) as $tenant)
                            <label style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:10px;background:#f8fafc;">
                                <input type="checkbox" name="tenant_ids[]" value="{{ $tenant->id }}" {{ in_array($tenant->id, old('tenant_ids', [])) ? 'checked' : '' }}>
                                <span>{{ $tenant->company_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('tenant_ids')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                    @error('tenant_ids.*')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:20px 24px;border-top:1px solid #eef2f7;background:#fcfcfd;border-radius:0 0 24px 24px;">
                    <button type="button" id="adminCouponPrevStep" class="btn-create" style="background:#64748b;display:none;">Back</button>
                    <div style="display:flex;gap:12px;margin-left:auto;">
                        <button type="button" id="adminCouponNextStep" class="btn-create">Next</button>
                        <button type="submit" id="adminCouponSubmit" class="btn-create" style="display:none;">Create Coupon</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const backdrop = document.getElementById('adminCouponWizardBackdrop');
            const openBtn = document.getElementById('openAdminCouponWizard');
            const closeBtn = document.getElementById('closeAdminCouponWizard');
            const prevBtn = document.getElementById('adminCouponPrevStep');
            const nextBtn = document.getElementById('adminCouponNextStep');
            const submitBtn = document.getElementById('adminCouponSubmit');
            const steps = Array.from(document.querySelectorAll('[data-admin-step]'));
            const indicators = Array.from(document.querySelectorAll('[data-admin-step-indicator]'));
            const codeInput = document.getElementById('admin_coupon_code');
            const generateBtn = document.getElementById('adminGenerateCouponCodeBtn');
            const hint = document.getElementById('adminCouponCodeModeHint');
            const modeInputs = document.querySelectorAll('input[name="code_mode"]');
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('tableBody');
            const paginationLinks = document.getElementById('paginationLinks');
            let currentStep = {{ $errors->has('tenant_ids') || $errors->has('tenant_ids.*') ? 3 : (($errors->has('discount_type') || $errors->has('discount_value') || $errors->has('usage_mode') || $errors->has('max_total_uses') || $errors->has('max_uses_per_user') || $errors->has('starts_at') || $errors->has('ends_at')) ? 2 : 1) }};
            let searchTimeout = null;

            const openModal = () => backdrop.style.display = 'flex';
            const closeModal = () => backdrop.style.display = 'none';
            const sanitize = (value) => String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 40);
            const randomCode = () => 'CPN' + Math.random().toString(36).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8);
            const usageMode = document.getElementById('admin_usage_mode');
            const totalUses = document.getElementById('admin_max_total_uses');
            const usesPerUser = document.getElementById('admin_max_uses_per_user');
            const currentMode = () => (Array.from(modeInputs).find((input) => input.checked) || {}).value || 'manual';
            const validateCurrentStep = () => {
                const activeStep = steps[currentStep - 1];
                if (!activeStep) return true;
                const fields = Array.from(activeStep.querySelectorAll('input, select, textarea'));
                for (const field of fields) {
                    if (field.disabled || field.type === 'hidden') continue;
                    if (field.name === 'code' && currentMode() === 'auto') continue;
                    if (typeof field.reportValidity === 'function' && !field.reportValidity()) {
                        field.focus();
                        return false;
                    }
                }
                return true;
            };

            const syncCodeMode = () => {
                codeInput.value = sanitize(codeInput.value);
                const isAuto = currentMode() === 'auto';
                codeInput.readOnly = isAuto;
                codeInput.style.backgroundColor = isAuto ? '#f8fafc' : '#ffffff';
                generateBtn.style.display = isAuto ? 'inline-flex' : 'none';
                hint.textContent = isAuto
                    ? 'Auto mode creates a unique uppercase code. Use "Generate Code" if you want a preview right now.'
                    : 'Manual mode lets you type your own uppercase letters-and-numbers code.';
            };

            const renderStep = () => {
                steps.forEach((step, index) => step.style.display = index + 1 === currentStep ? 'block' : 'none');
                indicators.forEach((indicator, index) => {
                    const active = index + 1 === currentStep;
                    indicator.style.background = active ? '#240E35' : '#f8fafc';
                    indicator.style.color = active ? '#ffffff' : '#64748b';
                });
                prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-flex';
                nextBtn.style.display = currentStep === steps.length ? 'none' : 'inline-flex';
                submitBtn.style.display = currentStep === steps.length ? 'inline-flex' : 'none';
            };

            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            backdrop.addEventListener('click', function (event) {
                if (event.target === backdrop) closeModal();
            });
            prevBtn.addEventListener('click', function () {
                currentStep = Math.max(1, currentStep - 1);
                renderStep();
            });
            nextBtn.addEventListener('click', function () {
                if (!validateCurrentStep()) {
                    return;
                }
                currentStep = Math.min(steps.length, currentStep + 1);
                renderStep();
            });
            generateBtn.addEventListener('click', function () {
                codeInput.value = randomCode();
                syncCodeMode();
            });
            codeInput.addEventListener('input', syncCodeMode);
            modeInputs.forEach((input) => input.addEventListener('change', function () {
                if (currentMode() === 'auto' && sanitize(codeInput.value) === '') {
                    codeInput.value = randomCode();
                }
                syncCodeMode();
            }));

            if (searchInput && tableBody && paginationLinks) {
                searchInput.addEventListener('keyup', function () {
                    clearTimeout(searchTimeout);
                    const query = searchInput.value;
                    if (query.length > 0 && query.length < 2) return;

                    searchTimeout = setTimeout(() => {
                        fetch(`{{ route('admin.coupons.index') }}?search=${encodeURIComponent(query)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then((response) => response.text())
                        .then((html) => {
                            tableBody.innerHTML = html;
                            if (query.length > 0) {
                                paginationLinks.style.display = 'none';
                            } else {
                                paginationLinks.style.display = 'block';
                                window.location.reload();
                            }
                        });
                    }, 300);
                });
            }

            syncCodeMode();
            const syncUsageFields = () => {
                if (!usageMode || !totalUses || !usesPerUser) return;
                const isSingle = String(usageMode.value || '') === 'single_use';
                if (isSingle) {
                    totalUses.value = '1';
                    usesPerUser.value = '1';
                }
                totalUses.disabled = isSingle;
                usesPerUser.disabled = isSingle;
                totalUses.style.backgroundColor = isSingle ? '#f8fafc' : '#ffffff';
                usesPerUser.style.backgroundColor = isSingle ? '#f8fafc' : '#ffffff';
            };
            syncUsageFields();
            renderStep();
            @if($openCreateModal)
                openModal();
            @endif
            if (usageMode) usageMode.addEventListener('change', syncUsageFields);
        });
    </script>
@endsection
