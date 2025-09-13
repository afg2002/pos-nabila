<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\User\Models\User;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'qty',
        'type',
        'ref_type',
        'ref_id',
        'note',
        'performed_by',
        'stock_before',
        'stock_after'
    ];

    protected $casts = [
        'qty' => 'integer',
        'ref_id' => 'integer'
    ];

    // Relasi ke product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi ke user (performed_by)
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\User\Models\User::class, 'performed_by');
    }

    // Relasi ke user yang melakukan aksi
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Scope untuk filter berdasarkan type
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope untuk filter berdasarkan produk
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Method untuk membuat stock movement
    public static function createMovement($productId, $qty, $type, $refType = null, $refId = null, $note = null, $performedBy = null)
    {
        return self::create([
            'product_id' => $productId,
            'qty' => $qty,
            'type' => $type,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'note' => $note,
            'performed_by' => $performedBy ?? auth()->id()
        ]);
    }
}
