<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Heal any legacy orders where MySQL stored '' (empty string) due to 'failed' not being in the ENUM
        DB::table('orders')
            ->where('status', '')
            ->orWhereNull('status')
            ->update(['status' => 'pending']);

        // 2. Expand orders.status ENUM on MySQL to include 'failed' as well
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'processing', 'packed', 'shipped', 'out for delivery', 'delivered', 'cancelled', 'returned', 'failed') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('orders')
            ->whereIn('status', ['', 'failed'])
            ->update(['status' => 'pending']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'processing', 'packed', 'shipped', 'out for delivery', 'delivered', 'cancelled', 'returned') NOT NULL DEFAULT 'pending'");
        }
    }
};
