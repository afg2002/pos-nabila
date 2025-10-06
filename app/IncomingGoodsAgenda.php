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
        'supplier_name',
        'goods_name',
        'description',
        'quantity',
        'unit',
        'unit_id',
        'unit_price',
        'total_amount',
        'scheduled_date',
        'payment_due_date',
        'status',
        'payment_status',
        'remaining_amount',
        'notes',
        'contact_person',
        'phone_number',
        'paid_amount',
        'capital_tracking_id',
        'received_at',
        'paid_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'payment_due_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'received_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the capital tracking associated with this agenda
     */
    public function capitalTracking(): BelongsTo
    {
        return $this->belongsTo(CapitalTracking::class);
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
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(float $amount, int $capitalTrackingId): void
    {
        $this->paid_amount += $amount;
        $this->capital_tracking_id = $capitalTrackingId;
        
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
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        $this->save();
    }
}
