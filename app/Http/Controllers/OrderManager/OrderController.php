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
            $method = strtolower($request->payment_method);
            if ($method === 'wallet') {
                $query->where(function ($q) {
                    $q->where('payment_method', 'wallet')
                      ->orWhere('payment_method', 'store_wallet')
                      ->orWhere('wallet_amount_used', '>', 0);
                });
            } elseif ($method === 'online') {
                $query->where(function ($q) {
                    $q->where('payment_method', 'online')
                      ->orWhere('payment_method', 'razorpay')
                      ->orWhere('payment_method', 'prepaid');
                });
            } else {
                $query->where('payment_method', $method);
            }
        }

        // 5. Payment Status Filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 6. Date Filter
        if ($request->filled('start_date') || $request->filled('end_date') || $request->filled('from_date') || $request->filled('to_date')) {
            $rawStart = $request->input('start_date') ?: $request->input('from_date');
            $rawEnd = $request->input('end_date') ?: $request->input('to_date');

            $start = $rawStart ? \Carbon\Carbon::parse($rawStart)->startOfDay() : null;
            $end = $rawEnd ? \Carbon\Carbon::parse($rawEnd)->endOfDay() : null;

            if ($start && $end && $start->gt($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            if ($start) {
                $query->where('created_at', '>=', $start);
            }
            if ($end) {
                $query->where('created_at', '<=', $end);
            }
        } elseif ($request->filled('date')) {
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

        $riders = User::deliveryPartners()->orderBy('name')->get();

        return view('order-manager.orders.index', compact('orders', 'statusCounts', 'riders'));
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

        // Delivery completion for local fleet / rider-assigned orders must be performed exclusively by the delivery partner via OTP verification
        if ($newStatus === 'delivered' && ($order->isLocalBengaluruDelivery() || $order->rider_id)) {
            $msg = "Delivery completion is restricted to the assigned delivery partner. The rider must verify the customer's Handover OTP at the doorstep to complete delivery.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

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
                if (!$order->cancellation) {
                    app(\App\Services\OrderService::class)->cancelOrderByAdmin($order, $notes ?: 'Cancelled by store management', 'wallet', $notes);
                }
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

            // Real-Time Push Notification to Customer App
            try {
                app(\App\Services\FcmNotificationService::class)->sendOrderNotification($order, $newStatus);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("[FCM] Order notification failed: " . $e->getMessage());
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
     * Trigger / Resend WhatsApp Out For Delivery message with Handover OTP to customer.
     */
    public function sendOtpWhatsApp(Request $request, Order $order): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $sent = app(\App\Services\WhatsAppService::class)->sendOutForDeliveryLocal($order);

        $customerPhone = $order->shipping_phone ?? $order->user?->mobile_number ?? 'Customer';
        $msg = $sent 
            ? "WhatsApp Out for Delivery notification has been sent to {$customerPhone}."
            : "Could not send automated WhatsApp message. Please check API credentials or use the 1-click WhatsApp button.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => $sent,
                'message' => $msg,
            ]);
        }

        return back()->with('toast', [
            'type'    => $sent ? 'success' : 'error',
            'title'   => $sent ? 'WhatsApp Dispatched' : 'Dispatch Failed',
            'message' => $msg,
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
                'message' => "Assigned to {$request->rider_name} ({$request->rider_phone}). Order #{$order->order_number} is now Shipped (Awaiting Hub Bag Scan)!",
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
            'title'   => 'Delivery Partner Assigned',
            'message' => "Assigned to {$request->rider_name} ({$request->rider_phone}). Order #{$order->order_number} is now Shipped (Awaiting Hub Bag Scan)!",
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
            'resolution_action' => ['required', 'in:re-attempt,reschedule,return_warehouse'],
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
            $slot = $request->delivery_slot ?? 'Express Delivery';
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

    /**
     * Display today's placed orders & quick dispatch hub.
     */
    public function todayOrders(Request $request): View
    {
        $query = Order::with(['user', 'items.product', 'fulfillment'])
            ->whereDate('created_at', today())
            ->latest('created_at');

        if ($request->filled('status')) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_city', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(30)->withQueryString();

        $todayAll = Order::whereDate('created_at', today())->get();
        $metrics = [
            'total_orders'     => $todayAll->count(),
            'total_revenue'    => (float) $todayAll->whereNotIn('status', ['cancelled', 'returned'])->sum('total_amount'),
            'needs_action'     => $todayAll->whereIn('status', ['pending', 'confirmed', 'processing'])->count(),
            'out_for_delivery' => $todayAll->whereIn('status', ['shipped', 'out for delivery'])->count(),
            'delivered'        => $todayAll->where('status', 'delivered')->count(),
            'cod_count'        => $todayAll->where('payment_method', 'cod')->count(),
            'prepaid_count'    => $todayAll->where('payment_method', '!=', 'cod')->count(),
        ];

        $riders = User::deliveryPartners()->orderBy('name')->get();

        return view('order-manager.orders.today', compact('orders', 'metrics', 'riders'));
    }

    /**
     * Export Orders to CSV / Excel spreadsheet.
     */
    public function exportCsv(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'fulfillment', 'rider']);

        if ($request->filled('order_ids')) {
            $ids = explode(',', $request->order_ids);
            $query->whereIn('id', array_map('intval', $ids));
        } elseif ($request->get('date') === 'today' || $request->has('today_only')) {
            $query->whereDate('created_at', today());
        } elseif ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
        }

        if ($request->filled('status')) {
            $query->whereIn('status', explode(',', $request->status));
        }

        $orders = $query->latest('created_at')->get();
        $filename = 'ShopCalm-Orders-' . ($request->has('today_only') || $request->get('date') === 'today' ? 'Today-' : '') . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->streamDownload(function () use ($orders) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'Order Number',
                'Date Placed',
                'Time',
                'Customer Name',
                'Customer Phone',
                'Customer Email',
                'Shipping Address',
                'City',
                'Pincode',
                'Items Count',
                'Products Ordered',
                'Total Amount (INR)',
                'Payment Mode',
                'Payment Status',
                'Order Status',
                'Assigned Rider / Courier',
                'Delivery Notes / Slot',
            ]);

            foreach ($orders as $o) {
                $itemsSummary = $o->items->map(function ($it) {
                    return "{$it->product_name} (x{$it->quantity})";
                })->implode('; ');

                fputcsv($output, [
                    $o->order_number,
                    $o->created_at->format('Y-m-d'),
                    $o->created_at->format('h:i A'),
                    $o->shipping_name,
                    $o->shipping_phone,
                    $o->shipping_email,
                    $o->shipping_address,
                    $o->shipping_city,
                    $o->shipping_zip,
                    $o->items->sum('quantity'),
                    $itemsSummary,
                    number_format($o->total_amount, 2, '.', ''),
                    strtoupper($o->payment_method),
                    strtoupper($o->payment_status ?? 'pending'),
                    ucwords($o->status),
                    $o->rider_name ?? ($o->courier_partner ? "{$o->courier_partner} ({$o->tracking_number})" : 'Unassigned'),
                    $o->notes ?? '',
                ]);
            }

            fclose($output);
        }, $filename, $headers);
    }

    /**
     * Generate printable daily dispatch run-sheet manifest for today's orders.
     */
    public function dailyManifest(Request $request): View
    {
        $query = Order::with(['user', 'items.product', 'fulfillment', 'rider']);

        if ($request->filled('order_ids')) {
            $ids = explode(',', $request->order_ids);
            $query->whereIn('id', array_map('intval', $ids));
        } else {
            $query->whereDate('created_at', today());
        }

        $orders = $query->latest('created_at')->get();
        return view('order-manager.orders.daily-manifest', compact('orders'));
    }

    /**
     * Bulk update order status for multiple selected orders.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'order_ids'  => ['required', 'string'],
            'new_status' => ['required', 'in:confirmed,processing,packed,shipped,out for delivery,delivered'],
        ]);

        $ids = array_filter(array_map('intval', explode(',', $request->order_ids)));
        if (empty($ids)) {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'No Orders Selected',
                'message' => 'Please select at least one order to update.',
            ]);
        }

        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();
        $newStatus = $request->new_status;
        $updatedCount = 0;

        $orders = Order::whereIn('id', $ids)->get();
        foreach ($orders as $order) {
            $oldStatus = $order->status;
            $order->update(['status' => $newStatus]);

            if ($order->fulfillment) {
                $fulfStatus = match($newStatus) {
                    'packed'           => 'packed',
                    'shipped'          => 'assigned',
                    'out for delivery' => 'out_for_delivery',
                    'delivered'        => 'delivered',
                    default            => 'processing',
                };
                $fulfData = ['status' => $fulfStatus];
                if ($newStatus === 'out for delivery' && empty($order->fulfillment->dispatched_at)) {
                    $fulfData['dispatched_at'] = now();
                }
                $order->fulfillment->update($fulfData);
            }

            $order->statusHistories()->create([
                'previous_status' => $oldStatus,
                'current_status'  => $newStatus,
                'changed_by'      => $staffId,
                'notes'           => "Bulk Status Update: Order moved to {$newStatus} by Order Manager.",
            ]);

            $updatedCount++;
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Bulk Update Successful',
            'message' => "Successfully updated {$updatedCount} orders to {$newStatus}!",
        ]);
    }

    /**
     * Bulk assign selected orders to a specific delivery partner.
     */
    public function bulkAssignRider(Request $request): RedirectResponse
    {
        $request->validate([
            'order_ids' => ['required', 'string'],
            'rider_id'  => ['required', 'exists:users,id'],
        ]);

        $ids = array_filter(array_map('intval', explode(',', $request->order_ids)));
        if (empty($ids)) {
            return back()->with('toast', [
                'type'    => 'error',
                'title'   => 'No Orders Selected',
                'message' => 'Please select at least one order to assign.',
            ]);
        }

        $rider = User::findOrFail($request->rider_id);
        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();

        $riderData = [
            'rider_id'      => $rider->id,
            'rider_name'    => $rider->name,
            'rider_phone'   => $rider->mobile_number,
            'delivery_slot' => 'Express Delivery (Today)',
            'notes'         => 'Assigned via Bulk Dispatch',
        ];

        $assignedCount = 0;
        $orders = Order::whereIn('id', $ids)->get();

        foreach ($orders as $order) {
            $this->fulfillmentService->assignLocalRider($order, $riderData, $staffId);
            $assignedCount++;
        }

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Bulk Rider Assignment Complete',
            'message' => "Assigned {$assignedCount} orders to {$rider->name}! Active in Rider App.",
        ]);
    }
}
