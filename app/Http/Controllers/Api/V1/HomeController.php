<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
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

        if ($liveMegaSale && $liveMegaSale->banner_image) {
            $campaignBanner = new Banner([
                'id'                  => 0,
                'offer_id'            => $liveMegaSale->id,
                'banner_type'         => 'CAMPAIGN_OFFER',
                'title'               => $liveMegaSale->title,
                'subtitle'            => $liveMegaSale->badge_text ?? ($liveMegaSale->discount_type === 'PERCENTAGE' ? round($liveMegaSale->discount_value) . "% OFF" : "Flat ₹" . round($liveMegaSale->discount_value) . " OFF"),
                'desktop_image'       => $liveMegaSale->banner_image,
                'mobile_image'        => $liveMegaSale->banner_image,
                'primary_button_text' => 'View Deals',
                'primary_button_link' => '/offers?offer_id=' . $liveMegaSale->id,
                'is_active'           => true,
            ]);
            $banners->prepend($campaignBanner);
        }

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
            return Offer::active()->latest()->take(5)->get();
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
        ];

        return $this->sendResponse($data, 'Homepage data retrieved successfully.');
    }
}
