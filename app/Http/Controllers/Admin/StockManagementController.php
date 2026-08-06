<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MovementType;
use App\Enums\StockSource;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockManagementController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function dashboard(Request $request)
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

        // Filtering by stock status
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->where('stock', '<=', DB::raw('low_stock_alert'))->where('stock', '>', 0);
            } elseif ($request->stock_status === 'out') {
                $query->where('stock', '=', 0);
            }
        }

        $products = $query->latest('updated_at')->paginate(15)->withQueryString();

        // High level stats for the header
        $stats = [
            'total_products' => Product::count(),
            'total_stock' => Product::sum('stock'),
            'low_stock' => Product::where('stock', '<=', DB::raw('low_stock_alert'))->where('stock', '>', 0)->count(),
            'out_of_stock' => Product::where('stock', '=', 0)->count(),
            'total_value' => Product::sum(DB::raw('stock * selling_price')),
        ];

        return view('admin.stock.dashboard', compact('products', 'stats'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'stockMovements.createdBy']);
        $movements = $product->stockMovements()->latest()->paginate(10);

        return view('admin.stock.show', compact('product', 'movements'));
    }

    public function history(Request $request)
    {
        $query = StockMovement::with(['product', 'createdBy', 'reference']);

        if ($request->filled('search')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $movements = $query->latest()->paginate(15)->withQueryString();

        return view('admin.stock.history', compact('movements'));
    }

    public function addStockForm(Product $product)
    {
        if ($product->status !== 'Active') {
            return redirect()->route('admin.stock.dashboard')->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'Inactive products cannot receive stock.']);
        }

        $product->load(['category', 'brand']);
        return view('admin.stock.add', compact('product'));
    }

    public function addStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->stockService->addStock($product, $request->quantity, $request->notes, Auth::id());
            return redirect()->route('admin.stock.dashboard')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => "Successfully added {$request->quantity} stock to {$product->name}."]);
        } catch (\Exception $e) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        }
    }

    public function reduceStockForm(Product $product)
    {
        $product->load(['category', 'brand']);
        return view('admin.stock.reduce', compact('product'));
    }

    public function reduceStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->stock,
            'notes' => 'required|string|max:500',
        ]);

        try {
            $newStock = $product->stock - $request->quantity;
            $this->stockService->adjustStock($product, $newStock, $request->notes, Auth::id());
            return redirect()->route('admin.stock.dashboard')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => "Successfully reduced stock for {$product->name} by {$request->quantity}."]);
        } catch (\Exception $e) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        }
    }

    public function adjustStockForm(Product $product)
    {
        $product->load(['category', 'brand']);
        return view('admin.stock.adjust', compact('product'));
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'new_stock' => 'required|integer|min:0',
            'notes' => 'required|string|max:500',
        ]);

        try {
            $this->stockService->adjustStock($product, $request->new_stock, $request->notes, Auth::id());
            return redirect()->route('admin.stock.dashboard')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => "Stock adjusted successfully for {$product->name}."]);
        } catch (\Exception $e) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => $e->getMessage()]);
        }
    }
}
