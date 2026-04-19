<?php

namespace Tests\Feature;

use App\Models\Funnel;
use App\Models\FunnelEvent;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FunnelTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunnelBuilderModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.paymongo.secret' => null,
            'services.paymongo.webhook_secret' => null,
        ]);
    }

    public function test_public_funnel_journey_tracks_events_and_exposes_analytics(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = $this->createJourneyFunnel($tenant, $user, 'journey-funnel');
        $steps = $funnel->steps()->orderBy('position')->get()->keyBy('type');

        $this->get(route('funnels.portal.step', ['funnelSlug' => $funnel->slug]))->assertOk();
        $this->get(route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $steps['opt_in']->slug]))->assertOk();

        $this->post(route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $steps['opt_in']->slug]), [
            'email' => 'lead@example.com',
            'name' => 'Lead Person',
            'website' => '',
        ])->assertRedirect(route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $steps['sales']->slug]));

        $this->post(route('funnels.portal.checkout', ['funnelSlug' => $funnel->slug, 'stepSlug' => $steps['checkout']->slug]), [
            'amount' => 1499,
            'website' => '',
        ])->assertRedirect(route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $steps['thank_you']->slug]));

        $this->assertDatabaseHas('funnel_events', [
            'funnel_id' => $funnel->id,
            'event_name' => 'funnel_step_viewed',
            'funnel_step_id' => $steps['landing']->id,
        ]);
        $this->assertDatabaseHas('funnel_events', [
            'funnel_id' => $funnel->id,
            'event_name' => 'funnel_opt_in_submitted',
            'funnel_step_id' => $steps['opt_in']->id,
        ]);
        $this->assertDatabaseHas('funnel_events', [
            'funnel_id' => $funnel->id,
            'event_name' => 'funnel_checkout_started',
            'funnel_step_id' => $steps['checkout']->id,
        ]);
        $this->assertDatabaseHas('funnel_events', [
            'funnel_id' => $funnel->id,
            'event_name' => 'funnel_payment_paid',
            'funnel_step_id' => $steps['checkout']->id,
        ]);

        $analyticsResponse = $this->actingAs($user)->getJson(route('funnels.analytics', $funnel));
        $analyticsResponse->assertOk();
        $analyticsResponse->assertJsonPath('analytics.totals.opt_in_count', 1);
        $analyticsResponse->assertJsonPath('analytics.totals.checkout_start_count', 1);
        $analyticsResponse->assertJsonPath('analytics.totals.paid_count', 1);
        $analyticsResponse->assertJsonPath('analytics.totals.revenue', 1499.0);

        $eventsResponse = $this->actingAs($user)->getJson(route('funnels.events', $funnel));
        $eventsResponse->assertOk();
        $eventsResponse->assertJsonPath('total', 4);
    }

    public function test_publish_blocks_broken_funnels_until_required_flow_is_valid(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');

        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'name' => 'Broken Funnel',
            'slug' => 'broken-funnel',
            'status' => 'draft',
        ]);

        FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Landing',
            'slug' => 'landing',
            'type' => 'landing',
            'position' => 1,
            'is_active' => true,
            'layout_json' => $this->buttonLayout('next_step'),
        ]);

        FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Checkout',
            'slug' => 'checkout',
            'type' => 'checkout',
            'position' => 2,
            'is_active' => true,
            'price' => null,
            'layout_json' => $this->emptyLayout(),
        ]);

        $response = $this->actingAs($user)->post(route('funnels.publish', $funnel));

        $response->assertRedirect();
        $response->assertSessionHas('error', function (string $message): bool {
            return str_contains($message, 'Add an active opt-in step.')
                && str_contains($message, 'Add an active sales step.')
                && str_contains($message, 'Add an active thank-you step.')
                && str_contains($message, 'must include at least one Button with action "Checkout submit"');
        });

        $this->assertSame('draft', $funnel->fresh()->status);
    }

    public function test_public_route_honeypot_and_rate_limits_are_enforced(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = $this->createJourneyFunnel($tenant, $user, 'throttle-funnel');
        $optInStep = $funnel->steps()->where('type', 'opt_in')->firstOrFail();

        $this->from(route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $optInStep->slug]))
            ->post(route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $optInStep->slug]), [
                'email' => 'bot@example.com',
                'website' => 'spam',
            ])
            ->assertSessionHasErrors('website');

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $optInStep->slug]), [
                'email' => 'repeat@example.com',
                'website' => '',
            ])->assertRedirect();
        }

        $this->post(route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $optInStep->slug]), [
            'email' => 'repeat@example.com',
            'website' => '',
        ])->assertStatus(429);
    }

    public function test_repeated_checkout_submissions_do_not_create_duplicate_payments(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = $this->createJourneyFunnel($tenant, $user, 'repeat-checkout');
        $checkoutStep = $funnel->steps()->where('type', 'checkout')->firstOrFail();
        $thankYouStep = $funnel->steps()->where('type', 'thank_you')->firstOrFail();

        $first = $this->post(route('funnels.portal.checkout', ['funnelSlug' => $funnel->slug, 'stepSlug' => $checkoutStep->slug]), [
            'amount' => 1499,
            'website' => '',
        ]);

        $second = $this->post(route('funnels.portal.checkout', ['funnelSlug' => $funnel->slug, 'stepSlug' => $checkoutStep->slug]), [
            'amount' => 1499,
            'website' => '',
        ]);

        $first->assertRedirect(route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $thankYouStep->slug]));
        $second->assertRedirect(route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $thankYouStep->slug]));

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame(1, FunnelEvent::query()->where('event_name', 'funnel_payment_paid')->count());
    }

    public function test_other_tenants_cannot_access_funnel_analytics(): void
    {
        [$tenant, $owner] = $this->createTenantUserWithRole('marketing-manager');
        [$otherTenant, $otherUser] = $this->createTenantUserWithRole('marketing-manager', 'Other Workspace');
        $funnel = $this->createJourneyFunnel($tenant, $owner, 'tenant-guarded');

        $this->actingAs($otherUser)
            ->get(route('funnels.analytics', $funnel))
            ->assertNotFound();
    }

    public function test_single_page_mode_blocks_adding_a_second_active_page(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'name' => 'Single Page Funnel',
            'slug' => 'single-page-funnel',
            'purpose' => 'single_page',
            'status' => 'draft',
        ]);

        FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Single Page',
            'slug' => 'single-page',
            'type' => 'landing',
            'position' => 1,
            'is_active' => true,
            'layout_json' => $this->buttonLayout('next_step'),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('funnels.steps.store', $funnel), [
                'title' => 'Another Page',
                'slug' => 'another-page',
                'type' => 'sales',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Single Page mode allows exactly one active page.');
    }

    public function test_single_page_publish_is_blocked_when_more_than_one_active_step_exists(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'name' => 'Single Page Publish Guard',
            'slug' => 'single-page-publish-guard',
            'purpose' => 'single_page',
            'status' => 'draft',
        ]);

        FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Single Page',
            'slug' => 'single-page',
            'type' => 'landing',
            'position' => 1,
            'is_active' => true,
            'layout_json' => $this->buttonLayout('next_step'),
        ]);
        FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Unexpected Extra',
            'slug' => 'unexpected-extra',
            'type' => 'sales',
            'position' => 2,
            'is_active' => true,
            'layout_json' => $this->buttonLayout('next_step'),
        ]);

        $response = $this->actingAs($user)->post(route('funnels.publish', $funnel));

        $response->assertRedirect();
        $response->assertSessionHas('error', function (string $message): bool {
            return str_contains($message, 'Single Page funnels must have exactly one active step.');
        });
    }

    public function test_publish_report_endpoint_returns_validation_payload(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'name' => 'Single Page Report',
            'slug' => 'single-page-report',
            'purpose' => 'single_page',
            'status' => 'draft',
        ]);

        FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Single',
            'slug' => 'single',
            'type' => 'landing',
            'position' => 1,
            'is_active' => true,
            'layout_json' => $this->buttonLayout('next_step'),
        ]);
        FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Extra',
            'slug' => 'extra',
            'type' => 'sales',
            'position' => 2,
            'is_active' => true,
            'layout_json' => $this->buttonLayout('next_step'),
        ]);

        $response = $this->actingAs($user)->getJson(route('funnels.publish.report', $funnel));

        $response->assertOk();
        $response->assertJsonPath('mode', 'single_page');
        $response->assertJsonPath('is_valid', false);
        $response->assertJsonStructure([
            'checks',
            'issues',
            'active_steps',
            'parity_checklist',
        ]);
    }

    public function test_edit_normalizes_existing_legacy_layout_schema_for_saved_steps(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'name' => 'Legacy Schema Funnel',
            'slug' => 'legacy-schema-funnel',
            'status' => 'draft',
        ]);

        $step = FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Legacy Step',
            'slug' => 'legacy-step',
            'type' => 'landing',
            'position' => 1,
            'is_active' => true,
            'layout_json' => [
                'sections' => [
                    [
                        'id' => 'sec-old',
                        'rows' => [],
                        'elements' => [],
                    ],
                ],
            ],
        ]);

        $this->actingAs($user)->get(route('funnels.edit', $funnel))->assertOk();

        $normalized = $step->fresh()->layout_json;
        $this->assertIsArray($normalized);
        $this->assertArrayHasKey('root', $normalized);
        $this->assertArrayHasKey('sections', $normalized);
        $this->assertNotEmpty($normalized['root']);
    }

    public function test_funnel_analytics_exposes_enhanced_metrics_and_filter_state(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = $this->createJourneyFunnel($tenant, $user, 'enhanced-analytics');
        $steps = $funnel->steps()->orderBy('position')->get()->keyBy('type');

        $this->get(route('funnels.portal.step', ['funnelSlug' => $funnel->slug]))->assertOk();
        $this->post(route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $steps['opt_in']->slug]), [
            'email' => 'metric@example.com',
            'name' => 'Metric Lead',
            'website' => '',
        ])->assertRedirect();
        $this->post(route('funnels.portal.checkout', ['funnelSlug' => $funnel->slug, 'stepSlug' => $steps['checkout']->slug]), [
            'amount' => 1499,
            'website' => '',
        ])->assertRedirect();

        $response = $this->actingAs($user)->getJson(route('funnels.analytics', [
            'funnel' => $funnel,
            'step_id' => $steps['checkout']->id,
            'event_name' => 'funnel_checkout_started',
        ]));

        $response->assertOk();
        $response->assertJsonPath('analytics.filters.step_id', $steps['checkout']->id);
        $response->assertJsonPath('analytics.filters.event_name', 'funnel_checkout_started');
        $response->assertJsonPath('analytics.totals.checkout_start_count', 1);
        $response->assertJsonPath('analytics.totals.average_order_value', 1499.0);
        $response->assertJsonPath('analytics.totals.revenue_per_visit', 1499.0);
        $response->assertJsonStructure([
            'analytics' => [
                'daily_series',
                'conversion_funnel',
            ],
        ]);
    }

    public function test_funnel_analytics_export_downloads_csv(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = $this->createJourneyFunnel($tenant, $user, 'analytics-export');
        $steps = $funnel->steps()->orderBy('position')->get()->keyBy('type');

        $this->get(route('funnels.portal.step', ['funnelSlug' => $funnel->slug]))->assertOk();
        $this->post(route('funnels.portal.optin', ['funnelSlug' => $funnel->slug, 'stepSlug' => $steps['opt_in']->slug]), [
            'email' => 'export@example.com',
            'name' => 'Export Lead',
            'website' => '',
        ])->assertRedirect();

        $response = $this->actingAs($user)->get(route('funnels.analytics.export', $funnel));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertSee('Occurred At,Event,Step,Lead,Payment Status,Amount,Session', false);
        $response->assertSee('funnel_opt_in_submitted', false);
    }

    public function test_physical_order_analytics_view_shows_excel_download_actions(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = $this->createPhysicalOrderFunnel($tenant, $user, 'physical-order-actions');
        $this->seedPhysicalOrderAnalytics($funnel);

        $response = $this->actingAs($user)->get(route('funnels.analytics', $funnel));

        $response->assertOk();
        $response->assertSee('Pending Orders');
        $response->assertSee('Paid Orders');
        $response->assertSee('Order Directory');
        $response->assertSee('Download to Excel');
        $response->assertSee(route('funnels.analytics.orders.export', ['funnel' => $funnel, 'section' => 'pending']), false);
        $response->assertSee(route('funnels.analytics.orders.export', ['funnel' => $funnel, 'section' => 'paid']), false);
        $response->assertSee(route('funnels.analytics.orders.export', ['funnel' => $funnel, 'section' => 'directory']), false);
    }

    public function test_physical_order_excel_exports_download_real_xlsx_files_for_pending_paid_and_directory(): void
    {
        [$tenant, $user] = $this->createTenantUserWithRole('marketing-manager');
        $funnel = $this->createPhysicalOrderFunnel($tenant, $user, 'physical-order-export');
        $this->seedPhysicalOrderAnalytics($funnel);

        $pendingResponse = $this->actingAs($user)->get(route('funnels.analytics.orders.export', [
            'funnel' => $funnel,
            'section' => 'pending',
        ]));
        $pendingResponse->assertOk();
        $pendingResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx"', (string) $pendingResponse->headers->get('content-disposition'));
        $pendingBinary = $pendingResponse->getContent();
        $this->assertStringStartsWith('PK', $pendingBinary);
        $this->assertStringContainsString('xl/worksheets/sheet1.xml', $pendingBinary);
        $this->assertStringContainsString('Pending Orders', $pendingBinary);
        $this->assertStringContainsString('Pending Customer', $pendingBinary);
        $this->assertStringContainsString('Pending Product x1', $pendingBinary);

        $paidResponse = $this->actingAs($user)->get(route('funnels.analytics.orders.export', [
            'funnel' => $funnel,
            'section' => 'paid',
        ]));
        $paidResponse->assertOk();
        $paidResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $paidBinary = $paidResponse->getContent();
        $this->assertStringStartsWith('PK', $paidBinary);
        $this->assertStringContainsString('Paid Orders', $paidBinary);
        $this->assertStringContainsString('Paid Customer', $paidBinary);
        $this->assertStringContainsString('Protein Box x2', $paidBinary);
        $this->assertStringContainsString('https://tracking.example.com/paid-order', $paidBinary);

        $directoryResponse = $this->actingAs($user)->get(route('funnels.analytics.orders.export', [
            'funnel' => $funnel,
            'section' => 'directory',
        ]));
        $directoryResponse->assertOk();
        $directoryResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $directoryBinary = $directoryResponse->getContent();
        $this->assertStringStartsWith('PK', $directoryBinary);
        $this->assertStringContainsString('Order Directory', $directoryBinary);
        $this->assertStringContainsString('Paid Customer', $directoryBinary);
        $this->assertStringContainsString('Pending Customer', $directoryBinary);
        $this->assertStringContainsString('Leave package at front desk', $directoryBinary);
    }

    private function createTenantUserWithRole(string $roleSlug, string $companyName = 'Tracked Workspace'): array
    {
        $tenant = Tenant::create([
            'company_name' => $companyName,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
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

    private function createJourneyFunnel(Tenant $tenant, User $user, string $slug): Funnel
    {
        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'status' => 'published',
        ]);

        $steps = [
            ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'position' => 1, 'layout_json' => $this->buttonLayout('next_step')],
            ['title' => 'Opt In', 'slug' => 'opt-in', 'type' => 'opt_in', 'position' => 2, 'layout_json' => $this->formLayout()],
            ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'position' => 3, 'layout_json' => $this->buttonLayout('next_step')],
            ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'position' => 4, 'price' => 1499, 'layout_json' => $this->buttonLayout('checkout')],
            ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'position' => 5, 'layout_json' => $this->emptyLayout()],
        ];

        foreach ($steps as $step) {
            FunnelStep::create(array_merge([
                'funnel_id' => $funnel->id,
                'is_active' => true,
            ], $step));
        }

        return $funnel->fresh('steps');
    }

    private function createPhysicalOrderFunnel(Tenant $tenant, User $user, string $slug): Funnel
    {
        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'created_by' => $user->id,
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'purpose' => 'physical_product',
            'status' => 'published',
        ]);

        $steps = [
            ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'position' => 1, 'layout_json' => $this->buttonLayout('next_step')],
            ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'position' => 2, 'price' => 1499, 'layout_json' => $this->buttonLayout('checkout')],
            ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'position' => 3, 'layout_json' => $this->emptyLayout()],
        ];

        foreach ($steps as $step) {
            FunnelStep::create(array_merge([
                'funnel_id' => $funnel->id,
                'is_active' => true,
            ], $step));
        }

        return $funnel->fresh('steps');
    }

    private function seedPhysicalOrderAnalytics(Funnel $funnel): void
    {
        $checkoutStep = $funnel->steps()->where('type', 'checkout')->firstOrFail();

        $paidLead = Lead::create([
            'tenant_id' => $funnel->tenant_id,
            'name' => 'Paid Customer',
            'email' => 'paid@example.com',
            'phone' => '09123456789',
            'status' => 'new',
            'score' => 0,
        ]);

        $pendingLead = Lead::create([
            'tenant_id' => $funnel->tenant_id,
            'name' => 'Pending Customer',
            'email' => 'pending@example.com',
            'phone' => '09987654321',
            'status' => 'new',
            'score' => 0,
        ]);

        $paidPayment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'lead_id' => $paidLead->id,
            'amount' => 2998,
            'subtotal_amount' => 2998,
            'discount_amount' => 0,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'provider' => 'manual',
            'session_identifier' => 'session-paid-order',
        ]);

        $pendingPayment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'lead_id' => $pendingLead->id,
            'amount' => 1499,
            'subtotal_amount' => 1499,
            'discount_amount' => 0,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
            'session_identifier' => 'session-pending-order',
        ]);

        FunnelEvent::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'lead_id' => $paidLead->id,
            'payment_id' => $paidPayment->id,
            'event_name' => FunnelTrackingService::EVENT_CHECKOUT_STARTED,
            'session_identifier' => $paidPayment->session_identifier,
            'meta' => [
                'step_slug' => $checkoutStep->slug,
                'step_type' => $checkoutStep->type,
                'amount' => 2998,
                'payment_status' => 'paid',
                'provider' => 'manual',
                'order_key' => 'payment:' . $paidPayment->id,
                'funnel_purpose' => 'physical_product',
                'customer' => [
                    'full_name' => 'Paid Customer',
                    'email' => 'paid@example.com',
                    'phone' => '09123456789',
                ],
                'shipping' => [
                    'street' => '123 Paid Street',
                    'barangay' => 'Barangay Uno',
                    'city_municipality' => 'Manila',
                    'province' => 'Metro Manila',
                    'postal_code' => '1000',
                    'notes' => 'Leave package at front desk',
                ],
                'delivery_address' => '123 Paid Street, Barangay Uno, Manila, Metro Manila, 1000',
                'order_items' => [
                    ['name' => 'Protein Box', 'quantity' => 2, 'price' => '1499.00', 'badge' => 'Best Seller'],
                ],
                'order_quantity' => 2,
                'order_items_label' => 'Protein Box x2',
            ],
            'occurred_at' => now()->subHours(2),
        ]);

        app(FunnelTrackingService::class)->trackPaymentPaid($paidPayment, ['source' => 'feature_test_paid_order']);

        FunnelEvent::query()
            ->where('payment_id', $paidPayment->id)
            ->where('event_name', FunnelTrackingService::EVENT_PAYMENT_PAID)
            ->latest('id')
            ->firstOrFail()
            ->update(['occurred_at' => now()->subHour()]);

        FunnelEvent::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'lead_id' => $paidLead->id,
            'payment_id' => $paidPayment->id,
            'event_name' => FunnelTrackingService::EVENT_ORDER_DELIVERY_UPDATED,
            'session_identifier' => $paidPayment->session_identifier,
            'meta' => [
                'order_key' => 'payment:' . $paidPayment->id,
                'recipient_email' => 'paid@example.com',
                'customer_name' => 'Paid Customer',
                'delivery_status' => 'shipped',
                'tracking_url' => 'https://tracking.example.com/paid-order',
                'courier_name' => 'LBC',
                'custom_message' => 'Packed and ready for delivery.',
            ],
            'occurred_at' => now()->subMinutes(50),
        ]);

        FunnelEvent::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $checkoutStep->id,
            'lead_id' => $pendingLead->id,
            'payment_id' => $pendingPayment->id,
            'event_name' => FunnelTrackingService::EVENT_CHECKOUT_STARTED,
            'session_identifier' => $pendingPayment->session_identifier,
            'meta' => [
                'step_slug' => $checkoutStep->slug,
                'step_type' => $checkoutStep->type,
                'amount' => 1499,
                'payment_status' => 'pending',
                'provider' => 'paymongo',
                'order_key' => 'payment:' . $pendingPayment->id,
                'funnel_purpose' => 'physical_product',
                'customer' => [
                    'full_name' => 'Pending Customer',
                    'email' => 'pending@example.com',
                    'phone' => '09987654321',
                ],
                'shipping' => [
                    'street' => '456 Pending Avenue',
                    'barangay' => 'Barangay Dos',
                    'city_municipality' => 'Quezon City',
                    'province' => 'Metro Manila',
                    'postal_code' => '1100',
                ],
                'delivery_address' => '456 Pending Avenue, Barangay Dos, Quezon City, Metro Manila, 1100',
                'order_items' => [
                    ['name' => 'Pending Product', 'quantity' => 1, 'price' => '1499.00'],
                ],
                'order_quantity' => 1,
                'order_items_label' => 'Pending Product x1',
            ],
            'occurred_at' => now()->subMinutes(30),
        ]);
    }

    private function emptyLayout(): array
    {
        return ['root' => [], 'sections' => []];
    }

    private function formLayout(): array
    {
        return [
            'root' => [
                [
                    'kind' => 'section',
                    'id' => 'sec-form',
                    'rows' => [
                        [
                            'id' => 'row-form',
                            'columns' => [
                                [
                                    'id' => 'col-form',
                                    'elements' => [
                                        [
                                            'id' => 'el-form',
                                            'type' => 'form',
                                            'content' => 'Submit',
                                            'settings' => [
                                                'fields' => [
                                                    ['type' => 'email', 'label' => 'Email', 'required' => true],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'sections' => [],
        ];
    }

    private function buttonLayout(string $actionType): array
    {
        return [
            'root' => [
                [
                    'kind' => 'section',
                    'id' => 'sec-button-' . $actionType,
                    'rows' => [
                        [
                            'id' => 'row-button-' . $actionType,
                            'columns' => [
                                [
                                    'id' => 'col-button-' . $actionType,
                                    'elements' => [
                                        [
                                            'id' => 'el-button-' . $actionType,
                                            'type' => 'button',
                                            'content' => 'Continue',
                                            'settings' => [
                                                'actionType' => $actionType,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'sections' => [],
        ];
    }
}
