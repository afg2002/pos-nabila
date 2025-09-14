<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Sale;
use App\SaleItem;
use App\Product;
use App\Domains\User\Models\User;
use Carbon\Carbon;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user and some products
        $user = User::first();
        $products = Product::take(5)->get();
        
        if (!$user || $products->isEmpty()) {
            $this->command->info('Please run UserSeeder and ProductSeeder first');
            return;
        }

        // Create sales for the last 30 days
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Create 1-5 sales per day
            $salesCount = rand(1, 5);
            
            for ($j = 0; $j < $salesCount; $j++) {
                $sale = Sale::create([
                    'sale_number' => 'SL-' . $date->format('Ymd') . '-' . str_pad($j + 1, 3, '0', STR_PAD_LEFT),
                    'cashier_id' => $user->id,
                    'subtotal' => 0,
                    'discount_total' => 0,
                    'final_total' => 0,
                    'status' => 'PAID',
                    'created_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                    'updated_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                ]);

                $subtotal = 0;
                
                // Add 1-3 items per sale
                $itemsCount = rand(1, 3);
                $selectedProducts = $products->random($itemsCount);
                
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 5);
                    $unitPrice = $product->price_retail;
                    $itemTotal = $qty * $unitPrice;
                    
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'price_tier' => 'retail',
                        'unit_price' => $unitPrice,
                    ]);
                    
                    $subtotal += $itemTotal;
                }
                
                // Update sale totals
                $discountTotal = rand(0, 1) ? $subtotal * 0.05 : 0; // 5% discount sometimes
                $finalTotal = $subtotal - $discountTotal;
                
                $sale->update([
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'final_total' => $finalTotal,
                ]);
            }
        }
        
        $this->command->info('Sales seeder completed successfully!');
    }
}