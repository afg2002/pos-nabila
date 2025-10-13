<?php

namespace Database\Factories;

use App\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'type' => $this->faker->randomElement(['regular', 'member', 'vip', 'preferred']),
            'discount_percentage' => $this->faker->numberBetween(0, 20),
            'total_purchases' => 0,
            'total_transactions' => 0,
            'birth_date' => $this->faker->optional()->date(),
            'gender' => $this->faker->optional()->randomElement(['male', 'female']),
            'is_active' => true,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
            'status' => 'inactive',
        ]);
    }

    public function vip(): self
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'vip',
            'discount_percentage' => 15,
        ]);
    }

    public function member(): self
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'member',
            'discount_percentage' => 10,
        ]);
    }

    public function preferred(): self
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'preferred',
            'discount_percentage' => 12,
        ]);
    }
}