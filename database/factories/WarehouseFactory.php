<?php

namespace Database\Factories;

use App\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company . ' Warehouse',
            'code' => strtoupper($this->faker->unique()->lexify('WH???')),
            'type' => $this->faker->randomElement(['warehouse', 'store', 'kiosk']),
            'branch' => $this->faker->optional()->city,
            'address' => $this->faker->address,
            'phone' => $this->faker->optional()->phoneNumber,
            'is_default' => false,
        ];
    }

    public function default()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true,
            ];
        });
    }

    public function asStore()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'store',
            ];
        });
    }

    public function asWarehouse()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'warehouse',
            ];
        });
    }

    public function main()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Main Store',
                'code' => 'MAIN',
                'type' => 'store',
                'is_default' => true,
            ];
        });
    }
}