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
        Schema::table('pincodes', function (Blueprint $table) {
            if (!Schema::hasColumn('pincodes', 'cod_fee')) {
                $table->decimal('cod_fee', 8, 2)->nullable()->default(null)->after('delivery_charge');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pincodes', function (Blueprint $table) {
            if (Schema::hasColumn('pincodes', 'cod_fee')) {
                $table->dropColumn('cod_fee');
            }
        });
    }
};
