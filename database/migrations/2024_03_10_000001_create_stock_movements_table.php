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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type');
            $table->string('source');
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');

            // Polymorphic relation to track what caused the movement (e.g., an Order ID)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Explicitly named indexes for easier management
            $table->index('movement_type', 'sm_type_index');
            $table->index('source', 'sm_source_index');
            $table->index('created_by', 'sm_user_index');
            $table->index('created_at', 'sm_date_index');
            $table->index(['reference_type', 'reference_id'], 'sm_ref_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // 1. Drop the foreign keys first
            $table->dropForeign(['product_id']);
            if (Schema::hasColumn('stock_movements', 'created_by')) {
                $table->dropForeign(['created_by']);
            }

            // 2. Drop the explicit indexes
            $table->dropIndex('sm_type_index');
            $table->dropIndex('sm_source_index');
            $table->dropIndex('sm_user_index');
            $table->dropIndex('sm_date_index');
            $table->dropIndex('sm_ref_index');
        });

        Schema::dropIfExists('stock_movements');
    }
};
