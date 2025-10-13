<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Product;
use App\ProductUnit;
use App\StockMovement;
use App\Warehouse;
use Carbon\Carbon;

class ProductStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default warehouse
        $warehouse = Warehouse::where('is_default', true)->first();
        if (!$warehouse) {
            $warehouse = Warehouse::first();
        }

        if (!$warehouse) {
            $this->command->error('No warehouse found. Please run WarehouseSeeder first.');
            return;
        }

        // Get available product units
        $units = ProductUnit::where('is_active', true)->get();
        if ($units->isEmpty()) {
            $this->command->error('No product units found. Please run ProductUnitSeeder first.');
            return;
        }

        // Sample product categories
        $categories = [
            'Elektronik',
            'Fashion',
            'Makanan & Minuman',
            'Kesehatan & Kecantikan',
            'Rumah Tangga',
            'Olahraga',
            'Buku & Alat Tulis',
            'Otomotif'
        ];

        // Sample products data
        $sampleProducts = [
            // Elektronik
            ['name' => 'Smartphone Samsung Galaxy A54', 'category' => 'Elektronik', 'base_cost' => 3500000, 'price_retail' => 4200000],
            ['name' => 'Laptop ASUS VivoBook 14', 'category' => 'Elektronik', 'base_cost' => 6500000, 'price_retail' => 7800000],
            ['name' => 'Earphone Wireless Sony', 'category' => 'Elektronik', 'base_cost' => 450000, 'price_retail' => 540000],
            ['name' => 'Power Bank Xiaomi 10000mAh', 'category' => 'Elektronik', 'base_cost' => 180000, 'price_retail' => 216000],
            ['name' => 'Kabel USB Type-C', 'category' => 'Elektronik', 'base_cost' => 25000, 'price_retail' => 35000],

            // Fashion
            ['name' => 'Kaos Polo Pria', 'category' => 'Fashion', 'base_cost' => 85000, 'price_retail' => 120000],
            ['name' => 'Jeans Wanita Slim Fit', 'category' => 'Fashion', 'base_cost' => 150000, 'price_retail' => 210000],
            ['name' => 'Sepatu Sneakers Adidas', 'category' => 'Fashion', 'base_cost' => 650000, 'price_retail' => 850000],
            ['name' => 'Tas Ransel Laptop', 'category' => 'Fashion', 'base_cost' => 120000, 'price_retail' => 168000],
            ['name' => 'Jam Tangan Digital Casio', 'category' => 'Fashion', 'base_cost' => 280000, 'price_retail' => 350000],

            // Makanan & Minuman
            ['name' => 'Kopi Arabica Premium 250g', 'category' => 'Makanan & Minuman', 'base_cost' => 45000, 'price_retail' => 65000],
            ['name' => 'Teh Hijau Organik', 'category' => 'Makanan & Minuman', 'base_cost' => 35000, 'price_retail' => 50000],
            ['name' => 'Madu Murni 500ml', 'category' => 'Makanan & Minuman', 'base_cost' => 75000, 'price_retail' => 95000],
            ['name' => 'Biskuit Coklat Premium', 'category' => 'Makanan & Minuman', 'base_cost' => 18000, 'price_retail' => 25000],
            ['name' => 'Minuman Energi Botol', 'category' => 'Makanan & Minuman', 'base_cost' => 8000, 'price_retail' => 12000],

            // Kesehatan & Kecantikan
            ['name' => 'Vitamin C 1000mg', 'category' => 'Kesehatan & Kecantikan', 'base_cost' => 85000, 'price_retail' => 110000],
            ['name' => 'Masker Wajah Aloe Vera', 'category' => 'Kesehatan & Kecantikan', 'base_cost' => 15000, 'price_retail' => 22000],
            ['name' => 'Shampo Anti Ketombe', 'category' => 'Kesehatan & Kecantikan', 'base_cost' => 28000, 'price_retail' => 38000],
            ['name' => 'Sabun Mandi Herbal', 'category' => 'Kesehatan & Kecantikan', 'base_cost' => 12000, 'price_retail' => 18000],
            ['name' => 'Parfum Pria 100ml', 'category' => 'Kesehatan & Kecantikan', 'base_cost' => 180000, 'price_retail' => 250000],

            // Rumah Tangga
            ['name' => 'Panci Set Stainless Steel', 'category' => 'Rumah Tangga', 'base_cost' => 250000, 'price_retail' => 350000],
            ['name' => 'Gelas Kaca Set 6pcs', 'category' => 'Rumah Tangga', 'base_cost' => 45000, 'price_retail' => 65000],
            ['name' => 'Handuk Mandi Premium', 'category' => 'Rumah Tangga', 'base_cost' => 55000, 'price_retail' => 75000],
            ['name' => 'Lampu LED 12 Watt', 'category' => 'Rumah Tangga', 'base_cost' => 25000, 'price_retail' => 35000],
            ['name' => 'Sapu Lantai Microfiber', 'category' => 'Rumah Tangga', 'base_cost' => 35000, 'price_retail' => 50000],
        ];

        $createdProducts = 0;
        $totalStockMovements = 0;

        foreach ($sampleProducts as $index => $productData) {
            // Generate unique SKU
            $sku = 'PRD-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            
            // Check if product already exists
            if (Product::where('sku', $sku)->exists()) {
                continue;
            }

            // Random unit selection
            $unit = $units->random();
            
            // Calculate prices with margin
            $baseCost = $productData['base_cost'];
            $priceRetail = $productData['price_retail'];
            $priceSemiGrosir = $priceRetail * 0.95; // 5% discount
            $priceGrosir = $priceRetail * 0.90; // 10% discount
            $minMarginPct = (($priceRetail - $baseCost) / $baseCost) * 100;

            // Create product
            $product = Product::create([
                'sku' => $sku,
                'barcode' => '8901234' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'name' => $productData['name'],
                'category' => $productData['category'],
                'unit_id' => $unit->id,
                'base_cost' => $baseCost,
                'cost_price' => $baseCost,
                'price_retail' => $priceRetail,
                'price_semi_grosir' => $priceSemiGrosir,
                'price_grosir' => $priceGrosir,
                'min_margin_pct' => round($minMarginPct, 2),
                'default_price_type' => 'retail',
                'current_stock' => 0,
                'status' => 'active',
            ]);

            $createdProducts++;

            // Create initial stock movement
            $initialStock = rand(20, 100);
            StockMovement::create([
                'product_id' => $product->id,
                'qty' => $initialStock,
                'type' => 'IN',
                'ref_type' => 'initial_stock',
                'ref_id' => null,
                'note' => "Stok awal produk - {$product->name}",
                'performed_by' => 1,
                'warehouse_id' => $warehouse->id,
                'warehouse' => $warehouse->code,
                'stock_before' => 0,
                'stock_after' => $initialStock,
                'metadata' => json_encode(['initial_setup' => true]),
                'reason_code' => 'INITIAL_STOCK',
                'approved_by' => 1,
                'approved_at' => now(),
            ]);
            $totalStockMovements++;

            // Create some random stock movements (sales simulation)
            $movementsCount = rand(2, 5);
            $currentStock = $initialStock;

            for ($i = 0; $i < $movementsCount; $i++) {
                $movementType = rand(1, 10) <= 7 ? 'OUT' : 'IN'; // 70% OUT, 30% IN
                
                if ($movementType === 'OUT' && $currentStock > 5) {
                    $qty = rand(1, min(10, $currentStock - 5));
                    $stockBefore = $currentStock;
                    $currentStock -= $qty;
                    
                    StockMovement::create([
                        'product_id' => $product->id,
                        'qty' => -$qty,
                        'type' => 'OUT',
                        'ref_type' => 'sale',
                        'ref_id' => null,
                        'note' => 'Penjualan produk',
                        'performed_by' => 1,
                        'warehouse_id' => $warehouse->id,
                        'warehouse' => $warehouse->code,
                        'stock_before' => $stockBefore,
                        'stock_after' => $currentStock,
                        'metadata' => json_encode(['sale_simulation' => true]),
                        'reason_code' => 'SALE',
                        'approved_by' => 1,
                        'approved_at' => now(),
                        'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    ]);
                } else {
                    $qty = rand(10, 30);
                    $stockBefore = $currentStock;
                    $currentStock += $qty;
                    
                    StockMovement::create([
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'type' => 'IN',
                        'ref_type' => 'purchase',
                        'ref_id' => null,
                        'note' => 'Pembelian stok tambahan',
                        'performed_by' => 1,
                        'warehouse_id' => $warehouse->id,
                        'warehouse' => $warehouse->code,
                        'stock_before' => $stockBefore,
                        'stock_after' => $currentStock,
                        'metadata' => json_encode(['purchase_simulation' => true]),
                        'reason_code' => 'PURCHASE',
                        'approved_by' => 1,
                        'approved_at' => now(),
                        'created_at' => Carbon::now()->subDays(rand(1, 25)),
                    ]);
                }
                $totalStockMovements++;
            }

            // Update product's current stock
            $product->update(['current_stock' => $currentStock]);
        }

        $this->command->info("Successfully created {$createdProducts} products with stock data!");
        $this->command->info("Total stock movements created: {$totalStockMovements}");
        
        // Display category distribution
        $categoryStats = Product::select('category')
            ->selectRaw('count(*) as count')
            ->groupBy('category')
            ->get();
            
        $this->command->info("\nProduct distribution by category:");
        foreach ($categoryStats as $stat) {
            $this->command->info("- {$stat->category}: {$stat->count} products");
        }
    }
}