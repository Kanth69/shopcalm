<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderCancellation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    /**
     * Display listing of customer order refund requests.
     */
    public function index(Request $request)
    {
        $query = OrderCancellation::with(['order', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('refund_status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('refund_method', $request->method);
        }

        // Filter by Order Payment Method
        if ($request->filled('payment_method')) {
            $pm = strtolower($request->payment_method);
            if ($pm === 'wallet') {
                $query->where(function ($q) {
                    $q->where('refund_method', 'wallet')
                      ->orWhere('wallet_refund_amount', '>', 0)
                      ->orWhereHas('order', function ($oq) {
                          $oq->where('payment_method', 'wallet')
                             ->orWhere('wallet_amount_used', '>', 0);
                      });
                });
            } elseif ($pm === 'online') {
                $query->whereHas('order', function ($oq) {
                    $oq->whereIn('payment_method', ['online', 'razorpay', 'prepaid']);
                });
            } else {
                $query->whereHas('order', function ($oq) use ($pm) {
                    $oq->where('payment_method', $pm);
                });
            }
        }

        // Filter by Cancellation Date (order_cancellations.created_at)
        if ($request->filled('start_date') || $request->filled('end_date') || $request->filled('from_date') || $request->filled('to_date')) {
            $rawStart = $request->input('start_date') ?: $request->input('from_date');
            $rawEnd = $request->input('end_date') ?: $request->input('to_date');

            $start = $rawStart ? Carbon::parse($rawStart)->startOfDay() : null;
            $end = $rawEnd ? Carbon::parse($rawEnd)->endOfDay() : null;

            if ($start && $end && $start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            if ($start) {
                $query->where('order_cancellations.created_at', '>=', $start);
            }
            if ($end) {
                $query->where('order_cancellations.created_at', '<=', $end);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanSearch = str_replace('CNL-', '', $search);

            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->whereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$search}%")->orWhere('order_number', 'like', "%{$cleanSearch}%"))
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                  ->orWhere('refund_upi_id', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhere('razorpay_refund_id', 'like', "%{$search}%");
            });
        }

        $cancellations = $query->paginate(15)->withQueryString();

        // Key stats
        $pendingUpiCount   = OrderCancellation::where('refund_method', 'bank_upi')->where('refund_status', 'pending')->count();
        $pendingUpiAmount  = OrderCancellation::where('refund_method', 'bank_upi')->where('refund_status', 'pending')->sum('refund_amount');
        $pendingRzCount    = OrderCancellation::whereIn('refund_method', ['original_source', 'online', 'dual'])->whereIn('online_refund_status', ['pending', 'none'])->where('online_refund_amount', '>', 0)->count();
        $pendingRzAmount   = OrderCancellation::whereIn('refund_method', ['original_source', 'online', 'dual'])->whereIn('online_refund_status', ['pending', 'none'])->where('online_refund_amount', '>', 0)->sum('online_refund_amount');
        $processedCount    = OrderCancellation::where(function($q) {
            $q->where('refund_status', 'processed')->orWhere('online_refund_status', 'processed');
        })->count();
        $processedAmount   = OrderCancellation::where(function($q) {
            $q->where('refund_status', 'processed')->orWhere('online_refund_status', 'processed');
        })->sum('refund_amount');
        $totalGstRetained  = OrderCancellation::sum('cancellation_fee');

        return view('admin.refunds.index', compact(
            'cancellations',
            'pendingUpiCount',
            'pendingUpiAmount',
            'pendingRzCount',
            'pendingRzAmount',
            'processedCount',
            'processedAmount',
            'totalGstRetained'
        ));
    }

    /**
     * Mark a Pending Bank UPI refund as Processed.
     */
    public function processRefund(Request $request, OrderCancellation $cancellation)
    {
        $request->validate([
            'payment_reference' => 'required|string|max:100',
            'payout_channel'    => 'nullable|string|max:50',
            'notes'             => 'nullable|string|max:255',
        ]);

        $channelNote = $request->filled('payout_channel') ? " via " . strtoupper(str_replace('_', ' ', $request->payout_channel)) : "";
        $refNote = " (UTR Ref: {$request->payment_reference})";
        $adminNotes = $request->filled('notes') ? " - Note: {$request->notes}" : "";

        $cancellation->update([
            'refund_status'     => 'processed',
            'payment_reference' => $request->payment_reference,
        ]);

        // Log in order status history for complete audit trail
        $order = $cancellation->order;
        if ($order) {
            $order->statusHistories()->create([
                'previous_status' => $order->status,
                'current_status'  => $order->status,
                'changed_by'      => \Illuminate\Support\Facades\Auth::id(),
                'notes'           => "Net Refund of ₹" . number_format($cancellation->refund_amount, 2) . " to UPI handle ({$cancellation->refund_upi_id}) marked PROCESSED by Admin{$channelNote}{$refNote}{$adminNotes}.",
            ]);
        }

        // Trigger WhatsApp Refund Processed Notification (with UTR Proof)
        try {
            // WhatsApp notification deleted (only registration/reset OTP allowed)
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("[Admin RefundController] WhatsApp Refund Processed notification failed: " . $e->getMessage());
        }

        return redirect()->back()->with('success', "Refund of ₹" . number_format($cancellation->refund_amount, 2) . " for Order #{$order->order_number} to UPI ({$cancellation->refund_upi_id}) has been marked as PROCESSED!");
    }

    /**
     * Live sync Razorpay refund status directly from gateway.
     */
    public function syncRazorpay(Request $request, OrderCancellation $cancellation)
    {
        $res = app(\App\Services\OrderService::class)->syncRazorpayRefundStatus($cancellation);
        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }
        return redirect()->back()->with('error', $res['message']);
    }

    /**
     * Trigger / Retry direct PG refund via Razorpay.
     */
    public function triggerRazorpay(Request $request, OrderCancellation $cancellation)
    {
        $res = app(\App\Services\OrderService::class)->triggerRazorpayRefund($cancellation);
        if ($res['success']) {
            return redirect()->back()->with('success', $res['message']);
        }
        return redirect()->back()->with('error', $res['message']);
    }
}

