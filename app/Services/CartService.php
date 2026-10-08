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
        $userId = Auth::id() ?? (Auth::guard('sanctum')->id() ?? request()->user('sanctum')?->id);

        if ($userId) {
            if (request()->header('X-Device-Id')) {
                $this->mergeSessionCart(request()->header('X-Device-Id'));
            }
            $cart = Cart::with('items.product.brand')->firstOrCreate(['user_id' => $userId]);
        } else {
            $sessionId = request()->header('X-Device-Id') ?: Session::getId();
            $cart = Cart::with('items.product.brand')->firstOrCreate(['session_id' => $sessionId]);
        }

        $this->sanitizeCartQuantities($cart);
        return $cart;
    }

    public function sanitizeCartQuantities(Cart $cart): void
    {
        if (!$cart->relationLoaded('items')) {
            $cart->load('items.product');
        }

        foreach ($cart->items as $item) {
            if (!$item->product || $item->product->status !== 'Active') {
                continue;
            }

            $maxStock = $item->product->getOptionStock($item->selected_option);

            if ($maxStock > 0 && $item->quantity > $maxStock) {
                $item->quantity = $maxStock;
                $item->save();
            }
        }
    }

    public function setBuyNowSession(int $productId, int $quantity = 1, ?string $selectedOption = null)
    {
        $product = Product::findOrFail($productId);

        if ($product->status !== 'Active') {
            return ['success' => false, 'type' => 'error', 'title' => 'Unavailable', 'message' => 'Product is currently inactive.'];
        }

        $maxStock = $product->getOptionStock($selectedOption);

        if ($maxStock < 1) {
            return ['success' => false, 'type' => 'error', 'title' => 'Out of Stock', 'message' => 'Product is currently out of stock.'];
        }

        Session::put('buy_now_data', [
            'product_id'      => $productId,
            'quantity'        => min($quantity, $maxStock),
            'selected_option' => $selectedOption,
        ]);

        return ['success' => true, 'type' => 'success', 'title' => 'Instant Buy', 'message' => 'Proceeding to Checkout.'];
    }

    public function getSelectedCart()
    {
        if (Session::has('buy_now_data')) {
            $data = Session::get('buy_now_data');
            $product = Product::with(['category', 'brand'])->find($data['product_id']);
            if ($product && $product->status === 'Active') {
                $maxStock = $product->getOptionStock($data['selected_option']);
                if ($maxStock >= 1) {
                    $qty = min($data['quantity'], $maxStock);
                    $offerService = app(\App\Services\OfferService::class);
                    $productWithOffer = $offerService->applyOfferDiscountsToProducts(collect([$product]))->first();
                    $price = $productWithOffer->sale_price ?? $product->price;

                    $virtualItem = new \App\Models\CartItem([
                        'product_id'      => $product->id,
                        'quantity'        => $qty,
                        'unit_price'      => $price,
                        'selected_option' => $data['selected_option'],
                        'is_selected'     => true,
                    ]);
                    $virtualItem->id = 999999;
                    $virtualItem->setRelation('product', $productWithOffer);

                    $virtualCart = new Cart();
                    $virtualCart->setRelation('items', collect([$virtualItem]));
                    return $virtualCart;
                }
            }
            Session::forget('buy_now_data');
        }

        $cart = $this->getCart();
        $cart->load(['items' => function ($query) {
            $query->where('is_selected', true)->with(['product.category', 'product.brand']);
        }]);
        return $cart;
    }

    public function addProduct(int $productId, int $quantity = 1, ?string $selectedOption = null, bool $isBuyNow = false)
    {
        $cart = $this->getCart();
        $product = Product::findOrFail($productId);

        if ($product->status !== 'Active') {
            return ['success' => false, 'type' => 'error', 'title' => 'Unavailable', 'message' => 'Product is currently inactive.'];
        }

        $maxStock = $product->getOptionStock($selectedOption);

        if ($maxStock < 1) {
            return ['success' => false, 'type' => 'error', 'title' => 'Out of Stock', 'message' => 'Product is currently out of stock.'];
        }

        if ($isBuyNow) {
            // Deselect all existing cart items so ONLY this product is checked out
            $cart->items()->update(['is_selected' => false]);
        }

        $query = $cart->items()->where('product_id', $productId);
        if ($selectedOption) {
            $query->where('selected_option', $selectedOption);
        } else {
            $query->whereNull('selected_option');
        }
        $cartItem = $query->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > $maxStock) {
                if ($cartItem->quantity >= $maxStock) {
                    $cartItem->update(['is_selected' => true]);
                    return [
                        'success' => true,
                        'type'    => 'warning',
                        'title'   => 'Stock Limit Reached',
                        'message' => "Selected maximum available stock ({$maxStock} items) for checkout."
                    ];
                }
                $cartItem->update(['quantity' => $maxStock, 'is_selected' => true]);
                return [
                    'success' => true,
                    'type'    => 'warning',
                    'title'   => 'Quantity Adjusted',
                    'message' => "Cart quantity set to maximum available stock ({$maxStock} items available)."
                ];
            }
            $cartItem->increment('quantity', $quantity);
            $cartItem->update(['is_selected' => true]);
        } else {
            $finalQty = min($quantity, $maxStock);
            $offerService = app(\App\Services\OfferService::class);
            $productWithOffer = $offerService->applyOfferDiscountsToProducts(collect([$product]))->first();
            $price = $productWithOffer->sale_price ?? $product->price;
            $cart->items()->create([
                'product_id'      => $productId,
                'quantity'        => $finalQty,
                'unit_price'      => $price,
                'selected_option' => $selectedOption,
                'is_selected'     => true,
            ]);
        }

        $optionLabel = $selectedOption ? " ({$selectedOption})" : "";
        return ['success' => true, 'type' => 'success', 'title' => 'Added to Bag', 'message' => "{$product->name}{$optionLabel} added to your cart."];
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

        $maxStock = $product->getOptionStock($cartItem->selected_option);

        if ($quantity > $maxStock) {
            return ['success' => false, 'type' => 'error', 'title' => 'Stock Limit Reached', 'message' => "Cannot update to more than available stock ({$maxStock} available)."];
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

    public function clearSelectedItems()
    {
        if (Session::has('buy_now_data')) {
            Session::forget('buy_now_data');
            return true;
        }

        $cart = $this->getCart();
        return $cart->items()->where('is_selected', true)->delete() > 0;
    }

    public function toggleSelect(int $itemId, ?bool $isSelected = null)
    {
        $cart = $this->getCart();
        $cartItem = $cart->items()->find($itemId);
        if (!$cartItem) {
            return false;
        }

        $newVal = $isSelected !== null ? (bool) $isSelected : !$cartItem->is_selected;
        $cartItem->update(['is_selected' => $newVal]);
        return true;
    }

    public function toggleSelectAll(bool $isSelected)
    {
        $cart = $this->getCart();
        $cart->items()->update(['is_selected' => (bool) $isSelected]);
        return true;
    }

    public function subtotal()
    {
        return $this->getSelectedCart()->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function totalItems()
    {
        return $this->getCart()->items->where('is_selected', true)->sum('quantity');
    }

    public function allItemsCount()
    {
        return $this->getCart()->items->sum('quantity');
    }

    public function mergeSessionCart(?string $guestSessionId = null)
    {
        $userId = Auth::id() ?? (Auth::guard('sanctum')->id() ?? request()->user('sanctum')?->id);

        if ($userId) {
            $sessionId = $guestSessionId ?? (request()->header('X-Device-Id') ?: Session::getId());
            $sessionCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
            $userCart = Cart::firstOrCreate(['user_id' => $userId]);

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
