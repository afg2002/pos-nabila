<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Toko Utama',
                'code' => 'MAIN',
                'type' => 'store',
                'address' => 'Jl. Raya Utama No. 123, Jakarta',
                'phone' => '021-12345678',
                'is_default' => true,
            ],
            [
                'name' => 'Gudang Pusat',
                'code' => 'WH01',
                'type' => 'warehouse',
                'address' => 'Jl. Gudang No. 456, Jakarta',
                'phone' => '021-87654321',
                'is_default' => false,
            ]
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::firstOrCreate(
                ['code' => $warehouse['code']],
                $warehouse
            );
        }
    }
}