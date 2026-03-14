<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * automation_sequence_steps belongs to sequences (sequence_id), not workflows.
     * Make automation_workflow_id nullable so sequence steps can be inserted without it.
     */
    public function up(): void
    {
        if (Schema::hasColumn('automation_sequence_steps', 'automation_workflow_id')) {
            DB::statement('ALTER TABLE automation_sequence_steps MODIFY automation_workflow_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('automation_sequence_steps', 'automation_workflow_id')) {
            DB::statement('ALTER TABLE automation_sequence_steps MODIFY automation_workflow_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
