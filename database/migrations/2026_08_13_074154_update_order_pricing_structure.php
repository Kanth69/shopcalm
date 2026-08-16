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
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('discount_amount', 'coupon_discount_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->renameColumn('product_price', 'unit_price');
            $table->decimal('original_price', 10, 2)->after('product_name');
            $table->decimal('offer_discount', 10, 2)->default(0)->after('original_price');
            $table->decimal('total_price', 10, 2)->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('coupon_discount_amount', 'discount_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->renameColumn('unit_price', 'product_price');
            $table->dropColumn(['original_price', 'offer_discount', 'total_price']);
        });
    }
};
