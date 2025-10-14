<?php

namespace Database\Factories;

use App\Sale;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(10000, 500000);
        $discountTotal = $this->faker->boolean(30) ? $subtotal * 0.05 : 0; // 30% chance of discount
        
        return [
            'sale_number' => 'POS-' . date('Ymd') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'cashier_id' => User::factory(),
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'final_total' => $subtotal - $discountTotal,
            'payment_method' => $this->faker->randomElement(['cash', 'transfer', 'edc', 'qr']),
            'payment_notes' => $this->faker->optional()->sentence(),
            'status' => 'PAID',
        ];
    }

    public function cancelled(): self
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'CANCELLED',
        ]);
    }

    public function withDiscount(float $percentage = 10): self
    {
        return $this->state(function (array $attributes) use ($percentage) {
            $discountTotal = $attributes['subtotal'] * ($percentage / 100);
            
            return [
                'discount_total' => $discountTotal,
                'final_total' => $attributes['subtotal'] - $discountTotal,
            ];
        });
    }

    public function cash(): self
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'cash',
        ]);
    }

    public function card(): self
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'edc',
        ]);
    }
}