<?php

namespace Tests\Feature;

use App\Models\Funnel;
use App\Models\FunnelTemplate;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunnelTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_and_publish_a_funnel_template(): void
    {
        $admin = $this->createUserWithRole('super-admin', null);

        $createResponse = $this->actingAs($admin)->post(route('admin.funnel-templates.store'), [
            'name' => 'Coaching Offer Template',
            'description' => 'Shared coaching funnel template',
        ]);

        $template = FunnelTemplate::query()->firstOrFail();

        $createResponse->assertRedirect(route('admin.funnel-templates.edit', $template));
        $this->assertSame(FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP, $template->template_type);
        $this->assertContains(FunnelTemplate::PURPOSE_TAG_PREFIX . 'service', $template->template_tags ?? []);
        $this->assertDatabaseCount('funnel_template_steps', 5);

        $publishResponse = $this->actingAs($admin)->post(route('admin.funnel-templates.publish', $template));

        $publishResponse->assertRedirect();
        $this->assertSame('published', $template->fresh()->status);
        $this->assertNotNull($template->fresh()->published_at);
    }

    public function test_published_templates_appear_in_customer_funnel_create_and_clone_into_workspace(): void
    {
        $admin = $this->createUserWithRole('super-admin', null);
        $ownerTenant = Tenant::create([
            'company_name' => 'Customer Workspace',
            'status' => 'active',
        ]);
        $owner = $this->createUserWithRole('account-owner', $ownerTenant);

        $template = FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'SaaS Demo Funnel',
            'slug' => 'saas-demo-funnel',
            'description' => 'Shared demo template',
            'template_type' => FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP,
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $template->steps()->createMany([
            ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'position' => 1, 'is_active' => true, 'template' => 'simple', 'step_tags' => [], 'layout_json' => $this->layoutWithButton('next_step')],
            ['title' => 'Opt In', 'slug' => 'opt-in', 'type' => 'opt_in', 'position' => 2, 'is_active' => true, 'template' => 'simple', 'step_tags' => [], 'layout_json' => $this->layoutWithForm()],
            ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'position' => 3, 'is_active' => true, 'template' => 'simple', 'step_tags' => [], 'layout_json' => $this->layoutWithButton('next_step')],
            ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'position' => 4, 'is_active' => true, 'template' => 'simple', 'step_tags' => [], 'price' => 1999, 'layout_json' => $this->layoutWithButton('checkout')],
            ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'position' => 5, 'is_active' => true, 'template' => 'simple', 'step_tags' => [], 'layout_json' => ['root' => [], 'sections' => []]],
        ]);

        $this->actingAs($owner)
            ->get(route('funnels.create'))
            ->assertOk()
            ->assertSee('SaaS Demo Funnel');

        $storeResponse = $this->actingAs($owner)->post(route('funnels.store'), [
            'name' => 'Workspace Funnel',
            'description' => 'Tenant copy',
            'funnel_purpose' => 'service',
            'template_id' => $template->id,
        ]);

        $funnel = Funnel::query()->firstOrFail();

        $storeResponse->assertRedirect(route('funnels.edit', $funnel));
        $this->assertSame($ownerTenant->id, $funnel->tenant_id);
        $this->assertSame('Workspace Funnel', $funnel->name);
        $this->assertSame('service', $funnel->purpose);
        $this->assertCount(5, $funnel->steps);
        $this->assertDatabaseHas('funnel_steps', [
            'funnel_id' => $funnel->id,
            'type' => 'checkout',
            'price' => 1999.00,
        ]);
        $this->assertDatabaseHas('funnel_step_revisions', [
            'funnel_step_id' => $funnel->steps()->where('type', 'landing')->firstOrFail()->id,
        ]);
    }

    public function test_customer_create_flow_hides_single_page_templates_and_allows_manual_step_by_step_build(): void
    {
        $admin = $this->createUserWithRole('super-admin', null);
        $ownerTenant = Tenant::create([
            'company_name' => 'Customer Workspace',
            'status' => 'active',
        ]);
        $owner = $this->createUserWithRole('account-owner', $ownerTenant);

        FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Visible Step Template',
            'slug' => 'visible-step-template',
            'description' => 'Shared step template',
            'template_type' => FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP,
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Hidden Single Page Template',
            'slug' => 'hidden-single-page-template',
            'description' => 'Legacy single page template',
            'template_type' => 'single_page',
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'published',
            'published_at' => now()->addMinute(),
        ]);

        $this->actingAs($owner)
            ->get(route('funnels.create'))
            ->assertOk()
            ->assertSee('Step-by-Step Page')
            ->assertSee('Visible Step Template')
            ->assertDontSee('Hidden Single Page Template');

        $storeResponse = $this->actingAs($owner)->post(route('funnels.store'), [
            'name' => 'Manual Physical Product Funnel',
            'description' => 'Built from scratch',
            'template_type' => 'step_by_step',
            'funnel_purpose' => 'physical_product',
        ]);

        $funnel = Funnel::query()->firstOrFail();

        $storeResponse->assertRedirect(route('funnels.edit', $funnel));
        $this->assertSame('physical_product', $funnel->purpose);
        $this->assertCount(4, $funnel->steps);
        $this->assertDatabaseHas('funnel_steps', [
            'funnel_id' => $funnel->id,
            'type' => 'checkout',
        ]);
    }

    public function test_template_visibility_is_limited_by_subscribed_plan(): void
    {
        $admin = $this->createUserWithRole('super-admin', null);
        Plan::query()->where('code', 'starter')->update(['max_templates' => 1]);

        $ownerTenant = Tenant::create([
            'company_name' => 'Plan Limited Workspace',
            'subscription_plan' => 'starter',
            'status' => 'active',
        ]);
        $owner = $this->createUserWithRole('account-owner', $ownerTenant);

        FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Older Step Template',
            'slug' => 'older-step-template',
            'template_type' => FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP,
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Newest Step Template',
            'slug' => 'newest-step-template',
            'template_type' => FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP,
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('funnels.create'))
            ->assertOk()
            ->assertSee('Newest Step Template')
            ->assertDontSee('Older Step Template');
    }

    public function test_account_owner_cannot_access_super_admin_template_management(): void
    {
        $tenant = Tenant::create([
            'company_name' => 'Tenant Workspace',
            'status' => 'active',
        ]);
        $owner = $this->createUserWithRole('account-owner', $tenant);

        $this->actingAs($owner)
            ->get(route('admin.funnel-templates.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_replace_existing_template_from_json(): void
    {
        $admin = $this->createUserWithRole('super-admin', null);

        $template = FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Old Template',
            'slug' => 'old-template',
            'description' => 'Before replace',
            'template_type' => 'step_by_step',
            'template_tags' => ['Legacy'],
            'status' => 'draft',
        ]);

        $template->steps()->createMany([
            ['title' => 'Old Landing', 'slug' => 'old-landing', 'type' => 'landing', 'position' => 1, 'is_active' => true, 'template' => 'simple', 'step_tags' => [], 'layout_json' => ['root' => [], 'sections' => []]],
            ['title' => 'Old Checkout', 'slug' => 'old-checkout', 'type' => 'checkout', 'position' => 2, 'is_active' => true, 'template' => 'simple', 'step_tags' => [], 'price' => 999, 'layout_json' => ['root' => [], 'sections' => []]],
        ]);

        $payload = [
            'name' => 'Imported Physical Template',
            'description' => 'After replace',
            'steps' => [
                [
                    'title' => 'Landing',
                    'slug' => 'landing',
                    'type' => 'landing',
                    'layout_json' => ['root' => [], 'sections' => []],
                ],
                [
                    'title' => 'Checkout',
                    'slug' => 'checkout',
                    'type' => 'checkout',
                    'price' => 299,
                    'layout_json' => ['root' => [], 'sections' => []],
                ],
                [
                    'title' => 'Thank You',
                    'slug' => 'thank-you',
                    'type' => 'thank_you',
                    'layout_json' => ['root' => [], 'sections' => []],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.funnel-templates.replace-json.store', $template), [
            'name' => 'Updated Template',
            'description' => 'Updated from JSON',
            'template_type' => 'step_by_step',
            'funnel_purpose' => 'physical_product',
            'template_tags' => 'Premium, Physical Product',
            'import_json' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'publish_now' => '1',
        ]);

        $response->assertRedirect(route('admin.funnel-templates.edit', $template));

        $template->refresh();
        $this->assertSame('Updated Template', $template->name);
        $this->assertSame('Updated from JSON', $template->description);
        $this->assertSame('published', $template->status);
        $this->assertSame('old-template', $template->slug);
        $this->assertContains(FunnelTemplate::PURPOSE_TAG_PREFIX . 'physical_product', $template->template_tags ?? []);
        $this->assertDatabaseMissing('funnel_template_steps', [
            'funnel_template_id' => $template->id,
            'slug' => 'old-landing',
        ]);
        $this->assertDatabaseHas('funnel_template_steps', [
            'funnel_template_id' => $template->id,
            'slug' => 'checkout',
            'price' => 299.00,
        ]);
        $this->assertSame(3, $template->steps()->count());
    }

    public function test_template_row_analytics_icon_links_to_filtered_template_analytics(): void
    {
        $admin = $this->createUserWithRole('super-admin', null);

        $template = FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Analytics Ready Template',
            'slug' => 'analytics-ready-template',
            'template_type' => FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP,
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.funnel-templates.index'))
            ->assertOk()
            ->assertSee(route('admin.funnel-templates.analytics', ['template' => $template->id]), false);
    }

    public function test_super_admin_can_open_template_filtered_analytics_page(): void
    {
        $admin = $this->createUserWithRole('super-admin', null);

        $selectedTemplate = FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Selected Template',
            'slug' => 'selected-template',
            'template_type' => FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP,
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'published',
            'published_at' => now(),
            'royalty_rate' => 12.5,
        ]);

        $otherTemplate = FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Other Template',
            'slug' => 'other-template',
            'template_type' => FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP,
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        Payment::query()->create([
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'source_funnel_template_id' => $selectedTemplate->id,
            'amount' => 1499,
            'template_royalty_amount' => 187.38,
            'affiliate_commission_amount' => 50,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        Payment::query()->create([
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'source_funnel_template_id' => $otherTemplate->id,
            'amount' => 999,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.funnel-templates.analytics', ['template' => $selectedTemplate->id]))
            ->assertOk()
            ->assertSee('Filtered View')
            ->assertSee('Selected Template')
            ->assertDontSee('Other Template');
    }

    public function test_super_admin_can_download_template_performance_excel(): void
    {
        $admin = $this->createUserWithRole('super-admin', null);

        $template = FunnelTemplate::create([
            'created_by' => $admin->id,
            'name' => 'Excel Ready Template',
            'slug' => 'excel-ready-template',
            'template_type' => FunnelTemplate::TEMPLATE_TYPE_STEP_BY_STEP,
            'template_tags' => [FunnelTemplate::PURPOSE_TAG_PREFIX . 'service'],
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.funnel-templates.analytics.export', ['template' => $template->id]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition');
    }

    private function createUserWithRole(string $roleSlug, ?Tenant $tenant): User
    {
        $user = User::factory()->create([
            'tenant_id' => $tenant?->id,
            'status' => 'active',
        ]);

        $role = Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucwords(str_replace('-', ' ', $roleSlug))]
        );

        $user->roles()->attach($role);
        $user->load('roles');

        return $user;
    }

    private function layoutWithButton(string $actionType): array
    {
        return [
            'root' => [[
                'kind' => 'section',
                'id' => 'sec-' . $actionType,
                'rows' => [[
                    'id' => 'row-' . $actionType,
                    'columns' => [[
                        'id' => 'col-' . $actionType,
                        'elements' => [[
                            'id' => 'el-' . $actionType,
                            'type' => 'button',
                            'content' => 'Continue',
                            'settings' => ['actionType' => $actionType],
                        ]],
                    ]],
                ]],
            ]],
            'sections' => [],
        ];
    }

    private function layoutWithForm(): array
    {
        return [
            'root' => [[
                'kind' => 'section',
                'id' => 'sec-form',
                'rows' => [[
                    'id' => 'row-form',
                    'columns' => [[
                        'id' => 'col-form',
                        'elements' => [[
                            'id' => 'el-form',
                            'type' => 'form',
                            'content' => 'Submit',
                            'settings' => [
                                'fields' => [
                                    ['type' => 'email', 'label' => 'Email', 'required' => true],
                                ],
                            ],
                        ]],
                    ]],
                ]],
            ]],
            'sections' => [],
        ];
    }
}
