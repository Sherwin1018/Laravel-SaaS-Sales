<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider', 32)->nullable()->after('status');
            $table->string('provider_reference', 64)->nullable()->after('provider');
            $table->string('payment_method', 64)->nullable()->after('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_reference', 'payment_method']);
        });
    }
};
