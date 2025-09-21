<?php

namespace App;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Receivable extends Model
{
    protected $fillable = [
        'customer_name',
        'amount',
        'paid_amount',
        'status',
        'due_date',
        'paid_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function recordPayment(float $amount, ?string $notes = null): void
    {
        $this->paid_amount = min($this->amount, $this->paid_amount + $amount);
        $this->notes = $notes ?? $this->notes;
        $this->status = $this->paid_amount >= $this->amount ? 'paid' : 'partial';
        $this->paid_date = now();
        $this->updated_by = Auth::id();
        $this->save();
    }

    public function markAsOverdue(): void
    {
        if ($this->status !== 'paid' && $this->due_date->isPast()) {
            $this->update([
                'status' => 'overdue',
                'updated_by' => Auth::id(),
            ]);
        }
    }
}
