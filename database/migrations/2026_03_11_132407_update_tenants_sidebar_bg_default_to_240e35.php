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
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_sidebar_bg SET DEFAULT '#240E35'");
            return;
        }

        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_bg VARCHAR(255) DEFAULT '#240E35'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tenants ALTER COLUMN theme_sidebar_bg SET DEFAULT '#4169E1'");
            return;
        }

        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_bg VARCHAR(255) DEFAULT '#4169E1'");
    }
};
