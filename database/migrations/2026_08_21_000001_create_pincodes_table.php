<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pincodes', function (Blueprint $table) {
            $table->id();
            $table->string('pincode', 6)->unique()->index();
            $table->string('city');
            $table->string('state');
            $table->unsignedTinyInteger('delivery_days')->default(3);
            $table->boolean('is_cod_available')->default(true);
            $table->decimal('delivery_charge', 8, 2)->default(0.00);
            $table->boolean('is_serviceable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pincodes');
    }
};
