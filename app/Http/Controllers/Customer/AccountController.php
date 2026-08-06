<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function orders()
    {
        $orders = Auth::user()->orders()->with('items')->latest()->paginate(10);
        return view('customer.account.orders', compact('orders'));
    }

    public function showOrder(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(404);
        }
        return view('customer.account.order_details', compact('order'));
    }

    public function reviews()
    {
        $reviews = Auth::user()->reviews()->with('product')->latest()->paginate(10);
        return view('customer.account.reviews', compact('reviews'));
    }

    public function addresses()
    {
        return view('customer.account.addresses');
    }

    public function changePassword()
    {
        return view('customer.account.change_password');
    }
}
