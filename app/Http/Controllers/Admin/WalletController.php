<?php

namespace App\Http\Controllers\Admin;

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

    public function index(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to Wallets Management.');
        }

        $query = Wallet::with(['user', 'referrer.wallet', 'transactions.order'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('referral_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('mobile_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $wallets = $query->paginate(15)->withQueryString();

        // High-level Financial KPIs
        $totalLiability = (float) Wallet::sum('balance');
        $totalDistributed = (float) WalletTransaction::where('type', 'CREDIT')->sum('amount');
        $totalRedeemed = (float) WalletTransaction::where('source', 'CHECKOUT_REDEEM')->sum('amount');
        $totalWalletsCount = Wallet::count();

        // Dynamic Referral Rules
        $rules = [
            'signup_bonus'        => WalletService::getSignupBonus(),
            'first_order_reward'  => WalletService::getFirstOrderReward(),
            'second_order_reward' => WalletService::getSecondOrderReward(),
            'third_order_reward'  => WalletService::getThirdOrderReward(),
            'min_order_spend'     => WalletService::getMinOrderSpend(),
            'referral_enabled'    => WalletService::isReferralProgramActive(),
            'signup_bonus_enabled'=> WalletService::isSignupBonusActive(),
        ];

        return view('admin.wallets.index', compact(
            'wallets',
            'totalLiability',
            'totalDistributed',
            'totalRedeemed',
            'totalWalletsCount',
            'rules'
        ));
    }

    /**
     * Update Dynamic Multi-Tier Referral & Wallet Program Rules
     */
    public function updateRules(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Super Admin privileges required to edit referral program rules.');
        }

        $validated = $request->validate([
            'wallet_signup_bonus'           => 'required|numeric|min:0',
            'wallet_first_order_reward'     => 'required|numeric|min:0',
            'wallet_second_order_reward'    => 'required|numeric|min:0',
            'wallet_third_order_reward'     => 'required|numeric|min:0',
            'wallet_min_order_reward_spend' => 'nullable|numeric|min:0',
            'wallet_referral_enabled'       => 'nullable|boolean',
            'wallet_signup_bonus_enabled'   => 'nullable|boolean',
        ]);

        \App\Models\Setting::set('wallet_signup_bonus', (float) $validated['wallet_signup_bonus']);
        \App\Models\Setting::set('wallet_first_order_reward', (float) $validated['wallet_first_order_reward']);
        \App\Models\Setting::set('wallet_second_order_reward', (float) $validated['wallet_second_order_reward']);
        \App\Models\Setting::set('wallet_third_order_reward', (float) $validated['wallet_third_order_reward']);
        \App\Models\Setting::set('wallet_min_order_reward_spend', (float) ($validated['wallet_min_order_reward_spend'] ?? 0));
        \App\Models\Setting::set('wallet_referral_enabled', $request->has('wallet_referral_enabled') ? '1' : '0');
        \App\Models\Setting::set('wallet_signup_bonus_enabled', $request->has('wallet_signup_bonus_enabled') ? '1' : '0');

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Rules Updated',
            'message' => 'Wallet & Referral Program rules saved successfully!',
        ]);
    }

    public function adjust(Request $request, Wallet $wallet)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Super Admin privileges required to adjust wallet balance.');
        }

        $validated = $request->validate([
            'type'   => 'required|in:CREDIT,DEBIT',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $this->walletService->adminAdjust(
                $wallet->user,
                (float) $validated['amount'],
                $validated['type'],
                $validated['reason']
            );

            return back()->with('toast', [
                'type'    => 'success',
                'title'   => 'Wallet Adjusted',
                'message' => "Successfully {$validated['type']}ED ₹" . number_format($validated['amount'], 2) . " for {$wallet->user->name}.",
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'Adjustment Failed',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function toggleStatus(Wallet $wallet)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Super Admin privileges required to freeze/unfreeze wallets.');
        }

        $newStatus = ($wallet->status === 'active') ? 'frozen' : 'active';
        $wallet->update(['status' => $newStatus]);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Status Updated',
            'message' => "Wallet for {$wallet->user->name} is now " . strtoupper($newStatus) . ".",
        ]);
    }
}
