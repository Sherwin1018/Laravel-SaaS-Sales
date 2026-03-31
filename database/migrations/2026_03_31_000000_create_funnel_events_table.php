<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('funnel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('funnel_step_id')->nullable()->constrained('funnel_steps')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_name', 80);
            $table->string('session_identifier', 120)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['tenant_id', 'event_name', 'occurred_at']);
            $table->index(['funnel_id', 'event_name', 'occurred_at']);
            $table->index(['funnel_step_id', 'event_name', 'occurred_at']);
            $table->index(['session_identifier', 'event_name', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_events');
    }
};
