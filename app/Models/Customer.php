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
    public function getTierBadgeColorAttribute(): string
    {
        return match ($this->tier) {
            'gold'   => 'warning',
            'silver' => 'secondary',
            default  => 'danger',
        };
    }

    public function getTierLabelAttribute(): string
    {
        return ucfirst($this->tier);
    }

    // ---- Relationships ----
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    public function pointHistory()
    {
        return $this->hasMany(PointHistory::class, 'customer_id');
    }

    public function tierHistories()
    {
        return $this->hasMany(CustomerTierHistory::class, 'customer_id')->latest();
    }
}

