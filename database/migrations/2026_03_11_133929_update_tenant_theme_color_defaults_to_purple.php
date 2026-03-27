<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_primary_color SET DEFAULT '#240E35'");
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_accent_color SET DEFAULT '#6B4A7A'");
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_sidebar_text SET DEFAULT '#F8F4FB'");
            return;
        }

        DB::statement("ALTER TABLE tenants MODIFY theme_primary_color VARCHAR(255) DEFAULT '#240E35'");
        DB::statement("ALTER TABLE tenants MODIFY theme_accent_color VARCHAR(255) DEFAULT '#6B4A7A'");
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_text VARCHAR(255) DEFAULT '#F8F4FB'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_primary_color SET DEFAULT '#2563EB'");
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_accent_color SET DEFAULT '#0EA5E9'");
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_sidebar_text SET DEFAULT '#1E40AF'");
            return;
        }

        DB::statement("ALTER TABLE tenants MODIFY theme_primary_color VARCHAR(255) DEFAULT '#2563EB'");
        DB::statement("ALTER TABLE tenants MODIFY theme_accent_color VARCHAR(255) DEFAULT '#0EA5E9'");
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_text VARCHAR(255) DEFAULT '#1E40AF'");
    }
};
