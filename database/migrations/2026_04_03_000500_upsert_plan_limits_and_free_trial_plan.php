<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $plans = [
            [
                'code' => 'starter',
                'name' => 'Starter',
                'price' => 1499.00,
                'period' => 'per month',
                'summary' => 'For teams launching their first lead capture and conversion funnels.',
                'features' => [
                    '1 workspace with Account Owner dashboard access',
                    'Lead capture funnels and conversion tracking',
                    'Basic funnel analytics and payment monitoring',
                    'Email and landing-page-ready funnel journeys',
                ],
                'spotlight' => 'Best for New Teams',
                'is_active' => true,
                'sort_order' => 1,
                'max_users' => 5,
                'max_leads' => 1000,
                'max_funnels' => 3,
                'max_workflows' => 1,
                'max_monthly_messages' => 2000,
                'automation_enabled' => false,
            ],
            [
                'code' => 'growth',
                'name' => 'Growth',
                'price' => 3499.00,
                'period' => 'per month',
                'summary' => 'For growing businesses managing campaigns, leads, and sales handoff in one place.',
                'features' => [
                    'Unlimited active funnels for one brand workspace',
                    'Marketing, sales, and finance collaboration tools',
                    'Role-based dashboards and pipeline visibility',
                    'PayMongo-ready checkout journeys for your offers',
                ],
                'spotlight' => 'Recommended',
                'is_active' => true,
                'sort_order' => 2,
                'max_users' => 20,
                'max_leads' => 10000,
                'max_funnels' => null,
                'max_workflows' => 10,
                'max_monthly_messages' => 30000,
                'automation_enabled' => true,
            ],
            [
                'code' => 'scale',
                'name' => 'Scale',
                'price' => 6999.00,
                'period' => 'per month',
                'summary' => 'For teams that want advanced funnel execution with higher-volume operations.',
                'features' => [
                    'Everything in Growth plus enterprise-ready onboarding',
                    'Priority support for launch and billing workflows',
                    'Multi-team operational visibility for leaders',
                    'Built for aggressive campaign and revenue targets',
                ],
                'spotlight' => 'Best For Teams',
                'is_active' => true,
                'sort_order' => 3,
                'max_users' => null,
                'max_leads' => null,
                'max_funnels' => null,
                'max_workflows' => null,
                'max_monthly_messages' => null,
                'automation_enabled' => true,
            ],
            [
                'code' => 'free-trial',
                'name' => 'Free Trial',
                'price' => 0.00,
                'period' => '7 days',
                'summary' => 'Short trial plan for evaluating the workspace before paid upgrade.',
                'features' => [
                    'Account Owner dashboard access during trial period',
                    'Limited team, leads, and funnel usage',
                    'Upgrade to Starter, Growth, or Scale anytime',
                    'No onboarding email dispatch required for trial signup',
                ],
                'spotlight' => null,
                'is_active' => true,
                'sort_order' => 0,
                'max_users' => 3,
                'max_leads' => 300,
                'max_funnels' => 1,
                'max_workflows' => 1,
                'max_monthly_messages' => 500,
                'automation_enabled' => false,
            ],
        ];

        foreach ($plans as $plan) {
            $exists = DB::table('plans')->where('code', $plan['code'])->exists();

            $payload = [
                'name' => $plan['name'],
                'price' => $plan['price'],
                'period' => $plan['period'],
                'summary' => $plan['summary'],
                'features' => json_encode($plan['features'], JSON_THROW_ON_ERROR),
                'spotlight' => $plan['spotlight'],
                'is_active' => $plan['is_active'],
                'sort_order' => $plan['sort_order'],
                'max_users' => $plan['max_users'],
                'max_leads' => $plan['max_leads'],
                'max_funnels' => $plan['max_funnels'],
                'max_workflows' => $plan['max_workflows'],
                'max_monthly_messages' => $plan['max_monthly_messages'],
                'automation_enabled' => $plan['automation_enabled'],
                'updated_at' => $now,
            ];

            if ($exists) {
                DB::table('plans')->where('code', $plan['code'])->update($payload);
                continue;
            }

            DB::table('plans')->insert(array_merge(
                ['code' => $plan['code'], 'created_at' => $now],
                $payload
            ));
        }
    }

    public function down(): void
    {
        DB::table('plans')->where('code', 'free-trial')->delete();

        DB::table('plans')->where('code', 'starter')->update([
            'spotlight' => null,
            'max_users' => null,
            'max_leads' => null,
            'max_funnels' => null,
            'max_workflows' => null,
            'max_monthly_messages' => null,
            'automation_enabled' => true,
            'updated_at' => now(),
        ]);

        DB::table('plans')->where('code', 'growth')->update([
            'spotlight' => 'Most Popular',
            'max_users' => null,
            'max_leads' => null,
            'max_funnels' => null,
            'max_workflows' => null,
            'max_monthly_messages' => null,
            'automation_enabled' => true,
            'updated_at' => now(),
        ]);

        DB::table('plans')->where('code', 'scale')->update([
            'spotlight' => 'Best For Teams',
            'max_users' => null,
            'max_leads' => null,
            'max_funnels' => null,
            'max_workflows' => null,
            'max_monthly_messages' => null,
            'automation_enabled' => true,
            'updated_at' => now(),
        ]);
    }
};
