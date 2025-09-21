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
}
