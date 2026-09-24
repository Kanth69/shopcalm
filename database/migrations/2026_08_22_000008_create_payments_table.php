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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number')->index();
            $table->string('gateway', 50)->default('cashfree')->index(); // 'cashfree', 'cod', 'razorpay', 'phonepe'
            $table->string('gateway_order_id')->nullable()->index(); // e.g. cf_order_id
            $table->string('gateway_payment_id')->nullable()->index(); // e.g. cf_payment_id / transaction ID
            $table->string('payment_session_id')->nullable()->index(); // Cashfree payment_session_id
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('status', 50)->default('PENDING')->index(); // 'SUCCESS', 'FAILED', 'PENDING', 'USER_DROPPED', 'CANCELLED'
            $table->string('payment_method_group', 50)->nullable(); // 'upi', 'card', 'netbanking', 'wallet', 'cod'
            $table->json('payment_method_details')->nullable(); // detailed upi_id, card_network, bank_name, etc.
            $table->string('bank_reference')->nullable()->index(); // Bank UTR / RRN number
            $table->timestamp('payment_time')->nullable();
            $table->text('gateway_message')->nullable();
            $table->json('raw_response')->nullable(); // full gateway API response payload for financial audit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
