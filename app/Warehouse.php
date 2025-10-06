<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'branch',
        'address',
        'phone',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductWarehouseStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderByDesc('is_default')->orderBy('name');
    }

    public static function getDefault(): ?self
    {
        return static::query()->where('is_default', true)->first();
    }
}
