<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('funnel_visits')) {
            return;
        }

        Schema::create('funnel_visits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_step_id')->nullable()->constrained('funnel_steps')->nullOnDelete();

            // UTM tracking fields (when present)
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();

            // Visitor referrer (when present). Kept relatively small.
            $table->string('referrer', 500)->nullable();

            // Primary timestamp for “how many times the link was clicked/visited”
            $table->timestamp('visited_at')->useCurrent();

            $table->timestamps();

            $table->index(['tenant_id', 'funnel_id', 'visited_at']);
            $table->index(['tenant_id', 'utm_source', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_visits');
    }
};

