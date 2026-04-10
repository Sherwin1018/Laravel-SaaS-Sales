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
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('score');
        });

        Schema::table('funnels', function (Blueprint $table) {
            $table->boolean('require_double_opt_in')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });

        Schema::table('funnels', function (Blueprint $table) {
            $table->dropColumn('require_double_opt_in');
        });
    }
};
