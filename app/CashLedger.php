<?php

namespace App;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashLedger extends Model
{
    use HasFactory;

    protected $table = 'cash_ledger';

    protected $fillable = [
        'transaction_date',
        'type',
        'category',
        'description',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'capital_tracking_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Get the capital tracking associated with this entry
     */
    public function capitalTracking(): BelongsTo
    {
        return $this->belongsTo(CapitalTracking::class);
    }

    /**
     * Get the user who created this entry
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference()
    {
        if ($this->reference_type && $this->reference_id) {
            $modelClass = 'App\\' . $this->reference_type;
            if (class_exists($modelClass)) {
                return $modelClass::find($this->reference_id);
            }
        }
        return null;
    }

    /**
     * Create cash ledger entry for sales
     */
    public static function createSalesEntry(Sale $sale, CapitalTracking $capital): self
    {
        $currentBalance = self::getCurrentBalance($capital->id);
        $newBalance = $currentBalance + $sale->total_amount;

        $entry = self::create([
            'transaction_date' => $sale->created_at->toDateString(),
            'type' => 'in',
            'category' => 'sales',
            'description' => "Penjualan #{$sale->id}",
            'amount' => $sale->total_amount,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'reference_type' => 'Sale',
            'reference_id' => $sale->id,
            'capital_tracking_id' => $capital->id,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Update capital tracking
        $capital->updateAmount($sale->total_amount, 'add');

        return $entry;
    }

    /**
     * Create cash ledger entry for purchase
     */
    public static function createPurchaseEntry(PurchaseOrder $purchaseOrder, float $amount, CapitalTracking $capital): self
    {
        $currentBalance = self::getCurrentBalance($capital->id);
        $newBalance = $currentBalance - $amount;

        $entry = self::create([
            'transaction_date' => now()->toDateString(),
            'type' => 'out',
            'category' => 'purchase',
            'description' => "Pembayaran PO #{$purchaseOrder->po_number}",
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'reference_type' => 'PurchaseOrder',
            'reference_id' => $purchaseOrder->id,
            'capital_tracking_id' => $capital->id,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Update capital tracking
        $capital->updateAmount($amount, 'subtract');

        return $entry;
    }

    /**
     * Create cash ledger entry for expense
     */
    public static function createExpenseEntry(string $description, float $amount, CapitalTracking $capital, string $notes = null): self
    {
        $currentBalance = self::getCurrentBalance($capital->id);
        $newBalance = $currentBalance - $amount;

        $entry = self::create([
            'transaction_date' => now()->toDateString(),
            'type' => 'out',
            'category' => 'expense',
            'description' => $description,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'capital_tracking_id' => $capital->id,
            'notes' => $notes,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Update capital tracking
        $capital->updateAmount($amount, 'subtract');

        return $entry;
    }

    /**
     * Get current balance for a capital
     */
    public static function getCurrentBalance(int $capitalId): float
    {
        $lastEntry = self::where('capital_tracking_id', $capitalId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastEntry ? $lastEntry->balance_after : 0;
    }

    /**
     * Get daily summary
     */
    public static function getDailySummary(string $date, int $capitalId = null)
    {
        $query = self::whereDate('transaction_date', $date);
        
        if ($capitalId) {
            $query->where('capital_tracking_id', $capitalId);
        }

        $entries = $query->get();

        return [
            'total_in' => $entries->where('type', 'in')->sum('amount'),
            'total_out' => $entries->where('type', 'out')->sum('amount'),
            'net_flow' => $entries->where('type', 'in')->sum('amount') - $entries->where('type', 'out')->sum('amount'),
            'entries_count' => $entries->count(),
        ];
    }
}
