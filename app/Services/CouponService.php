<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Funnel;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use Illuminate\Support\Collection;

class CouponService
{
    public function normalizeCode(?string $code): string
    {
        return Coupon::normalizeCode($code);
    }

    public function syncCouponStatus(Coupon $coupon): Coupon
    {
        $targetStatus = $coupon->status;

        if ($coupon->status !== Coupon::STATUS_INACTIVE) {
            if ($coupon->ends_at && now()->greaterThan($coupon->ends_at)) {
                $targetStatus = Coupon::STATUS_EXPIRED;
            } elseif ($this->hasReachedUsageLimit($coupon)) {
                $targetStatus = Coupon::STATUS_EXHAUSTED;
            } else {
                $targetStatus = Coupon::STATUS_ACTIVE;
            }
        }

        if ($targetStatus !== $coupon->status) {
            $coupon->forceFill(['status' => $targetStatus])->save();

            return $coupon->refresh()->loadMissing(['assignedTenants:id', 'funnels:id']);
        }

        return $coupon->loadMissing(['assignedTenants:id', 'funnels:id']);
    }

    public function visibleToTenant(int $tenantId)
    {
        return Coupon::query()
            ->with(['assignedTenants:id,company_name', 'funnels:id,name'])
            ->visibleToTenant($tenantId)
            ->latest('id');
    }

    public function availableForFunnel(Funnel $funnel): Collection
    {
        $coupons = $this->visibleToTenant((int) $funnel->tenant_id)
            ->get()
            ->map(fn (Coupon $coupon) => $this->syncCouponStatus($coupon))
            ->filter(function (Coupon $coupon) use ($funnel) {
                if ($coupon->status !== Coupon::STATUS_ACTIVE) {
                    return false;
                }

                if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
                    return false;
                }

                return $this->couponMatchesFunnel($coupon, $funnel);
            })
            ->values();

