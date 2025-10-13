<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'status',
        'total_amount',
        'paid_amount',
        'payment_status',
        'expected_delivery_date',
        'received_date',
        'payment_date',
        'payment_method',
        'notes',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'received_date' => 'date',
        'payment_date' => 'date',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function incomingGoodsAgendas(): HasMany
    {
        return $this->hasMany(IncomingGoodsAgenda::class);
    }

    public function cashflowAgendas(): HasMany
    {
        return $this->hasMany(CashflowAgenda::class);
    }

    // Methods
    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->expected_delivery_date && 
               $this->expected_delivery_date->isPast() && 
               $this->status === 'pending';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Profit Calculation Methods
    public function calculateTotalCost(): float
    {
        return $this->items->sum('total_cost');
    }

    public function calculateTotalSellingValue(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->selling_price ?? $item->unit_price) * $item->quantity;
        });
    }

    public function calculateTotalProfit(): float
    {
        return $this->items->sum('total_profit');
    }

    public function calculateAverageProfitMargin(): float
    {
        $itemsWithProfit = $this->items->filter(function ($item) {
            return $item->selling_price > 0;
        });

        if ($itemsWithProfit->isEmpty()) {
            return 0;
        }

        return $itemsWithProfit->avg('profit_margin');
    }

    public function updateTotals(): void
    {
        $this->total_amount = $this->calculateTotalCost();
        $this->save();
    }

    // Accessors
    public function getTotalSellingValueAttribute(): float
    {
        return $this->calculateTotalSellingValue();
    }

    public function getTotalProfitAttribute(): float
    {
        return $this->calculateTotalProfit();
    }

    public function getAverageProfitMarginAttribute(): float
    {
        return $this->calculateAverageProfitMargin();
    }

    public function getFormattedTotalSellingValueAttribute(): string
    {
        return 'Rp ' . number_format($this->total_selling_value, 0, ',', '.');
    }

    public function getFormattedTotalProfitAttribute(): string
    {
        return 'Rp ' . number_format($this->total_profit, 0, ',', '.');
    }

    public function getFormattedAverageProfitMarginAttribute(): string
    {
        return number_format($this->average_profit_margin, 2) . '%';
    }

    public function getProfitabilityStatusAttribute(): string
    {
        $margin = $this->average_profit_margin;
        
        if ($margin >= 30) {
            return 'high';
        } elseif ($margin >= 15) {
            return 'medium';
        } elseif ($margin > 0) {
            return 'low';
        } else {
            return 'none';
        }
    }

    public function getProfitabilityStatusBadgeClassAttribute(): string
    {
        return match($this->profitability_status) {
            'high' => 'bg-green-100 text-green-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'low' => 'bg-yellow-100 text-yellow-800',
            'none' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Scopes
    public function scopeWithProfit($query)
    {
        return $query->whereHas('items', function ($q) {
            $q->whereNotNull('selling_price')
              ->where('selling_price', '>', 0);
        });
    }

    public function scopeHighMargin($query, $margin = 30)
    {
        return $query->whereHas('items', function ($q) use ($margin) {
            $q->where('profit_margin', '>=', $margin);
        });
    }

    public function scopeLowMargin($query, $margin = 10)
    {
        return $query->whereHas('items', function ($q) use ($margin) {
            $q->where('profit_margin', '<=', $margin);
        });
    }
}