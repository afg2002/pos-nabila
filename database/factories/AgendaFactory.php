<?php

namespace Database\Factories;

use App\Models\Agenda;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgendaFactory extends Factory
{
    protected $model = Agenda::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'agenda_date' => $this->faker->date(),
            'agenda_time' => $this->faker->time('H:i'),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => 'pending',
            'related_type' => null,
            'related_id' => null,
            'completion_notes' => null,
            'created_by' => null,
        ];
    }

    public function purchaseOrderRelated($purchaseOrderId): self
    {
        return $this->state(function () use ($purchaseOrderId) {
            return [
                'related_type' => 'purchase_order',
                'related_id' => $purchaseOrderId,
                'title' => 'Follow up PO ' . $purchaseOrderId,
            ];
        });
    }
}