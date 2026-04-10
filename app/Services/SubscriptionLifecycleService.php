<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class SubscriptionLifecycleService
{
    public const BILLING_CURRENT = 'current';
    public const BILLING_OVERDUE = 'overdue';
    public const BILLING_INACTIVE = 'inactive';
    public const BILLING_TRIAL = 'trial';
    public const GRACE_DAYS = 3;

    public function activateTenantSubscriptionFromPayment(Payment $payment, array $plan, ?string $paymentMethod = null): Tenant
    {
        return DB::transaction(function () use ($payment, $plan, $paymentMethod) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $tenant = Tenant::query()->lockForUpdate()->findOrFail($payment->tenant_id);

            if (! $payment->isPlatformSubscription()) {
                throw new \RuntimeException('Only platform subscription payments can activate tenant subscriptions.');
            }

            if ($payment->status !== 'paid') {
                $payment->update([
                    'status' => 'paid',
                    'payment_method' => $paymentMethod ?? $payment->payment_method,
                    'payment_date' => now()->toDateString(),
                ]);
            }

            $tenant->update([
                'subscription_plan' => $plan['name'],
                'status' => 'active',
                'billing_status' => self::BILLING_CURRENT,
                'billing_grace_ends_at' => null,
                'last_payment_failed_at' => null,
                'subscription_activated_at' => $tenant->subscription_activated_at ?? now(),
                'trial_ends_at' => null,
            ]);

            return $tenant->fresh();
        });
    }

    public function markPaymentFailed(Payment $payment): ?Tenant
    {
        return DB::transaction(function () use ($payment) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $tenant = Tenant::query()->lockForUpdate()->find($payment->tenant_id);

            if ($payment->status !== 'failed') {
                $payment->update([
                    'status' => 'failed',
                    'payment_date' => $payment->payment_date ?: now()->toDateString(),
                ]);
            }

            if (! $tenant) {
                return null;
            }

            if (! $payment->isPlatformSubscription()) {
                return $tenant->fresh();
            }

            if ($tenant->status === 'active') {
                $tenant->update([
                    'billing_status' => self::BILLING_OVERDUE,
                    'billing_grace_ends_at' => now()->addDays(self::GRACE_DAYS),
                    'last_payment_failed_at' => now(),
                ]);

                $this->dispatchAutomationEvent('payment_failed', [
                    'tenant_id' => $tenant->id,
                    'invoice_id' => (string) ($payment->provider_reference ?: $payment->id),
                    'payment_id' => $payment->id,
                ]);
            }

            return $tenant->fresh();
        });
    }

    public function expireGracePeriodIfNeeded(Tenant $tenant): Tenant
    {
        if (
            $tenant->status === 'active'
            && $tenant->billing_status === self::BILLING_OVERDUE
            && $tenant->billing_grace_ends_at
            && now()->greaterThan($tenant->billing_grace_ends_at)
        ) {
            $tenant->update([
                'status' => 'inactive',
                'billing_status' => self::BILLING_INACTIVE,
            ]);
        }

        return $tenant->fresh();
    }

    public function restoreTenantBilling(Tenant $tenant): Tenant
    {
        $tenant->update([
            'status' => 'active',
            'billing_status' => self::BILLING_CURRENT,
            'billing_grace_ends_at' => null,
            'last_payment_failed_at' => null,
            'subscription_activated_at' => $tenant->subscription_activated_at ?? now(),
        ]);

        $this->dispatchAutomationEvent('payment_recovered', [
            'tenant_id' => $tenant->id,
        ]);

        return $tenant->fresh();
    }

    public function billingStateLabel(Tenant $tenant): string
    {
        return match ($tenant->billing_status) {
            self::BILLING_OVERDUE => 'Overdue',
            self::BILLING_INACTIVE => 'Inactive',
            self::BILLING_TRIAL => 'Trial',
            default => 'Current',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchAutomationEvent(string $eventName, array $payload): void
    {
        try {
            app(N8nEmailOrchestrator::class)->dispatch($eventName, $payload);
        } catch (\Throwable) {
            // Best-effort dispatch only.
        }
    }
}
