<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add tenant preference for "From" email used in automation (sequences, workflows).
     * n8n can use this when sending emails so each tenant can set their preferred sender.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('automation_from_email', 255)->nullable()->after('theme_sidebar_text');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('automation_from_email');
        });
    }
};
