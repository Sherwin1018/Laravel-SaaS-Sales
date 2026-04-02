<?php

use App\Models\Funnel;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\SignupIntent;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads')) {
            DB::table('leads')
                ->select(['id', 'status'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $normalized = Lead::normalizeStatus($row->status);
                        if ($normalized !== $row->status) {
                            DB::table('leads')->where('id', $row->id)->update(['status' => $normalized]);
                        }
                    }
                });
        }

        if (Schema::hasTable('payments')) {
            DB::table('payments')
                ->select(['id', 'status'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $normalized = Payment::normalizeStatus($row->status);
                        if ($normalized !== $row->status) {
                            DB::table('payments')->where('id', $row->id)->update(['status' => $normalized]);
                        }
                    }
                });
        }

        if (Schema::hasTable('funnels')) {
            DB::table('funnels')
                ->select(['id', 'status'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $normalized = Funnel::normalizeStatus($row->status);
                        if ($normalized !== $row->status) {
                            DB::table('funnels')->where('id', $row->id)->update(['status' => $normalized]);
                        }
                    }
                });
        }

        if (Schema::hasTable('tenants')) {
            DB::table('tenants')
                ->select(['id', 'status'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $normalized = Tenant::normalizeStatus($row->status);
                        if ($normalized !== $row->status) {
                            DB::table('tenants')->where('id', $row->id)->update(['status' => $normalized]);
                        }
                    }
                });
        }

        if (Schema::hasTable('signup_intents')) {
            DB::table('signup_intents')
                ->select(['id', 'status'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $normalized = SignupIntent::normalizeStatus($row->status);
                        if ($normalized !== $row->status) {
                            DB::table('signup_intents')->where('id', $row->id)->update(['status' => $normalized]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        // Normalization is intentionally irreversible.
    }
};
