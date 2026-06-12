<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'address',
        'contact_person',
        'phone',
    ];

    // Relasi ke riwayat pembelian/restok barang dari supplier ini
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }
}
