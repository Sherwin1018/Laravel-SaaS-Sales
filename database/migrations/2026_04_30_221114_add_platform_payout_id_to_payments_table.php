<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table
                ->foreignId('platform_payout_id')
                ->nullable()
                ->after('session_identifier')
                ->constrained('platform_payouts')
                ->nullOnDelete();

            $table->index(['tenant_id', 'payment_type', 'status', 'platform_payout_id'], 'payments_payout_rollup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_payout_rollup_idx');
            $table->dropConstrainedForeignId('platform_payout_id');
        });
    }
};
