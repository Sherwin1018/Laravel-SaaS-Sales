<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('funnel_steps')) {
            Schema::table('funnel_steps', function (Blueprint $table) {
                if (! Schema::hasColumn('funnel_steps', 'layout_json_tablet')) {
                    $table->json('layout_json_tablet')->nullable()->after('layout_json');
                }
                if (! Schema::hasColumn('funnel_steps', 'layout_json_mobile')) {
                    $table->json('layout_json_mobile')->nullable()->after('layout_json_tablet');
                }
            });
        }

        if (Schema::hasTable('funnel_template_steps')) {
            Schema::table('funnel_template_steps', function (Blueprint $table) {
                if (! Schema::hasColumn('funnel_template_steps', 'layout_json_tablet')) {
                    $table->json('layout_json_tablet')->nullable()->after('layout_json');
                }
                if (! Schema::hasColumn('funnel_template_steps', 'layout_json_mobile')) {
                    $table->json('layout_json_mobile')->nullable()->after('layout_json_tablet');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('funnel_steps')) {
            Schema::table('funnel_steps', function (Blueprint $table) {
                if (Schema::hasColumn('funnel_steps', 'layout_json_mobile')) {
                    $table->dropColumn('layout_json_mobile');
                }
                if (Schema::hasColumn('funnel_steps', 'layout_json_tablet')) {
                    $table->dropColumn('layout_json_tablet');
                }
            });
        }

        if (Schema::hasTable('funnel_template_steps')) {
            Schema::table('funnel_template_steps', function (Blueprint $table) {
                if (Schema::hasColumn('funnel_template_steps', 'layout_json_mobile')) {
                    $table->dropColumn('layout_json_mobile');
                }
                if (Schema::hasColumn('funnel_template_steps', 'layout_json_tablet')) {
                    $table->dropColumn('layout_json_tablet');
                }
            });
        }
    }
};
