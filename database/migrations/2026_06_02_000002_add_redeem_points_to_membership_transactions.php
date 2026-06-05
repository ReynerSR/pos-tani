<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'points_redeemed')) {
                $table->decimal('points_redeemed', 10, 2)->default(0)->after('points_earned');
            }

            if (! Schema::hasColumn('transactions', 'point_redeem_amount')) {
                $table->decimal('point_redeem_amount', 15, 2)->default(0)->after('points_redeemed');
            }
        });

        Schema::table('membership_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('membership_rules', 'redeem_point_value')) {
                $table->decimal('redeem_point_value', 15, 2)->default(100)->after('point_per_nominal');
            }

            if (! Schema::hasColumn('membership_rules', 'minimum_redeem_points')) {
                $table->decimal('minimum_redeem_points', 10, 2)->default(100)->after('redeem_point_value');
            }

            if (! Schema::hasColumn('membership_rules', 'max_redeem_percent')) {
                $table->decimal('max_redeem_percent', 5, 2)->default(50)->after('minimum_redeem_points');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'point_redeem_amount')) {
                $table->dropColumn('point_redeem_amount');
            }

            if (Schema::hasColumn('transactions', 'points_redeemed')) {
                $table->dropColumn('points_redeemed');
            }
        });

        Schema::table('membership_rules', function (Blueprint $table) {
            foreach (['max_redeem_percent', 'minimum_redeem_points', 'redeem_point_value'] as $column) {
                if (Schema::hasColumn('membership_rules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
