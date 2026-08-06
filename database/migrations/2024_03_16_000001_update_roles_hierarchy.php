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
        // 1. Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // 2. Shift existing users up to temporary IDs to avoid conflicts
        // Admin (1) -> 22, Customer (2) -> 33
        DB::table('users')->where('role_id', 1)->update(['role_id' => 22]);
        DB::table('users')->where('role_id', 2)->update(['role_id' => 33]);

        // 3. Re-map the roles table
        DB::table('roles')->truncate();
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Super Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Customer', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Update users to new final IDs
        // Temporary 22 -> Admin (2), Temporary 33 -> Customer (3)
        DB::table('users')->where('role_id', 22)->update(['role_id' => 2]);
        DB::table('users')->where('role_id', 33)->update(['role_id' => 3]);

        // 5. Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('users')->where('role_id', 2)->update(['role_id' => 1]);
        DB::table('users')->where('role_id', 3)->update(['role_id' => 2]);

        DB::table('roles')->truncate();
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Customer', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::enableForeignKeyConstraints();
    }
};
