<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add trigger_event (and any other missing columns) so automation_workflows
     * matches the schema expected by AutomationWorkflow model and API.
     */
    public function up(): void
    {
        Schema::table('automation_workflows', function (Blueprint $table) {
            if (!Schema::hasColumn('automation_workflows', 'trigger_event')) {
                $table->string('trigger_event')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('automation_workflows', function (Blueprint $table) {
            if (Schema::hasColumn('automation_workflows', 'trigger_event')) {
                $table->dropColumn('trigger_event');
            }
        });
    }
};
