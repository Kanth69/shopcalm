<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    protected $cartService;
    protected $checkoutService;
    protected $couponService;

    public function __construct(CartService $cartService, CheckoutService $checkoutService, CouponService $couponService)
    {
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
        $this->couponService = $couponService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        if ($cart->items->isEmpty()) {
            return redirect()->route('home')->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'Your cart is empty.']);
        }

        $subtotal = $this->cartService->subtotal();
        
        $totalMrp = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        $totalDiscount = $totalMrp - $subtotal;

        $discountAmount = 0;
        $couponCode = Session::get('applied_coupon');
        $couponError = null;

        if ($couponCode) {
            try {
                $coupon = $this->couponService->validateCoupon($couponCode, Auth::user(), $subtotal, $cart);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotal);
            } catch (\Exception $e) {
                Session::forget('applied_coupon');
                $couponError = $e->getMessage();
                $couponCode = null;
            }
        }

        $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
        $grandTotal = max(0, $subtotal - $discountAmount - $offerDiscount);
        $addresses = Auth::user() ? Auth::user()->addresses()->latest()->get() : collect();

        $availableCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->startOfDay());
            })
            ->get();

        foreach ($availableCoupons as $c) {
            try {
                $this->couponService->validateCoupon($c->code, Auth::user(), $subtotal, $cart);
                $c->is_eligible = true;
                $c->ineligibility_reason = null;
                $c->calculated_discount = $this->couponService->calculateDiscount($c, $subtotal);
            } catch (\Exception $e) {
                $c->is_eligible = false;
                $c->ineligibility_reason = $e->getMessage();
                $c->calculated_discount = 0;
            }
        }

        return view('customer.checkout.index', compact('cart', 'subtotal', 'totalMrp', 'totalDiscount', 'discountAmount', 'offerDiscount', 'grandTotal', 'couponCode', 'couponError', 'addresses', 'availableCoupons'));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => 'required|string']);

        $cart = $this->cartService->getCart();
        $subtotal = $this->cartService->subtotal();

        try {
            $coupon = $this->couponService->validateCoupon($request->coupon_code, Auth::user(), $subtotal, $cart);
            $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotal);
            Session::put('applied_coupon', $coupon->code);

            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $grandTotal = max(0, $subtotal - $discountAmount - $offerDiscount);
            
            $totalMrp = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = $totalMrp - $subtotal;
            $totalSavings = $totalDiscount + $discountAmount + $offerDiscount;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Coupon applied successfully!',
                    'coupon_code' => $coupon->code,
                    'discount_amount' => $discountAmount,
                    'formatted_discount' => number_format($discountAmount, 2),
                    'subtotal' => number_format($subtotal, 2),
                    'grand_total' => number_format($grandTotal, 2),
                    'total_savings' => number_format($totalSavings, 2),
                ]);
            }

            return back()->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Coupon applied successfully!']);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        }
    }

    public function removeCoupon(Request $request)
    {
        Session::forget('applied_coupon');
        $cart = $this->cartService->getCart();
        $subtotal = $this->cartService->subtotal();
        
        $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
        $grandTotal = max(0, $subtotal - $offerDiscount);
        
        $totalMrp = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        $totalDiscount = $totalMrp - $subtotal;
        $totalSavings = $totalDiscount + $offerDiscount;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon removed.',
                'subtotal' => number_format($subtotal, 2),
                'grand_total' => number_format($grandTotal, 2),
                'total_savings' => number_format($totalSavings, 2)
            ]);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Removed', 'message' => 'Coupon removed.']);
    }

    public function saveAddress(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $user = Auth::guard('customer')->user() ?? Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in to save your address.'
            ], 401);
        }

        $newAddress = $user->addresses()->create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'zip' => $validated['zip'],
            'country' => $validated['country'] ?? 'India',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address saved successfully!',
            'address' => $newAddress,
            'all_addresses' => $user->addresses()->latest()->get()
        ]);
    }

    public function placeOrder(PlaceOrderRequest $request)
    {
        try {
            $order = $this->checkoutService->placeOrder($request->validated());
            Session::forget('applied_coupon');
            return redirect()->route('checkout.success', $order);
        } catch (\Exception $e) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        }
    }

    public function success(Order $order)
    {
        return view('customer.checkout.success', compact('order'));
    }
}
