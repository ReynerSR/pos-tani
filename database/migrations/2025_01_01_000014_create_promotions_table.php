<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membuat tabel 'promotions' untuk menyimpan data program promo dan diskon barang
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('promo_name', 150);
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('discount_amount', 15, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
