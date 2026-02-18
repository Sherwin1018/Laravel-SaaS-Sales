<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('source_funnel_id')->nullable()->after('tenant_id')->constrained('funnels')->nullOnDelete();
            $table->foreignId('source_funnel_page_id')->nullable()->after('source_funnel_id')->constrained('funnel_pages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['source_funnel_id']);
            $table->dropForeign(['source_funnel_page_id']);
        });
    }
};
