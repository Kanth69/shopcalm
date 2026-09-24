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
        Schema::create('order_cancellations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('cancellation_reason');
            $table->decimal('cancellation_fee', 10, 2)->default(0.00); // Non-refundable GST Tax Amount
            $table->decimal('refund_amount', 10, 2)->default(0.00);    // Net Refund Amount
            $table->enum('refund_status', ['none', 'pending', 'processed', 'failed'])->default('pending');
            $table->enum('refund_method', ['none', 'wallet', 'original_source', 'bank_upi'])->default('none');
            $table->string('refund_upi_id')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_cancellations');
    }
};
