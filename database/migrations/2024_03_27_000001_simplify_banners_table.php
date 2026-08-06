<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // Drop foreign key if it exists
            if (Schema::hasColumn('banners', 'offer_id')) {
                $table->dropForeign(['offer_id']);
            }

            // Drop complex columns to simplify the module
            $columnsToDrop = [
                'description',
                'secondary_button_text',
                'secondary_button_link',
                'badge_text',
                'badge_color',
                'banner_type',
                'offer_id',
                'background_color',
                'text_alignment',
                'start_date',
                'end_date',
                'theme'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('banners', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        // Add them back if rolled back
        Schema::table('banners', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_button_link')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('badge_color')->default('primary');
            $table->string('banner_type')->default('HERO');
            $table->foreignId('offer_id')->nullable()->constrained('offers')->nullOnDelete();
            $table->string('background_color')->nullable();
            $table->string('text_alignment')->default('left');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('theme')->default('light');
        });
    }
};
