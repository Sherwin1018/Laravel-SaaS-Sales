<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('activation_state', 64)->default('active')->after('status');
            $table->foreignId('invited_by')->nullable()->after('activation_state')->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->nullable()->after('invited_by');
            $table->timestamp('activation_completed_at')->nullable()->after('invited_at');
            $table->string('google_id', 191)->nullable()->after('activation_completed_at');
            $table->boolean('must_change_password')->default(false)->after('google_id');
            $table->boolean('is_customer_portal_user')->default(false)->after('must_change_password');
            $table->index(['activation_state', 'tenant_id']);
        });

        Schema::table('signup_intents', function (Blueprint $table) {
            $table->string('mobile', 32)->nullable()->after('email');
            $table->string('lifecycle_state', 64)->default('signup_intent_created')->after('status');
            $table->timestamp('email_sent_at')->nullable()->after('paid_at');
            $table->timestamp('activated_at')->nullable()->after('completed_at');
            $table->index(['lifecycle_state', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('signup_intents', function (Blueprint $table) {
            $table->dropIndex(['lifecycle_state', 'created_at']);
            $table->dropColumn([
                'mobile',
                'lifecycle_state',
                'email_sent_at',
                'activated_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['activation_state', 'tenant_id']);
            $table->dropConstrainedForeignId('invited_by');
            $table->dropColumn([
                'activation_state',
                'invited_at',
                'activation_completed_at',
                'google_id',
                'must_change_password',
                'is_customer_portal_user',
            ]);
        });
    }
};
