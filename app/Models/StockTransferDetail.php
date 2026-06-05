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
     * The transfer this line belongs to.
     */
    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    /**
     * The product being moved.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}