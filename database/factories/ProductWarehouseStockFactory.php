<?php

namespace Database\Factories;

use App\Product;
use App\ProductWarehouseStock;
use App\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductWarehouseStockFactory extends Factory
{
    protected $model = ProductWarehouseStock::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'stock_on_hand' => $this->faker->numberBetween(0, 1000),
            'reserved_stock' => $this->faker->numberBetween(0, 50),
            'safety_stock' => $this->faker->numberBetween(5, 20),
        ];
    }

    public function withStock(int $stock): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_on_hand' => $stock,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_on_hand' => 0,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_on_hand' => $this->faker->numberBetween(1, 5),
        ]);
    }

    public function highStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_on_hand' => $this->faker->numberBetween(500, 1000),
        ]);
    }
}