<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('funnel_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            $table->foreignId('funnel_step_id')->nullable()->after('funnel_id')->constrained('funnel_steps')->nullOnDelete();
            $table->string('session_identifier', 120)->nullable()->after('payment_method');

            $table->index(['funnel_id', 'status', 'payment_date']);
            $table->index(['session_identifier', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['funnel_id', 'status', 'payment_date']);
            $table->dropIndex(['session_identifier', 'status']);
            $table->dropConstrainedForeignId('funnel_id');
            $table->dropConstrainedForeignId('funnel_step_id');
            $table->dropColumn('session_identifier');
        });
    }
};
