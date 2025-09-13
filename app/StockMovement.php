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
        'stock_after',

        'metadata',
        'reason_code',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'qty' => 'integer',
        'ref_id' => 'integer',
        'expiry_date' => 'date',

        'metadata' => 'array',
        'approved_at' => 'datetime'
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

    // Relasi ke user yang approve
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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

    // Method untuk membuat stock movement dengan enhanced tracking
    public static function createMovement($productId, $qty, $type, $options = [])
    {
        $defaultOptions = [
            'ref_type' => null,
            'ref_id' => null,
            'note' => null,
            'performed_by' => auth()->id(),

            'metadata' => null,
            'reason_code' => null,
            'approved_by' => null,
            'approved_at' => null
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        return self::create([
            'product_id' => $productId,
            'qty' => $qty,
            'type' => $type,
            ...$options
        ]);
    }

    
    // Scope untuk movement yang perlu approval
    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_by')->whereNull('approved_at');
    }
    
    // Scope untuk movement yang sudah diapprove
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by')->whereNotNull('approved_at');
    }
    
    // Method untuk approve movement
    public function approve($userId = null)
    {
        $this->update([
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now()
        ]);
        
        return $this;
    }
    
    // Method untuk check apakah sudah expired
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
    
    // Method untuk check apakah akan expired dalam X hari
    public function isExpiringIn($days = 30)
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days;
    }
}
