<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('funnel_steps', 'template')) {
                $table->string('template', 30)->default('simple')->after('layout_style');
            }
            if (!Schema::hasColumn('funnel_steps', 'template_data')) {
                $table->json('template_data')->nullable()->after('template');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funnel_steps', function (Blueprint $table) {
            if (Schema::hasColumn('funnel_steps', 'template_data')) {
                $table->dropColumn('template_data');
            }
            if (Schema::hasColumn('funnel_steps', 'template')) {
                $table->dropColumn('template');
            }
        });
    }
};

