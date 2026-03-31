<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantCustomField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseTwoCrmStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_field_index_only_shows_current_tenant_fields(): void
    {
        [$tenantA, $ownerA] = $this->createTenantUserWithRole('account-owner', 'Tenant A');
        [$tenantB] = $this->createTenantUserWithRole('account-owner', 'Tenant B');

        TenantCustomField::create([
            'tenant_id' => $tenantA->id,
            'label' => 'Facebook Page',
            'field_type' => 'text',
            'is_active' => true,
        ]);

        TenantCustomField::create([
            'tenant_id' => $tenantB->id,
            'label' => 'Product Interest',
            'field_type' => 'text',
            'is_active' => true,
        ]);

        $response = $this->actingAs($ownerA)->get(route('crm.custom-fields.index'));

        $response->assertOk();
        $response->assertSee('Facebook Page');
        $response->assertDontSee('Product Interest');
    }

    public function test_lead_creation_saves_custom_field_values_and_applies_configured_source_scoring(): void
    {
        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner');
        [, $salesAgent] = $this->createTenantUserWithRole('sales-agent', $tenant->company_name, $tenant);

        $field = TenantCustomField::create([
            'tenant_id' => $tenant->id,
            'label' => 'Business Type',
            'field_type' => 'select',
            'options' => ['Agency', 'Retail'],
            'is_required' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->post(route('leads.store'), [
            'name' => 'Phase Two Lead',
            'email' => 'phasetwo@example.com',
            'phone' => '09123456789',
            'source_campaign' => 'Referral',
            'status' => 'new',
            'assigned_to' => $salesAgent->id,
            'tags' => 'vip, referral',
            'custom_fields' => [
                $field->id => 'Agency',
            ],
        ]);

        $response->assertRedirect(route('leads.index'));

        $lead = Lead::firstOrFail();
        $this->assertSame(15, $lead->fresh()->score);
        $this->assertDatabaseHas('lead_custom_field_values', [
            'lead_id' => $lead->id,
            'tenant_custom_field_id' => $field->id,
            'value' => 'Agency',
        ]);
        $this->assertDatabaseHas('lead_stage_histories', [
            'lead_id' => $lead->id,
            'from_status' => null,
            'to_status' => 'new',
        ]);
    }

    public function test_stage_change_creates_history_and_applies_stage_score(): void
    {
        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner');
        [, $salesAgent] = $this->createTenantUserWithRole('sales-agent', $tenant->company_name, $tenant);

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'assigned_to' => $salesAgent->id,
            'name' => 'Audit Lead',
            'email' => 'audit@example.com',
            'phone' => '09123456789',
            'source_campaign' => 'Direct',
            'tags' => [],
            'status' => 'new',
            'score' => 0,
        ]);

        $response = $this->actingAs($owner)->put(route('leads.update', $lead), [
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'source_campaign' => $lead->source_campaign,
            'status' => 'contacted',
            'score' => 0,
            'assigned_to' => $salesAgent->id,
            'tags' => '',
        ]);

        $response->assertRedirect(route('leads.index'));
        $lead->refresh();

        $this->assertSame('contacted', $lead->status);
        $this->assertSame(5, $lead->score);
        $this->assertDatabaseHas('lead_stage_histories', [
            'lead_id' => $lead->id,
            'from_status' => 'new',
            'to_status' => 'contacted',
            'changed_by' => $owner->id,
        ]);
    }

    public function test_manual_scoring_uses_config_values_instead_of_hard_coded_points(): void
    {
        config()->set('lead_scoring.manual_events.email_opened.points', 7);
        config()->set('lead_scoring.manual_events.email_opened.label', 'Email Opened Custom');

        [$tenant, $owner] = $this->createTenantUserWithRole('account-owner');

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'assigned_to' => null,
            'name' => 'Config Lead',
            'email' => 'config@example.com',
            'phone' => '09123456789',
            'source_campaign' => 'Direct',
            'tags' => [],
            'status' => 'new',
            'score' => 0,
        ]);

        $response = $this->actingAs($owner)->post(route('leads.score-event', $lead), [
            'event' => 'email_opened',
        ]);

        $response->assertRedirect();
        $this->assertSame(7, $lead->fresh()->score);
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'activity_type' => 'Scoring',
            'notes' => 'Email Opened Custom (+7 points)',
        ]);
    }

    private function createTenantUserWithRole(string $roleSlug, string $companyName = 'Phase Two Workspace', ?Tenant $tenant = null): array
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
