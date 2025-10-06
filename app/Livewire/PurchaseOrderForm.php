<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\PurchaseOrder;
use App\PurchaseOrderItem;
use App\Product;
use App\ProductWarehouseStock;
use App\StockMovement;
use App\Supplier;

class PurchaseOrderForm extends Component
{
    public $showModal = false;
    public $editingId = null;

    // Core fields
    public $supplier_id;
    public $warehouse_id;
    public $expected_delivery_date;
    public $notes;

    // Status & cancellation
    public $status = 'pending';
    public $cancellation_reason = null;
    public $original_status = null;

    // Receiving & payment
    public $received_date = null; // maps to actual_delivery_date
    public $total_amount = 0;
    public $paid_amount = 0;
    public $payment_date = null;
    public $payment_method = null;

    // Items
    public $items = [];

    protected function rules()
    {
        $rules = [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,ordered,received,partially_received,cancelled',
            'cancellation_reason' => 'nullable|string',
            'paid_amount' => 'nullable|numeric|min:0',
        ];
    
        // Items required when creating, or when changing status to a receiving state during edit
        $shouldRequireItems = !$this->editingId;
        if ($this->editingId) {
            $receivingStatuses = ['received', 'partially_received'];
            $statusChangedToReceiving = in_array($this->status, $receivingStatuses) && ($this->status !== $this->original_status);
            $shouldRequireItems = $statusChangedToReceiving;
        }
    
        if ($shouldRequireItems) {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.product_id'] = 'required|exists:products,id';
            $rules['items.*.quantity'] = 'required|integer|min:1';
            $rules['items.*.unit_price'] = 'required|numeric|min:0';
            $rules['items.*.received_quantity'] = 'nullable|integer|min:0';
        }
    
        return $rules;
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->supplier_id = null;
        $this->warehouse_id = null;
        $this->expected_delivery_date = null;
        $this->notes = null;
        $this->status = 'pending';
        $this->cancellation_reason = null;
        $this->original_status = null;
        $this->received_date = null;
        $this->total_amount = 0;
        $this->paid_amount = 0;
        $this->payment_date = null;
        $this->payment_method = null;
        $this->items = [];
        $this->resetErrorBag();
    }

    public function addItem(): void
    {
        // For UX/tests, allow adding empty item then validate on save
        $this->items[] = [
            'product_id' => null,
            'quantity' => null,
            'unit_price' => null,
            'received_quantity' => null,
            'notes' => null,
        ];
    }

