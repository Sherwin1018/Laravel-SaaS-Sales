<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
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

        return [$tenant, $owner];
    }

    private function role(string $roleSlug): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucwords(str_replace('-', ' ', $roleSlug))]
        );
    }
}
