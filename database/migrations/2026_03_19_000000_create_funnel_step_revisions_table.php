<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_step_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_step_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('layout_json');
            $table->string('background_color', 7)->nullable();
            $table->timestamps();

            $table->index(['funnel_step_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_step_revisions');
    }
};
