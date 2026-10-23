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
            return Banner::active()->orderBy('display_order')->get(['id','offer_id','banner_type','bg_gradient','title','subtitle','desktop_image','mobile_image','primary_button_text','primary_button_link','display_order','is_active']);
        });

        $activeOffers = Cache::remember('home_active_offers', 300, function() {
            return \App\Models\Offer::live()->orderBy('priority', 'desc')->get();
        });

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
            return Brand::where('status', 1)
                ->withCount(['products' => fn($q) => $q->where('status', 'Active')])
                ->orderBy('products_count', 'desc')
                ->orderBy('name', 'asc')
                ->take(6)
                ->get();
        });
        $totalBrandsCount = Cache::remember('home_total_brands_count', 3600, function () {
            return Brand::where('status', 1)->count();
        });

        $flashDeals = $offerService->getLiveFlashDeals();
        $flashProducts = collect();
        $activeFlashDeal = $flashDeals->first();

        if ($activeFlashDeal) {
            $query = Product::with(['category', 'brand'])
                ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')
                ->where('status', 'Active');

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

        $settings = Setting::getAll();

        $upcomingDeal = Cache::remember('home_upcoming_deal', 300, function () {
            return \App\Models\Offer::where('start_time', '>', now())->orderBy('start_time', 'asc')->first();
        });

        $testimonials = Cache::remember('home_testimonials', 300, function() {
            return \App\Models\ProductReview::with(['user', 'product'])
                ->where('status', 'Approved')
                ->where('rating', '>=', 4)
                ->latest()
                ->take(8)
                ->get();
        });

        $exploreProducts = Cache::remember('home_explore_products_p1', 300, function() {
            return Product::with(['category', 'brand'])
                ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')
                ->where('status', 'Active')
                ->latest()
                ->take(8)
                ->get();
        });
        $exploreProducts = $offerService->applyOfferDiscountsToProducts($exploreProducts);

        $totalExploreCount = Cache::remember('home_total_active_products', 600, function() {
            return Product::where('status', 'Active')->count();
        });

        return view('customer.home', compact(
            'banners',
            'activeOffers',
            'featuredProducts',
            'trendingProducts',
            'latestProducts',
            'flashProducts',
            'activeFlashDeal',
            'categories',
            'brands',
            'settings',
            'liveMegaSale',
            'upcomingDeal',
            'testimonials',
            'exploreProducts',
            'totalExploreCount',
            'totalBrandsCount'
        ));
    }

    public function shop(Request $request)
    {
        // 1. Live Suggestion Dropdown API (Matches Companion Mobile App Search)
        if ($request->filled('suggestion') || ($request->ajax() && $request->filled('q') && !$request->has('page') && !$request->has('category') && !$request->has('brand') && !$request->has('sort') && !$request->has('min_price'))) {
            $term = trim((string)($request->q ?? ''));
            if (mb_strlen($term) < 1) {
                return response()->json([
                    'term'        => '',
                    'total_count' => 0,
                    'structured'  => [],
                    'products'    => [],
                ]);
            }

            $termLower = mb_strtolower($term);
            $structured = [];
            $uniqueKeys = [];

            // 1. Matches from Categories
            $categories = Category::where('status', 'Active')
                ->where('name', 'like', "%{$term}%")
                ->take(3)
                ->get(['id', 'name', 'slug']);

            foreach ($categories as $cat) {
                $key = 'category:' . mb_strtolower($cat->name);
                if (!isset($uniqueKeys[$key])) {
                    $uniqueKeys[$key] = true;
                    $structured[] = [
                        'text'     => $cat->name,
                        'type'     => 'category',
                        'url'      => route('shop', ['q' => $cat->name]),
                    ];
                }
            }

            // 2. Matches from Brands
            $brands = Brand::where('status', 1)
                ->where('name', 'like', "%{$term}%")
                ->take(3)
                ->get(['id', 'name', 'slug']);

            foreach ($brands as $b) {
                $key = 'brand:' . mb_strtolower($b->name);
                if (!isset($uniqueKeys[$key])) {
                    $uniqueKeys[$key] = true;
                    $structured[] = [
                        'text'     => $b->name,
                        'type'     => 'brand',
                        'url'      => route('shop', ['q' => $b->name]),
                    ];
                }
            }

            // 3. Matches from Products (name, tags, category, brand & multi-token)
            $tokens = array_filter(preg_split('/\s+/', $term));
            $productQuery = Product::where('status', 'Active')
                ->where(function ($q) use ($term, $tokens) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('tags', 'like', "%{$term}%")
                      ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$term}%"))
                      ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$term}%"));

                    if (count($tokens) > 1) {
                        $q->orWhere(function ($sub) use ($tokens) {
                            foreach ($tokens as $tk) {
                                $sub->where(function ($w) use ($tk) {
                                    $w->where('name', 'like', "%{$tk}%")
                                      ->orWhere('tags', 'like', "%{$tk}%")
                                      ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$tk}%"))
                                      ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$tk}%"));
                                });
                            }
                        });
                    }
                });

            $totalCount = (clone $productQuery)->count();

            $nameProducts = $productQuery
                ->take(8)
                ->get(['id', 'name', 'slug']);

            foreach ($nameProducts as $p) {
                $key = 'product:' . mb_strtolower($p->name);
                if (!isset($uniqueKeys[$key])) {
                    $uniqueKeys[$key] = true;
                    $structured[] = [
                        'text'     => $p->name,
                        'type'     => 'product',
                        'url'      => route('product.show', $p->slug),
                    ];
                }
            }

            return response()->json([
                'term'        => $term,
                'total_count' => $totalCount,
                'structured'  => $structured,
            ]);
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
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }
        if ($request->boolean('only_discounted') || $request->boolean('on_sale') || $request->filled('offer')) {
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
            } else {
                $query->whereRaw('1 = 0');
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
                'active_filters_html' => '',
                'has_more'            => $products->hasMorePages(),
                'next_page'           => $products->hasMorePages() ? $products->currentPage() + 1 : null,
                'current_page'        => $products->currentPage(),
                'loaded_count'        => min($products->total(), $products->currentPage() * $products->perPage()),
                'total_count'         => $products->total(),
                'remaining_count'     => max(0, $products->total() - ($products->currentPage() * $products->perPage())),
            ]);
        }

        $categories = Cache::remember('all_categories', 3600, function () {
            return Category::where('status', 'Active')
                ->withCount(['products' => function ($q) {
                    $q->where('status', 'Active');
                }])
                ->orderBy('name')
                ->get();
        });
        $brands = Cache::remember('all_brands', 3600, function () {
            return Brand::where('status', 1)
                ->withCount(['products' => function ($q) {
                    $q->where('status', 'Active');
                }])
                ->orderBy('name')
                ->get();
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
                $q->whereIn('category_id', $categoryIds)->where('status', 'Active');
            });
        }

        $brandsQuery->withCount(['products' => function ($q) use ($categoryIds) {
            $q->where('status', 'Active');
            if (!empty($categoryIds)) {
                $q->whereIn('category_id', $categoryIds);
            }
        }]);

        $brands = $brandsQuery->orderBy('name')->get();

        return view('customer.components.shop.brand-filter-options', compact('brands', 'prefix'));
    }

    public function productDetails(Product $product, OfferService $offerService)
    {
        $product->load([
            'category', 
            'brand', 
            'galleryImages', 
        ]);
        $product->loadAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating');

        // 1. Single aggregate query for total review count & star distribution
        $ratingCounts = $product->approvedReviews()
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $totalReviewsCount = array_sum($ratingCounts);

        $ratingPercentages = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingPercentages[$i] = $totalReviewsCount > 0
                ? (int) round((($ratingCounts[$i] ?? 0) / $totalReviewsCount) * 100)
                : 0;
        }

        // 2. Initial set of approved reviews (first 4 reviews)
        $initialReviews = $product->approvedReviews()
            ->with('user')
            ->latest()
            ->take(4)
            ->get();

        $product->setRelation('approvedReviews', $initialReviews);
        
        $product = $offerService->applyOfferDiscountToProduct($product);

        $relatedProducts = Product::with(['category', 'brand'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')
            ->where('status', 'Active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();
            
        $relatedProducts = $offerService->applyOfferDiscountsToProducts($relatedProducts);

        return view('customer.product_details', compact(
            'product', 
            'relatedProducts', 
            'initialReviews', 
            'totalReviewsCount', 
            'ratingPercentages'
        ));
    }

    public function loadMoreReviews(Request $request, Product $product)
    {
        $perPage = 4;
        $page = (int) $request->input('page', 2);

        $reviews = $product->approvedReviews()
            ->with('user')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $html = '';
        foreach ($reviews as $review) {
            $html .= view('customer.components.reviews.review-card', compact('review'))->render();
        }

        $total = $reviews->total();
        $loadedSoFar = min($total, $page * $perPage);
        $remaining = max(0, $total - $loadedSoFar);

        return response()->json([
            'html'        => $html,
            'hasMore'     => $reviews->hasMorePages(),
            'nextPage'    => $reviews->hasMorePages() ? $page + 1 : null,
            'loadedSoFar' => $loadedSoFar,
            'remaining'   => $remaining,
            'total'       => $total,
        ]);
    }

    public function categoryProducts(Category $category, OfferService $offerService)
    {
        $products = Product::with(['category', 'brand'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')
            ->where('status', 'Active')
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(12);

        $offerService->applyOfferDiscountsToProducts($products->getCollection());

        return view('customer.category_products', compact('category', 'products'));
    }

    public function brandProducts(Brand $brand, OfferService $offerService)
    {
        $products = Product::with(['category', 'brand'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')
            ->where('status', 'Active')
            ->where('brand_id', $brand->id)
            ->latest()
            ->paginate(12);

        $offerService->applyOfferDiscountsToProducts($products->getCollection());

        return view('customer.brand_products', compact('brand', 'products'));
    }

    public function categories()
    {
        $categories = Cache::remember('all_categories_index', 3600, function () {
            return Category::where('status', 'Active')
                ->withCount(['products' => fn($q) => $q->where('status', 'Active')])
                ->withMin(['products as min_price' => fn($q) => $q->where('status', 'Active')], 'price')
                ->orderBy('name')
                ->get();
        });

        return view('customer.categories_index', compact('categories'));
    }

    public function brands()
    {
        $brands = Cache::remember('all_brands_index', 3600, function () {
            return Brand::where('status', 1)
                ->withCount(['products' => fn($q) => $q->where('status', 'Active')])
                ->orderBy('name')
                ->get();
        });

        return view('customer.brands_index', compact('brands'));
    }

    public function exploreMoreHomeProducts(Request $request, OfferService $offerService)
    {
        $perPage = 8;
        $page = (int) $request->input('page', 2);

        $products = Product::with(['category', 'brand'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating')
            ->where('status', 'Active')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $offerService->applyOfferDiscountsToProducts($products->items());

        $html = '';
        foreach ($products as $product) {
            $html .= '<div class="col d-flex">' . view('customer.components.product-card', compact('product'))->render() . '</div>';
        }

        $total = $products->total();
        $loaded = min($total, $page * $perPage);
        $remaining = max(0, $total - $loaded);

        return response()->json([
            'html'        => $html,
            'hasMore'     => $products->hasMorePages(),
            'nextPage'    => $products->hasMorePages() ? $page + 1 : null,
            'loadedCount' => $loaded,
            'remaining'   => $remaining,
            'total'       => $total,
        ]);
    }
}
