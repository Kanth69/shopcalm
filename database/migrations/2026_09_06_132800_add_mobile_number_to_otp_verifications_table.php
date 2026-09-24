<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('mobile_number')->nullable()->after('email');
            $table->index(['mobile_number', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::table('otp_verifications', function (Blueprint $table) {
            $table->dropIndex(['mobile_number', 'purpose']);
            $table->dropColumn('mobile_number');
            $table->string('email')->nullable(false)->change();
        });
    }
};
