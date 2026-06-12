<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'user_id',
        // When restocking goods the admin selects which warehouse
        // receives the purchased items.  This column may be null
        // temporarily until warehouses are configured.
        'warehouse_id',
        'purchase_date',
        'total_price',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_price'   => 'decimal:2',
        'approved_at'   => 'datetime',
    ];

    // Relasi ke supplier tempat barang dibeli
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // Relasi ke pengguna (admin/kasir) yang menginputkan draf/data pembelian
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke pengguna (pemilik) yang menyetujui transaksi pembelian ini
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relasi ke daftar detail produk-produk yang dibeli dalam transaksi ini
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_id');
    }

    /**
     * Relasi ke gudang tujuan tempat barang yang dibeli disimpan.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
