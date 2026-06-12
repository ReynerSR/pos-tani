<?php

namespace Database\Seeders;

use App\Models\MembershipRule;
use Illuminate\Database\Seeder;

class MembershipRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat aturan default/awal untuk sistem poin, diskon, dan tier membership pelanggan
        MembershipRule::create([
            'tier_silver_min'   => 5000000,
            'tier_gold_min'     => 15000000,
            'point_per_nominal' => 1000,
            'redeem_point_value' => 100,
            'minimum_redeem_points' => 100,
            'max_redeem_percent' => 50,
            'discount_bronze'   => 0,
            'discount_silver'   => 3,
            'discount_gold'     => 5,
            'updated_by'        => 1,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
