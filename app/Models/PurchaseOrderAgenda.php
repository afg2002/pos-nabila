<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderAgenda extends Model
{
    protected $table = 'purchase_order_agenda';

    protected $fillable = [
        'company_name',
        'due_date',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'payment_status',
        'notes',
        'capital_tracking_id'
    ];

    protected $casts = [
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_STATUS_UNPAID = 'unpaid';
    const PAYMENT_STATUS_PARTIAL = 'partial';
    const PAYMENT_STATUS_PAID = 'paid';

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->remaining_amount = $model->total_amount - $model->paid_amount;
            
            // Update payment status based on amounts
            if ($model->paid_amount == 0) {
                $model->payment_status = self::PAYMENT_STATUS_UNPAID;
            } elseif ($model->paid_amount >= $model->total_amount) {
                $model->payment_status = self::PAYMENT_STATUS_PAID;
            } else {
                $model->payment_status = self::PAYMENT_STATUS_PARTIAL;
            }
        });
    }

    public function capitalTracking(): BelongsTo
    {
        return $this->belongsTo(CapitalTracking::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < now() && $this->payment_status !== self::PAYMENT_STATUS_PAID;
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_amount == 0) return 0;
        return ($this->paid_amount / $this->total_amount) * 100;
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('payment_status', '!=', self::PAYMENT_STATUS_PAID);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }
}
