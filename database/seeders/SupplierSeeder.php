<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'PT Petrokimia Gresik', 'address' => 'Jl. Jenderal Ahmad Yani, Gresik', 'contact_person' => 'Budi Santoso', 'phone' => '081234567001'],
            ['name' => 'CV Agro Mandiri', 'address' => 'Jl. Raya Ngawi No. 45, Ngawi', 'contact_person' => 'Siti Rahayu', 'phone' => '081234567002'],
            ['name' => 'UD Makmur Tani', 'address' => 'Jl. Pahlawan No. 12, Madiun', 'contact_person' => 'Hendra Wijaya', 'phone' => '081234567003'],
            ['name' => 'PT Bayer Indonesia', 'address' => 'Gedung Bayer Center, Jakarta', 'contact_person' => 'Dewi Lestari', 'phone' => '081234567004'],
            ['name' => 'CV Subur Makmur', 'address' => 'Jl. Diponegoro No. 88, Ngawi', 'contact_person' => 'Agus Prasetyo', 'phone' => '081234567005'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create(array_merge($supplier, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
