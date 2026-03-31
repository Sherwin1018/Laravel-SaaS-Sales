<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseSevenAnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_exposes_platform_analytics_metrics(): void
    {
        $admin = User::factory()->create(['tenant_id' => null, 'status' => 'active']);
        $admin->roles()->attach($this->role('super-admin', 'Super Admin'));
        $admin->load('roles');

        $tenantA = Tenant::create(['company_name' => 'A', 'status' => 'active', 'subscription_plan' => 'Starter']);
        $tenantB = Tenant::create(['company_name' => 'B', 'status' => 'inactive', 'subscription_plan' => 'Growth']);

        Payment::create([
            'tenant_id' => $tenantA->id,
            'amount' => 1000,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHasAll([
            'mrr',
            'previousMonthMrr',
            'mrrGrowthRate',
            'churnRate',
            'arpu',
            'usageMetrics',
            'tenantGrowth',
            'inactiveTenantCount',
            'payingTenantCount',
        ]);
    }

    public function test_owner_dashboard_exposes_tenant_safe_usage_and_revenue_trend(): void
    {
        $tenant = Tenant::create([
            'company_name' => 'Owner Workspace',
            'status' => 'active',
            'subscription_plan' => 'Starter',
            'billing_status' => 'current',
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $owner->roles()->attach($this->role('account-owner', 'Account Owner'));
        $owner->load('roles');

        Payment::create([
            'tenant_id' => $tenant->id,
            'amount' => 1499,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard.owner'));

        $response->assertOk();
        $response->assertViewHas('analyticsSummary', function (array $summary) {
            return array_key_exists('usage', $summary)
                && array_key_exists('revenue_trend_labels', $summary)
                && array_key_exists('revenue_trend_values', $summary);
        });
    }

    private function role(string $slug, string $name): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );
    }
}
