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
        Schema::create('cash_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_number')->unique();
            $table->foreignId('rider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->integer('order_count')->default(1);
            $table->string('payment_mode')->default('cash'); // cash, upi, qr
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('order_fulfillments', function (Blueprint $table) {
            if (!Schema::hasColumn('order_fulfillments', 'cod_settlement_id')) {
                $table->foreignId('cod_settlement_id')->nullable()->after('delivery_issue_resolution')->constrained('cash_settlements')->nullOnDelete();
            }
            if (!Schema::hasColumn('order_fulfillments', 'cod_deposited_at')) {
                $table->timestamp('cod_deposited_at')->nullable()->after('cod_settlement_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropForeign(['cod_settlement_id']);
            $table->dropColumn(['cod_settlement_id', 'cod_deposited_at']);
        });

        Schema::dropIfExists('cash_settlements');
    }
};
