<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Fetch dynamic wallet settings
        $signupBonus = WalletService::getSignupBonus();
        $firstReward = WalletService::getFirstOrderReward();
        $secondReward = WalletService::getSecondOrderReward();
        $thirdReward = WalletService::getThirdOrderReward();
        $totalPotentialReward = $firstReward + $secondReward + $thirdReward;
        $isReferralActive = WalletService::isReferralProgramActive();
        $isSignupBonusActive = WalletService::isSignupBonusActive();

        // Fetch referred friends and their delivery milestones
        $referredWallets = Wallet::where('referred_by', $user->id)
            ->with(['user.orders' => function ($q) {
                $q->where('status', 'delivered')->latest();
            }])
            ->latest()
            ->get();

        $friendsStats = $referredWallets->map(function ($friendWallet) use ($firstReward, $secondReward, $thirdReward) {
            $friendUser = $friendWallet->user;
            $deliveredOrdersCount = $friendUser ? $friendUser->orders->count() : 0;

            $totalEarnedFromFriend = 0.00;
            if ($deliveredOrdersCount >= 1) $totalEarnedFromFriend += $firstReward;
            if ($deliveredOrdersCount >= 2) $totalEarnedFromFriend += $secondReward;
            if ($deliveredOrdersCount >= 3) $totalEarnedFromFriend += $thirdReward;

            return (object) [
                'friend_name' => $friendUser?->name ?? 'Friend',
                'joined_date' => $friendWallet->created_at,
                'delivered_count' => $deliveredOrdersCount,
                'first_order_done' => $deliveredOrdersCount >= 1,
                'second_order_done' => $deliveredOrdersCount >= 2,
                'third_order_done' => $deliveredOrdersCount >= 3,
                'total_earned' => $totalEarnedFromFriend,
            ];
        });

        $totalReferralsCount = $friendsStats->count();
        $totalReferralEarnings = $friendsStats->sum('total_earned');

        // Transactions passbook history
        $transactions = $wallet->transactions()->latest()->paginate(12);

        // Shareable referral URL
        $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
        $referralUrl = route('register', ['ref' => $wallet->referral_code]);
        $whatsappShareText = urlencode("Hey! Shop on {$storeName} and get ₹" . number_format($signupBonus, 0) . " instant wallet credit on your signup! Use my referral code {$wallet->referral_code} or click here: {$referralUrl}");

        return view('customer.account.wallet', compact(
            'wallet',
            'friendsStats',
            'totalReferralsCount',
            'totalReferralEarnings',
            'transactions',
            'referralUrl',
            'whatsappShareText',
            'signupBonus',
            'firstReward',
            'secondReward',
            'thirdReward',
            'totalPotentialReward',
            'isReferralActive',
            'isSignupBonusActive'
        ));
    }
}
