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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'tax_amount')) {
                    $table->decimal('tax_amount', 10, 2)->default(0.00)->after('coupon_discount_amount');
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'tax_rate')) {
                    $table->decimal('tax_rate', 5, 2)->default(18.00)->after('total_price');
                }
                if (!Schema::hasColumn('order_items', 'tax_amount')) {
                    $table->decimal('tax_amount', 10, 2)->default(0.00)->after('tax_rate');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'tax_amount')) {
                    $table->dropColumn('tax_amount');
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (Schema::hasColumn('order_items', 'tax_amount')) {
                    $table->dropColumn('tax_amount');
                }
                if (Schema::hasColumn('order_items', 'tax_rate')) {
                    $table->dropColumn('tax_rate');
                }
            });
        }
    }
};
