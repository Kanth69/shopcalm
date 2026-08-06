<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Enums\StockSource;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    /**
     * Add purchased stock to a product.
     *
     * @param Product $product
     * @param int $quantity
     * @param string|null $notes
     * @param int|null $adminId
     * @return Product
     * @throws Exception
     */
    public function addStock(Product $product, int $quantity, ?string $notes = null, ?int $adminId = null): Product
    {
        if ($quantity <= 0) {
            throw new Exception("Quantity to add must be greater than zero.");
        }

        return DB::transaction(function () use ($product, $quantity, $notes, $adminId) {
            // Lock the row to prevent race conditions
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->first();

            $stockBefore = $lockedProduct->stock;
            $stockAfter = $stockBefore + $quantity;

            // 1. Log the movement
            $lockedProduct->stockMovements()->create([
                'movement_type' => MovementType::PURCHASE,
                'source' => StockSource::PURCHASE,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
                'created_by' => $adminId,
            ]);

            // 2. Update the actual product stock
            $lockedProduct->stock = $stockAfter;
            $lockedProduct->save();

            return $lockedProduct;
        });
    }

    /**
     * Manually adjust stock (override current value).
     *
     * @param Product $product
     * @param int $newStock
     * @param string $notes Reason is mandatory for adjustment
     * @param int|null $adminId
     * @return Product
     * @throws Exception
     */
    public function adjustStock(Product $product, int $newStock, string $notes, ?int $adminId = null): Product
    {
        if ($newStock < 0) {
            throw new Exception("Stock cannot be negative.");
        }

        return DB::transaction(function () use ($product, $newStock, $notes, $adminId) {
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->first();

            $stockBefore = $lockedProduct->stock;

            if ($stockBefore === $newStock) {
                throw new Exception("New stock value is the same as current stock. No adjustment needed.");
            }

            $quantityDifference = abs($newStock - $stockBefore);

            // Log the movement
            $lockedProduct->stockMovements()->create([
                'movement_type' => MovementType::ADJUSTMENT,
                'source' => StockSource::MANUAL,
                'quantity' => $quantityDifference, // Store absolute difference
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'notes' => $notes,
                'created_by' => $adminId,
            ]);

            // Update the actual product stock
            $lockedProduct->stock = $newStock;
            $lockedProduct->save();

            return $lockedProduct;
        });
    }
}
