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
            $table->dropForeign(['current_campaign_id']);
            $table->dropColumn(['discount_percentage', 'is_best_seller', 'current_campaign_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->boolean('is_best_seller')->default(false);
            $table->unsignedBigInteger('current_campaign_id')->nullable();
        });
    }
};
