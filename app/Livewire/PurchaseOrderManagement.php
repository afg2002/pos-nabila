<?php

namespace App\Livewire;

use App\PurchaseOrder;
use App\PurchaseOrderItem;
use App\CapitalTracking;
use App\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderManagement extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $statusFilter = '';
    public $paymentStatusFilter = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $supplier_name;
    public $supplier_contact;
    public $capital_tracking_id;
    public $expected_date;
    public $payment_due_date;
    public $payment_schedule_date;
    public $reminder_enabled = true;
    public $notes;
    public $status = 'pending';
    public $payment_status = 'unpaid';
    
    // Items
    public $items = [];
    public $newItem = [
        'product_name' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'notes' => ''
    ];
    
    public $editingId = null;
    public $showModal = false;
    public $confirmingDelete = false;
    public $deleteId = null;
    public $viewingPO = null;
    public $showDetailModal = false;

    protected $rules = [
        'supplier_name' => 'required|string|max:255',
        'supplier_contact' => 'nullable|string|max:255',
        'capital_tracking_id' => 'required|exists:capital_tracking,id',
        'expected_date' => 'required|date|after_or_equal:today',
        'payment_due_date' => 'required|date|after_or_equal:expected_date',
        'payment_schedule_date' => 'nullable|date|after_or_equal:today',
        'reminder_enabled' => 'boolean',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,ordered,received,cancelled',
        'payment_status' => 'required|in:unpaid,partial,paid',
        'items' => 'required|array|min:1',
        'items.*.product_name' => 'required|string|max:255',
        'items.*.quantity' => 'required|numeric|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.notes' => 'nullable|string',
    ];

    protected $messages = [
        'supplier_name.required' => 'Nama supplier harus diisi',
        'capital_tracking_id.required' => 'Modal usaha harus dipilih',
        'expected_date.required' => 'Tanggal masuk harus diisi',
        'payment_due_date.required' => 'Tanggal pembayaran harus diisi',
        'payment_schedule_date.date' => 'Format tanggal agenda pembayaran tidak valid',
        'payment_schedule_date.after_or_equal' => 'Tanggal agenda pembayaran tidak boleh kurang dari hari ini',
        'items.required' => 'Minimal harus ada 1 item',
        'items.*.product_name.required' => 'Nama produk harus diisi',
        'items.*.quantity.required' => 'Quantity harus diisi',
        'items.*.unit_price.required' => 'Harga satuan harus diisi',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
        $this->resetPage();
    }

    public function render()
    {
        $purchaseOrders = PurchaseOrder::with(['capitalTracking', 'creator', 'items'])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('po_number', 'like', '%' . $this->search . '%')
                      ->orWhere('supplier_name', 'like', '%' . $this->search . '%')
                      ->orWhere('supplier_contact', 'like', '%' . $this->search . '%')
                      ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->paymentStatusFilter !== '', function ($query) {
                $query->where('payment_status', $this->paymentStatusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $capitalTrackings = CapitalTracking::where('is_active', true)->get();

        return view('livewire.purchase-order-management', [
            'purchaseOrders' => $purchaseOrders,
            'capitalTrackings' => $capitalTrackings
        ]);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->supplier_name = '';
        $this->supplier_contact = '';
        $this->capital_tracking_id = '';
        $this->expected_date = '';
        $this->payment_due_date = '';
        $this->payment_schedule_date = '';
        $this->reminder_enabled = true;
        $this->notes = '';
        $this->status = 'pending';
        $this->payment_status = 'unpaid';
        $this->items = [];
        $this->newItem = [
            'product_name' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'notes' => ''
        ];
        $this->editingId = null;
        $this->resetErrorBag();
    }

    protected function rules()
    {
        return [
            'supplier_name' => 'required|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'capital_tracking_id' => 'required|exists:capital_trackings,id',
            'expected_date' => 'required|date',
            'payment_due_date' => 'required|date|after_or_equal:expected_date',
            'payment_schedule_date' => 'nullable|date',
            'reminder_enabled' => 'boolean',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ];
    }

    public function addItem()
    {
        $this->validate([
            'newItem.product_name' => 'required|string|max:255',
            'newItem.quantity' => 'required|numeric|min:1',
            'newItem.unit_price' => 'required|numeric|min:0',
        ]);

        $this->items[] = $this->newItem;
        $this->newItem = [
            'product_name' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'notes' => ''
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->editingId) {
                $purchaseOrder = PurchaseOrder::findOrFail($this->editingId);
                $purchaseOrder->update([
                    'supplier_name' => $this->supplier_name,
                    'supplier_contact' => $this->supplier_contact,
                    'expected_date' => $this->expected_date,
                    'payment_due_date' => $this->payment_due_date,
                    'payment_schedule_date' => $this->payment_schedule_date,
                    'reminder_enabled' => $this->reminder_enabled,
                    'notes' => $this->notes,
                    'status' => $this->status,
                    'payment_status' => $this->payment_status,
                ]);

                // Update items
                $purchaseOrder->items()->delete();
                foreach ($this->items as $item) {
                    $purchaseOrder->items()->create($item);
                }

                // Create scheduled reminders if payment schedule is set
                if ($this->payment_schedule_date && $this->reminder_enabled) {
                    $purchaseOrder->createScheduledReminders();
                }

                session()->flash('message', 'Purchase Order berhasil diperbarui!');
            } else {
                $totalAmount = collect($this->items)->sum(function ($item) {
                    return $item['quantity'] * $item['unit_price'];
                });

                $purchaseOrder = PurchaseOrder::create([
                    'po_number' => PurchaseOrder::generatePoNumber(),
                    'supplier_name' => $this->supplier_name,
                    'supplier_contact' => $this->supplier_contact,
                    'capital_tracking_id' => $this->capital_tracking_id,
                    'expected_date' => $this->expected_date,
                    'payment_due_date' => $this->payment_due_date,
                    'payment_schedule_date' => $this->payment_schedule_date,
                    'reminder_enabled' => $this->reminder_enabled,
                    'total_amount' => $totalAmount,
                    'notes' => $this->notes,
                    'status' => $this->status,
                    'payment_status' => $this->payment_status,
                    'created_by' => Auth::id(),
                ]);

                // Add items
                foreach ($this->items as $item) {
                    $purchaseOrder->items()->create($item);
                }

                // Create scheduled reminders if payment schedule is set
                if ($this->payment_schedule_date && $this->reminder_enabled) {
                    $purchaseOrder->createScheduledReminders();
                }

                session()->flash('message', 'Purchase Order berhasil dibuat!');
            }
        });

        $this->closeModal();
    }

    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::with('items')->findOrFail($id);
        
        $this->editingId = $id;
        $this->supplier_name = $purchaseOrder->supplier_name;
        $this->supplier_contact = $purchaseOrder->supplier_contact;
        $this->capital_tracking_id = $purchaseOrder->capital_tracking_id;
        $this->expected_date = $purchaseOrder->expected_date->format('Y-m-d');
        $this->payment_due_date = $purchaseOrder->payment_due_date->format('Y-m-d');
        $this->payment_schedule_date = $purchaseOrder->payment_schedule_date ? $purchaseOrder->payment_schedule_date->format('Y-m-d') : null;
        $this->reminder_enabled = $purchaseOrder->reminder_enabled;
        $this->notes = $purchaseOrder->notes;
        $this->status = $purchaseOrder->status;
        $this->payment_status = $purchaseOrder->payment_status;
        
        $this->items = $purchaseOrder->items->map(function ($item) {
            return [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'notes' => $item->notes,
            ];
        })->toArray();
        
        $this->showModal = true;
    }

    public function viewDetail($id)
    {
        $this->viewingPO = PurchaseOrder::with(['capitalTracking', 'creator', 'items'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->viewingPO = null;
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            $purchaseOrder = PurchaseOrder::findOrFail($this->deleteId);
            $purchaseOrder->delete();
            session()->flash('message', 'Purchase Order berhasil dihapus!');
        }
        
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function updateStatus($id, $status)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update(['status' => $status]);
        
        session()->flash('message', "Status Purchase Order berhasil diubah menjadi {$status}!");
    }

    public function updatePaymentStatus($id, $paymentStatus)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->updatePaymentStatus($paymentStatus);
        
        session()->flash('message', "Status pembayaran berhasil diubah menjadi {$paymentStatus}!");
    }

    public function exportPurchaseOrders()
    {
        try {
            $purchaseOrders = PurchaseOrder::with(['capitalTracking', 'creator', 'items'])
                ->when($this->search, function ($query) {
                    $query->where(function($q) {
                        $q->where('po_number', 'like', '%' . $this->search . '%')
                          ->orWhere('supplier_name', 'like', '%' . $this->search . '%')
                          ->orWhere('supplier_contact', 'like', '%' . $this->search . '%')
                          ->orWhere('notes', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->statusFilter !== '', function ($query) {
                    $query->where('status', $this->statusFilter);
                })
                ->when($this->paymentStatusFilter !== '', function ($query) {
                    $query->where('payment_status', $this->paymentStatusFilter);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->get();

            $filename = 'purchase_orders_' . date('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($purchaseOrders) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fwrite($file, "\xEF\xBB\xBF");
                
                // Header
                fputcsv($file, [
                    'No PO',
                    'Supplier',
                    'Kontak Supplier',
                    'Modal Usaha',
                    'Tanggal Masuk',
                    'Tanggal Pembayaran',
                    'Total Amount',
                    'Status',
                    'Status Pembayaran',
                    'Catatan',
                    'Dibuat Oleh',
                    'Tanggal Dibuat'
                ]);

                foreach ($purchaseOrders as $po) {
                    fputcsv($file, [
                        $po->po_number,
                        $po->supplier_name,
                        $po->supplier_contact ?? '-',
                        $po->capitalTracking->name ?? '-',
                        $po->expected_date->format('Y-m-d'),
                        $po->payment_due_date->format('Y-m-d'),
                        number_format($po->total_amount, 0, ',', '.'),
                        ucfirst($po->status),
                        ucfirst($po->payment_status),
                        $po->notes ?? '-',
                        $po->creator->name ?? '-',
                        $po->created_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }
}
