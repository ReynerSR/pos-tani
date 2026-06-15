<?php

namespace App\Services;

use App\Models\Product;

class HppCalculatorService
{
    /**
     * Menghitung nilai rata-rata tertimbang HPP baru setelah ada transaksi pembelian stok (restock).
     *
     * Rumus (Rata-rata Tertimbang):
     * HPP_Baru = ((Stok_Saat_Ini * HPP_Saat_Ini) + (Qty_Beli * Harga_Beli)) / (Stok_Saat_Ini + Qty_Beli)
     */
    public function calculateWeightedAverage(Product $product, int $qtyBought, float $buyPrice): float
    {
        // HPP harus dihitung berdasarkan total stok yang ada di seluruh
        // gudang. Field `stock` bawaan hanya mencerminkan stok di
        // gudang toko utama. Gunakan accessor `total_stock` untuk
        // memperhitungkan semua kuantitas barang saat menghitung rata-rata tertimbang.
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
