<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop existing tables if they exist to avoid conflicts (Teardown for initial setup)
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupon_category');
        Schema::dropIfExists('coupon_brand');
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupon_excluded_category');
        Schema::dropIfExists('coupon_excluded_brand');
        Schema::dropIfExists('coupon_excluded_product');
        Schema::dropIfExists('coupons');
        Schema::enableForeignKeyConstraints();

        // 2. Create the main Coupons table
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Discount config
            $table->string('discount_type'); // Enums\CouponType
            $table->decimal('discount_value', 10, 2)->default(0);

            // Order Conditions
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('max_discount_amount', 10, 2)->nullable();

            // Applicability settings
            $table->boolean('is_entire_store')->default(true);
            $table->string('customer_restriction')->default('ALL'); // Enums\CustomerRestriction

            // Usage Limits
            $table->integer('total_usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->integer('per_customer_limit')->default(1);

            // Time Validity
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            // Use String status to match Enums\CouponStatus values (DRAFT, ACTIVE, etc.)
            $table->string('status')->default('ACTIVE');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'status']);
            $table->index(['start_at', 'end_at']);
        });

        // 3. Create inclusion relationship tables
        Schema::create('coupon_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('coupon_brand', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('coupon_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        });

        // 4. Create exclusion relationship tables
        Schema::create('coupon_excluded_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('coupon_excluded_brand', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
        });

        Schema::create('coupon_excluded_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        });

        // 5. Create usage tracking table
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // 1. Must disable foreign key checks BEFORE dropping the parent 'coupons' table
        // because pivot tables and usages depend on it.
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupon_excluded_product');
        Schema::dropIfExists('coupon_excluded_brand');
        Schema::dropIfExists('coupon_excluded_category');
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupon_brand');
        Schema::dropIfExists('coupon_category');
        Schema::dropIfExists('coupons');

        Schema::enableForeignKeyConstraints();
    }
};
