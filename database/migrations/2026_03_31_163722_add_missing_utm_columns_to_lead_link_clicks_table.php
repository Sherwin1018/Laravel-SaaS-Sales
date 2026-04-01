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
        Schema::table('lead_link_clicks', function (Blueprint $table) {
            // Add missing UTM columns if they don't exist
            if (!Schema::hasColumn('lead_link_clicks', 'utm_term')) {
                $table->string('utm_term', 100)->nullable()->after('utm_campaign');
            }
            if (!Schema::hasColumn('lead_link_clicks', 'utm_content')) {
                $table->string('utm_content', 100)->nullable()->after('utm_term');
            }
            if (!Schema::hasColumn('lead_link_clicks', 'utm_id')) {
                $table->string('utm_id', 100)->nullable()->after('utm_content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_link_clicks', function (Blueprint $table) {
            $table->dropColumn(['utm_term', 'utm_content', 'utm_id']);
        });
    }
};
