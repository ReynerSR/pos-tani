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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

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
