<?php

namespace Tests\Feature;

use App\Models\Funnel;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
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
            'default_tags' => '',
        ]);

        $response->assertRedirect(route('funnels.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('funnels', ['slug' => 'blocked-funnel']);
    }

    private function createPlan(array $overrides = []): Plan
    {
        return Plan::create(array_merge([
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
        ], $overrides));
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

        return [$tenant, $user];
    }

    private function role(string $roleSlug): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucwords(str_replace('-', ' ', $roleSlug))]
        );
    }
}
