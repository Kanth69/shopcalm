<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferTarget;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use DB;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $offers = $query->orderBy('priority', 'desc')->latest()->paginate(10);

        return view('admin.offers.index', compact('offers'));
    }

    public function create()
    {
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        $products = Product::where('status', 'Active')->orderBy('name')->take(100)->get();

        return view('admin.offers.create', compact('categories', 'brands', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:MEGA_SALE,FLASH_DEAL,BANK_OFFER,CATEGORY_SALE,DIRECT_DISCOUNT',
            'badge_text' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|max:2048',
            'theme_color' => 'nullable|string|max:20',
            'discount_type' => 'required|in:PERCENTAGE,FLAT',
            'discount_value' => 'required|numeric|min:0.01',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'target_type' => 'nullable|in:CATEGORY,BRAND,PRODUCT',
            'target_id' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $imagePath = null;
            if ($request->hasFile('banner_image')) {
                $imagePath = $request->file('banner_image')->store('offers', 'public');
            }

            $offer = Offer::create([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']) . '-' . Str::random(5),
                'type' => $validated['type'],
                'badge_text' => $validated['badge_text'] ?? null,
                'description' => $validated['description'] ?? null,
                'banner_image' => $imagePath,
                'theme_color' => $validated['theme_color'] ?? '#2563eb',
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_purchase_amount' => $validated['min_purchase_amount'] ?? 0,
                'max_discount_amount' => $validated['max_discount_amount'] ?? null,
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'priority' => $validated['priority'] ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            if (!empty($validated['target_type']) && !empty($validated['target_id'])) {
                OfferTarget::create([
                    'offer_id' => $offer->id,
                    'target_type' => $validated['target_type'],
                    'target_id' => $validated['target_id'],
                ]);
            }

            // Auto-create Homepage Hero Banner if campaign banner image uploaded
            if ($imagePath && $request->has('create_hero_banner')) {
                Banner::create([
                    'offer_id' => $offer->id,
                    'banner_type' => 'CAMPAIGN_OFFER',
                    'title' => $offer->title,
                    'subtitle' => $offer->badge_text ?? "{$offer->discount_value}% OFF",
                    'desktop_image' => $imagePath,
                    'primary_button_text' => 'Shop Sale',
                    'primary_button_link' => '/shop',
                    'display_order' => 0,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('admin.offers.index')->with('toast', ['type' => 'success', 'title' => 'Created', 'message' => 'Sales Offer created successfully!']);
    }

    public function edit(Offer $offer)
    {
        $offer->load('targets');
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        $products = Product::where('status', 'Active')->orderBy('name')->take(100)->get();

        return view('admin.offers.edit', compact('offer', 'categories', 'brands', 'products'));
    }

    public function update(Request $request, Offer $offer)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:MEGA_SALE,FLASH_DEAL,BANK_OFFER,CATEGORY_SALE,DIRECT_DISCOUNT',
            'badge_text' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|max:2048',
            'theme_color' => 'nullable|string|max:20',
            'discount_type' => 'required|in:PERCENTAGE,FLAT',
            'discount_value' => 'required|numeric|min:0.01',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'target_type' => 'nullable|in:CATEGORY,BRAND,PRODUCT',
            'target_id' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $validated, $offer) {
            $imagePath = $offer->banner_image;
            if ($request->hasFile('banner_image')) {
                if ($offer->banner_image) {
                    Storage::disk('public')->delete($offer->banner_image);
                }
                $imagePath = $request->file('banner_image')->store('offers', 'public');
            }

            $offer->update([
                'title' => $validated['title'],
                'type' => $validated['type'],
                'badge_text' => $validated['badge_text'] ?? null,
                'description' => $validated['description'] ?? null,
                'banner_image' => $imagePath,
                'theme_color' => $validated['theme_color'] ?? '#2563eb',
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'min_purchase_amount' => $validated['min_purchase_amount'] ?? 0,
                'max_discount_amount' => $validated['max_discount_amount'] ?? null,
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'priority' => $validated['priority'] ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            $offer->targets()->delete();
            if (!empty($validated['target_type']) && !empty($validated['target_id'])) {
                OfferTarget::create([
                    'offer_id' => $offer->id,
                    'target_type' => $validated['target_type'],
                    'target_id' => $validated['target_id'],
                ]);
            }
        });

        return redirect()->route('admin.offers.index')->with('toast', ['type' => 'success', 'title' => 'Updated', 'message' => 'Offer updated successfully!']);
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();
        return redirect()->route('admin.offers.index')->with('toast', ['type' => 'success', 'title' => 'Deleted', 'message' => 'Offer campaign archived.']);
    }
}
