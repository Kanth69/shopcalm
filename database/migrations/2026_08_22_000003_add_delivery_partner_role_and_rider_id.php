<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure Delivery Partner role exists in roles table
        DB::table('roles')->updateOrInsert(
            ['id' => 7],
            ['name' => 'Delivery Partner', 'created_at' => now(), 'updated_at' => now()]
        );

        // 2. Add rider_id and delivery_otp to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'rider_id')) {
                $table->foreignId('rider_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'delivery_otp')) {
                $table->string('delivery_otp', 10)->nullable();
            }
        });

        // 3. Add rider_id to order_fulfillments table
        Schema::table('order_fulfillments', function (Blueprint $table) {
            if (!Schema::hasColumn('order_fulfillments', 'rider_id')) {
                $table->foreignId('rider_id')->nullable()->after('rider_phone')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            if (Schema::hasColumn('order_fulfillments', 'rider_id')) {
                $table->dropForeign(['rider_id']);
                $table->dropColumn('rider_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'rider_id')) {
                $table->dropForeign(['rider_id']);
                $table->dropColumn('rider_id');
            }
            if (Schema::hasColumn('orders', 'delivery_otp')) {
                $table->dropColumn('delivery_otp');
            }
        });
    }
};
