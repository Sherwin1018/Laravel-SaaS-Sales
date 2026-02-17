<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $superAdminRole = Role::where('slug', 'super-admin')->first();

       
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'], 
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'tenant_id' => null, // global user
                'role' => 'super_admin',
            ]
        );

        // 3. Attach the role (pivot table)
        // Check if not already attached to avoid duplication
        if (!$superAdmin->roles()->where('role_id', $superAdminRole->id)->exists()) {
            $superAdmin->roles()->attach($superAdminRole);
        }

        // Demo tenant and tenant users (Account Owner, Marketing Manager) for testing
        $tenant = Tenant::firstOrCreate(
            ['company_name' => 'Demo Company'],
            ['subscription_plan' => 'starter', 'status' => 'active']
        );
        $accountOwnerRole = Role::where('slug', 'account-owner')->first();
        $accountOwner = User::updateOrCreate(
            ['email' => 'accountowner@gmail.com'],
            [
                'name' => 'Account Owner',
                'password' => Hash::make('password123'),
                'tenant_id' => $tenant->id,
                'role' => 'account-owner',
            ]
        );
        if (!$accountOwner->roles()->where('role_id', $accountOwnerRole->id)->exists()) {
            $accountOwner->roles()->attach($accountOwnerRole);
        }
        $marketingManagerRole = Role::where('slug', 'marketing-manager')->first();
        $marketingManager = User::updateOrCreate(
            ['email' => 'marketingmanager@gmail.com'],
            [
                'name' => 'Marketing Manager',
                'password' => Hash::make('password123'),
                'tenant_id' => $tenant->id,
                'role' => 'marketing-manager',
            ]
        );
        if (!$marketingManager->roles()->where('role_id', $marketingManagerRole->id)->exists()) {
            $marketingManager->roles()->attach($marketingManagerRole);
        }
    }
}
