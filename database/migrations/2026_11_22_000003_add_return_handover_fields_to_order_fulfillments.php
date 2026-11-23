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
        Schema::table('order_fulfillments', function (Blueprint $table) {
            if (!Schema::hasColumn('order_fulfillments', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('delivered_at');
            }
            if (!Schema::hasColumn('order_fulfillments', 'returned_to')) {
                $table->foreignId('returned_to')->nullable()->after('returned_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            if (Schema::hasColumn('order_fulfillments', 'returned_to')) {
                $table->dropForeign(['returned_to']);
                $table->dropColumn('returned_to');
            }
            if (Schema::hasColumn('order_fulfillments', 'returned_at')) {
                $table->dropColumn('returned_at');
            }
        });
    }
};
