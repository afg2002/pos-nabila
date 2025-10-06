<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $supplier = Supplier::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $user = User::factory()->create();

        $orderDate = now();
        $expected = $this->faker->dateTimeBetween('+1 days', '+14 days');
        $expectedCarbon = Carbon::instance($expected);

        return [
            'po_number' => PurchaseOrder::generatePoNumber(),
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'supplier_name' => $supplier->name,
            'supplier_contact' => $supplier->contact_person,
            'order_date' => $orderDate,
            'expected_date' => $expectedCarbon,
            'expected_delivery_date' => $expectedCarbon,
            'payment_due_date' => $this->faker->optional()->dateTimeBetween($expectedCarbon, '+30 days'),
            'payment_schedule_date' => $this->faker->optional()->dateTimeBetween($orderDate, '+20 days'),
            'reminder_enabled' => $this->faker->boolean(50),
            'total_amount' => $this->faker->randomFloat(2, 100000, 5000000),
            'paid_amount' => 0,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $this->faker->optional()->sentence(),
            'created_by' => $user->id,
            'capital_tracking_id' => null,
            'cancellation_reason' => null,
        ];
    }

    public function received(): self
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'received',
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function paid(): self
    {
        return $this->state(fn(array $attributes) => [
            'paid_amount' => $attributes['total_amount'] ?? 0,
            'payment_status' => 'paid',
        ]);
    }
}