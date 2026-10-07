<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->getCustomerOrders($request);

        $user           = Auth::guard('customer')->user() ?? Auth::user();
        $totalCount     = $user ? $user->orders()->count() : 0;
        $deliveredCount = $user ? $user->orders()->where('status', 'delivered')->count() : 0;
        $activeCount    = $user ? $user->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count() : 0;

        return view('customer.orders.index', compact('orders', 'totalCount', 'deliveredCount', 'activeCount'));
    }

    /**
     * Get JSON cancellation breakdown summary for modal.
     */
    public function cancellationSummary(Order $order)
    {
        $isPendingUnpaid = in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed';

        if (!$isPendingUnpaid && \App\Models\Setting::get('allow_customer_cancellation', '1') != '1') {
            return response()->json([
                'success' => false,
                'message' => 'Customer order cancellation is currently disabled by store management.',
            ], 403);
        }

        $user = Auth::guard('customer')->user() ?? Auth::user();
        $userId = $user ? $user->id : null;

        if (!$userId || (int)$order->user_id !== (int)$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this order.',
            ], 403);
        }

        $summary = $this->orderService->calculateCancellationSummary($order);
        return response()->json([
            'success' => true,
            'summary' => $summary,
            'is_cod' => $order->payment_method === 'cod',
            'is_paid' => $order->payment_status === 'paid',
        ]);
    }

    /**
     * Handle Customer Order Cancellation.
     */
    public function cancel(Request $request, Order $order)
    {
        $wantsJson = $request->ajax() || $request->wantsJson() || $request->expectsJson();
        $isPendingUnpaid = in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed';

        if (!$isPendingUnpaid && \App\Models\Setting::get('allow_customer_cancellation', '1') != '1') {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer order cancellation is currently disabled by store management.',
                ], 403);
            }
            return redirect()->back()->with('error', 'Customer order cancellation is currently disabled by store management.');
        }

        $user = Auth::guard('customer')->user() ?? Auth::user();
        $userId = $user ? $user->id : null;

        if (!$userId || (int)$order->user_id !== (int)$userId) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this order.',
                ], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized access to this order.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
            'refund_method'       => 'nullable|string|in:original_source,wallet,none',
        ]);

        try {
            if (!in_array($order->status, ['pending', 'failed', 'confirmed'])) {
                $errMsg = "Order #{$order->order_number} cannot be cancelled as it is currently in '{$order->status}' status. Cancellations are only allowed during Pending or Confirmed stage.";
                if ($wantsJson) {
                    return response()->json([
                        'success' => false,
                        'message' => $errMsg,
                    ], 422);
                }
                return redirect()->back()->with('error', $errMsg);
            }

            if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                // COD order: 100% FREE Cancellation, 0 Fee
                $cancellation = $this->orderService->cancelCodOrderFree($order, $request->cancellation_reason);

                $msg = $cancellation->refund_amount > 0
                    ? "Order #{$order->order_number} cancelled! ₹" . number_format($cancellation->refund_amount, 2) . " has been refunded back to your ShopCalm Wallet balance instantly."
                    : "Order #{$order->order_number} has been cancelled successfully (100% Free COD Cancellation).";
            } elseif ($order->payment_method === 'online' && $order->payment_status !== 'paid') {
                // Unpaid Online Order: 100% Free Cancellation
                $refundMethod = $request->input('refund_method', 'original_source');
                $upiId = $request->input('refund_upi_id');
                $cancellation = $this->orderService->cancelPrepaidOrder($order, $request->cancellation_reason, $refundMethod, $upiId);

                $msg = $cancellation->wallet_refund_amount > 0
                    ? "Order #{$order->order_number} cancelled! ₹" . number_format($cancellation->wallet_refund_amount, 2) . " has been refunded back to your ShopCalm Wallet balance instantly."
                    : "Order #{$order->order_number} has been cancelled successfully.";
            } else {
                // Prepaid Order (Online Payment - Paid)
                $refundMethod = $request->input('refund_method', 'original_source');
                $upiId = $request->input('refund_upi_id');
                $cancellation = $this->orderService->cancelPrepaidOrder($order, $request->cancellation_reason, $refundMethod, $upiId);

                $refundAmountFormatted = number_format($cancellation->refund_amount, 2);
                $msg = $cancellation->refund_status === 'processed'
                    ? "Order #{$order->order_number} cancelled! Direct refund of ₹{$refundAmountFormatted} (Net of GST Fee) has been initiated back to your original payment source (Razorpay PG)."
                    : "Order #{$order->order_number} cancelled! Net refund request of ₹{$refundAmountFormatted} (Net of GST Fee) submitted successfully. Amount will be credited back to your original payment source (UPI / Card / Bank) via Razorpay within 3-5 business days.";
            }

            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                ]);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
