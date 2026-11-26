<?php

namespace App\Http\Controllers\OrderManager;

use App\Http\Controllers\Controller;
use App\Models\OrderReplacement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReplacementController extends Controller
{
    /**
     * Dedicated Replacements & Reverse Logistics Registry for Order Manager.
     */
    public function index(Request $request): View
    {
        $query = OrderReplacement::with(['order', 'orderItem', 'product', 'user', 'rider'])->latest();

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('replacement_number', 'like', "%{$s}%")
                  ->orWhereHas('order', fn($oq) => $oq->where('order_number', 'like', "%{$s}%"))
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$s}%")->orWhere('mobile_number', 'like', "%{$s}%"))
                  ->orWhere('rider_name', 'like', "%{$s}%");
            });
        }

        $replacements = $query->paginate(15)->withQueryString();

        $metrics = [
            'all'              => OrderReplacement::count(),
            'requested'        => OrderReplacement::where('status', 'requested')->count(),
            'pickup_assigned'  => OrderReplacement::whereIn('status', ['approved', 'pickup_assigned'])->count(),
            'picked_up'        => OrderReplacement::where('status', 'picked_up')->count(),
            'completed'        => OrderReplacement::where('status', 'completed')->count(),
            'rejected'         => OrderReplacement::where('status', 'rejected')->count(),
        ];

        $riders = User::deliveryPartners()->orderBy('name')->get();

        return view('order-manager.replacements.index', compact('replacements', 'metrics', 'riders'));
    }

    /**
     * Zero-Refresh Live Poller Heartbeat (Triggered by frontend every 15-20 seconds).
     */
    public function livePoll(Request $request): JsonResponse
    {
        $lastCheckedId = (int) $request->input('last_id', 0);

        $newRequests = OrderReplacement::with(['order', 'product', 'user'])
            ->where('id', '>', $lastCheckedId)
            ->where('status', 'requested')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($rep) {
                return [
                    'id'                 => $rep->id,
                    'replacement_number' => $rep->replacement_number,
                    'order_number'       => $rep->order?->order_number,
                    'customer_name'      => $rep->user?->name ?? 'Customer',
                    'customer_phone'     => $rep->user?->mobile_number ?? $rep->order?->shipping_phone,
                    'item_name'          => $rep->product?->name ?? 'Item',
                    'reason_label'       => $rep->reason_label,
                    'has_photos'         => !empty($rep->proof_images),
                    'created_at_human'   => $rep->created_at->diffForHumans(),
                ];
            });

        $pendingCount = OrderReplacement::where('status', 'requested')->count();
        $latestId = OrderReplacement::max('id') ?: 0;

        return response()->json([
            'success'       => true,
            'pending_count' => $pendingCount,
            'latest_id'     => $latestId,
            'new_count'     => $newRequests->count(),
            'new_requests'  => $newRequests,
        ]);
    }

    /**
     * Approve Replacement, Schedule Pickup Date & Assign Delivery Boy.
     */
    public function approveAndAssign(Request $request, OrderReplacement $replacement): RedirectResponse|JsonResponse
    {
        $request->validate([
            'pickup_date'         => ['required', 'date', 'after_or_equal:today'],
            'rider_id'            => ['required', 'exists:users,id'],
            'order_manager_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'pickup_date.required' => 'Please select a doorstep pickup date.',
            'rider_id.required'    => 'Please select a Delivery Partner to assign this pickup.',
        ]);

        $rider = User::find($request->rider_id);
        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();

        $replacement->update([
            'status'              => 'pickup_assigned',
            'pickup_date'         => $request->pickup_date,
            'rider_id'            => $rider->id,
            'rider_name'          => $rider->name,
            'rider_phone'         => $rider->mobile_number,
            'order_manager_notes' => $request->order_manager_notes,
            'reviewed_by'         => $staffId,
            'reviewed_at'         => now(),
        ]);

        $msg = "Replacement #{$replacement->replacement_number} approved! Assigned to {$rider->name} for pickup on " . \Carbon\Carbon::parse($request->pickup_date)->format('d M Y') . '.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'status'  => $replacement->status,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Reject Replacement Request with Explanation.
     */
    public function reject(Request $request, OrderReplacement $replacement): RedirectResponse|JsonResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Please provide a clear reason for rejecting this replacement claim.',
        ]);

        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();

        $replacement->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by'      => $staffId,
            'reviewed_at'      => now(),
        ]);

        $msg = "Replacement #{$replacement->replacement_number} rejected.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'status'  => 'rejected',
            ]);
        }

        return redirect()->back()->with('warning', $msg);
    }
}
