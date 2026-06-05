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
     * Get the warehouse this stock belongs to.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the product for this stock entry.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}