<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Sale;
use App\SaleItem;
use App\Product;
use App\StockMovement;
use App\Domains\User\Models\User;
use Carbon\Carbon;

class DummySalesSeeder extends Seeder
{
    public function run()
    {
        // Get first product for dummy data
        $product = Product::first();
        
        if (!$product) {
            $this->command->info('No products found. Please seed products first.');
            return;
        }
        
        // Get first user as cashier
        $cashier = User::first();
        if (!$cashier) {
            $this->command->info('No users found. Please seed users first.');
            return;
        }

        // Create dummy sales for the last 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->subDays($i);
            
            // Create 2-5 sales per day
            $salesCount = rand(2, 5);
            
            for ($j = 0; $j < $salesCount; $j++) {
                $saleNumber = 'TRX-' . $date->format('Ymd') . '-' . $i . $j . '-' . time() . rand(100, 999);
                $finalTotal = rand(50000, 200000);
                
                $sale = Sale::create([
                    'sale_number' => $saleNumber,
                    'cashier_id' => $cashier->id,
                    'subtotal' => $finalTotal,
                    'discount_total' => 0,
                    'final_total' => $finalTotal,
                    'status' => 'PAID',
                    'created_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                    'updated_at' => now()
                ]);
                
                // Create sale items
                $itemsCount = rand(1, 3);
                for ($k = 0; $k < $itemsCount; $k++) {
                    $randomProduct = Product::inRandomOrder()->first();
                    $qty = rand(1, 5);
                    
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $randomProduct->id,
                        'qty' => $qty,
                        'unit_price' => $randomProduct->selling_price ?? 10000
                    ]);
                }
            }
        }
        
        // Create some stock movements
        for ($i = 0; $i < 10; $i++) {
            $randomProduct = Product::inRandomOrder()->first();
            
            StockMovement::create([
                'product_id' => $randomProduct->id,
                'type' => rand(0, 1) ? 'IN' : 'OUT',
                'qty' => rand(5, 50),
                'note' => 'Dummy stock movement for testing',
                'performed_by' => $cashier->id,
                'created_at' => Carbon::now()->subDays(rand(0, 6))
            ]);
        }
        
        $this->command->info('Dummy sales and stock movements created successfully!');
    }
}