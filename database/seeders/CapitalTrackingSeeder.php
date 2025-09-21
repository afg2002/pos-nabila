<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\CapitalTracking;

class CapitalTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $capitalTrackings = [
            [
                'name' => 'Modal Utama Toko',
                'description' => 'Modal utama untuk operasional toko sehari-hari',
                'initial_amount' => 50000000,
                'current_amount' => 50000000,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Modal Investasi',
                'description' => 'Modal khusus untuk investasi jangka panjang',
                'initial_amount' => 25000000,
                'current_amount' => 25000000,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Modal Darurat',
                'description' => 'Dana cadangan untuk keperluan mendesak',
                'initial_amount' => 10000000,
                'current_amount' => 10000000,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Modal Ekspansi',
                'description' => 'Modal untuk pengembangan bisnis',
                'initial_amount' => 30000000,
                'current_amount' => 28500000,
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Modal Lama',
                'description' => 'Modal yang sudah tidak aktif digunakan',
                'initial_amount' => 15000000,
                'current_amount' => 0,
                'is_active' => false,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($capitalTrackings as $tracking) {
            CapitalTracking::create($tracking);
        }
    }
}
