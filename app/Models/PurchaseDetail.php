<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'qty',
        'unit_buy_price',
        'new_hpp',
        'subtotal',
    ];

    protected $casts = [
        'unit_buy_price' => 'decimal:2',
        'new_hpp'        => 'decimal:2',
        'subtotal'       => 'decimal:2',
    ];

    // Relasi ke faktur pembelian utama (induk transaksi)
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    // Relasi ke produk yang dibeli
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
