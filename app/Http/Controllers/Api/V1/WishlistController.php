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
            $hasDiscount = (isset($product->sale_price) && (float) $product->sale_price < (float) $product->price) || !empty($product->offer_discount_percentage);
            $finalSelling = $hasDiscount ? (float) $product->sale_price : (float) $product->price;
            $mrp = (float) $product->price;
            $discountPct = $product->offer_discount_percentage ?? ($hasDiscount && $mrp > 0 ? round((($mrp - $finalSelling) / $mrp) * 100) : 0);

            $img = $product->main_image;
            $imgUrl = !empty($img) ? (str_starts_with($img, 'http') ? $img : asset('storage/' . ltrim($img, '/'))) : null;

            return [
                'id'                  => (int) $product->id,
                'name'                => $product->name,
                'slug'                => $product->slug,
                'price'               => $mrp,
                'mrp'                 => $mrp,
                'selling_price'       => $finalSelling,
                'offer_price'         => $hasDiscount ? $finalSelling : null,
                'discount_percentage' => (int) $discountPct,
                'main_image_url'      => $imgUrl,
                'primary_image'       => $imgUrl,
                'stock'               => (int) $product->stock,
                'stock_quantity'      => (int) $product->stock,
                'in_stock'            => (bool) ($product->stock > 0),
                'average_rating'      => (float) $product->averageRating(),
                'rating'              => (float) $product->averageRating(),
                'category_id'         => (int) ($product->category_id ?? 0),
                'category_name'       => $product->category?->name,
                'brand_name'          => $product->brand?->name,
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
