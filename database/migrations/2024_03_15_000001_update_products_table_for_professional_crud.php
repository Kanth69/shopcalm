<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode')->nullable()->after('sku');
            }
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->nullable()->after('selling_price');
            }
            if (!Schema::hasColumn('products', 'gst_percentage')) {
                $table->decimal('gst_percentage', 5, 2)->default(0)->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'tags')) {
                $table->string('tags')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'is_best_seller')) {
                $table->boolean('is_best_seller')->default(false)->after('trending');
            }
            if (!Schema::hasColumn('products', 'specifications')) {
                $table->text('specifications')->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = ['barcode', 'cost_price', 'gst_percentage', 'tags', 'is_best_seller', 'specifications', 'meta_title', 'meta_description'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
