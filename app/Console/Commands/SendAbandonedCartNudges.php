<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\FcmNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAbandonedCartNudges extends Command
{
    protected $signature = 'shopcalm:send-cart-nudges';
    protected $description = 'Send automated push notifications to customers who left items in their cart';

    public function handle(FcmNotificationService $fcmService): int
    {
        $this->info('Starting Abandoned Cart Push Nudge engine...');

        // Find carts updated > 30 minutes ago and < 24 hours ago with items
        $thresholdTime = Carbon::now()->subMinutes(30);
        $cutoffTime    = Carbon::now()->subHours(24);

        $abandonedCarts = Cart::whereNotNull('user_id')
            ->where('updated_at', '<=', $thresholdTime)
            ->where('updated_at', '>=', $cutoffTime)
            ->whereHas('items', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->with(['user', 'items'])
            ->get();

        $sentCount = 0;

        foreach ($abandonedCarts as $cart) {
            $user = $cart->user;
            if (!$user) {
                continue;
            }

            // Anti-spam check: Did we already nudge this user in the last 24 hours?
            $recentNudge = UserDevice::where('user_id', $user->id)
                ->where('last_cart_nudge_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($recentNudge) {
                continue;
            }

            $itemCount = $cart->items->sum('quantity');
            if ($itemCount <= 0) {
                continue;
            }

            $sent = $fcmService->sendCartNudge($user, $itemCount);
            if ($sent) {
                $sentCount++;
                $this->line("Sent cart nudge to user #{$user->id} ({$user->name}) for {$itemCount} items.");
            }
        }

        $this->info("Abandoned Cart Nudge completed. {$sentCount} notifications sent.");
        return Command::SUCCESS;
    }
}
