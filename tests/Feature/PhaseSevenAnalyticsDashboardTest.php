<?php

namespace Tests\Feature;

use App\Models\Funnel;
use App\Models\FunnelEvent;
use App\Models\FunnelStep;
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
                && array_key_exists('revenue_trend_values', $summary)
                && array_key_exists('physical_sales', $summary);
        });
    }

    public function test_owner_dashboard_aggregates_physical_product_sales_summary(): void
    {
        $tenant = Tenant::create([
            'company_name' => 'Physical Workspace',
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

        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $owner->id,
            'name' => 'Bottle Funnel',
            'slug' => 'bottle-funnel',
            'purpose' => 'physical_product',
            'status' => 'published',
        ]);

        $checkoutStep = FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Checkout',
            'slug' => 'checkout',
            'type' => 'checkout',
            'position' => 1,
            'price' => 1499,
            'is_active' => true,
            'layout_json' => ['root' => [], 'sections' => []],
        ]);

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'amount' => 1499,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'session_identifier' => 'session-physical-1',
        ]);

        FunnelEvent::create([
            'tenant_id' => $tenant->id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'payment_id' => $payment->id,
            'event_name' => 'funnel_checkout_started',
            'session_identifier' => 'session-physical-1',
            'meta' => [
                'amount' => 1499,
                'funnel_purpose' => 'physical_product',
                'customer' => [
                    'full_name' => 'Jamie Buyer',
                    'email' => 'jamie@example.com',
                    'phone' => '09171234567',
                ],
                'delivery_address' => 'Manila, Philippines',
                'order_items' => [
                    ['name' => 'Steel Bottle', 'quantity' => 2, 'price' => '749.50'],
                ],
                'order_items_label' => 'Steel Bottle x2',
            ],
            'occurred_at' => now()->subMinutes(10),
        ]);

        FunnelEvent::create([
            'tenant_id' => $tenant->id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'payment_id' => $payment->id,
            'event_name' => 'funnel_payment_paid',
            'session_identifier' => 'session-physical-1',
            'meta' => [
                'amount' => 1499,
                'payment_status' => 'paid',
                'provider' => 'paymongo',
                'funnel_purpose' => 'physical_product',
            ],
            'occurred_at' => now()->subMinutes(5),
        ]);

        FunnelEvent::create([
            'tenant_id' => $tenant->id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'payment_id' => $payment->id,
            'event_name' => 'funnel_order_delivery_updated',
            'session_identifier' => 'session-physical-1',
            'meta' => [
                'delivery_status' => 'shipped',
                'tracking_url' => 'https://example.test/tracking/123',
                'funnel_purpose' => 'physical_product',
            ],
            'occurred_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard.owner'));

        $response->assertOk();
        $response->assertViewHas('analyticsSummary', function (array $summary) {
            return (int) data_get($summary, 'physical_sales.funnel_count', 0) === 1
                && (int) data_get($summary, 'physical_sales.paid_orders', 0) === 1
                && (int) data_get($summary, 'physical_sales.units_ordered', 0) === 2
                && (float) data_get($summary, 'physical_sales.paid_revenue', 0) === 1499.0
                && (int) data_get($summary, 'physical_sales.delivery_statuses.shipped', 0) === 1
                && data_get($summary, 'physical_sales.top_products.0.name') === 'Steel Bottle';
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
