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
        // Clear transient Buy Now session so permanent DB cart is displayed
        \Illuminate\Support\Facades\Session::forget('buy_now_data');

        $cart = $this->cartService->getCart();
        $subtotal = $this->cartService->subtotal();
        
        $selectedItems = $cart->items->where('is_selected', true);
        // Calculate Total MRP and Discount for selected items
        $totalMrp = $selectedItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        $totalDiscount = max(0, $totalMrp - $subtotal);
        
        $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
        $grandTotal = max(0, $subtotal - $offerDiscount);
        
        return view('customer.cart', compact('cart', 'subtotal', 'totalMrp', 'totalDiscount', 'offerDiscount', 'grandTotal'));
    }

    public function toggleSelect($itemId, Request $request)
    {
        $isSelected = $request->has('is_selected') ? (bool) $request->is_selected : null;
        $success = $this->cartService->toggleSelect($itemId, $isSelected);

        if ($request->ajax()) {
            $cart = $this->cartService->getCart();
            $subtotal = $this->cartService->subtotal();
            
            $selectedItems = $cart->items->where('is_selected', true);
            $totalMrp = $selectedItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = max(0, $totalMrp - $subtotal);
            
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $grandTotal = max(0, $subtotal - $offerDiscount);

            return response()->json([
                'success' => $success,
                'cart_count' => $this->cartService->totalItems(),
                'all_cart_count' => $this->cartService->allItemsCount(),
                'item_id' => (int) $itemId,
                'subtotal' => $subtotal,
                'total_mrp' => $totalMrp,
                'total_discount' => $totalDiscount,
                'offer_discount' => $offerDiscount,
                'grand_total' => $grandTotal,
            ]);
        }

        return back();
    }

    public function toggleSelectAll(Request $request)
    {
        $isSelected = (bool) $request->input('is_selected', true);
        $this->cartService->toggleSelectAll($isSelected);

        if ($request->ajax()) {
            $cart = $this->cartService->getCart();
            $subtotal = $this->cartService->subtotal();
            
            $selectedItems = $cart->items->where('is_selected', true);
            $totalMrp = $selectedItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = max(0, $totalMrp - $subtotal);
            
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $grandTotal = max(0, $subtotal - $offerDiscount);

            return response()->json([
                'success' => true,
                'cart_count' => $this->cartService->totalItems(),
                'all_cart_count' => $this->cartService->allItemsCount(),
                'subtotal' => $subtotal,
                'total_mrp' => $totalMrp,
                'total_discount' => $totalDiscount,
                'offer_discount' => $offerDiscount,
                'grand_total' => $grandTotal,
            ]);
        }

        return back();
    }

    public function add(StoreCartRequest $request)
    {
        $isBuyNow = $request->has('buy_now') && (bool) $request->buy_now;

        if ($isBuyNow) {
            $result = $this->cartService->setBuyNowSession(
                (int) $request->product_id,
                (int) ($request->quantity ?? 1),
                $request->selected_option
            );

            if ($request->ajax()) {
                return response()->json([
                    'success'      => $result['success'],
                    'message'      => $result['message'] ?? 'Proceeding to checkout...',
                    'cart_count'   => $this->cartService->allItemsCount(),
                    'product_id'   => (int) $request->product_id,
                    'redirect_url' => $result['success'] ? route('checkout.index') : null,
                ]);
            }

            if ($result['success']) {
                return redirect()->route('checkout.index');
            }

            return back()->with('toast', [
                'type'    => $result['type'],
                'title'   => $result['title'],
                'message' => $result['message']
            ]);
        }

        $result = $this->cartService->addProduct(
            (int) $request->product_id,
            (int) ($request->quantity ?? 1),
            $request->selected_option
        );

        if ($request->ajax()) {
            $cart = $this->cartService->getCart();
            $cartItem = $cart->items->where('product_id', $request->product_id)->first();

            return response()->json([
                'success'      => $result['success'],
                'message'      => $result['message'] ?? 'Product added to bag successfully!',
                'cart_count'   => $this->cartService->allItemsCount(),
                'product_id'   => (int) $request->product_id,
                'item_id'      => $cartItem ? $cartItem->id : null,
                'quantity'     => $cartItem ? $cartItem->quantity : 0,
                'redirect_url' => null,
            ]);
        }

        return back()->with('toast', [
            'type'    => $result['type'],
            'title'   => $result['title'],
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
            $totalMrp = $cart->items->where('is_selected', true)->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = max(0, $totalMrp - $subtotal);
            
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
            
            $totalMrp = $cart->items->where('is_selected', true)->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = max(0, $totalMrp - $subtotal);
            
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

    public function moveToWishlist($itemId, Request $request)
    {
        if (!auth()->check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'require_login' => true,
                    'message' => 'Please log in to save items to your wishlist.'
                ], 401);
            }
            return redirect()->route('login')->with('toast', [
                'type' => 'info',
                'title' => 'Login Required',
                'message' => 'Please log in to save items to your wishlist.'
            ]);
        }

        $cart = $this->cartService->getCart();
        $cartItem = $cart->items->where('id', $itemId)->first();

        if (!$cartItem) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cart item not found.']);
            }
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'Item not found in cart.']);
        }

        $wishlist = auth()->user()->wishlist()->firstOrCreate();
        \App\Models\WishlistItem::firstOrCreate([
            'wishlist_id' => $wishlist->id,
            'product_id'  => $cartItem->product_id,
        ]);

        $this->cartService->removeItem($itemId);

        if ($request->ajax()) {
            $cart = $this->cartService->getCart();
            $subtotal = $this->cartService->subtotal();
            
            $totalMrp = $cart->items->where('is_selected', true)->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });
            $totalDiscount = max(0, $totalMrp - $subtotal);
            
            $offerDiscount = app(\App\Services\OfferService::class)->calculateCheckoutOfferDiscount($cart);
            $grandTotal = max(0, $subtotal - $offerDiscount);

            return response()->json([
                'success' => true,
                'message' => 'Item moved to your wishlist.',
                'cart_count' => $this->cartService->totalItems(),
                'item_id' => (int) $itemId,
                'subtotal' => $subtotal,
                'total_mrp' => $totalMrp,
                'total_discount' => $totalDiscount,
                'offer_discount' => $offerDiscount,
                'grand_total' => $grandTotal,
            ]);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Moved', 'message' => 'Item moved to your wishlist.']);
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
