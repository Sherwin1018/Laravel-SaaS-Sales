<?php

namespace Tests\Feature;

use App\Models\ExternalDeliveryLog;
use App\Models\Funnel;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;
use App\Models\User;
use App\Services\FunnelCustomerPortalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseFivePlanEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_index_exposes_plan_usage_summary(): void
    {
        $plan = $this->createPlan([
            'code' => 'starter',
            'name' => 'Starter',
            'max_users' => 3,
        ]);

        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner', [
            'subscription_plan' => $plan->code,
        ]);

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ])->roles()->attach($this->role('sales-agent'));

        $response = $this->actingAs($owner)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewHas('planUsage', function (array $usage) {
            return ($usage['users']['used'] ?? null) === 2
                && ($usage['users']['limit'] ?? null) === 3
                && ($usage['users']['remaining'] ?? null) === 1
                && ($usage['automation_enabled'] ?? null) === true;
        });
    }

    public function test_user_creation_is_blocked_when_team_member_limit_is_reached(): void
    {
        $plan = $this->createPlan([
            'code' => 'starter',
            'name' => 'Starter',
            'max_users' => 1,
        ]);

        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner', [
            'subscription_plan' => $plan->code,
        ]);
        $this->role('sales-agent');

        $response = $this->actingAs($owner)->from(route('users.index'))->post(route('users.store'), [
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => 'StrongPass!123',
            'password_confirmation' => 'StrongPass!123',
            'role' => 'sales-agent',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
    }

    public function test_lead_creation_is_blocked_when_lead_limit_is_reached(): void
    {
        $plan = $this->createPlan([
            'code' => 'starter',
            'name' => 'Starter',
            'max_leads' => 1,
        ]);

        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner', [
            'subscription_plan' => $plan->code,
        ]);
        [, $agent] = $this->createTenantUserWithRole('sales-agent', [], $tenant);

        Lead::create([
            'tenant_id' => $tenant->id,
            'assigned_to' => $agent->id,
            'name' => 'Existing Lead',
            'email' => 'existing.lead@example.com',
            'phone' => '09123456789',
            'source_campaign' => 'Referral',
            'tags' => [],
            'status' => 'new',
            'score' => 0,
        ]);

        $response = $this->actingAs($owner)->from(route('leads.index'))->post(route('leads.store'), [
            'name' => 'Blocked Lead',
            'email' => 'blocked.lead@example.com',
            'phone' => '09123456789',
            'source_campaign' => 'Referral',
            'status' => 'new',
            'assigned_to' => $agent->id,
            'tags' => '',
        ]);

        $response->assertRedirect(route('leads.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('leads', ['email' => 'blocked.lead@example.com']);
    }

    public function test_funnel_creation_is_blocked_when_funnel_limit_is_reached(): void
    {
        $plan = $this->createPlan([
            'code' => 'starter',
            'name' => 'Starter',
            'max_funnels' => 1,
        ]);

        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner', [
            'subscription_plan' => $plan->code,
        ]);

        Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $owner->id,
            'name' => 'Existing Funnel',
            'slug' => 'existing-funnel',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($owner)->from(route('funnels.index'))->post(route('funnels.store'), [
            'name' => 'Blocked Funnel',
            'description' => 'Should not be created.',
            'funnel_purpose' => 'service',
            'default_tags' => '',
        ]);

        $response->assertRedirect(route('funnels.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('funnels', ['slug' => 'blocked-funnel']);
    }

    public function test_message_usage_summary_deduplicates_billable_logs_by_idempotency_key(): void
    {
        $plan = $this->createPlan([
            'code' => 'starter',
            'name' => 'Starter',
            'max_monthly_messages' => 5,
        ]);

        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner', [
            'subscription_plan' => $plan->code,
        ]);

        ExternalDeliveryLog::create([
            'tenant_id' => $tenant->id,
            'channel' => 'email',
            'event_name' => 'owner_digest',
            'provider' => 'smtp',
            'status' => 'sent',
            'idempotency_key' => 'msg-001',
            'is_billable' => true,
            'sent_at' => now(),
        ]);

        ExternalDeliveryLog::create([
            'tenant_id' => $tenant->id,
            'channel' => 'email',
            'event_name' => 'owner_digest_retry',
            'provider' => 'smtp',
            'status' => 'sent',
            'idempotency_key' => 'msg-001',
            'is_billable' => true,
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($owner)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewHas('planUsage', function (array $usage) {
            return ($usage['messages']['used'] ?? null) === 1
                && ($usage['messages']['remaining'] ?? null) === 4
                && ($usage['messages']['is_over_limit'] ?? null) === false
                && ($usage['messages']['status'] ?? null) === 'available';
        });
    }

    public function test_customer_portal_users_do_not_count_against_team_member_usage_limits(): void
    {
        $plan = $this->createPlan([
            'code' => 'starter',
            'name' => 'Starter',
            'max_users' => 1,
        ]);

        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner', [
            'subscription_plan' => $plan->code,
        ]);

        User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Portal Buyer',
            'email' => 'buyer@example.com',
            'password' => 'Password@123456',
            'role' => 'customer',
            'status' => 'inactive',
            'activation_state' => 'invited',
            'is_customer_portal_user' => true,
        ]);

        $response = $this->actingAs($owner)->get(route('users.index'));

        $response->assertOk();
        $response->assertViewHas('planUsage', function (array $usage) {
            return ($usage['users']['used'] ?? null) === 1
                && ($usage['users']['limit'] ?? null) === 1
                && ($usage['users']['remaining'] ?? null) === 0;
        });
    }

    public function test_customer_portal_user_can_be_provisioned_even_when_team_member_limit_is_full(): void
    {
        $plan = $this->createPlan([
            'code' => 'starter',
            'name' => 'Starter',
            'max_users' => 1,
        ]);

        [$tenant] = $this->createTenantUserWithRole('account-owner', [
            'subscription_plan' => $plan->code,
        ]);

        $portalAccess = app(FunnelCustomerPortalService::class)->provisionForPaidCustomer(
            $tenant,
            'portalbuyer@example.com',
            'Portal Buyer',
            '09171234567'
        );

        $this->assertSame('portalbuyer@example.com', $portalAccess['user']?->email);
        $this->assertTrue((bool) ($portalAccess['setup_required'] ?? false));
        $this->assertNotNull($portalAccess['setup_url'] ?? null);
        $this->assertDatabaseHas('users', [
            'tenant_id' => $tenant->id,
            'email' => 'portalbuyer@example.com',
            'is_customer_portal_user' => true,
        ]);
    }

    public function test_owner_dashboard_shows_pending_renewal_state_instead_of_zero_countdown_for_past_due_renewal_date(): void
    {
        $plan = $this->createPlan([
            'code' => 'growth',
            'name' => 'Growth',
            'automation_enabled' => true,
        ]);

        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner', [
            'subscription_plan' => $plan->code,
            'billing_status' => 'current',
            'subscription_renews_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard.owner'));

        $response->assertOk();
        $response->assertSee('Monthly Renewal Pending');
        $response->assertSee('Renewal Pending');
        $response->assertDontSee('data-subscription-countdown', false);
    }

    private function createPlan(array $overrides = []): Plan
    {
        $payload = array_merge([
            'code' => 'starter',
            'name' => 'Starter',
            'price' => 499,
            'period' => 'per month',
            'summary' => 'Starter plan',
            'features' => ['Basic CRM'],
            'spotlight' => null,
            'is_active' => true,
            'sort_order' => 0,
            'max_users' => null,
            'max_leads' => null,
            'max_funnels' => null,
            'max_workflows' => null,
            'max_monthly_messages' => null,
            'automation_enabled' => true,
        ], $overrides);

        return Plan::query()->updateOrCreate(
            ['code' => $payload['code']],
            $payload
        );
    }

    private function createTenantUserWithRole(string $roleSlug, array $tenantOverrides = [], ?Tenant $tenant = null): array
    {
        $tenant ??= Tenant::create(array_merge([
            'company_name' => 'Phase Five Workspace',
            'subscription_plan' => 'starter',
            'status' => 'active',
        ], $tenantOverrides));

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => 'password',
            'status' => 'active',
        ]);

        $user->roles()->attach($this->role($roleSlug));
        $user->load('roles');
        $this->createApprovedPayoutAccount($tenant);

        return [$tenant, $user];
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
                'account_name' => 'Plan Owner',
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
