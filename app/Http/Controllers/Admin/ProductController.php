<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest': $query->oldest(); break;
            case 'price_low': $query->orderBy('price', 'asc'); break;
            case 'price_high': $query->orderBy('price', 'desc'); break;
            default: $query->latest(); break;
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();

        $totalCount = Product::count();
        $activeCount = Product::where('status', 'Active')->count();
        $pendingCount = Product::where('status', 'Pending_Approval')->count();
        $rejectedCount = Product::where('status', 'Rejected')->count();
        $inactiveCount = Product::where('status', 'Inactive')->count();

        return view('admin.products.index', compact(
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
     * Approve a pending product and make it live on the storefront.
     */
    public function approve(Product $product)
    {
        $this->authorize('approve-products');

        $product->update([
            'status'           => 'Active',
            'rejection_reason' => null,
        ]);

        $product->rejectionReasons()->where('status', 'active')->update(['status' => 'resolved']);

        return back()->with('toast', [
            'type'    => 'success',
            'title'   => 'Product Approved',
            'message' => "Product '{$product->name}' is now approved and live on the storefront.",
        ]);
    }

    /**
     * Reject a product with mandatory feedback reason.
     */
    public function reject(Request $request, Product $product)
    {
        $this->authorize('approve-products');

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Please provide a clear reason for rejecting this product.',
            'rejection_reason.min'      => 'Rejection reason must be at least 5 characters.',
        ]);

        $product->update([
            'status'           => 'Rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $product->rejectionReasons()->create([
            'rejected_by' => auth()->id(),
            'reason'      => $request->rejection_reason,
            'status'      => 'active',
        ]);

        return back()->with('toast', [
            'type'    => 'info',
            'title'   => 'Product Rejected',
            'message' => "Product '{$product->name}' has been marked as rejected and sent back to Product Manager.",
        ]);
    }

    public function create()
    {
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(ProductRequest $request)
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        // Handle duplicate slug
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products/main', 'public');
        }

        $validated['featured'] = $request->has('featured');
        $validated['trending'] = $request->has('trending');

        $product = Product::create($validated);

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $path = $image->store('products/gallery', 'public');
                $product->galleryImages()->create(['image' => $path]);
            }
        }

        return redirect()->route('admin.products.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Product created successfully.']);
    }

    public function show(Product $product, \App\Services\OfferService $offerService)
    {
        $product->load(['category', 'brand', 'galleryImages', 'reviews.user']);
        $product = $offerService->applyOfferDiscountToProduct($product);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        $product->load('galleryImages');
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        // Handle duplicate slug
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Product::where('slug', $validated['slug'])->where('id', '!=', $product->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        if ($request->hasFile('main_image')) {
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $validated['main_image'] = $request->file('main_image')->store('products/main', 'public');
        }

        $validated['featured'] = $request->has('featured');
        $validated['trending'] = $request->has('trending');

        $product->update($validated);

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $path = $image->store('products/gallery', 'public');
                $product->galleryImages()->create(['image' => $path]);
            }
        }

        return redirect()->route('admin.products.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Product updated successfully.']);
    }

    public function destroy(Request $request, Product $product)
    {
        $product->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);
        }

        return redirect()->route('admin.products.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Product deleted successfully.']);
    }

    public function deleteGalleryImage($id)
    {
        $image = ProductImage::findOrFail($id);
        Storage::disk('public')->delete($image->image);
        $image->delete();

        return response()->json(['success' => 'Gallery image deleted successfully.']);
    }
}
