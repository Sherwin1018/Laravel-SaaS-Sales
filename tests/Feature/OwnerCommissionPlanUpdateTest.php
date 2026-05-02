<?php

namespace Tests\Feature;

use App\Models\CommissionPlan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerCommissionPlanUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_owner_can_update_commission_settings_for_their_tenant(): void
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Owner Workspace',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        TenantPayoutAccount::query()->create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'gcash',
            'account_name' => 'Owner Workspace',
            'destination_value' => '09170000000',
            'masked_destination' => '*******0000',
            'is_verified' => true,
            'verified_at' => now(),
            'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
            'is_default' => true,
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $owner->roles()->attach($this->role('account-owner'));
        $owner->load('roles');

        $marketingManager = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $marketingManager->roles()->attach($this->role('marketing-manager'));

        $plan = app(CommissionService::class)->resolvePlanForTenant($tenant);

        $response = $this->actingAs($owner)->put(route('reports.owner.commission-plan.update'), [
            'gateway_fee_rate' => 4.25,
            'platform_fee_rate' => 1.75,
            'sales_agent_rate' => 8.50,
            'marketing_manager_rate' => 2.50,
            'hold_days' => 14,
            'default_marketing_manager_user_id' => $marketingManager->id,
        ]);

        $response->assertRedirect(route('reports.owner'));
        $response->assertSessionHas('success', 'Commission settings updated successfully.');

        $plan->refresh();

        $this->assertSame('4.25', $plan->gateway_fee_rate);
        $this->assertSame('1.75', $plan->platform_fee_rate);
        $this->assertSame('8.50', $plan->sales_agent_rate);
        $this->assertSame('2.50', $plan->marketing_manager_rate);
        $this->assertSame(14, $plan->hold_days);
        $this->assertSame($marketingManager->id, $plan->default_marketing_manager_user_id);
    }

    public function test_commission_settings_reject_invalid_combined_rates(): void
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Owner Workspace',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        TenantPayoutAccount::query()->create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'gcash',
            'account_name' => 'Owner Workspace',
            'destination_value' => '09171111111',
            'masked_destination' => '*******1111',
            'is_verified' => true,
            'verified_at' => now(),
            'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
            'is_default' => true,
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $owner->roles()->attach($this->role('account-owner'));
        $owner->load('roles');

        $plan = CommissionPlan::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Plan',
            'is_active' => true,
            'is_default' => true,
            'gateway_fee_rate' => 3.00,
            'platform_fee_rate' => 2.00,
            'sales_agent_rate' => 7.00,
            'marketing_manager_rate' => 3.00,
            'hold_days' => 7,
            'sales_attribution_model' => 'assigned_lead',
            'marketing_attribution_model' => 'last_touch_campaign',
        ]);

        $response = $this->actingAs($owner)->from(route('reports.owner'))->put(route('reports.owner.commission-plan.update'), [
            'gateway_fee_rate' => 60,
            'platform_fee_rate' => 40,
            'sales_agent_rate' => 55,
            'marketing_manager_rate' => 50,
            'hold_days' => 14,
            'default_marketing_manager_user_id' => null,
        ]);

        $response->assertRedirect(route('reports.owner'));
        $response->assertSessionHas('error', 'Gateway and platform fees combined must stay below 100%.');

        $plan->refresh();
        $this->assertSame('3.00', $plan->gateway_fee_rate);
        $this->assertSame('2.00', $plan->platform_fee_rate);
    }

    private function role(string $slug): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => ucwords(str_replace('-', ' ', $slug))]
        );
    }
}
