<?php

namespace App\Services;

use App\Models\Product;

class HppCalculatorService
{
    /**
     * Calculate new weighted average HPP after a purchase restocking.
     *
     * Formula (Weighted Average):
     * new_hpp = ((current_stock * current_hpp) + (qty_bought * buy_price)) / (current_stock + qty_bought)
     */
    public function calculateWeightedAverage(Product $product, int $qtyBought, float $buyPrice): float
    {
        // HPP should be calculated based on total stock across all
        // warehouses.  The legacy `stock` field reflects only the
        // primary store warehouse.  Use the accessor `total_stock` to
        // consider all quantities when computing weighted average.
        $currentStock = max(0, $product->total_stock);
        $currentHpp   = (float) $product->hpp;

        $totalStockValue    = ($currentStock * $currentHpp) + ($qtyBought * $buyPrice);
        $totalStockQuantity = $currentStock + $qtyBought;

        if ($totalStockQuantity === 0) {
            return $buyPrice;
        }

        return round($totalStockValue / $totalStockQuantity, 2);
    }
}
