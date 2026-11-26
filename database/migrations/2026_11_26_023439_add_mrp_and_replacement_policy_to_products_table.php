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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'mrp')) {
                $table->decimal('mrp', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'is_replaceable')) {
                $table->boolean('is_replaceable')->default(false)->after('status');
            }
            if (!Schema::hasColumn('products', 'replacement_window_days')) {
                $table->unsignedSmallInteger('replacement_window_days')->default(7)->after('is_replaceable');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'replacement_window_days')) {
                $table->dropColumn('replacement_window_days');
            }
            if (Schema::hasColumn('products', 'is_replaceable')) {
                $table->dropColumn('is_replaceable');
            }
            if (Schema::hasColumn('products', 'mrp')) {
                $table->dropColumn('mrp');
            }
        });
    }
};
