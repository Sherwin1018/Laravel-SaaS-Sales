<?php

namespace Tests\Feature;

use App\Models\Funnel;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseOneHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_manager_redirects_to_marketing_dashboard_after_login(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.marketing'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_sales_agent_cannot_view_unassigned_lead_from_same_tenant(): void
    {
        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner');
        [, $salesAgent] = $this->createTenantUserWithRole('sales-agent', $tenant->company_name, $tenant);

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'assigned_to' => null,
            'name' => 'Protected Lead',
            'email' => 'protected@example.com',
            'phone' => '09123456789',
            'source_campaign' => 'manual',
            'status' => 'new',
            'score' => 0,
        ]);

        $this->actingAs($salesAgent)
            ->get(route('leads.edit', $lead))
            ->assertForbidden();
    }

    public function test_cross_tenant_lead_access_is_forbidden(): void
    {
        [$tenantA, $ownerA] = $this->createTenantUserWithRole('account-owner', 'Workspace A');
        [$tenantB, $ownerB] = $this->createTenantUserWithRole('account-owner', 'Workspace B');

        $lead = Lead::create([
            'tenant_id' => $tenantA->id,
            'assigned_to' => null,
            'name' => 'Tenant A Lead',
            'email' => 'tenanta@example.com',
            'phone' => '09123456789',
            'source_campaign' => 'manual',
            'status' => 'new',
            'score' => 0,
        ]);

        $this->actingAs($ownerB)
            ->get(route('leads.edit', $lead))
            ->assertForbidden();
    }

    public function test_owner_dashboard_counts_normalized_closed_status_values(): void
    {
        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner');

        Lead::withoutGlobalScope('tenant')->insert([
            [
                'tenant_id' => $tenant->id,
                'assigned_to' => null,
                'name' => 'Won Legacy',
                'email' => 'won@example.com',
                'phone' => '09123456789',
                'source_campaign' => 'manual',
                'status' => 'Closed Won',
                'score' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tenant_id' => $tenant->id,
                'assigned_to' => null,
                'name' => 'Lost Legacy',
                'email' => 'lost@example.com',
                'phone' => '09123456788',
                'source_campaign' => 'manual',
                'status' => 'closed lost',
                'score' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard.owner'));

        $response->assertOk();
        $response->assertViewHas('wonCount', 1);
        $response->assertViewHas('lostCount', 1);
        $response->assertViewHas('conversionRate', 50.0);
    }

    private function createTenantUserWithRole(string $roleSlug, string $companyName = 'Phase One Workspace', ?Tenant $tenant = null): array
    {
        $tenant ??= Tenant::create([
            'company_name' => $companyName,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => 'password',
            'status' => 'active',
        ]);

        $role = Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucwords(str_replace('-', ' ', $roleSlug))]
        );

        $user->roles()->attach($role);
        $user->load('roles');

        return [$tenant, $user];
    }
}
