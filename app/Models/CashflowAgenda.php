<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashflowAgenda extends Model
{
    protected $table = 'cashflow_agenda';

    protected $fillable = [
        'date',
        'total_omset',
        'total_ecer',
        'total_grosir',
        'grosir_cash_hari_ini',
        'qr_payment_amount',
        'edc_payment_amount',
        'notes',
        'capital_tracking_id',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'total_omset' => 'decimal:2',
        'total_ecer' => 'decimal:2',
        'total_grosir' => 'decimal:2',
        'grosir_cash_hari_ini' => 'decimal:2',
        'qr_payment_amount' => 'decimal:2',
        'edc_payment_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Calculate total omset from ecer and grosir
            $model->total_omset = $model->total_ecer + $model->total_grosir;
        });
    }

    public function capitalTracking(): BelongsTo
    {
        return $this->belongsTo(CapitalTracking::class);
    }

    public function getNetCashflowAttribute(): float
    {
        $totalPayments = $this->grosir_cash_hari_ini + $this->qr_payment_amount + $this->edc_payment_amount;
        return $this->total_omset - $totalPayments;
    }

    public function getTotalPaymentsAttribute(): float
    {
        return $this->grosir_cash_hari_ini + $this->qr_payment_amount + $this->edc_payment_amount;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Profit Calculation Methods
    public function getTotalPurchaseOrderProfitAttribute(): float
    {
        return $this->purchaseOrders->sum('total_profit');
    }

    public function getAveragePurchaseOrderMarginAttribute(): float
    {
        $ordersWithProfit = $this->purchaseOrders->filter(function ($order) {
            return $order->average_profit_margin > 0;
        });

        if ($ordersWithProfit->isEmpty()) {
            return 0;
        }

        return $ordersWithProfit->avg('average_profit_margin');
    }

    public function getTotalPurchaseOrderCostAttribute(): float
    {
        return $this->purchaseOrders->sum('total_amount');
    }

    public function getTotalPurchaseOrderSellingValueAttribute(): float
    {
        return $this->purchaseOrders->sum('total_selling_value');
    }

    public function getFormattedTotalPurchaseOrderProfitAttribute(): string
    {
        return 'Rp ' . number_format($this->total_purchase_order_profit, 0, ',', '.');
    }

    public function getFormattedAveragePurchaseOrderMarginAttribute(): string
    {
        return number_format($this->average_purchase_order_margin, 2) . '%';
    }

    public function getFormattedTotalPurchaseOrderCostAttribute(): string
    {
        return 'Rp ' . number_format($this->total_purchase_order_cost, 0, ',', '.');
    }

    public function getFormattedTotalPurchaseOrderSellingValueAttribute(): string
    {
        return 'Rp ' . number_format($this->total_purchase_order_selling_value, 0, ',', '.');
    }

    public function getProfitabilityStatusAttribute(): string
    {
        $margin = $this->average_purchase_order_margin;
        
        if ($margin >= 30) {
            return 'high';
        } elseif ($margin >= 15) {
            return 'medium';
        } elseif ($margin > 0) {
            return 'low';
        } else {
            return 'none';
        }
    }

    public function getProfitabilityStatusBadgeClassAttribute(): string
    {
        return match($this->profitability_status) {
            'high' => 'bg-green-100 text-green-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'low' => 'bg-yellow-100 text-yellow-800',
            'none' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getHasPurchaseOrdersAttribute(): bool
    {
        return $this->purchaseOrders->isNotEmpty();
    }

    public function getPurchaseOrderCountAttribute(): int
    {
        return $this->purchaseOrders->count();
    }
}
