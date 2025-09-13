<?php

namespace Database\Seeders;

use App\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate 5 sample products
        Product::factory()->count(5)->create();
        
        // Optionally create some specific products
        Product::create([
            'sku' => 'PRD-001',
            'barcode' => '1234567890123',
            'name' => 'Laptop Gaming ASUS ROG',
            'category' => 'Elektronik',
            'unit' => 'pcs',
            'base_cost' => 8000000,
            'price_retail' => 12000000,
            'price_grosir' => 10000000,
            'min_margin_pct' => 25.0,
            'is_active' => true,
        ]);
        
        Product::create([
            'sku' => 'PRD-002',
            'barcode' => '2345678901234',
            'name' => 'Mouse Wireless Logitech',
            'category' => 'Elektronik',
            'unit' => 'pcs',
            'base_cost' => 150000,
            'price_retail' => 250000,
            'price_grosir' => 200000,
            'min_margin_pct' => 20.0,
            'is_active' => true,
        ]);
    }
}