<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add step_order, type, config to automation_sequence_steps if missing.
     */
    public function up(): void
    {
        Schema::table('automation_sequence_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('automation_sequence_steps', 'step_order')) {
                $table->unsignedInteger('step_order')->default(1)->after('sequence_id');
            }
            if (!Schema::hasColumn('automation_sequence_steps', 'type')) {
                $table->string('type', 50)->default('email')->after('step_order');
            }
            if (!Schema::hasColumn('automation_sequence_steps', 'config')) {
                $table->json('config')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('automation_sequence_steps', function (Blueprint $table) {
            if (Schema::hasColumn('automation_sequence_steps', 'config')) {
                $table->dropColumn('config');
            }
            if (Schema::hasColumn('automation_sequence_steps', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('automation_sequence_steps', 'step_order')) {
                $table->dropColumn('step_order');
            }
        });
    }
};
