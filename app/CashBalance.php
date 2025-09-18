<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CashBalance extends Model
{
    protected $fillable = [
        'date',
        'opening_balance',
        'cash_in',
        'cash_out',
        'closing_balance',
        'type',
        'description',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'date',
        'opening_balance' => 'decimal:2',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    // Relasi
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Accessors & Mutators
    public function getNetCashFlowAttribute(): float
    {
        return $this->cash_in - $this->cash_out;
    }

    // Static Methods
    public static function getCurrentBalance(): float
    {
        $latest = static::orderBy('date', 'desc')->first();
        return $latest ? $latest->closing_balance : 0;
    }

    public static function createDailyBalance(Carbon $date = null): self
    {
        $date = $date ?? today();
        
        // Cek apakah sudah ada balance untuk tanggal ini
        $existing = static::where('date', $date)->where('type', 'daily')->first();
        if ($existing) {
            return $existing;
        }

        // Ambil closing balance dari hari sebelumnya
        $previousBalance = static::where('date', '<', $date)
            ->orderBy('date', 'desc')
            ->first();

        $openingBalance = $previousBalance ? $previousBalance->closing_balance : 0;

        // Hitung cash in dari penjualan hari ini
        $cashIn = \App\Sale::whereDate('created_at', $date)->sum('total_amount');

        // Hitung cash out dari pembayaran supplier hari ini
        $cashOut = \App\PaymentSchedule::whereDate('paid_date', $date)
            ->where('status', 'paid')
            ->sum('paid_amount');

        $closingBalance = $openingBalance + $cashIn - $cashOut;

        return static::create([
            'date' => $date,
            'opening_balance' => $openingBalance,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'closing_balance' => $closingBalance,
            'type' => 'daily',
            'description' => 'Daily cash balance calculation',
            'created_by' => auth()->id() ?? 1
        ]);
    }

    public static function getTotalDebt(): float
    {
        return \App\PaymentSchedule::where('status', '!=', 'paid')->sum('amount') - 
               \App\PaymentSchedule::where('status', '!=', 'paid')->sum('paid_amount');
    }

    public static function getFinancialCondition(): array
    {
        $currentBalance = static::getCurrentBalance();
        $totalDebt = static::getTotalDebt();
        $actualCondition = $currentBalance - $totalDebt;

        return [
            'current_balance' => $currentBalance,
            'total_debt' => $totalDebt,
            'actual_condition' => $actualCondition,
            'status' => $actualCondition >= 0 ? 'healthy' : 'critical'
        ];
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-calculate closing balance jika belum diset
            if (!$model->closing_balance) {
                $model->closing_balance = $model->opening_balance + $model->cash_in - $model->cash_out;
            }
        });
    }
}
