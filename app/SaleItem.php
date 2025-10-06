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
        'below_margin_flag',
        'custom_item_name',
        'custom_item_description',
        'is_custom'
    ];

    protected $casts = [
        'qty' => 'integer',
        'unit_price' => 'decimal:2',
        'margin_pct_at_sale' => 'decimal:2',
        'below_margin_flag' => 'boolean',
        'is_custom' => 'boolean'
    ];

    // Relasi ke sale
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // Relasi ke product (nullable untuk custom items)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Method untuk kalkulasi margin percentage
    public function calculateMarginPercentage(): float
    {
        // Skip calculation for custom items
        if ($this->is_custom || !$this->product) {
            return 0;
        }
        
        if ($this->product->base_cost == 0) {
            return 0;
        }
        
        return (($this->unit_price - $this->product->base_cost) / $this->product->base_cost) * 100;
    }

    // Method untuk cek apakah di bawah margin minimum
    public function isBelowMinimumMargin(): bool
    {
        // Custom items don't have margin checks
        if ($this->is_custom || !$this->product) {
            return false;
        }
        
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
        // Custom items don't need price validation
        if ($this->is_custom) {
            return true;
        }
        
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
        // Skip tier pricing for custom items
        if ($this->is_custom) {
            $this->unit_price = $customPrice ?? $this->unit_price;
            $this->price_tier = 'custom';
            $this->margin_pct_at_sale = 0;
            $this->below_margin_flag = false;
            return;
        }
        
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

    // Helper method to get item name (product name or custom name)
    public function getItemNameAttribute(): string
    {
        if ($this->is_custom) {
            return $this->custom_item_name;
        }
        
        return $this->product ? $this->product->name : 'Unknown Product';
    }

    // Helper method to get item description
    public function getItemDescriptionAttribute(): ?string
    {
        if ($this->is_custom) {
            return $this->custom_item_description;
        }
        
        return $this->product ? $this->product->description : null;
    }

    // Scope for custom items
    public function scopeCustomItems($query)
    {
        return $query->where('is_custom', true);
    }

    // Scope for regular items
    public function scopeRegularItems($query)
    {
        return $query->where('is_custom', false);
    }
}
