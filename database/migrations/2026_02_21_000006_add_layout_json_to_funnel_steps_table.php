<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('funnel_steps', 'layout_json')) {
                $table->json('layout_json')->nullable()->after('template_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funnel_steps', function (Blueprint $table) {
            if (Schema::hasColumn('funnel_steps', 'layout_json')) {
                $table->dropColumn('layout_json');
            }
        });
    }
};

