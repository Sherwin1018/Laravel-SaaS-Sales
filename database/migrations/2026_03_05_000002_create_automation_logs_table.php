<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('workflow_id')->nullable()->constrained('automation_workflows')->nullOnDelete();
            $table->string('event');
            $table->string('status', 20); // success | failed
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('ran_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
    }
};
