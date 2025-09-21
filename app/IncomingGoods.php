<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class IncomingGoods extends Model
{
    protected $fillable = [
        'invoice_number',
        'supplier_name',
        'supplier_contact',
        'expected_date',
        'actual_arrival_date',
        'total_cost',
        'paid_amount',
        'remaining_debt',
        'status',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'expected_date' => 'date',
        'actual_arrival_date' => 'date',
        'total_cost' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_debt' => 'decimal:2',
    ];

    // Relasi
    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
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

    public function scopeArrived($query)
    {
        return $query->where('status', 'arrived');
    }

    public function scopeOverdue($query)
    {
        return $query->whereHas('paymentSchedules', function($q) {
            $q->where('due_date', '<', now())
              ->where('status', '!=', 'paid');
        });
    }

    public function scopeExpectedToday($query)
    {
        return $query->where('expected_date', today());
    }

    public function scopeExpectedThisWeek($query)
    {
        return $query->whereBetween('expected_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Accessors & Mutators
    public function getIsOverdueAttribute(): bool
    {
        return $this->paymentSchedules()
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->exists();
    }

    public function getPaymentProgressAttribute(): float
    {
        if ($this->total_cost == 0) return 0;
        return ($this->paid_amount / $this->total_cost) * 100;
    }

    public function getDaysUntilExpectedAttribute(): int
    {
        return now()->diffInDays($this->expected_date, false);
    }

    // Methods
    public function markAsArrived(Carbon $arrivalDate = null): void
    {
        $this->update([
            'status' => 'arrived',
            'actual_arrival_date' => $arrivalDate ?? now(),
            'updated_by' => auth()->id()
        ]);
    }

    public function addPayment(float $amount, string $method = null, string $notes = null): void
    {
        $this->increment('paid_amount', $amount);
        $this->decrement('remaining_debt', $amount);
        
        // Update status berdasarkan pembayaran
        if ($this->remaining_debt <= 0) {
            $this->update(['status' => 'fully_paid']);
        } elseif ($this->paid_amount > 0) {
            $this->update(['status' => 'partial_paid']);
        }

        // Update payment schedule yang sesuai
        $pendingSchedule = $this->paymentSchedules()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        if ($pendingSchedule) {
            $pendingSchedule->addPayment($amount, $method, $notes);
        }
    }

    public function getTotalDebtAttribute(): float
    {
        return $this->paymentSchedules()->sum('amount') - $this->paymentSchedules()->sum('paid_amount');
    }
}
