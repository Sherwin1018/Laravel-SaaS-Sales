<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->enum('type', ['landing', 'opt_in', 'sales', 'checkout', 'upsell', 'downsell', 'thank_you']);
            $table->text('content')->nullable();
            $table->string('cta_label')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['funnel_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_steps');
    }
};
