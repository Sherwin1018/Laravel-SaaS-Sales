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
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_sidebar_bg SET DEFAULT '#4169E1'");
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_sidebar_text SET DEFAULT '#F8FAFF'");
            return;
        }

        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_bg VARCHAR(255) DEFAULT '#4169E1'");
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_text VARCHAR(255) DEFAULT '#F8FAFF'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_sidebar_bg SET DEFAULT '#FFFFFF'");
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_sidebar_text SET DEFAULT '#1E40AF'");
            return;
        }

        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_bg VARCHAR(255) DEFAULT '#FFFFFF'");
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_text VARCHAR(255) DEFAULT '#1E40AF'");
    }
};
