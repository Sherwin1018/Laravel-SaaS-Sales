<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Funnel;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(CouponService $coupons)
    {
        $tenantId = (int) auth()->user()->tenant_id;

        $tenantCoupons = $coupons->visibleToTenant($tenantId)->paginate(12);
        $tenantCoupons->getCollection()->transform(fn (Coupon $coupon) => $coupons->syncCouponStatus($coupon));

        return view('coupons.index', compact('tenantCoupons'));
    }

    public function create()
    {
        $coupon = new Coupon([
            'status' => Coupon::STATUS_ACTIVE,
            'discount_type' => Coupon::DISCOUNT_FIXED,
            'usage_mode' => Coupon::USAGE_SINGLE,
        ]);

        return view('coupons.create', [
            'coupon' => $coupon,
            'funnels' => Funnel::query()
                ->where('tenant_id', auth()->user()->tenant_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'selectedFunnelIds' => [],
            'codeMode' => 'manual',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCoupon($request);
        $couponCode = $this->resolveCouponCode($validated);

        $coupon = Coupon::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            'scope_type' => Coupon::SCOPE_TENANT,
            'code' => $couponCode,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => Coupon::STATUS_ACTIVE,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'usage_mode' => $validated['usage_mode'],
            'max_total_uses' => $this->nullableInt($validated['max_total_uses'] ?? null),
            'max_uses_per_user' => $this->nullableInt($validated['max_uses_per_user'] ?? null),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        $coupon->funnels()->sync($validated['funnel_ids'] ?? []);

        return redirect()->route('coupons.index')->with('success', 'Coupon successfully created.');
    }

    public function edit(Coupon $coupon)
    {
        $this->ensureTenantCoupon($coupon);
        $coupon->load('funnels:id');

        return view('coupons.edit', [
            'coupon' => $coupon,
            'funnels' => Funnel::query()
                ->where('tenant_id', auth()->user()->tenant_id)
                ->orderBy('name')
                ->get(['id', 'name']),
            'selectedFunnelIds' => $coupon->funnels->pluck('id')->all(),
            'codeMode' => old('code_mode', 'manual'),
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->ensureTenantCoupon($coupon);
        $validated = $this->validateCoupon($request, $coupon->id, true);
        $couponCode = $this->resolveCouponCode($validated, $coupon->id);

        $coupon->update([
            'code' => $couponCode,
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'usage_mode' => $validated['usage_mode'],
            'max_total_uses' => $this->nullableInt($validated['max_total_uses'] ?? null),
            'max_uses_per_user' => $this->nullableInt($validated['max_uses_per_user'] ?? null),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        $coupon->funnels()->sync($validated['funnel_ids'] ?? []);

        return redirect()->route('coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        $this->ensureTenantCoupon($coupon);
        $coupon->delete();

        return redirect()->route('coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    private function validateCoupon(Request $request, ?int $couponId = null, bool $includeStatus = false): array
    {
        $request->merge([
            'code' => Coupon::normalizeCode($request->input('code')),
        ]);

        $tenantId = (int) auth()->user()->tenant_id;
        $statusRule = [Rule::in([
            Coupon::STATUS_ACTIVE,
            Coupon::STATUS_INACTIVE,
            Coupon::STATUS_EXPIRED,
            Coupon::STATUS_EXHAUSTED,
        ])];

        return $request->validate([
            'code_mode' => ['required', Rule::in(['manual', 'auto'])],
            'code' => [
                Rule::requiredIf(fn () => $request->input('code_mode', 'manual') === 'manual'),
                'string',
                'max:40',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'title' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:2000',
            'discount_type' => ['required', Rule::in([Coupon::DISCOUNT_FIXED, Coupon::DISCOUNT_PERCENT])],
            'discount_value' => 'required|numeric|min:0.01|max:999999.99',
            'usage_mode' => ['required', Rule::in([Coupon::USAGE_SINGLE, Coupon::USAGE_MULTI])],
            'max_total_uses' => 'nullable|integer|min:1|max:1000000',
            'max_uses_per_user' => 'nullable|integer|min:1|max:1000000',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'funnel_ids' => 'nullable|array',
            'funnel_ids.*' => [
                'integer',
                Rule::exists('funnels', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'status' => $includeStatus ? array_merge(['required'], $statusRule) : 'nullable',
        ]);
    }

    private function ensureTenantCoupon(Coupon $coupon): void
    {
        abort_unless(
            $coupon->scope_type === Coupon::SCOPE_TENANT
            && (int) $coupon->tenant_id === (int) auth()->user()->tenant_id,
            404
        );
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function resolveCouponCode(array $validated, ?int $ignoreCouponId = null): string
    {
        $mode = $validated['code_mode'] ?? 'manual';
        if ($mode === 'auto') {
            return $this->generateUniqueCouponCode($ignoreCouponId);
        }

        return Coupon::normalizeCode($validated['code'] ?? '');
    }

    private function generateUniqueCouponCode(?int $ignoreCouponId = null): string
    {
        do {
            $code = 'CPN' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $query = Coupon::query()->where('code', $code);
            if ($ignoreCouponId !== null) {
                $query->where('id', '!=', $ignoreCouponId);
            }
        } while ($query->exists());

        return $code;
    }
}
