<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use HasFactory;

    protected $table = 'invoice_payments';

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getPaymentDateFormattedAttribute(): string
    {
        return $this->payment_date->format('d/m/Y H:i');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Tunai',
            'qr' => 'QR Code',
            'edc' => 'EDC/Kartu',
            'transfer' => 'Transfer',
            default => 'Unknown',
        };
    }

    public function getPaymentMethodIconAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => '💵',
            'qr' => '📱',
            'edc' => '💳',
            'transfer' => '🏦',
            default => '❓',
        };
    }

    // Methods
    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            // Update cash ledger when payment is made
            $payment->updateCashLedger();
        });
    }

    private function updateCashLedger(): void
    {
        $invoice = $this->invoice;
        if (!$invoice) return;

        // Create cash ledger entry
        CashLedger::create([
            'date' => $this->payment_date->format('Y-m-d'),
            'type' => 'income',
            'category' => 'sales_payment',
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'description' => "Pembayaran Invoice {$invoice->invoice_number} - {$invoice->customer_name}",
            'reference_id' => $this->id,
            'reference_type' => 'invoice_payment',
            'created_by' => $this->created_by,
        ]);

        // Update cashflow agenda if exists for this date
        $this->updateCashflowAgenda();
    }

    private function updateCashflowAgenda(): void
    {
        $invoice = $this->invoice;
        if (!$invoice) return;

        $date = $this->payment_date->format('Y-m-d');
        
        // Find or create cashflow agenda for this date
        $cashflow = CashflowAgenda::firstOrCreate([
            'date' => $date,
            'capital_tracking_id' => $this->getDefaultCapitalTracking(),
        ]);

        // Update payment method totals
        switch ($this->payment_method) {
            case 'cash':
                $cashflow->increment('grosir_cash_hari_ini', $this->amount);
                break;
            case 'qr':
                $cashflow->increment('qr_payment_amount', $this->amount);
                break;
            case 'edc':
                $cashflow->increment('edc_payment_amount', $this->amount);
                break;
        }

        // Update total omset
        $cashflow->total_omset = $cashflow->total_ecer + $cashflow->total_grosir;
        $cashflow->save();
    }

    private function getDefaultCapitalTracking(): int
    {
        return CapitalTracking::where('is_active', true)->first()->id ?? 1;
    }
}