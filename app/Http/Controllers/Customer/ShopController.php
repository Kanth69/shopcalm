<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Setting;
use App\Services\OfferService;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function home()
    {
        $offerService = app(\App\Services\OfferService::class);
        $liveMegaSale = $offerService->getLiveMegaSale();

        $banners = Cache::remember('home_banners', 300, function() {
            return Banner::active()->orderBy('display_order')->get(['id','offer_id','banner_type','title','subtitle','desktop_image','mobile_image','primary_button_text','primary_button_link','display_order','is_active']);
        });

        if ($liveMegaSale && $liveMegaSale->banner_image) {
            $campaignBanner = new Banner([
                'offer_id'    => $liveMegaSale->id,
                'banner_type' => 'CAMPAIGN_OFFER',
                'title'       => $liveMegaSale->title,
                'subtitle'    => $liveMegaSale->badge_text ?? ($liveMegaSale->discount_type === 'PERCENTAGE' ? "{$liveMegaSale->discount_value}% OFF" : "Flat ₹{$liveMegaSale->discount_value} OFF"),
                'desktop_image'       => $liveMegaSale->banner_image,
                'primary_button_text' => 'View Deals',
                'primary_button_link' => '/offers?offer_id=' . $liveMegaSale->id,
            ]);
            $banners->prepend($campaignBanner);
        }

        $featuredProducts = Cache::remember('featured_products', 300, function () {
            return Product::with(['category', 'brand'])->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')->where('status', 'Active')->where('featured', true)->latest()->take(8)->get();
        });
        $trendingProducts = Cache::remember('trending_products', 300, function () {
            return Product::with(['category', 'brand'])->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')->where('status', 'Active')->where('trending', true)->latest()->take(8)->get();
        });
        $latestProducts = Cache::remember('latest_products', 300, function () {
            return Product::with(['category', 'brand'])->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')->where('status', 'Active')->latest()->take(8)->get();
        });

        $featuredProducts = $offerService->applyOfferDiscountsToProducts($featuredProducts);
        $trendingProducts = $offerService->applyOfferDiscountsToProducts($trendingProducts);
        $latestProducts = $offerService->applyOfferDiscountsToProducts($latestProducts);

        $categories = Cache::remember('home_categories', 3600, function () {
            return Category::where('status', 'Active')->orderBy('name')->take(10)->get();
        });
        $brands = Cache::remember('home_brands', 3600, function () {
            return Brand::where('status', 1)->orderBy('name')->take(10)->get();
        });

        $flashDeals = $offerService->getLiveFlashDeals();
        $flashProducts = collect();
        $activeFlashDeal = $flashDeals->first();

        if ($activeFlashDeal) {
            $query = Product::where('status', 'Active');
            if ($activeFlashDeal->targets->isNotEmpty()) {
                $targetCatIds = $activeFlashDeal->targets->where('target_type', 'CATEGORY')->pluck('target_id')->filter()->toArray();
                $targetBrandIds = $activeFlashDeal->targets->where('target_type', 'BRAND')->pluck('target_id')->filter()->toArray();
                $targetProductIds = $activeFlashDeal->targets->where('target_type', 'PRODUCT')->pluck('target_id')->filter()->toArray();

                $query->where(function ($q) use ($targetCatIds, $targetBrandIds, $targetProductIds) {
                    if (!empty($targetCatIds)) $q->orWhereIn('category_id', $targetCatIds);
                    if (!empty($targetBrandIds)) $q->orWhereIn('brand_id', $targetBrandIds);
                    if (!empty($targetProductIds)) $q->orWhereIn('id', $targetProductIds);
                });
            }

            $flashProducts = $query->take(4)->get();
            $flashProducts = $offerService->applyOfferDiscountsToProducts($flashProducts);
        }

        $settings = Setting::all()->pluck('value', 'key')->toArray();

        $upcomingDeal = \App\Models\Offer::where('start_time', '>', now())->orderBy('start_time', 'asc')->first();

        return view('customer.home', compact(
            'banners',
            'featuredProducts',
            'trendingProducts',
            'latestProducts',
            'flashProducts',
            'activeFlashDeal',
            'categories',
            'brands',
            'settings',
            'liveMegaSale',
            'upcomingDeal'
        ));
    }

    public function shop(Request $request)
    {
        // 1. Live Suggestion Dropdown API (for header search input)
        if ($request->filled('suggestion') || ($request->ajax() && $request->filled('q') && !$request->has('page') && !$request->has('category') && !$request->has('brand') && !$request->has('sort') && !$request->has('min_price'))) {
            $term = trim($request->q);
            $suggestions = Product::where('status', 'Active')
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('tags', 'like', "%{$term}%")
                      ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$term}%"))
                      ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$term}%"));
                })
                ->with('category')
                ->take(8)
                ->get()
                ->map(fn($p) => [
                    'name'     => $p->name,
                    'category' => $p->category->name ?? '',
                    'url'      => route('product.show', $p->slug),
                ]);

            return response()->json(['suggestions' => $suggestions]);
        }

        // 2. Main Search / Filter Query
        $didYouMean = null;
        if ($request->filled('q')) {
            $searchResult = $this->searchService->search(trim($request->q));
            $query = $searchResult['query'];
            $didYouMean = $searchResult['did_you_mean'] ?? null;
        } else {
            $query = Product::query()->where('status', 'Active');
        }

        // Apply additional filters
        if ($request->filled('category')) {
            $query->whereIn('category_id', (array)$request->category);
        }
        if ($request->filled('brand')) {
            $query->whereIn('brand_id', (array)$request->brand);
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }
        if ($request->boolean('on_sale') || $request->filled('offer')) {
            $liveOffers = \App\Models\Offer::live()->with('targets')->get();
            if ($liveOffers->isNotEmpty()) {
                $targetCatIds = [];
                $targetBrandIds = [];
                $targetProductIds = [];
                $isStorewide = false;

                foreach ($liveOffers as $offer) {
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
        }

        // Always eager-load avg_rating to prevent N+1 query loop on card render
        $query->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating');

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating_high':
                $query->orderBy('avg_rating', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->with(['category:id,name,slug', 'brand:id,name,slug'])->paginate(12)->appends($request->query());

        // Apply offer discount badges & prices
        app(\App\Services\OfferService::class)->applyOfferDiscountsToProducts($products->items());

        if ($request->ajax()) {
            return response()->json([
                'product_grid_html'   => view('customer.components.shop.product-grid', compact('products'))->render(),
                'pagination_html'     => $products->links('pagination::bootstrap-5')->render(),
                'product_count'       => $products->total(),
                'active_filters_html' => view('customer.components.active-filters')->render(),
            ]);
        }

        $categories = Cache::remember('all_categories', 3600, function () {
            return Category::where('status', 'Active')->orderBy('name')->get();
        });
        $brands = Cache::remember('all_brands', 3600, function () {
            return Brand::where('status', 1)->orderBy('name')->get();
        });

        return view('customer.shop', compact('products', 'categories', 'brands', 'didYouMean'));
    }

    public function getBrandsByCategory(Request $request)
    {
        $categoryIds = $request->input('category_ids', []);
        $prefix = $request->input('prefix', 'ajax');

        $brandsQuery = Brand::where('status', 1);

        if (!empty($categoryIds)) {
            $brandsQuery->whereHas('products', function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds);
            });
        }

        $brands = $brandsQuery->orderBy('name')->get();

        return view('customer.components.shop.brand-filter-options', compact('brands', 'prefix'));
    }

    public function productDetails(Product $product, OfferService $offerService)
    {
        $product->load([
            'category', 
            'brand', 
            'galleryImages', 
            'approvedReviews' => fn($q) => $q->with('user')->latest()
        ]);
        
        $product = $offerService->applyOfferDiscountToProduct($product);

        $relatedProducts = Product::with(['category', 'brand'])->where('status', 'Active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();
            
        $relatedProducts = $offerService->applyOfferDiscountsToProducts($relatedProducts);

        return view('customer.product_details', compact('product', 'relatedProducts'));
    }

    public function categoryProducts(Category $category)
    {
        $products = Product::with(['category', 'brand'])->where('status', 'Active')
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(12);
        return view('customer.category_products', compact('category', 'products'));
    }

    public function brandProducts(Brand $brand)
    {
        $products = Product::with(['category', 'brand'])->where('status', 'Active')
            ->where('brand_id', $brand->id)
            ->latest()
            ->paginate(12);
        return view('customer.brand_products', compact('brand', 'products'));
    }

    public function categories()
    {
        $categories = Category::where('status', 'Active')->withCount('products')->orderBy('name')->get();
        return view('customer.categories_index', compact('categories'));
    }

    public function brands()
    {
        $brands = Brand::where('status', 'Active')->withCount('products')->orderBy('name')->get();
        return view('customer.brands_index', compact('brands'));
    }
}
