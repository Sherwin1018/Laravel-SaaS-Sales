<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signup_intents', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('company_name');
            $table->string('email')->unique();
            $table->text('password_encrypted');
            $table->string('plan_code', 50);
            $table->string('plan_name', 120);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'completed'])->default('pending');
            $table->string('provider', 64)->nullable();
            $table->string('provider_reference', 191)->nullable()->unique();
            $table->string('payment_method', 64)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signup_intents');
    }
};
