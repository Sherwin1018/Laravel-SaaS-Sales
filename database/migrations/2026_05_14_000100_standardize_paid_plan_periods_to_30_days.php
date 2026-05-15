<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')
            ->where('code', '!=', 'free-trial')
            ->update(['period' => '30 days']);
    }

    public function down(): void
    {
        DB::table('plans')
            ->where('code', '!=', 'free-trial')
            ->update(['period' => 'per month']);
    }
};
