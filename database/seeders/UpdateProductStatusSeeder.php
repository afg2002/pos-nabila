<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateProductStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder updates existing products to set their status based on is_active field
     * and ensures all products have a proper status value.
     */
    public function run(): void
    {
        $this->command->info('Starting to update product status...');
        
        try {
            DB::beginTransaction();
            
            // Count products that need updating
            $totalProducts = Product::whereNull('status')->count();
            $this->command->info("Found {$totalProducts} products without status.");
            
            if ($totalProducts === 0) {
                $this->command->info('All products already have status assigned.');
                DB::rollBack();
                return;
            }
            
            // Update products in batches to avoid memory issues
            $batchSize = 100;
            $processed = 0;
            
            Product::whereNull('status')
                ->chunk($batchSize, function ($products) use (&$processed) {
                    foreach ($products as $product) {
                        // Set status based on is_active field
                        $status = $product->is_active ? 'active' : 'inactive';
                        
                        $product->update([
                            'status' => $status,
                            'updated_at' => now()
                        ]);
                        
                        $processed++;
                        
                        // Show progress every 50 products
                        if ($processed % 50 === 0) {
                            $this->command->info("Processed {$processed} products...");
                        }
                    }
                });
            
            // Update statistics
            $activeCount = Product::where('status', 'active')->count();
            $inactiveCount = Product::where('status', 'inactive')->count();
            $discontinuedCount = Product::where('status', 'discontinued')->count();
            
            DB::commit();
            
            $this->command->info('Product status update completed successfully!');
            $this->command->info("Summary:");
            $this->command->info("- Total processed: {$processed}");
            $this->command->info("- Active products: {$activeCount}");
            $this->command->info("- Inactive products: {$inactiveCount}");
            $this->command->info("- Discontinued products: {$discontinuedCount}");
            
            // Log the operation
            Log::info('Product status seeder completed', [
                'processed' => $processed,
                'active' => $activeCount,
                'inactive' => $inactiveCount,
                'discontinued' => $discontinuedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error updating product status: ' . $e->getMessage());
            Log::error('Product status seeder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}