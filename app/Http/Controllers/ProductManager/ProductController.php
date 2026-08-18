<?php

namespace App\Http\Controllers\ProductManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display all catalog products for the Product Manager.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'brand'])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('status', 'Approved')], 'rating');

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

        // Metrics for filter tabs
        $totalCount = Product::count();
        $activeCount = Product::where('status', 'Active')->count();
        $pendingCount = Product::where('status', 'Pending_Approval')->count();
        $rejectedCount = Product::where('status', 'Rejected')->count();
        $inactiveCount = Product::where('status', 'Inactive')->count();

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
        $products = Product::with(['category', 'brand'])
            ->where('status', 'Pending_Approval')
            ->latest()
            ->paginate(15);

        return view('product-manager.products.pending', compact('products'));
    }

    /**
     * Dedicated view for rejected products with admin feedback notes.
     */
    public function rejected(Request $request): View
    {
        $products = Product::with(['category', 'brand'])
            ->where('status', 'Rejected')
            ->latest()
            ->paginate(15);

        return view('product-manager.products.rejected', compact('products'));
    }

    /**
     * Show the product creation form.
     */
    public function create(): View
    {
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();

        return view('product-manager.products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created product and submit for Admin approval.
     */
    public function store(ProductRequest $request): RedirectResponse
    {
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
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        // Enforce approval workflow: New products by Product Manager start as Pending_Approval
        $validated['status'] = 'Pending_Approval';
        $validated['rejection_reason'] = null;
        $validated['submitted_by'] = Auth::id();
        $validated['featured'] = $request->boolean('featured');
        $validated['trending'] = $request->boolean('trending');

        $product = Product::create($validated);

        // Handle Multiple Gallery Images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('products/gallery', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                ]);
            }
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
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $product->load(['category', 'brand', 'galleryImages']);

        return view('product-manager.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update product details.
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
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
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        // Approval workflow: If product was Rejected or Pending, keep/refresh Pending_Approval
        if ($product->status === 'Rejected' || $product->status === 'Pending_Approval') {
            $validated['status'] = 'Pending_Approval';
            $validated['rejection_reason'] = null;
        }
        // If product was Active, update details and preserve Active status to prevent live store disruption

        $validated['featured'] = $request->boolean('featured');
        $validated['trending'] = $request->boolean('trending');
        $validated['submitted_by'] = Auth::id();

        $product->update($validated);

        // Handle Additional Gallery Images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $path = $file->store('products/gallery', 'public');
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
        $product->update([
            'status'           => 'Pending_Approval',
            'rejection_reason' => null,
            'submitted_by'     => Auth::id(),
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
        if ($product->status === 'Pending_Approval') {
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
    public function deleteGalleryImage(int $id): RedirectResponse
    {
        $image = ProductImage::findOrFail($id);
        if ($image->image && Storage::disk('public')->exists($image->image)) {
            Storage::disk('public')->delete($image->image);
        }
        $image->delete();

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'Image Deleted',
            'message' => 'Gallery photo removed successfully.',
        ]);
    }
}
