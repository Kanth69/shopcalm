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
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->string('campaign_type', 40)->default('custom')->after('destination_id');
            $table->string('target_language', 20)->default('all')->after('target_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropColumn(['campaign_type', 'target_language']);
        });
    }
};
