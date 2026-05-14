<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Services\N8nEmailOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class N8nDispatchReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_skips_duplicate_successful_webhook_by_idempotency_key(): void
    {
        Http::fake([
            'https://n8n.example.test/*' => Http::response(['ok' => true], 200),
        ]);

        config([
            'services.n8n.webhook_url' => 'https://n8n.example.test/webhook',
            'services.n8n.webhook_retry_times' => 2,
            'services.n8n.webhook_retry_delay_ms' => 50,
        ]);

        $tenant = Tenant::create([
            'company_name' => 'Dispatch Workspace',
            'status' => 'active',
            'billing_status' => 'current',
            'subscription_plan' => 'Growth',
        ]);

        $payload = [
            'tenant_id' => $tenant->id,
            'event_id' => 'dispatch-event-001',
            'idempotency_key' => 'dispatch-key-001',
        ];

        $service = app(N8nEmailOrchestrator::class);

        $this->assertTrue($service->dispatch('lead_captured', $payload));
        $this->assertTrue($service->dispatch('lead_captured', $payload));

        Http::assertSentCount(1);
        $this->assertDatabaseHas('external_delivery_logs', [
            'channel' => 'webhook',
            'provider' => 'n8n',
            'status' => 'sent',
            'idempotency_key' => 'dispatch-key-001',
        ]);
        $this->assertDatabaseHas('external_delivery_logs', [
            'channel' => 'webhook',
            'provider' => 'n8n',
            'status' => 'duplicate',
            'idempotency_key' => 'dispatch-key-001',
        ]);
    }

    public function test_dispatch_routes_events_to_direct_auth_automation_and_finance_webhooks(): void
    {
        Http::fake([
            'https://n8n.example.test/*' => Http::response(['ok' => true], 200),
        ]);

        config([
            'services.n8n.webhook_url' => 'https://n8n.example.test/webhook/laravel-events',
            'services.n8n.base_url' => 'https://n8n.example.test',
        ]);

        $tenant = Tenant::create([
            'company_name' => 'Dispatch Routing Workspace',
            'status' => 'active',
            'billing_status' => 'current',
            'subscription_plan' => 'Growth',
        ]);

        $service = app(N8nEmailOrchestrator::class);

        $this->assertTrue($service->dispatch('team_member_invited', [
            'tenant_id' => $tenant->id,
            'event_id' => 'auth-route-001',
            'idempotency_key' => 'auth-route-001',
            'email' => 'team@example.com',
            'name' => 'Team Member',
        ]));
        $this->assertTrue($service->dispatch('funnel_checkout_started', [
            'tenant_id' => $tenant->id,
            'event_id' => 'automation-route-001',
            'idempotency_key' => 'automation-route-001',
        ]));
        $this->assertTrue($service->dispatch('settlement_payout_recorded', [
            'tenant_id' => $tenant->id,
            'event_id' => 'finance-route-001',
            'idempotency_key' => 'finance-route-001',
        ]));
        $this->assertTrue($service->dispatch('owner_digest', [
            'tenant_id' => $tenant->id,
            'event_id' => 'unified-route-001',
            'idempotency_key' => 'unified-route-001',
        ]));

        Http::assertSent(fn ($request) => $request->url() === 'https://n8n.example.test/webhook/laravel-auth-events');
        Http::assertSent(fn ($request) => $request->url() === 'https://n8n.example.test/webhook/saas-automation-events');
        Http::assertSent(fn ($request) => $request->url() === 'https://n8n.example.test/webhook/laravel-finance-events');
        Http::assertSent(fn ($request) => $request->url() === 'https://n8n.example.test/webhook/laravel-unified-events');
    }

    public function test_starter_plan_uses_limited_local_automation_without_shared_webhook_handoff(): void
    {
        Http::fake([
            'https://n8n.example.test/*' => Http::response(['ok' => true], 200),
        ]);
        Mail::fake();

        config([
            'services.n8n.webhook_url' => 'https://n8n.example.test/webhook',
        ]);

        $tenant = Tenant::create([
            'company_name' => 'Starter Limited Workspace',
            'status' => 'active',
            'billing_status' => 'current',
            'subscription_plan' => 'Starter',
        ]);

        $service = app(N8nEmailOrchestrator::class);

        $this->assertTrue($service->dispatch('funnel_checkout_abandoned', [
            'tenant_id' => $tenant->id,
            'event_id' => 'starter-abandoned-001',
            'idempotency_key' => 'starter-abandoned-001',
            'recipient_email' => 'lead@example.com',
            'name' => 'Starter Lead',
        ]));

        Http::assertNothingSent();
        $this->assertDatabaseHas('external_delivery_logs', [
            'channel' => 'email',
            'event_name' => 'funnel_checkout_abandoned_customer',
            'status' => 'processed',
            'idempotency_key' => 'starter-abandoned-001',
        ]);
        $this->assertDatabaseHas('external_delivery_logs', [
            'channel' => 'webhook',
            'provider' => 'n8n',
            'event_name' => 'funnel_checkout_abandoned_customer',
            'status' => 'processed',
            'idempotency_key' => 'starter-abandoned-001',
        ]);
    }
}
