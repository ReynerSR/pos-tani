<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',

        'role',
        'is_active',
        'is_main_owner',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',

            'is_active' => 'boolean',
            'is_main_owner' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    // ---- Role Helpers ----
    public function isPemilik(): bool
    {
        return $this->role === 'pemilik';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function canAccessHPP(): bool
    {
        return $this->role === 'pemilik';
    }

    public function canManageRules(): bool
    {
        return $this->role === 'pemilik';
    }

    public function canManageUsers(): bool
    {
        return $this->role === 'pemilik';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'pemilik' => 'Pemilik Toko',
            'admin'   => 'Admin Operasional',
            'kasir'   => 'Kasir',
            default   => ucfirst($this->role),
        };
    }


    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen_at !== null && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    public function getOnlineStatusLabelAttribute(): string
    {
        return $this->is_online ? 'Online' : 'Offline';
    }

    // ---- Relationships ----
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'user_id');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}
