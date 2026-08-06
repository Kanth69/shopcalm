<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_completion_rules', function (Blueprint $table) {
            $table->id();
            $table->string('field_key')->unique(); // e.g. gender, date_of_birth
            $table->string('display_name');
            $table->integer('weight'); // Percentage weightage
            $table->boolean('is_required')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_completion_rules');
    }
};
