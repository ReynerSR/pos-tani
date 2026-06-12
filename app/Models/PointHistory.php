<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointHistory extends Model
{
    protected $table = 'point_history';

    protected $fillable = [
        'customer_id',
        'transaction_id',
        'points_earned',
        'description',
    ];

    protected $casts = [
        'points_earned' => 'decimal:2',
    ];

    // Relasi ke pelanggan yang memiliki riwayat poin ini
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Relasi ke transaksi yang menjadi sumber penambahan/pengurangan poin
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
