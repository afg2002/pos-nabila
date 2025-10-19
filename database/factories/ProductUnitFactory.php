<?php

namespace Database\Factories;

use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductUnitFactory extends Factory
{
    protected $model = ProductUnit::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Pieces', 'Lusin', 'Kg', 'Box', 'Pack', 'Meter', 'Liter'
            ]),
            'abbreviation' => $this->faker->unique()->randomElement([
                'pcs', 'lsn', 'kg', 'box', 'pack', 'm', 'l'
            ]),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function ordered(int $order): self
    {
        return $this->state(fn(array $attributes) => [
            'sort_order' => $order,
        ]);
    }
}