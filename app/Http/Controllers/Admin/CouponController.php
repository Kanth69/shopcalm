<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Enums\CouponApplicableType;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::with(['creator', 'targets']);

        if ($request->filled('search')) {
            $query->where('code', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active': $query->where('is_active', true); break;
                case 'inactive': $query->where('is_active', false); break;
                case 'expired': $query->where('valid_until', '<', now()); break;
                case 'upcoming': $query->where('valid_from', '>', now()); break;
            }
        }

        $coupons = $query->latest()->paginate(15)->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $categories = Category::where('status', 'Active')->get();
        $brands = Brand::where('status', 1)->get();
        $products = Product::where('status', 'Active')->get();
        return view('admin.coupons.create', compact('categories', 'brands', 'products'));
    }

    public function store(CouponRequest $request)
    {
        $data = $request->validated();
        $appType = $request->applicable_type;

        // Auto fallback applicable_id
        if ($appType === 'CATEGORY' && $request->filled('categories')) {
            $data['applicable_id'] = $request->categories[0] ?? null;
        } elseif ($appType === 'BRAND' && $request->filled('brands')) {
            $data['applicable_id'] = $request->brands[0] ?? null;
        } elseif ($appType === 'PRODUCT' && $request->filled('products')) {
            $data['applicable_id'] = $request->products[0] ?? null;
        }

        $coupon = Coupon::create(array_merge($data, [
            'created_by' => auth()->id()
        ]));

        $this->syncCouponTargets($coupon, $appType, $request);

        return redirect()->route('admin.coupons.index')->with('toast', ['type' => 'success', 'title' => 'Coupon Created', 'message' => 'Coupon added successfully.']);
    }

    public function edit(Coupon $coupon)
    {
        $coupon->load(['targets']);
        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $brands = Brand::where('status', 1)->orderBy('name')->get();
        $products = Product::where('status', 'Active')->orderBy('name')->get();
        return view('admin.coupons.edit', compact('coupon', 'categories', 'brands', 'products'));
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $data = $request->validated();
        $appType = $request->applicable_type;

        // Auto fallback applicable_id
        if ($appType === 'CATEGORY' && $request->filled('categories')) {
            $data['applicable_id'] = $request->categories[0] ?? null;
        } elseif ($appType === 'BRAND' && $request->filled('brands')) {
            $data['applicable_id'] = $request->brands[0] ?? null;
        } elseif ($appType === 'PRODUCT' && $request->filled('products')) {
            $data['applicable_id'] = $request->products[0] ?? null;
        }

        $coupon->update(array_merge($data, [
            'updated_by' => auth()->id()
        ]));

        $this->syncCouponTargets($coupon, $appType, $request);

        return redirect()->route('admin.coupons.index')->with('toast', ['type' => 'success', 'title' => 'Coupon Updated', 'message' => 'Coupon updated successfully.']);
    }

    private function syncCouponTargets(Coupon $coupon, string $appType, Request $request): void
    {
        $coupon->targets()->delete();

        if ($appType === 'CATEGORY') {
            $ids = $request->filled('categories') ? (array) $request->categories : ($request->applicable_id ? [$request->applicable_id] : []);
            foreach ($ids as $id) {
                if ($id) {
                    $coupon->targets()->create(['target_type' => 'category', 'target_id' => $id]);
                }
            }
        } elseif ($appType === 'BRAND') {
            $ids = $request->filled('brands') ? (array) $request->brands : ($request->applicable_id ? [$request->applicable_id] : []);
            foreach ($ids as $id) {
                if ($id) {
                    $coupon->targets()->create(['target_type' => 'brand', 'target_id' => $id]);
                }
            }
        } elseif ($appType === 'PRODUCT') {
            $ids = $request->filled('products') ? (array) $request->products : ($request->applicable_id ? [$request->applicable_id] : []);
            foreach ($ids as $id) {
                if ($id) {
                    $coupon->targets()->create(['target_type' => 'product', 'target_id' => $id]);
                }
            }
        }
    }

    public function destroy(Request $request, Coupon $coupon)
    {
        $coupon->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully.'
            ]);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Coupon Deleted', 'message' => 'Coupon moved to trash.']);
    }
}
