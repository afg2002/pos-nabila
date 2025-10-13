<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingGoodsAgenda extends Model
{
    use HasFactory;

    protected $table = 'incoming_goods_agenda';

    protected $fillable = [
        'purchase_order_id',
        'source',
        'supplier_id',
        'supplier_name',
        'goods_name',
        'description',
        'quantity',
        'unit',
        'unit_id',
        'unit_price',
        'total_amount',
        'total_quantity',
        'quantity_unit',
        'total_purchase_amount',
        'scheduled_date',
        'payment_due_date',
        'status',
        'payment_status',
        'remaining_amount',
        'notes',
        'contact_person',
        'phone_number',
        'paid_amount',
        'received_at',
        'paid_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'payment_due_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_quantity' => 'decimal:2',
        'total_purchase_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the supplier associated with this agenda
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }


    /**
     * Get the product unit associated with this agenda
     */
    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    /**
     * Get the purchase order associated with this agenda
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Check if goods are scheduled for today
     */
    public function getIsScheduledTodayAttribute(): bool
    {
        return $this->scheduled_date->isToday();
    }

    /**
     * Check if payment is due today
     */
    public function getIsPaymentDueTodayAttribute(): bool
    {
        return $this->payment_due_date->isToday();
    }

    /**
     * Check if payment is overdue
     */
    public function getIsPaymentOverdueAttribute(): bool
    {
        return $this->payment_due_date->isPast() && $this->status !== 'paid';
    }

    /**
     * Get remaining amount to be paid
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if fully paid
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Mark as received
     */
    public function markAsReceived(): void
    {
        $this->status = 'received';
        $this->received_at = now();
        $this->save();
        
        // Create stock movement for simplified agendas
        if ($this->is_simplified) {
            $this->createStockMovement();
        }
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(float $amount): void
    {
        $this->paid_amount += $amount;
        
        if ($this->is_fully_paid) {
            $this->status = 'paid';
            $this->paid_at = now();
        }
        
        $this->save();
    }

    /**
     * Process payment for this agenda
     */
    public function makePayment(float $amount, string $notes = null): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than 0');
        }

        if ($amount > $this->remaining_amount) {
            throw new \InvalidArgumentException('Payment amount cannot exceed remaining amount');
        }

        // Store original paid amount for sync calculation
        $originalPaidAmount = $this->paid_amount;

        // Update paid amount
        $this->paid_amount += $amount;
        
        // Update payment status based on remaining amount
        $this->updatePaymentStatus();
        
        // Add payment notes if provided
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . 
                          now()->format('d/m/Y H:i') . ": Pembayaran Rp " . number_format($amount, 0, ',', '.') . 
                          ($notes ? " - " . $notes : '');
        }

        $this->save();

        // Sync with Purchase Order if linked - pass the payment amount
        $this->syncWithPurchaseOrderPayment($amount);
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
            $this->paid_at = now();
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } elseif ($this->payment_due_date && $this->payment_due_date->isPast()) {
            $this->payment_status = 'overdue';
        } else {
            $this->payment_status = 'pending';
        }
    }

    /**
     * Sync payment with linked Purchase Order
     */
    public function syncWithPurchaseOrderPayment(float $paymentAmount = null): void
    {
        if ($this->purchase_order_id && $this->purchaseOrder) {
            // Use provided payment amount or calculate from difference
            if ($paymentAmount === null) {
                $paymentAmount = $this->paid_amount - $this->getOriginal('paid_amount');
            }
            
            // Update PO's paid amount with the payment
            $this->purchaseOrder->paid_amount += $paymentAmount;
            $this->purchaseOrder->updatePaymentStatus();
            $this->purchaseOrder->save();
        }
    }

    /**
     * Calculate remaining amount
     */
    public function calculateRemainingAmount(): void
    {
        // Use total_purchase_amount if available, otherwise use total_amount
        $totalAmount = $this->total_purchase_amount ?? $this->total_amount;
        $this->remaining_amount = $totalAmount - $this->paid_amount;
        $this->save();
    }

    /**
     * Get the effective total amount (use total_purchase_amount if available)
     */
    public function getEffectiveTotalAmountAttribute(): float
    {
        return $this->total_purchase_amount ?? $this->total_amount ?? 0;
    }

    /**
     * Get the effective quantity (use total_quantity if available)
     */
    public function getEffectiveQuantityAttribute(): float
    {
        return $this->total_quantity ?? $this->quantity ?? 0;
    }

    /**
     * Get the effective unit (use quantity_unit if available)
     */
    public function getEffectiveUnitAttribute(): string
    {
        return $this->quantity_unit ?? $this->unit ?? '';
    }

    /**
     * Check if this is a simplified input agenda
     */
    public function getIsSimplifiedAttribute(): bool
    {
        return !is_null($this->total_purchase_amount);
    }

    /**
     * Get supplier name from relationship or fallback to supplier_name field
     */
    public function getEffectiveSupplierNameAttribute(): string
    {
        if ($this->supplier_id && $this->supplier) {
            return $this->supplier->name;
        }
        return $this->supplier_name ?? '';
    }

    /**
     * Auto-populate supplier_name from supplier relationship
     */
    public function setSupplierIdAttribute($value)
    {
        $this->attributes['supplier_id'] = $value;
        
        if ($value) {
            $supplier = Supplier::find($value);
            if ($supplier) {
                $this->attributes['supplier_name'] = $supplier->name;
                $this->attributes['contact_person'] = $supplier->contact_person;
                $this->attributes['phone_number'] = $supplier->phone;
            }
        }
    }

    /**
     * Create stock movement for simplified agenda
     */
    private function createStockMovement(): void
    {
        if (!$this->is_simplified) {
            return;
        }

        StockMovement::create([
            'product_id' => null, // No specific product for simplified input
            'type' => 'in',
            'quantity' => $this->effective_quantity,
            'unit' => $this->effective_unit,
            'reference_type' => 'incoming_goods_agenda',
            'reference_id' => $this->id,
            'description' => 'Barang masuk dari ' . $this->effective_supplier_name . ' (Input Sederhana)',
            'movement_date' => $this->received_at ?? now(),
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'bg-blue-100 text-blue-800',
            'received' => 'bg-green-100 text-green-800',
            'paid' => 'bg-emerald-100 text-emerald-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get payment status badge class for display
     */
    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'partial' => 'bg-orange-100 text-orange-800',
            'paid' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check if payment is due today
     */
    public function getIsDueTodayAttribute(): bool
    {
        return $this->payment_due_date->isToday();
    }

    /**
     * Check if payment is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->payment_due_date->isPast() && $this->payment_status !== 'paid';
    }
}
