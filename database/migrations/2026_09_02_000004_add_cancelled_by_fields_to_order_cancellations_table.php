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
            if (!Schema::hasColumn('order_cancellations', 'cancelled_by_type')) {
                $table->enum('cancelled_by_type', ['customer', 'admin'])->default('customer')->after('user_id');
            }
            if (!Schema::hasColumn('order_cancellations', 'cancelled_by_id')) {
                $table->foreignId('cancelled_by_id')->nullable()->after('cancelled_by_type')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('order_cancellations', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('cancellation_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_cancellations', function (Blueprint $table) {
            if (Schema::hasColumn('order_cancellations', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
            if (Schema::hasColumn('order_cancellations', 'cancelled_by_id')) {
                $table->dropForeign(['cancelled_by_id']);
                $table->dropColumn('cancelled_by_id');
            }
            if (Schema::hasColumn('order_cancellations', 'cancelled_by_type')) {
                $table->dropColumn('cancelled_by_type');
            }
        });
    }
};
