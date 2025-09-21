<?php

namespace App;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\DebtReminder;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_name',
        'supplier_contact',
        'order_date',
        'expected_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'payment_due_date',
        'payment_schedule_date',
        'reminder_enabled',
        'total_amount',
        'paid_amount',
        'status',
        'payment_status',
        'notes',
        'capital_tracking_id',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'payment_due_date' => 'date',
        'payment_schedule_date' => 'date',
        'reminder_enabled' => 'boolean',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    /**
     * Get the capital tracking associated with this PO
     */
    public function capitalTracking(): BelongsTo
    {
        return $this->belongsTo(CapitalTracking::class);
    }

    /**
     * Get the user who created this PO
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the items for this PO
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get debt reminders for this PO
     */
    public function debtReminders(): HasMany
    {
        return $this->hasMany(DebtReminder::class);
    }

    /**
     * Get remaining amount to pay
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if PO is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->payment_due_date && 
               $this->payment_due_date->isPast() && 
               $this->payment_status !== 'paid';
    }

    /**
     * Get days until due or overdue
     */
    public function getDaysUntilDueAttribute(): int
    {
        if (!$this->payment_due_date) {
            return 0;
        }
        return now()->diffInDays($this->payment_due_date, false);
    }

    /**
     * Generate PO number
     */
    public static function generatePoNumber(): string
    {
        $prefix = 'PO';
        $date = now()->format('Ymd');
        $lastPo = self::whereDate('created_at', now())->latest()->first();
        $sequence = $lastPo ? (int)substr($lastPo->po_number, -3) + 1 : 1;
        
        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }
        $this->save();
    }

    /**
     * Create automatic reminders based on payment schedule date
     */
    public function createScheduledReminders(): void
    {
        if (!$this->payment_schedule_date || !$this->reminder_enabled) {
            return;
        }

        // Clear existing scheduled reminders for this PO
        $this->debtReminders()->where('type', 'scheduled_reminder')->delete();

        $scheduleDate = $this->payment_schedule_date->copy();
        $threeDaysBefore = $scheduleDate->copy()->subDays(3);
        $oneDayAfter = $scheduleDate->copy()->addDay();

        // Create reminder 3 days before scheduled date
        if ($threeDaysBefore->isFuture()) {
            DebtReminder::create([
                'purchase_order_id' => $this->id,
                'reminder_date' => $threeDaysBefore,
                'type' => 'scheduled_reminder',
                'status' => 'pending',
                'message' => "Agenda pembayaran PO #{$this->po_number} untuk supplier {$this->supplier_name} dalam 3 hari. Jumlah: Rp " . number_format($this->remaining_amount, 0, ',', '.'),
            ]);
        }

        // Create reminder on scheduled date
        DebtReminder::create([
            'purchase_order_id' => $this->id,
            'reminder_date' => $scheduleDate,
            'type' => 'scheduled_reminder',
            'status' => 'pending',
            'message' => "Hari ini adalah jadwal pembayaran PO #{$this->po_number} untuk supplier {$this->supplier_name}. Jumlah: Rp " . number_format($this->remaining_amount, 0, ',', '.'),
        ]);

        // Create reminder 1 day after if not paid
        DebtReminder::create([
            'purchase_order_id' => $this->id,
            'reminder_date' => $oneDayAfter,
            'type' => 'scheduled_reminder',
            'status' => 'pending',
            'message' => "Agenda pembayaran PO #{$this->po_number} untuk supplier {$this->supplier_name} sudah terlewat 1 hari. Segera lakukan pembayaran. Jumlah: Rp " . number_format($this->remaining_amount, 0, ',', '.'),
        ]);
    }
}
