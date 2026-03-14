<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make channel nullable on automation_sequence_steps so sequence steps
     * can be inserted without it (we only use type/config for email/delay).
     */
    public function up(): void
    {
        if (Schema::hasColumn('automation_sequence_steps', 'channel')) {
            DB::statement('ALTER TABLE automation_sequence_steps MODIFY channel VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('automation_sequence_steps', 'channel')) {
            DB::statement('ALTER TABLE automation_sequence_steps MODIFY channel VARCHAR(255) NOT NULL');
        }
    }
};
