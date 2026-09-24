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
        // 1. Drop obsolete password_reset_tokens table if it exists
        if (Schema::hasTable('password_reset_tokens')) {
            Schema::dropIfExists('password_reset_tokens');
        }

        // 2. Drop unused google_id column from users table if present
        if (Schema::hasColumn('users', 'google_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('google_id');
            });
        }

        // 3. Drop unused avatar_url column from customer_profiles table if present
        if (Schema::hasColumn('customer_profiles', 'avatar_url')) {
            Schema::table('customer_profiles', function (Blueprint $table) {
                $table->dropColumn('avatar_url');
            });
        }

        // 4. Drop unused taxable_amount column from order_items table if present
        if (Schema::hasColumn('order_items', 'taxable_amount')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('taxable_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasColumn('users', 'google_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('google_id')->nullable()->unique();
            });
        }

        if (!Schema::hasColumn('customer_profiles', 'avatar_url')) {
            Schema::table('customer_profiles', function (Blueprint $table) {
                $table->string('avatar_url')->nullable();
            });
        }

        if (!Schema::hasColumn('order_items', 'taxable_amount')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('taxable_amount', 10, 2)->nullable();
            });
        }
    }
};
