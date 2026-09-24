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
        Schema::table('order_cancellations', function (Blueprint $table) {
            if (!Schema::hasColumn('order_cancellations', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'dropped'])->default('pending')->after('payment_reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_cancellations', function (Blueprint $table) {
            if (Schema::hasColumn('order_cancellations', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
