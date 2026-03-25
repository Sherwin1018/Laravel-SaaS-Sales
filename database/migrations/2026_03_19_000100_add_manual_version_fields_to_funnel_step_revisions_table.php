<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funnel_step_revisions', function (Blueprint $table) {
            if (! Schema::hasColumn('funnel_step_revisions', 'version_type')) {
                $table->string('version_type', 20)->default('autosave')->after('background_color');
            }
            if (! Schema::hasColumn('funnel_step_revisions', 'label')) {
                $table->string('label', 120)->nullable()->after('version_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('funnel_step_revisions', function (Blueprint $table) {
            if (Schema::hasColumn('funnel_step_revisions', 'label')) {
                $table->dropColumn('label');
            }
            if (Schema::hasColumn('funnel_step_revisions', 'version_type')) {
                $table->dropColumn('version_type');
            }
        });
    }
};
