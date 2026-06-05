<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'cashier_id',
        'customer_id',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'total_price',
        'cash_received',
        'change_amount',
        'points_earned',
        'points_redeemed',
        'point_redeem_amount',
        'payment_status',
        'notes',
        'transaction_date',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_price'      => 'decimal:2',
        'cash_received'    => 'decimal:2',
        'change_amount'    => 'decimal:2',
        'points_earned'    => 'decimal:2',
        'points_redeemed'  => 'decimal:2',
        'point_redeem_amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public static function generateTransactionNumber(): string
    {
        $prefix = 'TRX-' . date('Ymd') . '-';
        $last   = static::where('transaction_number', 'like', $prefix . '%')->latest('id')->first();
        $seq    = $last ? (int) substr($last->transaction_number, -4) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ---- Relationships ----
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }

    public function pointHistory()
    {
        return $this->hasOne(PointHistory::class, 'transaction_id');
    }
}
