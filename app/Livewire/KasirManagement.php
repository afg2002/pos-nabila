<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Sale;
use App\Domains\User\Models\User;
use Carbon\Carbon;

class KasirManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $cashierId = '';
    public $paymentMethod = '';
    public $selectedSale = null;
    public $showDetailModal = false;
    public $showDeleteConfirmModal = false;
    public $confirmDeleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'cashierId' => ['except' => ''],
        'paymentMethod' => ['except' => '']
    ];

    public function mount()
    {
        // Check if user is authenticated and has permission
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasPermission('pos.access')) {
            abort(403, 'You do not have permission to access cashier management.');
        }

        // Set default date range to today
        $this->dateFrom = Carbon::today()->format('Y-m-d');
        $this->dateTo = Carbon::today()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingCashierId()
    {
        $this->resetPage();
    }

    public function updatingPaymentMethod()
    {
        $this->resetPage();
    }

    public function showDetail($saleId)
    {
        $this->selectedSale = Sale::with(['saleItems.product', 'cashier'])->find($saleId);
        $this->showDetailModal = true;
    }

    public function closeDetail()
    {
        $this->selectedSale = null;
        $this->showDetailModal = false;
    }

    public function printReceiptThermal($saleId)
    {
        $this->selectedSale = Sale::with(['saleItems.product', 'cashier'])->find($saleId);
        $this->dispatch('print-receipt-thermal');
    }

    public function exportReceiptPNG($saleId)
    {
        $this->selectedSale = Sale::with(['saleItems.product', 'cashier'])->find($saleId);
        $this->dispatch('export-receipt-png');
    }

    public function exportReceiptPDFThermal($saleId)
    {
        $this->selectedSale = Sale::with(['saleItems.product', 'cashier'])->find($saleId);
        $this->dispatch('export-receipt-pdf-thermal');
    }

    public function exportInvoiceA4($saleId)
    {
        $this->selectedSale = Sale::with(['saleItems.product', 'cashier'])->find($saleId);
        $this->dispatch('export-invoice-a4');
    }

    // Backward compatibility methods
    public function printReceipt($saleId)
    {
        $this->printReceiptThermal($saleId);
    }

    public function exportReceiptImage($saleId)
    {
        $this->exportReceiptPNG($saleId);
    }

    public function exportReceiptPDF($saleId)
    {
        $this->exportReceiptPDFThermal($saleId);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->dateFrom = Carbon::today()->format('Y-m-d');
        $this->dateTo = Carbon::today()->format('Y-m-d');
        $this->cashierId = '';
        $this->paymentMethod = '';
        $this->resetPage();
    }

    public function getSalesProperty()
    {
        $query = Sale::with(['cashier', 'saleItems'])
            ->withCount('saleItems') // Add item count for sorting
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('sale_number', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->cashierId, function ($q) {
                $q->where('cashier_id', $this->cashierId);
            })
            ->when($this->paymentMethod, function ($q) {
                $q->where('payment_method', $this->paymentMethod);
            });

        // When searching, prioritize by transaction size and amount
        if ($this->search) {
            $query->orderBy('final_total', 'desc')  // Highest amount first
                  ->orderBy('sale_items_count', 'desc'); // Most items first
        }

        // Default order by date
        $query->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function getCashiersProperty()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'kasir');
        })->get();
    }

    public function getTotalSalesProperty()
    {
        return $this->sales->sum('final_total');
    }

    public function getTotalTransactionsProperty()
    {
        return $this->sales->count();
    }

    // Delete Transaction Flow
    public function confirmDelete($saleId)
    {
        $this->confirmDeleteId = $saleId;
        $this->showDeleteConfirmModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirmModal = false;
        $this->confirmDeleteId = null;
    }

    public function deleteSale()
    {
        if (!$this->confirmDeleteId) {
            return;
        }
        $sale = Sale::find($this->confirmDeleteId);
        if (!$sale) {
            $this->dispatch('notify', type: 'error', message: 'Transaksi tidak ditemukan');
            return;
        }
        // Mark as cancelled instead of DELETED since DELETED is not in the enum
        $sale->status = 'CANCELLED';
        $sale->save();

        // Reset state and refresh list
        $this->showDeleteConfirmModal = false;
        $this->confirmDeleteId = null;
        $this->selectedSale = null;
        $this->resetPage();

        $this->dispatch('notify', type: 'success', message: 'Transaksi berhasil dihapus');
    }

    public function render()
    {
        return view('livewire.kasir-management', [
            'sales' => $this->sales,
            'cashiers' => $this->cashiers,
            'totalSales' => $this->totalSales,
            'totalTransactions' => $this->totalTransactions
        ])->layout('layouts.app');
    }
}