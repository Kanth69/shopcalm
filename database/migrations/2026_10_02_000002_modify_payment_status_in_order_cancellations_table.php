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
        if (Schema::hasColumn('order_cancellations', 'payment_status') && DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE order_cancellations MODIFY payment_status VARCHAR(50) DEFAULT 'pending' NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('order_cancellations', 'payment_status') && DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE order_cancellations MODIFY payment_status ENUM('pending', 'paid', 'failed', 'dropped') DEFAULT 'pending'");
        }
    }
};
