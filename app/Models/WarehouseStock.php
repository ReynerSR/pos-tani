<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per‑warehouse inventory quantities.
 *
 * Each row records the quantity of a particular product in a
 * specific warehouse, along with an optional minimum stock level
 * threshold for that location.
 */
class WarehouseStock extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'stock',
        'minimum_stock',
    ];

    /**
     * Relasi ke gudang tempat stok ini berada.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Relasi ke produk yang disimpan pada stok gudang ini.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}