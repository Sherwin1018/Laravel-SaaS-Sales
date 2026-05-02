<?php

namespace Database\Seeders;

use App\Services\PlatformAdminProvisioningService;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PlatformAdminProvisioningService::class)->provisionConfiguredAccounts();
    }
}
