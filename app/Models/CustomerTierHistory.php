<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerTierHistory extends Model
{
    protected $fillable = [
        'customer_id',
        'transaction_id',
        'changed_by',
        'old_tier',
        'new_tier',
        'old_total_accumulation',
        'new_total_accumulation',
        'source',
        'notes',
    ];

    protected $casts = [
        'old_total_accumulation' => 'decimal:2',
        'new_total_accumulation' => 'decimal:2',
    ];

    // Relasi ke pelanggan yang mengalami perubahan tier
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi ke transaksi yang menyebabkan perubahan tier ini (jika ada)
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke pengguna (kasir/admin/sistem) yang memproses perubahan tier
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
