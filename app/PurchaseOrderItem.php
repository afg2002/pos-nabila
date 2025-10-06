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
        // allow alias input from UI/tests
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Alias: unit_price <=> unit_cost
    public function getUnitPriceAttribute()
    {
        // Prioritize persisted unit_price column if available
        if (array_key_exists('unit_price', $this->attributes) && $this->attributes['unit_price'] !== null) {
            return (float) $this->attributes['unit_price'];
        }
        return (float) ($this->attributes['unit_cost'] ?? 0);
    }

    // Mutator: when unit_price is set, keep unit_cost and unit_price in sync
    public function setUnitPriceAttribute($value)
    {
        $numeric = (float) $value;
        $this->attributes['unit_price'] = $numeric;
        $this->attributes['unit_cost'] = $numeric;
    }

    // Mutator: when unit_cost is set, mirror to unit_price
    public function setUnitCostAttribute($value)
    {
        $numeric = (float) $value;
        $this->attributes['unit_cost'] = $numeric;
        $this->attributes['unit_price'] = $numeric;
    }

    // Auto-calc total_cost when saving
    protected static function booted()
    {
        static::creating(function ($model) {
            // Ensure unit_cost mirrors unit_price if provided
            if (isset($model->attributes['unit_price'])) {
                $model->attributes['unit_cost'] = (float) $model->attributes['unit_price'];
            }
            $qty = (int) ($model->quantity ?? 0);
            $unit = (float) ($model->unit_cost ?? 0);
            $model->total_cost = $qty * $unit;
        });

        static::updating(function ($model) {
            // Keep unit_cost mirrors unit_price if provided on update
            if (isset($model->attributes['unit_price'])) {
                $model->attributes['unit_cost'] = (float) $model->attributes['unit_price'];
            }
            // Keep total_cost in sync
            $qty = (int) ($model->quantity ?? 0);
            $unit = (float) ($model->unit_cost ?? 0);
            $model->total_cost = $qty * $unit;
        });
    }

    /**
     * Get remaining quantity to receive
     */
    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->received_quantity;
    }

    /**
     * Check if fully received
     */
    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    /**
     * Calculate receive percentage
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
