<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_template_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_template_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('subtitle', 160)->nullable();
            $table->string('slug', 120);
            $table->enum('type', ['landing', 'opt_in', 'sales', 'checkout', 'upsell', 'downsell', 'thank_you', 'custom']);
            $table->text('content')->nullable();
            $table->string('cta_label', 120)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('hero_image_url')->nullable();
            $table->string('layout_style', 40)->nullable();
            $table->string('template', 60)->default('simple');
            $table->json('template_data')->nullable();
            $table->json('step_tags')->nullable();
            $table->string('background_color', 7)->nullable();
            $table->string('button_color', 7)->nullable();
            $table->json('layout_json')->nullable();
            $table->timestamps();

            $table->unique(['funnel_template_id', 'slug'], 'fts_template_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_template_steps');
    }
};
