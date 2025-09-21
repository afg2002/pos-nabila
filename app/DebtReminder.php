<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'capital_tracking_id',
        'reminder_date',
        'due_date',
        'reminder_type',
        'type',
        'title',
        'debtor_name',
        'amount',
        'message',
        'description',
        'notes',
        'contact_info',
        'status',
        'sent_at',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    /**
     * Get the purchase order associated with this reminder
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the user who acknowledged this reminder
     */
    public function acknowledgedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Get the capital tracking associated with this reminder
     */
    public function capitalTracking(): BelongsTo
    {
        return $this->belongsTo(CapitalTracking::class);
    }

    /**
     * Check if reminder is due
     */
    public function getIsDueAttribute(): bool
    {
        return $this->reminder_date->isToday() || $this->reminder_date->isPast();
    }

    /**
     * Check if reminder is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->reminder_date->isPast() && $this->status === 'pending';
    }

    /**
     * Mark reminder as sent
     */
    public function markAsSent(): void
    {
        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();
    }

    /**
     * Mark reminder as acknowledged
     */
    public function markAsAcknowledged(int $userId): void
    {
        $this->status = 'acknowledged';
        $this->acknowledged_at = now();
        $this->acknowledged_by = $userId;
        $this->save();
    }

    /**
     * Mark reminder as dismissed
     */
    public function markAsDismissed(): void
    {
        $this->status = 'dismissed';
        $this->save();
    }

    /**
     * Create automatic reminders for a purchase order
     */
    public static function createAutomaticReminders(PurchaseOrder $purchaseOrder): void
    {
        if (!$purchaseOrder->payment_due_date) {
            return;
        }

        // Reminder 3 days before due date
        if ($purchaseOrder->payment_due_date->subDays(3)->isFuture()) {
            self::create([
                'purchase_order_id' => $purchaseOrder->id,
                'reminder_date' => $purchaseOrder->payment_due_date->subDays(3),
                'reminder_type' => 'payment_due',
                'title' => 'Pembayaran akan jatuh tempo dalam 3 hari',
                'message' => "PO #{$purchaseOrder->po_number} untuk supplier {$purchaseOrder->supplier_name} akan jatuh tempo dalam 3 hari. Sisa pembayaran: Rp " . number_format($purchaseOrder->remaining_amount, 0, ',', '.'),
                'status' => 'pending',
            ]);
        }

        // Reminder on due date
        self::create([
            'purchase_order_id' => $purchaseOrder->id,
            'reminder_date' => $purchaseOrder->payment_due_date,
            'reminder_type' => 'payment_due',
            'title' => 'Pembayaran jatuh tempo hari ini',
            'message' => "PO #{$purchaseOrder->po_number} untuk supplier {$purchaseOrder->supplier_name} jatuh tempo hari ini. Sisa pembayaran: Rp " . number_format($purchaseOrder->remaining_amount, 0, ',', '.'),
            'status' => 'pending',
        ]);

        // Overdue reminder (1 day after due date)
        self::create([
            'purchase_order_id' => $purchaseOrder->id,
            'reminder_date' => $purchaseOrder->payment_due_date->addDay(),
            'reminder_type' => 'overdue',
            'title' => 'Pembayaran terlambat',
            'message' => "PO #{$purchaseOrder->po_number} untuk supplier {$purchaseOrder->supplier_name} sudah terlambat 1 hari. Segera lakukan pembayaran. Sisa pembayaran: Rp " . number_format($purchaseOrder->remaining_amount, 0, ',', '.'),
            'status' => 'pending',
        ]);
    }
}
