<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 40)->nullable()->after('is_customer_portal_user');
                $table->unique('referral_code', 'users_referral_code_unique');
            }
        });

        DB::table('users')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                $existing = DB::table('users')->where('id', $user->id)->value('referral_code');
                if (is_string($existing) && trim($existing) !== '') {
                    return;
                }

                $base = Str::upper(Str::slug((string) ($user->name ?? 'user'), ''));
                $base = $base !== '' ? Str::substr($base, 0, 10) : 'USER';
                $code = $base . '-' . strtoupper(Str::random(6));

                while (DB::table('users')->where('referral_code', $code)->exists()) {
                    $code = $base . '-' . strtoupper(Str::random(6));
                }

                DB::table('users')->where('id', $user->id)->update([
                    'referral_code' => $code,
                ]);
            });

        Schema::table('funnels', function (Blueprint $table) {
            if (! Schema::hasColumn('funnels', 'source_template_id')) {
                $table->foreignId('source_template_id')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('funnel_templates')
                    ->nullOnDelete();
                $table->index(['tenant_id', 'source_template_id'], 'funnels_tenant_source_template_idx');
            }
        });

        Schema::table('funnel_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('funnel_templates', 'royalty_rate')) {
                $table->decimal('royalty_rate', 5, 2)->nullable()->after('published_at');
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            if (! Schema::hasColumn('leads', 'source_platform')) {
                $table->string('source_platform', 60)->nullable()->after('source_campaign');
            }
            if (! Schema::hasColumn('leads', 'source_medium')) {
                $table->string('source_medium', 60)->nullable()->after('source_platform');
            }
            if (! Schema::hasColumn('leads', 'source_content')) {
                $table->string('source_content', 150)->nullable()->after('source_medium');
            }
            if (! Schema::hasColumn('leads', 'referrer_user_id')) {
                $table->foreignId('referrer_user_id')->nullable()->after('source_content')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('leads', 'referral_code_snapshot')) {
                $table->string('referral_code_snapshot', 40)->nullable()->after('referrer_user_id');
            }
            $table->index(['tenant_id', 'source_campaign'], 'leads_tenant_source_campaign_idx');
            $table->index(['tenant_id', 'referrer_user_id'], 'leads_tenant_referrer_idx');
        });

        Schema::table('signup_intents', function (Blueprint $table) {
            if (! Schema::hasColumn('signup_intents', 'source_platform')) {
                $table->string('source_platform', 60)->nullable()->after('plan_name');
            }
            if (! Schema::hasColumn('signup_intents', 'source_medium')) {
                $table->string('source_medium', 60)->nullable()->after('source_platform');
            }
            if (! Schema::hasColumn('signup_intents', 'source_campaign')) {
                $table->string('source_campaign', 120)->nullable()->after('source_medium');
            }
            if (! Schema::hasColumn('signup_intents', 'source_content')) {
                $table->string('source_content', 150)->nullable()->after('source_campaign');
            }
            if (! Schema::hasColumn('signup_intents', 'referrer_user_id')) {
                $table->foreignId('referrer_user_id')->nullable()->after('source_content')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('signup_intents', 'referral_code_snapshot')) {
                $table->string('referral_code_snapshot', 40)->nullable()->after('referrer_user_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'source_funnel_template_id')) {
                $table->foreignId('source_funnel_template_id')->nullable()->after('funnel_id')->constrained('funnel_templates')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'source_platform')) {
                $table->string('source_platform', 60)->nullable()->after('coupon_code');
            }
            if (! Schema::hasColumn('payments', 'source_medium')) {
                $table->string('source_medium', 60)->nullable()->after('source_platform');
            }
            if (! Schema::hasColumn('payments', 'source_campaign')) {
                $table->string('source_campaign', 120)->nullable()->after('source_medium');
            }
            if (! Schema::hasColumn('payments', 'source_content')) {
                $table->string('source_content', 150)->nullable()->after('source_campaign');
            }
            if (! Schema::hasColumn('payments', 'referrer_user_id')) {
                $table->foreignId('referrer_user_id')->nullable()->after('source_content')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'referral_code_snapshot')) {
                $table->string('referral_code_snapshot', 40)->nullable()->after('referrer_user_id');
            }
            if (! Schema::hasColumn('payments', 'assigned_sales_user_id')) {
                $table->foreignId('assigned_sales_user_id')->nullable()->after('referral_code_snapshot')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('payments', 'non_commissionable_amount')) {
                $table->decimal('non_commissionable_amount', 10, 2)->default(0)->after('refund_amount');
            }
            if (! Schema::hasColumn('payments', 'commissionable_amount')) {
                $table->decimal('commissionable_amount', 10, 2)->default(0)->after('non_commissionable_amount');
            }
            if (! Schema::hasColumn('payments', 'gateway_fee_amount')) {
                $table->decimal('gateway_fee_amount', 10, 2)->default(0)->after('commissionable_amount');
            }
            if (! Schema::hasColumn('payments', 'platform_share_amount')) {
                $table->decimal('platform_share_amount', 10, 2)->default(0)->after('gateway_fee_amount');
            }
            if (! Schema::hasColumn('payments', 'template_royalty_amount')) {
                $table->decimal('template_royalty_amount', 10, 2)->default(0)->after('platform_share_amount');
            }
            if (! Schema::hasColumn('payments', 'affiliate_commission_amount')) {
                $table->decimal('affiliate_commission_amount', 10, 2)->default(0)->after('template_royalty_amount');
            }
            if (! Schema::hasColumn('payments', 'sales_commission_amount')) {
                $table->decimal('sales_commission_amount', 10, 2)->default(0)->after('affiliate_commission_amount');
            }
            if (! Schema::hasColumn('payments', 'marketing_commission_amount')) {
                $table->decimal('marketing_commission_amount', 10, 2)->default(0)->after('sales_commission_amount');
            }
            if (! Schema::hasColumn('payments', 'tenant_net_income_amount')) {
                $table->decimal('tenant_net_income_amount', 10, 2)->default(0)->after('marketing_commission_amount');
            }

            $table->index(['source_funnel_template_id', 'status', 'payment_date'], 'payments_template_status_date_idx');
            $table->index(['tenant_id', 'source_platform', 'payment_date'], 'payments_tenant_source_platform_idx');
            $table->index(['tenant_id', 'referrer_user_id', 'status'], 'payments_tenant_referrer_status_idx');
        });

        Schema::table('commission_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('commission_plans', 'affiliate_sale_rate')) {
                $table->decimal('affiliate_sale_rate', 5, 2)->default(5.00)->after('marketing_manager_rate');
            }
            if (! Schema::hasColumn('commission_plans', 'platform_referral_rate')) {
                $table->decimal('platform_referral_rate', 5, 2)->default(10.00)->after('affiliate_sale_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission_plans', function (Blueprint $table) {
            foreach (['affiliate_sale_rate', 'platform_referral_rate'] as $column) {
                if (Schema::hasColumn('commission_plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            foreach ([
                'payments_template_status_date_idx',
                'payments_tenant_source_platform_idx',
                'payments_tenant_referrer_status_idx',
            ] as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Throwable) {
                }
            }

            foreach ([
                'source_funnel_template_id',
                'referrer_user_id',
                'assigned_sales_user_id',
            ] as $foreign) {
                if (Schema::hasColumn('payments', $foreign)) {
                    try {
                        $table->dropConstrainedForeignId($foreign);
                    } catch (\Throwable) {
                    }
                }
            }

            foreach ([
                'source_platform',
                'source_medium',
                'source_campaign',
                'source_content',
                'referral_code_snapshot',
                'refund_amount',
                'non_commissionable_amount',
                'commissionable_amount',
                'gateway_fee_amount',
                'platform_share_amount',
                'template_royalty_amount',
                'affiliate_commission_amount',
                'sales_commission_amount',
                'marketing_commission_amount',
                'tenant_net_income_amount',
            ] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('signup_intents', function (Blueprint $table) {
            if (Schema::hasColumn('signup_intents', 'referrer_user_id')) {
                try {
                    $table->dropConstrainedForeignId('referrer_user_id');
                } catch (\Throwable) {
                }
            }

            foreach ([
                'source_platform',
                'source_medium',
                'source_campaign',
                'source_content',
                'referral_code_snapshot',
            ] as $column) {
                if (Schema::hasColumn('signup_intents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            foreach ([
                'leads_tenant_source_campaign_idx',
                'leads_tenant_referrer_idx',
            ] as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Throwable) {
                }
            }

            if (Schema::hasColumn('leads', 'referrer_user_id')) {
                try {
                    $table->dropConstrainedForeignId('referrer_user_id');
                } catch (\Throwable) {
                }
            }

            foreach ([
                'source_platform',
                'source_medium',
                'source_content',
                'referral_code_snapshot',
            ] as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('funnel_templates', function (Blueprint $table) {
            if (Schema::hasColumn('funnel_templates', 'royalty_rate')) {
                $table->dropColumn('royalty_rate');
            }
        });

        Schema::table('funnels', function (Blueprint $table) {
            if (Schema::hasColumn('funnels', 'source_template_id')) {
                try {
                    $table->dropIndex('funnels_tenant_source_template_idx');
                } catch (\Throwable) {
                }
                try {
                    $table->dropConstrainedForeignId('source_template_id');
                } catch (\Throwable) {
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referral_code')) {
                try {
                    $table->dropUnique('users_referral_code_unique');
                } catch (\Throwable) {
                }
                $table->dropColumn('referral_code');
            }
        });
    }
};
