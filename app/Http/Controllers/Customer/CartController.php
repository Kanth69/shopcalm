<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        $subtotal = $this->cartService->subtotal();
        
        // Calculate Total MRP and Discount
        $totalMrp = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        $totalDiscount = $totalMrp - $subtotal;
        
        $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
        $grandTotal = max(0, $subtotal - $offerDiscount);
        
        return view('customer.cart', compact('cart', 'subtotal', 'totalMrp', 'totalDiscount', 'offerDiscount', 'grandTotal'));
    }

    public function add(StoreCartRequest $request)
    {
        $result = $this->cartService->addProduct($request->product_id, $request->quantity);

        if ($request->has('buy_now')) {
            return redirect()->route('checkout.index');
        }

        if ($request->ajax()) {
            $cart = $this->cartService->getCart();
            $cartItem = $cart->items->where('product_id', $request->product_id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Product added to bag successfully!',
                'cart_count' => $this->cartService->totalItems(),
                'product_id' => (int) $request->product_id,
                'item_id' => $cartItem ? $cartItem->id : null,
                'quantity' => $cartItem ? $cartItem->quantity : 0,
            ]);
        }

        return $result;
    }

    public function update(UpdateCartRequest $request, $itemId)
    {
        $result = $this->cartService->updateQuantity($itemId, $request->quantity);

        if ($request->ajax()) {
            $cart = $this->cartService->getCart();
            $cartItem = $cart->items->where('id', $itemId)->first();

            $subtotal = $this->cartService->subtotal();
            $totalMrp = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = $totalMrp - $subtotal;
            
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $grandTotal = max(0, $subtotal - $offerDiscount);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully!',
                'cart_count' => $this->cartService->totalItems(),
                'item_id' => (int) $itemId,
                'product_id' => $cartItem ? (int) $cartItem->product_id : null,
                'quantity' => $cartItem ? (int) $cartItem->quantity : 0,
                'subtotal' => $subtotal,
                'total_mrp' => $totalMrp,
                'total_discount' => $totalDiscount,
                'offer_discount' => $offerDiscount,
                'grand_total' => $grandTotal,
                'item_total' => $cartItem ? ($cartItem->quantity * $cartItem->unit_price) : 0,
            ]);
        }

        return $result;
    }

    public function remove($itemId, Request $request)
    {
        $result = $this->cartService->removeItem($itemId);

        if ($request->ajax()) {
            $cart = $this->cartService->getCart();
            $subtotal = $this->cartService->subtotal();
            $totalMrp = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = $totalMrp - $subtotal;
            
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $grandTotal = max(0, $subtotal - $offerDiscount);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart_count' => $this->cartService->totalItems(),
                'item_id' => (int) $itemId,
                'quantity' => 0,
                'subtotal' => $subtotal,
                'total_mrp' => $totalMrp,
                'total_discount' => $totalDiscount,
                'offer_discount' => $offerDiscount,
                'grand_total' => $grandTotal,
            ]);
        }

        return $result;
    }

    public function clear(Request $request)
    {
        $result = $this->cartService->clearCart();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared.',
                'cart_count' => 0,
                'subtotal' => 0,
            ]);
        }
        
        return $result;
    }
}
