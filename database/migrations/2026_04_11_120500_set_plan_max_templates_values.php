<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('plans', 'max_templates')) {
            return;
        }

        $now = now();

        DB::table('plans')->whereRaw('LOWER(code) = ?', ['free-trial'])->update([
            'max_templates' => 1,
            'updated_at' => $now,
        ]);
        DB::table('plans')->whereRaw('LOWER(code) = ?', ['starter'])->update([
            'max_templates' => 2,
            'updated_at' => $now,
        ]);
        DB::table('plans')->whereRaw('LOWER(code) = ?', ['growth'])->update([
            'max_templates' => null,
            'updated_at' => $now,
        ]);
        DB::table('plans')->whereRaw('LOWER(code) = ?', ['scale'])->update([
            'max_templates' => null,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('plans', 'max_templates')) {
            return;
        }

        DB::table('plans')->update([
            'max_templates' => null,
            'updated_at' => now(),
        ]);
    }
};
