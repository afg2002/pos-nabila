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
}