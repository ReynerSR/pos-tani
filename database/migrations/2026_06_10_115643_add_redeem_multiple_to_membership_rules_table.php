<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('membership_rules', function (Blueprint $table) {
            $table->integer('redeem_multiple')->default(100)->after('minimum_redeem_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_rules', function (Blueprint $table) {
            $table->dropColumn('redeem_multiple');
        });
    }
};
