<?php

namespace App\Http\Controllers\ProductManager;

use App\Enums\MovementType;
use App\Enums\StockSource;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Display inventory & stock overview for Product Manager.
     */
    public function dashboard(Request $request): View
    {
        $query = Product::with(['category', 'brand']);

        if ($request->filled('filter')) {
            if ($request->filter === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($request->filter === 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<=', 10);
            } elseif ($request->filter === 'in_stock') {
                $query->where('stock', '>', 10);
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('stock', 'asc')->paginate(15)->withQueryString();

        $totalItems = Product::count();
        $totalUnits = Product::sum('stock');
        $totalValuation = Product::selectRaw('SUM(price * stock) as total_val')->value('total_val') ?? 0;
        $lowStockCount = Product::where('stock', '>', 0)->where('stock', '<=', 10)->count();
        $outOfStockCount = Product::where('stock', '<=', 0)->count();

        $categories = Category::orderBy('name')->get();

        return view('product-manager.stock.dashboard', compact(
            'products',
            'totalItems',
            'totalUnits',
            'totalValuation',
            'lowStockCount',
            'outOfStockCount',
            'categories'
        ));
    }

    /**
     * Show stock addition/adjustment form.
     */
    public function stockForm(Product $product, string $action): View
    {
        return view('product-manager.stock.form', compact('product', 'action'));
    }

    /**
     * Add stock quantity to a product.
     */
    public function addStock(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'notes'    => ['nullable', 'string', 'max:255'],
        ]);

        $quantity = (int) $request->quantity;
        $stockBefore = $product->stock;
        $stockAfter = $stockBefore + $quantity;

        DB::transaction(function () use ($product, $quantity, $stockBefore, $stockAfter, $request) {
            $product->update(['stock' => $stockAfter]);

            StockMovement::create([
                'product_id'    => $product->id,
                'movement_type' => MovementType::PURCHASE,
                'source'        => StockSource::PURCHASE,
                'quantity'      => $quantity,
                'stock_before'  => $stockBefore,
                'stock_after'   => $stockAfter,
                'notes'         => $request->notes ?? 'Stock added via Product Manager Portal',
                'created_by'    => Auth::guard('product_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id(),
            ]);
        });

        return redirect()->route('product-manager.stock.dashboard')->with('toast', [
            'type' => 'success',
            'title' => 'Stock Added',
            'message' => "Added {$quantity} units to '{$product->name}'. New stock: {$stockAfter}.",
        ]);
    }

    /**
     * Reduce stock quantity from a product.
     */
    public function reduceStock(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . max(1, $product->stock)],
            'notes'    => ['required', 'string', 'max:255'],
        ]);

        $quantity = (int) $request->quantity;
        $stockBefore = $product->stock;
        $stockAfter = max(0, $stockBefore - $quantity);

        DB::transaction(function () use ($product, $quantity, $stockBefore, $stockAfter, $request) {
            $product->update(['stock' => $stockAfter]);

            StockMovement::create([
                'product_id'    => $product->id,
                'movement_type' => MovementType::ADJUSTMENT,
                'source'        => StockSource::MANUAL,
                'quantity'      => -$quantity,
                'stock_before'  => $stockBefore,
                'stock_after'   => $stockAfter,
                'notes'         => $request->notes,
                'created_by'    => Auth::guard('product_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id(),
            ]);
        });

        return redirect()->route('product-manager.stock.dashboard')->with('toast', [
            'type' => 'success',
            'title' => 'Stock Reduced',
            'message' => "Removed {$quantity} units from '{$product->name}'. New stock: {$stockAfter}.",
        ]);
    }

    /**
     * Adjust stock directly to a target quantity.
     */
    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'target_stock' => ['required', 'integer', 'min:0'],
            'notes'        => ['required', 'string', 'max:255'],
        ]);

        $stockBefore = $product->stock;
        $targetStock = (int) $request->target_stock;
        $diff = $targetStock - $stockBefore;

        if ($diff === 0) {
            return back()->with('toast', [
                'type' => 'info',
                'title' => 'No Change',
                'message' => 'Target stock matches current inventory quantity.',
            ]);
        }

        DB::transaction(function () use ($product, $diff, $stockBefore, $targetStock, $request) {
            $product->update(['stock' => $targetStock]);

            StockMovement::create([
                'product_id'    => $product->id,
                'movement_type' => MovementType::ADJUSTMENT,
                'source'        => StockSource::MANUAL,
                'quantity'      => $diff,
                'stock_before'  => $stockBefore,
                'stock_after'   => $targetStock,
                'notes'         => $request->notes,
                'created_by'    => Auth::guard('product_manager')->id() ?? Auth::guard('admin')->id() ?? Auth::id(),
            ]);
        });

        return redirect()->route('product-manager.stock.dashboard')->with('toast', [
            'type' => 'success',
            'title' => 'Stock Adjusted',
            'message' => "Stock for '{$product->name}' adjusted to {$targetStock}.",
        ]);
    }

    /**
     * Display stock movement audit history.
     */
    public function history(Request $request): View
    {
        $query = StockMovement::with(['product', 'createdBy'])->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $movements = $query->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('product-manager.stock.history', compact('movements', 'products'));
    }
}
