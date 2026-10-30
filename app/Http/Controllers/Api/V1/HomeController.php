<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Setting;
use App\Services\OfferService;
use Illuminate\Support\Facades\Cache;

class HomeController extends BaseApiController
{
    public const CACHE_KEY = 'api_v1_home_payload';
    public const CACHE_TTL = 600; // 10 minutes (flushed automatically on any admin edit)

    /**
     * Get all data required for the customer homepage in 1 consolidated, pre-computed snapshot.
     */
    public function index()
    {
        $data = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->buildConsolidatedHomePayload();
        });

        return $this->sendResponse($data, 'Homepage data retrieved successfully.');
    }

    /**
     * Clear all home screen caches. Called automatically when products, banners, offers, categories or settings are modified.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('home_banners');
        Cache::forget('home_offers');
        Cache::forget('home_categories');
        Cache::forget('home_brands');
        Cache::forget('featured_products');
        Cache::forget('trending_products');
        Cache::forget('latest_products');
        Cache::forget('home_explore_products_p1');
        Cache::forget('home_total_active_products');
    }

    /**
     * Build the consolidated home payload in a single optimized pass.
     */
    protected function buildConsolidatedHomePayload(): array
    {
        $offerService = app(OfferService::class);

        // 1. Banners (Active only, ordered)
        $banners = Banner::active()
            ->orderBy('display_order')
            ->get([
                'id', 'offer_id', 'banner_type', 'bg_gradient', 'title', 'subtitle',
                'desktop_image', 'mobile_image', 'primary_button_text', 'primary_button_link',
                'display_order', 'is_active'
            ])
            ->map(function ($banner) {
                $rawImg = $banner->mobile_image ?: $banner->desktop_image;
                $imageUrl = !empty($rawImg) ? (str_starts_with($rawImg, 'http') ? $rawImg : asset('storage/' . ltrim($rawImg, '/'))) : null;
                $desktopUrl = !empty($banner->desktop_image)
                    ? (str_starts_with($banner->desktop_image, 'http') ? $banner->desktop_image : asset('storage/' . ltrim($banner->desktop_image, '/')))
                    : null;
                $mobileUrl = !empty($banner->mobile_image)
                    ? (str_starts_with($banner->mobile_image, 'http') ? $banner->mobile_image : asset('storage/' . ltrim($banner->mobile_image, '/')))
                    : $desktopUrl;

                return [
                    'id'                  => $banner->id ?? 0,
                    'offer_id'            => $banner->offer_id,
                    'banner_type'         => $banner->banner_type ?? 'GENERAL_PROMO',
                    'bg_gradient'         => $banner->bg_gradient,
                    'title'               => $banner->title,
                    'subtitle'            => $banner->subtitle,
                    'desktop_image'       => $banner->desktop_image,
                    'mobile_image'        => $banner->mobile_image,
                    'image_url'           => $imageUrl,
                    'desktop_image_url'   => $desktopUrl,
                    'mobile_image_url'    => $mobileUrl,
                    'primary_button_text' => $banner->primary_button_text,
                    'primary_button_link' => $banner->primary_button_link,
                    'display_order'       => $banner->display_order ?? 0,
                    'is_active'           => (bool) ($banner->is_active ?? true),
                ];
            });

        // 2. Active Offers (Live offers with badge and timing)
        $offers = Offer::live()
            ->orderBy('priority', 'desc')
            ->get()
            ->map(function ($offer) {
                $rawImg = $offer->banner_image;
                $imageUrl = !empty($rawImg) ? (str_starts_with($rawImg, 'http') ? $rawImg : asset('storage/' . ltrim($rawImg, '/'))) : null;
                return [
                    'id'                  => $offer->id,
                    'title'               => $offer->title,
                    'slug'                => $offer->slug,
                    'type'                => $offer->type,
                    'badge_text'          => $offer->badge_text ?? ($offer->discount_type === 'PERCENTAGE' ? round($offer->discount_value) . "% OFF" : "Flat ₹" . round($offer->discount_value) . " OFF"),
                    'description'         => $offer->description,
                    'banner_image'        => $imageUrl,
                    'image'               => $imageUrl,
                    'theme_color'         => $offer->theme_color ?? '#6366f1',
                    'discount_type'       => $offer->discount_type,
                    'discount_value'      => (float) $offer->discount_value,
                    'min_purchase_amount' => (float) $offer->min_purchase_amount,
                    'max_discount_amount' => (float) $offer->max_discount_amount,
                    'start_time'          => $offer->start_time ? $offer->start_time->toISOString() : null,
                    'end_time'            => $offer->end_time ? $offer->end_time->toISOString() : null,
                    'is_active'           => (bool) $offer->is_active,
                ];
            });

        // 3. Active Categories
        $categories = Category::where('status', 'Active')
            ->orderBy('name')
            ->take(16)
            ->get(['id', 'name', 'slug', 'image', 'status'])
            ->map(function ($cat) {
                return [
                    'id'        => $cat->id,
                    'name'      => $cat->name,
                    'slug'      => $cat->slug,
                    'image'     => $cat->image,
                    'image_url' => $cat->image ? (str_starts_with($cat->image, 'http') ? $cat->image : asset('storage/' . ltrim($cat->image, '/'))) : null,
                ];
            });

        // 4. Active Brands
        $brands = Brand::where('status', 1)
            ->orderBy('name')
            ->take(12)
            ->get(['id', 'name', 'slug', 'logo', 'status'])
            ->map(function ($brand) {
                return [
                    'id'       => $brand->id,
                    'name'     => $brand->name,
                    'slug'     => $brand->slug,
                    'logo'     => $brand->logo,
                    'logo_url' => $brand->logo ? (str_starts_with($brand->logo, 'http') ? $brand->logo : asset('storage/' . ltrim($brand->logo, '/'))) : null,
                ];
            });

        // 5. Consolidated Products Query with Eager Loading (Page 1)
        $productsPaginator = Product::select([
                'id', 'category_id', 'brand_id', 'product_type', 'name', 'slug', 'sku', 'price',
                'stock', 'featured', 'trending', 'status', 'main_image',
                'has_options', 'option_type', 'option_stocks', 'option_images',
                'created_at'
            ])
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug,logo',
            ])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')
            ->where('status', 'Active')
            ->latest()
            ->paginate(16);

        // Apply OfferService Discounts
        $offerService->applyOfferDiscountsToProducts($productsPaginator->getCollection());

        // Transform collection to match mobile app format exactly
        $transformedProducts = $productsPaginator->getCollection()->map(function ($product) {
            return $this->formatProductItem($product);
        })->values();

        // Subsets for home sections
        $featuredProducts = $transformedProducts->where('featured', true)->take(8)->values();
        $trendingProducts = $transformedProducts->where('trending', true)->take(8)->values();
        $latestProducts   = $transformedProducts->take(12)->values();

        // 6. Settings snapshot
        $settings = $this->getStoreSettingsArray();

        return [
            'banners'          => $banners,
            'offers'           => $offers,
            'categories'       => $categories,
            'brands'           => $brands,
            'products'         => $transformedProducts,
            'featuredProducts' => $featuredProducts,
            'trendingProducts' => $trendingProducts,
            'latestProducts'   => $latestProducts,
            'pagination'       => [
                'current_page' => $productsPaginator->currentPage(),
                'last_page'    => $productsPaginator->lastPage(),
                'per_page'     => $productsPaginator->perPage(),
                'total'        => $productsPaginator->total(),
            ],
            'settings'         => $settings,
        ];
    }

    /**
     * Format a product item for high performance and mobile app compatibility.
     */
    protected function formatProductItem($product): array
    {
        $mainImgUrl = null;
        if (!empty($product->main_image)) {
            $mainImgUrl = str_starts_with($product->main_image, 'http')
                ? $product->main_image
                : asset('storage/' . ltrim($product->main_image, '/'));
        }

        $optImgs = $product->option_images;
        if (is_string($optImgs)) {
            $optImgs = json_decode($optImgs, true) ?: [];
        }
        $formattedOptImgs = [];
        if (is_array($optImgs)) {
            foreach ($optImgs as $optKey => $imgPath) {
                $formattedOptImgs[$optKey] = (!empty($imgPath) && !str_starts_with($imgPath, 'http'))
                    ? asset('storage/' . ltrim($imgPath, '/'))
                    : $imgPath;
            }
        }

        $sellingPrice = (float) $product->price;
        $offerPrice = isset($product->sale_price) ? (float) $product->sale_price : null;
        $discountPct = (int) ($product->offer_discount_percentage ?? 0);
        if ($offerPrice !== null && $offerPrice < $sellingPrice && $discountPct <= 0) {
            $discountPct = (int) round((($sellingPrice - $offerPrice) / $sellingPrice) * 100);
        }

        return [
            'id'                  => $product->id,
            'category_id'         => $product->category_id ?? 0,
            'brand_id'            => $product->brand_id,
            'product_type'        => $product->product_type,
            'name'                => $product->name,
            'slug'                => $product->slug,
            'sku'                 => $product->sku,
            'price'               => $sellingPrice,
            'selling_price'       => $offerPrice !== null && $offerPrice < $sellingPrice ? $offerPrice : $sellingPrice,
            'mrp'                 => $sellingPrice,
            'sale_price'          => $offerPrice,
            'offer_price'         => $offerPrice,
            'discount_percentage' => $discountPct,
            'stock'               => (int) $product->stock,
            'stock_quantity'      => (int) $product->stock,
            'in_stock'            => $product->stock > 0,
            'featured'            => (bool) $product->featured,
            'trending'            => (bool) $product->trending,
            'status'              => $product->status,
            'main_image'          => $product->main_image,
            'main_image_url'      => $mainImgUrl,
            'image_url'           => $mainImgUrl,
            'has_options'         => (bool) $product->has_options,
            'option_type'         => $product->option_type ?? 'Size',
            'option_stocks'       => $product->option_stocks ?? [],
            'option_images'       => $formattedOptImgs,
            'average_rating'      => round((float) ($product->avg_rating ?? $product->averageRating() ?? 4.5), 1),
            'rating'              => round((float) ($product->avg_rating ?? $product->averageRating() ?? 4.5), 1),
            'category_name'       => $product->category?->name,
            'brand_name'          => $product->brand?->name,
            'category'            => $product->category ? [
                'id'   => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'brand'               => $product->brand ? [
                'id'   => $product->brand->id,
                'name' => $product->brand->name,
                'slug' => $product->brand->slug,
                'logo' => $product->brand->logo,
            ] : null,
        ];
    }

    /**
     * Array of public store configuration and settings.
     */
    protected function getStoreSettingsArray(): array
    {
        return [
            'store_name'                  => Setting::get('store_name', 'ShopCalm'),
            'tagline'                     => Setting::get('tagline', "Nature's Purest Goodness"),
            'contact_email'               => Setting::get('contact_email', 'support@shopcalm.in'),
            'contact_phone'               => Setting::get('contact_phone', ''),
            'whatsapp_number'             => Setting::get('whatsapp_number', ''),
            'support_hours'               => Setting::get('support_hours', 'Mon - Sat: 9:00 AM - 8:00 PM'),
            'currency'                    => Setting::get('currency', 'INR'),
            'currency_symbol'             => Setting::get('currency_symbol', '₹'),
            'free_shipping_min'           => (float) Setting::get('free_shipping_min', 499),
            'default_shipping_fee'        => (float) Setting::get('default_shipping_fee', 40.00),
            'cod_flat_fee'                => (float) Setting::get('cod_flat_fee', 40.00),
            'enable_cod'                  => Setting::get('enable_cod', '1') === '1',
            'allow_customer_cancellation' => Setting::get('allow_customer_cancellation', '1') === '1',
            'facebook_url'                => Setting::get('facebook_url', ''),
            'instagram_url'               => Setting::get('instagram_url', ''),
            'twitter_url'                 => Setting::get('twitter_url', ''),
            'youtube_url'                 => Setting::get('youtube_url', ''),
        ];
    }

    /**
     * Get public store configuration and contact settings endpoint.
     */
    public function getStoreSettings()
    {
        return $this->sendResponse($this->getStoreSettingsArray(), 'Store settings retrieved successfully.');
    }
}
