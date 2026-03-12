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
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_bg VARCHAR(255) DEFAULT '#4169E1'");
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_text VARCHAR(255) DEFAULT '#F8FAFF'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_bg VARCHAR(255) DEFAULT '#FFFFFF'");
        DB::statement("ALTER TABLE tenants MODIFY theme_sidebar_text VARCHAR(255) DEFAULT '#1E40AF'");
    }
};
