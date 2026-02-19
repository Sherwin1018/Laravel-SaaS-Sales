<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'theme_primary_color')) {
                $table->string('theme_primary_color')->default('#2563EB')->after('logo_path');
            }
            if (!Schema::hasColumn('tenants', 'theme_accent_color')) {
                $table->string('theme_accent_color')->default('#0EA5E9')->after('theme_primary_color');
            }
            if (!Schema::hasColumn('tenants', 'theme_sidebar_bg')) {
                $table->string('theme_sidebar_bg')->default('#FFFFFF')->after('theme_accent_color');
            }
            if (!Schema::hasColumn('tenants', 'theme_sidebar_text')) {
                $table->string('theme_sidebar_text')->default('#1E40AF')->after('theme_sidebar_bg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'theme_primary_color')) {
                $table->dropColumn('theme_primary_color');
            }
            if (Schema::hasColumn('tenants', 'theme_accent_color')) {
                $table->dropColumn('theme_accent_color');
            }
            if (Schema::hasColumn('tenants', 'theme_sidebar_bg')) {
                $table->dropColumn('theme_sidebar_bg');
            }
            if (Schema::hasColumn('tenants', 'theme_sidebar_text')) {
                $table->dropColumn('theme_sidebar_text');
            }
        });
    }
};

