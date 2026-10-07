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
        // 1. Ensure roles table contains all enterprise roles
        $roles = [
            ['id' => 1, 'name' => 'Super Admin'],
            ['id' => 2, 'name' => 'Admin'],
            ['id' => 3, 'name' => 'Customer'],
            ['id' => 4, 'name' => 'Product Manager'],
            ['id' => 5, 'name' => 'Order Manager'],
            ['id' => 6, 'name' => 'Customer Support'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                ['name' => $role['name'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. Add rejection_reason and update status column on products table
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('products', 'submitted_by')) {
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete()->after('rejection_reason');
            }
        });

        // Change status column to string with default 'Active' to support 'Pending_Approval' and 'Rejected'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE products MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'submitted_by')) {
                $table->dropForeign(['submitted_by']);
                $table->dropColumn('submitted_by');
            }
            if (Schema::hasColumn('products', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
