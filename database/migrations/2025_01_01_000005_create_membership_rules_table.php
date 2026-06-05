<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_rules', function (Blueprint $table) {
            $table->id();
            $table->decimal('tier_silver_min', 15, 2)->default(5000000);
            $table->decimal('tier_gold_min', 15, 2)->default(15000000);
            $table->integer('point_per_nominal')->default(1000);
            $table->decimal('discount_bronze', 5, 2)->default(0);
            $table->decimal('discount_silver', 5, 2)->default(3);
            $table->decimal('discount_gold', 5, 2)->default(5);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_rules');
    }
};
