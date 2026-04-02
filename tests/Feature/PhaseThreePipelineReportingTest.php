<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadStageHistory;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseThreePipelineReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_pipeline_reporting_includes_stage_counts_conversions_and_aging(): void
    {
        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner');
        [, $salesAgent] = $this->createTenantUserWithRole('sales-agent', $tenant->company_name, $tenant);

        $contactedLead = $this->createLeadWithHistory($tenant, $salesAgent, 'contacted', [
            [null, 'new', now()->subDays(12)],
            ['new', 'contacted', now()->subDays(10)],
        ]);

        $proposalLead = $this->createLeadWithHistory($tenant, $salesAgent, 'proposal_sent', [
            [null, 'new', now()->subDays(8)],
            ['new', 'contacted', now()->subDays(7)],
            ['contacted', 'proposal_sent', now()->subDays(5)],
        ]);

        $wonLead = $this->createLeadWithHistory($tenant, $salesAgent, 'closed_won', [
            [null, 'new', now()->subDays(6)],
            ['new', 'contacted', now()->subDays(5)],
            ['contacted', 'proposal_sent', now()->subDays(4)],
            ['proposal_sent', 'closed_won', now()->subDays(2)],
        ]);

        $lostLead = $this->createLeadWithHistory($tenant, $salesAgent, 'closed_lost', [
            [null, 'new', now()->subDays(4)],
            ['new', 'contacted', now()->subDays(3)],
            ['contacted', 'proposal_sent', now()->subDays(2)],
            ['proposal_sent', 'closed_lost', now()->subDays(1)],
        ]);

        $response = $this->actingAs($owner)->get(route('leads.index'));

        $response->assertOk();
        $response->assertViewHas('pipelineReports', function (array $reports) {
            return ($reports['summary']['total_leads'] ?? null) === 4
                && ($reports['summary']['won_count'] ?? null) === 1
                && ($reports['summary']['lost_count'] ?? null) === 1
                && ($reports['summary']['win_rate'] ?? null) === 50.0
                && ($reports['stage_counts']['contacted']['count'] ?? null) === 1
                && ($reports['stage_counts']['proposal_sent']['count'] ?? null) === 1
                && ($reports['stage_conversions'][0]['rate'] ?? null) === 100.0
                && ($reports['stage_conversions'][2]['converted'] ?? null) === 1
                && ($reports['stage_aging']['contacted']['older_than_7_days'] ?? null) === 1;
        });
    }

    public function test_sales_agent_pipeline_reporting_only_counts_assigned_leads(): void
    {
        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner');
        [, $agentA] = $this->createTenantUserWithRole('sales-agent', $tenant->company_name, $tenant);
        [, $agentB] = $this->createTenantUserWithRole('sales-agent', $tenant->company_name, $tenant);

        $this->createLeadWithHistory($tenant, $agentA, 'new', [
            [null, 'new', now()->subDay()],
        ]);

        $this->createLeadWithHistory($tenant, $agentB, 'closed_won', [
            [null, 'new', now()->subDays(3)],
            ['new', 'contacted', now()->subDays(2)],
            ['contacted', 'proposal_sent', now()->subDays(1)],
            ['proposal_sent', 'closed_won', now()->subHours(10)],
        ]);

        $response = $this->actingAs($agentA)->get(route('leads.index'));

        $response->assertOk();
        $response->assertViewHas('pipelineReports', function (array $reports) {
            return ($reports['summary']['total_leads'] ?? null) === 1
                && ($reports['summary']['won_count'] ?? null) === 0
                && ($reports['stage_counts']['new']['count'] ?? null) === 1;
        });
    }

    private function createLeadWithHistory(Tenant $tenant, User $assignedUser, string $status, array $entries): Lead
    {
        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'assigned_to' => $assignedUser->id,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '09123456789',
            'source_campaign' => 'Direct',
            'tags' => [],
            'status' => $status,
            'score' => 0,
            'created_at' => $entries[0][2] ?? now(),
            'updated_at' => now(),
        ]);

        foreach ($entries as [$from, $to, $createdAt]) {
            LeadStageHistory::create([
                'lead_id' => $lead->id,
                'tenant_id' => $tenant->id,
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => $assignedUser->id,
                'metadata' => ['source' => 'test'],
                'created_at' => $createdAt,
            ]);
        }

        return $lead->fresh('stageHistories');
    }

    private function createTenantUserWithRole(string $roleSlug, string $companyName = 'Phase Three Workspace', ?Tenant $tenant = null): array
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
