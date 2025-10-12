<?php

namespace App;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use SoftDeletes;
    
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
        'warehouse',
        'warehouse_id',
        'metadata',
        'reason_code',
        'approved_by',
        'approved_at',
        // alias-friendly names
        'quantity',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'qty' => 'integer',
        'ref_id' => 'integer',
        'warehouse_id' => 'integer',
        'expiry_date' => 'date',
        'metadata' => 'array',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    
    public function getWarehouseNameAttribute()
    {
        // Get the warehouse relationship directly to avoid the string issue
        $warehouse = $this->warehouse()->getResults();
        return $warehouse ? $warehouse->name : 'Tanpa Gudang';
    }
    
    // Override the attribute accessor to prevent the string issue
    public function getWarehouseAttribute()
    {
        // Return the warehouse relationship, not the string
        return $this->warehouse()->getResults();
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public static function createMovement($productId, $qty, $type, $options = [])
    {
        $defaultOptions = [
            'ref_type' => null,
            'ref_id' => null,
            'note' => null,
            'performed_by' => auth()->id() ?? 1, // Fallback to user ID 1 for tests
            'warehouse_id' => null,
            'warehouse' => null,
            'stock_before' => null,
            'stock_after' => null,
            'metadata' => null,
            'reason_code' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];

        $options = array_merge($defaultOptions, $options);

        if (! $options['warehouse_id']) {
            $options['warehouse_id'] = optional(Warehouse::getDefault())->id;
        }

        $warehouse = $options['warehouse_id'] ? Warehouse::find($options['warehouse_id']) : null;

        if ($warehouse) {
            $options['warehouse'] = $options['warehouse'] ?? $warehouse->code;
        }

        $movement = self::create([
            'product_id' => $productId,
            'qty' => $qty,
            'type' => $type,
            ...$options,
        ]);

        if ($movement->warehouse_id) {
            $stockRow = ProductWarehouseStock::query()->firstOrCreate(
                [
                    'product_id' => $movement->product_id,
                    'warehouse_id' => $movement->warehouse_id,
                ],
                [
                    'stock_on_hand' => 0,
                    'reserved_stock' => 0,
                    'safety_stock' => 0,
                ]
            );

            if (strtoupper($type) === 'ADJUSTMENT' && ! is_null($movement->stock_after)) {
                $stockRow->stock_on_hand = (int) $movement->stock_after;
                $stockRow->save();
            } else {
                $stockRow->increment('stock_on_hand', (int) $movement->qty);
            }
        }

        $movement->product?->refreshCurrentStock();

        return $movement;
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_by')->whereNull('approved_at');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by')->whereNotNull('approved_at');
    }

    public function approve($userId = null)
    {
        $this->update([
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);

        return $this;
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringIn($days = 30)
    {
        return $this->expiry_date && $this->expiry_date->diffInDays(now()) <= $days;
    }

    // ===== Alias accessors & mutators =====
    public function getQuantityAttribute()
    {
        return $this->qty;
    }

    public function setQuantityAttribute($value)
    {
        $this->attributes['qty'] = (int) $value;
    }

    public function getReferenceTypeAttribute()
    {
        return $this->ref_type;
    }

    public function setReferenceTypeAttribute($value)
    {
        $this->attributes['ref_type'] = $value;
    }

    public function getReferenceIdAttribute()
    {
        return $this->ref_id;
    }

    public function setReferenceIdAttribute($value)
    {
        $this->attributes['ref_id'] = (int) $value;
    }

    /**
     * Check if this is a manual stock movement
     */
    public function getIsManualAttribute()
    {
        return $this->ref_type === 'manual';
    }

    public static function booted()
    {
        static::creating(function ($model) {
            // Sync physical alias columns before insert
            $model->attributes['quantity'] = (int) ($model->qty ?? ($model->attributes['qty'] ?? 0));
            $model->attributes['reference_type'] = $model->ref_type ?? ($model->attributes['ref_type'] ?? null);
            $model->attributes['reference_id'] = isset($model->ref_id)
                ? (int) $model->ref_id
                : (isset($model->attributes['ref_id']) ? (int) $model->attributes['ref_id'] : null);
        });
    
        static::updating(function ($model) {
            // Keep alias columns in sync on updates
            $model->attributes['quantity'] = (int) ($model->qty ?? ($model->attributes['qty'] ?? 0));
            $model->attributes['reference_type'] = $model->ref_type ?? ($model->attributes['ref_type'] ?? null);
            $model->attributes['reference_id'] = isset($model->ref_id)
                ? (int) $model->ref_id
                : (isset($model->attributes['ref_id']) ? (int) $model->attributes['ref_id'] : null);
        });
    }
}
