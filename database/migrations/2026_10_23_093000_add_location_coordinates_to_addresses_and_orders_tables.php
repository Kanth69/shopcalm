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
            if (!Schema::hasColumn('addresses', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('country');
            }
            if (!Schema::hasColumn('addresses', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('addresses', 'landmark')) {
                $table->string('landmark')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('addresses', 'type')) {
                $table->string('type', 50)->default('home')->after('landmark');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_latitude')) {
                $table->decimal('shipping_latitude', 10, 8)->nullable()->after('shipping_country');
            }
            if (!Schema::hasColumn('orders', 'shipping_longitude')) {
                $table->decimal('shipping_longitude', 11, 8)->nullable()->after('shipping_latitude');
            }
            if (!Schema::hasColumn('orders', 'shipping_landmark')) {
                $table->string('shipping_landmark')->nullable()->after('shipping_longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'landmark', 'type']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_latitude', 'shipping_longitude', 'shipping_landmark']);
        });
    }
};
