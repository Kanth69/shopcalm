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
        $isPendingUnpaid = in_array($order->status, ['pending', 'failed', '']) || $order->payment_status === 'failed';

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
        $isPendingUnpaid = in_array($order->status, ['pending', 'failed', '']) || $order->payment_status === 'failed';

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
            if (!in_array($order->status, ['pending', 'failed', 'confirmed', ''])) {
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

                $walletRefundAmt = (float) ($cancellation->wallet_refund_amount ?? 0);
                $onlineRefundAmt = (float) ($cancellation->online_refund_amount ?? 0);
                $onlineFormatted = number_format($onlineRefundAmt, 2);
                $walletFormatted = number_format($walletRefundAmt, 2);

                if ($walletRefundAmt > 0 && $onlineRefundAmt > 0) {
                    $msg = "Order #{$order->order_number} cancelled! ₹{$walletFormatted} refunded to your ShopCalm Wallet instantly, and ₹{$onlineFormatted} (Net of GST Fee) initiated back to your original payment source (Razorpay PG).";
                } elseif ($walletRefundAmt > 0) {
                    $msg = "Order #{$order->order_number} cancelled! ₹{$walletFormatted} has been refunded back to your ShopCalm Wallet balance instantly.";
                } else {
                    $msg = $cancellation->refund_status === 'processed'
                        ? "Order #{$order->order_number} cancelled! Direct refund of ₹{$onlineFormatted} (Net of GST Fee) has been initiated back to your original payment source (Razorpay PG)."
                        : "Order #{$order->order_number} cancelled! Net refund request of ₹{$onlineFormatted} (Net of GST Fee) submitted successfully. Expected credit: 3-5 business days.";
                }
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

    /**
     * Handle Customer Delivery Decision / Exception Resolution (2-Way Sync with Hub & Fleet).
     */
    public function deliveryDecision(Request $request, Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user || (int)$order->user_id !== (int)$user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $validated = $request->validate([
            'decision'      => 'required|string|in:reschedule_tomorrow,leave_security_neighbor,reattempt_today',
            'delivery_slot' => 'nullable|string|max:100',
            'instructions'  => 'nullable|string|max:300',
        ]);

        $decision = $validated['decision'];
        $fulfillment = $order->fulfillment;
        $resolutionNote = '';

        if ($decision === 'reschedule_tomorrow') {
            $slot = $validated['delivery_slot'] ?? 'Tomorrow Morning (8:00 AM - 12:00 PM)';
            $resolutionNote = "Rescheduled by Customer to {$slot}";

            if ($fulfillment) {
                $fulfillment->update([
                    'delivery_slot'              => $slot,
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => $resolutionNote,
                    'notes'                      => trim(($fulfillment->notes ?? '') . " | [Customer Rescheduled: {$slot}]"),
                ]);
            }

            // Sync delivery slot to order model as well
            $order->update(['delivery_slot' => $slot]);
        } elseif ($decision === 'leave_security_neighbor') {
            $customNote = $validated['instructions'] ?: 'Please leave package with building security or next-door neighbor';
            $resolutionNote = "Customer Handover Instruction: {$customNote}";

            if ($fulfillment) {
                $fulfillment->update([
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => $resolutionNote,
                    'notes'                      => trim(($fulfillment->notes ?? '') . " | [Customer: {$customNote}]"),
                ]);
            }
        } elseif ($decision === 'reattempt_today') {
            $resolutionNote = 'Customer is at home now — ready for doorstep handover';

            if ($fulfillment) {
                $fulfillment->update([
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => $resolutionNote,
                    'notes'                      => trim(($fulfillment->notes ?? '') . ' | [Customer: Home now, ready for re-attempt]'),
                ]);
            }
        }

        // Record in Order Status Audit History for Order Manager visibility
        $order->statusHistories()->create([
            'previous_status' => $order->status,
            'current_status'  => $order->status,
            'notes'           => "Customer Delivery Action: {$resolutionNote}",
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Your delivery decision has been synchronized with the dispatch hub and assigned delivery partner.',
                'resolution' => $resolutionNote,
                'delivery_slot' => $order->fresh()->delivery_slot,
            ]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Instructions Updated',
            'message' => 'Your delivery decision has been sent to our dispatch hub and delivery partner.',
        ]);
    }

    /**
     * Real-time Lightweight Poller Endpoint for Order Tracking (15s Heartbeat).
     */
    public function statusPoll(Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user || (int)$order->user_id !== (int)$user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $order->load(['fulfillment', 'feedback']);
        $fulfillment = $order->fulfillment;

        return response()->json([
            'success'            => true,
            'order_id'           => $order->id,
            'order_number'       => $order->order_number,
            'status'             => $order->status,
            'status_label'       => ucfirst($order->status),
            'payment_status'     => $order->payment_status,
            'is_out_for_delivery'=> $order->status === 'out for delivery',
            'is_delivered'       => $order->status === 'delivered',
            'is_cancelled'       => $order->status === 'cancelled',
            'has_active_issue'   => (bool)($fulfillment?->hasActiveDeliveryIssue()),
            'issue_reason'       => $fulfillment?->delivery_issue,
            'delivery_slot'      => $fulfillment?->delivery_slot ?: ($order->delivery_slot ?: 'Fast Doorstep Delivery'),
            'rider_name'         => $order->rider_name,
            'rider_phone'        => $order->rider_phone,
            'feedback_submitted' => (bool)$order->feedback,
        ]);
    }

    /**
     * 1-Tap "Buy Again" / Re-Order All Items into Customer Shopping Bag.
     */
    public function reorder(Order $order)
    {
        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user || (int)$order->user_id !== (int)$user->id) {
            abort(403);
        }

        $cartService = app(\App\Services\CartService::class);
        $order->load('items.product');

        $addedCount = 0;
        $failedCount = 0;

        foreach ($order->items as $item) {
            if ($item->product && $item->product->status === 'Active') {
                $qty = max(1, (int)$item->quantity);
                $opt = $item->selected_option;
                $res = $cartService->addProduct((int)$item->product_id, $qty, $opt);
                if (is_array($res) && ($res['success'] ?? false)) {
                    $addedCount++;
                } else {
                    $failedCount++;
                }
            } else {
                $failedCount++;
            }
        }

        if ($addedCount === 0) {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'Unavailable',
                'message' => 'Items from this order are currently out of stock or inactive.',
            ]);
        }

        $msg = "Added {$addedCount} item(s) from Order #{$order->order_number} to your shopping bag.";
        if ($failedCount > 0) {
            $msg .= " ({$failedCount} item(s) were out of stock).";
        }

        return redirect()->route('cart.index')->with('toast', [
            'type'    => 'success',
            'title'   => 'Bag Updated! 🛍️',
            'message' => $msg,
        ]);
    }
}
