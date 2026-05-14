<?php

namespace Tests\Feature;

use App\Models\Funnel;
use App\Models\ExternalDeliveryLog;
use App\Models\FunnelEvent;
use App\Models\FunnelStep;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\N8nEmailOrchestrator;
use App\Services\FunnelTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunnelPaymentConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'funnels.paid_confirmation_email_enabled' => true,
            'services.brevo.api_key' => null,
            'services.n8n.webhook_url' => '',
            'mail.default' => 'log',
        ]);
    }

    public function test_paymongo_paid_event_sends_customer_confirmation_email_once(): void
    {
        [$funnel, $step, $lead] = $this->createFunnelCheckoutContext();

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'amount' => 1499,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
            'provider_reference' => 'cs_checkout_001',
            'payment_method' => 'gcash',
            'session_identifier' => 'sess-paymongo-001',
        ]);

        FunnelEvent::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'payment_id' => $payment->id,
            'event_name' => FunnelTrackingService::EVENT_CHECKOUT_STARTED,
            'session_identifier' => 'sess-paymongo-001',
            'meta' => [
                'customer' => [
                    'full_name' => 'PayMongo Buyer',
                    'email' => 'buyer@example.com',
                ],
                'order_items' => [
                    ['name' => 'Starter Bundle', 'quantity' => 1, 'price' => 'PHP 1,499.00'],
                ],
            ],
            'occurred_at' => now()->subMinute(),
        ]);

        $tracking = app(FunnelTrackingService::class);
        $tracking->trackPaymentPaid($payment);
        $tracking->trackPaymentPaid($payment);

        $this->assertSame(1, FunnelEvent::query()
            ->where('payment_id', $payment->id)
            ->where('event_name', FunnelTrackingService::EVENT_PAYMENT_PAID)
            ->count());

        $this->assertDatabaseHas('external_delivery_logs', [
            'channel' => 'email',
            'event_name' => 'funnel_payment_paid_customer',
            'recipient' => 'buyer@example.com',
            'status' => 'processed',
            'idempotency_key' => 'funnel_paid_customer_email:' . FunnelEvent::query()
                ->where('payment_id', $payment->id)
                ->where('event_name', FunnelTrackingService::EVENT_PAYMENT_PAID)
                ->value('id'),
        ]);
    }

    public function test_manual_receipt_confirmation_uses_checkout_customer_email_without_duplication(): void
    {
        [$funnel, $step, $lead] = $this->createFunnelCheckoutContext();

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'amount' => 799,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'provider' => 'manual',
            'payment_method' => 'manual_transfer',
            'session_identifier' => 'sess-manual-001',
        ]);

        FunnelEvent::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'payment_id' => $payment->id,
            'event_name' => FunnelTrackingService::EVENT_CHECKOUT_STARTED,
            'session_identifier' => 'sess-manual-001',
            'meta' => [
                'customer' => [
                    'full_name' => 'Manual Buyer',
                    'email' => 'manual@example.com',
                ],
                'order_items' => [
                    ['name' => 'QR Order', 'quantity' => 2, 'price' => 'PHP 799.00'],
                ],
            ],
            'occurred_at' => now()->subMinute(),
        ]);

        $tracking = app(FunnelTrackingService::class);
        $tracking->trackPaymentPaid($payment, ['source' => 'receipt_review_approved']);
        $tracking->trackPaymentPaid($payment, ['source' => 'receipt_review_approved']);

        $this->assertSame(1, FunnelEvent::query()
            ->where('payment_id', $payment->id)
            ->where('event_name', FunnelTrackingService::EVENT_PAYMENT_PAID)
            ->count());

        $this->assertSame(1, \App\Models\ExternalDeliveryLog::query()
            ->where('channel', 'email')
            ->where('event_name', 'funnel_payment_paid_customer')
            ->where('recipient', 'manual@example.com')
            ->count());
    }

    public function test_paid_event_can_handoff_rich_customer_email_to_n8n_without_local_mailer_dependency(): void
    {
        config([
            'services.n8n.webhook_url' => 'https://n8n.example.test/webhook/laravel-auth-events',
        ]);

        $orchestrator = \Mockery::mock(N8nEmailOrchestrator::class);
        $orchestrator->shouldReceive('dispatch')
            ->once()
            ->withArgs(function (string $eventName, array $payload): bool {
                return $eventName === 'funnel_payment_paid_customer'
                    && ($payload['email'] ?? null) === 'handoff@example.com'
                    && ($payload['subject'] ?? null) === 'Payment successful - Summer Offer'
                    && str_contains((string) ($payload['html'] ?? ''), 'Ordered products')
                    && str_contains((string) ($payload['html'] ?? ''), 'Estimated arrival')
                    && str_contains((string) ($payload['text'] ?? ''), 'Set your password here:')
                    && ($payload['portal_role'] ?? null) === 'Customer';
            })
            ->andReturn(true);
        $this->app->instance(N8nEmailOrchestrator::class, $orchestrator);

        [$funnel, $step, $lead] = $this->createFunnelCheckoutContext();

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'amount' => 1848,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
            'provider_reference' => 'cs_checkout_002',
            'payment_method' => 'gcash',
            'session_identifier' => 'sess-paymongo-002',
        ]);

        FunnelEvent::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'payment_id' => $payment->id,
            'event_name' => FunnelTrackingService::EVENT_CHECKOUT_STARTED,
            'session_identifier' => 'sess-paymongo-002',
            'meta' => [
                'customer' => [
                    'full_name' => 'Handoff Buyer',
                    'email' => 'handoff@example.com',
                    'phone' => '09151234567',
                ],
                'shipping' => [
                    'street' => 'Mahogany Street',
                    'barangay' => 'New Pandan',
                    'city_municipality' => 'Panabo City',
                    'province' => 'Davao del Norte',
                    'postal_code' => '2456',
                ],
                'order_items' => [
                    ['name' => 'Weekend Club Cap', 'quantity' => 1, 'price' => 'PHP 999.00'],
                    ['name' => 'Classic Slim Wallet', 'quantity' => 1, 'price' => 'PHP 849.00'],
                ],
            ],
            'occurred_at' => now()->subMinute(),
        ]);

        app(FunnelTrackingService::class)->trackPaymentPaid($payment);

        $this->assertDatabaseHas('external_delivery_logs', [
            'channel' => 'email',
            'event_name' => 'funnel_payment_paid_customer',
            'recipient' => 'handoff@example.com',
            'provider' => 'n8n',
            'status' => 'processed',
            'idempotency_key' => 'funnel_paid_customer_email:' . FunnelEvent::query()
                ->where('payment_id', $payment->id)
                ->where('event_name', FunnelTrackingService::EVENT_PAYMENT_PAID)
                ->value('id'),
        ]);
    }

    public function test_paid_customer_email_can_retry_after_non_delivery_or_stale_handoff_logs(): void
    {
        config([
            'services.n8n.webhook_url' => 'https://n8n.example.test/webhook/laravel-auth-events',
        ]);

        $orchestrator = \Mockery::mock(N8nEmailOrchestrator::class);
        $orchestrator->shouldReceive('dispatch')
            ->once()
            ->andReturn(true);
        $this->app->instance(N8nEmailOrchestrator::class, $orchestrator);

        [$funnel, $step, $lead] = $this->createFunnelCheckoutContext();

        $payment = Payment::create([
            'tenant_id' => $funnel->tenant_id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'amount' => 2299,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
            'provider_reference' => 'cs_checkout_003',
            'payment_method' => 'gcash',
            'session_identifier' => 'sess-paymongo-003',
        ]);

        FunnelEvent::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'payment_id' => $payment->id,
            'event_name' => FunnelTrackingService::EVENT_CHECKOUT_STARTED,
            'session_identifier' => 'sess-paymongo-003',
            'meta' => [
                'customer' => [
                    'full_name' => 'Retry Buyer',
                    'email' => 'lead@example.com',
                ],
                'order_items' => [
                    ['name' => 'Everyday White Sneaker', 'quantity' => 1, 'price' => 'PHP 2,299.00'],
                ],
            ],
            'occurred_at' => now()->subMinute(),
        ]);

        $paidEvent = FunnelEvent::create([
            'tenant_id' => $funnel->tenant_id,
            'funnel_id' => $funnel->id,
            'funnel_step_id' => $step->id,
            'lead_id' => $lead->id,
            'payment_id' => $payment->id,
            'event_name' => FunnelTrackingService::EVENT_PAYMENT_PAID,
            'session_identifier' => 'sess-paymongo-003',
            'meta' => [
                'customer' => [
                    'full_name' => 'Retry Buyer',
                    'email' => 'lead@example.com',
                ],
                'order_items' => [
                    ['name' => 'Everyday White Sneaker', 'quantity' => 1, 'price' => 'PHP 2,299.00'],
                ],
            ],
            'occurred_at' => now(),
        ]);

        ExternalDeliveryLog::query()->create([
            'tenant_id' => $funnel->tenant_id,
            'lead_id' => $lead->id,
            'channel' => 'email',
            'event_name' => 'funnel_payment_paid_customer',
            'recipient' => 'lead@example.com',
            'provider' => 'log',
            'status' => 'processed',
            'error_message' => 'Laravel mail fallback used a non-delivery mailer (log).',
            'idempotency_key' => 'funnel_paid_customer_email:' . $paidEvent->id,
            'is_billable' => true,
            'meta' => ['handoff_only' => false],
        ]);

        app(\App\Services\FunnelPaidCustomerEmailService::class)->sendForPaidEvent($paidEvent);

        $this->assertSame(2, ExternalDeliveryLog::query()
            ->where('channel', 'email')
            ->where('event_name', 'funnel_payment_paid_customer')
            ->where('idempotency_key', 'funnel_paid_customer_email:' . $paidEvent->id)
            ->count());
    }

    public function test_email_status_callback_marks_paid_customer_delivery_log_as_sent(): void
    {
        config([
            'services.n8n.callback_bearer_token' => 'callback-secret',
        ]);

        $tenant = Tenant::create([
            'company_name' => 'Callback Workspace',
            'subscription_plan' => 'Starter',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'callback-customer@example.com',
            'role' => 'customer',
            'status' => 'inactive',
            'activation_state' => 'invited',
            'is_customer_portal_user' => true,
        ]);

        $log = ExternalDeliveryLog::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'channel' => 'email',
            'event_name' => 'funnel_payment_paid_customer',
            'recipient' => 'callback-customer@example.com',
            'provider' => 'n8n',
            'status' => 'processed',
            'idempotency_key' => 'funnel_paid_customer_email:callback',
            'is_billable' => true,
            'meta' => ['handoff_only' => true],
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer callback-secret',
        ])->postJson(route('api.n8n.email-status'), [
            'event_name' => 'funnel_payment_paid_customer',
            'email' => 'callback-customer@example.com',
            'user_id' => $user->id,
            'status' => 'sent',
            'sent_at' => now()->toIso8601String(),
            'idempotency_key' => 'funnel_paid_customer_email:callback',
            'provider' => 'n8n',
            'response_code' => 202,
        ])->assertOk();

        $this->assertDatabaseHas('external_delivery_logs', [
            'id' => $log->id,
            'status' => 'sent',
            'response_code' => 202,
            'recipient' => 'callback-customer@example.com',
        ]);
    }

    /**
     * @return array{0: Funnel, 1: FunnelStep, 2: Lead}
     */
    private function createFunnelCheckoutContext(): array
    {
        $tenant = Tenant::create([
            'company_name' => 'Checkout Workspace',
            'subscription_plan' => 'Starter',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        $funnel = Funnel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Summer Offer',
            'slug' => 'summer-offer',
            'status' => 'published',
            'purpose' => 'physical_product',
        ]);

        $step = FunnelStep::create([
            'funnel_id' => $funnel->id,
            'title' => 'Checkout',
            'slug' => 'checkout',
            'type' => 'checkout',
            'price' => 1499,
            'position' => 1,
            'is_active' => true,
        ]);

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'name' => 'Checkout Lead',
            'email' => 'lead@example.com',
            'status' => 'new',
            'score' => 0,
        ]);

        return [$funnel, $step, $lead];
    }
}
