<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_template_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk', 30)->default('public');
            $table->string('path');
            $table->string('kind', 20)->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->unique(['disk', 'path'], 'fta_disk_path_unique');
            $table->index(['funnel_template_id', 'created_at'], 'fta_template_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_template_assets');
    }
};
