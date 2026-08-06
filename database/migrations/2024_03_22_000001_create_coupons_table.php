<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safety drop for conflicts
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::enableForeignKeyConstraints();

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('discount_type'); // Enum: PERCENTAGE, FLAT
            $table->decimal('discount_value', 10, 2);
            $table->decimal('minimum_order_amount', 10, 2)->default(0);
            $table->decimal('maximum_discount_amount', 10, 2)->nullable();

            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_customer')->default(1);
            $table->integer('used_count')->default(0);

            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('stackable')->default(false);
            $table->integer('priority')->default(0);

            // Eligibility
            $table->string('applicable_type')->default('ALL'); // ALL, CATEGORY, BRAND, PRODUCT
            $table->unsignedBigInteger('applicable_id')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_active', 'valid_from', 'valid_until']);
            $table->index(['applicable_type', 'applicable_id']);
        });

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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::enableForeignKeyConstraints();
    }
};
