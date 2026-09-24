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
            if (!Schema::hasColumn('order_fulfillments', 'delivery_issue')) {
                $table->string('delivery_issue')->nullable()->after('delivery_otp');
            }
            if (!Schema::hasColumn('order_fulfillments', 'delivery_issue_at')) {
                $table->timestamp('delivery_issue_at')->nullable()->after('delivery_issue');
            }
            if (!Schema::hasColumn('order_fulfillments', 'delivery_issue_resolved_at')) {
                $table->timestamp('delivery_issue_resolved_at')->nullable()->after('delivery_issue_at');
            }
            if (!Schema::hasColumn('order_fulfillments', 'delivery_issue_resolution')) {
                $table->string('delivery_issue_resolution')->nullable()->after('delivery_issue_resolved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_issue',
                'delivery_issue_at',
                'delivery_issue_resolved_at',
                'delivery_issue_resolution',
            ]);
        });
    }
};
