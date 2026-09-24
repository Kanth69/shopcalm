<?php

namespace App\Http\Controllers\ProductManager;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    /**
     * Display brand listing for Product Manager.
     */
    public function index(Request $request): View
    {
        $query = Brand::withCount('products');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', (int)$request->status);
        }

        $brands = $query->latest()->paginate(12)->withQueryString();

        return view('product-manager.brands.index', compact('brands'));
    }

    /**
     * Show brand creation form.
     */
    public function create(): View
    {
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        if ($categories->isEmpty()) {
            $categories = Category::orderBy('name')->get();
        }

        return view('product-manager.brands.create', compact('categories'));
    }

    /**
     * Store a newly created brand.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:brands,name'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:brands,slug'],
            'logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'status'      => ['required', 'in:0,1'],
        ]);

        if (empty($validated['category_id'])) {
            $defaultCat = Category::first();
            $validated['category_id'] = $defaultCat ? $defaultCat->id : null;
        }

        if (empty($validated['slug'])) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $count = 1;
            while (Brand::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $validated['slug'] = $slug;
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        Brand::create($validated);

        return redirect()->route('product-manager.brands.index')->with('toast', [
            'type' => 'success',
            'title' => 'Brand Added',
            'message' => "Brand '{$validated['name']}' has been created.",
        ]);
    }

    /**
     * Show brand edit form.
     */
    public function edit(Brand $brand): View
    {
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        if ($categories->isEmpty()) {
            $categories = Category::orderBy('name')->get();
        }

        return view('product-manager.brands.edit', compact('brand', 'categories'));
    }

    /**
     * Update brand details.
     */
    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:brands,name,' . $brand->id],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:brands,slug,' . $brand->id],
            'logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'status'      => ['required', 'in:0,1'],
        ]);

        if (empty($validated['category_id'])) {
            $validated['category_id'] = $brand->category_id ?? (Category::value('id') ?? null);
        }

        if (!empty($validated['name']) && $validated['name'] !== $brand->name && empty($validated['slug'])) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $count = 1;
            while (Brand::where('slug', $slug)->where('id', '!=', $brand->id)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $validated['slug'] = $slug;
        }

        if ($request->hasFile('logo')) {
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($validated);

        return redirect()->route('product-manager.brands.index')->with('toast', [
            'type' => 'success',
            'title' => 'Brand Updated',
            'message' => "Brand '{$brand->name}' updated successfully.",
        ]);
    }
}
