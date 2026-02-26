<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('type', 50)->default('sequence');
            $table->string('trigger_tag', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->nullable(); // active, draft, inactive
            $table->string('n8n_workflow_id', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_workflows');
    }
};
