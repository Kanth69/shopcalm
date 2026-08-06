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
        $banners = Cache::remember('home_banners', 3600, function() {
            return Banner::active()->orderBy('display_order')->get();
        });

        $offers = Cache::remember('home_offers', 3600, function() {
            return Offer::active()->latest()->take(3)->get();
        });

        $featuredProducts = Cache::remember('featured_products', 600, function () {
            return Product::with(['category', 'brand'])->where('status', 'Active')->where('featured', true)->latest()->take(8)->get();
        });

        $trendingProducts = Cache::remember('trending_products', 600, function () {
            return Product::with(['category', 'brand'])->where('status', 'Active')->where('trending', true)->latest()->take(8)->get();
        });

        $latestProducts = Cache::remember('latest_products', 600, function () {
            return Product::with(['category', 'brand'])->where('status', 'Active')->latest()->take(8)->get();
        });

        $categories = Cache::remember('home_categories', 3600, function () {
            return Category::where('status', 'Active')->orderBy('name')->take(10)->get();
        });

        $brands = Cache::remember('home_brands', 3600, function () {
            return Brand::where('status', 1)->orderBy('name')->take(10)->get();
        });

        $data = [
            'banners' => $banners,
            'offers' => $offers,
            'featuredProducts' => $featuredProducts,
            'trendingProducts' => $trendingProducts,
            'latestProducts' => $latestProducts,
            'categories' => $categories,
            'brands' => $brands,
        ];

        return $this->sendResponse($data, 'Homepage data retrieved successfully.');
    }
}
