<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends BaseApiController
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Helper to format database Cart model into standardized mobile API response.
     */
    protected function formatCartResponse(Cart $cart): array
    {
        $cart->load(['items.product']);

        $items = [];
        $subtotal = 0.00;
        $totalSavings = 0.00;

        foreach ($cart->items as $item) {
            $product = $item->product;
            $unitPrice = (float) $item->unit_price;
            $originalPrice = (float) ($product ? $product->price : $unitPrice);
            $itemTotal = round($unitPrice * $item->quantity, 2);

            if ($item->is_selected) {
                $subtotal += $itemTotal;
                if ($originalPrice > $unitPrice) {
                    $totalSavings += ($originalPrice - $unitPrice) * $item->quantity;
                }
            }

            $imgPath = $product ? ($product->main_image ?? $product->featured_image ?? null) : null;
            $items[] = [
                'cart_item_id'    => (int) $item->id,
                'product_id'      => (int) $item->product_id,
                'name'            => $product ? $product->name : 'Product',
                'slug'            => $product ? $product->slug : '',
                'quantity'        => (int) $item->quantity,
                'price'           => $unitPrice,
                'original_price'  => $originalPrice,
                'image_url'       => $imgPath ? (str_starts_with($imgPath, 'http') ? $imgPath : asset('storage/' . $imgPath)) : null,
                'selected_option' => $item->selected_option,
                'is_selected'     => (bool) $item->is_selected,
                'total'           => $itemTotal,
                'in_stock'        => $product ? ($product->stock > 0 && $product->status === 'Active') : false,
                'max_available'   => $product ? $product->getOptionStock($item->selected_option) : 0,
            ];
        }

        $freeShippingMin = (float) Setting::get('free_shipping_min', 499);
        $deliveryCharge = ($subtotal >= $freeShippingMin || count($items) === 0) ? 0.00 : 40.00;
        $grandTotal = $subtotal + $deliveryCharge;

        return [
            'cart_id'                 => (int) $cart->id,
            'items'                   => $items,
            'item_count'              => count($items),
            'total_quantity'          => array_sum(array_column($items, 'quantity')),
            'selected_items_count'    => count(array_filter($items, fn($i) => $i['is_selected'])),
            'subtotal'                => round($subtotal, 2),
            'total_savings'           => round($totalSavings, 2),
            'delivery_charge'         => round($deliveryCharge, 2),
            'free_shipping_min'       => $freeShippingMin,
            'qualifies_free_shipping' => ($subtotal >= $freeShippingMin),
            'grand_total'             => round($grandTotal, 2),
        ];
    }

    /**
     * Get Current Cart Contents & Summary.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart();
        return $this->sendResponse($this->formatCartResponse($cart), 'Cart retrieved successfully.');
    }

    /**
     * Add Product Item to Cart.
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'quantity'        => 'required|integer|min:1',
            'selected_option' => 'nullable|string',
            'is_buy_now'      => 'nullable|boolean',
        ]);

        $result = $this->cartService->addProduct(
            (int) $request->product_id,
            (int) $request->quantity,
            $request->selected_option,
            (bool) $request->input('is_buy_now', false)
        );

        if (!($result['success'] ?? false)) {
            return $this->sendError($result['message'] ?? 'Unable to add product to cart.', [], 400);
        }

        $cart = $this->cartService->getCart();
        return $this->sendResponse(
            $this->formatCartResponse($cart),
            $result['message'] ?? 'Product added to bag.'
        );
    }

    /**
     * Update Quantity of a Cart Item.
     */
    public function update(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $result = $this->cartService->updateQuantity($itemId, (int) $request->quantity);

        if (!($result['success'] ?? false)) {
            return $this->sendError($result['message'] ?? 'Unable to update cart quantity.', [], 400);
        }

        $cart = $this->cartService->getCart();
        return $this->sendResponse(
            $this->formatCartResponse($cart),
            $result['message'] ?? 'Cart updated successfully.'
        );
    }

    /**
     * Remove an Item from Cart.
     */
    public function remove(int $itemId): JsonResponse
    {
        $removed = $this->cartService->removeItem($itemId);

        $cart = $this->cartService->getCart();
        return $this->sendResponse(
            $this->formatCartResponse($cart),
            $removed ? 'Item removed from bag.' : 'Item not found in bag.'
        );
    }

    /**
     * Clear Entire Cart.
     */
    public function clear(): JsonResponse
    {
        $this->cartService->clearCart();
        $cart = $this->cartService->getCart();
        return $this->sendResponse(
            $this->formatCartResponse($cart),
            'Cart cleared successfully.'
        );
    }

    /**
     * Toggle Item Selection Checkbox (for selective checkout in mobile apps).
     */
    public function toggleSelect(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'is_selected' => 'nullable|boolean',
        ]);

        $this->cartService->toggleSelect($itemId, $request->has('is_selected') ? (bool) $request->is_selected : null);
        $cart = $this->cartService->getCart();

        return $this->sendResponse(
            $this->formatCartResponse($cart),
            'Cart selection updated.'
        );
    }
}
