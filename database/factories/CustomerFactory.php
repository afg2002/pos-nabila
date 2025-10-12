<?php

namespace Database\Factories;

use App\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'type' => $this->faker->randomElement(['regular', 'member', 'vip']),
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
}