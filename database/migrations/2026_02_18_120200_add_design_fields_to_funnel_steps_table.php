<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('funnel_steps', 'subtitle')) {
                $table->string('subtitle', 160)->nullable()->after('title');
            }
            if (!Schema::hasColumn('funnel_steps', 'hero_image_url')) {
                $table->string('hero_image_url', 2048)->nullable()->after('content');
            }
            if (!Schema::hasColumn('funnel_steps', 'layout_style')) {
                $table->string('layout_style', 30)->default('centered')->after('hero_image_url');
            }
            if (!Schema::hasColumn('funnel_steps', 'background_color')) {
                $table->string('background_color', 7)->nullable()->after('layout_style'); // #RRGGBB
            }
            if (!Schema::hasColumn('funnel_steps', 'button_color')) {
                $table->string('button_color', 7)->nullable()->after('background_color'); // #RRGGBB
            }
        });
    }

    public function down(): void
    {
        Schema::table('funnel_steps', function (Blueprint $table) {
            foreach (['subtitle', 'hero_image_url', 'layout_style', 'background_color', 'button_color'] as $col) {
                if (Schema::hasColumn('funnel_steps', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

