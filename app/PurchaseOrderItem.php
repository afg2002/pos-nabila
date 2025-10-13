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
        'unit_price',
        'total_cost',
        'selling_price',
        'profit_per_item',
        'profit_margin',
        'received_quantity',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'profit_per_item' => 'decimal:2',
        'profit_margin' => 'decimal:2',
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Profit Calculation Methods
    public function calculateProfitPerItem(): float
    {
        $sellingPrice = $this->selling_price ?? $this->unit_price;
        return max(0, $sellingPrice - $this->unit_cost);
    }

    public function calculateTotalProfit(): float
    {
        return $this->calculateProfitPerItem() * $this->quantity;
    }

    public function calculateProfitMargin(): float
    {
        $sellingPrice = $this->selling_price ?? $this->unit_price;
        
        if ($sellingPrice <= 0) {
            return 0;
        }
        
        $profitPerItem = $this->calculateProfitPerItem();
        return ($profitPerItem / $sellingPrice) * 100;
    }

    public function updateProfitCalculations(): void
    {
        $this->profit_per_item = $this->calculateProfitPerItem();
        $this->profit_margin = $this->calculateProfitMargin();
        
        // Set selling_price to unit_price if not set
        if (is_null($this->selling_price)) {
            $this->selling_price = $this->unit_price;
        }
        
        $this->save();
    }

    // Accessors
    public function getProfitPerItemAttribute(): float
    {
        return $this->calculateProfitPerItem();
    }

    public function getTotalProfitAttribute(): float
    {
        return $this->calculateTotalProfit();
    }

    public function getProfitMarginAttribute(): float
    {
        return $this->calculateProfitMargin();
    }

    public function getFormattedProfitPerItemAttribute(): string
    {
        return 'Rp ' . number_format($this->profit_per_item, 0, ',', '.');
    }

    public function getFormattedTotalProfitAttribute(): string
    {
        return 'Rp ' . number_format($this->total_profit, 0, ',', '.');
    }

    public function getFormattedProfitMarginAttribute(): string
    {
        return number_format($this->profit_margin, 2) . '%';
    }

    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->received_quantity;
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    public function getIsPartiallyReceivedAttribute(): bool
    {
        return $this->received_quantity > 0 && $this->received_quantity < $this->quantity;
    }

    // Scopes
    public function scopeWithProfit($query)
    {
        return $query->whereNotNull('selling_price')
                    ->where('selling_price', '>', 0);
    }

    public function scopeHighMargin($query, $margin = 30)
    {
        return $query->where('profit_margin', '>=', $margin);
    }

    public function scopeLowMargin($query, $margin = 10)
    {
        return $query->where('profit_margin', '<=', $margin);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Auto-calculate profit fields when saving
            if ($item->isDirty(['unit_cost', 'selling_price', 'unit_price'])) {
                $item->profit_per_item = $item->calculateProfitPerItem();
                $item->profit_margin = $item->calculateProfitMargin();
                
                // Set selling_price to unit_price if not set
                if (is_null($item->selling_price)) {
                    $item->selling_price = $item->unit_price;
                }
            }
        });

        static::created(function ($item) {
            // Update purchase order total when item is created
            $item->purchaseOrder->updateTotals();
        });

        static::updated(function ($item) {
            // Update purchase order total when item is updated
            if ($item->isDirty(['quantity', 'unit_cost', 'total_cost', 'selling_price'])) {
                $item->purchaseOrder->updateTotals();
            }
        });

        static::deleted(function ($item) {
            // Update purchase order total when item is deleted
            $item->purchaseOrder->updateTotals();
        });
    }
}