<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_number',
        'cashier_id',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'payment_amount',
        'discount_total',
        'final_total',
        'payment_method',
        'payment_notes',
        // Allow persisting of payment status (PAID, PARTIAL, UNPAID)
        'payment_status',
        // Optional notes column if present in schema
        'notes',
        // Payment breakdown fields may be set after creation
        'cash_amount',
        'qr_amount',
        'edc_amount',
        'change_amount',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'final_total' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'qr_amount' => 'decimal:2',
        'edc_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