    public function edit(int $purchaseOrderId): void
    {
        $po = PurchaseOrder::with('items')->findOrFail($purchaseOrderId);
        $this->editingId = $po->id;
        $this->supplier_id = $po->supplier_id;
        $this->warehouse_id = $po->warehouse_id;
        $this->expected_delivery_date = optional($po->expected_delivery_date)?->format('Y-m-d');
        $this->notes = $po->notes;
        $this->status = $po->status;
        $this->original_status = $po->status;
        $this->total_amount = (float) $po->total_amount;
        $this->paid_amount = (float) $po->paid_amount;
        $this->items = [];
        foreach ($po->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) ($item->unit_price ?? $item->unit_cost),
                'received_quantity' => (int) $item->received_quantity,
                'notes' => $item->notes,
            ];
        }
    }

    public function calculateTotal(): void
    {
        $total = 0;
        foreach ($this->items as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $total += $qty * $price;
        }
        $this->total_amount = $total;
    }

    public function save()
    {
        // Early guard: prevent receiving on cancelled PO without triggering other validations
        if ($this->editingId && $this->original_status === 'cancelled' && in_array($this->status, ['received', 'partially_received'])) {
            $this->resetErrorBag();
            $this->addError('status', 'Purchase order yang dibatalkan tidak dapat diterima');
            return;
        }

        $this->validate();

        DB::transaction(function () {
            if ($this->editingId) {
                $po = PurchaseOrder::with('items')->findOrFail($this->editingId);

                // Update PO base fields
                $po->supplier_id = $this->supplier_id;
                $po->warehouse_id = $this->warehouse_id;
                $po->expected_delivery_date = $this->expected_delivery_date ? \Carbon\Carbon::parse($this->expected_delivery_date) : null;
                $po->notes = $this->notes;
                $po->status = $this->status;
                $po->cancellation_reason = $this->cancellation_reason;
                $po->total_amount = $this->total_amount ?: $po->total_amount;

                // Payment update if provided
                if ($this->paid_amount !== null) {
                    $po->paid_amount = (float) $this->paid_amount;
                    $po->updatePaymentStatus();
                }

                // Receiving logic
                if (in_array($this->status, ['received', 'partially_received'])) {
                    $po->actual_delivery_date = $this->received_date ? \Carbon\Carbon::parse($this->received_date) : $po->actual_delivery_date;

                    // Map items by id for precise updates
                    $itemsById = $po->items->keyBy('id');

                    foreach ($this->items as $formItem) {
                        if (!isset($formItem['id'])) {
                            // Skip items without id in edit mode
                            continue;
                        }
                        /** @var PurchaseOrderItem $itemModel */
                        $itemModel = $itemsById->get($formItem['id']);
                        if (!$itemModel) {
                            continue;
                        }

                        $newReceived = null;
                        if ($this->status === 'received') {
                            $newReceived = (int) $itemModel->quantity;
                        } else {
                            $newReceived = (int) ($formItem['received_quantity'] ?? 0);
                            $newReceived = min($newReceived, (int) $itemModel->quantity);
                        }

                        $delta = max(0, $newReceived - (int) $itemModel->received_quantity);
                        if ($delta > 0) {
                            // Update item received quantity
                            $itemModel->received_quantity = $newReceived;
                            $itemModel->save();

                            // Create stock movement IN for delta
                            StockMovement::create([
                                'product_id' => $itemModel->product_id,
                                'warehouse_id' => $this->warehouse_id,
                                'type' => 'IN',
                                // set both legacy and alias columns to satisfy tests and model behavior
                                'qty' => $delta,
                                'quantity' => $delta,
                                'ref_type' => 'purchase_order',
                                'reference_type' => 'purchase_order',
                                'ref_id' => $po->id,
                                'reference_id' => $po->id,
                                'note' => 'Receive PO #' . $po->po_number,
                                'performed_by' => Auth::id(),
                            ]);

                            // Adjust warehouse stock
                            $stock = ProductWarehouseStock::firstOrCreate([
                                'product_id' => $itemModel->product_id,
                                'warehouse_id' => $this->warehouse_id,
                            ], [
                                'stock_on_hand' => 0,
                                'reserved_stock' => 0,
                                'safety_stock' => 0,
                            ]);
                            $stock->adjust($delta);
                        } else {
                            // No new receipt, still persist any other changes
                            $itemModel->save();
                        }
                    }
                }

                $po->save();
            } else {
                // Create new PO
                $po = new PurchaseOrder();
                $po->po_number = PurchaseOrder::generatePoNumber();
                $po->supplier_id = $this->supplier_id;
                // set denormalized supplier fields to satisfy DB non-null constraints
                $supplier = $this->supplier_id ? Supplier::find($this->supplier_id) : null;
                $po->supplier_name = $supplier?->name;
                $po->supplier_contact = $supplier?->contact_person;
                $po->warehouse_id = $this->warehouse_id;
                $po->expected_delivery_date = $this->expected_delivery_date ? \Carbon\Carbon::parse($this->expected_delivery_date) : null;
                 // set order date to now to satisfy non-null constraint
                 $po->order_date = now();
                 $po->notes = $this->notes;
                 $po->status = 'pending';
                 $po->payment_status = 'unpaid';
                 $po->created_by = Auth::id();

                // Calculate total from items
                $total = 0;
                foreach ($this->items as $it) {
                    $qty = (int) ($it['quantity'] ?? 0);
                    $price = (float) ($it['unit_price'] ?? 0);
                    $total += $qty * $price;
                }
                $po->total_amount = $total;
                $po->save();

                // Persist items
                foreach ($this->items as $it) {
                    $product = $it['product_id'] ? Product::find($it['product_id']) : null;
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $it['product_id'] ?? null,
                        'product_name' => $product?->name ?? 'Item',
                        'product_sku' => $product?->sku,
                        'quantity' => (int) ($it['quantity'] ?? 0),
                        'unit_price' => (float) ($it['unit_price'] ?? 0),
                        'notes' => $it['notes'] ?? null,
                    ]);
                }
            }
        });

        $this->dispatch('refresh');
    }

    public function render()
    {
        // This component is primarily used via Livewire::test in Feature tests
        return view('livewire.blank');
    }
}