<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Offer;
use App\Models\Product;
use App\Services\OfferService;
use Illuminate\Http\Request;

class OfferController extends BaseApiController
{
    protected $offerService;

    public function __construct(OfferService $offerService)
    {
        $this->offerService = $offerService;
    }

    public function index(Request $request)
    {
        $allOffers = Offer::live()->with('targets')->orderBy('priority', 'desc')->get();

        $selectedOfferId = $request->input('offer_id');
        $selectedOfferSlug = $request->input('offer') ?? $request->input('slug');
        $selectedOffer = null;

        if ($selectedOfferId) {
            $selectedOffer = $allOffers->firstWhere('id', (int) $selectedOfferId);
        } elseif ($selectedOfferSlug) {
            $selectedOffer = $allOffers->firstWhere('slug', $selectedOfferSlug);
        }

        $query = Product::where('status', 'Active')->with(['category', 'brand'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')
            ->withCount(['reviews as reviews_count' => fn($q) => $q->where('status', 'Approved')]);

        if ($selectedOffer) {
            if ($selectedOffer->targets->isNotEmpty()) {
                $targetCatIds = $selectedOffer->targets->where('target_type', 'CATEGORY')->pluck('target_id')->filter()->toArray();
                $targetBrandIds = $selectedOffer->targets->where('target_type', 'BRAND')->pluck('target_id')->filter()->toArray();
                $targetProductIds = $selectedOffer->targets->where('target_type', 'PRODUCT')->pluck('target_id')->filter()->toArray();

                $query->where(function ($q) use ($targetCatIds, $targetBrandIds, $targetProductIds) {
                    if (!empty($targetCatIds)) $q->orWhereIn('category_id', $targetCatIds);
                    if (!empty($targetBrandIds)) $q->orWhereIn('brand_id', $targetBrandIds);
                    if (!empty($targetProductIds)) $q->orWhereIn('id', $targetProductIds);
                });
            }
        } elseif ($allOffers->isNotEmpty()) {
            $targetCatIds = [];
            $targetBrandIds = [];
            $targetProductIds = [];
            $isStorewide = false;

            foreach ($allOffers as $offer) {
                if ($offer->targets->isEmpty()) {
                    $isStorewide = true;
                } else {
                    foreach ($offer->targets as $t) {
                        if ($t->target_type === 'CATEGORY') $targetCatIds[] = $t->target_id;
                        if ($t->target_type === 'BRAND') $targetBrandIds[] = $t->target_id;
                        if ($t->target_type === 'PRODUCT') $targetProductIds[] = $t->target_id;
                    }
                }
            }

            if (!$isStorewide) {
                $query->where(function ($q) use ($targetCatIds, $targetBrandIds, $targetProductIds) {
                    if (!empty($targetCatIds)) $q->orWhereIn('category_id', $targetCatIds);
                    if (!empty($targetBrandIds)) $q->orWhereIn('brand_id', $targetBrandIds);
                    if (!empty($targetProductIds)) $q->orWhereIn('id', $targetProductIds);
                });
            }
        }

        $products = $query->latest()->take(50)->get();

        if ($selectedOffer) {
            $this->offerService->applySpecificOfferToProducts($products, $selectedOffer);
        } else {
            $this->offerService->applyOfferDiscountsToProducts($products);
        }

        $transformedProducts = $products->map(function ($product) {
            $mainImg = $product->main_image;
            $mainUrl = !empty($mainImg) ? (str_starts_with($mainImg, 'http') ? $mainImg : asset('storage/' . ltrim($mainImg, '/'))) : null;
            $basePrice = (float) ($product->price ?? 0);
            $salePrice = isset($product->sale_price) ? (float) $product->sale_price : $basePrice;

            return [
                'id'                        => $product->id,
                'name'                      => $product->name,
                'slug'                      => $product->slug,
                'sku'                       => $product->sku,
                'product_type'              => $product->product_type,
                'category_id'               => $product->category_id,
                'category_name'             => $product->category?->name,
                'brand_name'                => $product->brand?->name,
                'price'                     => $basePrice,
                'mrp'                       => $basePrice,
                'selling_price'             => $salePrice,
                'sale_price'                => $salePrice,
                'offer_price'               => $salePrice,
                'discount_percentage'       => $product->offer_discount_percentage ?? 0,
                'offer_discount_percentage' => $product->offer_discount_percentage ?? 0,
                'offer_badge'               => $product->offer_badge ?? null,
                'main_image'                => $mainImg,
                'main_image_url'            => $mainUrl,
                'primary_image'             => $mainUrl,
                'image_url'                 => $mainUrl,
                'stock'                     => (int) $product->stock,
                'stock_quantity'            => (int) $product->stock,
                'in_stock'                  => $product->stock > 0,
                'rating'                    => (float) ($product->avg_rating ?? $product->averageRating() ?? 4.5),
                'total_reviews'             => (int) ($product->reviews_count ?? 0),
            ];
        });

        $mappedOffers = $allOffers->map(function ($o) {
            $rawImg = $o->banner_image;
            $imgUrl = !empty($rawImg) ? (str_starts_with($rawImg, 'http') ? $rawImg : asset('storage/' . ltrim($rawImg, '/'))) : null;
            return [
                'id'                  => $o->id,
                'title'               => $o->title,
                'slug'                => $o->slug,
                'type'                => $o->type,
                'badge_text'          => $o->badge_text ?? ($o->discount_type === 'PERCENTAGE' ? round($o->discount_value) . '% OFF' : 'Flat ₹' . round($o->discount_value) . ' OFF'),
                'description'         => $o->description,
                'banner_image'        => $imgUrl,
                'image'               => $imgUrl,
                'theme_color'         => $o->theme_color ?? '#6366f1',
                'discount_type'       => $o->discount_type,
                'discount_value'      => (float) $o->discount_value,
                'min_purchase_amount' => (float) $o->min_purchase_amount,
                'max_discount_amount' => (float) $o->max_discount_amount,
                'start_time'          => $o->start_time ? $o->start_time->toISOString() : null,
                'end_time'            => $o->end_time ? $o->end_time->toISOString() : null,
                'is_active'           => (bool) $o->is_active,
            ];
        });

        $mappedSelectedOffer = null;
        if ($selectedOffer) {
            $rawImg = $selectedOffer->banner_image;
            $imgUrl = !empty($rawImg) ? (str_starts_with($rawImg, 'http') ? $rawImg : asset('storage/' . ltrim($rawImg, '/'))) : null;
            $mappedSelectedOffer = [
                'id'                  => $selectedOffer->id,
                'title'               => $selectedOffer->title,
                'slug'                => $selectedOffer->slug,
                'type'                => $selectedOffer->type,
                'badge_text'          => $selectedOffer->badge_text ?? ($selectedOffer->discount_type === 'PERCENTAGE' ? round($selectedOffer->discount_value) . '% OFF' : 'Flat ₹' . round($selectedOffer->discount_value) . ' OFF'),
                'description'         => $selectedOffer->description,
                'banner_image'        => $imgUrl,
                'image'               => $imgUrl,
                'theme_color'         => $selectedOffer->theme_color ?? '#6366f1',
                'discount_type'       => $selectedOffer->discount_type,
                'discount_value'      => (float) $selectedOffer->discount_value,
                'min_purchase_amount' => (float) $selectedOffer->min_purchase_amount,
                'max_discount_amount' => (float) $selectedOffer->max_discount_amount,
                'start_time'          => $selectedOffer->start_time ? $selectedOffer->start_time->toISOString() : null,
                'end_time'            => $selectedOffer->end_time ? $selectedOffer->end_time->toISOString() : null,
                'is_active'           => (bool) $selectedOffer->is_active,
            ];
        }

        return $this->sendResponse([
            'all_offers'     => $mappedOffers,
            'selected_offer' => $mappedSelectedOffer,
            'products'       => $transformedProducts,
        ], 'Offer products retrieved successfully.');
    }
}
