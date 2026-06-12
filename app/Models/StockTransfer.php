<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a movement of stock between two warehouses.
 *
 * Stock transfers allow items to be moved from one warehouse to
 * another.  They do not change the product’s HPP because no
 * purchase occurs — inventory merely changes location.
 */
class StockTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'user_id',
        'status',
        'notes',
        'transfer_date',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    /**
     * Relasi ke gudang asal (tempat stok dikurangi).
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Relasi ke gudang tujuan (tempat stok ditambahkan).
     */
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Relasi ke pengguna yang menginisiasi transfer stok.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke detail produk-produk yang dipindahkan.
     */
    public function details()
    {
        return $this->hasMany(StockTransferDetail::class, 'stock_transfer_id');
    }
}