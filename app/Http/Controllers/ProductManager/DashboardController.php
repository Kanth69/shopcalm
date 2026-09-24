<?php

namespace App\Http\Controllers\ProductManager;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Product Manager catalog & inventory dashboard.
     */
    public function index(): View
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'Active')->count();
        $pendingProducts = Product::where('status', 'Pending_Approval')->count();
        $rejectedProducts = Product::where('status', 'Rejected')->count();
        $inactiveProducts = Product::where('status', 'Inactive')->count();

        $lowStockCount = Product::where('stock', '>', 0)->where('stock', '<=', 10)->count();
        $outOfStockCount = Product::where('stock', '<=', 0)->count();

        $totalCategories = Category::count();
        $totalBrands = Brand::count();

        $pendingReviewsCount = ProductReview::where('status', 'Pending')->count();

        $recentProducts = Product::with(['category', 'brand'])
            ->latest()
            ->take(8)
            ->get();

        $recentRejected = Product::with(['category', 'brand'])
            ->where('status', 'Rejected')
            ->latest()
            ->take(5)
            ->get();

        return view('product-manager.dashboard', compact(
            'totalProducts',
            'activeProducts',
            'pendingProducts',
            'rejectedProducts',
            'inactiveProducts',
            'lowStockCount',
            'outOfStockCount',
            'totalCategories',
            'totalBrands',
            'pendingReviewsCount',
            'recentProducts',
            'recentRejected'
        ));
    }
}
