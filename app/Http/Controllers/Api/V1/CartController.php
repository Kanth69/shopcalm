<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends BaseApiController
{
    /**
     * Helper to calculate cart totals for JSON response.
     */
    protected function calculateCartTotals(array $cart): array
    {
        $items = [];
        $subtotal = 0.00;
        $totalSavings = 0.00;

        foreach ($cart as $id => $details) {
            $itemTotal = (float) $details['price'] * (int) $details['quantity'];
            $subtotal += $itemTotal;

            if (!empty($details['original_price']) && $details['original_price'] > $details['price']) {
                $totalSavings += ($details['original_price'] - $details['price']) * $details['quantity'];
            }

            $items[] = array_merge($details, [
                'cart_item_id' => (string) $id,
                'total'        => round($itemTotal, 2),
                'image_url'    => !empty($details['image']) ? asset('storage/' . $details['image']) : null,
            ]);
        }

        $freeShippingMin = (float) Setting::get('free_shipping_min', 499);
        $deliveryCharge = ($subtotal >= $freeShippingMin || count($cart) === 0) ? 0.00 : 40.00;
        $grandTotal = $subtotal + $deliveryCharge;

        return [
            'items'                 => $items,
            'item_count'            => count($items),
            'total_quantity'        => array_sum(array_column($items, 'quantity')),
            'subtotal'              => round($subtotal, 2),
            'total_savings'         => round($totalSavings, 2),
            'delivery_charge'       => round($deliveryCharge, 2),
            'free_shipping_min'     => $freeShippingMin,
            'qualifies_free_shipping' => ($subtotal >= $freeShippingMin),
            'grand_total'           => round($grandTotal, 2),
        ];
    }

    /**
     * Get Current Cart Contents & Summary.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = session()->get('cart', []);
        return $this->sendResponse($this->calculateCartTotals($cart), 'Cart retrieved successfully.');
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
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->status !== 'Active') {
            return $this->sendError('Product is currently unavailable.', [], 400);
        }

        $selectedOption = $request->selected_option;
        $price = (float) $product->selling_price;
        $originalPrice = (float) $product->price;

        // Check variant option price if selected
        if ($selectedOption && !empty($product->options) && is_array($product->options)) {
            foreach ($product->options as $option) {
                if (isset($option['name']) && $option['name'] === $selectedOption) {
                    if (!empty($option['price'])) {
                        $price = (float) $option['price'];
                    }
                    break;
                }
            }
        }

        // Cart Key: product_id + option
        $cartKey = $product->id . ($selectedOption ? '_' . md5($selectedOption) : '');
        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += (int) $request->quantity;
        } else {
            $cart[$cartKey] = [
                'product_id'      => $product->id,
                'name'            => $product->name,
                'slug'            => $product->slug,
                'quantity'        => (int) $request->quantity,
                'price'           => $price,
                'original_price'  => $originalPrice,
                'image'           => $product->featured_image,
                'selected_option' => $selectedOption,
            ];
        }

        session()->put('cart', $cart);

        return $this->sendResponse(
            $this->calculateCartTotals($cart),
            "{$product->name} added to cart."
        );
    }

    /**
     * Update Quantity of a Cart Item.
     */
    public function update(Request $request, string $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$itemId])) {
            return $this->sendError('Cart item not found.', [], 404);
        }

        $cart[$itemId]['quantity'] = (int) $request->quantity;
        session()->put('cart', $cart);

        return $this->sendResponse(
            $this->calculateCartTotals($cart),
            'Cart updated successfully.'
        );
    }

    /**
     * Remove an Item from Cart.
     */
    public function remove(string $itemId): JsonResponse
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            session()->put('cart', $cart);
        }

        return $this->sendResponse(
            $this->calculateCartTotals($cart),
            'Item removed from cart.'
        );
    }

    /**
     * Clear Entire Cart.
     */
    public function clear(): JsonResponse
    {
        session()->forget('cart');
        return $this->sendResponse(
            $this->calculateCartTotals([]),
            'Cart cleared successfully.'
        );
    }
}
