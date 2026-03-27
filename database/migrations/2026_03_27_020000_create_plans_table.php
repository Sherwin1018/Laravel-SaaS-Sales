<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 120);
            $table->decimal('price', 10, 2);
            $table->string('period', 60);
            $table->text('summary');
            $table->json('features');
            $table->string('spotlight', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('plans')->insert([
            [
                'code' => 'starter',
                'name' => 'Starter',
                'price' => 1499.00,
                'period' => 'per month',
                'summary' => 'For teams launching their first lead capture and conversion funnels.',
                'features' => json_encode([
                    '1 workspace with Account Owner dashboard access',
                    'Lead capture funnels and conversion tracking',
                    'Basic funnel analytics and payment monitoring',
                    'Email and landing-page-ready funnel journeys',
                ], JSON_THROW_ON_ERROR),
                'spotlight' => null,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'growth',
                'name' => 'Growth',
                'price' => 3499.00,
                'period' => 'per month',
                'summary' => 'For growing businesses managing campaigns, leads, and sales handoff in one place.',
                'features' => json_encode([
                    'Unlimited active funnels for one brand workspace',
                    'Marketing, sales, and finance collaboration tools',
                    'Role-based dashboards and pipeline visibility',
                    'PayMongo-ready checkout journeys for your offers',
                ], JSON_THROW_ON_ERROR),
                'spotlight' => 'Most Popular',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'scale',
                'name' => 'Scale',
                'price' => 6999.00,
                'period' => 'per month',
                'summary' => 'For teams that want advanced funnel execution with higher-volume operations.',
                'features' => json_encode([
                    'Everything in Growth plus enterprise-ready onboarding',
                    'Priority support for launch and billing workflows',
                    'Multi-team operational visibility for leaders',
                    'Built for aggressive campaign and revenue targets',
                ], JSON_THROW_ON_ERROR),
                'spotlight' => 'Best For Teams',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
