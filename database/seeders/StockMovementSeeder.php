<?php

namespace Database\Seeders;

use App\Product;
use App\StockMovement;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing products
        $products = Product::where('is_active', true)->get();
        
        if ($products->isEmpty()) {
            $this->command->info('No products found. Please run ProductSeeder first.');
            return;
        }
        
        // Create stock movements for each product
        foreach ($products as $product) {
            $currentStock = $product->current_stock ?? 0;
            
            // Initial stock IN
            $initialStock = rand(50, 200);
            $stockBefore = $currentStock;
            $stockAfter = $stockBefore + $initialStock;
            
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'IN',
                'qty' => $initialStock,
                'ref_type' => 'manual',
                'ref_id' => null,
                'note' => 'Stok awal produk',
                'performed_by' => 1, // Assuming admin user has ID 1
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
            
            $currentStock = $stockAfter;
            
            // Stock OUT movements
            for ($i = 0; $i < rand(2, 5); $i++) {
                $outQty = rand(5, min(20, $currentStock));
                if ($currentStock >= $outQty) {
                    $stockBefore = $currentStock;
                    $stockAfter = $stockBefore - $outQty;
                    
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'OUT',
                        'qty' => -$outQty, // Negative for OUT
                        'ref_type' => 'sale',
                        'ref_id' => null,
                        'note' => 'Penjualan produk',
                        'performed_by' => 1,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'created_at' => Carbon::now()->subDays(rand(1, 25)),
                        'updated_at' => Carbon::now()->subDays(rand(1, 25)),
                    ]);
                    
                    $currentStock = $stockAfter;
                }
            }
            
            // Additional stock IN
            $additionalStock = rand(20, 50);
            $stockBefore = $currentStock;
            $stockAfter = $stockBefore + $additionalStock;
            
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'IN',
                'qty' => $additionalStock,
                'ref_type' => 'purchase',
                'ref_id' => null,
                'note' => 'Pembelian tambahan',
                'performed_by' => 1,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'created_at' => Carbon::now()->subDays(rand(1, 15)),
                'updated_at' => Carbon::now()->subDays(rand(1, 15)),
            ]);
            
            // Update final product stock
            $product->update(['current_stock' => $stockAfter]);
        }
        
        $this->command->info('Stock movements created successfully!');
    }
}