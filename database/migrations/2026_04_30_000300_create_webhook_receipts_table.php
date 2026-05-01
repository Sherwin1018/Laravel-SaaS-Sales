<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 40);
            $table->string('event_id', 191);
            $table->string('event_type', 160)->nullable();
            $table->string('payload_hash', 64);
            $table->string('status', 40)->default('processing');
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('processed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id'], 'webhook_receipts_provider_event_unique');
            $table->index(['provider', 'event_type'], 'webhook_receipts_provider_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_receipts');
    }
};
