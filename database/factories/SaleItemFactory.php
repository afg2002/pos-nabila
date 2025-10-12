<?php

namespace Database\Factories;

use App\SaleItem;
use App\Sale;
use App\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        $qty = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->numberBetween(5000, 100000);
        
        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'qty' => $qty,
            'price_tier' => $this->faker->randomElement(['retail', 'grosir', 'semi_grosir', 'custom']),
            'unit_price' => $unitPrice,
            'custom_reason' => null,
            'margin_pct_at_sale' => $this->faker->randomFloat(2, 10, 50),
            'below_margin_flag' => false,
            'custom_item_name' => null,
            'custom_item_description' => null,
            'is_custom' => false,
        ];
    }

    public function custom(): self
    {
        return $this->state(fn(array $attributes) => [
            'product_id' => null,
            'custom_item_name' => $this->faker->words(3, true),
            'custom_item_description' => $this->faker->optional()->sentence(),
            'is_custom' => true,
            'price_tier' => 'custom',
            'margin_pct_at_sale' => 0,
        ]);
    }

    public function belowMargin(): self
    {
        return $this->state(fn(array $attributes) => [
            'below_margin_flag' => true,
            'custom_reason' => $this->faker->sentence(),
            'price_tier' => 'custom',
        ]);
    }

    public function retail(): self
    {
        return $this->state(fn(array $attributes) => [
            'price_tier' => 'retail',
        ]);
    }

    public function grosir(): self
    {
        return $this->state(fn(array $attributes) => [
            'price_tier' => 'grosir',
        ]);
    }
}