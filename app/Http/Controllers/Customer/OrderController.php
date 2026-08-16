<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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

        $user           = Auth::user();
        $totalCount     = $user->orders()->count();
        $deliveredCount = $user->orders()->where('status', 'delivered')->count();
        $activeCount    = $user->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count();

        return view('customer.orders.index', compact('orders', 'totalCount', 'deliveredCount', 'activeCount'));
    }
}
