<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderCancellation;
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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                  ->orWhere('refund_upi_id', 'like', "%{$search}%");
            });
        }

        $cancellations = $query->paginate(15);

        // Key stats
        $pendingUpiCount   = OrderCancellation::where('refund_method', 'bank_upi')->where('refund_status', 'pending')->count();
        $pendingUpiAmount  = OrderCancellation::where('refund_method', 'bank_upi')->where('refund_status', 'pending')->sum('refund_amount');
        $processedCount    = OrderCancellation::where('refund_status', 'processed')->count();
        $processedAmount   = OrderCancellation::where('refund_status', 'processed')->sum('refund_amount');
        $totalGstRetained  = OrderCancellation::sum('cancellation_fee');

        return view('admin.refunds.index', compact(
            'cancellations',
            'pendingUpiCount',
            'pendingUpiAmount',
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

        return redirect()->back()->with('success', "Refund of ₹" . number_format($cancellation->refund_amount, 2) . " for Order #{$order->order_number} to UPI ({$cancellation->refund_upi_id}) has been marked as PROCESSED!");
    }
}
