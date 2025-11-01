<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductUnitScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_id',
        'to_base_qty',
        'notes',
    ];

    protected $casts = [
        'to_base_qty' => 'decimal:6',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}