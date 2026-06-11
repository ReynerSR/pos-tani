<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_tier_histories')) {
            return;
        }

        Schema::create('customer_tier_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_tier', 30)->nullable();
            $table->string('new_tier', 30);
            $table->decimal('old_total_accumulation', 15, 2)->default(0);
            $table->decimal('new_total_accumulation', 15, 2)->default(0);
            $table->string('source', 50)->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tier_histories');
    }
};
