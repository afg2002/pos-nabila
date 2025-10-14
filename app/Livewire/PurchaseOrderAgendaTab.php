<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\IncomingGoodsAgenda;
use App\Models\Supplier;
use App\Models\BatchExpiration;
use Carbon\Carbon;

class PurchaseOrderAgendaTab extends Component
{
    // Form properties for simplified input
    public $supplier_id;
    public $total_quantity;
    public $quantity_unit = 'pcs';
    public $total_purchase_amount;
    public $scheduled_date;
    public $payment_due_date;
    public $expired_date;
    public $notes;
    public $auto_generate_po = true;

    // Properties for detailed input
    public $supplier_name;
    public $goods_name;  // Fixed: Changed from 'item_name' to 'goods_name'
    public $quantity;
    public $unit_price;
    public $batch_number;

    // UI state
    public $showSimplifiedForm = true;
    public $search = '';
    public $filterStatus = 'all';
    public $selectedAgenda = null;
    public $showPaymentModal = false;
    public $paymentAmount;
    public $paymentMethod = 'cash';
    public $paymentNotes;

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'total_quantity' => 'required|numeric|min:0.01',
        'quantity_unit' => 'required|string|max:20',
        'total_purchase_amount' => 'required|numeric|min:1000',
        'scheduled_date' => 'required|date|after_or_equal:today',
        'payment_due_date' => 'required|date|after:scheduled_date',
        'expired_date' => 'nullable|date|after:scheduled_date',
        'batch_number' => 'nullable|string|max:50|unique:incoming_goods_agenda,batch_number',
        'notes' => 'nullable|string|max:500',
    ];

    protected $listeners = [
        'refreshPurchaseOrder' => '$refresh',
        'showPaymentModal' => 'openPaymentModal',
    ];

    public function mount()
    {
        $this->scheduled_date = today()->format('Y-m-d');
        $this->payment_due_date = today()->addDays(14)->format('Y-m-d');
        $this->expired_date = today()->addMonths(6)->format('Y-m-d');
    }

    public function toggleFormMode()
    {
        $this->showSimplifiedForm = !$this->showSimplifiedForm;
        $this->resetValidation();
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'supplier_id', 'total_quantity', 'quantity_unit', 'total_purchase_amount',
            'scheduled_date', 'payment_due_date', 'expired_date', 'notes', 'auto_generate_po',
            'supplier_name', 'goods_name', 'quantity', 'unit_price', 'batch_number'  // Fixed: goods_name
        ]);
        
        $this->scheduled_date = today()->format('Y-m-d');
        $this->payment_due_date = today()->addDays(14)->format('Y-m-d');
        $this->expired_date = today()->addMonths(6)->format('Y-m-d');
    }

    public function createSimplifiedAgenda()
    {
        $this->validate();

        try {
            // Get supplier information
            $supplier = Supplier::find($this->supplier_id);
            
            // Calculate unit price for simplified input
            $calculatedUnitPrice = $this->total_quantity > 0 ? $this->total_purchase_amount / $this->total_quantity : 0;
            
            $agenda = IncomingGoodsAgenda::create([
                'supplier_id' => $this->supplier_id,
                'supplier_name' => $supplier ? $supplier->name : null,
                'total_quantity' => $this->total_quantity,
                'quantity_unit' => $this->quantity_unit,
                'total_purchase_amount' => $this->total_purchase_amount,
                'scheduled_date' => $this->scheduled_date,
                'payment_due_date' => $this->payment_due_date,
                'expired_date' => $this->expired_date,
                'notes' => $this->notes,
                'status' => 'scheduled',
                'payment_status' => 'unpaid',
                'created_by' => auth()->id(),
                // Add required fields for simplified input
                'quantity' => $this->total_quantity,  // Required by database
                'unit' => $this->quantity_unit,      // Required by database
                'total_amount' => $this->total_purchase_amount,  // Required by database
                'unit_price' => $calculatedUnitPrice,  // Required by database
                'goods_name' => 'Barang Various (Input Sederhana)',  // Required by database
            ]);

            // Auto-generate batch number if expired date is set
            if ($this->expired_date) {
                $batchNumber = 'BATCH-' . date('Ymd') . '-' . uniqid();
                $agenda->update(['batch_number' => $batchNumber]);
                
                // Create batch expiration record
                BatchExpiration::create([
                    'incoming_goods_agenda_id' => $agenda->id,
                    'batch_number' => $batchNumber,
                    'expired_date' => $this->expired_date,
                    'quantity' => $this->total_quantity,
                    'remaining_quantity' => $this->total_quantity,
                    'created_by' => auth()->id(),
                ]);
            }

            // Auto-generate PO if requested
            if ($this->auto_generate_po) {
                $agenda->autoGeneratePurchaseOrder();
            }

            $this->resetForm();
            $this->dispatch('refreshPurchaseOrder');
            
            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => 'Agenda barang datang berhasil dibuat!'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Gagal membuat agenda: ' . $e->getMessage()
            ]);
        }
    }

    public function createDetailedAgenda()
    {
        $this->validate([
            'supplier_name' => 'required|string|max:255',
            'goods_name' => 'required|string|max:255',  // Fixed: goods_name
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:100',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'payment_due_date' => 'required|date|after:scheduled_date',
            'expired_date' => 'nullable|date|after:scheduled_date',
            'batch_number' => 'nullable|string|max:50|unique:incoming_goods_agenda,batch_number',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $totalAmount = $this->quantity * $this->unit_price;
            
            $agenda = IncomingGoodsAgenda::create([
                'supplier_name' => $this->supplier_name,
                'goods_name' => $this->goods_name,  // Fixed: goods_name
                'quantity' => $this->quantity,
                'unit' => 'pcs', // Default unit for detailed input
                'unit_price' => $this->unit_price,
                'total_amount' => $totalAmount,
                'scheduled_date' => $this->scheduled_date,
                'payment_due_date' => $this->payment_due_date,
                'expired_date' => $this->expired_date,
                'notes' => $this->notes,
                'status' => 'scheduled',
                'payment_status' => 'unpaid',
                'created_by' => auth()->id(),
                // Add simplified fields for compatibility
                'total_quantity' => $this->quantity,
                'quantity_unit' => 'pcs',
                'total_purchase_amount' => $totalAmount,
            ]);

            // Auto-generate batch number if expired date is set
            if ($this->expired_date) {
                $batchNumber = $this->batch_number ?: 'BATCH-' . date('Ymd') . '-' . uniqid();
                $agenda->update(['batch_number' => $batchNumber]);
                
                // Create batch expiration record
                BatchExpiration::create([
                    'incoming_goods_agenda_id' => $agenda->id,
                    'batch_number' => $batchNumber,
                    'expired_date' => $this->expired_date,
                    'quantity' => $this->quantity,
                    'remaining_quantity' => $this->quantity,
                    'created_by' => auth()->id(),
                ]);
            }

            $this->resetForm();
            $this->dispatch('refreshPurchaseOrder');
            
            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => 'Agenda barang datang berhasil dibuat!'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Gagal membuat agenda: ' . $e->getMessage()
            ]);
        }
    }

    public function markAsReceived($agendaId)
    {
        try {
            $agenda = IncomingGoodsAgenda::findOrFail($agendaId);
            $agenda->markAsReceived();
            
            $this->dispatch('refreshPurchaseOrder');
            
            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => 'Barang berhasil diterima!'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Gagal menerima barang: ' . $e->getMessage()
            ]);
        }
    }

    public function openPaymentModal($agendaId)
    {
        $this->selectedAgenda = IncomingGoodsAgenda::findOrFail($agendaId);
        $this->paymentAmount = $this->selectedAgenda->remaining_amount;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedAgenda = null;
        $this->reset(['paymentAmount', 'paymentMethod', 'paymentNotes']);
    }

    public function makePayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1000',
            'paymentMethod' => 'required|in:cash,qr,edc,transfer',
        ]);

        try {
            $this->selectedAgenda->makePayment($this->paymentAmount, $this->paymentNotes);
            
            $this->dispatch('refreshPurchaseOrder');
            $this->closePaymentModal();
            
            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => 'Pembayaran berhasil dicatat!'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Gagal mencatat pembayaran: ' . $e->getMessage()
            ]);
        }
    }

    public function generatePurchaseOrder($agendaId)
    {
        try {
            $agenda = IncomingGoodsAgenda::findOrFail($agendaId);
            $po = $agenda->autoGeneratePurchaseOrder();
            
            $this->dispatch('refreshPurchaseOrder');
            
            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => "Purchase Order {$po->po_number} berhasil dibuat!"
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Gagal membuat PO: ' . $e->getMessage()
            ]);
        }
    }

    public function getAgendasProperty()
    {
        $query = IncomingGoodsAgenda::with(['supplier', 'batchExpirations'])
            ->orderBy('scheduled_date', 'asc');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('batch_number', 'like', '%' . $this->search . '%')
                  ->orWhere('supplier_name', 'like', '%' . $this->search . '%')
                  ->orWhere('goods_name', 'like', '%' . $this->search . '%')  // Fixed: goods_name
                  ->orWhereHas('supplier', function ($subQuery) {
                      $subQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Apply status filter
        if ($this->filterStatus !== 'all') {
            if ($this->filterStatus === 'overdue') {
                $query->where('payment_due_date', '<', today())
                      ->where('payment_status', '!=', 'paid');
            } else {
                $query->where('status', $this->filterStatus);
            }
        }

        return $query->get();
    }

    public function getSuppliersProperty()
    {
        return Supplier::orderBy('name')->get();
    }

    public function getPendingAgendasProperty()
    {
        return $this->agendas->where('status', 'scheduled');
    }

    public function getReceivedAgendasProperty()
    {
        return $this->agendas->where('status', 'received');
    }

    public function getOverdueAgendasProperty()
    {
        return $this->agendas->filter(function ($agenda) {
            return $agenda->payment_due_date->isPast() && $agenda->payment_status !== 'paid';
        });
    }

    public function getExpiringBatchesProperty()
    {
        return BatchExpiration::with(['incomingGoodsAgenda.supplier'])
            ->whereBetween('expired_date', [today(), today()->addDays(30)])
            ->where('remaining_quantity', '>', 0)
            ->orderBy('expired_date')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.purchase-order-agenda-tab', [
            'agendas' => $this->agendas,
            'suppliers' => $this->suppliers,
            'pendingAgendas' => $this->pendingAgendas,
            'receivedAgendas' => $this->receivedAgendas,
            'overdueAgendas' => $this->overdueAgendas,
            'expiringBatches' => $this->expiringBatches,
        ]);
    }
}