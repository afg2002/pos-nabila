<?php

namespace Database\Factories;

use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrder;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        $purchaseOrder = PurchaseOrder::factory()->create();
        $product = Product::factory()->create();

        $quantity = $this->faker->numberBetween(1, 50);
        $unitPrice = $this->faker->randomFloat(2, 1000, 500000);

        return [
            'purchase_order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $quantity,
            // PurchaseOrderItem setUnitPriceAttribute akan sinkron ke unit_cost
            'unit_price' => $unitPrice,
            // total_cost dihitung otomatis via model events
            'received_quantity' => 0,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function receivedPartially(int $received): self
    {
        return $this->state(fn(array $attributes) => [
            'received_quantity' => min($received, $attributes['quantity'] ?? $received),
        ]);
    }

    public function fullyReceived(): self
    {
        return $this->state(fn(array $attributes) => [
            'received_quantity' => $attributes['quantity'] ?? 0,
        ]);
    }
}