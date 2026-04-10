<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $plans = [
            'free-trial' => [
                'summary' => 'Account Owner dashboard access during trial period with limited team, leads, and funnel usage.',
                'features' => [
                    'Account Owner dashboard access during trial period',
                    'Limited team, leads, and funnel usage',
                    'Upgrade to Starter, Growth, or Scale anytime',
                    'No onboarding email dispatch required for trial signup',
                ],
                'spotlight' => null,
                'sort_order' => 0,
                'max_users' => 3,
                'max_leads' => 300,
                'max_funnels' => 1,
                'max_workflows' => 1,
                'max_monthly_messages' => 500,
            ],
            'starter' => [
                'summary' => 'For teams launching their first lead capture and conversion funnels.',
                'features' => [
                    '1 workspace with Account Owner dashboard access',
                    'Lead capture funnels and conversion tracking',
                    'Basic funnel analytics and payment monitoring',
                    'Email and landing-page-ready funnel journeys',
                ],
                'spotlight' => 'Best for New Teams',
                'sort_order' => 1,
                'max_users' => 5,
                'max_leads' => 1000,
                'max_funnels' => 3,
                'max_workflows' => 1,
                'max_monthly_messages' => 2000,
            ],
            'growth' => [
                'summary' => 'For growing businesses managing campaigns, leads, and sales handoff in one place.',
                'features' => [
                    'Unlimited active funnels for one brand workspace',
                    'Marketing, sales, and finance collaboration tools',
                    'Role-based dashboards and pipeline visibility',
                    'PayMongo-ready checkout journeys for your offers',
                ],
                'spotlight' => 'Recommended',
                'sort_order' => 2,
                'max_users' => 20,
                'max_leads' => 10000,
                'max_funnels' => null,
                'max_workflows' => 10,
                'max_monthly_messages' => 30000,
            ],
            'scale' => [
                'summary' => 'For teams that want advanced funnel execution with higher-volume operations.',
                'features' => [
                    'Everything in Growth plus enterprise-ready onboarding',
                    'Priority support for launch and billing workflows',
                    'Multi-team operational visibility for leaders',
                    'Built for aggressive campaign and revenue targets',
                ],
                'spotlight' => 'Best For Teams',
                'sort_order' => 3,
                'max_users' => null,
                'max_leads' => null,
                'max_funnels' => null,
                'max_workflows' => null,
                'max_monthly_messages' => null,
            ],
        ];

        foreach ($plans as $code => $values) {
            DB::table('plans')
                ->where('code', $code)
                ->update([
                    'summary' => $values['summary'],
                    'features' => json_encode($values['features'], JSON_THROW_ON_ERROR),
                    'spotlight' => $values['spotlight'],
                    'sort_order' => $values['sort_order'],
                    'max_users' => $values['max_users'],
                    'max_leads' => $values['max_leads'],
                    'max_funnels' => $values['max_funnels'],
                    'max_workflows' => $values['max_workflows'],
                    'max_monthly_messages' => $values['max_monthly_messages'],
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // Intentionally no-op: this migration syncs editable marketing copy and limits.
    }
};
