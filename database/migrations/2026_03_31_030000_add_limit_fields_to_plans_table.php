<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_users')->nullable()->after('sort_order');
            $table->unsignedInteger('max_leads')->nullable()->after('max_users');
            $table->unsignedInteger('max_funnels')->nullable()->after('max_leads');
            $table->unsignedInteger('max_workflows')->nullable()->after('max_funnels');
            $table->unsignedInteger('max_monthly_messages')->nullable()->after('max_workflows');
            $table->boolean('automation_enabled')->default(true)->after('max_monthly_messages');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'max_users',
                'max_leads',
                'max_funnels',
                'max_workflows',
                'max_monthly_messages',
                'automation_enabled',
            ]);
        });
    }
};
