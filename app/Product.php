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
        'photo',
        'unit_id',
        'base_cost',
        'price_retail',
        'price_semi_grosir',
        'price_grosir',
        'min_margin_pct',
        'default_price_type',
        'current_stock',
        'is_active'
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'price_retail' => 'decimal:2',
        'price_semi_grosir' => 'decimal:2',
        'price_grosir' => 'decimal:2',
        'min_margin_pct' => 'decimal:2',
        'default_price_type' => 'string',
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

    // Relasi ke product unit
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
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

    // Method untuk mendapatkan URL foto dengan placeholder
    public function getPhotoUrl()
    {
        if ($this->photo && file_exists(storage_path('app/public/products/' . $this->photo))) {
            return asset('storage/products/' . $this->photo);
        }
        
        // Return local placeholder image
        return asset('storage/placeholders/no-image.svg');
    }

    /**
     * Get price by price type
     */
    public function getPriceByType($priceType = null)
    {
        $type = $priceType ?: $this->default_price_type;
        
        switch ($type) {
            case 'retail':
                return $this->price_retail;
            case 'semi_grosir':
                return $this->price_semi_grosir ?? $this->price_retail;
            case 'grosir':
                return $this->price_grosir;
            case 'custom':
                return $this->price_retail; // Default for custom, will be manually adjusted
            default:
                return $this->price_retail;
        }
    }

    /**
     * Get price type display name
     */
    public function getPriceTypeDisplayName()
    {
        $types = [
            'retail' => 'Retail',
            'semi_grosir' => 'Semi Grosir',
            'grosir' => 'Grosir',
            'custom' => 'Custom'
        ];
        
        return $types[$this->default_price_type] ?? 'Retail';
    }

    /**
     * Get available price types
     */
    public static function getPriceTypes()
    {
        return [
            'retail' => 'Retail',
            'semi_grosir' => 'Semi Grosir',
            'grosir' => 'Grosir',
            'custom' => 'Custom'
        ];
    }
}
