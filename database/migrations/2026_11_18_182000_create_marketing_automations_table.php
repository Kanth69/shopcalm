<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketing_automations', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('name', 120);
            $table->string('category', 40)->default('order'); // 'order', 'growth'
            $table->string('trigger_event', 80);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('delay_minutes')->default(0);

            // Reward Configuration (Zero hardcoding)
            $table->enum('reward_type', ['none', 'wallet_cash', 'coupon'])->default('none');
            $table->decimal('reward_amount', 10, 2)->nullable();
            $table->unsignedInteger('reward_expiry_days')->nullable();
            $table->decimal('min_order_value', 10, 2)->nullable();

            // Multi-lingual Templates (Telugu, Hindi, English)
            $table->json('title_template');
            $table->json('body_template');
            $table->json('supported_tags');

            // Metrics & Audit
            $table->unsignedBigInteger('total_sent')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });

        // Seed Default Industry-Standard Static Automations (Fully editable via UI)
        $defaults = [
            [
                'key' => 'order_placed',
                'name' => 'Order Placed Confirmation',
                'category' => 'order',
                'trigger_event' => 'order.created',
                'is_active' => true,
                'delay_minutes' => 0,
                'reward_type' => 'none',
                'reward_amount' => null,
                'reward_expiry_days' => null,
                'min_order_value' => null,
                'title_template' => json_encode([
                    'en' => 'Order Confirmed! 🎉',
                    'te' => 'ఆర్డర్ ఖరారైంది! 🎉',
                    'hi' => 'ऑर्डर कन्फर्म हो गया! 🎉',
                ]),
                'body_template' => json_encode([
                    'en' => 'Thank you {customer_name}! Your order #{order_id} for ₹{order_total} has been placed successfully. Tap to track.',
                    'te' => 'ధన్యవాదాలు {customer_name}! మీ ఆర్డర్ #{order_id} (₹{order_total}) విజయవంతంగా నమోదైంది. ట్రాక్ చేయడానికి ట్యాప్ చేయండి.',
                    'hi' => 'धन्यवाद {customer_name}! आपका ऑर्डर #{order_id} (₹{order_total}) सफलतापूर्वक प्लेस हो गया है। ट्रैक करने के लिए टैप करें।',
                ]),
                'supported_tags' => json_encode(['{customer_name}', '{order_id}', '{order_total}']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'order_shipped',
                'name' => 'Order Dispatched / Shipped',
                'category' => 'order',
                'trigger_event' => 'order.status:shipped',
                'is_active' => true,
                'delay_minutes' => 0,
                'reward_type' => 'none',
                'reward_amount' => null,
                'reward_expiry_days' => null,
                'min_order_value' => null,
                'title_template' => json_encode([
                    'en' => 'Package Dispatched! 🚚',
                    'te' => 'ఆర్డర్ బయలుదేరింది! 🚚',
                    'hi' => 'पैकेज निकल चुका है! 🚚',
                ]),
                'body_template' => json_encode([
                    'en' => 'Great news {customer_name}! Order #{order_id} has been packed and handed over to delivery. It is on the way!',
                    'te' => 'శుభవార్త {customer_name}! మీ ఆర్డర్ #{order_id} డెలివరీకి బయలుదేరింది. త్వరలోనే మీకు అందుతుంది!',
                    'hi' => 'बड़ी खुशखबरी {customer_name}! आपका ऑर्डर #{order_id} डिलीवरी के लिए निकल चुका है और रास्ते में है!',
                ]),
                'supported_tags' => json_encode(['{customer_name}', '{order_id}']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'order_out_for_delivery',
                'name' => 'Out for Delivery Today',
                'category' => 'order',
                'trigger_event' => 'order.status:out for delivery',
                'is_active' => true,
                'delay_minutes' => 0,
                'reward_type' => 'none',
                'reward_amount' => null,
                'reward_expiry_days' => null,
                'min_order_value' => null,
                'title_template' => json_encode([
                    'en' => 'Arriving Today! 📦',
                    'te' => 'ఈరోజే డెలివరీ! 📦',
                    'hi' => 'आज डिलीवरी होगी! 📦',
                ]),
                'body_template' => json_encode([
                    'en' => 'Get ready {customer_name}! Your package #{order_id} is out for delivery with our delivery partner today.',
                    'te' => 'సిద్ధంగా ఉండండి {customer_name}! మీ ఆర్డర్ #{order_id} ఈరోజే మా డెలివరీ భాగస్వామి ద్వారా మీకు చేరనుంది.',
                    'hi' => 'तैयार रहें {customer_name}! आपका पैकेज #{order_id} आज आपके पते पर डिलीवर होने वाला है।',
                ]),
                'supported_tags' => json_encode(['{customer_name}', '{order_id}']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'order_delivered',
                'name' => 'Order Delivered & Feedback',
                'category' => 'order',
                'trigger_event' => 'order.status:delivered',
                'is_active' => true,
                'delay_minutes' => 15,
                'reward_type' => 'none',
                'reward_amount' => null,
                'reward_expiry_days' => null,
                'min_order_value' => null,
                'title_template' => json_encode([
                    'en' => 'Package Delivered! 🎁',
                    'te' => 'ఆర్డర్ అందింది! 🎁',
                    'hi' => 'ऑर्डर डिलीवर हो गया! 🎁',
                ]),
                'body_template' => json_encode([
                    'en' => 'Your order #{order_id} has been safely delivered. We hope you love your products! Tap to leave a quick rating.',
                    'te' => 'మీ ఆర్డర్ #{order_id} సురక్షితంగా డెలివరీ చేయబడింది. మీకు నచ్చిందని ఆశిస్తున్నాం! రేటింగ్ ఇవ్వడానికి ట్యాప్ చేయండి.',
                    'hi' => 'आपका ऑर्डर #{order_id} सुरक्षित डिलीवर हो गया है। उम्मीद है आपको पसंद आया होगा! रेटिंग देने के लिए टैप करें।',
                ]),
                'supported_tags' => json_encode(['{customer_name}', '{order_id}']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'birthday_reward',
                'name' => 'Birthday Special Greetings',
                'category' => 'growth',
                'trigger_event' => 'schedule.daily_birthday',
                'is_active' => true,
                'delay_minutes' => 0,
                'reward_type' => 'none',
                'reward_amount' => null,
                'reward_expiry_days' => null,
                'min_order_value' => null,
                'title_template' => json_encode([
                    'en' => 'Happy Birthday, {customer_name}! 🎂🎉',
                    'te' => 'పుట్టినరోజు శుభాకాంక్షలు, {customer_name}! 🎂🎉',
                    'hi' => 'जन्मदिन की हार्दिक शुभकामनाएं, {customer_name}! 🎂🎉',
                ]),
                'body_template' => json_encode([
                    'en' => 'Wishing you a wonderful year filled with joy, success, and style from all of us at ShopCalm!',
                    'te' => 'మీ జీవితం ఆనందం, ఆరోగ్యాలతో నిండాలని ShopCalm కుటుంబం తరపున మనస్ఫూర్తిగా కోరుకుంటున్నాము!',
                    'hi' => 'ShopCalm परिवार की ओर से आपके सुख, समृद्धि और अच्छे स्वास्थ्य की मंगल कामनाएं!',
                ]),
                'supported_tags' => json_encode(['{customer_name}']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'abandoned_cart',
                'name' => 'Abandoned Cart 2-Hour Recovery',
                'category' => 'growth',
                'trigger_event' => 'schedule.abandoned_cart',
                'is_active' => true,
                'delay_minutes' => 120,
                'reward_type' => 'none',
                'reward_amount' => null,
                'reward_expiry_days' => null,
                'min_order_value' => null,
                'title_template' => json_encode([
                    'en' => 'You left items in your cart! 🛒',
                    'te' => 'మీ కార్ట్‌లో వస్తువులు మిగిలిపోయాయి! 🛒',
                    'hi' => 'आपकी कार्ट में आइटम बाकी हैं! 🛒',
                ]),
                'body_template' => json_encode([
                    'en' => 'Hi {customer_name}, high demand items in your cart are selling fast! Complete your order now before stock runs out.',
                    'te' => 'నమస్తే {customer_name}, మీ కార్ట్ లోని వస్తువులకు డిమాండ్ ఎక్కువ ఉంది! స్టాక్ ముగిసేలోపు ఇప్పుడే ఆర్డర్ పూర్తి చేయండి.',
                    'hi' => 'नमस्ते {customer_name}, आपकी कार्ट के आइटम तेजी से बिक रहे हैं! स्टॉक खत्म होने से पहले अभी चेकआउट पूरा करें।',
                ]),
                'supported_tags' => json_encode(['{customer_name}']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'customer_winback',
                'name' => '30-Day Inactive Customer Win-back',
                'category' => 'growth',
                'trigger_event' => 'schedule.customer_winback',
                'is_active' => true,
                'delay_minutes' => 0,
                'reward_type' => 'none',
                'reward_amount' => null,
                'reward_expiry_days' => null,
                'min_order_value' => null,
                'title_template' => json_encode([
                    'en' => 'We miss you, {customer_name}! 🛍️',
                    'te' => 'మిమ్మల్ని మిస్ అవుతున్నాం {customer_name}! 🛍️',
                    'hi' => 'हम आपको याद कर रहे हैं {customer_name}! 🛍️',
                ]),
                'body_template' => json_encode([
                    'en' => 'It has been a while! Discover our newest handpicked arrivals and exclusive discounts curated just for you.',
                    'te' => 'చాలా రోజులయ్యింది! మీ కోసం ప్రత్యేకంగా ఎంపిక చేసిన కొత్త కలెక్షన్స్ మరియు డిస్కౌంట్లను ఇప్పుడే చూడండి.',
                    'hi' => 'काफी समय हो गया! आपके लिए खास तौर पर चुने गए नए अराइवल्स और बेहतरीन डिस्काउंट्स आज ही देखें।',
                ]),
                'supported_tags' => json_encode(['{customer_name}']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('marketing_automations')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_automations');
    }
};
