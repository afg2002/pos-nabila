<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\User\Models\User;

class CapitalTracking extends Model
{
    use HasFactory;

    protected $table = 'capital_tracking';

    protected $fillable = [
        'name',
        'initial_amount',
        'current_amount',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this capital tracking
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get purchase orders using this capital
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get cash ledger entries for this capital
     */
    public function cashLedgerEntries(): HasMany
    {
        return $this->hasMany(CashLedger::class);
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->current_amount;
    }

    /**
     * Get used amount (calculated in real-time)
     */
    public function getUsedAmountAttribute(): float
    {
        return max(0, $this->initial_amount - $this->current_amount);
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->initial_amount == 0) {
            return 0;
        }
        
        // Only calculate usage percentage if current amount is less than initial
        // If current > initial (due to additional capital), usage is 0%
        if ($this->current_amount >= $this->initial_amount) {
            return 0;
        }
        
        $usedAmount = $this->initial_amount - $this->current_amount;
        return ($usedAmount / $this->initial_amount) * 100;
    }

    /**
     * Update current amount
     */
    public function updateAmount(float $amount, string $operation = 'subtract'): void
    {
        if ($operation === 'subtract') {
            $this->current_amount -= $amount;
        } else {
            $this->current_amount += $amount;
        }
        $this->save();
    }
}
