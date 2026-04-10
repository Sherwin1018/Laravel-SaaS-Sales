<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setup_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 64);
            $table->string('token_hash', 255)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purpose']);
            $table->index(['expires_at', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_tokens');
    }
};
