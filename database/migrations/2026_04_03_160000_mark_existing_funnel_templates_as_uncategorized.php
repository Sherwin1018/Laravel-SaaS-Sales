<?php

use App\Models\FunnelTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('funnel_templates')
            ->where('template_type', 'service')
            ->update([
                'template_type' => FunnelTemplate::TEMPLATE_TYPE_UNCATEGORIZED,
            ]);
    }

    public function down(): void
    {
        DB::table('funnel_templates')
            ->where('template_type', FunnelTemplate::TEMPLATE_TYPE_UNCATEGORIZED)
            ->update([
                'template_type' => 'service',
            ]);
    }
};
