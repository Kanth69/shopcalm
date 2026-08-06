<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Services\SearchService;
use App\Services\OfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseApiController
{
    protected $searchService;
    protected $offerService;

    public function __construct(SearchService $searchService, OfferService $offerService)
    {
        $this->searchService = $searchService;
        $this->offerService = $offerService;
    }

    /**
     * Unified endpoint for all product discovery.
     */
    public function index(Request $request)
    {
        $query = Product::where('status', 'Active');

        // 1. Search Query
        if ($request->filled('search')) {
            $searchResult = $this->searchService->search($request->input('search'));
            $query = $searchResult['query'];
        }

        // 2. Offer/Collection Filtering
        $activeOffer = null;
        if ($request->filled('offer')) {
            $offer = Offer::where('slug', $request->input('offer'))->active()->first();
            if ($offer) {
                $activeOffer = [
                    'name' => $offer->name,
                    'slug' => $offer->slug,
                    'description' => $offer->description,
                    'banner_image' => $offer->banner_image ? asset('storage/' . $offer->banner_image) : null,
                    'offer_type' => $offer->offer_type,
                    'end_date' => $offer->end_date
                ];
                // Intersect the base query with the offer query
                $offerQuery = $this->offerService->getProductsQuery($offer);
                $query->whereIn('id', $offerQuery->pluck('products.id'));
            }
        }

        // 3. Category & Brand Filtering (Support arrays or single slugs/ids)
        if ($request->filled('category')) {
            $categories = (array) $request->input('category');
            $query->whereHas('category', function ($q) use ($categories) {
                $q->whereIn('slug', $categories)->orWhereIn('id', $categories);
            });
        }

        if ($request->filled('brand')) {
            $brands = (array) $request->input('brand');
            $query->whereHas('brand', function ($q) use ($brands) {
                $q->whereIn('slug', $brands)->orWhereIn('id', $brands);
            });
        }

        // 4. Product Type Filtering
        if ($request->filled('product_type')) {
            $query->whereIn('product_type', (array) $request->input('product_type'));
        }

        // 5. Price Range Filtering
        if ($request->filled('min_price')) {
            $query->where(function($q) use ($request) {
                $q->whereRaw('COALESCE(offer_price, selling_price) >= ?', [$request->input('min_price')]);
            });
        }
        if ($request->filled('max_price')) {
            $query->where(function($q) use ($request) {
                $q->whereRaw('COALESCE(offer_price, selling_price) <= ?', [$request->input('max_price')]);
            });
        }

        // 6. Availability & Badges
        if ($request->has('availability')) {
            if ($request->input('availability') === 'in_stock') $query->where('stock', '>', 0);
            if ($request->input('availability') === 'out_of_stock') $query->where('stock', '=', 0);
        }
        if ($request->boolean('featured')) $query->where('featured', true);
        if ($request->boolean('trending')) $query->where('trending', true);
        if ($request->boolean('only_discounted')) $query->whereNotNull('offer_price');

        // 7. Rating Filter
        if ($request->filled('rating')) {
            $query->whereHas('reviews', function($q) {
                $q->where('status', 'Approved');
            }, '>=', 1)->whereRaw('(SELECT AVG(rating) FROM product_reviews WHERE product_id = products.id AND status = "Approved") >= ?', [$request->input('rating')]);
        }

        // 8. Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc': $query->orderByRaw('COALESCE(offer_price, selling_price) ASC'); break;
            case 'price_desc': $query->orderByRaw('COALESCE(offer_price, selling_price) DESC'); break;
            case 'rating_high': $query->withAvg(['reviews' => fn($q) => $q->where('status', 'Approved')], 'rating')->orderBy('reviews_avg_rating', 'desc'); break;
            case 'discount_high': $query->whereNotNull('offer_price')->orderByRaw('(selling_price - offer_price) / selling_price DESC'); break;
            case 'name_asc': $query->orderBy('name', 'asc'); break;
            case 'name_desc': $query->orderBy('name', 'desc'); break;
            default: $query->latest(); break; // 'latest'
        }

        // --- Fetch Filter Metadata ---
        $filterCategories = Category::where('status', 'Active')->orderBy('name')->get(['id', 'name', 'slug']);

        $categoryIds = (array) $request->input('category');
        $filterBrandsQuery = Brand::where('status', 1);
        if (!empty($categoryIds)) {
            $filterBrandsQuery->whereHas('products', function($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds)->orWhereHas('category', function($c) use ($categoryIds) {
                    $c->whereIn('slug', $categoryIds);
                });
            });
        }
        $filterBrands = $filterBrandsQuery->orderBy('name')->get(['id', 'name', 'slug']);

        $brandIds = (array) $request->input('brand');
        $filterTypesQuery = Product::where('status', 'Active')->whereNotNull('product_type');
        if (!empty($categoryIds)) {
            $filterTypesQuery->whereHas('category', function($c) use ($categoryIds) {
                $c->whereIn('id', $categoryIds)->orWhereIn('slug', $categoryIds);
            });
        }
        if (!empty($brandIds)) {
            $filterTypesQuery->whereHas('brand', function($b) use ($brandIds) {
                $b->whereIn('id', $brandIds)->orWhereIn('slug', $brandIds);
            });
        }
        $filterProductTypes = $filterTypesQuery->distinct()->pluck('product_type');

        // 9. Execute Pagination
        $products = $query->with(['category:id,name,slug', 'brand:id,name,slug'])->paginate(12)->appends($request->query());

        // Transform for frontend
        $products->getCollection()->transform(function ($product) {
            $product->main_image_url = $product->main_image && !str_starts_with($product->main_image, 'http') ? asset('storage/' . $product->main_image) : ($product->main_image ?? null);
            if ($product->offer_price) {
                $product->discount_percentage = round((($product->selling_price - $product->offer_price) / $product->selling_price) * 100);
            }
            $product->average_rating = $product->averageRating();
            return $product;
        });

        $responseData = [
            'products' => $products,
            'filters' => [
                'categories' => $filterCategories,
                'brands' => $filterBrands,
                'product_types' => $filterProductTypes,
                'price_bounds' => ['min' => 0, 'max' => 200000]
            ],
            'meta' => [
                'offer' => $activeOffer,
                'search' => $request->filled('search') ? ['original_term' => $request->input('search')] : null
            ]
        ];

        return $this->sendResponse($responseData, 'Products retrieved successfully.');
    }

    /**
     * Fetch full details for a single product.
     */
    public function show($slug)
    {
        $product = Product::with(['category', 'brand', 'galleryImages', 'reviews' => function($q) {
            $q->with('user')->where('status', 'Approved')->latest();
        }])->where('slug', $slug)->where('status', 'Active')->first();

        if (!$product) {
            return $this->sendError('Product not found or inactive.', [], 404);
        }

        // Standardize Images
        $product->main_image_url = $product->main_image && !str_starts_with($product->main_image, 'http') ? asset('storage/' . $product->main_image) : ($product->main_image ?? null);
        $product->galleryImages->transform(function ($img) {
            $img->image_url = $img->image && !str_starts_with($img->image, 'http') ? asset('storage/' . $img->image) : $img->image;
            return $img;
        });

        // Compute aggregations
        $product->average_rating = $product->averageRating();
        $product->total_reviews = $product->reviews->count();
        if ($product->offer_price) {
            $product->discount_percentage = round((($product->selling_price - $product->offer_price) / $product->selling_price) * 100);
        }

        $product->rating_breakdown = [
            5 => $product->ratingPercentage(5),
            4 => $product->ratingPercentage(4),
            3 => $product->ratingPercentage(3),
            2 => $product->ratingPercentage(2),
            1 => $product->ratingPercentage(1),
        ];

        // Fetch Related Products (same category)
        $relatedProducts = Product::with(['category:id,name,slug', 'brand:id,name,slug'])
            ->where('status', 'Active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get()
            ->transform(function ($rp) {
                $rp->main_image_url = $rp->main_image && !str_starts_with($rp->main_image, 'http') ? asset('storage/' . $rp->main_image) : ($rp->main_image ?? null);
                if ($rp->offer_price) {
                    $rp->discount_percentage = round((($rp->selling_price - $rp->offer_price) / $rp->selling_price) * 100);
                }
                $rp->average_rating = $rp->averageRating();
                return $rp;
            });

        return $this->sendResponse([
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ], 'Product details retrieved successfully.');
    }
}
