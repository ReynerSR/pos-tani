<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'product_name',
        'category',
        'unit',
        'selling_price',
        'hpp',
        // `stock` represents the on–hand quantity available in the
        // primary selling location (the “Toko” warehouse).  Total
        // quantities across other warehouses are stored in the
        // `warehouse_stocks` table.  See the `warehouseStocks` and
        // `storeStock` accessors below.
        'stock',
        'minimum_stock',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'selling_price' => 'decimal:2',
        'hpp'           => 'decimal:2',
    ];

    // ---- Accessors ----
    // Mengambil status stok saat ini (empty/low/ok) berdasarkan jumlah dan batas minimum
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'empty';
        }
        if ($this->stock <= $this->minimum_stock) {
            return 'low';
        }
        return 'ok';
    }

    // Mengambil label status stok dalam bahasa Indonesia (Habis/Kritis/Aman)
    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'empty' => 'Habis',
            'low'   => 'Kritis',
            default => 'Aman',
        };
    }

    // ---- Relationships ----

    // Relasi ke detail transaksi penjualan produk ini
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'product_id');
    }

    // Relasi ke detail pembelian (restok) produk ini dari supplier
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class, 'product_id');
    }

    // Mengambil detail pembelian terakhir untuk mengetahui supplier terakhir
    public function latestPurchaseDetail()
    {
        return $this->hasOne(PurchaseDetail::class, 'product_id')->latestOfMany();
    }

    // Relasi ke riwayat penyesuaian stok (stock opname) produk ini
    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'product_id');
    }

    /**
     * Warehouse stock records for this product.
     *
     * A product can be stored in multiple warehouses.  The
     * `warehouse_stocks` table tracks the quantity of each product
     * in each location.  Use `$product->warehouseStocks` to access
     * per‑warehouse quantities.
     */
    // Relasi ke data stok produk di setiap lokasi gudang
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'product_id');
    }

    /**
     * Accessor: total stock across all warehouses.
     *
     * This sums the `stock` column from related `warehouse_stocks`
     * records.  It does **not** include the `stock` attribute on
     * this model (which reflects only the Toko warehouse) because
     * that value is duplicated in the corresponding warehouse stock
     * row.
     */
    // Accessor: Mengambil total stok gabungan dari seluruh gudang
    public function getTotalStockAttribute(): int
    {
        // Use loaded relationship if available to avoid extra queries
        if ($this->relationLoaded('warehouseStocks')) {
            return $this->warehouseStocks->sum('stock');
        }
        return (int) $this->warehouseStocks()->sum('stock');
    }

    /**
     * Accessor: stock in the primary selling warehouse (“Toko”).
     *
     * If a warehouse flagged with `is_store=true` exists, return the
     * quantity recorded in `warehouse_stocks`.  Otherwise return the
     * legacy `stock` column.  This allows older code to continue
     * working while the new warehouse architecture is phased in.
     */
    // Accessor: Mengambil jumlah stok khusus yang berada di gudang utama (toko)
    public function getStoreStockAttribute(): int
    {
        // Attempt to find a store warehouse stock
        $storeStock = $this->warehouseStocks()
            ->whereHas('warehouse', function ($q) {
                $q->where('is_store', true);
            })->first();

        return $storeStock ? (int) $storeStock->stock : (int) $this->stock;
    }
}
