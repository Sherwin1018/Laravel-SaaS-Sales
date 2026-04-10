<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 120);
            $table->string('status', 40)->default('info');
            $table->string('message', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('signup_intent_id')->nullable()->constrained()->nullOnDelete();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['event_type', 'occurred_at']);
            $table->index(['status', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_audit_logs');
    }
};

