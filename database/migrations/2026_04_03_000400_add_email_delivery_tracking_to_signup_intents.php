<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signup_intents', function (Blueprint $table) {
            $table->string('email_delivery_status', 30)->nullable()->after('email_sent_at');
            $table->unsignedInteger('email_delivery_attempts')->default(0)->after('email_delivery_status');
            $table->timestamp('email_last_attempt_at')->nullable()->after('email_delivery_attempts');
            $table->string('email_last_error', 255)->nullable()->after('email_last_attempt_at');
            $table->index(['email_delivery_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('signup_intents', function (Blueprint $table) {
            $table->dropIndex(['email_delivery_status', 'created_at']);
            $table->dropColumn([
                'email_delivery_status',
                'email_delivery_attempts',
                'email_last_attempt_at',
                'email_last_error',
            ]);
        });
    }
};

