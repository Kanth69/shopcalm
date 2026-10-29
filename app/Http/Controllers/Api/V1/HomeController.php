<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class HomeController extends BaseApiController
{
    /**
     * Get all data required for the customer homepage.
     * Reuses the exact caching and querying logic from Customer\ShopController@home
     */
    public function index()
    {
        $offerService = app(\App\Services\OfferService::class);
        $liveMegaSale = $offerService->getLiveMegaSale();

        $cachedBanners = Cache::remember('home_banners', 300, function() {
            return Banner::active()->orderBy('display_order')->get([
                'id', 'offer_id', 'banner_type', 'bg_gradient', 'title', 'subtitle',
                'desktop_image', 'mobile_image', 'primary_button_text', 'primary_button_link',
                'display_order', 'is_active'
            ]);
        });

        $banners = collect($cachedBanners)->map(fn($b) => clone $b);

        $banners = $banners->values()->map(function ($banner) {
            $rawImg = $banner->mobile_image ?: $banner->desktop_image;
            $imageUrl = null;
            if (!empty($rawImg)) {
                $imageUrl = str_starts_with($rawImg, 'http') ? $rawImg : asset('storage/' . ltrim($rawImg, '/'));
            }
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

        $offers = Cache::remember('home_offers', 300, function() {
            return Offer::live()->orderBy('priority', 'desc')->get()->map(function($offer) {
                $rawImg = $offer->banner_image;
                $imageUrl = null;
                if (!empty($rawImg)) {
                    $imageUrl = str_starts_with($rawImg, 'http') ? $rawImg : asset('storage/' . ltrim($rawImg, '/'));
                }
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
        });

        $featuredProducts = Cache::remember('featured_products', 300, function () {
            return Product::with(['category', 'brand'])->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')->where('status', 'Active')->where('featured', true)->latest()->take(8)->get();
        });

        $trendingProducts = Cache::remember('trending_products', 300, function () {
            return Product::with(['category', 'brand'])->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')->where('status', 'Active')->where('trending', true)->latest()->take(8)->get();
        });

        $latestProducts = Cache::remember('latest_products', 300, function () {
            return Product::with(['category', 'brand'])->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')->where('status', 'Active')->latest()->take(12)->get();
        });

        $featuredProducts = $offerService->applyOfferDiscountsToProducts($featuredProducts);
        $trendingProducts = $offerService->applyOfferDiscountsToProducts($trendingProducts);
        $latestProducts   = $offerService->applyOfferDiscountsToProducts($latestProducts);

        $categories = Cache::remember('home_categories', 3600, function () {
            return Category::where('status', 'Active')->orderBy('name')->take(12)->get();
        });

        $brands = Cache::remember('home_brands', 3600, function () {
            return Brand::where('status', 1)->orderBy('name')->take(10)->get();
        });

        $allProducts = $trendingProducts->concat($featuredProducts)->concat($latestProducts)->unique('id')->values();
        if ($allProducts->isEmpty()) {
            $allProducts = Product::with(['category', 'brand'])->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')->where('status', 'Active')->latest()->take(20)->get();
            $allProducts = $offerService->applyOfferDiscountsToProducts($allProducts);
        }

        $data = [
            'banners'          => $banners,
            'offers'           => $offers,
            'products'         => $allProducts,
            'featuredProducts' => $featuredProducts,
            'trendingProducts' => $trendingProducts,
            'latestProducts'   => $latestProducts,
            'categories'       => $categories,
            'brands'           => $brands,
            'settings'         => [
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
            ],
        ];

        return $this->sendResponse($data, 'Homepage data retrieved successfully.');
    }

    /**
     * Get public store configuration and contact settings.
     */
    public function getStoreSettings()
    {
        $settings = [
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

        return $this->sendResponse($settings, 'Store settings retrieved successfully.');
    }
}
