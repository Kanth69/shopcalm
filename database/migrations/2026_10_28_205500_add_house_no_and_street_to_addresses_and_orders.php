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
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'house_no')) {
                $table->string('house_no', 255)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('addresses', 'street')) {
                $table->string('street', 255)->nullable()->after('house_no');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_house_no')) {
                $table->string('shipping_house_no', 255)->nullable()->after('shipping_phone');
            }
            if (!Schema::hasColumn('orders', 'shipping_street')) {
                $table->string('shipping_street', 255)->nullable()->after('shipping_house_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'house_no')) {
                $table->dropColumn('house_no');
            }
            if (Schema::hasColumn('addresses', 'street')) {
                $table->dropColumn('street');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_house_no')) {
                $table->dropColumn('shipping_house_no');
            }
            if (Schema::hasColumn('orders', 'shipping_street')) {
                $table->dropColumn('shipping_street');
            }
        });
    }
};
