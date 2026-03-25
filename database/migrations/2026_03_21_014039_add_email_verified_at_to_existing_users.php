<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pre-verify all existing users so they are not blocked after enabling
     * email verification. New users created by Account Owner will receive
     * a verification email and must verify before accessing the platform.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot safely revert; leave email_verified_at as-is
    }
};
