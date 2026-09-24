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
        // 1. Add wallet_amount_used and cod_fee to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'wallet_amount_used')) {
                $table->decimal('wallet_amount_used', 10, 2)->default(0.00)->after('coupon_discount_amount');
            }
            if (!Schema::hasColumn('orders', 'cod_fee')) {
                $table->decimal('cod_fee', 10, 2)->nullable()->default(null)->after('wallet_amount_used');
            }
        });

        // 2. Create customer wallets table
        if (!Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->decimal('balance', 12, 2)->default(0.00);
                $table->decimal('total_earned', 12, 2)->default(0.00);
                $table->string('referral_code', 30)->unique();
                $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
                $table->enum('status', ['active', 'frozen'])->default('active');
                $table->timestamps();
            });
        }

        // 3. Create wallet transactions ledger table
        if (!Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->enum('type', ['CREDIT', 'DEBIT']);
                $table->decimal('amount', 12, 2);
                $table->decimal('balance_after', 12, 2);
                $table->enum('source', [
                    'SIGNUP_BONUS',
                    'REFERRAL_BONUS',
                    'CHECKOUT_REDEEM',
                    'ADMIN_ADJUSTMENT',
                    'ORDER_REFUND'
                ]);
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->string('description', 255);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'wallet_amount_used')) {
                $table->dropColumn('wallet_amount_used');
            }
            if (Schema::hasColumn('orders', 'cod_fee')) {
                $table->dropColumn('cod_fee');
            }
        });
    }
};
