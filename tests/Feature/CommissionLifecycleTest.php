<?php

namespace Tests\Feature;

use App\Models\CommissionEntry;
use App\Models\CommissionPlan;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CommissionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_funnel_payment_creates_sales_and_marketing_commissions_and_failed_payment_reverses_them(): void
    {
        Http::fake(['https://n8n.test/*' => Http::response(['ok' => true], 200)]);
        config(['services.n8n.webhook_url' => 'https://n8n.test/webhook']);

        [$tenant, $salesAgent, $marketingManager, $payment, $plan] = $this->commissionFixture();

        app(CommissionService::class)->syncPayment($payment, $plan);

        $entries = CommissionEntry::query()
            ->where('payment_id', $payment->id)
            ->orderBy('commission_type')
            ->get();

        $this->assertCount(2, $entries);
        $this->assertSame(
            [CommissionEntry::STATUS_HELD, CommissionEntry::STATUS_HELD],
            $entries->pluck('status')->all()
        );
        $this->assertSame(
            ['marketing_manager', 'sales_agent'],
            $entries->pluck('commission_type')->all()
        );
        $this->assertEqualsCanonicalizing(
            [$marketingManager->id, $salesAgent->id],
            $entries->pluck('user_id')->all()
        );

        $payment->update(['status' => 'failed']);
        app(CommissionService::class)->reverseForPayment($payment, 'payment_failed');

        $this->assertSame(
            [CommissionEntry::STATUS_REVERSED, CommissionEntry::STATUS_REVERSED],
            CommissionEntry::query()
                ->where('payment_id', $payment->id)
                ->orderBy('commission_type')
                ->pluck('status')
                ->all()
        );
    }

    public function test_release_held_command_marks_due_commissions_payable_and_dispatches_events(): void
    {
        Http::fake(['https://n8n.test/*' => Http::response(['ok' => true], 200)]);
        config(['services.n8n.webhook_url' => 'https://n8n.test/webhook']);

        [, , , $payment, $plan] = $this->commissionFixture();

        app(CommissionService::class)->syncPayment($payment, $plan);

        CommissionEntry::query()
            ->where('payment_id', $payment->id)
            ->update([
                'hold_until' => now()->subMinute(),
            ]);

        $this->artisan('commissions:release-held')
            ->expectsOutput('Released 2 commission entries.')
            ->assertExitCode(0);

        $this->assertSame(
            [CommissionEntry::STATUS_PAYABLE, CommissionEntry::STATUS_PAYABLE],
            CommissionEntry::query()
                ->where('payment_id', $payment->id)
                ->orderBy('commission_type')
                ->pluck('status')
                ->all()
        );

        $eventNames = collect(Http::recorded())
            ->map(fn (array $record) => data_get($record[0]->data(), 'event_name'))
            ->filter()
            ->values();

        $this->assertSame(2, $eventNames->filter(fn ($name) => $name === 'commission_created')->count());
        $this->assertSame(2, $eventNames->filter(fn ($name) => $name === 'commission_payable')->count());
    }

    /**
     * @return array{0: Tenant, 1: User, 2: User, 3: Payment, 4: CommissionPlan}
     */
    private function commissionFixture(): array
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Commission Tenant',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        $salesAgent = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $salesAgent->roles()->attach($this->role('sales-agent'));
        $salesAgent->load('roles');

        $marketingManager = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $marketingManager->roles()->attach($this->role('marketing-manager'));
        $marketingManager->load('roles');

        $lead = Lead::query()->create([
            'tenant_id' => $tenant->id,
            'assigned_to' => $salesAgent->id,
            'name' => 'Lead Customer',
            'email' => 'lead@example.test',
            'source_campaign' => 'Summer Launch',
            'status' => 'new',
        ]);

        $payment = Payment::query()->create([
            'tenant_id' => $tenant->id,
            'lead_id' => $lead->id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'amount' => 1000,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
        ]);

        $plan = CommissionPlan::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Plan',
            'is_active' => true,
            'is_default' => true,
            'gateway_fee_rate' => 3.00,
            'platform_fee_rate' => 2.00,
            'sales_agent_rate' => 7.00,
            'marketing_manager_rate' => 3.00,
            'hold_days' => 7,
            'sales_attribution_model' => 'assigned_lead',
            'marketing_attribution_model' => 'last_touch_campaign',
            'default_marketing_manager_user_id' => $marketingManager->id,
        ]);

        return [$tenant, $salesAgent, $marketingManager, $payment, $plan];
    }

    private function role(string $slug): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => ucwords(str_replace('-', ' ', $slug))]
        );
    }
}
