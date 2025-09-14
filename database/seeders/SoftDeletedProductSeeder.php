<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Product;
use App\ProductUnit;

class SoftDeletedProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unit = ProductUnit::first() ?? ProductUnit::create([
            'name' => 'Pcs',
            'abbreviation' => 'pcs'
        ]);

        // Create 3 products and soft delete them
        for ($i = 1; $i <= 3; $i++) {
            $product = Product::create([
                'name' => "Produk Test Deleted {$i}",
                'sku' => "TEST-DEL-{$i}",
                'barcode' => "123456789{$i}",
                'category' => 'Test Category',
                'unit_id' => $unit->id,
                'base_cost' => 10000,
                'price_retail' => 15000,
                'price_grosir' => 12000,
                'price_semi_grosir' => 13000,
                'current_stock' => 10,
                'status' => 'active',
                'default_price_type' => 'retail'
            ]);

            // Soft delete the product
            $product->softDeleteWithStatus();
        }

        $this->command->info('Created 3 soft deleted products for testing bulk restore.');
    }
}