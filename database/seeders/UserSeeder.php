<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Menambahkan data pengguna awal (pemilik, admin, dan kasir) beserta password hash-nya
        User::insert([
            [
                'name'       => 'Sianny Soesanto',
                'username'   => 'pemilik',
                'email'      => 'pemilik@taniagung.com',
                'password'   => Hash::make('pemilik123'),
                'role'       => 'pemilik',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Admin Operasional',
                'username'   => 'admin',
                'email'      => 'admin@taniagung.com',
                'password'   => Hash::make('admin123'),
                'role'       => 'admin',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Kasir 1',
                'username'   => 'kasir1',
                'email'      => 'kasir1@taniagung.com',
                'password'   => Hash::make('kasir123'),
                'role'       => 'kasir',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
