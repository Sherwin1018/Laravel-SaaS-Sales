<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add trigger_filters column so automation_workflows matches the schema
     * expected by AutomationWorkflow model and AutomationController.
     */
    public function up(): void
    {
        Schema::table('automation_workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('automation_workflows', 'trigger_filters')) {
                $table->json('trigger_filters')->nullable()->after('trigger_event');
            }
        });
    }

    public function down(): void
    {
        Schema::table('automation_workflows', function (Blueprint $table) {
            if (Schema::hasColumn('automation_workflows', 'trigger_filters')) {
                $table->dropColumn('trigger_filters');
            }
        });
    }
};
