<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tenantIds = DB::table('tenant_payout_accounts')
            ->select('tenant_id')
            ->groupBy('tenant_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('tenant_id');

        foreach ($tenantIds as $tenantId) {
            $accounts = DB::table('tenant_payout_accounts')
                ->where('tenant_id', $tenantId)
                ->orderByDesc('is_default')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get(['id']);

            $keeperId = optional($accounts->first())->id;

            if (! $keeperId) {
                continue;
            }

            DB::table('tenant_payout_accounts')
                ->where('id', $keeperId)
                ->update(['is_default' => true]);

            DB::table('tenant_payout_accounts')
                ->where('tenant_id', $tenantId)
                ->where('id', '!=', $keeperId)
                ->delete();
        }

        Schema::table('tenant_payout_accounts', function (Blueprint $table) {
            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_payout_accounts', function (Blueprint $table) {
            $table->dropUnique(['tenant_id']);
        });
    }
};
