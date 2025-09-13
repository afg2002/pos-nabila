<?php

namespace Database\Factories;

use App\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $basePrice = $this->faker->randomFloat(2, 5000, 100000);
        $retailPrice = $basePrice * $this->faker->randomFloat(2, 1.2, 2.5);
        $wholesalePrice = $basePrice * $this->faker->randomFloat(2, 1.1, 1.8);
        
        return [
            'sku' => 'PRD-' . $this->faker->unique()->numerify('######'),
            'barcode' => $this->faker->unique()->ean13(),
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement(['Elektronik', 'Makanan', 'Minuman', 'Pakaian', 'Alat Tulis', 'Kesehatan']),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'liter', 'box', 'pack']),
            'base_cost' => $basePrice,
            'price_retail' => $retailPrice,
            'price_grosir' => $wholesalePrice,
            'min_margin_pct' => $this->faker->randomFloat(2, 10, 30),
            'is_active' => $this->faker->boolean(90),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            // Create initial stock movement with system user (ID 1)
            \App\StockMovement::create([
                'product_id' => $product->id,
                'qty' => $this->faker->numberBetween(10, 100),
                'type' => 'IN',
                'ref_type' => 'initial_stock',
                'ref_id' => null,
                'note' => 'Initial stock from seeder',
                'performed_by' => 1, // Assuming user ID 1 exists (admin)
            ]);
        });
    }
}