        return $coupons;
    }

    public function validateForCheckout(
        Funnel $funnel,
        float $subtotalAmount,
        ?string $couponCode,
        ?string $customerEmail = null,
        ?Lead $lead = null
    ): array {
        $normalizedCode = $this->normalizeCode($couponCode);
        if ($normalizedCode === '') {
            return [
                'coupon' => null,
                'coupon_code' => '',
                'discount_amount' => 0.0,
                'final_amount' => $subtotalAmount,
                'error' => null,
            ];
        }

        $coupon = Coupon::query()
            ->with(['assignedTenants:id', 'funnels:id'])
            ->where('code', $normalizedCode)
            ->first();

        if (! $coupon) {
            return $this->invalidResult($normalizedCode, $subtotalAmount, 'Coupon code not found.');
        }

        $coupon = $this->syncCouponStatus($coupon);
        if ($coupon->status !== Coupon::STATUS_ACTIVE) {
            return $this->invalidResult($normalizedCode, $subtotalAmount, 'This coupon is no longer active.');
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            return $this->invalidResult($normalizedCode, $subtotalAmount, 'This coupon is not active yet.');
        }

        if ($coupon->ends_at && now()->gt($coupon->ends_at)) {
            return $this->invalidResult($normalizedCode, $subtotalAmount, 'This coupon has expired.');
        }

        if (! $this->couponMatchesTenant($coupon, (int) $funnel->tenant_id)) {
            return $this->invalidResult($normalizedCode, $subtotalAmount, 'This coupon is not available for this tenant.');
        }

        if (! $this->couponMatchesFunnel($coupon, $funnel)) {
            return $this->invalidResult($normalizedCode, $subtotalAmount, 'This coupon is not available for this funnel.');
        }

        if ($this->hasReachedUsageLimit($coupon)) {
            $this->syncCouponStatus($coupon);

            return $this->invalidResult($normalizedCode, $subtotalAmount, 'This coupon has already reached its usage limit.');
        }

        if ($this->hasReachedPerUserLimit($coupon, $customerEmail, $lead)) {
            return $this->invalidResult($normalizedCode, $subtotalAmount, 'You have already used this coupon the maximum number of times.');
        }

        $discountAmount = $this->discountAmount($coupon, $subtotalAmount);
        if ($discountAmount <= 0) {
            return $this->invalidResult($normalizedCode, $subtotalAmount, 'This coupon did not change the checkout amount.');
        }

        return [
            'coupon' => $coupon,
            'coupon_code' => $normalizedCode,
            'discount_amount' => $discountAmount,
            'final_amount' => max(0, round($subtotalAmount - $discountAmount, 2)),
            'error' => null,
        ];
    }

    public function redeem(
        Coupon $coupon,
        Payment $payment,
        Funnel $funnel,
        FunnelStep $step,
        ?Lead $lead,
        ?string $customerEmail,
        float $orderAmount,
        float $discountAmount,
        float $finalAmount
    ): void {
        if ($payment->coupon_id !== $coupon->id || $discountAmount <= 0) {
            return;
        }

        $existing = CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->where('payment_id', $payment->id)
            ->first();

        if ($existing) {
            return;
        }

        CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'payment_id' => $payment->id,
            'lead_id' => $lead?->id,
            'customer_email' => $customerEmail ?: null,
            'coupon_code' => $coupon->code,
            'order_amount' => $orderAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'redeemed_at' => now(),
        ]);

        $coupon->increment('times_used');

        $freshCoupon = $coupon->fresh();
        if ($this->hasReachedUsageLimit($freshCoupon)) {
            $freshCoupon->forceFill([
                'status' => Coupon::STATUS_EXHAUSTED,
                'used_at' => $freshCoupon->used_at ?? now(),
            ])->save();
        }
    }

    public function discountAmount(Coupon $coupon, float $subtotalAmount): float
    {
        $subtotalAmount = round(max(0, $subtotalAmount), 2);

        if ($coupon->discount_type === Coupon::DISCOUNT_PERCENT) {
            $percent = min(100, max(0, (float) $coupon->discount_value));

            return round($subtotalAmount * ($percent / 100), 2);
        }

        return round(min($subtotalAmount, max(0, (float) $coupon->discount_value)), 2);
    }

    private function invalidResult(string $couponCode, float $subtotalAmount, string $message): array
    {
        return [
            'coupon' => null,
            'coupon_code' => $couponCode,
            'discount_amount' => 0.0,
            'final_amount' => $subtotalAmount,
            'error' => $message,
        ];
    }

    private function hasReachedUsageLimit(Coupon $coupon): bool
    {
        $maxTotalUses = $coupon->usage_mode === Coupon::USAGE_SINGLE
            ? 1
            : ($coupon->max_total_uses ?: null);

        return $maxTotalUses !== null && (int) $coupon->times_used >= (int) $maxTotalUses;
    }

    private function hasReachedPerUserLimit(Coupon $coupon, ?string $customerEmail, ?Lead $lead): bool
    {
        $limit = $coupon->usage_mode === Coupon::USAGE_SINGLE
            ? 1
            : ($coupon->max_uses_per_user ?: null);

        if ($limit === null) {
            return false;
        }

        $query = $coupon->redemptions();
        $normalizedEmail = trim(strtolower((string) $customerEmail));

        if ($lead) {
            $query->where('lead_id', $lead->id);
        } elseif ($normalizedEmail !== '') {
            $query->where('customer_email', $normalizedEmail);
        } else {
            return false;
        }

        return (int) $query->count() >= (int) $limit;
    }

    private function couponMatchesTenant(Coupon $coupon, int $tenantId): bool
    {
        if ($coupon->scope_type === Coupon::SCOPE_TENANT) {
            return (int) $coupon->tenant_id === $tenantId;
        }

        return $coupon->assignedTenants->contains(fn ($tenant) => (int) $tenant->id === $tenantId);
    }

    private function couponMatchesFunnel(Coupon $coupon, Funnel $funnel): bool
    {
        if (! $this->couponMatchesTenant($coupon, (int) $funnel->tenant_id)) {
            return false;
        }

        if (! $coupon->relationLoaded('funnels')) {
            $coupon->loadMissing('funnels:id');
        }

        if ($coupon->funnels->isEmpty()) {
            return true;
        }

        return $coupon->funnels->contains(fn ($linkedFunnel) => (int) $linkedFunnel->id === (int) $funnel->id);
    }
}
