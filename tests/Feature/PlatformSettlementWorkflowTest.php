<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\PlatformPayout;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlatformSettlementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payout_admin_can_record_settlement_and_dispatch_payout_event(): void
    {
        Http::fake(['https://n8n.test/*' => Http::response(['ok' => true], 200)]);
        config(['services.n8n.webhook_url' => 'https://n8n.test/webhook']);

        $tenant = Tenant::query()->create([
            'company_name' => 'Tenant Settlement',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'owner@tenant.test',
            'status' => 'active',
        ]);
        $owner->roles()->attach($this->role('account-owner'));
        $owner->load('roles');

        TenantPayoutAccount::query()->create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'gcash',
            'account_name' => 'Tenant Owner',
            'destination_value' => '09171234567',
            'masked_destination' => '*******4567',
            'is_verified' => true,
            'verified_at' => now(),
            'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
            'is_default' => true,
        ]);

        $payment = Payment::query()->create([
            'tenant_id' => $tenant->id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'amount' => 1500,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
            'provider' => 'paymongo',
        ]);

        $payoutAdmin = User::factory()->create([
            'email' => 'platform.finance.admin@gmail.com',
            'status' => 'active',
        ]);
        $payoutAdmin->roles()->attach($this->role('payout-admin'));
        $payoutAdmin->load('roles');

        $response = $this->actingAs($payoutAdmin)->post(route('platform.settlements.store', $tenant), [
            'payment_reference' => 'GCASH-REF-1001',
        ]);

        $response->assertRedirect(route('platform.settlements.index'));
        $response->assertSessionHas('success', 'Payout recorded successfully.');

        $payment->refresh();
        $this->assertNotNull($payment->platform_payout_id);

        $payout = PlatformPayout::query()->firstOrFail();
        $this->assertSame('09171234567', $payout->destination_value_snapshot);
        $this->assertSame('Tenant Owner', $payout->account_name_snapshot);
        $this->assertSame('GCASH-REF-1001', $payout->payment_reference);

        Http::assertSent(function ($request) use ($tenant, $payout) {
            $data = $request->data();

            return $request->url() === 'https://n8n.test/webhook'
                && ($data['event_name'] ?? null) === 'settlement_payout_recorded'
                && (int) ($data['tenant_id'] ?? 0) === $tenant->id
                && (int) ($data['platform_payout_id'] ?? 0) === $payout->id
                && ($data['masked_destination'] ?? null) === '*******4567';
        });
    }

    public function test_settlements_index_shows_full_destination_to_payout_admin(): void
    {
        $tenant = Tenant::query()->create([
            'company_name' => 'Tenant Destination',
            'status' => 'active',
            'billing_status' => 'current',
        ]);

        TenantPayoutAccount::query()->create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'gcash',
            'account_name' => 'Tenant Owner',
            'destination_value' => '09179876543',
            'masked_destination' => '*******6543',
            'is_verified' => true,
            'verified_at' => now(),
            'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
            'is_default' => true,
        ]);

        Payment::query()->create([
            'tenant_id' => $tenant->id,
            'payment_type' => Payment::TYPE_FUNNEL_CHECKOUT,
            'amount' => 999,
            'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);

        $payoutAdmin = User::factory()->create(['status' => 'active']);
        $payoutAdmin->roles()->attach($this->role('payout-admin'));
        $payoutAdmin->load('roles');

        $response = $this->actingAs($payoutAdmin)->get(route('platform.settlements.index'));

        $response->assertOk();
        $response->assertSeeText('09179876543');
        $response->assertSeeText('Masked: *******6543');
    }

    private function role(string $slug): Role
    {
        return Role::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => ucwords(str_replace('-', ' ', $slug))]
        );
    }
}
