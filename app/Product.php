<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'category',
        'unit',
        'base_cost',
        'price_retail',
        'price_grosir',
        'min_margin_pct',
        'current_stock',
        'is_active'
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'price_retail' => 'decimal:2',
        'price_grosir' => 'decimal:2',
        'min_margin_pct' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relasi ke stock movements
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // Relasi ke sale items
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // Method untuk mendapatkan stok saat ini
    public function getCurrentStock()
    {
        return $this->stockMovements()->sum('qty');
    }
    
    /**
     * Sinkronisasi current_stock dengan stock movements
     */
    public function syncStock()
    {
        $calculatedStock = $this->getCurrentStock();
        $this->update(['current_stock' => $calculatedStock]);
        return $calculatedStock;
    }
    
    /**
     * Periksa apakah current_stock sesuai dengan stock movements
     */
    public function isStockSynced()
    {
        return $this->current_stock == $this->getCurrentStock();
    }

    // Method untuk cek apakah stok menipis (kurang dari 10)
    public function isLowStock(): bool
    {
        return $this->getCurrentStock() < 10;
    }

    // Scope untuk produk aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
        });
    }
}
