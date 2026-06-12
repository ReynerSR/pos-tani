<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'detail',
        'ip_address',
    ];

    // Relasi ke model User (pengguna yang melakukan aktivitas)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Fungsi statis bantuan untuk mencatat log aktivitas baru ke database secara otomatis
    public static function record(string $action, string $detail = ''): void
    {
        static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'detail'     => $detail,
            'ip_address' => request()->ip(),
        ]);
    }
}
