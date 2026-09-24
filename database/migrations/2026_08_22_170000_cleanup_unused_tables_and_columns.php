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
        // 1. Drop foreign keys and tables for wallet_transactions
        if (Schema::hasTable('wallet_transactions')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->dropForeign(['wallet_id']);
                $table->dropForeign(['user_id']);
                $table->dropForeign(['order_id']);
            });
            Schema::dropIfExists('wallet_transactions');
        }

        // 2. Drop foreign keys and tables for customer_wallets
        if (Schema::hasTable('customer_wallets')) {
            Schema::table('customer_wallets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['referred_by']);
            });
            Schema::dropIfExists('customer_wallets');
        }

        // 3. Drop empty dummy table if exists
        Schema::dropIfExists('customer_wallets_and_transactions');

        // 4. Drop foreign keys and table for account_deletion_requests
        if (Schema::hasTable('account_deletion_requests')) {
            Schema::table('account_deletion_requests', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['reviewed_by']);
            });
            Schema::dropIfExists('account_deletion_requests');
        }

        // 5. Drop dead columns from orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'wallet_amount_used')) {
                    $table->dropColumn('wallet_amount_used');
                }
                if (Schema::hasColumn('orders', 'cod_fee')) {
                    $table->dropColumn('cod_fee');
                }
            });
        }

        // 6. Drop dead column from order_fulfillments table
        if (Schema::hasTable('order_fulfillments')) {
            Schema::table('order_fulfillments', function (Blueprint $table) {
                if (Schema::hasColumn('order_fulfillments', 'cod_collected_amount')) {
                    $table->dropColumn('cod_collected_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for dead/unused tables cleanup
    }
};
