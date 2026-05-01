<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel', 40);
            $table->string('event_name', 160)->nullable();
            $table->string('recipient', 255)->nullable();
            $table->string('provider', 80)->nullable();
            $table->string('status', 40)->default('failed');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('error_message')->nullable();
            $table->string('idempotency_key', 191)->nullable();
            $table->boolean('is_billable')->default(false);
            $table->json('meta')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'channel', 'sent_at'], 'delivery_logs_tenant_channel_sent_idx');
            $table->index(['tenant_id', 'is_billable', 'sent_at'], 'delivery_logs_tenant_billable_sent_idx');
            $table->index(['channel', 'status', 'created_at'], 'delivery_logs_channel_status_created_idx');
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_delivery_logs');
    }
};
