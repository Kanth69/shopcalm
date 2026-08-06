<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    private function formData(): array
    {
        return [
            'offers'     => Offer::where('is_active', true)->orderBy('title')->get(),
            'categories' => Category::where('status', 'Active')->orderBy('name')->get(),
            'brands'     => Brand::where('status', 1)->orderBy('name')->get(),
        ];
    }

    public function index(Request $request)
    {
        $query = Banner::with('offer');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%")
                  ->orWhere('subtitle', 'like', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('banner_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $banners = $query->orderBy('display_order')->paginate(15)->withQueryString();

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create', $this->formData());
    }

    public function store(BannerRequest $request)
    {
        DB::transaction(function() use ($request) {
            $data = $request->validated();
            $data['created_by'] = auth()->id();

            // Auto-build the link from category or brand dropdown selection
            if ($request->banner_type === 'CATEGORY_HEADER' && $request->filled('link_category_id')) {
                $data['primary_button_link'] = '/shop?category=' . $request->link_category_id;
            } elseif ($request->banner_type === 'BRAND_PROMO' && $request->filled('link_brand_id')) {
                $data['primary_button_link'] = '/shop?brand=' . $request->link_brand_id;
            }

            if ($request->hasFile('desktop_image')) {
                $data['desktop_image'] = $request->file('desktop_image')->store('banners/desktop', 'public');
            }
            if ($request->hasFile('mobile_image')) {
                $data['mobile_image'] = $request->file('mobile_image')->store('banners/mobile', 'public');
            }

            Banner::create($data);
            Cache::forget('home_banners');
        });

        return redirect()->route('admin.banners.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Banner created successfully.']);
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', array_merge(['banner' => $banner], $this->formData()));
    }

    public function update(BannerRequest $request, Banner $banner)
    {
        DB::transaction(function() use ($request, $banner) {
            $data = $request->validated();
            $data['updated_by'] = auth()->id();

            // Auto-build the link from category or brand dropdown selection
            if ($request->banner_type === 'CATEGORY_HEADER' && $request->filled('link_category_id')) {
                $data['primary_button_link'] = '/shop?category=' . $request->link_category_id;
            } elseif ($request->banner_type === 'BRAND_PROMO' && $request->filled('link_brand_id')) {
                $data['primary_button_link'] = '/shop?brand=' . $request->link_brand_id;
            }

            if ($request->hasFile('desktop_image')) {
                if ($banner->desktop_image) Storage::disk('public')->delete($banner->desktop_image);
                $data['desktop_image'] = $request->file('desktop_image')->store('banners/desktop', 'public');
            }
            if ($request->hasFile('mobile_image')) {
                if ($banner->mobile_image) Storage::disk('public')->delete($banner->mobile_image);
                $data['mobile_image'] = $request->file('mobile_image')->store('banners/mobile', 'public');
            }

            $banner->update($data);
            Cache::forget('home_banners');
        });

        return redirect()->route('admin.banners.index')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Banner updated successfully.']);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        Cache::forget('home_banners');
        return back()->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Banner deleted.']);
    }
}
