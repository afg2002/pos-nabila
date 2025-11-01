<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'category',
        'photo',
        'unit_id',
        'base_cost',
        'cost_price',
        'price_purchase',
        'price_retail',
        'price_semi_grosir',
        'price_grosir',
        'min_margin_pct',
        'default_price_type',
        'current_stock',
        'status',
    ];

    protected $casts = [
        'base_cost' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'price_retail' => 'decimal:2',
        'price_semi_grosir' => 'decimal:2',
        'price_grosir' => 'decimal:2',
        'min_margin_pct' => 'decimal:2',
        'default_price_type' => 'string',
        'deleted_at' => 'datetime',
    ];

    // Alias accessor: price_purchase -> cost_price
    public function getPricePurchaseAttribute()
    {
        return $this->cost_price ?? $this->base_cost;
    }

    // Alias mutator: set price_purchase writes to cost_price
    public function setPricePurchaseAttribute($value)
    {
        $this->attributes['cost_price'] = $value;
    }

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

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(ProductWarehouseStock::class);
    }

    /**
     * New: per-product unit scales (conversion factors to base unit)
     */
    public function unitScales(): HasMany
    {
        return $this->hasMany(ProductUnitScale::class);
    }

    /**
     * Helper: list all units available for this product including base unit
     * Returns array of [unit_id => ['name' => ..., 'abbr' => ..., 'to_base_qty' => ...]]
     */
    public function listAllUnits(): array
    {
        $base = $this->unit; // ProductUnit model
        $scales = $this->relationLoaded('unitScales') ? $this->unitScales : $this->unitScales()->get();

        $result = [];
        if ($base) {
            $result[$base->id] = [
                'name' => $base->name,
                'abbr' => $base->abbreviation,
                'to_base_qty' => 1,
            ];
        }
        foreach ($scales as $scale) {
            if ($scale->unit) {
                $result[$scale->unit->id] = [
                    'name' => $scale->unit->name,
                    'abbr' => $scale->unit->abbreviation,
                    'to_base_qty' => (float) $scale->to_base_qty,
                ];
            }
        }
        return $result;
    }

    /**
     * Get stock for a specific warehouse
     */
    public function getWarehouseStock($warehouseId): int
    {
        $warehouseStock = $this->warehouseStocks()
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $warehouseStock ? $warehouseStock->stock_on_hand : 0;
    }

    public function defaultWarehouseStock(): ?ProductWarehouseStock
    {
        $defaultWarehouse = Warehouse::getDefault();

        if (! $defaultWarehouse) {
            return $this->warehouseStocks()->orderByDesc('created_at')->first();
        }

        return $this->warehouseStocks()->where('warehouse_id', $defaultWarehouse->id)->first();
    }

    public function stockForWarehouse(Warehouse $warehouse): ProductWarehouseStock
    {
        return $this->warehouseStocks()->firstOrCreate(
            ['warehouse_id' => $warehouse->id],
            ['stock_on_hand' => 0, 'reserved_stock' => 0, 'safety_stock' => 0]
        );
    }

    // Relasi ke product unit
    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    // Method untuk mendapatkan stok saat ini
    public function getCurrentStock(bool $refresh = false)
    {
        if (! $refresh && $this->relationLoaded('warehouseStocks')) {
            return $this->warehouseStocks->sum('stock_on_hand');
        }

        $warehouseStockSum = $this->warehouseStocks()->sum('stock_on_hand');

        if ($warehouseStockSum !== 0) {
            return $warehouseStockSum;
        }

        return $this->stockMovements()->sum('qty');
    }

    /**
     * Sinkronisasi current_stock dengan data per gudang.
     */
    public function syncStock(): int
    {
        return $this->refreshCurrentStock();
    }

    public function refreshCurrentStock(): int
    {
        $calculatedStock = $this->getCurrentStock(true);
        $this->update(['current_stock' => $calculatedStock]);

        return $calculatedStock;
    }

    /**
     * Periksa apakah current_stock sesuai dengan total stok gudang.
     */
    public function isStockSynced(): bool
    {
        return (int) $this->current_stock === (int) $this->getCurrentStock(true);
    }

    // Method untuk cek apakah stok menipis (kurang dari 10)
    public function isLowStock(): bool
    {
        return $this->getCurrentStock() < 10;
    }

    // Scope untuk produk aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope untuk produk berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk produk yang tidak dihapus dan aktif (untuk POS)
    public function scopeAvailableForSale($query)
    {
        return $query->where('status', 'active')
            ->whereNull('deleted_at')
            ->where('current_stock', '>', 0);
    }

    // Scope untuk produk yang bisa dihapus (tidak ada transaksi)
    public function scopeDeletable($query)
    {
        return $query->whereDoesntHave('stockMovements')
            ->whereDoesntHave('saleItems');
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        });
    }

    // Method untuk mendapatkan URL foto dengan placeholder
    public function getPhotoUrl()
    {
        if ($this->photo && file_exists(storage_path('app/public/products/'.$this->photo))) {
            return asset('storage/products/'.$this->photo);
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
     * Generate an SKU based on category/name with an incremental numeric suffix.
     * Example: for category "Minuman Ringan" -> prefix "MR" -> "MR-0001".
     */
    public static function generateSku(?string $category = null, ?string $name = null, int $width = 4): string
    {
        // Derive prefix from category or name
        $source = trim((string) ($category ?: $name ?: 'PRD'));
        $words = preg_split('/[\s\-_,]+/', $source, -1, PREG_SPLIT_NO_EMPTY);
        $letters = collect($words)->map(function ($w) {
            return strtoupper(substr(Str::slug($w, ''), 0, 1));
        })->implode('');
        $prefix = $letters !== '' ? $letters : strtoupper(substr(Str::slug($source, ''), 0, 3));
        if ($prefix === '') {
            $prefix = 'PRD';
        }

        $next = static::nextSkuSequence($prefix, $width);
        $sku = sprintf('%s-%s', $prefix, str_pad((string) $next, $width, '0', STR_PAD_LEFT));

        // Guard uniqueness by retrying if collision occurs
        $attempts = 0;
        while (static::where('sku', $sku)->exists() && $attempts < 5) {
            $next++;
            $sku = sprintf('%s-%s', $prefix, str_pad((string) $next, $width, '0', STR_PAD_LEFT));
            $attempts++;
        }

        return $sku;
    }

    /**
     * Alternative: Generate short unique SKU using random bytes (hex), keeping it compact.
     * Example: for category "Obat" -> prefix "O" -> "O-3F1A9B".
     */
    public static function generateSkuShort(?string $category = null, ?string $name = null, int $length = 6): string
    {
        // Derive prefix from category or name
        $source = trim((string) ($category ?: $name ?: 'PRD'));
        $words = preg_split('/[\s\-_,]+/', $source, -1, PREG_SPLIT_NO_EMPTY);
        $letters = collect($words)->map(function ($w) {
            return strtoupper(substr(Str::slug($w, ''), 0, 1));
        })->implode('');
        $prefix = $letters !== '' ? $letters : strtoupper(substr(Str::slug($source, ''), 0, 3));
        if ($prefix === '') {
            $prefix = 'PRD';
        }

        $length = max(4, $length); // keep it at least 4 chars

        // Generate a short, random, uppercase hex suffix and ensure uniqueness
        $attempts = 0;
        do {
            $suffix = strtoupper(bin2hex(random_bytes((int) ceil($length / 2))));
            $short = substr($suffix, 0, $length);
            $sku = $prefix.'-'.$short;
            $attempts++;
        } while (static::where('sku', $sku)->exists() && $attempts < 20);

        return $sku;
    }

    protected static function nextSkuSequence(string $prefix, int $width = 4): int
    {
        $existing = static::where('sku', 'LIKE', $prefix.'-%')->pluck('sku');
        $max = 0;
        foreach ($existing as $sku) {
            $parts = explode('-', $sku);
            $num = (int) (end($parts));
            if ($num > $max) {
                $max = $num;
            }
        }
        return $max + 1;
    }

    /**
     * Accessor: unified 'price' attribute returning effective price per default_price_type
     */
    public function getPriceAttribute()
    {
        return $this->getPriceByType();
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
            'custom' => 'Custom',
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
            'custom' => 'Custom',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'discontinued' => 'Dihentikan',
            'deleted' => 'Dihapus',
        ];
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName()
    {
        $statuses = self::getStatuses();

        return $statuses[$this->status] ?? 'Tidak Diketahui';
    }

    /**
     * Check if product can be deleted (no transaction history)
     */
    public function canBeDeleted()
    {
        return ! $this->stockMovements()->exists() && ! $this->saleItems()->exists();
    }

    /**
     * Check if product is available for sale
     */
    public function isAvailableForSale()
    {
        return in_array($this->status, ['active']) &&
               $this->current_stock > 0;
    }

    /**
     * Soft delete with status update
     */
    public function softDeleteWithStatus()
    {
        $this->update(['status' => 'inactive']);

        return $this->delete();
    }

    /**
     * Restore product with active status
     */
    public function restoreWithStatus()
    {
        $this->restore();

        return $this->update(['status' => 'active']);
    }

    /**
     * Get the effective cost price for profit calculations
     * Uses cost_price if available, otherwise falls back to base_cost
     */
    public function getEffectiveCostPrice()
    {
        return $this->cost_price ?? $this->base_cost ?? 0;
    }

    /**
     * Calculate profit for a given selling price and quantity
     */
    public function calculateProfit($sellingPrice, $quantity = 1)
    {
        $costPrice = $this->getEffectiveCostPrice();
        $totalRevenue = $sellingPrice * $quantity;
        $totalCost = $costPrice * $quantity;
        
        return [
            'revenue' => $totalRevenue,
            'cost' => $totalCost,
            'profit' => $totalRevenue - $totalCost,
            'margin_percentage' => $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue) * 100 : 0
        ];
    }

    /**
     * Get profit margin percentage for a given price type
     */
    public function getProfitMarginForPriceType($priceType = null)
    {
        $sellingPrice = $this->getPriceByType($priceType);
        $costPrice = $this->getEffectiveCostPrice();
        
        if ($sellingPrice <= 0) {
            return 0;
        }
        
        return (($sellingPrice - $costPrice) / $sellingPrice) * 100;
    }


    protected static function booted()
    {
        static::creating(function (Product $product) {
            // Auto-generate SKU if missing to safeguard programmatic creates
            if (empty($product->sku)) {
                // Default to short unique generator to avoid collisions
                $product->sku = static::generateSkuShort($product->category ?? null, $product->name ?? null);
            }
        });
    }
}
