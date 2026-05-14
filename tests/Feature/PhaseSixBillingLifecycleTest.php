<?php

namespace Tests\Feature;

use App\Models\FinanceAuditLog;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;
use App\Models\User;
use App\Services\SubscriptionLifecycleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSixBillingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_payment_marks_active_tenant_as_overdue_with_grace_period(): void
    {
        [$tenant, $owner] = $this->createTenantOwner();

        $response = $this->actingAs($owner)->post(route('payments.store'), [
            'amount' => 1499,
            'status' => 'failed',
            'payment_date' => now()->toDateString(),
            'provider' => 'manual',
            'provider_reference' => 'INV-001',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $tenant->refresh();
        $this->assertSame('active', $tenant->status);
        $this->assertSame('overdue', $tenant->billing_status);
        $this->assertNotNull($tenant->billing_grace_ends_at);
        $this->assertSame('failed', Payment::query()->firstOrFail()->status);
    }

    public function test_overdue_owner_is_redirected_to_billing_during_grace_period(): void
    {
        [$tenant, $owner] = $this->createTenantOwner([
            'billing_status' => 'overdue',
            'billing_grace_ends_at' => now()->addDays(2),
            'last_payment_failed_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard.owner'));

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('error');
    }

    public function test_expired_grace_period_deactivates_tenant(): void
    {
        [$tenant, $owner] = $this->createTenantOwner([
            'billing_status' => 'overdue',
            'billing_grace_ends_at' => now()->subMinute(),
            'last_payment_failed_at' => now()->subDays(4),
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard.owner'));

        $response->assertRedirect(route('trial.billing.show'));
        $response->assertSessionHas('error', 'Your workspace is inactive. Complete payment to restore access.');

        $tenant->refresh();
        $this->assertSame('inactive', $tenant->status);
        $this->assertSame('inactive', $tenant->billing_status);
    }

    public function test_paid_payment_restores_overdue_tenant_to_current_state(): void
    {
        [$tenant, $owner] = $this->createTenantOwner([
            'billing_status' => 'overdue',
            'billing_grace_ends_at' => now()->addDay(),
            'last_payment_failed_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($owner)->post(route('payments.store'), [
            'amount' => 1499,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'provider' => 'manual',
            'provider_reference' => 'INV-PAID-001',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $tenant->refresh();
        $this->assertSame('active', $tenant->status);
        $this->assertSame('current', $tenant->billing_status);
        $this->assertNull($tenant->billing_grace_ends_at);
        $this->assertNull($tenant->last_payment_failed_at);
    }

    public function test_subscription_renewal_extends_from_existing_future_renewal_date(): void
    {
        $existingRenewal = now()->addDays(5)->startOfMinute();
        $tenant = Tenant::create([
            'company_name' => 'Renewal Workspace',
            'subscription_plan' => 'Starter',
            'status' => 'active',
            'billing_status' => 'current',
            'subscription_activated_at' => now()->subMonth(),
            'subscription_renews_at' => $existingRenewal,
        ]);

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'payment_type' => Payment::TYPE_PLATFORM_SUBSCRIPTION,
            'amount' => 1499,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'provider' => 'manual',
            'provider_reference' => 'INV-RENEW-001',
        ]);

        $renewedTenant = app(SubscriptionLifecycleService::class)->activateTenantSubscriptionFromPayment($payment, [
            'code' => 'starter',
            'name' => 'Starter',
            'price' => 1499,
        ], 'bank_transfer');

        $this->assertSame('active', $renewedTenant->status);
        $this->assertSame('current', $renewedTenant->billing_status);
        $this->assertTrue($renewedTenant->subscription_renews_at->equalTo($existingRenewal->copy()->addMonthNoOverflow()));
        $this->assertSame(1, FinanceAuditLog::query()->where('payment_id', $payment->id)->where('event_type', 'subscription_paid')->count());
    }

    public function test_duplicate_failed_payment_does_not_extend_grace_period_or_duplicate_audit_logs(): void
    {
        Carbon::setTestNow('2026-05-06 09:00:00');

        try {
            $tenant = Tenant::create([
                'company_name' => 'Grace Workspace',
                'subscription_plan' => 'Starter',
                'status' => 'active',
                'billing_status' => 'current',
                'subscription_activated_at' => now()->subMonth(),
                'subscription_renews_at' => now()->addDays(10),
            ]);

            $payment = Payment::create([
                'tenant_id' => $tenant->id,
                'payment_type' => Payment::TYPE_PLATFORM_SUBSCRIPTION,
                'amount' => 1499,
                'status' => 'pending',
                'payment_date' => now()->toDateString(),
                'provider' => 'manual',
                'provider_reference' => 'INV-FAILED-001',
            ]);

            $service = app(SubscriptionLifecycleService::class);
            $service->markPaymentFailed($payment);
            $tenant->refresh();

            $firstGraceEndsAt = $tenant->billing_grace_ends_at?->copy();

            Carbon::setTestNow('2026-05-07 13:45:00');
            $service->markPaymentFailed($payment->fresh());
            $tenant->refresh();

            $this->assertSame('overdue', $tenant->billing_status);
            $this->assertNotNull($firstGraceEndsAt);
            $this->assertTrue($tenant->billing_grace_ends_at->equalTo($firstGraceEndsAt));
            $this->assertSame(1, FinanceAuditLog::query()->where('payment_id', $payment->id)->where('event_type', 'subscription_overdue')->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createTenantOwner(array $tenantOverrides = []): array
    {
        $tenant = Tenant::create(array_merge([
            'company_name' => 'Billing Workspace',
            'subscription_plan' => 'Starter',
            'status' => 'active',
            'billing_status' => 'current',
        ], $tenantOverrides));

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => 'password',
            'status' => 'active',
        ]);

        $owner->roles()->attach($this->role('account-owner'));
        $owner->load('roles');
        $this->createApprovedPayoutAccount($tenant);

        return [$tenant, $owner];
    }

    private function role(string $roleSlug): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucwords(str_replace('-', ' ', $roleSlug))]
        );
    }

    private function createApprovedPayoutAccount(Tenant $tenant): TenantPayoutAccount
    {
        return TenantPayoutAccount::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'is_default' => true,
            ],
            [
                'destination_type' => 'gcash',
                'account_name' => 'Billing Workspace Owner',
                'destination_value' => '09171234567',
                'masked_destination' => '0917****567',
                'provider_destination_reference' => 'gcash-ref-'.$tenant->id,
                'is_verified' => true,
                'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
                'verified_at' => now(),
            ]
        );
    }
}
