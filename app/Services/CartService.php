<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart()
    {
        if (Auth::check()) {
            return Cart::with('items.product')->firstOrCreate(['user_id' => Auth::id()]);
        }

        $sessionId = Session::getId();
        return Cart::with('items.product')->firstOrCreate(['session_id' => $sessionId]);
    }

    public function addProduct(int $productId, int $quantity = 1)
    {
        $cart = $this->getCart();
        $product = Product::findOrFail($productId);

        if ($product->status !== 'Active' || $product->stock < $quantity) {
            return ['success' => false, 'type' => 'error', 'title' => 'Unavailable', 'message' => 'Product is unavailable or out of stock.'];
        }

        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($product->stock < $newQuantity) {
                return ['success' => false, 'type' => 'warning', 'title' => 'Limit Reached', 'message' => 'Cannot add more than available stock.'];
            }
            $cartItem->increment('quantity', $quantity);
        } else {
            $offerService = app(\App\Services\OfferService::class);
            $productWithOffer = $offerService->applyOfferDiscountsToProducts(collect([$product]))->first();
            $price = $productWithOffer->sale_price ?? $product->price;
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $price,
            ]);
        }

        return ['success' => true, 'type' => 'success', 'title' => 'Added to Bag', 'message' => "{$product->name} added to your cart."];
    }

    public function updateQuantity(int $itemId, int $quantity)
    {
        $cart = $this->getCart();
        $cartItem = $cart->items()->findOrFail($itemId);
        $product = $cartItem->product;

        if ($quantity < 1) {
            $success = $this->removeItem($itemId);
            return ['success' => $success, 'type' => $success ? 'success' : 'error', 'title' => $success ? 'Removed' : 'Error', 'message' => $success ? 'Item removed from cart.' : 'Item not found.'];
        }

        if ($product->stock < $quantity) {
            return ['success' => false, 'type' => 'error', 'title' => 'Error', 'message' => 'Cannot update to more than available stock.'];
        }

        $cartItem->update(['quantity' => $quantity]);
        return ['success' => true, 'type' => 'success', 'title' => 'Updated', 'message' => 'Cart updated.'];
    }

    public function removeItem(int $itemId)
    {
        $cart = $this->getCart();
        return $cart->items()->where('id', $itemId)->delete() > 0;
    }

    public function clearCart()
    {
        $cart = $this->getCart();
        return $cart->items()->delete() > 0;
    }

    public function subtotal()
    {
        return $this->getCart()->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function totalItems()
    {
        return $this->getCart()->items->sum('quantity');
    }

    public function mergeSessionCart(?string $guestSessionId = null)
    {
        if (Auth::check()) {
            $sessionId = $guestSessionId ?? Session::getId();
            $sessionCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
            $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            if ($sessionCart && $sessionCart->id !== $userCart->id) {
                foreach ($sessionCart->items as $sessionItem) {
                    $userItem = $userCart->items()->where('product_id', $sessionItem->product_id)->first();
                    if ($userItem) {
                        $userItem->increment('quantity', $sessionItem->quantity);
                    } else {
                        $userCart->items()->create([
                            'product_id' => $sessionItem->product_id,
                            'quantity' => $sessionItem->quantity,
                            'unit_price' => $sessionItem->unit_price,
                        ]);
                    }
                }
                $sessionCart->items()->delete();
                $sessionCart->delete();
            }
        }
    }
}
