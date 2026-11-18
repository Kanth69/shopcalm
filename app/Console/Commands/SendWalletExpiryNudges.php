<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\UserDevice;
use App\Models\Wallet;
use App\Services\FcmNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendWalletExpiryNudges extends Command
{
    protected $signature = 'shopcalm:send-wallet-nudges {--force : Bypass the 10:00 PM - 8:30 AM DND quiet hours window}';
    protected $description = 'Send targeted push alerts to customers with unused wallet reward credits to drive immediate high conversions';

    public function handle(FcmNotificationService $fcmService): int
    {
        $this->info('Starting Wallet Reward Balance Expiry Nudge Engine...');

        // 1. DND (Do Not Disturb) Night Quiet Hours Guard (10:00 PM - 8:30 AM IST)
        $now = Carbon::now();
        $hour = (int) $now->format('H');
        $minute = (int) $now->format('i');
        $currentMins = ($hour * 60) + $minute;
        $dndStart = 22 * 60;        // 10:00 PM
        $dndEnd   = 8 * 60 + 30;    // 8:30 AM

        $isNight = ($currentMins >= $dndStart || $currentMins < $dndEnd);

        if ($isNight && !$this->option('force')) {
            $this->warn("🌙 Quiet hours active (10:00 PM - 8:30 AM). Skipping wallet nudges to maintain high user trust. Use --force to override.");
            return Command::SUCCESS;
        }

        // 2. Query users with active positive wallet balance (>= ₹50) and active devices
        $wallets = Wallet::where('status', 'active')
            ->where('balance', '>=', 50)
            ->with(['user.profile', 'user.addresses', 'user.devices' => function ($q) {
                $q->where('is_active', true)->whereNotNull('fcm_token');
            }])
            ->get();

        $sentCount = 0;

        foreach ($wallets as $wallet) {
            $user = $wallet->user;
            if (!$user || $user->devices->isEmpty()) {
                continue;
            }

            // Exclude users who already ordered in the last 7 days (already active buyers)
            $hasRecentOrder = $user->orders()->where('created_at', '>=', now()->subDays(7))->exists();
            if ($hasRecentOrder) {
                continue;
            }

            $balance = number_format($wallet->balance, 0);

            // Determine language preference (Explicit profile preference or Regional Pincode fallback)
            $prefLang = optional($user->profile)->preferred_language ?? 'en';
            if ($prefLang === 'en') {
                $recentAddress = $user->addresses()->latest()->first();
                if ($recentAddress) {
                    $state = strtolower($recentAddress->state ?? '');
                    $zip = (string) ($recentAddress->zip ?? '');
                    if (str_contains($state, 'andhra') || str_contains($state, 'telangana') || str_starts_with($zip, '50') || str_starts_with($zip, '51') || str_starts_with($zip, '52') || str_starts_with($zip, '53')) {
                        $prefLang = 'te';
                    } elseif (str_contains($state, 'delhi') || str_contains($state, 'uttar') || str_contains($state, 'rajasthan') || str_contains($state, 'bihar')) {
                        $prefLang = 'hi';
                    }
                }
            }

            // Formulate localized copy
            if ($prefLang === 'te') {
                $title = "💰 మీ ShopCalm క్యాష్ ₹{$balance} ఖాతాలో ఉంది!";
                $body  = "మీ వాలెట్ రివార్డ్ క్రెడిట్స్ గడువు ముగియకముందే మీ తదుపరి ఆర్డర్‌పై ఉపయోగించండి. ఇప్పుడే షాపింగ్ చేయండి! 🛍️";
            } elseif ($prefLang === 'hi') {
                $title = "💰 आपके वॉलेट में ₹{$balance} ShopCalm कैश बचा है!";
                $body  = "अपने वॉलेट रिवॉर्ड्स को एक्सपायर होने से पहले इस्तेमाल करें। आज ही पसंदीदा सामान ऑर्डर करें!";
            } else {
                $title = "💰 You have ₹{$balance} ShopCalm Cash waiting!";
                $body  = "Don't let your wallet reward credits expire! Apply your ₹{$balance} balance on your next order today.";
            }

            $payload = [
                'type'          => 'broadcast',
                'screen'        => 'offers',
                'campaign_type' => 'wallet_expiry',
            ];

            $userSent = false;
            foreach ($user->devices as $device) {
                if ($fcmService->sendToToken($device->fcm_token, $title, $body, $payload)) {
                    $userSent = true;
                }
            }

            if ($userSent) {
                $sentCount++;
            }
        }

        if ($sentCount > 0) {
            AppNotification::create([
                'title'            => '💰 Wallet Balance Expiry Reminders',
                'body'             => "Automated wallet rewards expiry nudge broadcast to {$sentCount} eligible customer(s).",
                'destination_type' => 'offers',
                'campaign_type'    => 'wallet_expiry',
                'target_gender'    => 'all',
                'target_age_group' => 'all',
                'target_activity'  => 'all',
                'target_language'  => 'all',
                'recipient_count'  => $sentCount,
                'status'           => 'Approved',
                'approved_at'      => now(),
            ]);
        }

        $this->info("Completed. Wallet balance expiry nudges sent to {$sentCount} active user(s).");
        return Command::SUCCESS;
    }
}
