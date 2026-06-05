<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('whatsapp_number', 20);
            $table->text('address')->nullable();
            $table->enum('tier', ['bronze', 'silver', 'gold'])->default('bronze');
            $table->decimal('total_accumulation', 15, 2)->default(0);
            $table->decimal('point_balance', 10, 2)->default(0);
            $table->date('registered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
