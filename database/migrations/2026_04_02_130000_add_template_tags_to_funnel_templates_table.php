<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('funnel_templates', 'template_tags')) {
                $table->json('template_tags')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funnel_templates', function (Blueprint $table) {
            if (Schema::hasColumn('funnel_templates', 'template_tags')) {
                $table->dropColumn('template_tags');
            }
        });
    }
};
