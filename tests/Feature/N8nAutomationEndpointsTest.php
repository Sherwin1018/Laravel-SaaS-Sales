<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class N8nAutomationEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private array $headers = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        config(['services.n8n.callback_bearer_token' => 'test-n8n-token']);
        $this->headers = ['Authorization' => 'Bearer test-n8n-token'];
    }

    public function test_lead_activity_and_score_endpoints_work(): void
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Tenant A',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        $lead = Lead::withoutGlobalScope('tenant')->create([
            'tenant_id' => $tenant->id,
            'name' => 'Lead A',
            'email' => 'lead@example.com',
            'status' => 'new',
            'score' => 0,
        ]);

        $activityResponse = $this->withHeaders($this->headers)->postJson('/api/n8n/lead-activity', [
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'activity_type' => 'Lead Captured',
            'notes' => 'Captured from funnel',
        ]);

        $activityResponse->assertOk()->assertJson(['ok' => true]);

        $scoreResponse = $this->withHeaders($this->headers)->postJson('/api/n8n/lead-score', [
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'event' => 'lead_captured',
            'points' => 10,
        ]);

        $scoreResponse->assertOk()->assertJson([
            'ok' => true,
            'lead_id' => $lead->id,
            'score' => 10,
        ]);
    }

    public function test_invoice_status_and_payment_recovered_endpoints_work(): void
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Tenant B',
            'status' => 'active',
            'billing_status' => 'overdue',
        ]);

        $payment = Payment::query()->create([
            'tenant_id' => $tenant->id,
            'payment_type' => Payment::TYPE_PLATFORM_SUBSCRIPTION,
            'amount' => 1000,
            'status' => 'failed',
            'payment_date' => now()->toDateString(),
            'provider_reference' => 'inv_1001',
        ]);

        $statusResponse = $this->withHeaders($this->headers)->getJson('/api/n8n/invoice-status?tenant_id=' . $tenant->id . '&invoice_id=' . $payment->provider_reference);
        $statusResponse->assertOk()->assertJson([
            'ok' => true,
            'status' => 'failed',
        ]);

        $recoveredResponse = $this->withHeaders($this->headers)->postJson('/api/n8n/payment-recovered', [
            'tenant_id' => $tenant->id,
            'invoice_id' => $payment->provider_reference,
        ]);

        $recoveredResponse->assertOk()->assertJson([
            'ok' => true,
            'tenant_status' => 'active',
            'billing_status' => 'current',
        ]);
    }

    public function test_analytics_and_owner_digest_endpoints_work(): void
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Tenant C',
            'status' => 'trial',
            'billing_status' => 'trial',
        ]);

        User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => 'Password@123456',
            'role' => 'account-owner',
            'status' => 'active',
            'activation_state' => 'active',
        ]);

        $analyticsResponse = $this->withHeaders($this->headers)->getJson('/api/n8n/analytics-daily');
        $analyticsResponse->assertOk()->assertJson(['ok' => true]);

        $digestResponse = $this->withHeaders($this->headers)->postJson('/api/n8n/send-owner-digest', [
            'date' => now()->toDateString(),
            'audience' => 'trial_owners',
            'include_upgrade_nudges' => true,
        ]);
        $digestResponse->assertOk()->assertJson(['ok' => true]);
    }
}
