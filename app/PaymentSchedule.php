<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'incoming_goods_id',
        'due_date',
        'amount',
        'paid_amount',
        'status',
        'paid_date',
        'payment_method',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    // Relasi
    public function incomingGoods(): BelongsTo
    {
        return $this->belongsTo(IncomingGoods::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'paid');
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Accessors & Mutators
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->amount == 0) return 0;
        return ($this->paid_amount / $this->amount) * 100;
    }

    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->due_date, false);
    }

    // Methods
    public function addPayment(float $amount, string $method = null, string $notes = null): void
    {
        $this->increment('paid_amount', $amount);
        
        // Update status berdasarkan pembayaran
        if ($this->paid_amount >= $this->amount) {
            $this->update([
                'status' => 'paid',
                'paid_date' => now(),
                'payment_method' => $method,
                'notes' => $notes,
                'updated_by' => auth()->id()
            ]);
        } elseif ($this->paid_amount > 0) {
            $this->update([
                'status' => 'partial',
                'payment_method' => $method,
                'notes' => $notes,
                'updated_by' => auth()->id()
            ]);
        }
    }

    public function markAsOverdue(): void
    {
        if ($this->due_date < now() && $this->status !== 'paid') {
            $this->update([
                'status' => 'overdue',
                'updated_by' => auth()->id()
            ]);
        }
    }

    // Boot method untuk auto-update status
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-update status overdue
            if ($model->due_date < now() && $model->status === 'pending') {
                $model->status = 'overdue';
            }
        });
    }
}
