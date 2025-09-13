<?php

namespace App\Console\Commands;

use App\Product;
use Illuminate\Console\Command;

class SyncProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync {--all : Sync all products} {--unsync : Sync only unsynchronized products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi stok produk antara current_stock dan stock movements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi stok produk...');
        
        $query = Product::query();
        
        if ($this->option('unsync')) {
            // Filter hanya produk yang tidak sinkron
            $products = $query->get()->filter(function($product) {
                return !$product->isStockSynced();
            });
            $this->info('Mode: Hanya produk yang tidak sinkron');
        } else {
            // Semua produk
            $products = $query->get();
            $this->info('Mode: Semua produk');
        }
        
        if ($products->isEmpty()) {
            $this->info('Tidak ada produk yang perlu disinkronisasi.');
            return 0;
        }
        
        $this->info("Ditemukan {$products->count()} produk untuk disinkronisasi.");
        
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();
        
        $syncedCount = 0;
        $errorCount = 0;
        
        foreach ($products as $product) {
            try {
                $oldStock = $product->current_stock;
                $newStock = $product->syncStock();
                
                if ($oldStock != $newStock) {
                    $syncedCount++;
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("\nError pada produk {$product->sku}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        
        $this->newLine(2);
        $this->info("Sinkronisasi selesai!");
        $this->info("- Produk yang disinkronisasi: {$syncedCount}");
        $this->info("- Produk dengan error: {$errorCount}");
        $this->info("- Total produk diproses: {$products->count()}");
        
        return 0;
    }
}
