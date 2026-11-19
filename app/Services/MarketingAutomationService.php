<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\MarketingAutomation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MarketingAutomationService
{
    protected FcmNotificationService $fcm;

    public function __construct(FcmNotificationService $fcm)
    {
        $this->fcm = $fcm;
    }

    /**
     * Map order status string to marketing automation key.
     */
    public function mapOrderStatusToKey(string $status): ?string
    {
        $normalized = strtolower(trim($status));
        return match ($normalized) {
            'placed', 'created', 'pending'         => 'order_placed',
            'confirmed'                            => 'order_placed',
            'shipped', 'dispatched'                => 'order_shipped',
            'out for delivery', 'out_for_delivery' => 'order_out_for_delivery',
            'delivered'                            => 'order_delivered',
            'cancelled'                            => 'order_cancelled',
            default                                => null,
        };
    }

    /**
     * Trigger real-time order lifecycle automated notification.
     */
    public function triggerOrderEvent(Order $order, string $status): bool
    {
        $key = $this->mapOrderStatusToKey($status);
        if (!$key) {
            return false;
        }

        $automation = MarketingAutomation::where('key', $key)->first();
        if (!$automation || !$automation->is_active) {
            return false;
        }

        $user = $order->user;
        $customerName = $user ? $user->name : ($order->shipping_name ?: 'Valued Customer');
        $firstName = explode(' ', trim($customerName))[0];

        $tags = [
            '{customer_name}' => $firstName,
            '{order_id}'      => $order->order_number ?: $order->id,
            '{order_total}'   => number_format((float) $order->total_amount, 2),
        ];

        $message = $automation->formatMessage('default', $tags);

        $payload = [
            'type'        => 'order_update',
            'order_id'    => (string) $order->id,
            'status'      => $status,
            'screen'      => 'order_detail',
        ];

        $sent = false;
        if ($user) {
            $sentCount = $this->fcm->sendToUser($user, $message['title'], $message['body'], $payload);
            $sent = $sentCount > 0;
        }

        $automation->increment('total_sent');
        $automation->update(['last_triggered_at' => now()]);

        return $sent;
    }

    /**
     * Run daily automated birthday celebratory greeting.
     */
    public function runBirthdayAutomations(): int
    {
        $automation = MarketingAutomation::where('key', 'birthday_reward')->first();
        if (!$automation || !$automation->is_active) {
            return 0;
        }

        $todayMonthDay = now()->format('m-d');
        $currentYear = now()->year;

        // Find users whose date of birth matches today
        $users = User::whereHas('profile', function ($q) use ($todayMonthDay) {
            $q->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') = ?", [$todayMonthDay]);
        })->get();

        $processedCount = 0;

        foreach ($users as $user) {
            // Guard: 1 greeting per user per year
            $cacheKey = "bday_greeted_{$user->id}_{$currentYear}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            $firstName = explode(' ', trim($user->name))[0];

            $tags = [
                '{customer_name}' => $firstName,
            ];

            $message = $automation->formatMessage('default', $tags);

            $this->fcm->sendToUser($user, $message['title'], $message['body'], [
                'type'   => 'birthday_greeting',
                'screen' => 'home',
            ]);

            Cache::put($cacheKey, true, now()->endOfYear());
            $processedCount++;
        }

        if ($processedCount > 0) {
            $automation->increment('total_sent', $processedCount);
            $automation->update(['last_triggered_at' => now()]);
        }

        return $processedCount;
    }

    /**
     * Run abandoned cart recovery notifications for carts left untouched.
     */
    public function runAbandonedCartAutomations(): int
    {
        $automation = MarketingAutomation::where('key', 'abandoned_cart')->first();
        if (!$automation || !$automation->is_active) {
            return 0;
        }

        $delayMinutes = $automation->delay_minutes ?: 120;
        $windowStart = now()->subMinutes($delayMinutes + 120);
        $windowEnd = now()->subMinutes($delayMinutes);

        $abandonedCarts = Cart::whereNotNull('user_id')
            ->whereHas('items')
            ->whereBetween('updated_at', [$windowStart, $windowEnd])
            ->with(['user', 'items.product'])
            ->get();

        $processedCount = 0;

        foreach ($abandonedCarts as $cart) {
            $user = $cart->user;
            if (!$user) {
                continue;
            }

            // Skip if user ordered in the last 24 hours
            $hasRecentOrder = Order::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if ($hasRecentOrder) {
                continue;
            }

            $firstName = explode(' ', trim($user->name))[0];

            $tags = [
                '{customer_name}' => $firstName,
            ];

            $message = $automation->formatMessage('default', $tags);

            $this->fcm->sendToUser($user, $message['title'], $message['body'], [
                'type'   => 'abandoned_cart',
                'screen' => 'cart',
            ]);

            $processedCount++;
        }

        if ($processedCount > 0) {
            $automation->increment('total_sent', $processedCount);
            $automation->update(['last_triggered_at' => now()]);
        }

        return $processedCount;
    }

    /**
     * Run customer win-back automation for inactive users (30+ days without purchase).
     */
    public function runCustomerWinbackAutomations(): int
    {
        $automation = MarketingAutomation::where('key', 'customer_winback')->first();
        if (!$automation || !$automation->is_active) {
            return 0;
        }

        $inactiveUsers = User::whereDoesntHave('orders', function ($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        })
        ->whereHas('devices', function ($q) {
            $q->where('is_active', true);
        })
        ->take(100)
        ->get();

        $processedCount = 0;

        foreach ($inactiveUsers as $user) {
            $firstName = explode(' ', trim($user->name))[0];

            $tags = [
                '{customer_name}' => $firstName,
            ];

            $message = $automation->formatMessage('default', $tags);

            $this->fcm->sendToUser($user, $message['title'], $message['body'], [
                'type'   => 'customer_winback',
                'screen' => 'shop',
            ]);

            $processedCount++;
        }

        if ($processedCount > 0) {
            $automation->increment('total_sent', $processedCount);
            $automation->update(['last_triggered_at' => now()]);
        }

        return $processedCount;
    }

    /**
     * Simulate a preview for an automation rule.
     */
    public function simulate(MarketingAutomation $automation): array
    {
        $sampleTags = [
            '{customer_name}' => 'Srikant',
            '{order_id}'      => 'SC-84920',
            '{order_total}'   => '1,499.00',
        ];

        return $automation->formatMessage('default', $sampleTags);
    }
}
