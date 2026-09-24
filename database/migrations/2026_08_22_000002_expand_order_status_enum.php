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
        // Expand orders status to support all lifecycle stages
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'processing', 'packed', 'shipped', 'out for delivery', 'delivered', 'cancelled', 'returned') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'packed', 'shipped', 'out for delivery', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
