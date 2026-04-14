<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Tenant;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCouponController extends Controller
{
    public function index(Request $request, CouponService $couponService)
    {
        $query = Coupon::query()
            ->with(['assignedTenants:id,company_name', 'creator:id,name'])
            ->platform()
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $coupons = $query->paginate(12);
        $coupons->getCollection()->transform(fn (Coupon $coupon) => $couponService->syncCouponStatus($coupon));

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $coupon = new Coupon([
            'status' => Coupon::STATUS_ACTIVE,
            'discount_type' => Coupon::DISCOUNT_FIXED,
            'usage_mode' => Coupon::USAGE_SINGLE,
        ]);

        return view('admin.coupons.create', [
            'coupon' => $coupon,
            'tenants' => Tenant::query()->orderBy('company_name')->get(['id', 'company_name']),
            'selectedTenantIds' => [],
            'codeMode' => 'manual',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCoupon($request);
        $couponCode = $this->resolveCouponCode($validated);

        $coupon = Coupon::create([
            'tenant_id' => null,
            'created_by' => auth()->id(),
            'scope_type' => Coupon::SCOPE_PLATFORM,
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

        $coupon->assignedTenants()->sync($validated['tenant_ids']);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon successfully created.');
    }

    public function edit(Coupon $coupon)
    {
        abort_unless($coupon->scope_type === Coupon::SCOPE_PLATFORM, 404);

        $coupon->load('assignedTenants:id');

        return view('admin.coupons.edit', [
            'coupon' => $coupon,
            'tenants' => Tenant::query()->orderBy('company_name')->get(['id', 'company_name']),
            'selectedTenantIds' => $coupon->assignedTenants->pluck('id')->all(),
            'codeMode' => old('code_mode', 'manual'),
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        abort_unless($coupon->scope_type === Coupon::SCOPE_PLATFORM, 404);

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

        $coupon->assignedTenants()->sync($validated['tenant_ids']);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        abort_unless($coupon->scope_type === Coupon::SCOPE_PLATFORM, 404);

        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    private function validateCoupon(Request $request, ?int $couponId = null, bool $includeStatus = false): array
    {
        $request->merge([
            'code' => Coupon::normalizeCode($request->input('code')),
        ]);

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
            'tenant_ids' => 'required|array|min:1',
            'tenant_ids.*' => 'integer|exists:tenants,id',
            'status' => $includeStatus ? array_merge(['required'], $statusRule) : 'nullable',
        ]);
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
