<?php

namespace App\Http\Controllers\OrderManager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Services\FulfillmentService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    protected $orderService;
    protected $fulfillmentService;

    public function __construct(OrderService $orderService, FulfillmentService $fulfillmentService)
    {
        $this->orderService = $orderService;
        $this->fulfillmentService = $fulfillmentService;
    }

    /**
     * Display a listing of orders with filters, fulfillment status, and zone counts.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['user', 'items.product', 'fulfillment']);

        // 1. Search Query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_email', 'like', "%{$search}%")
                  ->orWhere('shipping_city', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('fulfillment', function ($fq) use ($search) {
                      $fq->where('rider_name', 'like', "%{$search}%")
                         ->orWhere('rider_phone', 'like', "%{$search}%")
                         ->orWhere('tracking_number', 'like', "%{$search}%")
                         ->orWhere('carrier_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Fulfillment Zone Filter (Bengaluru Local vs National Courier)
        if ($request->filled('zone')) {
            if ($request->zone === 'bengaluru') {
                $query->whereHas('fulfillment', function ($q) {
                    $q->where('type', 'local_fleet');
                });
            } elseif ($request->zone === 'courier') {
                $query->whereHas('fulfillment', function ($q) {
                    $q->where('type', 'courier');
                });
            }
        }

        // 3. Status Filter
        if ($request->filled('status')) {
            $statuses = explode(',', $request->status);
            if (count($statuses) > 1) {
                $query->whereIn('status', $statuses);
            } else {
                $query->where('status', $request->status);
            }
        }

        // 4. Payment Method Filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // 5. Payment Status Filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 6. Date Filter
        if ($request->filled('date')) {
            if ($request->date === 'today') {
                $query->whereDate('created_at', today());
            } elseif ($request->date === 'yesterday') {
                $query->whereDate('created_at', today()->subDay());
            } elseif ($request->date === 'week') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($request->date === 'month') {
                $query->where('created_at', '>=', now()->subDays(30));
            }
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        // Metric counts for tab headers
        $statusCounts = [
            'all'              => Order::count(),
            'bengaluru_local'  => Order::whereHas('fulfillment', fn($q) => $q->where('type', 'local_fleet'))->count(),
            'national_courier' => Order::whereHas('fulfillment', fn($q) => $q->where('type', 'courier'))->count(),
            'needs_processing' => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'packing_queue'    => Order::whereIn('status', ['processing', 'packed'])->count(),
            'in_transit'       => Order::whereIn('status', ['shipped', 'out for delivery'])->count(),
            'delivered'        => Order::where('status', 'delivered')->count(),
            'cancelled'        => Order::whereIn('status', ['cancelled', 'returned'])->count(),
        ];

        return view('order-manager.orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Display detailed order view with fulfillment actions and item breakdown.
     */
    public function show(Order $order): View
    {
        $order->load([
            'user',
            'rider',
            'items.product',
            'statusHistories.user',
            'coupon',
            'fulfillment.assignedBy',
            'fulfillment.rider',
        ]);

        $deliveryPartners = User::deliveryPartners()
            ->withCount(['assignedDeliveries' => fn($q) => $q->whereIn('status', ['processing', 'packed', 'shipped', 'out for delivery'])])
            ->get();

        return view('order-manager.orders.show', compact('order', 'deliveryPartners'));
    }

    /**
     * Advance / Update the order status and record timeline history.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,processing,packed,shipped,out for delivery,delivered,cancelled,returned'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = $request->status;
        $notes     = $request->notes;

        DB::transaction(function () use ($order, $newStatus, $notes) {
            $previousStatus = $order->status;
            $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();

            $updateData = ['status' => $newStatus];

            if ($newStatus === 'shipped' && !$order->shipped_at) {
                $updateData['shipped_at'] = now();
            }

            if ($newStatus === 'delivered' && !$order->delivered_at) {
                $updateData['delivered_at'] = now();
                $updateData['payment_status'] = 'paid';
            }

            if ($newStatus === 'cancelled' && $previousStatus !== 'cancelled') {
                app(\App\Services\OrderService::class)->cancelOrderByAdmin($order, $notes ?: 'Cancelled by store management', 'wallet', $notes);
            } else {
                $order->update($updateData);
            }

            if ($order->fulfillment) {
                $fStatus = match($newStatus) {
                    'delivered' => 'delivered',
                    'out for delivery' => 'out_for_delivery',
                    'shipped' => 'dispatched',
                    'cancelled', 'returned' => 'returned',
                    default => $order->fulfillment->status,
                };
                $order->fulfillment->update([
                    'status'       => $fStatus,
                    'delivered_at' => ($newStatus === 'delivered') ? now() : $order->fulfillment->delivered_at,
                ]);
            }

            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => $newStatus,
                'changed_by'      => $staffId,
                'notes'           => $notes ?: "Status updated to " . ucfirst(str_replace('_', ' ', $newStatus)) . " by Order Manager.",
            ]);

            // If marked as delivered, trigger Tiered Referral Milestone Rewards
            if ($newStatus === 'delivered') {
                app(\App\Services\WalletService::class)->rewardReferrerOnDeliveredOrder($order);
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            $order->refresh();
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->order_number} is now marked as " . ucfirst(str_replace('_', ' ', $newStatus)) . ".",
                'status' => $order->status,
                'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                'fulfillment_badge' => $order->fulfillment_badge,
                'order' => $order->only(['id', 'order_number', 'status', 'shipped_at', 'delivered_at']),
            ]);
        }

        return redirect()->route('order-manager.orders.show', $order)->with('toast', [
            'type'    => 'success',
            'title'   => 'Order Status Updated',
            'message' => "Order #{$order->order_number} is now marked as " . ucfirst(str_replace('_', ' ', $newStatus)) . ".",
        ]);
    }

    /**
     * Assign Bengaluru Local Delivery Rider / Executive.
     */
    public function assignRider(Request $request, Order $order): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'rider_id'      => ['nullable', 'exists:users,id'],
            'rider_name'    => ['required', 'string', 'max:100'],
            'rider_phone'   => ['required', 'string', 'max:20'],
            'delivery_slot' => ['nullable', 'string', 'max:100'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        $data = $request->all();
        if ($request->filled('rider_id')) {
            $riderUser = User::find($request->rider_id);
            if ($riderUser) {
                $data['rider_name'] = $riderUser->name;
                $data['rider_phone'] = $riderUser->mobile_number ?: $request->rider_phone;
            }
        }

        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();
        $this->fulfillmentService->assignLocalRider($order, $data, $staffId);

        if ($request->ajax() || $request->wantsJson()) {
            $order->refresh();
            return response()->json([
                'success' => true,
                'message' => "Assigned to {$request->rider_name} ({$request->rider_phone}). Order #{$order->order_number} is now Out for Delivery!",
                'status' => $order->status,
                'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                'rider_name' => $order->rider_name,
                'rider_phone' => $order->rider_phone,
                'delivery_slot' => $order->delivery_slot,
                'order' => $order->only(['id', 'order_number', 'status', 'rider_name', 'rider_phone', 'delivery_slot']),
            ]);
        }

        return redirect()->route('order-manager.orders.show', $order)->with('toast', [
            'type'    => 'success',
            'title'   => 'Local Rider Dispatched',
            'message' => "Assigned to {$request->rider_name} ({$request->rider_phone}). Order #{$order->order_number} is now Out for Delivery!",
        ]);
    }

    /**
     * 1-Click Smart Auto-Assign Unassigned Local Fleet Parcels across Active Riders.
     */
    public function autoAssignRiders(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();
        $result = $this->fulfillmentService->autoAssignLocalFleetOrders($staffId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => $result['status'] === 'success',
                'message' => $result['message'],
                'count'   => $result['count'],
            ]);
        }

        return back()->with('toast', [
            'type'    => $result['status'],
            'title'   => 'Auto-Assignment Completed',
            'message' => $result['message'],
        ]);
    }

    /**
     * Update courier logistics partner and tracking details.
     */
    public function updateTracking(Request $request, Order $order): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'courier_partner' => ['required', 'string', 'max:100'],
            'tracking_number' => ['required', 'string', 'max:100'],
            'tracking_url'    => ['nullable', 'url', 'max:500'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();
        $this->fulfillmentService->assignCourierTracking($order, $request->all(), $staffId);

        if ($request->ajax() || $request->wantsJson()) {
            $order->refresh();
            return response()->json([
                'success' => true,
                'message' => "Courier {$request->courier_partner} with AWB #{$request->tracking_number} assigned for Order #{$order->order_number}.",
                'status' => $order->status,
                'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
                'courier_partner' => $order->courier_partner,
                'tracking_number' => $order->tracking_number,
                'tracking_url' => $order->tracking_url,
                'order' => $order->only(['id', 'order_number', 'status', 'courier_partner', 'tracking_number', 'tracking_url']),
            ]);
        }

        return redirect()->route('order-manager.orders.show', $order)->with('toast', [
            'type'    => 'success',
            'title'   => 'Tracking Info Saved',
            'message' => "Courier {$request->courier_partner} with AWB #{$request->tracking_number} assigned for Order #{$order->order_number}.",
        ]);
    }

    /**
     * Resolve delivery exception reported by delivery partner.
     */
    public function resolveException(Request $request, Order $order): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'resolution_action' => ['required', 'in:re-attempt,reschedule,return_warehouse,cancel'],
            'resolution_notes'  => ['nullable', 'string', 'max:500'],
            'rider_id'          => ['nullable', 'exists:users,id'],
            'delivery_slot'     => ['nullable', 'string', 'max:100'],
        ]);

        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();
        $action = $request->resolution_action;
        $notes = $request->resolution_notes ?? 'Resolution logged by Order Manager.';
        $previousStatus = $order->status;
        $msg = "Exception on Order #{$order->order_number} resolved.";

        if ($action === 're-attempt') {
            $order->update(['status' => 'out for delivery']);
            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'status'                     => 'out_for_delivery',
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => "Re-attempted Today: {$notes}",
                ]);
            }
            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'out for delivery',
                'changed_by'      => $staffId,
                'notes'           => "Delivery Exception Resolved [Re-attempt Today]: {$notes}",
            ]);
            $msg = "Order #{$order->order_number} marked for immediate doorstep re-attempt!";
        } elseif ($action === 'reschedule') {
            $slot = $request->delivery_slot ?? 'Next-Day Express Delivery (1-2 Days)';
            $riderData = [
                'rider_id'      => $request->rider_id ?: $order->rider_id,
                'rider_name'    => $order->rider_name,
                'rider_phone'   => $order->rider_phone,
                'delivery_slot' => $slot,
                'notes'         => "Rescheduled by Order Manager: {$notes}",
            ];
            if ($request->filled('rider_id')) {
                $rUser = User::find($request->rider_id);
                if ($rUser) {
                    $riderData['rider_name'] = $rUser->name;
                    $riderData['rider_phone'] = $rUser->mobile_number ?: $order->rider_phone;
                }
            }
            $this->fulfillmentService->assignLocalRider($order, $riderData, $staffId);
            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => "Rescheduled to {$slot}: {$notes}",
                ]);
            }
            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'out for delivery',
                'changed_by'      => $staffId,
                'notes'           => "Delivery Exception Resolved [Rescheduled to {$slot}]: {$notes}",
            ]);
            $msg = "Order #{$order->order_number} successfully rescheduled to {$slot}!";
        } elseif ($action === 'return_warehouse') {
            $order->update(['status' => 'returned']);
            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'status'                     => 'returned',
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => "Returned to Warehouse: {$notes}",
                ]);
            }
            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'returned',
                'changed_by'      => $staffId,
                'notes'           => "Delivery Exception Resolved [Return to Warehouse]: {$notes}",
            ]);
            $msg = "Order #{$order->order_number} marked as Returned to Warehouse inventory.";
        } elseif ($action === 'cancel') {
            $order->update(['status' => 'cancelled']);
            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'status'                     => 'failed',
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => "Cancelled: {$notes}",
                ]);
            }
            $order->statusHistories()->create([
                'previous_status' => $previousStatus,
                'current_status'  => 'cancelled',
                'changed_by'      => $staffId,
                'notes'           => "Delivery Exception Resolved [Cancelled]: {$notes}",
            ]);
            $msg = "Order #{$order->order_number} has been cancelled.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'status'  => $order->status,
            ]);
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Exception Resolved',
            'message' => $msg,
        ]);
    }

    /**
     * Generate printable local delivery run-sheet / manifest.
     */
    public function manifest(Order $order): View
    {
        $order->load(['user', 'items.product', 'fulfillment']);
        return view('order-manager.orders.manifest', compact('order'));
    }

    /**
     * Generate printable tax invoice.
     */
    public function invoice(Order $order): View
    {
        $order->load(['user', 'items.product', 'coupon']);
        return view('order-manager.orders.invoice', compact('order'));
    }

    /**
     * Generate printable warehouse packing slip.
     */
    public function packingSlip(Order $order): View
    {
        $order->load(['user', 'items.product', 'fulfillment']);
        return view('order-manager.orders.packing-slip', compact('order'));
    }

    /**
     * Generate 4" x 6" (100mm x 150mm) Thermal Shipping Label with Barcode & QR Code.
     */
    public function shippingLabel(Order $order): View
    {
        $order->load(['user', 'items.product', 'fulfillment', 'rider']);
        return view('order-manager.orders.shipping-label', compact('order'));
    }

    /**
     * Generate multi-page batch Thermal Shipping Labels for selected orders.
     */
    public function bulkShippingLabels(Request $request): View|RedirectResponse
    {
        $orderIds = $request->input('order_ids');
        if (is_string($orderIds)) {
            $orderIds = explode(',', $orderIds);
        }

        $orderIds = array_filter(array_map('intval', (array) $orderIds));

        if (empty($orderIds)) {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'No Orders Selected',
                'message' => 'Please select at least one order to print shipping labels.',
            ]);
        }

        $orders = Order::with(['user', 'items.product', 'fulfillment', 'rider'])
            ->whereIn('id', $orderIds)
            ->get();

        return view('order-manager.orders.bulk-shipping-labels', compact('orders'));
    }
}
