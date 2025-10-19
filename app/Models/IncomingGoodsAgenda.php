<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class IncomingGoodsAgenda extends Model
{
    use HasFactory;

    protected $table = 'incoming_goods_agenda';

    protected $fillable = [
        'supplier_id',
        'batch_number',
        'expired_date',
        'supplier_name',
        'supplier_contact',
        'goods_name',  // Fixed: Changed from 'item_name' to 'goods_name'
        'description',
        'quantity',  // Added: Required for detailed input
        'unit',  // Added: Required for detailed input
        'unit_id', // Added: Ensure unit relation can be saved
        'unit_price',  // Added: Required for detailed input
        'total_amount',
        'total_quantity',
        'quantity_unit',
        'total_purchase_amount',
        'scheduled_date',
        'payment_due_date',
        'status',
        'payment_status',
        'paid_amount',
        'remaining_amount',
        'notes',
        'contact_person',
        'phone_number',
        'is_purchase_order_generated',
        'po_number',
        'purchase_order_id',
        'received_date',
        'received_at',
        'paid_at',
        'business_modal_id',
        'source',
        'warehouse_id',
        'product_id',
        'created_by',
        'input_mode',  // Added: To track simplified vs detailed mode
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'payment_due_date' => 'date',
        'received_date' => 'date',
        'expired_date' => 'date',
        'received_at' => 'datetime',
        'paid_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_purchase_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'total_quantity' => 'decimal:2',
        'is_purchase_order_generated' => 'boolean',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function businessModal()
    {
        return $this->belongsTo(BusinessModal::class);
    }

    public function capitalTrackings()
    {
        return $this->hasMany(CapitalTracking::class, 'reference_id')
                    ->where('reference_type', 'incoming_goods_agenda');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function batchExpirations()
    {
        return $this->hasMany(BatchExpiration::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeScheduledToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopePaymentDueToday($query)
    {
        return $query->whereDate('payment_due_date', today())
                     ->where('payment_status', '!=', 'paid');
    }

    public function scopeOverduePayment($query)
    {
        return $query->where('payment_due_date', '<', today())
                     ->where('payment_status', '!=', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereBetween('expired_date', [today(), today()->addDays($days)])
                    ->where('status', '!=', 'received');
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->payment_due_date < today() && $this->payment_status !== 'paid';
    }

    public function getIsDueTodayAttribute()
    {
        return $this->payment_due_date->isToday() && $this->payment_status !== 'paid';
    }

    public function getIsExpiredAttribute()
    {
        return $this->expired_date && $this->expired_date->isPast();
    }

    public function getIsExpiringSoonAttribute($days = 30)
    {
        return $this->expired_date && $this->expired_date->between(today(), today()->addDays($days));
    }

    public function getIsSimplifiedAttribute()
    {
        if (!is_null($this->input_mode)) {
            return $this->input_mode === 'simplified';
        }
        return !empty($this->total_purchase_amount) && !empty($this->total_quantity);
    }

    public function getEffectiveSupplierNameAttribute()
    {
        return $this->supplier?->name ?? $this->supplier_name;
    }

    public function getEffectiveQuantityAttribute()
    {
        return $this->is_simplified ? $this->total_quantity : $this->quantity;
    }

    public function getEffectiveUnitAttribute()
    {
        return $this->is_simplified ? $this->quantity_unit : $this->unit;
    }

    public function getEffectiveUnitPriceAttribute()
    {
        if ($this->is_simplified && $this->total_quantity > 0) {
            return $this->total_purchase_amount / $this->total_quantity;
        }
        return $this->unit_price;
    }

    public function getEffectiveTotalAmountAttribute()
    {
        return $this->is_simplified ? $this->total_purchase_amount : $this->total_amount;
    }

    public function getEffectiveGoodsNameAttribute()
    {
        return $this->is_simplified ? 'Barang Various (Input Sederhana)' : $this->goods_name;  // Fixed: Using goods_name
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending', 'scheduled' => 'bg-yellow-100 text-yellow-800',
            'received' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'pending', 'unpaid' => 'bg-yellow-100 text-yellow-800',
            'partial' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            'overdue' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getExpirationStatusBadgeClassAttribute()
    {
        if (!$this->expired_date) {
            return 'bg-gray-100 text-gray-800';
        }
        
        if ($this->is_expired) {
            return 'bg-red-100 text-red-800';
        } elseif ($this->is_expiring_soon) {
            return 'bg-orange-100 text-orange-800';
        } else {
            return 'bg-green-100 text-green-800';
        }
    }

    // Methods
    public function markAsReceived()
    {
        $this->update([
            'status' => 'received',
            'received_date' => now(),
            'received_at' => now(),
        ]);

        // Create batch expiration record if expired_date is set
        if ($this->expired_date && $this->batch_number) {
            BatchExpiration::create([
                'incoming_goods_agenda_id' => $this->id,
                'batch_number' => $this->batch_number,
                'expired_date' => $this->expired_date,
                'quantity' => $this->effective_quantity,
                'remaining_quantity' => $this->effective_quantity,
                'created_by' => auth()->id(),
            ]);
        }

        // Increase stock in the selected warehouse if product and warehouse are specified
        if ($this->product_id && $this->warehouse_id) {
            $this->increaseWarehouseStock();
        }
    }

    /**
     * Increase stock in the specified warehouse
     */
    private function increaseWarehouseStock()
    {
        $product = $this->product;
        $warehouse = $this->warehouse;

        if ($product && $warehouse) {
            // Get or create warehouse stock record
            $warehouseStock = $product->stockForWarehouse($warehouse);
            
            // Increase stock
            $warehouseStock->adjust($this->effective_quantity);

            // Create stock movement record
            $product->stockMovements()->create([
                'type' => 'in',
                'qty' => $this->effective_quantity,
                'note' => "Penerimaan barang dari agenda: {$this->effective_supplier_name}",
                'warehouse_id' => $this->warehouse_id,
                'supplier_name' => $this->effective_supplier_name,
                'unit_cost' => $this->effective_unit_price,
                'total_cost' => $this->effective_total_amount,
                'created_by' => auth()->id(),
            ]);
        }
    }

    public function calculateRemainingAmount()
    {
        $this->remaining_amount = $this->effective_total_amount - $this->paid_amount;
        $this->save();
        
        return $this->remaining_amount;
    }

    public function updatePaymentStatus()
    {
        if ($this->remaining_amount <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->payment_status = 'partial';
        } elseif ($this->payment_due_date < today()) {
            $this->payment_status = 'overdue';
        } else {
            $this->payment_status = 'unpaid';
        }
        
        $this->save();
        
        // Sync with Purchase Order payment status if linked
        $this->syncWithPurchaseOrderPayment();
    }

    /**
     * Sync agenda payment status with linked Purchase Order
     */
    public function syncWithPurchaseOrderPayment()
    {
        if ($this->purchase_order_id && $this->purchaseOrder) {
            // Update PO paid amount based on agenda payment
            $this->purchaseOrder->paid_amount = $this->paid_amount;
            $this->purchaseOrder->updatePaymentStatus();
        }
    }

    /**
     * Make payment for this agenda
     */
    public function makePayment($amount, $notes = null)
    {
        $amount = (float) $amount;
        
        // Validate payment amount
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than 0');
        }
        
        if ($amount > $this->remaining_amount) {
            throw new \InvalidArgumentException('Payment amount cannot exceed remaining amount');
        }
        
        // Update paid amount
        $this->paid_amount += $amount;
        $this->calculateRemainingAmount();
        $this->updatePaymentStatus();
        
        // Add payment notes if provided
        if ($notes) {
            $currentNotes = $this->notes ? $this->notes . "\n" : '';
            $this->notes = $currentNotes . "Payment: Rp " . number_format($amount, 0, ',', '.') . " - " . $notes;
            $this->save();
        }
        
        return $this;
    }

    public function generatePurchaseOrderNumber()
    {
        $prefix = 'PO-' . date('Ym');
        $sequence = static::where('po_number', 'like', $prefix . '%')->count() + 1;
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function autoGeneratePurchaseOrder()
    {
        if (!$this->is_purchase_order_generated) {
            $po = PurchaseOrder::create([
                'po_number' => $this->generatePurchaseOrderNumber(),
                'supplier_name' => $this->effective_supplier_name,
                'supplier_contact' => $this->supplier_contact,
                'total_amount' => $this->effective_total_amount,
                'order_date' => now(),
                'expected_delivery_date' => $this->scheduled_date,
                'payment_due_date' => $this->payment_due_date,
                'status' => 'ordered',
                'payment_status' => 'unpaid',
                'notes' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            $this->update([
                'is_purchase_order_generated' => true,
                'po_number' => $po->po_number,
                'purchase_order_id' => $po->id,
            ]);

            return $po;
        }
        return $this->purchaseOrder;
    }

    // Profit Calculation Methods
    public function getExpectedProfitAttribute(): float
    {
        if (!$this->purchaseOrder) {
            return 0;
        }
        
        return $this->purchaseOrder->total_profit;
    }

    public function getExpectedProfitMarginAttribute(): float
    {
        if (!$this->purchaseOrder) {
            return 0;
        }
        
        return $this->purchaseOrder->average_profit_margin;
    }

    public function getExpectedSellingValueAttribute(): float
    {
        if (!$this->purchaseOrder) {
            return 0;
        }
        
        return $this->purchaseOrder->total_selling_value;
    }

    public function getFormattedExpectedProfitAttribute(): string
    {
        return 'Rp ' . number_format($this->expected_profit, 0, ',', '.');
    }

    public function getFormattedExpectedProfitMarginAttribute(): string
    {
        return number_format($this->expected_profit_margin, 2) . '%';
    }

    public function getFormattedExpectedSellingValueAttribute(): string
    {
        return 'Rp ' . number_format($this->expected_selling_value, 0, ',', '.');
    }

    public function getProfitabilityStatusAttribute(): string
    {
        $margin = $this->expected_profit_margin;
        
        if ($margin >= 30) {
            return 'high';
        } elseif ($margin >= 15) {
            return 'medium';
        } elseif ($margin > 0) {
            return 'low';
        } else {
            return 'none';
        }
    }

    public function getProfitabilityStatusBadgeClassAttribute(): string
    {
        return match($this->profitability_status) {
            'high' => 'bg-green-100 text-green-800',
            'medium' => 'bg-blue-100 text-blue-800',
            'low' => 'bg-yellow-100 text-yellow-800',
            'none' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getIsLinkedToPurchaseOrderAttribute(): bool
    {
        return !is_null($this->purchase_order_id);
    }

    public function getPurchaseOrderNumberAttribute(): ?string
    {
        return $this->purchaseOrder?->po_number;
    }

    /**
     * Get the product unit associated with this agenda
     */
    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($agenda) {
            if (empty($agenda->remaining_amount)) {
                $agenda->remaining_amount = $agenda->effective_total_amount - ($agenda->paid_amount ?? 0);
            }
            
            // Auto-generate batch number if empty
            if (empty($agenda->batch_number)) {
                $agenda->batch_number = 'BATCH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
            
            // Auto-populate supplier_name from supplier_id if not provided
            if (empty($agenda->supplier_name) && $agenda->supplier_id) {
                $supplier = Supplier::find($agenda->supplier_id);
                if ($supplier) {
                    $agenda->supplier_name = $supplier->name;
                }
            }
            
            // Handle field population based on input_mode
            if ($agenda->input_mode === 'simplified') {
                // Auto-populate goods_name for simplified input
                if (empty($agenda->goods_name)) {
                    $agenda->goods_name = 'Barang Various (Input Sederhana)';
                }
                
                // Auto-populate quantity and unit for simplified input
                if (empty($agenda->quantity) && !empty($agenda->total_quantity)) {
                    $agenda->quantity = $agenda->total_quantity;
                }
                if (empty($agenda->unit)) {
                    $agenda->unit = $agenda->quantity_unit;
                }
                if (empty($agenda->total_amount)) {
                    $agenda->total_amount = $agenda->total_purchase_amount;
                }
                // Auto-populate unit_price for simplified input
                if (empty($agenda->unit_price) && $agenda->total_quantity > 0) {
                    $agenda->unit_price = $agenda->total_purchase_amount / $agenda->total_quantity;
                }
            } else {
                // For detailed mode, ensure quantity has a default value if empty
                if (empty($agenda->quantity)) {
                    $agenda->quantity = 1;
                }
            }
        });

        static::updating(function ($agenda) {
            $agenda->remaining_amount = $agenda->effective_total_amount - $agenda->paid_amount;
        });
    }
}