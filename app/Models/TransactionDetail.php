<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'qty',
        'unit_price',
        'final_unit_price',
        'subtotal',
    ];

    protected $casts = [
        'unit_price'       => 'decimal:2',
        'final_unit_price' => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    // Relasi ke transaksi utama (faktur)
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    // Relasi ke produk yang terjual
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
