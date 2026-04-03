<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('funnel_template_step_revisions')) {
            Schema::create('funnel_template_step_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('funnel_template_step_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->json('layout_json')->nullable();
                $table->string('background_color', 7)->nullable();
                $table->string('version_type', 30)->default('autosave');
                $table->string('label', 120)->nullable();
                $table->timestamps();
            });
        }

        if (! $this->indexExists('funnel_template_step_revisions', 'ftsr_step_created_idx')) {
            Schema::table('funnel_template_step_revisions', function (Blueprint $table) {
                $table->index(['funnel_template_step_id', 'created_at'], 'ftsr_step_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_template_step_revisions');
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$indexName]);

        return $result !== [];
    }
};
