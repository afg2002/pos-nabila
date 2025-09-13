<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Product;
use App\StockMovement;
use App\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResetProductData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:reset {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all product data and regenerate with realistic dummy data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete ALL products, stock movements, and sale items. Are you sure?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('Starting product data reset...');

        try {
            // Disable foreign key checks temporarily
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Delete related data first (foreign key constraints)
            $this->info('Deleting sale items...');
            SaleItem::truncate();

            $this->info('Deleting stock movements...');
            StockMovement::truncate();

            // Clean up product photos
            $this->info('Cleaning up product photos...');
            $products = Product::whereNotNull('photo')->get();
            foreach ($products as $product) {
                if ($product->photo && Storage::disk('public')->exists('products/' . $product->photo)) {
                    Storage::disk('public')->delete('products/' . $product->photo);
                }
            }

            // Delete all products
            $this->info('Deleting all products...');
            Product::truncate();

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('✅ All product data has been successfully deleted!');
            $this->info('Now run: php artisan db:seed --class=ProductSeeder');

        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error occurred: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}