<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\FinanceAuditLog;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebhookReceipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayMongoWebhookReliabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        config(['services.paymongo.webhook_secret' => null]);
    }

    public function test_repeated_event_id_is_short_circuited_without_duplicate_subscription_activation(): void
    {
        [$tenant, $payment] = $this->createTrialUpgradePayment();

        $payload = $this->checkoutSessionPaidPayload('evt_repeat_001', (string) $payment->provider_reference);

        $firstResponse = $this->postJson('/webhooks/paymongo', $payload);
        $secondResponse = $this->postJson('/webhooks/paymongo', $payload);

        $firstResponse->assertOk()->assertJson(['ok' => true, 'duplicate' => false]);
        $secondResponse->assertOk()->assertJson(['ok' => true, 'duplicate' => true]);

        $tenant->refresh();
        $this->assertSame('active', $tenant->status);
        $this->assertSame('current', $tenant->billing_status);
        $this->assertSame(1, FinanceAuditLog::query()->where('payment_id', $payment->id)->where('event_type', 'subscription_paid')->count());

        $receipt = WebhookReceipt::query()
            ->where('provider', 'paymongo')
            ->where('event_id', 'evt_repeat_001')
            ->firstOrFail();

        $this->assertSame('processed', $receipt->status);
        $this->assertSame(2, (int) $receipt->attempts);
    }

    public function test_checkout_and_payment_paid_events_do_not_double_activate_same_trial_upgrade_payment(): void
    {
        [$tenant, $payment] = $this->createTrialUpgradePayment();

        $checkoutResponse = $this->postJson('/webhooks/paymongo', $this->checkoutSessionPaidPayload('evt_checkout_001', (string) $payment->provider_reference));
        $checkoutResponse->assertOk()->assertJson(['ok' => true, 'duplicate' => false]);

        $tenant->refresh();
        $firstRenewalAt = $tenant->subscription_renews_at?->copy();

        $paymentResponse = $this->postJson('/webhooks/paymongo', $this->paymentPaidPayload('evt_payment_001', $payment->id));
        $paymentResponse->assertOk()->assertJson(['ok' => true, 'duplicate' => false]);

        $tenant->refresh();
        $this->assertNotNull($firstRenewalAt);
        $this->assertTrue($tenant->subscription_renews_at->equalTo($firstRenewalAt));
        $this->assertSame(1, FinanceAuditLog::query()->where('payment_id', $payment->id)->where('event_type', 'subscription_paid')->count());
        $this->assertSame(2, WebhookReceipt::query()->where('provider', 'paymongo')->count());
    }

    /**
     * @return array{0: Tenant, 1: Payment}
     */
    private function createTrialUpgradePayment(): array
    {
        Plan::query()->updateOrCreate(
            ['code' => 'starter'],
            [
                'name' => 'Starter',
                'price' => 1499,
                'period' => 'per month',
                'summary' => 'Starter plan',
                'features' => ['Feature'],
                'spotlight' => null,
                'is_active' => true,
                'sort_order' => 0,
                'max_users' => 5,
                'max_leads' => 100,
                'max_funnels' => 3,
                'max_templates' => 2,
                'max_workflows' => 1,
                'max_monthly_messages' => 1000,
                'automation_enabled' => false,
            ]
        );

        $tenant = Tenant::create([
            'company_name' => 'Webhook Workspace',
            'subscription_plan' => 'Free Trial',
            'status' => 'trial',
            'billing_status' => 'trial',
            'trial_starts_at' => now()->subDays(2),
            'trial_ends_at' => now()->addDays(5),
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $owner->roles()->attach($this->role('account-owner'));

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'payment_type' => Payment::TYPE_PLATFORM_SUBSCRIPTION,
            'amount' => 1499,
            'status' => 'pending',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
            'provider_reference' => 'cs_trial_upgrade_001',
        ]);

        return [$tenant, $payment];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutSessionPaidPayload(string $eventId, string $sessionId): array
    {
        return [
            'data' => [
                'id' => $eventId,
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => $sessionId,
                        'attributes' => [
                            'metadata' => [
                                'flow' => 'trial_upgrade',
                                'plan_code' => 'starter',
                                'plan_name' => 'Starter',
                            ],
                            'payments' => [
                                [
                                    'attributes' => [
                                        'source' => [
                                            'type' => 'gcash',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentPaidPayload(string $eventId, int $paymentId): array
    {
        return [
            'data' => [
                'id' => $eventId,
                'attributes' => [
                    'type' => 'payment.paid',
                    'data' => [
                        'attributes' => [
                            'metadata' => [
                                'payment_id' => $paymentId,
                                'flow' => 'trial_upgrade',
                                'plan_code' => 'starter',
                            ],
                            'source' => [
                                'type' => 'gcash',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function role(string $slug): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => ucwords(str_replace('-', ' ', $slug))]
        );
    }
}
