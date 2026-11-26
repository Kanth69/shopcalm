<?php

namespace App\Http\Controllers\ProductManager;

use App\Enums\MovementType;
use App\Enums\StockSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StockMovement;
use App\Services\ImageOptimizerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ImageOptimizerService $imageOptimizer
    ) {}

    protected function getCurrentUser()
    {
        return auth('product_manager')->user() ?? auth('admin')->user() ?? auth()->user();
    }

    /**
     * Display all catalog products for the Product Manager.
     */
    public function index(Request $request): View
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $query = Product::with(['category', 'brand'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating');

        if (!$isSuperAdmin) {
            $query->where('submitted_by', $user?->id);
        } elseif ($request->filled('submitted_by')) {
            $query->where('submitted_by', $request->submitted_by);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Brand Filter
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Stock Level Filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            } elseif ($request->stock_status === 'in_stock') {
                $query->where('stock', '>', 10);
            }
        }

        // Search Query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('brand', fn($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'stock_asc':
                $query->orderBy('stock', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(15)->withQueryString();

        // Metrics for filter tabs (strictly scoped to this vendor or global for admin)
        $metricsQuery = Product::query();
        if (!$isSuperAdmin) {
            $metricsQuery->where('submitted_by', $user?->id);
        } elseif ($request->filled('submitted_by')) {
            $metricsQuery->where('submitted_by', $request->submitted_by);
        }

        $totalCount = (clone $metricsQuery)->count();
        $activeCount = (clone $metricsQuery)->where('status', 'Active')->count();
        $pendingCount = (clone $metricsQuery)->where('status', 'Pending_Approval')->count();
        $rejectedCount = (clone $metricsQuery)->where('status', 'Rejected')->count();
        $inactiveCount = (clone $metricsQuery)->where('status', 'Inactive')->count();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('product-manager.products.index', compact(
            'products',
            'categories',
            'brands',
            'totalCount',
            'activeCount',
            'pendingCount',
            'rejectedCount',
            'inactiveCount'
        ));
    }

    /**
     * Dedicated view for products awaiting Admin review.
     */
    public function pending(Request $request): View
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $query = Product::with(['category', 'brand'])
            ->where('status', 'Pending_Approval');

        if (!$isSuperAdmin) {
            $query->where('submitted_by', $user?->id);
        }

        $products = $query->latest()->paginate(15);

        return view('product-manager.products.pending', compact('products'));
    }

    /**
     * Dedicated view for rejected products with admin feedback notes.
     */
    public function rejected(Request $request): View
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $query = Product::with(['category', 'brand'])
            ->where('status', 'Rejected');

        if (!$isSuperAdmin) {
            $query->where('submitted_by', $user?->id);
        }

        $products = $query->latest()->paginate(15);

        return view('product-manager.products.rejected', compact('products'));
    }

    /**
     * Display the specified product details and full audit/stock history.
     */
    public function show(Product $product): View
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        if (!$isSuperAdmin && $product->submitted_by !== $user?->id) {
            abort(403, 'Unauthorized. You can only view your own products.');
        }

        $product->load([
            'category',
            'brand',
            'galleryImages',
            'reviews' => fn($q) => $q->latest()->take(10),
            'reviews.user',
            'stockMovements' => fn($q) => $q->latest()->take(15),
            'stockMovements.createdBy',
            'rejectionReasons' => fn($q) => $q->latest(),
            'rejectionReasons.rejector',
        ]);

        return view('product-manager.products.show', compact('product'));
    }

    /**
     * Show the product creation form.
     */
    public function create(): View
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        if ($categories->isEmpty()) {
            $categories = Category::orderBy('name')->get();
        }

        $brands = Brand::where('status', 1)->orderBy('name')->get();
        if ($brands->isEmpty()) {
            $brands = Brand::orderBy('name')->get();
        }

        return view('product-manager.products.create', compact('categories', 'brands', 'isSuperAdmin'));
    }

    /**
     * Store a newly created product and submit for Admin approval.
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $validated = $request->validated();

        // Generate clean unique slug
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }
        $validated['slug'] = $slug;

        // Auto-generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

        // Handle Main Product Image
        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $this->imageOptimizer->optimizeAndStore($request->file('main_image'), 'products/main');
        }

        $validated['mrp'] = $request->filled('mrp') ? (float)$request->mrp : null;
        $validated['cost_price'] = $request->filled('cost_price') ? (float)$request->cost_price : null;
        $validated['price'] = $validated['cost_price'] ?? 0;
        $validated['is_replaceable'] = $request->boolean('is_replaceable');
        $validated['replacement_window_days'] = $validated['is_replaceable'] ? ($request->input('replacement_window_days', 7) ?: 7) : 0;
        $validated['status'] = 'Pending_Approval';
        $validated['rejection_reason'] = null;
        $validated['submitted_by'] = $isSuperAdmin ? ($request->input('submitted_by', $user?->id) ?: $user?->id) : $user?->id;
        $validated['featured'] = $request->boolean('featured');
        $validated['trending'] = $request->boolean('trending');
        $validated['has_options'] = $request->boolean('has_options');

        if ($validated['has_options'] && $request->filled('option_keys') && $request->filled('option_values')) {
            $keys = $request->input('option_keys', []);
            $values = $request->input('option_values', []);
            $existingImages = $request->input('option_existing_images', []);
            $stocksMap = [];
            $optionImagesMap = [];

            foreach ($keys as $idx => $keyName) {
                $trimmedKey = trim($keyName);
                if ($trimmedKey !== '') {
                    $stocksMap[$trimmedKey] = max(0, (int) ($values[$idx] ?? 0));
                    if (!empty($existingImages[$idx])) {
                        $optionImagesMap[$trimmedKey] = $existingImages[$idx];
                    }
                }
            }

            if ($request->hasFile('option_image_files')) {
                foreach ($request->file('option_image_files') as $idx => $file) {
                    if ($file && $file->isValid()) {
                        $trimmedKey = trim($keys[$idx] ?? '');
                        if ($trimmedKey !== '') {
                            $path = $this->imageOptimizer->optimizeAndStore($file, 'products/variants');
                            $optionImagesMap[$trimmedKey] = $path;
                        }
                    }
                }
            }

            // Also map by primary color name for quick lookup
            foreach ($optionImagesMap as $k => $imgPath) {
                if (str_contains($k, ' - ')) {
                    $colorPart = trim(explode(' - ', $k, 2)[0] ?? '');
                    if ($colorPart !== '' && !isset($optionImagesMap[$colorPart])) {
                        $optionImagesMap[$colorPart] = $imgPath;
                    }
                }
            }

            $validated['option_stocks'] = $stocksMap;
            $validated['option_images'] = !empty($optionImagesMap) ? $optionImagesMap : null;
            if (!empty($stocksMap)) {
                $validated['stock'] = array_sum($stocksMap);
            }
        } elseif (!$validated['has_options']) {
            $validated['option_stocks'] = null;
            $validated['option_images'] = null;
            $validated['option_type'] = null;
        }

        $product = Product::create($validated);

        // Handle Multiple Gallery Images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $this->imageOptimizer->optimizeAndStore($file, 'products/gallery');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                ]);
            }
        }

        // Record Initial Inventory Stock Movement
        if ($product->stock > 0) {
            StockMovement::create([
                'product_id'    => $product->id,
                'movement_type' => MovementType::PURCHASE,
                'source'        => StockSource::PURCHASE,
                'quantity'      => $product->stock,
                'stock_before'  => 0,
                'stock_after'   => $product->stock,
                'notes'         => 'Initial inventory recorded upon product creation',
                'created_by'    => $user?->id ?? Auth::id(),
            ]);
        }

        return redirect()->route('product-manager.products.pending')->with('toast', [
            'type' => 'success',
            'title' => 'Submitted for Approval',
            'message' => "Product '{$product->name}' has been created and sent to Admin for review.",
        ]);
    }

    /**
     * Show the product edit form.
     */
    public function edit(Product $product): View
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        if (!$isSuperAdmin && $product->submitted_by !== $user?->id) {
            abort(403, 'Unauthorized. You can only edit your own products.');
        }

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $product->load(['category', 'brand', 'galleryImages']);

        return view('product-manager.products.edit', compact('product', 'categories', 'brands', 'isSuperAdmin'));
    }

    /**
     * Update product details.
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        if (!$isSuperAdmin && $product->submitted_by !== $user?->id) {
            abort(403, 'Unauthorized. You can only update your own products.');
        }

        $validated = $request->validated();

        // Handle Slug modification
        if ($validated['name'] !== $product->name) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $count = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $validated['slug'] = $slug;
        }

        // Handle Main Image Replacement
        if ($request->hasFile('main_image')) {
            if ($product->main_image && Storage::disk('public')->exists($product->main_image)) {
                Storage::disk('public')->delete($product->main_image);
            }
            $validated['main_image'] = $this->imageOptimizer->optimizeAndStore($request->file('main_image'), 'products/main');
        }

        // Approval workflow:
        // If product was Rejected, editing it resolves the rejection and moves it to Pending_Approval.
        // If product is already Active or Inactive, editing does NOT require admin approval (keeps current status).
        if ($product->status === 'Rejected') {
            $validated['status'] = 'Pending_Approval';
            $validated['rejection_reason'] = null;
            $product->rejectionReasons()->where('status', 'active')->update(['status' => 'resolved']);
        } elseif ($product->status === 'Pending_Approval') {
            $validated['status'] = 'Pending_Approval';
        } else {
            $validated['status'] = $product->status;
        }

        $validated['featured'] = $request->boolean('featured');
        $validated['trending'] = $request->boolean('trending');
        $validated['mrp'] = $request->filled('mrp') ? (float)$request->mrp : null;
        $validated['cost_price'] = $request->filled('cost_price') ? (float)$request->cost_price : null;
        $validated['is_replaceable'] = $request->boolean('is_replaceable');
        $validated['replacement_window_days'] = $validated['is_replaceable'] ? ($request->input('replacement_window_days', 7) ?: 7) : 0;

        // Selling Price is strictly controlled and decided upon Admin approval in /admin/products
        unset($validated['price']);

        $validated['has_options'] = $request->boolean('has_options');

        if ($validated['has_options'] && $request->filled('option_keys') && $request->filled('option_values')) {
            $keys = $request->input('option_keys', []);
            $values = $request->input('option_values', []);
            $existingImages = $request->input('option_existing_images', []);
            $stocksMap = [];
            $optionImagesMap = [];

            foreach ($keys as $idx => $keyName) {
                $trimmedKey = trim($keyName);
                if ($trimmedKey !== '') {
                    $stocksMap[$trimmedKey] = max(0, (int) ($values[$idx] ?? 0));
                    if (!empty($existingImages[$idx])) {
                        $optionImagesMap[$trimmedKey] = $existingImages[$idx];
                    }
                }
            }

            if ($request->hasFile('option_image_files')) {
                foreach ($request->file('option_image_files') as $idx => $file) {
                    if ($file && $file->isValid()) {
                        $trimmedKey = trim($keys[$idx] ?? '');
                        if ($trimmedKey !== '') {
                            $path = $this->imageOptimizer->optimizeAndStore($file, 'products/variants');
                            $optionImagesMap[$trimmedKey] = $path;
                        }
                    }
                }
            }

            // Also map by primary color name for quick lookup
            foreach ($optionImagesMap as $k => $imgPath) {
                if (str_contains($k, ' - ')) {
                    $colorPart = trim(explode(' - ', $k, 2)[0] ?? '');
                    if ($colorPart !== '' && !isset($optionImagesMap[$colorPart])) {
                        $optionImagesMap[$colorPart] = $imgPath;
                    }
                }
            }

            $validated['option_stocks'] = $stocksMap;
            $validated['option_images'] = !empty($optionImagesMap) ? $optionImagesMap : null;
            if (!empty($stocksMap)) {
                $validated['stock'] = array_sum($stocksMap);
            }
        } elseif (!$validated['has_options']) {
            $validated['option_stocks'] = null;
            $validated['option_images'] = null;
            $validated['option_type'] = null;
        }

        $product->update($validated);

        // Handle Additional Gallery Images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $this->imageOptimizer->optimizeAndStore($file, 'products/gallery');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                ]);
            }
        }

        $redirectRoute = $product->status === 'Pending_Approval' 
            ? route('product-manager.products.pending') 
            : route('product-manager.products.index');

        return redirect($redirectRoute)->with('toast', [
            'type' => 'success',
            'title' => 'Product Updated',
            'message' => "Product '{$product->name}' updated successfully.",
        ]);
    }

    /**
     * Resubmit a rejected product for Admin approval.
     */
    public function resubmit(Product $product): RedirectResponse
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        if (!$isSuperAdmin && $product->submitted_by !== $user?->id) {
            abort(403, 'Unauthorized.');
        }

        $product->update([
            'status'           => 'Pending_Approval',
            'rejection_reason' => null,
            'submitted_by'     => $product->submitted_by ?? $user?->id,
        ]);

        $product->rejectionReasons()->where('status', 'active')->update(['status' => 'resolved']);

        return redirect()->route('product-manager.products.pending')->with('toast', [
            'type' => 'success',
            'title' => 'Resubmitted for Review',
            'message' => "Product '{$product->name}' has been resubmitted to Admin for approval.",
        ]);
    }

    /**
     * Toggle product status between Active and Inactive (only for already approved products).
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        if (!$isSuperAdmin && $product->submitted_by !== $user?->id) {
            abort(403, 'Unauthorized.');
        }

        if (!$isSuperAdmin && $product->status === 'Pending_Approval') {
            return back()->with('toast', [
                'type' => 'warning',
                'title' => 'Approval Required',
                'message' => 'This product is awaiting Admin approval and cannot be made active directly.',
            ]);
        }

        $newStatus = ($product->status === 'Active') ? 'Inactive' : 'Active';
        $product->update(['status' => $newStatus]);

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'Status Updated',
            'message' => "Product status changed to {$newStatus}.",
        ]);
    }

    /**
     * Delete a gallery image.
     */
    public function deleteGalleryImage(Request $request, int $id)
    {
        $user = $this->getCurrentUser();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $image = ProductImage::with('product')->findOrFail($id);
        if (!$isSuperAdmin && $image->product && $image->product->submitted_by !== $user?->id) {
            abort(403, 'Unauthorized.');
        }

        if ($image->image && Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }
        $image->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gallery photo removed successfully.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'Image Deleted',
            'message' => 'Gallery photo removed successfully.',
        ]);
    }
}
