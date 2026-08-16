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
        $query = Coupon::with('creator');

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
        $coupon = Coupon::create(array_merge($request->validated(), [
            'created_by' => auth()->id()
        ]));

        if ($request->filled('categories')) {
            $coupon->categories()->sync((array) $request->categories);
        }
        if ($request->filled('brands')) {
            $coupon->brands()->sync((array) $request->brands);
        }
        if ($request->filled('products')) {
            $coupon->products()->sync((array) $request->products);
        }

        return redirect()->route('admin.coupons.index')->with('toast', ['type' => 'success', 'title' => 'Coupon Created', 'message' => 'Coupon added successfully.']);
    }

    public function edit(Coupon $coupon)
    {
        $categories = Category::where('status', 'Active')->get();
        $brands = Brand::where('status', 1)->get();
        $products = Product::where('status', 'Active')->get();
        return view('admin.coupons.edit', compact('coupon', 'categories', 'brands', 'products'));
    }

    public function update(CouponRequest $request, Coupon $coupon)
    {
        $coupon->update(array_merge($request->validated(), [
            'updated_by' => auth()->id()
        ]));

        if ($request->has('categories')) {
            $coupon->categories()->sync((array) $request->categories);
        }
        if ($request->has('brands')) {
            $coupon->brands()->sync((array) $request->brands);
        }
        if ($request->has('products')) {
            $coupon->products()->sync((array) $request->products);
        }

        return redirect()->route('admin.coupons.index')->with('toast', ['type' => 'success', 'title' => 'Coupon Updated', 'message' => 'Coupon updated successfully.']);
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
