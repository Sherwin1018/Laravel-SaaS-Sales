<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('billing_status', 30)->default('current')->after('status');
            $table->timestamp('billing_grace_ends_at')->nullable()->after('billing_status');
            $table->timestamp('last_payment_failed_at')->nullable()->after('billing_grace_ends_at');
            $table->timestamp('subscription_activated_at')->nullable()->after('last_payment_failed_at');
        });

        DB::table('tenants')->where('status', 'trial')->update(['billing_status' => 'trial']);
        DB::table('tenants')->where('status', 'inactive')->update(['billing_status' => 'inactive']);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'billing_status',
                'billing_grace_ends_at',
                'last_payment_failed_at',
                'subscription_activated_at',
            ]);
        });
    }
};
