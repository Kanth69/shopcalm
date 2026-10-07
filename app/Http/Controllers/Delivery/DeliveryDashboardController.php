<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class DeliveryDashboardController extends Controller
{
    /**
     * Show Delivery Partner Login Screen.
     */
    public function login(): View|RedirectResponse
    {
        if (Auth::guard('delivery_partner')->check()) {
            return redirect()->route('delivery.dashboard');
        }

        return view('delivery.auth.login');
    }

    /**
     * Authenticate Delivery Partner.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Support logging in via email or mobile number
        $loginField = filter_var($credentials['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile_number';
        $authAttempt = [
            $loginField => $credentials['email'],
            'password'  => $credentials['password'],
        ];

        if (Auth::guard('delivery_partner')->attempt($authAttempt, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::guard('delivery_partner')->user();

            if (!$user->canAccessDeliveryPortal()) {
                Auth::guard('delivery_partner')->logout();
                return back()->withErrors(['email' => 'Unauthorized account. Only registered Delivery Partners can access this portal.']);
            }

            return redirect()->intended(route('delivery.dashboard'))->with('toast', [
                'type'    => 'success',
                'title'   => 'Welcome Back!',
                'message' => "Logged in as {$user->name} (Delivery Partner).",
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our delivery records.',
        ])->onlyInput('email');
    }

    /**
     * Logout Delivery Partner.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('delivery_partner')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('delivery.login')->with('toast', [
            'type'    => 'info',
            'title'   => 'Signed Out',
            'message' => 'You have safely signed out from the Delivery Portal.',
        ]);
    }

    /**
     * Delivery Partner Mobile Dashboard / Active Run-Sheet.
     */
    public function index(Request $request): View
    {
        $rider = Auth::guard('delivery_partner')->user();

        // Active orders assigned to this rider
        $activeOrders = Order::with(['items.product', 'fulfillment'])
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id))
            ->whereIn('status', ['packed', 'shipped', 'out for delivery', 'processing'])
            ->latest('updated_at')
            ->get();

        // 1. Ready to Deliver (Actionable right now with no active reported issues)
        $readyOrders = $activeOrders->filter(function ($order) {
            return !($order->fulfillment?->hasActiveDeliveryIssue());
        });

        // 2. On Hold / Reported Issues (Warehouse currently reviewing/resolving)
        $onHoldOrders = $activeOrders->filter(function ($order) {
            return (bool) ($order->fulfillment?->hasActiveDeliveryIssue());
        });

        // Today's completed orders
        $deliveredTodayCount = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereDate('delivered_at', today()))
            ->where('status', 'delivered')
            ->count();

        // COD cash collected in hand that needs to be deposited at warehouse
        $cashInHandPending = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
            ->where('status', 'delivered')
            ->where('payment_method', 'cod')
            ->sum('total_amount');

        // COD cash collected today
        $codCollectedToday = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereDate('delivered_at', today()))
            ->where('status', 'delivered')
            ->where('payment_method', 'cod')
            ->sum('total_amount');

        $pendingCodAmount = $activeOrders->where('payment_method', 'cod')->sum('total_amount');

        return view('delivery.dashboard', compact(
            'rider',
            'activeOrders',
            'readyOrders',
            'onHoldOrders',
            'deliveredTodayCount',
            'codCollectedToday',
            'pendingCodAmount',
            'cashInHandPending'
        ));
    }

    /**
     * Cash Settlements History for Delivery Partner.
     */
    public function settlements(): View
    {
        $rider = Auth::guard('delivery_partner')->user();

        $settlements = \App\Models\CashSettlement::where('rider_id', $rider->id)
            ->with(['receivedBy', 'fulfillments.order'])
            ->latest()
            ->paginate(15);

        $cashInHandPending = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
            ->where('status', 'delivered')
            ->where('payment_method', 'cod')
            ->sum('total_amount');

        $totalDeposited = \App\Models\CashSettlement::where('rider_id', $rider->id)->sum('total_amount');

        return view('delivery.settlements', compact('rider', 'settlements', 'cashInHandPending', 'totalDeposited'));
    }

    /**
     * Single Order Details for Delivery Partner.
     */
    public function show(Order $order): View|RedirectResponse
    {
        $rider = Auth::guard('delivery_partner')->user();

        // Ensure rider owns this delivery (or Super Admin)
        if ($order->rider_id !== $rider->id && !$rider->isSuperAdmin()) {
            abort(403, 'Unauthorized. This delivery is not assigned to you.');
        }

        $order->load(['items.product', 'fulfillment', 'statusHistories']);

        return view('delivery.orders.show', compact('order', 'rider'));
    }

    /**
     * Real-time Payment Status Checker for Doorstep UPI.
     */
    public function checkPayment(Order $order): JsonResponse
    {
        $rider = Auth::guard('delivery_partner')->user();
        if ($order->rider_id !== $rider->id && !$rider->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // If order is already marked as paid locally
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success'          => true,
                'is_paid'          => true,
                'payment_status'   => 'paid',
                'payment_method'   => $order->payment_method,
                'total_amount'     => (float) $order->total_amount,
                'formatted_amount' => '₹' . number_format($order->total_amount, 2),
            ]);
        }

        // Real-time check via Razorpay Payment Gateway
        try {
            $razorpay = app(\App\Services\RazorpayService::class);
            $rzpPayments = $razorpay->getPaymentsForOrder($order->order_number);
            $successfulPayment = collect($rzpPayments)->firstWhere('status', 'captured');

            if ($successfulPayment) {
                DB::transaction(function () use ($order, $rider, $successfulPayment) {
                    $order->update([
                        'payment_method' => 'doorstep_upi',
                        'payment_status' => 'paid',
                    ]);

                    \App\Models\Payment::updateOrCreate(
                        ['order_id' => $order->id, 'gateway' => 'razorpay'],
                        [
                            'gateway_order_id'       => $successfulPayment['order_id'] ?? null,
                            'gateway_payment_id'     => (string) ($successfulPayment['id'] ?? 'RZP_POD_' . time()),
                            'amount'                 => (float) $order->total_amount,
                            'currency'               => 'INR',
                            'status'                 => 'SUCCESS',
                            'payment_method_group'   => 'upi',
                            'payment_method_details' => ['type' => 'doorstep_upi'],
                            'bank_reference'         => (string) ($successfulPayment['acquirer_data']['rrn'] ?? $successfulPayment['id'] ?? ''),
                            'payment_time'           => now(),
                            'gateway_message'        => 'Doorstep Razorpay UPI Payment Verified',
                            'raw_response'           => $successfulPayment,
                        ]
                    );

                    $order->statusHistories()->create([
                        'previous_status' => $order->status,
                        'current_status'  => $order->status,
                        'changed_by'      => $rider->id,
                        'notes'           => "Razorpay Doorstep UPI Payment of ₹" . number_format($order->total_amount, 2) . " auto-verified via Razorpay PG.",
                    ]);
                });

                return response()->json([
                    'success'          => true,
                    'is_paid'          => true,
                    'payment_status'   => 'paid',
                    'payment_method'   => 'doorstep_upi',
                    'total_amount'     => (float) $order->total_amount,
                    'formatted_amount' => '₹' . number_format($order->total_amount, 2),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info("Doorstep Razorpay poll check error: " . $e->getMessage());
        }

        return response()->json([
            'success'          => true,
            'is_paid'          => false,
            'payment_status'   => $order->payment_status,
            'payment_method'   => $order->payment_method,
            'total_amount'     => (float) $order->total_amount,
            'formatted_amount' => '₹' . number_format($order->total_amount, 2),
        ]);
    }

    /**
     * Confirm Doorstep UPI Payment (Zero Cash Handover Liability for Rider).
     */
    public function confirmDoorstepUpi(Request $request, Order $order): JsonResponse
    {
        $rider = Auth::guard('delivery_partner')->user();
        if ($order->rider_id !== $rider->id && !$rider->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success'          => true,
                'message'          => 'Payment already verified as Paid.',
                'payment_status'   => 'paid',
                'payment_method'   => $order->payment_method,
                'formatted_amount' => '₹' . number_format($order->total_amount, 2),
            ]);
        }

        $request->validate([
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $rider, $request) {
            $order->update([
                'payment_method' => 'doorstep_upi',
                'payment_status' => 'paid',
            ]);

            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'notes' => trim(($order->fulfillment->notes ?? '') . " | Paid via Razorpay Doorstep UPI (Txn: " . ($request->transaction_id ?? 'AUTO-VERIFIED') . ")"),
                ]);
            }

            \App\Models\Payment::updateOrCreate(
                ['order_id' => $order->id, 'gateway' => 'razorpay'],
                [
                    'amount'                 => (float) $order->total_amount,
                    'currency'               => 'INR',
                    'status'                 => 'SUCCESS',
                    'payment_method_group'   => 'upi',
                    'payment_method_details' => ['type' => 'doorstep_upi', 'verified_by' => $rider->name],
                    'bank_reference'         => (string) ($request->transaction_id ?? 'RZP_POD_' . time()),
                    'payment_time'           => now(),
                    'gateway_message'        => 'Doorstep UPI Payment Confirmed via Razorpay',
                ]
            );

            $order->statusHistories()->create([
                'previous_status' => $order->status,
                'current_status'  => $order->status,
                'changed_by'      => $rider->id,
                'notes'           => "Razorpay Doorstep UPI Payment of ₹" . number_format($order->total_amount, 2) . " verified by {$rider->name}. " . ($request->transaction_id ? "Ref: {$request->transaction_id}" : ""),
            ]);
        });

        return response()->json([
            'success'          => true,
            'message'          => 'Payment of ₹' . number_format($order->total_amount, 2) . ' received successfully via Razorpay UPI!',
            'payment_status'   => 'paid',
            'payment_method'   => 'doorstep_upi',
            'formatted_amount' => '₹' . number_format($order->total_amount, 2),
        ]);
    }

    /**
     * Complete Delivery & Verify OTP.
     */
    public function completeDelivery(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $rider = Auth::guard('delivery_partner')->user();

        if ($order->rider_id !== $rider->id && !$rider->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'otp'   => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // If order has an OTP, verify it unless bypassed with valid reason
        if ($order->delivery_otp && $request->filled('otp')) {
            if (trim($request->otp) !== trim($order->delivery_otp)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid OTP entered. Please ask customer for the 4-digit code.',
                    ], 422);
                }
                return back()->withErrors(['otp' => 'Invalid OTP. Please check with customer.']);
            }
        }

        DB::transaction(function () use ($order, $rider, $request) {
            $previousStatus = $order->status;

            $order->update([
                'status'         => 'delivered',
                'payment_status' => 'paid',
            ]);

            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'status'                     => 'delivered',
                    'delivered_at'               => now(),
                    'delivery_issue_resolved_at' => $order->fulfillment->delivery_issue ? now() : null,
                    'delivery_issue_resolution'  => $order->fulfillment->delivery_issue ? 'Delivered with Customer OTP' : null,
                    'notes'                      => $request->notes ?: "Delivered by {$rider->name}",
                ]);
            }

            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'delivered',
                'changed_by'      => $rider->id,
                'notes'           => "Doorstep delivery completed by {$rider->name}. " . ($request->notes ? "[Note: {$request->notes}]" : ''),
            ]);

            // Trigger Tiered Referral Milestone Rewards for Referrer
            app(\App\Services\WalletService::class)->rewardReferrerOnDeliveredOrder($order);

            // Trigger WhatsApp Order Delivered Notification
            try {
                app(\App\Services\WhatsAppService::class)->sendOrderDelivered($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[DeliveryDashboardController] WhatsApp Order Delivered notification failed: " . $e->getMessage());
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} marked as Delivered! Great job!",
                'redirect' => route('delivery.dashboard'),
            ]);
        }

        return redirect()->route('delivery.dashboard')->with('toast', [
            'type'    => 'success',
            'title'   => 'Delivery Completed!',
            'message' => "Order #{$order->order_number} delivered successfully.",
        ]);
    }

    /**
     * Report Delivery Issue / Failed Attempt.
     */
    public function reportIssue(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        $rider = Auth::guard('delivery_partner')->user();

        if ($order->rider_id !== $rider->id && !$rider->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if ($order->fulfillment) {
            $order->fulfillment->update([
                'delivery_issue'             => $request->reason,
                'delivery_issue_at'          => now(),
                'delivery_issue_resolved_at' => null,
                'delivery_issue_resolution'  => null,
            ]);
        }

        $order->statusHistories()->create([
            'previous_status' => $order->status,
            'current_status'  => $order->status,
            'changed_by'      => $rider->id,
            'notes'           => "Delivery issue reported by {$rider->name}: {$request->reason}",
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => "Issue reported for Order #{$order->order_number}. Order Manager notified.",
                'redirect' => route('delivery.dashboard'),
            ]);
        }

        return redirect()->route('delivery.dashboard')->with('toast', [
            'type'    => 'info',
            'title'   => 'Issue Reported',
            'message' => "Issue logged for Order #{$order->order_number}. Warehouse team notified.",
        ]);
    }

    /**
     * Today's Completed Deliveries History.
     */
    public function completed(): View
    {
        $rider = Auth::guard('delivery_partner')->user();

        $completedOrders = Order::with(['items.product', 'fulfillment'])
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id))
            ->where('status', 'delivered')
            ->latest('updated_at')
            ->paginate(20);

        return view('delivery.completed', compact('rider', 'completedOrders'));
    }
}
