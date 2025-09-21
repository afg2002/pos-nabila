<?php

namespace App\Livewire;

use App\CashLedger;
use App\CapitalTracking;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CashLedgerManagement extends Component
{
    use WithPagination;

    public $transaction_date;
    public $type = 'in';
    public $category;
    public $description;
    public $amount;
    public $capital_tracking_id;
    public $notes;
    
    public $editingId = null;
    public $showModal = false;
    public $confirmingDelete = false;
    public $deleteId = null;
    
    // Filters
    public $filterDate = '';
    public $filterType = '';
    public $filterCategory = '';
    public $filterCapitalTracking = '';

    protected $rules = [
        'transaction_date' => 'required|date',
        'type' => 'required|in:in,out',
        'category' => 'required|in:sales,purchase,expense,capital_injection,capital_withdrawal,other',
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'capital_tracking_id' => 'required|exists:capital_tracking,id',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'transaction_date.required' => 'Tanggal transaksi harus diisi',
        'type.required' => 'Tipe transaksi harus dipilih',
        'category.required' => 'Kategori harus diisi',
        'description.required' => 'Deskripsi harus diisi',
        'amount.required' => 'Jumlah harus diisi',
        'amount.numeric' => 'Jumlah harus berupa angka',
        'capital_tracking_id.required' => 'Modal usaha harus dipilih',
    ];

    public function mount()
    {
        $this->transaction_date = now()->format('Y-m-d');
        $this->filterDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $query = CashLedger::with(['capitalTracking', 'creator']);

        // Apply filters
        if ($this->filterDate) {
            $query->whereDate('transaction_date', $this->filterDate);
        }
        
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }
        
        if ($this->filterCategory) {
            $query->where('category', 'like', '%' . $this->filterCategory . '%');
        }
        
        if ($this->filterCapitalTracking) {
            $query->where('capital_tracking_id', $this->filterCapitalTracking);
        }

        $cashLedgers = $query->orderBy('transaction_date', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(15);

        $capitalTrackings = CapitalTracking::where('is_active', true)->get();
        
        // Get daily summary
        $dailySummary = $this->getDailySummary($this->filterDate ?: now()->format('Y-m-d'));
        
        // Calculate total income and expense for the filtered date
        $totalIncome = CashLedger::whereDate('transaction_date', $this->filterDate ?: now()->format('Y-m-d'))
                                ->where('type', 'in')
                                ->sum('amount');
        
        $totalExpense = CashLedger::whereDate('transaction_date', $this->filterDate ?: now()->format('Y-m-d'))
                                 ->where('type', 'out')
                                 ->sum('amount');
        
        // Calculate net balance
        $netBalance = $totalIncome - $totalExpense;

        return view('livewire.cash-ledger-management', [
            'cashLedgers' => $cashLedgers,
            'capitalTrackings' => $capitalTrackings,
            'dailySummary' => $dailySummary,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netBalance' => $netBalance,
            'incomeCategories' => $this->getIncomeCategories(),
            'expenseCategories' => $this->getExpenseCategories()
        ]);
    }

    public function getDailySummary($date)
    {
        $entries = CashLedger::whereDate('transaction_date', $date)->get();
        
        $totalIncome = $entries->where('type', 'in')->sum('amount');
        $totalExpense = $entries->where('type', 'out')->sum('amount');
        
        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $totalIncome - $totalExpense,
            'net_balance' => $totalIncome - $totalExpense, // Added missing net_balance key
            'transaction_count' => $entries->count(),
            'opening_balance' => $entries->first()->balance_before ?? 0,
            'closing_balance' => $entries->last()->balance_after ?? 0,
        ];
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
        $this->transaction_date = now()->format('Y-m-d');
        $this->type = 'in';
        $this->category = '';
        $this->description = '';
        $this->amount = '';
        $this->capital_tracking_id = '';
        $this->notes = '';
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $cashLedger = CashLedger::findOrFail($this->editingId);
            
            // Get previous balance (before this transaction)
            $previousEntry = CashLedger::where('capital_tracking_id', $this->capital_tracking_id)
                ->where('id', '<', $this->editingId)
                ->latest()
                ->first();
            
            $balanceBefore = $previousEntry ? $previousEntry->balance_after : 0;
            $balanceAfter = $this->type === 'in' 
                ? $balanceBefore + $this->amount 
                : $balanceBefore - $this->amount;

            $cashLedger->update([
                'transaction_date' => $this->transaction_date,
                'type' => $this->type,
                'category' => $this->category,
                'description' => $this->description,
                'amount' => $this->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'capital_tracking_id' => $this->capital_tracking_id,
                'notes' => $this->notes,
            ]);
            
            // Recalculate subsequent balances
            $this->recalculateSubsequentBalances($this->editingId, $this->capital_tracking_id);
            
            // Update capital tracking - need to recalculate total from all transactions
            $this->recalculateCapitalTracking($this->capital_tracking_id);
            
            session()->flash('message', 'Transaksi berhasil diperbarui!');
        } else {
            $currentBalance = CashLedger::getCurrentBalance($this->capital_tracking_id);
            
            // If this is the first transaction, use capital's initial amount as starting balance
            $capital = CapitalTracking::find($this->capital_tracking_id);
            $hasExistingTransactions = CashLedger::where('capital_tracking_id', $this->capital_tracking_id)->exists();
            
            if (!$hasExistingTransactions && $capital) {
                $balanceBefore = $capital->initial_amount;
            } else {
                $balanceBefore = $currentBalance;
            }
            
            $balanceAfter = $this->type === 'in' 
                ? $balanceBefore + $this->amount 
                : $balanceBefore - $this->amount;

            CashLedger::create([
                'transaction_date' => $this->transaction_date,
                'type' => $this->type,
                'category' => $this->category,
                'description' => $this->description,
                'amount' => $this->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'capital_tracking_id' => $this->capital_tracking_id,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ]);
            
            // Update capital tracking
            $capital = CapitalTracking::find($this->capital_tracking_id);
            if ($capital) {
                if ($this->type === 'in') {
                    $capital->updateAmount($this->amount, 'add');
                } else {
                    $capital->updateAmount($this->amount, 'subtract');
                }
            }
            
            session()->flash('message', 'Transaksi berhasil ditambahkan!');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        $cashLedger = CashLedger::findOrFail($id);
        
        $this->editingId = $id;
        $this->transaction_date = $cashLedger->transaction_date->format('Y-m-d');
        $this->type = $cashLedger->type;
        $this->category = $cashLedger->category;
        $this->description = $cashLedger->description;
        $this->amount = $cashLedger->amount;
        $this->capital_tracking_id = $cashLedger->capital_tracking_id;
        $this->notes = $cashLedger->notes;
        
        $this->showModal = true;
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            $cashLedger = CashLedger::findOrFail($this->deleteId);
            $capitalId = $cashLedger->capital_tracking_id;
            
            $cashLedger->delete();
            
            // Recalculate subsequent balances after deletion
            $this->recalculateSubsequentBalances($this->deleteId, $capitalId);
            
            // Update capital tracking after deletion
            $this->recalculateCapitalTracking($capitalId);
            
            session()->flash('message', 'Transaksi berhasil dihapus!');
        }
        
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }
    
    private function recalculateSubsequentBalances($fromId, $capitalId)
    {
        // Get all subsequent transactions
        $subsequentTransactions = CashLedger::where('capital_tracking_id', $capitalId)
            ->where('id', '>', $fromId)
            ->orderBy('transaction_date')
            ->orderBy('created_at')
            ->get();
            
        // Get the balance before the first subsequent transaction
        $previousEntry = CashLedger::where('capital_tracking_id', $capitalId)
            ->where('id', '<', $subsequentTransactions->first()->id ?? $fromId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($previousEntry) {
            $runningBalance = $previousEntry->balance_after;
        } else {
            // If no previous entry, start with capital's initial amount
            $capital = CapitalTracking::find($capitalId);
            $runningBalance = $capital ? $capital->initial_amount : 0;
        }
        
        // Recalculate each subsequent transaction
        foreach ($subsequentTransactions as $transaction) {
            $balanceBefore = $runningBalance;
            $balanceAfter = $transaction->type === 'in' 
                ? $balanceBefore + $transaction->amount 
                : $balanceBefore - $transaction->amount;
                
            $transaction->update([
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);
            
            $runningBalance = $balanceAfter;
        }
    }

    public function resetFilters()
    {
        $this->filterDate = now()->format('Y-m-d');
        $this->filterType = '';
        $this->filterCategory = '';
        $this->filterCapitalTracking = '';
    }

    public function getIncomeCategories()
    {
        return [
            'sales' => 'Penjualan',
            'capital_injection' => 'Modal Tambahan',
            'other' => 'Lain-lain',
        ];
    }

    public function getExpenseCategories()
    {
        return [
            'purchase' => 'Pembelian',
            'expense' => 'Pengeluaran',
            'capital_withdrawal' => 'Penarikan Modal',
            'other' => 'Lain-lain',
        ];
    }
    
    /**
     * Recalculate capital tracking based on all cash ledger transactions
     */
    private function recalculateCapitalTracking($capitalId)
    {
        $capital = CapitalTracking::find($capitalId);
        if (!$capital) {
            return;
        }
        
        // Get total income and expenses from cash ledger
        $totalIncome = CashLedger::where('capital_tracking_id', $capitalId)
            ->where('type', 'in')
            ->sum('amount');
            
        $totalExpenses = CashLedger::where('capital_tracking_id', $capitalId)
            ->where('type', 'out')
            ->sum('amount');
        
        // Calculate current amount: initial_amount + net cash flow
        $netCashFlow = $totalIncome - $totalExpenses;
        $newCurrentAmount = $capital->initial_amount + $netCashFlow;
        
        $capital->update([
            'current_amount' => $newCurrentAmount
        ]);
    }

    /**
     * Validate synchronization between cash ledger balance and capital tracking
     */
    public function validateSynchronization($capitalId)
    {
        $capital = CapitalTracking::find($capitalId);
        if (!$capital) return false;

        $cashLedgerBalance = CashLedger::getCurrentBalance($capitalId);
        $capitalCurrentAmount = $capital->current_amount;

        // They should be equal (allowing small floating point differences)
        $difference = abs($cashLedgerBalance - $capitalCurrentAmount);
        
        if ($difference > 0.01) {
            // Log the discrepancy for debugging
            \Log::warning("Synchronization issue detected", [
                'capital_id' => $capitalId,
                'cash_ledger_balance' => $cashLedgerBalance,
                'capital_current_amount' => $capitalCurrentAmount,
                'difference' => $difference
            ]);
            return false;
        }

        return true;
    }
}
