<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Menjalankan seeder-seeder yang telah dibuat untuk mengisi data awal ke database
        $this->call([
            UserSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            MembershipRuleSeeder::class,
        ]);
    }
}
