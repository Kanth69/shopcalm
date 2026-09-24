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
            // Logistics Package Dimensions & Weight (for iThink Logistics)
            $table->decimal('weight', 8, 2)->default(0.50)->after('main_image')->comment('Package weight in kg');
            $table->decimal('length', 8, 2)->default(15.00)->after('weight')->comment('Package length in cm');
            $table->decimal('width', 8, 2)->default(10.00)->after('length')->comment('Package width in cm');
            $table->decimal('height', 8, 2)->default(5.00)->after('width')->comment('Package height in cm');

            // Option & Stock Matrix (Sizes/Colors)
            $table->boolean('has_options')->default(false)->after('height');
            $table->string('option_type', 50)->nullable()->after('has_options')->comment('size, waist, color');
            $table->json('option_stocks')->nullable()->after('option_type')->comment('JSON map of options and stock');

            // GST Tax & Invoice Compliance
            $table->string('hsn_code', 20)->nullable()->default('8518')->after('option_stocks');
            $table->decimal('tax_rate', 5, 2)->default(18.00)->after('hsn_code')->comment('GST Tax percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'weight',
                'length',
                'width',
                'height',
                'has_options',
                'option_type',
                'option_stocks',
                'hsn_code',
                'tax_rate',
            ]);
        });
    }
};
