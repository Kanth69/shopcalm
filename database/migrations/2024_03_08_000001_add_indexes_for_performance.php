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
        Schema::table('products', function (Blueprint $table) {
            // MySQL automatically creates indexes for foreign keys (category_id, brand_id).
            // We only add explicit indexes for other columns used in filtering/sorting.
            $table->index('status');
            $table->index('featured');
            $table->index('trending');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['featured']);
            $table->dropIndex(['trending']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
