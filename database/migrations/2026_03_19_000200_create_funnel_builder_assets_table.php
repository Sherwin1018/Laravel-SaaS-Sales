<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_builder_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('funnel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disk', 40)->default('public');
            $table->string('path')->unique();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('kind', 20)->index();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'funnel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_builder_assets');
    }
};
