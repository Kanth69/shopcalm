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
        // 1. Create Offers Table
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['MEGA_SALE', 'FLASH_DEAL', 'BANK_OFFER', 'CATEGORY_SALE', 'DIRECT_DISCOUNT'])->default('MEGA_SALE');
            $table->string('badge_text')->nullable(); // e.g. 🔥 BIG BILLION DEAL
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('theme_color')->default('#2563eb'); // Accent color for sale bar / badges
            $table->enum('discount_type', ['PERCENTAGE', 'FLAT'])->default('PERCENTAGE');
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_purchase_amount', 10, 2)->default(0);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->integer('stock_limit')->nullable(); // For Flash Deals
            $table->integer('claimed_count')->default(0);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Create Offer Targets Pivot Table
        Schema::create('offer_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
            $table->enum('target_type', ['CATEGORY', 'BRAND', 'PRODUCT', 'PAYMENT_METHOD']);
            $table->unsignedBigInteger('target_id')->nullable();
            $table->timestamps();
        });

        // 3. Update Banners Table
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'offer_id')) {
                $table->foreignId('offer_id')->nullable()->constrained('offers')->onDelete('set null')->after('id');
            }
            if (!Schema::hasColumn('banners', 'banner_type')) {
                $table->string('banner_type')->default('GENERAL_PROMO')->after('offer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'offer_id')) {
                $table->dropForeign(['offer_id']);
                $table->dropColumn('offer_id');
            }
            if (Schema::hasColumn('banners', 'banner_type')) {
                $table->dropColumn('banner_type');
            }
        });

        Schema::dropIfExists('offer_targets');
        Schema::dropIfExists('offers');
    }
};
