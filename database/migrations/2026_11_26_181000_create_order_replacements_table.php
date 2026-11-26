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
        Schema::create('order_replacements', function (Blueprint $table) {
            $table->id();
            $table->string('replacement_number', 50)->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Valid professional reasons only:
            // physical_damage (photo mandatory), defective_not_working, wrong_item, missing_parts
            $table->string('reason', 50);
            $table->string('reason_label', 150);
            $table->text('customer_notes')->nullable();
            $table->json('proof_images')->nullable();
            
            // Lifecycle status: requested, approved, rejected, pickup_assigned, picked_up, completed, cancelled
            $table->string('status', 30)->default('requested');
            
            // Pickup details assigned by Order Manager
            $table->date('pickup_date')->nullable();
            $table->foreignId('rider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rider_name', 100)->nullable();
            $table->string('rider_phone', 25)->nullable();
            
            // Review & Decision tracking
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('order_manager_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['rider_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_replacements');
    }
};
