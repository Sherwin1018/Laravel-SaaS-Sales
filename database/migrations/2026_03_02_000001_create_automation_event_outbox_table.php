<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stores outbound automation events for idempotency: each event_id is unique.
     * Job marks sent_at when webhook is successfully sent so we can audit and avoid duplicate sends.
     */
    public function up(): void
    {
        Schema::create('automation_event_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 64)->unique();
            $table->string('event', 64)->index();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->json('payload');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_event_outbox');
    }
};
