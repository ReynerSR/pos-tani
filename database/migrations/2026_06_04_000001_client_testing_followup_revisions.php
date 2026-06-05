<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_tier_histories')) {
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

        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'status')) {
                $table->string('status', 30)->default('approved')->after('total_price');
            }
            if (! Schema::hasColumn('purchases', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchases', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });

        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'eligible_tiers')) {
                $table->json('eligible_tiers')->nullable()->after('discount_amount');
            }
            if (! Schema::hasColumn('promotions', 'can_redeem_with_points')) {
                $table->boolean('can_redeem_with_points')->default(false)->after('eligible_tiers');
            }
            if (! Schema::hasColumn('promotions', 'redeem_points_required')) {
                $table->integer('redeem_points_required')->default(0)->after('can_redeem_with_points');
            }
            if (! Schema::hasColumn('promotions', 'redeem_discount_amount')) {
                $table->decimal('redeem_discount_amount', 15, 2)->default(0)->after('redeem_points_required');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'visible_password')) {
                $table->text('visible_password')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tier_histories');

        Schema::table('purchases', function (Blueprint $table) {
            foreach (['approved_at', 'approved_by', 'status'] as $column) {
                if (Schema::hasColumn('purchases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('promotions', function (Blueprint $table) {
            foreach (['redeem_discount_amount', 'redeem_points_required', 'can_redeem_with_points', 'eligible_tiers'] as $column) {
                if (Schema::hasColumn('promotions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'visible_password')) {
                $table->dropColumn('visible_password');
            }
        });
    }
};
