<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutAccountUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_payout_submissions_reuse_a_single_tenant_record(): void
    {
        [$tenant, $owner] = $this->createTenantOwner();

        $payload = [
            'destination_type' => 'gcash',
            'account_name' => 'John Dave',
            'destination_value' => '09171234999',
            'provider_destination_reference' => '',
            'notes' => 'Primary payout',
        ];

        $this->actingAs($owner)
            ->put(route('profile.payout.update'), $payload)
            ->assertRedirect(route('profile.show'));

        $this->actingAs($owner)
            ->put(route('profile.payout.update'), $payload)
            ->assertRedirect(route('profile.show'));

        $this->assertDatabaseCount('tenant_payout_accounts', 1);

        $account = TenantPayoutAccount::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->assertSame('gcash', $account->destination_type);
        $this->assertTrue($account->is_default);
        $this->assertSame('Primary payout', data_get($account->meta, 'notes'));
    }

    public function test_switching_payout_type_clears_stale_preview_metadata_and_updates_same_record(): void
    {
        [$tenant, $owner] = $this->createTenantOwner();

        $account = TenantPayoutAccount::query()->create([
            'tenant_id' => $tenant->id,
            'destination_type' => 'gcash',
            'account_name' => 'John Dave',
            'destination_value' => '09171234999',
            'masked_destination' => '*******4999',
            'provider_destination_reference' => null,
            'is_verified' => true,
            'verified_at' => now(),
            'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
            'is_default' => true,
            'meta' => [
                'notes' => 'Original payout',
                'gcash' => [
                    'account_name' => 'John Dave',
                    'masked_destination' => '*******4999',
                    'reference' => null,
                ],
            ],
        ]);

        $this->actingAs($owner)
            ->put(route('profile.payout.update'), [
                'destination_type' => 'bank_transfer',
                'account_name' => 'John Dave Bank',
                'destination_value' => '1234567890',
                'provider_destination_reference' => 'bank-ref-123',
                'notes' => 'Bank payout',
            ])
            ->assertRedirect(route('profile.show'));

        $account->refresh();

        $this->assertDatabaseCount('tenant_payout_accounts', 1);
        $this->assertSame('bank_transfer', $account->destination_type);
        $this->assertSame('John Dave Bank', $account->account_name);
        $this->assertSame($account->id, TenantPayoutAccount::query()->where('tenant_id', $tenant->id)->value('id'));
        $this->assertFalse($account->is_verified);
        $this->assertSame(TenantPayoutAccount::STATUS_PENDING_PLATFORM_REVIEW, $account->verification_status);
        $this->assertNull(data_get($account->meta, 'gcash'));
        $this->assertSame('John Dave Bank', data_get($account->meta, 'card.account_name'));
        $this->assertSame('Bank payout', data_get($account->meta, 'notes'));
    }

    private function createTenantOwner(): array
    {
        $tenant = Tenant::create([
            'company_name' => 'Payout Workspace',
            'status' => 'active',
        ]);

        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => 'password',
            'status' => 'active',
        ]);

        $role = Role::query()->firstOrCreate(
            ['slug' => 'account-owner'],
            ['name' => 'Account Owner']
        );

        $owner->roles()->attach($role);
        $owner->load('roles');

        return [$tenant, $owner];
    }
}
