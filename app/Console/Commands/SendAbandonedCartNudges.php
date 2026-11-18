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
    protected $signature = 'shopcalm:send-cart-nudges {--force : Bypass the 10:00 PM - 8:30 AM DND quiet hours window}';
    protected $description = 'Send strategic abandoned cart pushes following the enterprise Day 1 (24h) & Day 5 (120h) cadence with DND guard';

    public function handle(FcmNotificationService $fcmService): int
    {
        $this->info('Starting Enterprise Abandoned Cart Push Nudge Engine...');

        // 1. DND (Do Not Disturb) Night Quiet Hours Guard (10:00 PM - 8:30 AM IST)
        $now = Carbon::now();
        $hour = (int) $now->format('H');
        $minute = (int) $now->format('i');
        $currentMins = ($hour * 60) + $minute;
        $dndStart = 22 * 60;        // 10:00 PM
        $dndEnd   = 8 * 60 + 30;    // 8:30 AM

        $isNight = ($currentMins >= $dndStart || $currentMins < $dndEnd);

        if ($isNight && !$this->option('force')) {
            $this->warn("🌙 Quiet hours active (10:00 PM - 8:30 AM). Skipping cart nudges to maintain high user trust. Use --force to override.");
            return Command::SUCCESS;
        }

        $sentCount = 0;

        // ─────────────────────────────────────────────────────────────
        // 2. STAGE 1: Day 1 Gentle Follow-up (24 hours to 48 hours ago)
        // ─────────────────────────────────────────────────────────────
        $day1Start = Carbon::now()->subHours(24);
        $day1End   = Carbon::now()->subHours(48);

        $stage1Carts = Cart::whereNotNull('user_id')
            ->where('updated_at', '<=', $day1Start)
            ->where('updated_at', '>=', $day1End)
            ->whereHas('items', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->with(['user', 'items'])
            ->get();

        foreach ($stage1Carts as $cart) {
            $user = $cart->user;
            if (!$user) {
                continue;
            }

            // Anti-spam check: Did we already nudge this user in the last 24 hours?
            $alreadyNudged = UserDevice::where('user_id', $user->id)
                ->where('last_cart_nudge_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($alreadyNudged) {
                continue;
            }

            $itemCount = $cart->items->sum('quantity');
            if ($itemCount <= 0) {
                continue;
            }

            $sent = $fcmService->sendCartNudge($user, $itemCount);
            if ($sent) {
                $sentCount++;
                $this->line(" [Day 1 Nudge] User #{$user->id} ({$user->name}) - {$itemCount} items.");
            }
        }

        // ─────────────────────────────────────────────────────────────
        // 3. STAGE 2: Day 5 Final Urgency (120 hours to 144 hours ago)
        // ─────────────────────────────────────────────────────────────
        $day5Start = Carbon::now()->subHours(120); // 5 days
        $day5End   = Carbon::now()->subHours(144); // 6 days

        $stage2Carts = Cart::whereNotNull('user_id')
            ->where('updated_at', '<=', $day5Start)
            ->where('updated_at', '>=', $day5End)
            ->whereHas('items', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->with(['user', 'items'])
            ->get();

        foreach ($stage2Carts as $cart) {
            $user = $cart->user;
            if (!$user) {
                continue;
            }

            // Must have had at least 72 hours since last nudge
            $recentlyNudged = UserDevice::where('user_id', $user->id)
                ->where('last_cart_nudge_at', '>=', Carbon::now()->subHours(72))
                ->exists();

            if ($recentlyNudged) {
                continue;
            }

            $itemCount = $cart->items->sum('quantity');
            if ($itemCount <= 0) {
                continue;
            }

            $sent = $fcmService->sendCartNudge($user, $itemCount);
            if ($sent) {
                $sentCount++;
                $this->line(" [Day 5 Final Nudge] User #{$user->id} ({$user->name}) - {$itemCount} items.");
            }
        }

        $this->info("Abandoned Cart Nudge cycle completed. {$sentCount} notifications sent.");
        return Command::SUCCESS;
    }
}
