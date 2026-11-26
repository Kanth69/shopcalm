<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exists = DB::table('marketing_automations')->where('key', 'back_in_stock')->exists();
        if (!$exists) {
            DB::table('marketing_automations')->insert([
                'key'                => 'back_in_stock',
                'name'               => 'Back in Stock Alert',
                'category'           => 'growth',
                'trigger_event'      => 'product.restocked',
                'is_active'          => true,
                'delay_minutes'      => 0,
                'reward_type'        => 'none',
                'reward_amount'      => null,
                'reward_expiry_days' => null,
                'min_order_value'    => null,
                'title_template'     => json_encode([
                    'en' => '{product_name} is Back in Stock! 🔥',
                    'te' => '{product_name} మళ్లీ స్టాక్‌లోకి వచ్చింది! 🔥',
                    'hi' => '{product_name} वापस स्टॉक में आ गया है! 🔥',
                ]),
                'body_template'      => json_encode([
                    'en' => 'Great news {customer_name}! The item you were waiting for is back on shelves. Tap to order before stock runs out!',
                    'te' => 'శుభవార్త {customer_name}! మీరు వేచి చూస్తున్న వస్తువు మళ్లీ అందుబాటులోకి వచ్చింది. స్టాక్ ముగిసేలోపు ఇప్పుడే ఆర్డర్ చేయండి!',
                    'hi' => 'खुशखबरी {customer_name}! आपका पसंदीदा आइटम वापस आ गया है। स्टॉक खत्म होने से पहले अभी खरीदें!',
                ]),
                'supported_tags'     => json_encode(['{customer_name}', '{product_name}', '{product_price}']),
                'total_sent'         => 0,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('marketing_automations')->where('key', 'back_in_stock')->delete();
    }
};
