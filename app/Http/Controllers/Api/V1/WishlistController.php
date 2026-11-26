<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Product;
use App\Models\WishlistItem;
use App\Services\CartService;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends BaseApiController
{
    protected $offerService;

    public function __construct(OfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    /**
     * Get user's wishlist products with active offer pricing.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        $wishlist = $user->wishlist()->firstOrCreate(['user_id' => $user->id]);

        $items = WishlistItem::where('wishlist_id', $wishlist->id)
            ->with(['product.category:id,name,slug', 'product.brand:id,name,slug'])
            ->latest()
            ->get();

        $products = $items->pluck('product')->filter()->values();
        $this->offerService->applyOfferDiscountsToProducts($products);

        $transformed = $products->map(function ($product) {
            $basePrice = (float) ($product->price ?? 0);
            $hasDiscount = (isset($product->sale_price) && (float) $product->sale_price < $basePrice) || !empty($product->offer_discount_percentage);
            $finalSelling = $hasDiscount ? (float) $product->sale_price : $basePrice;
            $mrp = (float) ($product->mrp && (float) $product->mrp > 0
                ? $product->mrp
                : ($basePrice > 0 ? round($basePrice * 1.25, 2) : $finalSelling));
            if ($mrp < $finalSelling) {
                $mrp = $finalSelling;
            }

            $discountPct = ($mrp > $finalSelling && $mrp > 0)
                ? (int) round((($mrp - $finalSelling) / $mrp) * 100)
                : (int) ($product->offer_discount_percentage ?? 0);

            $img = $product->main_image;
            $imgUrl = !empty($img) ? (str_starts_with($img, 'http') ? $img : asset('storage/' . ltrim($img, '/'))) : null;

            $isReplaceable = (bool) ($product->is_replaceable ?? false);
            $replacementDays = (int) ($product->replacement_window_days ?? 0);
            $policyBadge = $isReplaceable ? "{$replacementDays}D Replacement" : "Non-Returnable";

            return [
                'id'                       => (int) $product->id,
                'name'                     => $product->name,
                'slug'                     => $product->slug,
                'price'                    => $finalSelling,
                'mrp'                      => $mrp,
                'selling_price'            => $finalSelling,
                'offer_price'              => $hasDiscount ? $finalSelling : null,
                'discount_percentage'      => $discountPct,
                'you_save_amount'          => round(max(0, $mrp - $finalSelling), 2),
                'main_image_url'           => $imgUrl,
                'primary_image'            => $imgUrl,
                'stock'                    => (int) $product->stock,
                'stock_quantity'           => (int) $product->stock,
                'in_stock'                 => (bool) ($product->stock > 0),
                'average_rating'           => (float) $product->averageRating(),
                'rating'                   => (float) $product->averageRating(),
                'category_id'              => (int) ($product->category_id ?? 0),
                'category_name'            => $product->category?->name,
                'brand_name'               => $product->brand?->name,
                'is_replaceable'           => $isReplaceable,
                'replacement_window_days'  => $replacementDays,
                'replacement_policy_badge' => $policyBadge,
            ];
        });

        return $this->sendResponse([
            'items' => $transformed,
            'count' => $transformed->count(),
        ], 'Wishlist items retrieved successfully.');
    }

    /**
     * Toggle product in user's wishlist (Add if absent, Remove if present).
     */
    public function toggle(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $productId = (int) $validated['product_id'];
        $wishlist = $user->wishlist()->firstOrCreate(['user_id' => $user->id]);

        $item = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', $productId)
            ->first();

        if ($item) {
            $item->delete();
            $isWishlisted = false;
            $message = 'Removed from Wishlist';
        } else {
            WishlistItem::create([
                'wishlist_id' => $wishlist->id,
                'product_id'  => $productId,
            ]);
            $isWishlisted = true;
            $message = 'Saved to Wishlist';
        }

        $count = WishlistItem::where('wishlist_id', $wishlist->id)->count();

        return $this->sendResponse([
            'is_wishlisted' => $isWishlisted,
            'product_id'    => $productId,
            'count'         => $count,
        ], $message);
    }

    /**
     * Remove product from wishlist.
     */
    public function remove($productId, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        $wishlist = $user->wishlist()->firstOrCreate(['user_id' => $user->id]);

        WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', (int) $productId)
            ->delete();

        $count = WishlistItem::where('wishlist_id', $wishlist->id)->count();

        return $this->sendResponse([
            'is_wishlisted' => false,
            'product_id'    => (int) $productId,
            'count'         => $count,
        ], 'Removed from Wishlist');
    }

    /**
     * Move wishlist item to cart.
     */
    public function moveToCart($productId, Request $request, CartService $cartService): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        $wishlist = $user->wishlist()->firstOrCreate(['user_id' => $user->id]);

        $item = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', (int) $productId)
            ->first();

        if ($item) {
            $item->delete();
        }

        $cartService->addProduct((int) $productId);

        $count = WishlistItem::where('wishlist_id', $wishlist->id)->count();

        return $this->sendResponse([
            'count' => $count,
        ], 'Product moved to cart successfully.');
    }
}
