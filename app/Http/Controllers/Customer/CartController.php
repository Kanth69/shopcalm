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
        $result = $this->cartService->addProduct($request->product_id, $request->quantity ?? 1);

        if ($request->has('buy_now') && $result['success']) {
            return redirect()->route('checkout.index');
        }

        if ($request->ajax()) {
            $cart = $this->cartService->getCart();
            $cartItem = $cart->items->where('product_id', $request->product_id)->first();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? 'Product added to bag successfully!',
                'cart_count' => $this->cartService->totalItems(),
                'product_id' => (int) $request->product_id,
                'item_id' => $cartItem ? $cartItem->id : null,
                'quantity' => $cartItem ? $cartItem->quantity : 0,
            ]);
        }

        return back()->with('toast', [
            'type' => $result['type'],
            'title' => $result['title'],
            'message' => $result['message']
        ]);
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
                'success' => $result['success'],
                'message' => $result['message'],
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

        return back()->with('toast', [
            'type' => $result['type'],
            'title' => $result['title'],
            'message' => $result['message']
        ]);
    }

    public function remove($itemId, Request $request)
    {
        $success = $this->cartService->removeItem($itemId);

        if ($request->ajax()) {
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart.'
                ]);
            }
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

        if ($success) {
            return back()->with('toast', ['type' => 'success', 'title' => 'Removed', 'message' => 'Item removed from cart.']);
        }
        return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'Item not found.']);
    }

    public function clear(Request $request)
    {
        $success = $this->cartService->clearCart();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared.',
                'cart_count' => 0,
                'subtotal' => 0,
            ]);
        }
        
        return back()->with('toast', ['type' => 'success', 'title' => 'Cleared', 'message' => 'Your cart is now empty.']);
    }
}
