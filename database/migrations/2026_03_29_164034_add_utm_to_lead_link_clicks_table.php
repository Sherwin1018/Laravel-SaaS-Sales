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
            $table->string('utm_source', 100)->nullable()->after('destination_url');
            $table->string('utm_medium', 100)->nullable()->after('utm_source');
            $table->string('utm_campaign', 100)->nullable()->after('utm_medium');
            $table->string('utm_term', 100)->nullable()->after('utm_campaign');
            $table->string('utm_content', 100)->nullable()->after('utm_term');
            $table->string('utm_id', 100)->nullable()->after('utm_content');
            
            // Add index for performance
            $table->index(['tenant_id', 'utm_source', 'clicked_at']);
            $table->index(['tenant_id', 'utm_campaign', 'clicked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_link_clicks', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'utm_source', 'clicked_at']);
            $table->dropIndex(['tenant_id', 'utm_campaign', 'clicked_at']);
            $table->dropColumn(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id']);
        });
    }
};
