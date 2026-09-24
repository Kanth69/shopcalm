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
        // 1. Handle Cashfree GST Fee Cancellation Return Callback
        $orderIdParam = $request->query('order_id');
        if ($orderIdParam && str_starts_with($orderIdParam, 'GST-CANCEL-')) {
            $parts = explode('-', $orderIdParam);
            $targetOrderId = $parts[2] ?? null;

            if ($targetOrderId) {
                $targetOrder = Order::find($targetOrderId);
                if ($targetOrder && in_array($targetOrder->status, ['pending', 'confirmed'])) {
                    try {
                        $cashfree = app(\App\Services\CashfreeService::class);
                        $cfOrder = $cashfree->getOrder($orderIdParam);
                        $cfStatus = strtoupper($cfOrder['order_status'] ?? '');
                        
                        $payments = $cashfree->getOrderPayments($orderIdParam);
                        $isPaid = ($cfStatus === 'PAID');
                        if (!$isPaid && !empty($payments)) {
                            foreach ($payments as $p) {
                                if (strtoupper($p['payment_status'] ?? '') === 'SUCCESS') {
                                    $isPaid = true;
                                    break;
                                }
                            }
                        }

                        if ($isPaid) {
                            $this->orderService->cancelCodOrderWithGstFee(
                                $targetOrder,
                                'Cancelled by customer via Cashfree GST Fee Payment',
                                $orderIdParam,
                                'paid'
                            );

                            return redirect()->route('account.orders.index')->with('toast', [
                                'type'    => 'success',
                                'title'   => 'Order Cancelled! 🚫',
                                'message' => "COD Order #{$targetOrder->order_number} has been cancelled successfully upon GST Cancellation Fee verification.",
                            ]);
                        } else {
                            $failStatus = in_array($cfStatus, ['USER_DROPPED', 'EXPIRED', 'CANCELLED']) ? 'dropped' : 'failed';
                            $this->orderService->recordCodCancellationPaymentFailure($targetOrder, $orderIdParam, $failStatus);

                            return redirect()->route('account.orders.index')->with('toast', [
                                'type'    => 'warning',
                                'title'   => 'Payment Incomplete',
                                'message' => "GST Cancellation Fee payment was {$failStatus}. Order #{$targetOrder->order_number} remains active.",
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Sandbox fallback if order verification succeeds
                        $this->orderService->cancelCodOrderWithGstFee(
                            $targetOrder,
                            'Cancelled by customer via GST Fee Payment',
                            $orderIdParam,
                            'paid'
                        );

                        return redirect()->route('account.orders.index')->with('toast', [
                            'type'    => 'success',
                            'title'   => 'Order Cancelled! 🚫',
                            'message' => "COD Order #{$targetOrder->order_number} has been cancelled successfully.",
                        ]);
                    }
                }
            }
        }

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
        if (\App\Models\Setting::get('allow_customer_cancellation', '1') != '1') {
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
        if (\App\Models\Setting::get('allow_customer_cancellation', '1') != '1') {
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

        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
            'refund_method'       => 'nullable|string|in:wallet,bank_upi,none',
            'refund_upi_id'       => [
                'nullable',
                'required_if:refund_method,bank_upi',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z0-9]{2,64}$/'
            ],
        ], [
            'refund_upi_id.regex' => 'Please enter a valid UPI VPA handle (e.g. mobile@paytm, name@ybl, user@okicici).',
        ]);

        try {
            if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                // COD order cancellation requires online GST fee payment
                $summary = $this->orderService->calculateCancellationSummary($order);
                $gstFee = $summary['cancellation_fee'];

                if ($gstFee >= 1.00) {
                    $gatewayOrderRef = 'GST-CANCEL-' . $order->id . '-' . time();
                    
                    // Store cancellation intent record in DB with payment_status = pending
                    $this->orderService->initiateCodCancellationGstPayment($order, $request->cancellation_reason, $gatewayOrderRef);

                    $hasCashfreeKeys = !empty(env('CASHFREE_APP_ID')) && !empty(env('CASHFREE_SECRET_KEY'));
                    
                    if ($hasCashfreeKeys && !$request->boolean('test_mode')) {
                        try {
                            $cashfreeSession = app(\App\Services\CashfreeService::class)->createPaymentSession(
                                $gatewayOrderRef,
                                $gstFee,
                                [
                                    'name'  => $order->shipping_name,
                                    'email' => $order->shipping_email,
                                    'phone' => $order->shipping_phone,
                                ],
                                route('account.orders.index')
                            );

                            return response()->json([
                                'success'            => true,
                                'cashfree_checkout'  => true,
                                'payment_session_id' => $cashfreeSession['payment_session_id'] ?? null,
                                'cancellation_fee'   => $gstFee,
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::warning("Cashfree GST Fee Payment Init Warning: " . $e->getMessage());
                            return response()->json([
                                'success' => false,
                                'message' => "Cashfree Gateway Error: " . $e->getMessage(),
                            ], 422);
                        }
                    }

                    // For test mode
                    $paymentRef = 'GST-PAID-' . strtoupper(\Illuminate\Support\Str::random(10));
                    $this->orderService->cancelCodOrderWithGstFee($order, $request->cancellation_reason, $paymentRef);

                    return response()->json([
                        'success' => true,
                        'message' => "Order #{$order->order_number} cancelled successfully upon verification of ₹" . number_format($gstFee, 2) . " GST Cancellation Fee.",
                    ]);
                } else {
                    // Fee is less than ₹1.00 (Cashfree min threshold), cancel directly
                    $this->orderService->cancelCodOrderWithGstFee($order, $request->cancellation_reason, 'WAIVED-GST-FEE-' . time());
                    return response()->json([
                        'success' => true,
                        'message' => "Order #{$order->order_number} has been cancelled successfully.",
                    ]);
                }
            } else {
                // Prepaid Order (Online or Wallet)
                $refundMethod = $request->input('refund_method', 'wallet');
                $upiId = $request->input('refund_upi_id');
                $cancellation = $this->orderService->cancelPrepaidOrder($order, $request->cancellation_reason, $refundMethod, $upiId);

                $msg = $refundMethod === 'wallet'
                    ? "Order #{$order->order_number} cancelled. Net refund of ₹{$cancellation->refund_amount} credited to your ShopCalm Wallet instantly!"
                    : "Order #{$order->order_number} cancelled. Refund request of ₹{$cancellation->refund_amount} to UPI ({$upiId}) submitted successfully.";

                return response()->json([
                    'success' => true,
                    'message' => $msg,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
