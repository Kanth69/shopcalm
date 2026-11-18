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
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('banner_image', 500)->nullable();
            $table->string('destination_type', 50)->default('home'); // home, offers, category, cart
            $table->foreignId('destination_id')->nullable(); // category id
            $table->string('target_gender', 30)->default('all'); // all, Male, Female
            $table->string('target_age_group', 50)->default('all'); // all, birthday_month, under_25, 25_40, over_40
            $table->string('target_activity', 50)->default('all'); // all, dormant
            $table->unsignedInteger('recipient_count')->default(0);
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
