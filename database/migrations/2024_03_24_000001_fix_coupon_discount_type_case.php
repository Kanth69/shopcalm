<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix any existing data that might be causing the ValueError due to casing
        DB::table('coupons')->where('discount_type', 'percentage')->update(['discount_type' => 'PERCENTAGE']);
        DB::table('coupons')->where('discount_type', 'flat')->update(['discount_type' => 'FLAT']);
    }

    public function down(): void
    {
        // Not reversible
    }
};
