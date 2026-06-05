<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['full_name' => 'Pak Slamet Riyadi', 'whatsapp_number' => '081111100001', 'address' => 'Desa Margomulyo, Ngawi', 'tier' => 'gold',   'total_accumulation' => 18500000, 'point_balance' => 18],
            ['full_name' => 'Bu Kartini Dewi',   'whatsapp_number' => '081111100002', 'address' => 'Jl. Soekarno No.5, Ngawi', 'tier' => 'silver', 'total_accumulation' => 8200000,  'point_balance' => 8],
            ['full_name' => 'Mas Hendra Susilo',  'whatsapp_number' => '081111100003', 'address' => 'Desa Pitu, Ngawi',        'tier' => 'silver', 'total_accumulation' => 6000000,  'point_balance' => 6],
            ['full_name' => 'Pak Bambang Irawan', 'whatsapp_number' => '081111100004', 'address' => 'Desa Kedunggalar, Ngawi', 'tier' => 'bronze', 'total_accumulation' => 1500000,  'point_balance' => 1],
            ['full_name' => 'Bu Sari Wulandari',  'whatsapp_number' => '081111100005', 'address' => 'Jl. Pahlawan No.12, Ngawi','tier' => 'bronze','total_accumulation' => 750000,   'point_balance' => 0],
            ['full_name' => 'Pak Agus Triyono',   'whatsapp_number' => '081111100006', 'address' => 'Desa Bringin, Ngawi',    'tier' => 'bronze', 'total_accumulation' => 200000,   'point_balance' => 0],
        ];

        foreach ($customers as $c) {
            Customer::create(array_merge($c, [
                'registered_at' => now()->subMonths(rand(1, 12))->toDateString(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]));
        }
    }
}
