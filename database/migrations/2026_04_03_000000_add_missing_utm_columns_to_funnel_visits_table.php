<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_visits', function (Blueprint $table) {
            // Add missing UTM columns if they don't exist
            if (!Schema::hasColumn('funnel_visits', 'utm_term')) {
                $table->string('utm_term', 100)->nullable()->after('utm_campaign');
            }
            if (!Schema::hasColumn('funnel_visits', 'utm_content')) {
                $table->string('utm_content', 100)->nullable()->after('utm_term');
            }
            if (!Schema::hasColumn('funnel_visits', 'utm_id')) {
                $table->string('utm_id', 100)->nullable()->after('utm_content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funnel_visits', function (Blueprint $table) {
            $table->dropColumn(['utm_term', 'utm_content', 'utm_id']);
        });
    }
};
