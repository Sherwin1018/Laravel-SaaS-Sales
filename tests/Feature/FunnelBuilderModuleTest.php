<?php

namespace Tests\Feature;

use App\Models\Funnel;
use App\Models\FunnelEvent;
use App\Models\FunnelStep;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
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
