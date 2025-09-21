<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_cost',
        'total_cost',
        'received_quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'received_quantity' => 'integer',
    ];

    /**
     * Get the purchase order that owns this item
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product associated with this item (if exists)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get remaining quantity to receive
     */
    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->received_quantity;
    }

    /**
     * Check if item is fully received
     */
    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    /**
     * Get receive percentage
     */
    public function getReceivePercentageAttribute(): float
    {
        if ($this->quantity == 0) {
            return 0;
        }
        return ($this->received_quantity / $this->quantity) * 100;
    }

    /**
     * Update received quantity
     */
    public function updateReceivedQuantity(int $quantity): void
    {
        $this->received_quantity = min($quantity, $this->quantity);
        $this->save();
    }

    /**
     * Calculate total cost based on quantity and unit cost
     */
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->quantity * $this->unit_cost;
        $this->save();
    }
}
