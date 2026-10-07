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
            if (!Schema::hasColumn('order_cancellations', 'wallet_refund_amount')) {
                $table->decimal('wallet_refund_amount', 10, 2)->default(0.00)->after('cancellation_fee');
            }
            if (!Schema::hasColumn('order_cancellations', 'online_refund_amount')) {
                $table->decimal('online_refund_amount', 10, 2)->default(0.00)->after('wallet_refund_amount');
            }
            if (!Schema::hasColumn('order_cancellations', 'online_refund_status')) {
                $table->enum('online_refund_status', ['none', 'pending', 'processed', 'failed'])->default('none')->after('refund_status');
            }
            if (!Schema::hasColumn('order_cancellations', 'razorpay_refund_id')) {
                $table->string('razorpay_refund_id', 100)->nullable()->after('payment_reference');
            }
        });

        // Extend refund_method DB column type to include 'dual'
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE order_cancellations MODIFY COLUMN refund_method VARCHAR(50) DEFAULT 'none'");
            } catch (\Throwable $e) {
                // Driver fallback
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_cancellations', function (Blueprint $table) {
            if (Schema::hasColumn('order_cancellations', 'wallet_refund_amount')) {
                $table->dropColumn('wallet_refund_amount');
            }
            if (Schema::hasColumn('order_cancellations', 'online_refund_amount')) {
                $table->dropColumn('online_refund_amount');
            }
            if (Schema::hasColumn('order_cancellations', 'online_refund_status')) {
                $table->dropColumn('online_refund_status');
            }
            if (Schema::hasColumn('order_cancellations', 'razorpay_refund_id')) {
                $table->dropColumn('razorpay_refund_id');
            }
        });
    }
};
