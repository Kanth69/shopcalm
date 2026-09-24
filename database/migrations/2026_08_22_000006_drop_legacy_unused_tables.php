<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('riders');
        Schema::dropIfExists('stock_requests');
        Schema::dropIfExists('coupon_brand');
        Schema::dropIfExists('coupon_category');
        Schema::dropIfExists('coupon_product');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy tables do not need to be restored.
    }
};
