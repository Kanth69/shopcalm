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
            $table->index('price', 'products_price_index');
            $table->index(['status', 'category_id', 'created_at'], 'products_status_category_created_index');
            $table->index(['status', 'brand_id', 'created_at'], 'products_status_brand_created_index');
            $table->index(['status', 'created_at'], 'products_status_created_index');
            $table->index('name', 'products_name_index');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->index(['product_id', 'status', 'rating'], 'product_reviews_product_status_rating_index');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->index(['is_active', 'start_time', 'end_time'], 'offers_active_dates_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['status', 'name'], 'categories_status_name_index');
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->index(['status', 'name'], 'brands_status_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropIndex('brands_status_name_index');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_status_name_index');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropIndex('offers_active_dates_index');
        });

        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex('product_reviews_product_status_rating_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_price_index');
            $table->dropIndex('products_status_category_created_index');
            $table->dropIndex('products_status_brand_created_index');
            $table->dropIndex('products_status_created_index');
            $table->dropIndex('products_name_index');
        });
    }
};
