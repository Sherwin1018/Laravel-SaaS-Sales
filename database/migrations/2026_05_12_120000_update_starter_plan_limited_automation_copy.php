<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')
            ->where('code', 'starter')
            ->update([
                'summary' => 'For teams launching their first lead capture and conversion funnels with essential built-in automations.',
                'features' => json_encode([
                    '1 workspace with Account Owner dashboard access',
                    'Welcome/setup email and payment confirmation included',
                    'One lead capture autoresponse and one abandoned checkout reminder',
                    'Basic funnel analytics, payment monitoring, and status notifications',
                    'Limited built-in automations without shared n8n workflow access',
                ], JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('plans')
            ->where('code', 'starter')
            ->update([
                'summary' => 'For teams launching their first lead capture and conversion funnels with simple built-in operations.',
                'features' => json_encode([
                    '1 workspace with Account Owner dashboard access',
                    'Lead capture funnels and conversion tracking',
                    'Basic funnel analytics and payment monitoring',
                    'Basic funnel operations with built-in tracking and status updates',
                ], JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }
};
