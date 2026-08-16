<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function getCustomerOrders(Request $request)
    {
        $query = Auth::user()->orders()->with('items.product');

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest()->paginate(10);
    }

    public function getAdminOrders(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Update order status and record history.
     */
    public function updateOrderStatus(Order $order, string $newStatus, ?string $notes = null): Order
    {
        if ($order->status === $newStatus) {
            return $order;
        }

        return DB::transaction(function () use ($order, $newStatus, $notes) {
            $previousStatus = $order->status;

            // 1. Update the order
            $order->update(['status' => $newStatus]);

            // 2. Record the history
            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => $newStatus,
                'changed_by'      => Auth::id(), // Usually an admin ID
                'notes'           => $notes,
            ]);

            return $order;
        });
    }

    /**
     * Helper to log initial order creation status
     */
    public function recordInitialStatus(Order $order): void
    {
        $order->statusHistories()->create([
            'previous_status' => null,
            'current_status'  => $order->status,
            'changed_by'      => Auth::id(), // The customer placing the order
            'notes'           => 'Order Placed',
        ]);
    }
}
