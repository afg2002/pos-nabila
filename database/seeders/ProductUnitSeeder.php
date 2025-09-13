<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\ProductUnit;

class ProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Pieces',
                'abbreviation' => 'Pcs',
                'description' => 'Satuan per buah/piece',
                'sort_order' => 1
            ],
            [
                'name' => 'Lusin',
                'abbreviation' => 'Ls',
                'description' => 'Satuan per lusin (12 buah)',
                'sort_order' => 2
            ],
            [
                'name' => 'Karton',
                'abbreviation' => 'Karton',
                'description' => 'Satuan per karton',
                'sort_order' => 3
            ],
            [
                'name' => 'Box',
                'abbreviation' => 'Box',
                'description' => 'Satuan per box',
                'sort_order' => 4
            ],
            [
                'name' => 'Pound',
                'abbreviation' => 'Lb',
                'description' => 'Satuan per pound',
                'sort_order' => 5
            ]
        ];

        foreach ($units as $unit) {
            ProductUnit::firstOrCreate(
                ['name' => $unit['name']],
                $unit
            );
        }
    }
}
