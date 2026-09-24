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
        if (Schema::hasTable('delivery_partner_profiles')) {
            Schema::table('delivery_partner_profiles', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            Schema::dropIfExists('delivery_partner_profiles');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('delivery_partner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('serviceable_pincodes')->nullable();
            $table->integer('max_active_orders')->default(10);
            $table->boolean('is_online')->default(true);
            $table->timestamp('last_assigned_at')->nullable();
            $table->timestamps();
        });
    }
};
