<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipRule extends Model
{
    protected $fillable = [
        'tier_silver_min',
        'tier_gold_min',
        'point_per_nominal',
        'redeem_point_value',
        'minimum_redeem_points',
        'max_redeem_percent',
        'discount_bronze',
        'discount_silver',
        'discount_gold',
        'updated_by',
    ];

    protected $casts = [
        'tier_silver_min' => 'decimal:2',
        'tier_gold_min'   => 'decimal:2',
        'redeem_point_value' => 'decimal:2',
        'minimum_redeem_points' => 'decimal:2',
        'max_redeem_percent' => 'decimal:2',
        'discount_bronze' => 'decimal:2',
        'discount_silver' => 'decimal:2',
        'discount_gold'   => 'decimal:2',
    ];

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getCurrent(): self
    {
        return static::latest()->firstOrCreate([], [
            'tier_silver_min'  => 5000000,
            'tier_gold_min'    => 15000000,
            'point_per_nominal' => 1000,
            'redeem_point_value' => 100,
            'minimum_redeem_points' => 100,
            'max_redeem_percent' => 50,
            'discount_bronze'  => 0,
            'discount_silver'  => 3,
            'discount_gold'    => 5,
        ]);
    }

    public function getDiscountForTier(string $tier): float
    {
        return (float) match ($tier) {
            'gold'   => $this->discount_gold,
            'silver' => $this->discount_silver,
            default  => $this->discount_bronze,
        };
    }
}
