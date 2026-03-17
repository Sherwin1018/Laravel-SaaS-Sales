<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure automation_workflows has all columns required by AutomationWorkflow
     * model (action_type, action_config, created_by, trigger_filters, trigger_event).
     */
    public function up(): void
    {
        Schema::table('automation_workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('automation_workflows', 'trigger_event')) {
                $table->string('trigger_event')->nullable()->after('type');
            }
            if (!Schema::hasColumn('automation_workflows', 'trigger_filters')) {
                $table->json('trigger_filters')->nullable()->after('trigger_event');
            }
            if (!Schema::hasColumn('automation_workflows', 'action_type')) {
                $table->string('action_type')->nullable()->after('trigger_filters');
            }
            if (!Schema::hasColumn('automation_workflows', 'action_config')) {
                $table->json('action_config')->nullable()->after('action_type');
            }
            if (!Schema::hasColumn('automation_workflows', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('automation_workflows', function (Blueprint $table) {
            if (Schema::hasColumn('automation_workflows', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('automation_workflows', 'action_config')) {
                $table->dropColumn('action_config');
            }
            if (Schema::hasColumn('automation_workflows', 'action_type')) {
                $table->dropColumn('action_type');
            }
            if (Schema::hasColumn('automation_workflows', 'trigger_filters')) {
                $table->dropColumn('trigger_filters');
            }
            if (Schema::hasColumn('automation_workflows', 'trigger_event')) {
                $table->dropColumn('trigger_event');
            }
        });
    }
};
