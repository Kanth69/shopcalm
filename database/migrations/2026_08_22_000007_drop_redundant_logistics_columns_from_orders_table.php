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
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign key on rider_id if exists
            if (Schema::hasColumn('orders', 'rider_id')) {
                $table->dropForeign(['rider_id']);
                $table->dropColumn('rider_id');
            }

            $columnsToDrop = [
                'delivery_otp',
                'courier_partner',
                'tracking_number',
                'tracking_url',
                'shipped_at',
                'delivered_at',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_partner')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('rider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('delivery_otp', 6)->nullable();
        });
    }
};
