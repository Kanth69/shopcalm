<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('discount_type'); // Enums\DiscountType
            $table->decimal('discount_value', 10, 2);

            // Rules
            $table->decimal('minimum_order_amount', 10, 2)->default(0);
            $table->decimal('maximum_discount', 10, 2)->nullable();

            // Limits
            $table->integer('usage_limit')->nullable()->comment('Total times this coupon can be used across all users');
            $table->integer('used_count')->default(0);
            $table->integer('per_user_limit')->default(1)->comment('How many times a single user can use this coupon');

            // Validity
            $table->timestamp('start_date')->nullable();
            $table->timestamp('expiry_date')->nullable();

            $table->boolean('status')->default(true);
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Never lose historical data

            // Indexes for performance
            $table->index('code');
            $table->index('status');
            $table->index(['start_date', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
