<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Domains\User\Models\User;

class Sale extends Model
{
    protected $fillable = [
        'cashier_id',
        'subtotal',
        'discount_total',
        'final_total',
        'payment_method',
        'payment_notes',
        'status'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'final_total' => 'decimal:2'
    ];

    // Relasi ke cashier (user)
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    // Relasi ke sale items
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // Method untuk kalkulasi subtotal dari items
    public function calculateSubtotal()
    {
        return $this->saleItems->sum(function($item) {
            return $item->qty * $item->unit_price;
        });
    }

    // Method untuk kalkulasi final total
    public function calculateFinalTotal()
    {
        return $this->subtotal - $this->discount_total;
    }

    // Scope untuk filter berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk filter berdasarkan cashier
    public function scopeByCashier($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }

    // Scope untuk filter berdasarkan tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Method untuk mendapatkan penjualan hari ini
    public static function todaySales()
    {
        return self::whereDate('created_at', today())->byStatus('PAID');
    }

    // Method untuk mendapatkan total penjualan hari ini
    public static function todayTotal()
    {
        return self::todaySales()->sum('final_total');
    }
}
