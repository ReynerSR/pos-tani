<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Line item for a stock transfer.
 */
class StockTransferDetail extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'qty',
    ];

    /**
     * Relasi ke data induk riwayat transfer stok.
     */
    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    /**
     * Relasi ke produk yang dipindahkan.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}