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

    public function printReceipt($saleId)
    {
        $this->selectedSale = Sale::with(['saleItems.product', 'cashier'])->find($saleId);
        $this->dispatch('print-receipt');
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
            })
            ->orderBy('created_at', 'desc');

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