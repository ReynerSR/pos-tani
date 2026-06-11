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
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn([
                'can_redeem_with_points',
                'redeem_points_required',
                'redeem_discount_amount',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->boolean('can_redeem_with_points')->default(false);
            $table->integer('redeem_points_required')->default(0);
            $table->decimal('redeem_discount_amount', 15, 2)->default(0);
        });
    }
};
