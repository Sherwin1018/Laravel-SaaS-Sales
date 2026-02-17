<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_id')->constrained()->onDelete('cascade');
            $table->string('type'); // landing, opt-in, sales, checkout
            $table->string('title');
            $table->string('slug');
            $table->unique(['funnel_id', 'slug']);
            $table->longText('content')->nullable();
            $table->json('form_fields')->nullable(); // for opt-in: name, email, etc.
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_pages');
    }
};
