<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop unused automation_triggers table (not used by current automation feature).
     */
    public function up(): void
    {
        Schema::dropIfExists('automation_triggers');
    }

    public function down(): void
    {
        // Table structure unknown; leave empty or recreate if you need to rollback.
    }
};
