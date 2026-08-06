<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();

            $table->string('desktop_image');
            $table->string('mobile_image')->nullable();

            $table->string('primary_button_text')->nullable();
            $table->string('primary_button_link')->nullable();
            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_button_link')->nullable();

            $table->string('badge_text')->nullable();
            $table->string('badge_color')->default('primary');

            $table->string('banner_type')->default('HERO'); // HERO, PROMOTION, CATEGORY, BRAND, PRODUCT
            $table->string('related_type')->nullable(); // CATEGORY, BRAND, PRODUCT
            $table->unsignedBigInteger('related_id')->nullable();

            $table->string('background_color')->nullable();
            $table->string('text_alignment')->default('left'); // left, center, right
            $table->integer('display_order')->default(0);

            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'start_date', 'end_date']);
            $table->index(['banner_type', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
