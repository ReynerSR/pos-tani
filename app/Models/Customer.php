<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'full_name',
        'whatsapp_number',
        'address',
        'tier',
        'total_accumulation',
        'point_balance',
        'registered_at',
    ];

    protected $casts = [
        'total_accumulation' => 'decimal:2',
        'point_balance'      => 'decimal:2',
        'registered_at'      => 'date',
    ];

    // ---- Accessors ----
    // Mengambil warna badge Bootstrap berdasarkan tier pelanggan (misalnya: 'warning' untuk gold)
    public function getTierBadgeColorAttribute(): string
    {
        return match ($this->tier) {
            'gold'   => 'warning',
            'silver' => 'secondary',
            default  => 'danger',
        };
    }

    // Mengambil label tier dengan format huruf kapital di awal (contoh: 'Bronze', 'Silver', 'Gold')
    public function getTierLabelAttribute(): string
    {
        return ucfirst($this->tier);
    }

    // ---- Relationships ----
    // Relasi ke semua transaksi yang dilakukan oleh pelanggan ini
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    // Relasi ke riwayat penambahan atau pengurangan (redeem) poin pelanggan
    public function pointHistory()
    {
        return $this->hasMany(PointHistory::class, 'customer_id');
    }

    // Relasi ke riwayat perubahan tier (naik/turun level) pelanggan, diurutkan dari yang terbaru
    public function tierHistories()
    {
        return $this->hasMany(CustomerTierHistory::class, 'customer_id')->latest();
    }
}

