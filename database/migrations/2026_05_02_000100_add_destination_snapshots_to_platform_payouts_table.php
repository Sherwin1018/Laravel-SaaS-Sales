<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_payouts', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_payouts', 'destination_value_snapshot')) {
                $table->text('destination_value_snapshot')->nullable()->after('masked_destination');
            }

            if (! Schema::hasColumn('platform_payouts', 'account_name_snapshot')) {
                $table->string('account_name_snapshot', 160)->nullable()->after('destination_value_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_payouts', function (Blueprint $table) {
            if (Schema::hasColumn('platform_payouts', 'account_name_snapshot')) {
                $table->dropColumn('account_name_snapshot');
            }

            if (Schema::hasColumn('platform_payouts', 'destination_value_snapshot')) {
                $table->dropColumn('destination_value_snapshot');
            }
        });
    }
};
