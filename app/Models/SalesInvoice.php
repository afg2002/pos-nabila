<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use HasFactory;

    protected $table = 'sales_invoices';

    protected $fillable = [
        'sale_id',
        'invoice_number',
        'customer_name',
        'customer_phone',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'payment_status',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    // Relationships
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    // Accessors
    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->paid_amount, 0, ',', '.');
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->remaining_amount, 0, ',', '.');
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'bg-green-100 text-green-800',
            'partial' => 'bg-yellow-100 text-yellow-800',
            'unpaid' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
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

    // Methods
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym');
        $sequence = static::where('invoice_number', 'like', $prefix . '%')->count() + 1;
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function makePayment(float $amount, string $method, ?string $notes = null): InvoicePayment
    {
        // Validate payment amount
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than 0');
        }
        
        if ($amount > $this->remaining_amount) {
            throw new \InvalidArgumentException('Payment amount cannot exceed remaining amount');
        }
        
        // Create payment record
        $payment = InvoicePayment::create([
            'invoice_id' => $this->id,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_date' => now(),
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);

        // Update invoice payment status
        $this->paid_amount += $amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;
        
        if ($this->remaining_amount <= 0) {
            $this->payment_status = 'paid';
            $this->remaining_amount = 0;
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        }

        $this->save();
        
        // Update related sale payment status if needed
        $this->updateSalePaymentStatus();
        
        return $payment;
    }

    private function updateSalePaymentStatus(): void
    {
        $sale = $this->sale;
        if (!$sale) return;

        // Calculate total paid across all invoices for this sale
        $totalPaid = $sale->invoices()->sum('paid_amount');
        $totalAmount = $sale->invoices()->sum('total_amount');

        if ($totalPaid >= $totalAmount) {
            $sale->payment_status = 'paid';
        } elseif ($totalPaid > 0) {
            $sale->payment_status = 'partial';
        } else {
            $sale->payment_status = 'unpaid';
        }

        $sale->save();
    }

    public function getThermalPrintData(): array
    {
        return [
            'invoice_number' => $this->invoice_number,
            'customer_name' => $this->customer_name ?? 'Customer',
            'customer_phone' => $this->customer_phone,
            'date' => $this->created_at->format('d/m/Y H:i'),
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'remaining_amount' => $this->remaining_amount,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method_label,
            'notes' => $this->notes,
            'cashier' => $this->sale->createdBy->name ?? 'System',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = (new static)->generateInvoiceNumber();
            }
            
            // Calculate remaining amount
            $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;
        });

        static::updating(function ($invoice) {
            // Recalculate remaining amount
            $invoice->remaining_amount = max(0, $invoice->total_amount - $invoice->paid_amount);
            
            // Update payment status
            if ($invoice->remaining_amount <= 0) {
                $invoice->payment_status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->payment_status = 'partial';
            } else {
                $invoice->payment_status = 'unpaid';
            }
        });
    }
}