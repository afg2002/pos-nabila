<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchExpiration extends Model
{
    use HasFactory;

    protected $table = 'batch_expirations';

    protected $fillable = [
        'incoming_goods_agenda_id',
        'batch_number',
        'expired_date',
        'quantity',
        'remaining_quantity',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'quantity' => 'decimal:2',
        'remaining_quantity' => 'decimal:2',
    ];

    // Relationships
    public function incomingGoodsAgenda(): BelongsTo
    {
        return $this->belongsTo(IncomingGoodsAgenda::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getFormattedExpiredDateAttribute(): string
    {
        return $this->expired_date->format('d/m/Y');
    }

    public function getDaysUntilExpirationAttribute(): int
    {
        return now()->diffInDays($this->expired_date, false);
    }

    public function getExpirationStatusAttribute(): string
    {
        $daysUntil = $this->days_until_expiration;
        
        if ($daysUntil < 0) {
            return 'expired';
        } elseif ($daysUntil <= 30) {
            return 'warning';
        } elseif ($daysUntil <= 90) {
            return 'caution';
        } else {
            return 'safe';
        }
    }

    public function getExpirationStatusBadgeClassAttribute(): string
    {
        return match($this->expiration_status) {
            'expired' => 'bg-red-100 text-red-800',
            'warning' => 'bg-orange-100 text-orange-800',
            'caution' => 'bg-yellow-100 text-yellow-800',
            'safe' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getExpirationStatusLabelAttribute(): string
    {
        return match($this->expiration_status) {
            'expired' => 'Kadaluarsa',
            'warning' => 'Kadaluarsa < 30 hari',
            'caution' => 'Kadaluarsa < 90 hari',
            'safe' => 'Aman',
            default => 'Unknown',
        };
    }

    public function getUsedQuantityAttribute(): float
    {
        return $this->quantity - $this->remaining_quantity;
    }

    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->quantity, 2);
    }

    public function getFormattedRemainingQuantityAttribute(): string
    {
        return number_format($this->remaining_quantity, 2);
    }

    public function getFormattedUsedQuantityAttribute(): string
    {
        return number_format($this->used_quantity, 2);
    }

    public function getUsagePercentageAttribute(): float
    {
        if ($this->quantity == 0) return 0;
        return ($this->used_quantity / $this->quantity) * 100;
    }

    public function getRemainingPercentageAttribute(): float
    {
        if ($this->quantity == 0) return 0;
        return ($this->remaining_quantity / $this->quantity) * 100;
    }

    // Scopes
    public function scopeExpired($query)
    {
        return $query->where('expired_date', '<', today());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('expired_date', [today(), today()->addDays($days)]);
    }

    public function scopeSafe($query)
    {
        return $query->where('expired_date', '>', today()->addDays(90));
    }

    public function scopeWithRemaining($query)
    {
        return $query->where('remaining_quantity', '>', 0);
    }

    // Methods
    public function adjustQuantity(float $amount, string $type = 'reduce'): bool
    {
        if ($type === 'reduce') {
            if ($amount > $this->remaining_quantity) {
                return false; // Cannot reduce more than remaining
            }
            $this->remaining_quantity -= $amount;
        } else {
            $this->remaining_quantity += $amount;
        }

        return $this->save();
    }

    public function isExpired(): bool
    {
        return $this->expired_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expired_date->between(today(), today()->addDays($days));
    }

    public function getStockAlertLevel(): string
    {
        $remainingPercentage = $this->remaining_percentage;
        
        if ($remainingPercentage <= 10) {
            return 'critical';
        } elseif ($remainingPercentage <= 25) {
            return 'low';
        } elseif ($remainingPercentage <= 50) {
            return 'medium';
        } else {
            return 'good';
        }
    }

    public function getStockAlertBadgeClassAttribute(): string
    {
        return match($this->stock_alert_level) {
            'critical' => 'bg-red-100 text-red-800',
            'low' => 'bg-orange-100 text-orange-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'good' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            if (empty($batch->batch_number)) {
                $batch->batch_number = 'BATCH-' . date('Ymd') . '-' . uniqid();
            }
        });

        static::updated(function ($batch) {
            // Check if batch is depleted and log activity
            if ($batch->remaining_quantity <= 0 && $batch->getOriginal('remaining_quantity') > 0) {
                \Log::info("Batch {$batch->batch_number} has been depleted", [
                    'batch_id' => $batch->id,
                    'agenda_id' => $batch->incoming_goods_agenda_id,
                ]);
            }
        });
    }
}