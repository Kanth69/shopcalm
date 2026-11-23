<?php

namespace App\Http\Controllers\OrderManager;

use App\Http\Controllers\Controller;
use App\Models\CashSettlement;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettlementController extends Controller
{
    /**
     * Display the Fleet COD Cash Settlements Command Center.
     */
    public function index(Request $request): View
    {
        // 1. Auto-link legacy orders where rider_id was not set but rider_name matches a registered delivery partner
        $deliveryPartners = User::deliveryPartners()->get();
        foreach ($deliveryPartners as $dp) {
            $firstName = explode(' ', trim($dp->name))[0];
            if (!empty($firstName)) {
                $fulfs = OrderFulfillment::where('status', 'delivered')
                    ->where(function($q) {
                        $q->whereNull('rider_id')->orWhere('rider_id', 0);
                    })
                    ->where('rider_name', 'like', "%{$firstName}%")
                    ->get();

                foreach ($fulfs as $f) {
                    $f->update(['rider_id' => $dp->id]);
                }
            }
        }

        // 2. Riders with pending COD cash to deposit or undelivered returned parcels
        $riders = $deliveryPartners->map(function ($rider) {
            $pendingOrders = Order::with('fulfillment')
                ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
                ->where('status', 'delivered')
                ->whereIn('payment_method', ['cod', 'cash'])
                ->latest('updated_at')
                ->get();

            // Undelivered physical boxes in bag awaiting return to hub
            $undeliveredOrders = Order::with('fulfillment')
                ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->where('status', '!=', 'returned')->whereNull('returned_at'))
                ->where('status', '!=', 'delivered')
                ->latest('updated_at')
                ->get();

            $rider->pending_cod_amount = (float) $pendingOrders->sum('total_amount');
            $rider->pending_cod_orders = $pendingOrders;
            $rider->pending_cod_count  = $pendingOrders->count();
            $rider->undelivered_orders = $undeliveredOrders;
            $rider->undelivered_count  = $undeliveredOrders->count();
            $rider->needs_settlement   = ($rider->pending_cod_amount > 0 || $rider->undelivered_count > 0);
            $rider->last_settlement    = CashSettlement::where('rider_id', $rider->id)->latest()->first();

            return $rider;
        });

        // 3. Core Financial KPIs (100% synchronized with rider balances)
        $floatingCashTotal     = $riders->sum('pending_cod_amount');
        $pendingRidersCount    = $riders->where('needs_settlement', true)->count();
        $totalUndeliveredBoxes = $riders->sum('undelivered_count');
        $todayDepositedTotal   = CashSettlement::whereDate('created_at', today())->sum('total_amount');
        $allTimeDepositedTotal = CashSettlement::sum('total_amount');

        // 4. Historical settlement records (with Date Filtering)
        $settlementsQuery = CashSettlement::with(['rider', 'receivedBy', 'fulfillments.order'])->latest();

        if ($request->filled('date_from')) {
            $settlementsQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $settlementsQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $settlements = $settlementsQuery->paginate(15)->withQueryString();

        return view('order-manager.settlements.index', compact(
            'floatingCashTotal',
            'todayDepositedTotal',
            'allTimeDepositedTotal',
            'pendingRidersCount',
            'totalUndeliveredBoxes',
            'riders',
            'settlements'
        ));
    }

    /**
     * Process and record a rider's cash deposit at warehouse counter.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'rider_id'     => ['required', 'exists:users,id'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'payment_mode' => ['required', 'in:cash,upi,qr,bank_transfer'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $rider = User::findOrFail($request->rider_id);
        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();

        // Fetch all delivered COD orders for this rider awaiting deposit
        $pendingOrders = Order::with('fulfillment')
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
            ->where('status', 'delivered')
            ->whereIn('payment_method', ['cod', 'cash'])
            ->get();

        // Undelivered physical boxes in bag awaiting return to hub
        $undeliveredOrders = Order::with('fulfillment')
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->where('status', '!=', 'returned')->whereNull('returned_at'))
            ->where('status', '!=', 'delivered')
            ->get();

        if ($pendingOrders->isEmpty() && $undeliveredOrders->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Rider {$rider->name} has no pending cash or returned parcels to settle.",
                ], 422);
            }
            return back()->withErrors(['amount' => "Rider {$rider->name} has no pending cash or returned parcels to settle."]);
        }

        $settlement = DB::transaction(function () use ($rider, $staffId, $pendingOrders, $undeliveredOrders, $request) {
            $settlementNumber = CashSettlement::generateSettlementNumber();

            $settlement = CashSettlement::create([
                'settlement_number' => $settlementNumber,
                'rider_id'          => $rider->id,
                'received_by'       => $staffId,
                'total_amount'      => (float) $request->amount,
                'order_count'       => $pendingOrders->count(),
                'payment_mode'      => $request->payment_mode,
                'notes'             => $request->notes,
            ]);

            // Update all linked delivered fulfillments (cash collected in vault)
            foreach ($pendingOrders as $order) {
                if ($order->fulfillment) {
                    $order->fulfillment->update([
                        'cod_settlement_id' => $settlement->id,
                        'cod_deposited_at'  => now(),
                    ]);
                }

                $order->statusHistories()->create([
                    'previous_status' => $order->status,
                    'current_status'  => $order->status,
                    'changed_by'      => $staffId,
                    'notes'           => "COD Cash ₹" . number_format($order->total_amount, 2) . " deposited at hub counter (Voucher: {$settlementNumber}).",
                ]);
            }

            // Also mark any verified returned boxes into hub stock
            $staffUser = User::find($staffId);
            $staffName = $staffUser?->name ?? 'Hub Manager';
            $returnedOrderNumbers = $request->input('returned_orders', []);

            if (!empty($returnedOrderNumbers)) {
                $boxesToReturn = Order::with('fulfillment')
                    ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id))
                    ->whereIn('order_number', (array) $returnedOrderNumbers)
                    ->get();

                foreach ($boxesToReturn as $retOrder) {
                    if ($retOrder->fulfillment) {
                        $retOrder->fulfillment->update([
                            'status'                     => 'returned',
                            'returned_at'                => now(),
                            'returned_to'                => $staffId,
                            'delivery_issue_resolved_at' => now(),
                            'delivery_issue_resolution'  => 'Physically returned to Hub inventory by Rider',
                            'notes'                      => trim(($retOrder->fulfillment->notes ?? '') . " | [RETURNED_TO_HUB:{$staffName}]"),
                        ]);
                    }

                    $retOrder->statusHistories()->create([
                        'previous_status' => $retOrder->status,
                        'current_status'  => $retOrder->status,
                        'changed_by'      => $staffId,
                        'notes'           => "Parcel #{$retOrder->order_number} physically returned to Hub inventory by Rider {$rider->name}. Received by {$staffName}.",
                    ]);
                }
            }

            return $settlement;
        });

        $msg = "Cash Deposit of ₹" . number_format($settlement->total_amount, 2) . " accepted for {$rider->name}! Receipt #{$settlement->settlement_number} generated.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'           => true,
                'message'           => $msg,
                'settlement_number' => $settlement->settlement_number,
                'receipt_url'       => route('order-manager.settlements.show', $settlement),
            ]);
        }

        return redirect()->route('order-manager.settlements.show', $settlement)->with('toast', [
            'type'    => 'success',
            'title'   => 'Cash Deposit Confirmed',
            'message' => $msg,
        ]);
    }

    /**
     * Display printable official settlement receipt.
     */
    public function show(CashSettlement $settlement): View
    {
        $settlement->load(['rider', 'receivedBy', 'fulfillments.order.items.product']);

        return view('order-manager.settlements.receipt', compact('settlement'));
    }

    /**
     * Instant lookup of a rider by QR payload, Rider ID, or 6-digit Handover PIN.
     */
    public function lookup(Request $request): JsonResponse
    {
        $query = trim($request->input('query', ''));
        if (empty($query)) {
            return response()->json(['success' => false, 'message' => 'Please provide a scan code or PIN.'], 422);
        }

        $rider = null;

        // 1. Try decoding as JSON QR payload
        if (str_starts_with($query, '{') && str_ends_with($query, '}')) {
            $decoded = json_decode($query, true);
            if (!empty($decoded['rider_id'])) {
                $rider = User::find($decoded['rider_id']);
            }
        }

        // 2. Try as Rider ID or mobile
        if (!$rider && is_numeric($query)) {
            $num = (int) $query;
            $rider = User::where('id', $num)->orWhere('mobile_number', $query)->first();
        }

        // 3. Try matching by 6-digit PIN across active delivery partners
        if (!$rider && strlen($query) === 6 && ctype_digit($query)) {
            $deliveryPartners = User::deliveryPartners()->get();
            foreach ($deliveryPartners as $dp) {
                $expectedPin = sprintf('%06d', (($dp->id * 7919) + (int) date('ymd')) % 1000000);
                if ($expectedPin === $query) {
                    $rider = $dp;
                    break;
                }
            }
        }

        if (!$rider) {
            return response()->json(['success' => false, 'message' => 'Rider not found for this scan or PIN.'], 404);
        }

        $pendingOrders = Order::with('fulfillment')
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
            ->where('status', 'delivered')
            ->whereIn('payment_method', ['cod', 'cash'])
            ->latest('updated_at')
            ->get();

        $undeliveredOrders = Order::with('fulfillment')
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->where('status', '!=', 'returned')->whereNull('returned_at'))
            ->where('status', '!=', 'delivered')
            ->latest('updated_at')
            ->get();

        return response()->json([
            'success'            => true,
            'rider'              => [
                'id'            => $rider->id,
                'name'          => $rider->name,
                'mobile_number' => $rider->mobile_number,
            ],
            'pending_amount'     => (float) $pendingOrders->sum('total_amount'),
            'formatted_amount'   => '₹' . number_format($pendingOrders->sum('total_amount'), 2),
            'order_count'        => $pendingOrders->count(),
            'orders'             => $pendingOrders->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'amount'       => (float) $o->total_amount,
                'customer'     => $o->shipping_name,
            ]),
            'undelivered_count'  => $undeliveredOrders->count(),
            'undelivered_orders' => $undeliveredOrders->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'amount'       => (float) $o->total_amount,
                'customer'     => $o->shipping_name,
                'issue_reason' => $o->fulfillment?->delivery_issue ?? 'Undelivered',
                'status'       => $o->status,
            ]),
        ]);
    }

    /**
     * Instant barcode scan intake of an undelivered physical box at hub counter.
     */
    public function receiveReturnBox(Request $request): JsonResponse
    {
        $request->validate([
            'rider_id'     => ['required', 'exists:users,id'],
            'order_number' => ['required', 'string'],
        ]);

        $rider = User::findOrFail($request->rider_id);
        $orderNumber = trim($request->order_number);
        $staffId = Auth::guard('order_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id();
        $staffUser = User::find($staffId);
        $staffName = $staffUser?->name ?? 'Hub Manager';

        $order = Order::with('fulfillment')
            ->where('order_number', $orderNumber)
            ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id))
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => "Order #{$orderNumber} is not found in Rider {$rider->name}'s run sheet.",
            ], 404);
        }

        if ($order->fulfillment?->status === 'returned' || $order->fulfillment?->returned_at !== null) {
            return response()->json([
                'success'          => true,
                'already_returned' => true,
                'message'          => "Box #{$orderNumber} was already scanned & accepted into Hub inventory.",
                'order'            => [
                    'order_number' => $order->order_number,
                    'customer'     => $order->shipping_name,
                ],
            ]);
        }

        DB::transaction(function () use ($order, $rider, $staffId, $staffName) {
            if ($order->fulfillment) {
                $order->fulfillment->update([
                    'status'                     => 'returned',
                    'returned_at'                => now(),
                    'returned_to'                => $staffId,
                    'delivery_issue_resolved_at' => now(),
                    'delivery_issue_resolution'  => 'Physically returned to Hub inventory by Rider',
                    'notes'                      => trim(($order->fulfillment->notes ?? '') . " | [RETURNED_TO_HUB:{$staffName}]"),
                ]);
            }

            $order->statusHistories()->create([
                'previous_status' => $order->status,
                'current_status'  => $order->status,
                'changed_by'      => $staffId,
                'notes'           => "Parcel #{$order->order_number} physically returned to Hub inventory by Rider {$rider->name}. Received by {$staffName}.",
            ]);
        });

        // Calculate remaining undelivered boxes
        $remainingCount = Order::whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->where('status', '!=', 'returned')->whereNull('returned_at'))
            ->where('status', '!=', 'delivered')
            ->count();

        return response()->json([
            'success'         => true,
            'message'         => "Box #{$order->order_number} accepted into Hub stock!",
            'remaining_count' => $remainingCount,
            'order'           => [
                'order_number' => $order->order_number,
                'customer'     => $order->shipping_name,
                'amount'       => (float) $order->total_amount,
                'reason'       => $order->fulfillment?->delivery_issue ?? 'Undelivered',
            ],
        ]);
    }
}
