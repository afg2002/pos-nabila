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
        $this->command->info('Creating realistic Indonesian products...');
        
        // Generate 50 random products using the updated factory
        Product::factory()->count(50)->create();
        
        // Create some specific featured products
        $featuredProducts = [
            [
                'sku' => 'MK-001',
                'barcode' => '1234567890001',
                'name' => 'Nasi Goreng Spesial Seafood',
                'category' => 'Makanan',
                'unit_id' => 1, // Pieces
                'base_cost' => 20000,
                'price_retail' => 35000,
                'price_semi_grosir' => 30000,
                'price_grosir' => 28000,
                'default_price_type' => 'retail',
                'min_margin_pct' => 20.0,
                'current_stock' => 25,
                'is_active' => true,
            ],
            [
                'sku' => 'MN-001',
                'barcode' => '1234567890002',
                'name' => 'Es Teh Manis Jumbo',
                'category' => 'Minuman',
                'unit_id' => 1, // Pieces
                'base_cost' => 3000,
                'price_retail' => 8000,
                'price_semi_grosir' => 6000,
                'price_grosir' => 5000,
                'default_price_type' => 'retail',
                'min_margin_pct' => 15.0,
                'current_stock' => 100,
                'is_active' => true,
            ],
            [
                'sku' => 'EL-001',
                'barcode' => '1234567890003',
                'name' => 'Smartphone Samsung Galaxy A54 5G',
                'category' => 'Elektronik',
                'unit_id' => 1, // Pieces
                'base_cost' => 4500000,
                'price_retail' => 6500000,
                'price_semi_grosir' => 6000000,
                'price_grosir' => 5500000,
                'default_price_type' => 'retail',
                'min_margin_pct' => 25.0,
                'current_stock' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'PK-001',
                'barcode' => '1234567890004',
                'name' => 'Kaos Polo Katun Premium Pria',
                'category' => 'Pakaian',
                'unit_id' => 1, // Pieces
                'base_cost' => 45000,
                'price_retail' => 85000,
                'price_semi_grosir' => 70000,
                'price_grosir' => 60000,
                'default_price_type' => 'semi_grosir',
                'min_margin_pct' => 18.0,
                'current_stock' => 20,
                'is_active' => true,
            ],
            [
                'sku' => 'AT-001',
                'barcode' => '1234567890005',
                'name' => 'Pulpen Gel Pilot G2 Set 12 Warna',
                'category' => 'Alat Tulis',
                'unit_id' => 7, // Pack
                'base_cost' => 25000,
                'price_retail' => 45000,
                'price_semi_grosir' => 38000,
                'price_grosir' => 32000,
                'default_price_type' => 'grosir',
                'min_margin_pct' => 12.0,
                'current_stock' => 50,
                'is_active' => true,
            ],
            [
                'sku' => 'KS-001',
                'barcode' => '1234567890006',
                'name' => 'Masker KN95 Medical Grade Box 50pcs',
                'category' => 'Kesehatan',
                'unit_id' => 4, // Box
                'base_cost' => 75000,
                'price_retail' => 125000,
                'price_semi_grosir' => 110000,
                'price_grosir' => 95000,
                'default_price_type' => 'retail',
                'min_margin_pct' => 20.0,
                'current_stock' => 30,
                'is_active' => true,
            ],
        ];
        
        foreach ($featuredProducts as $productData) {
            $product = Product::create($productData);
            
            // Create initial stock movement
            \App\StockMovement::create([
                'product_id' => $product->id,
                'qty' => $product->current_stock,
                'type' => 'IN',
                'ref_type' => 'initial_stock',
                'ref_id' => null,
                'note' => 'Featured product - initial stock',
                'performed_by' => 1,
            ]);
        }
        
        $totalProducts = Product::count();
        $this->command->info("Successfully created {$totalProducts} products with realistic Indonesian names!");
        
        // Display some statistics
        $categoryCounts = Product::select('category')
            ->selectRaw('count(*) as count')
            ->groupBy('category')
            ->get();
            
        $this->command->info('\nProduct distribution by category:');
        foreach ($categoryCounts as $category) {
            $this->command->info("- {$category->category}: {$category->count} products");
        }
    }
}