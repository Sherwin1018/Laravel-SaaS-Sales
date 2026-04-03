<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_type', 40)
                ->default('platform_subscription')
                ->after('tenant_id');

            $table->index(['payment_type', 'status', 'payment_date']);
        });

        DB::table('payments')
            ->whereNotNull('funnel_id')
            ->update(['payment_type' => 'funnel_checkout']);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payment_type', 'status', 'payment_date']);
            $table->dropColumn('payment_type');
        });
    }
};
