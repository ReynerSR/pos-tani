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
    // Cek apakah pengguna adalah pemilik toko
    public function isPemilik(): bool
    {
        return $this->role === 'pemilik';
    }

    // Cek apakah pengguna adalah admin operasional
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Cek apakah pengguna adalah kasir
    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    // Cek hak akses untuk melihat/mengelola HPP (Harga Pokok Penjualan)
    public function canAccessHPP(): bool
    {
        return $this->role === 'pemilik';
    }

    // Cek hak akses untuk mengatur aturan membership (diskon, poin, dll)
    public function canManageRules(): bool
    {
        return $this->role === 'pemilik';
    }

    // Cek hak akses untuk mengelola data pengguna/karyawan
    public function canManageUsers(): bool
    {
        return $this->role === 'pemilik';
    }

    // Mendapatkan label peran/role pengguna untuk ditampilkan di antarmuka pengguna
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'pemilik' => 'Pemilik Toko',
            'admin'   => 'Admin Operasional',
            'kasir'   => 'Kasir',
            default   => ucfirst($this->role),
        };
    }


    // Mengecek apakah pengguna sedang online (aktif dalam 5 menit terakhir)
    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen_at !== null && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    // Mendapatkan teks status online/offline
    public function getOnlineStatusLabelAttribute(): string
    {
        return $this->is_online ? 'Online' : 'Offline';
    }

    // ---- Relationships ----
    // Relasi ke riwayat transaksi kasir yang dilakukan oleh pengguna ini
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    // Relasi ke riwayat pembelian barang (restok) yang diinput oleh pengguna ini
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'user_id');
    }

    // Relasi ke riwayat penyesuaian stok (stock opname) yang dilakukan pengguna ini
    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'user_id');
    }

    // Relasi ke catatan log aktivitas (Activity Log) dari pengguna ini
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}
