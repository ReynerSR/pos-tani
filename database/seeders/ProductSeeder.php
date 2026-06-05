<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Pupuk
            ['code' => 'PRD-001', 'name' => 'Pupuk Urea 50kg', 'category' => 'Pupuk', 'unit' => 'sak', 'supplier' => 'PT Petrokimia Gresik', 'sell' => 285000, 'hpp' => 240000, 'stock' => 50, 'min' => 10],
            ['code' => 'PRD-002', 'name' => 'Pupuk SP36 50kg', 'category' => 'Pupuk', 'unit' => 'sak', 'supplier' => 'PT Petrokimia Gresik', 'sell' => 320000, 'hpp' => 270000, 'stock' => 40, 'min' => 10],
            ['code' => 'PRD-003', 'name' => 'Pupuk KCL 50kg', 'category' => 'Pupuk', 'unit' => 'sak', 'supplier' => 'PT Petrokimia Gresik', 'sell' => 390000, 'hpp' => 340000, 'stock' => 35, 'min' => 8],
            ['code' => 'PRD-004', 'name' => 'Pupuk NPK Phonska 50kg', 'category' => 'Pupuk', 'unit' => 'sak', 'supplier' => 'PT Petrokimia Gresik', 'sell' => 310000, 'hpp' => 265000, 'stock' => 60, 'min' => 12],
            ['code' => 'PRD-005', 'name' => 'Pupuk ZA 50kg', 'category' => 'Pupuk', 'unit' => 'sak', 'supplier' => 'PT Petrokimia Gresik', 'sell' => 175000, 'hpp' => 145000, 'stock' => 45, 'min' => 10],
            ['code' => 'PRD-006', 'name' => 'Pupuk Organik Cair 1L', 'category' => 'Pupuk', 'unit' => 'botol', 'supplier' => 'CV Agro Mandiri', 'sell' => 55000, 'hpp' => 38000, 'stock' => 30, 'min' => 5],

            // Pestisida
            ['code' => 'PRD-007', 'name' => 'Pestisida Dursban 400ml', 'category' => 'Pestisida', 'unit' => 'botol', 'supplier' => 'PT Bayer Indonesia', 'sell' => 85000, 'hpp' => 62000, 'stock' => 25, 'min' => 5],
            ['code' => 'PRD-008', 'name' => 'Herbisida Roundup 1L', 'category' => 'Pestisida', 'unit' => 'botol', 'supplier' => 'PT Bayer Indonesia', 'sell' => 120000, 'hpp' => 90000, 'stock' => 20, 'min' => 5],
            ['code' => 'PRD-009', 'name' => 'Fungisida Antracol 500gr', 'category' => 'Pestisida', 'unit' => 'pcs', 'supplier' => 'PT Bayer Indonesia', 'sell' => 65000, 'hpp' => 48000, 'stock' => 18, 'min' => 5],
            ['code' => 'PRD-010', 'name' => 'Insektisida Curacron 500ml', 'category' => 'Pestisida', 'unit' => 'botol', 'supplier' => 'PT Bayer Indonesia', 'sell' => 95000, 'hpp' => 70000, 'stock' => 15, 'min' => 3],

            // Benih
            ['code' => 'PRD-011', 'name' => 'Benih Padi IR64 5kg', 'category' => 'Benih', 'unit' => 'pcs', 'supplier' => 'CV Agro Mandiri', 'sell' => 75000, 'hpp' => 55000, 'stock' => 30, 'min' => 5],
            ['code' => 'PRD-012', 'name' => 'Benih Jagung Hibrida 1kg', 'category' => 'Benih', 'unit' => 'pcs', 'supplier' => 'CV Agro Mandiri', 'sell' => 85000, 'hpp' => 62000, 'stock' => 20, 'min' => 5],
            ['code' => 'PRD-013', 'name' => 'Benih Cabai Rawit 10gr', 'category' => 'Benih', 'unit' => 'pcs', 'supplier' => 'CV Agro Mandiri', 'sell' => 45000, 'hpp' => 32000, 'stock' => 15, 'min' => 3],

            // Alat Pertanian
            ['code' => 'PRD-014', 'name' => 'Sprayer Manual 16L', 'category' => 'Alat', 'unit' => 'pcs', 'supplier' => 'UD Makmur Tani', 'sell' => 185000, 'hpp' => 140000, 'stock' => 8, 'min' => 2],
            ['code' => 'PRD-015', 'name' => 'Cangkul Besi', 'category' => 'Alat', 'unit' => 'pcs', 'supplier' => 'UD Makmur Tani', 'sell' => 95000, 'hpp' => 70000, 'stock' => 12, 'min' => 3],
            ['code' => 'PRD-016', 'name' => 'Plastik Mulsa 200m', 'category' => 'Alat', 'unit' => 'pcs', 'supplier' => 'CV Subur Makmur', 'sell' => 250000, 'hpp' => 195000, 'stock' => 5, 'min' => 2],

            // Stok Kritis (untuk demo)
            ['code' => 'PRD-017', 'name' => 'Pupuk Daun Grow More', 'category' => 'Pupuk', 'unit' => 'pcs', 'supplier' => 'CV Agro Mandiri', 'sell' => 72000, 'hpp' => 52000, 'stock' => 3, 'min' => 5],
            ['code' => 'PRD-018', 'name' => 'Kapur Dolomit 50kg', 'category' => 'Pupuk', 'unit' => 'sak', 'supplier' => 'CV Subur Makmur', 'sell' => 45000, 'hpp' => 30000, 'stock' => 2, 'min' => 5],
        ];

        foreach ($products as $p) {
            Product::create([
                'product_code'  => $p['code'],
                'product_name'  => $p['name'],
                'category'      => $p['category'],
                'unit'          => $p['unit'],
                'selling_price' => $p['sell'],
                'hpp'           => $p['hpp'],
                'stock'         => $p['stock'],
                'minimum_stock' => $p['min'],
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
