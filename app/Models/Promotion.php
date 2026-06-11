<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    protected $fillable = [
        'promo_name',
        'product_id',
        'discount_amount',
        'eligible_tiers',
        'start_date',
        'end_date',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'eligible_tiers' => 'array',
        'start_date'      => 'date',
        'end_date'        => 'date',
        'is_active'       => 'boolean',
    ];

    // ---- Relationships ----
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ---- Scopes ----

    /**
     * Scope: promo yang sedang aktif hari ini.
     */
    public function scopeActiveToday($query)
    {
        $today = Carbon::today()->toDateString();

        return $query->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    // ---- Helpers ----

    /**
     * Cek apakah promo ini sedang berlaku hari ini.
     */
    public function isActiveToday(): bool
    {
        $today = Carbon::today();

        return $this->is_active
            && $this->start_date->lte($today)
            && $this->end_date->gte($today);
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'Nonaktif';
        }

        $today = Carbon::today();

        if ($this->start_date->gt($today)) {
            return 'Belum Mulai';
        }

        if ($this->end_date->lt($today)) {
            return 'Kedaluwarsa';
        }

        return 'Aktif';
    }

    public function getStatusColorAttribute(): array
    {
        return match ($this->status_label) {
            'Aktif'          => ['#d1fae5', '#065f46'],
            'Belum Mulai'    => ['#dbeafe', '#1e40af'],
            'Kedaluwarsa'    => ['#fee2e2', '#991b1b'],
            default          => ['#f3f4f6', '#374151'],
        };
    }

    /**
     * Ambil promo aktif untuk satu produk (hari ini).
     * Return null jika tidak ada promo.
     */
    public static function getActiveForProduct(int $productId): ?self
    {
        return static::activeToday()
            ->where('product_id', $productId)
            ->first();
    }
}
