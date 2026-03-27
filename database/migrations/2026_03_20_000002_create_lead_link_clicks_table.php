<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_link_clicks')) {
            return;
        }

        Schema::create('lead_link_clicks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');

            // Context about where the link came from
            $table->unsignedBigInteger('workflow_id')->nullable()->index();
            $table->unsignedBigInteger('sequence_id')->nullable()->index();
            $table->unsignedInteger('sequence_step_order')->nullable()->index();

            // Link details
            $table->string('link_name', 200)->nullable();
            $table->text('destination_url');

            // Counting
            $table->unsignedInteger('click_number')->default(1);
            $table->timestamp('clicked_at')->useCurrent();

            $table->timestamps();

            $table->index(['tenant_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_link_clicks');
    }
};

