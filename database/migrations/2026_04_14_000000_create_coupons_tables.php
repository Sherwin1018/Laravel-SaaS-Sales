<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('scope_type', 20)->default('tenant');
            $table->string('code', 40)->unique();
            $table->string('title', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->string('discount_type', 20)->default('fixed');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->string('usage_mode', 20)->default('single_use');
            $table->unsignedInteger('max_total_uses')->nullable();
            $table->unsignedInteger('max_uses_per_user')->nullable();
            $table->unsignedInteger('times_used')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['scope_type', 'status']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('coupon_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['coupon_id', 'tenant_id']);
        });

        Schema::create('coupon_funnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('funnel_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coupon_id', 'funnel_id']);
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('funnel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('funnel_step_id')->nullable()->constrained('funnel_steps')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_email', 150)->nullable();
            $table->string('coupon_code', 40);
            $table->decimal('order_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->index(['coupon_id', 'redeemed_at']);
            $table->index(['coupon_id', 'customer_email']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('lead_id')->constrained()->nullOnDelete();
            $table->string('coupon_code', 40)->nullable()->after('coupon_id');
            $table->decimal('subtotal_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal_amount');

            $table->index(['coupon_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['coupon_id', 'status']);
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['coupon_code', 'subtotal_amount', 'discount_amount']);
        });

        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupon_funnels');
        Schema::dropIfExists('coupon_assignments');
        Schema::dropIfExists('coupons');
    }
};
