@extends('layouts.admin')

@section('title', 'Coupons')

@section('styles')
    <style>
        .team-table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .team-table {
            min-width: 940px;
        }
    </style>
@endsection

@section('content')
    @php
        $openCreateModal = $errors->any() || session('coupon_created');
        $selectedCodeMode = old('code_mode', 'manual');
        $tenantFunnels = \App\Models\Funnel::query()->where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get(['id', 'name']);
        $myCoupons = $tenantCoupons->getCollection()->filter(fn ($coupon) => $coupon->scope_type === \App\Models\Coupon::SCOPE_TENANT)->values();
        $platformCoupons = $tenantCoupons->getCollection()->filter(fn ($coupon) => $coupon->scope_type === \App\Models\Coupon::SCOPE_PLATFORM)->values();
    @endphp

    <div class="top-header">
        <h1>Coupons</h1>
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:14px 16px;border-radius:14px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;">
            <div style="font-weight:800;">Success</div>
            <div style="margin-top:4px;">{{ session('success') }}</div>
        </div>
    @endif

    <div class="actions" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
        <button type="button" id="openTenantCouponWizard" class="btn-create"><i class="fas fa-plus"></i> Create Tenant Coupon</button>
        <div style="padding:12px 16px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155;">
            Platform coupons assigned by superadmin also appear here.
        </div>
    </div>

    <div class="card">
        <div style="display:flex;flex-direction:column;gap:18px;">
            <div style="border:1px solid #e8eaf2;border-radius:18px;overflow:hidden;">
                <button type="button" data-coupon-collapse-trigger="my-coupons" aria-expanded="true" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:18px 20px;background:#fcfcff;border:none;cursor:pointer;">
                    <div style="text-align:left;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Tenant Coupons</div>
                        <div style="margin-top:4px;font-size:24px;font-weight:800;color:#240E35;">Coupons I Created</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:14px;">
                        <span style="padding:6px 12px;border-radius:999px;background:#efeaf8;color:#240E35;font-weight:700;">{{ $myCoupons->count() }}</span>
                        <span data-coupon-collapse-icon="my-coupons" style="font-size:22px;color:#475569;">-</span>
                    </div>
                </button>
                <div data-coupon-collapse-panel="my-coupons" style="padding:0 18px 18px;">
                    <div class="team-table-scroll">
                        <table class="team-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Discount</th>
                                    <th>Funnel Scope</th>
                                    <th>Ends</th>
                                    <th>Status</th>
                                    <th>Used</th>
                                    <th>Remaining</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myCoupons as $coupon)
                                    @php
                                        $maxTotalUses = $coupon->usage_mode === \App\Models\Coupon::USAGE_SINGLE ? 1 : ($coupon->max_total_uses ?: null);
                                        $remainingTotal = $maxTotalUses === null ? null : max(0, (int) $maxTotalUses - (int) $coupon->times_used);
                                        $perCustomerLimit = $coupon->usage_mode === \App\Models\Coupon::USAGE_SINGLE ? 1 : ($coupon->max_uses_per_user ?: null);
                                        $endsLabel = $coupon->ends_at ? $coupon->ends_at->format('M j, Y g:i A') : 'No end date';
                                    @endphp
                                    <tr>
                                        <td style="font-weight:800;letter-spacing:.08em;">{{ $coupon->code }}</td>
                                        <td>{{ $coupon->title ?: 'Untitled coupon' }}</td>
                                        <td>
                                            @if($coupon->discount_type === \App\Models\Coupon::DISCOUNT_PERCENT)
                                                {{ number_format((float) $coupon->discount_value, 2) }}%
                                            @else
                                                PHP {{ number_format((float) $coupon->discount_value, 2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($coupon->funnels->isEmpty())
                                                All funnels
                                            @else
                                                {{ $coupon->funnels->pluck('name')->implode(', ') }}
                                            @endif
                                        </td>
                                        <td style="white-space:nowrap;">
                                            {{ $endsLabel }}
                                        </td>
                                        <td>{{ ucfirst($coupon->status) }}</td>
                                        <td>{{ (int) $coupon->times_used }}</td>
                                        <td style="white-space:nowrap;">
                                            @if($remainingTotal === null)
                                                ∞
                                            @else
                                                {{ (int) $remainingTotal }}
                                            @endif
                                            @if($perCustomerLimit !== null)
                                                <div style="margin-top:4px;font-size:12px;color:#64748b;">Per customer: {{ (int) $perCustomerLimit }}</div>
                                            @else
                                                <div style="margin-top:4px;font-size:12px;color:#64748b;">Per customer: ∞</div>
                                            @endif
                                        </td>
                                        <td style="white-space:nowrap;">
                                            <a href="{{ route('coupons.edit', $coupon) }}" class="btn-create" style="padding:8px 12px;">Edit</a>
                                            <form method="POST" action="{{ route('coupons.destroy', $coupon) }}" style="display:inline-block;" onsubmit="return confirm('Delete this coupon?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-create" style="padding:8px 12px;background:#991b1b;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" style="text-align:center;color:#64748b;">You have not created any tenant coupons yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="border:1px solid #e8eaf2;border-radius:18px;overflow:hidden;">
                <button type="button" data-coupon-collapse-trigger="platform-coupons" aria-expanded="true" style="width:100%;display:flex;justify-content:space-between;align-items:center;padding:18px 20px;background:#fcfcff;border:none;cursor:pointer;">
                    <div style="text-align:left;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Platform Coupons</div>
                        <div style="margin-top:4px;font-size:24px;font-weight:800;color:#240E35;">Coupons From Superadmin</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:14px;">
                        <span style="padding:6px 12px;border-radius:999px;background:#efeaf8;color:#240E35;font-weight:700;">{{ $platformCoupons->count() }}</span>
                        <span data-coupon-collapse-icon="platform-coupons" style="font-size:22px;color:#475569;">-</span>
                    </div>
                </button>
                <div data-coupon-collapse-panel="platform-coupons" style="padding:0 18px 18px;">
                    <div class="team-table-scroll">
                        <table class="team-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Discount</th>
                                    <th>Funnel Scope</th>
                                    <th>Ends</th>
                                    <th>Status</th>
                                    <th>Used</th>
                                    <th>Remaining</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($platformCoupons as $coupon)
                                    @php
                                        $maxTotalUses = $coupon->usage_mode === \App\Models\Coupon::USAGE_SINGLE ? 1 : ($coupon->max_total_uses ?: null);
                                        $remainingTotal = $maxTotalUses === null ? null : max(0, (int) $maxTotalUses - (int) $coupon->times_used);
                                        $perCustomerLimit = $coupon->usage_mode === \App\Models\Coupon::USAGE_SINGLE ? 1 : ($coupon->max_uses_per_user ?: null);
                                        $endsLabel = $coupon->ends_at ? $coupon->ends_at->format('M j, Y g:i A') : 'No end date';
                                    @endphp
                                    <tr>
                                        <td style="font-weight:800;letter-spacing:.08em;">{{ $coupon->code }}</td>
                                        <td>{{ $coupon->title ?: 'Untitled coupon' }}</td>
                                        <td>
                                            @if($coupon->discount_type === \App\Models\Coupon::DISCOUNT_PERCENT)
                                                {{ number_format((float) $coupon->discount_value, 2) }}%
                                            @else
                                                PHP {{ number_format((float) $coupon->discount_value, 2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($coupon->funnels->isEmpty())
                                                All funnels
                                            @else
                                                {{ $coupon->funnels->pluck('name')->implode(', ') }}
                                            @endif
                                        </td>
                                        <td style="white-space:nowrap;">
                                            {{ $endsLabel }}
                                        </td>
                                        <td>{{ ucfirst($coupon->status) }}</td>
                                        <td>{{ (int) $coupon->times_used }}</td>
                                        <td style="white-space:nowrap;">
                                            @if($remainingTotal === null)
                                                ∞
                                            @else
                                                {{ (int) $remainingTotal }}
                                            @endif
                                            @if($perCustomerLimit !== null)
                                                <div style="margin-top:4px;font-size:12px;color:#64748b;">Per customer: {{ (int) $perCustomerLimit }}</div>
                                            @else
                                                <div style="margin-top:4px;font-size:12px;color:#64748b;">Per customer: ∞</div>
                                            @endif
                                        </td>
                                        <td style="color:#64748b;font-weight:700;">Read only</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" style="text-align:center;color:#64748b;">No superadmin coupons are assigned to this tenant yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:20px;">
            {{ $tenantCoupons->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <div id="tenantCouponWizardBackdrop" style="position:fixed;inset:0;background:rgba(15,23,42,.55);display:{{ $openCreateModal ? 'flex' : 'none' }};align-items:center;justify-content:center;padding:24px;z-index:1200;">
        <div style="width:min(920px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:24px;box-shadow:0 30px 80px rgba(15,23,42,.25);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding:24px 24px 12px;border-bottom:1px solid #eef2f7;">
                <div>
                    <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Tenant Coupon Wizard</div>
                    <h2 style="margin:6px 0 4px;color:#240E35;">Create a coupon step by step</h2>
                    <p style="margin:0;color:#475569;">Set the code, define the rules, then choose which funnels can use it.</p>
                </div>
                <button type="button" id="closeTenantCouponWizard" style="border:none;background:#f8fafc;border-radius:999px;width:40px;height:40px;font-size:20px;cursor:pointer;color:#334155;">X</button>
            </div>

            <form method="POST" action="{{ route('coupons.store') }}" id="tenantCouponWizardForm">
                @csrf
                <div style="padding:18px 24px 0;">
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
                        <div data-tenant-step-indicator="1" style="padding:12px 14px;border-radius:14px;background:#240E35;color:#fff;font-weight:700;">1. Identity</div>
                        <div data-tenant-step-indicator="2" style="padding:12px 14px;border-radius:14px;background:#f8fafc;color:#64748b;font-weight:700;">2. Rules</div>
                        <div data-tenant-step-indicator="3" style="padding:12px 14px;border-radius:14px;background:#f8fafc;color:#64748b;font-weight:700;">3. Funnel Access</div>
                    </div>
                </div>

                <div data-tenant-step="1" style="padding:24px;">
                    <div style="margin-bottom:18px;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Step 1</div>
                        <h3 style="margin:6px 0 4px;color:#240E35;">Create the coupon identity</h3>
                        <p style="margin:0;color:#475569;">Choose manual or auto-generated code, then name the coupon.</p>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
                        <div>
                            <label for="tenant_coupon_code" style="display:block;margin-bottom:8px;font-weight:bold;">Coupon Code</label>
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
                                    <button type="button" id="tenantGenerateCouponCodeBtn" class="btn-create" style="padding:8px 14px;display:none;">Generate Code</button>
                                </div>
                                <p id="tenantCouponCodeModeHint" style="margin:10px 0 0;color:#475569;font-size:12px;"></p>
                            </div>
                            <input type="text" name="code" id="tenant_coupon_code" maxlength="40" required value="{{ old('code') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;text-transform:uppercase;font-weight:700;letter-spacing:.08em;">
                            @error('code_mode')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                            @error('code')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="tenant_coupon_title" style="display:block;margin-bottom:8px;font-weight:bold;">Title</label>
                            <input type="text" name="title" id="tenant_coupon_title" maxlength="120" required value="{{ old('title') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            @error('title')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                            <label for="tenant_coupon_description" style="display:block;margin:16px 0 8px;font-weight:bold;">Description</label>
                            <textarea name="description" id="tenant_coupon_description" rows="5" required
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">{{ old('description') }}</textarea>
                            @error('description')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div data-tenant-step="2" style="display:none;padding:24px;">
                    <div style="margin-bottom:18px;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Step 2</div>
                        <h3 style="margin:6px 0 4px;color:#240E35;">Set the discount rules</h3>
                        <p style="margin:0;color:#475569;">Choose how the coupon discounts an order and how often it can be redeemed.</p>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;">
                        <div>
                            <label for="tenant_discount_type" style="display:block;margin-bottom:8px;font-weight:bold;">Discount Type</label>
                            <select name="discount_type" id="tenant_discount_type" required style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                                <option value="fixed" {{ old('discount_type', 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Percentage</option>
                            </select>
                            @error('discount_type')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="tenant_discount_value" style="display:block;margin-bottom:8px;font-weight:bold;">Discount Value</label>
                            <input type="number" step="0.01" min="0.01" name="discount_value" id="tenant_discount_value" required value="{{ old('discount_value') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            @error('discount_value')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="tenant_usage_mode" style="display:block;margin-bottom:8px;font-weight:bold;">Usage Mode</label>
                            <select name="usage_mode" id="tenant_usage_mode" required style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
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
                            <label for="tenant_max_total_uses" style="display:block;margin-bottom:8px;font-weight:bold;">Total redemptions (all customers)</label>
                            <input type="number" min="1" name="max_total_uses" id="tenant_max_total_uses" value="{{ old('max_total_uses') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            <div style="margin-top:6px;color:#475569;font-size:12px;">How many times this coupon can be redeemed <strong>in total</strong>. Leave blank for unlimited.</div>
                            @error('max_total_uses')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="tenant_max_uses_per_user" style="display:block;margin-bottom:8px;font-weight:bold;">Redemptions per customer</label>
                            <input type="number" min="1" name="max_uses_per_user" id="tenant_max_uses_per_user" value="{{ old('max_uses_per_user') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            <div style="margin-top:6px;color:#475569;font-size:12px;">How many times <strong>one customer</strong> can redeem this coupon. Leave blank for unlimited.</div>
                            @error('max_uses_per_user')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="tenant_starts_at" style="display:block;margin-bottom:8px;font-weight:bold;">Starts At</label>
                            <input type="datetime-local" name="starts_at" id="tenant_starts_at" value="{{ old('starts_at') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            @error('starts_at')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="tenant_ends_at" style="display:block;margin-bottom:8px;font-weight:bold;">Ends At</label>
                            <input type="datetime-local" name="ends_at" id="tenant_ends_at" value="{{ old('ends_at') }}"
                                style="width:100%;padding:12px 14px;border:1px solid #dbe2ea;border-radius:10px;">
                            @error('ends_at')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div data-tenant-step="3" style="display:none;padding:24px;">
                    <div style="margin-bottom:18px;">
                        <div style="font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b;">Step 3</div>
                        <h3 style="margin:6px 0 4px;color:#240E35;">Choose the funnel access</h3>
                        <p style="margin:0;color:#475569;">Select specific funnels or leave everything unchecked to make it available to all funnels.</p>
                    </div>
                    <div style="padding:14px;border:1px solid #e2e8f0;border-radius:14px;max-height:320px;overflow:auto;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;">
                        @forelse($tenantFunnels as $funnel)
                            <label style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:10px;background:#f8fafc;">
                                <input type="checkbox" name="funnel_ids[]" value="{{ $funnel->id }}" {{ in_array($funnel->id, old('funnel_ids', [])) ? 'checked' : '' }}>
                                <span>{{ $funnel->name }}</span>
                            </label>
                        @empty
                            <div style="color:#64748b;">No funnels yet. Leave this empty and the coupon will work for future funnels.</div>
                        @endforelse
                    </div>
                    @error('funnel_ids')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                    @error('funnel_ids.*')<div style="margin-top:6px;color:#b91c1c;font-size:12px;">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:20px 24px;border-top:1px solid #eef2f7;background:#fcfcfd;border-radius:0 0 24px 24px;">
                    <button type="button" id="tenantCouponPrevStep" class="btn-create" style="background:#64748b;display:none;">Back</button>
                    <div style="display:flex;gap:12px;margin-left:auto;">
                        <button type="button" id="tenantCouponNextStep" class="btn-create">Next</button>
                        <button type="submit" id="tenantCouponSubmit" class="btn-create" style="display:none;">Create Coupon</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const backdrop = document.getElementById('tenantCouponWizardBackdrop');
            const openBtn = document.getElementById('openTenantCouponWizard');
            const closeBtn = document.getElementById('closeTenantCouponWizard');
            const prevBtn = document.getElementById('tenantCouponPrevStep');
            const nextBtn = document.getElementById('tenantCouponNextStep');
            const submitBtn = document.getElementById('tenantCouponSubmit');
            const steps = Array.from(document.querySelectorAll('[data-tenant-step]'));
            const indicators = Array.from(document.querySelectorAll('[data-tenant-step-indicator]'));
            const codeInput = document.getElementById('tenant_coupon_code');
            const generateBtn = document.getElementById('tenantGenerateCouponCodeBtn');
            const hint = document.getElementById('tenantCouponCodeModeHint');
            const modeInputs = document.querySelectorAll('input[name="code_mode"]');
            let currentStep = {{ $errors->has('funnel_ids') || $errors->has('funnel_ids.*') ? 3 : (($errors->has('discount_type') || $errors->has('discount_value') || $errors->has('usage_mode') || $errors->has('max_total_uses') || $errors->has('max_uses_per_user') || $errors->has('starts_at') || $errors->has('ends_at')) ? 2 : 1) }};

            const openModal = () => backdrop.style.display = 'flex';
            const closeModal = () => backdrop.style.display = 'none';
            const sanitize = (value) => String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 40);
            const randomCode = () => 'CPN' + Math.random().toString(36).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 8);
            const currentMode = () => (Array.from(modeInputs).find((input) => input.checked) || {}).value || 'manual';
            const usageMode = document.getElementById('tenant_usage_mode');
            const totalUses = document.getElementById('tenant_max_total_uses');
            const usesPerUser = document.getElementById('tenant_max_uses_per_user');
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

            Array.from(document.querySelectorAll('[data-coupon-collapse-trigger]')).forEach((trigger) => {
                trigger.addEventListener('click', function () {
                    const key = trigger.getAttribute('data-coupon-collapse-trigger');
                    const panel = document.querySelector('[data-coupon-collapse-panel="' + key + '"]');
                    const icon = document.querySelector('[data-coupon-collapse-icon="' + key + '"]');
                    const isOpen = trigger.getAttribute('aria-expanded') === 'true';
                    trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                    if (panel) {
                        panel.style.display = isOpen ? 'none' : 'block';
                    }
                    if (icon) {
                        icon.textContent = isOpen ? '+' : '-';
                    }
                });
            });

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

            syncCodeMode();
            syncUsageFields();
            renderStep();
            @if($openCreateModal)
                openModal();
            @endif
            if (usageMode) usageMode.addEventListener('change', syncUsageFields);
        });
    </script>
@endsection
