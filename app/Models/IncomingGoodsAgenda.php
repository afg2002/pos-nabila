<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class IncomingGoodsAgenda extends Model
{
    use HasFactory;

    protected $table = 'incoming_goods_agenda';

    protected $fillable = [
        'supplier_name',
        'supplier_contact',
        'item_name',
        'quantity',
        'unit_price',
        'total_amount',
        'scheduled_date',
        'payment_due_date',
        'status',
        'payment_status',
        'paid_amount',
        'remaining_amount',
        'notes',
        'received_date',
        'business_modal_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'payment_due_date' => 'date',
        'received_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    // Relationships
    public function businessModal()
    {
        return $this->belongsTo(BusinessModal::class);
    }

    public function capitalTrackings()
    {
        return $this->hasMany(CapitalTracking::class, 'reference_id')
                    ->where('reference_type', 'incoming_goods_agenda');
    }

    // Scopes
    public function scopeScheduledToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopePaymentDueToday($query)
    {
        return $query->whereDate('payment_due_date', today())
                     ->where('payment_status', '!=', 'paid');
    }

    public function scopeOverduePayment($query)
    {
        return $query->where('payment_due_date', '<', today())
                     ->where('payment_status', '!=', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->payment_due_date < today() && $this->payment_status !== 'paid';
    }

    public function getIsDueTodayAttribute()
    {
        return $this->payment_due_date->isToday() && $this->payment_status !== 'paid';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'received' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'partial' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Methods
    public function markAsReceived()
    {
        $this->update([
            'status' => 'received',
            'received_date' => now(),
        ]);
    }

    public function calculateRemainingAmount()
    {
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        $this->save();
        
        return $this->remaining_amount;
    }

    public function updatePaymentStatus()
    {
        if ($this->remaining_amount <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } elseif ($this->payment_due_date < today()) {
            $this->payment_status = 'overdue';
        } else {
            $this->payment_status = 'pending';
        }
        
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($agenda) {
            if (empty($agenda->remaining_amount)) {
                $agenda->remaining_amount = $agenda->total_amount - ($agenda->paid_amount ?? 0);
            }
        });

        static::updating(function ($agenda) {
            $agenda->remaining_amount = $agenda->total_amount - $agenda->paid_amount;
        });
    }
}