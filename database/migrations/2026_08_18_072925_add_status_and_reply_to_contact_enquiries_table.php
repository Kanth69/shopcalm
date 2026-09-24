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
        Schema::table('contact_enquiries', function (Blueprint $table) {
            $table->string('status', 50)->default('unread')->after('is_read');
            $table->text('reply_notes')->nullable()->after('status');
            $table->foreignId('resolved_by')->nullable()->after('reply_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_enquiries', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['status', 'reply_notes', 'resolved_by', 'resolved_at']);
        });
    }
};
