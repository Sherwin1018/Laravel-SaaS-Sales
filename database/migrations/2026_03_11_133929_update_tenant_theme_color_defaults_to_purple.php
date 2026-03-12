<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY theme_primary_color VARCHAR(255) DEFAULT '#240E35'");
        DB::statement("ALTER TABLE tenants MODIFY theme_accent_color VARCHAR(255) DEFAULT '#6B4A7A'");
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_text VARCHAR(255) DEFAULT '#F8F4FB'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY theme_primary_color VARCHAR(255) DEFAULT '#2563EB'");
        DB::statement("ALTER TABLE tenants MODIFY theme_accent_color VARCHAR(255) DEFAULT '#0EA5E9'");
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_text VARCHAR(255) DEFAULT '#1E40AF'");
    }
};
