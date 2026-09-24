<?php

namespace App\Http\Controllers\ProductManager;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display product performance and inventory analytics for Product Manager.
     */
    public function index(Request $request): View
    {
        // 1. Top Selling Products (Velocity)
        $topSelling = Product::select(
                'products.id',
                'products.name',
                'products.sku',
                'products.price',
                'products.stock',
                'products.status',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_units_sold'),
                DB::raw('COALESCE(SUM(order_items.total_price), 0) as total_revenue')
            )
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price', 'products.stock', 'products.status')
            ->orderBy('total_units_sold', 'desc')
            ->take(10)
            ->get();

        // 2. Category Inventory Valuation
        $categoryBreakdown = Category::select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(products.id) as product_count'),
                DB::raw('COALESCE(SUM(products.stock), 0) as total_stock'),
                DB::raw('COALESCE(SUM(products.price * products.stock), 0) as total_value')
            )
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_value', 'desc')
            ->get();

        // 3. Brand Inventory Valuation
        $brandBreakdown = Brand::select(
                'brands.id',
                'brands.name',
                DB::raw('COUNT(products.id) as product_count'),
                DB::raw('COALESCE(SUM(products.stock), 0) as total_stock'),
                DB::raw('COALESCE(SUM(products.price * products.stock), 0) as total_value')
            )
            ->leftJoin('products', 'brands.id', '=', 'products.brand_id')
            ->groupBy('brands.id', 'brands.name')
            ->orderBy('total_value', 'desc')
            ->take(10)
            ->get();

        // 4. Stock Risk Items (Low & Out of stock)
        $riskProducts = Product::with(['category', 'brand'])
            ->where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->take(15)
            ->get();

        $totalInventoryValue = Product::selectRaw('SUM(price * stock) as total')->value('total') ?? 0;
        $totalUnitsInStock = Product::sum('stock');

        return view('product-manager.reports.index', compact(
            'topSelling',
            'categoryBreakdown',
            'brandBreakdown',
            'riskProducts',
            'totalInventoryValue',
            'totalUnitsInStock'
        ));
    }
}
