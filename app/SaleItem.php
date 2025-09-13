<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'qty',
        'price_tier',
        'unit_price',
        'custom_reason',
        'margin_pct_at_sale',
        'below_margin_flag'
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'margin_pct_at_sale' => 'decimal:2',
        'below_margin_flag' => 'boolean'
    ];

    // Relasi ke sale
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // Relasi ke product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Method untuk kalkulasi margin percentage
    public function calculateMarginPercentage(): float
    {
        if ($this->product->base_cost == 0) {
            return 0;
        }
        
        return (($this->unit_price - $this->product->base_cost) / $this->product->base_cost) * 100;
    }

    // Method untuk cek apakah di bawah margin minimum
    public function isBelowMinimumMargin(): bool
    {
        return $this->calculateMarginPercentage() < $this->product->min_margin_pct;
    }

    // Method untuk kalkulasi subtotal item
    public function getSubtotalAttribute(): float
    {
        return $this->qty * $this->unit_price;
    }

    // Method untuk validasi custom price
    public function validateCustomPrice(): bool
    {
        if ($this->price_tier === 'custom' && $this->isBelowMinimumMargin()) {
            return !empty($this->custom_reason);
        }
        return true;
    }

    // Scope untuk filter berdasarkan below margin
    public function scopeBelowMargin($query)
    {
        return $query->where('below_margin_flag', true);
    }

    // Method untuk set harga berdasarkan tier
    public function setPriceByTier($tier, $customPrice = null)
    {
        switch ($tier) {
            case 'retail':
                $this->unit_price = $this->product->price_retail;
                break;
            case 'grosir':
                $this->unit_price = $this->product->price_grosir;
                break;
            case 'semi_grosir':
                // Asumsi semi grosir adalah rata-rata retail dan grosir
                $this->unit_price = ($this->product->price_retail + $this->product->price_grosir) / 2;
                break;
            case 'custom':
                $this->unit_price = $customPrice;
                break;
        }
        
        $this->price_tier = $tier;
        $this->margin_pct_at_sale = $this->calculateMarginPercentage();
        $this->below_margin_flag = $this->isBelowMinimumMargin();
    }
}
