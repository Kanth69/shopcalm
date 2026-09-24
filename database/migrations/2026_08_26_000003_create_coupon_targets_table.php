<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create unified coupon_targets table
        if (!Schema::hasTable('coupon_targets')) {
            Schema::create('coupon_targets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
                $table->enum('target_type', ['category', 'brand', 'product']);
                $table->unsignedBigInteger('target_id');
                $table->timestamps();

                $table->unique(['coupon_id', 'target_type', 'target_id'], 'coupon_target_unique');
                $table->index(['target_type', 'target_id']);
            });
        }

        // 2. Migrate existing records from coupon_category
        if (Schema::hasTable('coupon_category')) {
            $catRecords = DB::table('coupon_category')->get();
            foreach ($catRecords as $row) {
                DB::table('coupon_targets')->updateOrInsert(
                    ['coupon_id' => $row->coupon_id, 'target_type' => 'category', 'target_id' => $row->category_id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
            Schema::dropIfExists('coupon_category');
        }

        // 3. Migrate existing records from coupon_brand
        if (Schema::hasTable('coupon_brand')) {
            $brandRecords = DB::table('coupon_brand')->get();
            foreach ($brandRecords as $row) {
                DB::table('coupon_targets')->updateOrInsert(
                    ['coupon_id' => $row->coupon_id, 'target_type' => 'brand', 'target_id' => $row->brand_id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
            Schema::dropIfExists('coupon_brand');
        }

        // 4. Migrate existing records from coupon_product
        if (Schema::hasTable('coupon_product')) {
            $productRecords = DB::table('coupon_product')->get();
            foreach ($productRecords as $row) {
                DB::table('coupon_targets')->updateOrInsert(
                    ['coupon_id' => $row->coupon_id, 'target_type' => 'product', 'target_id' => $row->product_id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
            Schema::dropIfExists('coupon_product');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_targets');
    }
};
