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

        // 2. Riders with pending COD cash to deposit
        $riders = $deliveryPartners->map(function ($rider) {
            $pendingOrders = Order::with('fulfillment')
                ->whereHas('fulfillment', fn($q) => $q->where('rider_id', $rider->id)->whereNull('cod_deposited_at'))
                ->where('status', 'delivered')
                ->where('payment_method', 'cod')
                ->latest('updated_at')
                ->get();

            $rider->pending_cod_amount = $pendingOrders->sum('total_amount');
            $rider->pending_cod_orders = $pendingOrders;
            $rider->pending_cod_count  = $pendingOrders->count();
            $rider->last_settlement    = CashSettlement::where('rider_id', $rider->id)->latest()->first();

            return $rider;
        });

        // 3. Core Financial KPIs (100% synchronized with rider balances)
        $floatingCashTotal     = $riders->sum('pending_cod_amount');
        $pendingRidersCount    = $riders->where('pending_cod_count', '>', 0)->count();
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
            ->where('payment_method', 'cod')
            ->get();

        if ($pendingOrders->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Rider {$rider->name} has no pending COD cash to deposit.",
                ], 422);
            }
            return back()->withErrors(['amount' => "Rider {$rider->name} has no pending COD cash to deposit."]);
        }

        $settlement = DB::transaction(function () use ($rider, $staffId, $pendingOrders, $request) {
            $settlementNumber = CashSettlement::generateSettlementNumber();

            $settlement = CashSettlement::create([
                'settlement_number' => $settlementNumber,
                'rider_id'          => $rider->id,
                'received_by'       => $staffId,
                'total_amount'      => $request->amount,
                'order_count'       => $pendingOrders->count(),
                'payment_mode'      => $request->payment_mode,
                'notes'             => $request->notes,
            ]);

            // Update all linked fulfillments
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
}
