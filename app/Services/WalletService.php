<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class WalletService
{
    // Default Rule Values (Fallbacks)
    const DEFAULT_SIGNUP_BONUS = 50.00;
    const DEFAULT_FIRST_ORDER_REWARD = 100.00;
    const DEFAULT_SECOND_ORDER_REWARD = 50.00;
    const DEFAULT_THIRD_ORDER_REWARD = 25.00;

    /**
     * Dynamic Rule Getters (read from Setting model with fallback defaults)
     */
    public static function getSignupBonus(): float
    {
        return (float) \App\Models\Setting::get('wallet_signup_bonus', self::DEFAULT_SIGNUP_BONUS);
    }

    public static function getFirstOrderReward(): float
    {
        return (float) \App\Models\Setting::get('wallet_first_order_reward', self::DEFAULT_FIRST_ORDER_REWARD);
    }

    public static function getSecondOrderReward(): float
    {
        return (float) \App\Models\Setting::get('wallet_second_order_reward', self::DEFAULT_SECOND_ORDER_REWARD);
    }

    public static function getThirdOrderReward(): float
    {
        return (float) \App\Models\Setting::get('wallet_third_order_reward', self::DEFAULT_THIRD_ORDER_REWARD);
    }

    public static function isReferralProgramActive(): bool
    {
        $val = \App\Models\Setting::get('wallet_referral_enabled', '1');
        return $val === true || $val === '1' || $val === 1 || $val === 'true';
    }

    public static function isSignupBonusActive(): bool
    {
        $val = \App\Models\Setting::get('wallet_signup_bonus_enabled', '1');
        return $val === true || $val === '1' || $val === 1 || $val === 'true';
    }

    public static function getMinOrderSpend(): float
    {
        return (float) \App\Models\Setting::get('wallet_min_order_reward_spend', 0.00);
    }

    /**
     * Get or automatically initialize a user's wallet with a unique referral code.
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => 0.00,
                'total_earned' => 0.00,
                'referral_code' => Wallet::generateReferralCode($user),
                'status' => 'active',
            ]
        );
    }

    /**
     * Apply referral on customer registration.
     */
    public function applySignupReferral(User $newUser, ?string $referralCode = null): ?Wallet
    {
        return DB::transaction(function () use ($newUser, $referralCode) {
            $wallet = $this->getOrCreateWallet($newUser);

            if (!$referralCode || !self::isReferralProgramActive()) {
                return $wallet;
            }

            $referralCode = strtoupper(trim($referralCode));
            $referrerWallet = Wallet::where('referral_code', $referralCode)
                ->where('user_id', '!=', $newUser->id)
                ->where('status', 'active')
                ->first();

            if ($referrerWallet) {
                // Bind referral parent
                $wallet->referred_by = $referrerWallet->user_id;
                $wallet->save();

                // Award instant Signup Welcome Bonus if enabled
                $signupBonus = self::getSignupBonus();
                if (self::isSignupBonusActive() && $signupBonus > 0) {
                    $wallet->credit(
                        $signupBonus,
                        'SIGNUP_BONUS',
                        'Welcome Bonus for joining via ' . ($referrerWallet->user?->name ?? 'friend') . "'s referral! 🎁"
                    );
                }

                Log::info("Referral Signup Applied: User #{$newUser->id} referred by User #{$referrerWallet->user_id}. ₹{$signupBonus} credited.");
            }

            return $wallet;
        });
    }

    /**
     * Hook triggered when an order is marked DELIVERED.
     * Evaluates 3-Tier repeat order referral rewards for the Referrer.
     */
    public function rewardReferrerOnDeliveredOrder(Order $order): ?WalletTransaction
    {
        if (!self::isReferralProgramActive()) {
            return null;
        }

        $minSpend = self::getMinOrderSpend();
        if ($minSpend > 0 && (float) $order->subtotal_amount < $minSpend) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            $customer = $order->user;
            if (!$customer) {
                return null;
            }

            $customerWallet = $this->getOrCreateWallet($customer);
            if (!$customerWallet->referred_by) {
                return null; // Not a referred customer
            }

            // Check if this specific order was already rewarded
            $alreadyRewarded = WalletTransaction::where('order_id', $order->id)
                ->where('source', 'REFERRAL_BONUS')
                ->exists();

            if ($alreadyRewarded) {
                return null;
            }

            // Count total delivered orders for this customer so far
            $deliveredCount = Order::where('user_id', $customer->id)
                ->where('status', 'delivered')
                ->count();

            $rewardAmount = 0.00;
            $milestoneText = '';

            if ($deliveredCount === 1) {
                $rewardAmount = self::getFirstOrderReward();
                $milestoneText = "1st Order Delivered Milestone 🎉";
            } elseif ($deliveredCount === 2) {
                $rewardAmount = self::getSecondOrderReward();
                $milestoneText = "2nd Order Delivered Milestone 🚀";
            } elseif ($deliveredCount === 3) {
                $rewardAmount = self::getThirdOrderReward();
                $milestoneText = "3rd Order Delivered Milestone 🌟";
            }

            if ($rewardAmount <= 0) {
                return null; // Beyond 3rd order or reward disabled
            }

            $referrer = User::find($customerWallet->referred_by);
            if (!$referrer) {
                return null;
            }

            $referrerWallet = $this->getOrCreateWallet($referrer);
            if ($referrerWallet->status !== 'active') {
                return null;
            }

            $description = "Referral Reward: ₹{$rewardAmount} earned for {$customer->name}'s {$milestoneText}";

            $txn = $referrerWallet->credit(
                $rewardAmount,
                'REFERRAL_BONUS',
                $description,
                $order->id
            );

            Log::info("Referral Reward Distributed: ₹{$rewardAmount} credited to User #{$referrer->id} for customer #{$customer->id}'s order #{$order->order_number} ({$milestoneText}).");

            return $txn;
        });
    }

    /**
     * Redeem wallet balance for an order during checkout.
     */
    public function redeemWalletForOrder(User $user, Order $order, float $amount): ?WalletTransaction
    {
        if ($amount <= 0) {
            return null;
        }

        $wallet = $this->getOrCreateWallet($user);

        return $wallet->debit(
            $amount,
            'CHECKOUT_REDEEM',
            "Redeemed ₹" . number_format($amount, 2) . " for Order #{$order->order_number}",
            $order->id
        );
    }

    /**
     * Super Admin manual credit or debit adjustment.
     */
    public function adminAdjust(User $user, float $amount, string $type, string $reason): WalletTransaction
    {
        $wallet = $this->getOrCreateWallet($user);

        if ($type === 'CREDIT') {
            return $wallet->credit($amount, 'ADMIN_ADJUSTMENT', "Admin Credit: {$reason}");
        } else {
            return $wallet->debit($amount, 'ADMIN_ADJUSTMENT', "Admin Debit: {$reason}");
        }
    }
}
