<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add sequence_id to automation_sequence_steps if missing (e.g. table existed with different schema).
     */
    public function up(): void
    {
        Schema::table('automation_sequence_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('automation_sequence_steps', 'sequence_id')) {
                $table->unsignedBigInteger('sequence_id')->nullable()->after('id');
                $table->foreign('sequence_id')->references('id')->on('automation_sequences')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('automation_sequence_steps', function (Blueprint $table) {
            if (Schema::hasColumn('automation_sequence_steps', 'sequence_id')) {
                $table->dropForeign(['sequence_id']);
                $table->dropColumn('sequence_id');
            }
        });
    }
};
