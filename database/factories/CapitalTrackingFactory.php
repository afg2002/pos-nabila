<?php

namespace Database\Factories;

use App\CapitalTracking;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CapitalTrackingFactory extends Factory
{
    protected $model = CapitalTracking::class;

    public function definition(): array
    {
        $initialAmount = $this->faker->numberBetween(1000000, 100000000);
        
        return [
            'name' => $this->faker->randomElement([
                'Modal Utama',
                'Modal Investasi',
                'Modal Darurat',
                'Modal Ekspansi',
                'Modal Operasional'
            ]),
            'description' => $this->faker->optional()->sentence(),
            'initial_amount' => $initialAmount,
            'current_amount' => $initialAmount,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withUsage(int $percentage = 50): self
    {
        return $this->state(function (array $attributes) use ($percentage) {
            $initialAmount = $attributes['initial_amount'];
            $usedAmount = ($initialAmount * $percentage) / 100;
            
            return [
                'current_amount' => $initialAmount - $usedAmount,
            ];
        });
    }
}