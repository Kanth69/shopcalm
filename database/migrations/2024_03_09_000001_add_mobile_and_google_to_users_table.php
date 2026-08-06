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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mobile_number')) {
                $table->string('mobile_number', 15)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('password');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('google_id');
            }

            // Change email to nullable
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Identify users with NULL emails and assign a placeholder to prevent truncation error
        // Only run if the table still exists
        if (Schema::hasTable('users')) {
            DB::table('users')->whereNull('email')->update([
                'email' => DB::raw("CONCAT('placeholder_', id, '@shopcalm.com')")
            ]);

            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'mobile_number')) {
                    $table->dropColumn('mobile_number');
                }
                if (Schema::hasColumn('users', 'google_id')) {
                    $table->dropColumn('google_id');
                }
                if (Schema::hasColumn('users', 'avatar')) {
                    $table->dropColumn('avatar');
                }

                $table->string('email')->nullable(false)->change();
            });
        }
    }
};
