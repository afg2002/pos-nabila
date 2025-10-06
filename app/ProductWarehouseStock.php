<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductWarehouseStock extends Model
{
    use HasFactory;

    protected $table = 'product_warehouse_stock';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'stock_on_hand',
        'reserved_stock',
        'safety_stock',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'warehouse_id' => 'integer',
        'stock_on_hand' => 'integer',
        'reserved_stock' => 'integer',
        'safety_stock' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (ProductWarehouseStock $stock) {
            $stock->product?->refreshCurrentStock();
        });

        static::deleted(function (ProductWarehouseStock $stock) {
            $stock->product?->refreshCurrentStock();
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function adjust(int $amount): void
    {
        $this->increment('stock_on_hand', $amount);
        $this->product?->refreshCurrentStock();
    }
}
