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
     * Relasi ke data stok barang spesifik untuk setiap gudang.
     */
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class, 'warehouse_id');
    }

    /**
     * Relasi ke riwayat transfer stok yang keluar (berasal) dari gudang ini.
     */
    public function transfersFrom()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    /**
     * Relasi ke riwayat transfer stok yang masuk (diterima) ke gudang ini.
     */
    public function transfersTo()
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }
}