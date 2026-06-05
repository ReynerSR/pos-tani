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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
