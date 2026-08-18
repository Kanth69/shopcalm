<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_rejection_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->string('status', 20)->default('active'); // 'active', 'resolved'
            $table->timestamps();
        });

        // Migrate any existing rejection reasons from products table into the new table
        if (Schema::hasColumn('products', 'rejection_reason')) {
            $existing = DB::table('products')
                ->whereNotNull('rejection_reason')
                ->where('rejection_reason', '!=', '')
                ->get(['id', 'rejection_reason', 'updated_at']);

            foreach ($existing as $item) {
                DB::table('product_rejection_reasons')->insert([
                    'product_id' => $item->id,
                    'rejected_by' => null,
                    'reason' => $item->rejection_reason,
                    'status' => 'active',
                    'created_at' => $item->updated_at ?? now(),
                    'updated_at' => $item->updated_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_rejection_reasons');
    }
};
