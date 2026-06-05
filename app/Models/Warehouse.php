<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a physical storage location.
 *
 * Warehouses can be simple storage rooms or the main selling
 * location (Toko).  A warehouse flagged with `is_store=true` is
 * treated as the primary stock source for cashier transactions.
 */
class Warehouse extends Model
{
    protected $fillable = [
        'code',
        'name',
        'location',
        'is_store',
        'is_active',
    ];

    protected $casts = [
        'is_store'  => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all stock records associated with this warehouse.
     */
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'warehouse_id');
    }

    /**
     * Transfers initiated from this warehouse.
     */
    public function transfersFrom()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    /**
     * Transfers received by this warehouse.
     */
    public function transfersTo()
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }
}