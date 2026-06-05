<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('is_active');
            }
        });

        if (Schema::hasColumn('customers', 'whatsapp_number')) {
            DB::table('customers')->whereNull('whatsapp_number')->update(['whatsapp_number' => '']);
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE customers MODIFY whatsapp_number VARCHAR(20) NOT NULL');
            }
        }

        if (Schema::hasColumn('products', 'supplier_id')) {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->dropForeign(['supplier_id']);
                } catch (Throwable $e) {
                    // Foreign key may not exist on SQLite/local copies.
                }
                $table->dropColumn('supplier_id');
            });
        }

        // Existing MySQL installations used ENUM for product unit. Convert it to VARCHAR
        // so owner/admin can add a custom satuan from the product form.
        if (Schema::hasColumn('products', 'unit') && DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE products MODIFY unit VARCHAR(30) NOT NULL DEFAULT 'pcs'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_seen_at')) {
                $table->dropColumn('last_seen_at');
            }
        });
    }
};
