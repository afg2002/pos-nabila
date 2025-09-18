<?php

namespace App\Livewire;

use App\AuditLog;
use App\CashBalance;
use App\IncomingGoods;
use App\PaymentSchedule;
use App\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FinancialForms extends Component
{
    use WithFileUploads;

    public $activeForm = 'cash';
    public $showModal = false;

    public $cash_amount;
    public $cash_notes;
    public $cash_type = 'in';
    public $cash_category = 'operational';

    public $payment_schedule_id;
    public $payment_amount;
    public $payment_method = 'cash';
    public $payment_notes;

    public $receivable_customer_name;
    public $receivable_amount;
    public $receivable_due_date;
    public $receivable_notes;
    public $receivable_status = 'pending';

    public $pendingPayments = [];
    public $recentTransactions = [];
    public $receivables = [];

    // Edit mode properties
    public $editMode = false;
    public $editingId = null;
    public $editingType = null; // 'cash', 'payment', 'receivable'

    // Filter and search properties
    public $searchTerm = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    protected $cashRules = [
        'cash_amount' => 'required|numeric|min:0',
        'cash_notes' => 'nullable|string|max:500',
        'cash_type' => 'required|in:in,out',
        'cash_category' => 'required|string|max:100',
    ];

    protected $paymentRules = [
        'payment_schedule_id' => 'required|exists:payment_schedules,id',
        'payment_amount' => 'required|numeric|min:0',
        'payment_method' => 'required|in:cash,transfer,check,credit',
        'payment_notes' => 'nullable|string|max:500',
    ];

    protected $receivableRules = [
        'receivable_customer_name' => 'required|string|max:255',
        'receivable_amount' => 'required|numeric|min:0',
        'receivable_due_date' => 'required|date|after:today',
        'receivable_notes' => 'nullable|string|max:500',
        'receivable_status' => 'required|in:pending,partial,paid,overdue',
    ];

    public function mount(): void
    {
        if (! Gate::allows('financial.forms')) {
            abort(403, 'Unauthorized access to financial forms');
        }

        $this->loadData();
    }

    public function loadData(): void
    {
        // Pending payments with filters
        $pendingQuery = PaymentSchedule::with('incomingGoods')
            ->whereIn('status', ['pending', 'partial', 'overdue']);

        if ($this->searchTerm) {
            $pendingQuery->whereHas('incomingGoods', function ($query) {
                $query->where('supplier_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('notes', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->dateFrom) {
            $pendingQuery->where('due_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $pendingQuery->where('due_date', '<=', $this->dateTo);
        }

        if ($this->statusFilter) {
            $pendingQuery->where('status', $this->statusFilter);
        }

        $this->pendingPayments = $pendingQuery->orderBy('due_date')->get();

        // Recent transactions with filters
        $transactionQuery = CashBalance::with('creator');

        if ($this->searchTerm) {
            $transactionQuery->where(function ($query) {
                $query->where('notes', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('category', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->dateFrom) {
            $transactionQuery->where('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $transactionQuery->where('date', '<=', $this->dateTo);
        }

        if ($this->typeFilter) {
            $transactionQuery->where('type', $this->typeFilter);
        }

        $this->recentTransactions = $transactionQuery
            ->orderBy($this->sortBy, $this->sortDirection)
            ->limit(10)
            ->get();

        // Receivables with filters
        $receivableQuery = Receivable::query();

        if ($this->searchTerm) {
            $receivableQuery->where(function ($query) {
                $query->where('customer_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('notes', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->dateFrom) {
            $receivableQuery->where('due_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $receivableQuery->where('due_date', '<=', $this->dateTo);
        }

        if ($this->statusFilter) {
            $receivableQuery->where('status', $this->statusFilter);
        }

        $this->receivables = $receivableQuery
            ->orderBy($this->sortBy, $this->sortDirection)
            ->limit(10)
            ->get();
    }

    public function setActiveForm(string $form): void
    {
        $this->activeForm = $form;
        $this->resetForm();
    }

    public function openModal(?string $form = null): void
    {
        if ($form) {
            $this->activeForm = $form;
        }

        $this->showModal = true;
        $this->resetForm();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->cash_amount = null;
        $this->cash_notes = null;
        $this->cash_type = 'in';
        $this->cash_category = 'operational';

        $this->payment_schedule_id = null;
        $this->payment_amount = null;
        $this->payment_method = 'cash';
        $this->payment_notes = null;

        $this->receivable_customer_name = null;
        $this->receivable_amount = null;
        $this->receivable_due_date = null;
        $this->receivable_notes = null;
        $this->receivable_status = 'pending';

        // Reset edit mode
        $this->editMode = false;
        $this->editingId = null;
        $this->editingType = null;

        $this->resetErrorBag();
    }

    public function saveCashBalance(): void
    {
        $this->validate($this->cashRules);

        try {
            DB::transaction(function () {
                $this->recordCashAdjustment(
                    amount: (float) $this->cash_amount,
                    direction: $this->cash_type,
                    category: $this->cash_category,
                    notes: $this->cash_notes
                );
            });

            $this->dispatch('cash-saved', [
                'message' => 'Saldo kas berhasil diperbarui',
                'type' => 'success',
            ]);

            $this->closeModal();
            $this->loadData();
            $this->dispatch('refresh-financial-data');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('cash-error', [
                'message' => 'Gagal menyimpan saldo kas: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function savePayment(): void
    {
        $this->validate($this->paymentRules);

        try {
            DB::transaction(function () {
                $schedule = PaymentSchedule::with('incomingGoods')
                    ->lockForUpdate()
                    ->findOrFail($this->payment_schedule_id);

                $remaining = max(0, (float) $schedule->amount - (float) $schedule->paid_amount);
                if ($this->payment_amount > $remaining) {
                    throw new \InvalidArgumentException('Jumlah pembayaran melebihi sisa tagihan');
                }

                $scheduleOriginal = $schedule->toArray();

                $schedule->addPayment((float) $this->payment_amount, $this->payment_method, $this->payment_notes);
                $schedule->refresh();

                /** @var IncomingGoods $incoming */
                $incoming = IncomingGoods::lockForUpdate()->find($schedule->incoming_goods_id);
                $incomingOriginal = $incoming->toArray();

                $incoming->paid_amount = $incoming->paymentSchedules()->sum('paid_amount');
                $incoming->remaining_debt = max(0, (float) $incoming->total_cost - (float) $incoming->paid_amount);

                if ($incoming->remaining_debt <= 0) {
                    $incoming->status = 'fully_paid';
                } elseif ($incoming->paid_amount > 0) {
                    $incoming->status = 'partial_paid';
                }

                $incoming->updated_by = Auth::id();
                $incoming->save();

                $notes = sprintf(
                    'Pembayaran invoice %s (%s)',
                    $incoming->invoice_number,
                    $schedule->due_date?->format('d/m/Y') ?? '-'
                );

                $cashRecord = $this->recordCashAdjustment(
                    amount: (float) $this->payment_amount,
                    direction: 'out',
                    category: 'payment',
                    notes: trim($notes . ($this->payment_notes ? ' - ' . $this->payment_notes : ''))
                );

                AuditLog::logUpdate('payment_schedules', $schedule->id, $scheduleOriginal, $schedule->toArray());
                AuditLog::logUpdate('incoming_goods', $incoming->id, $incomingOriginal, $incoming->toArray());
                AuditLog::logUpdate('cash_balances', $cashRecord->id, $cashRecord->getOriginal(), $cashRecord->toArray());
            });

            $this->dispatch('payment-saved', [
                'message' => 'Pembayaran berhasil dicatat',
                'type' => 'success',
            ]);

            $this->closeModal();
            $this->loadData();
            $this->dispatch('refresh-financial-data');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('payment-error', [
                'message' => 'Gagal menyimpan pembayaran: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function saveReceivable(): void
    {
        $this->validate($this->receivableRules);

        try {
            DB::transaction(function () {
                $receivable = Receivable::create([
                    'customer_name' => $this->receivable_customer_name,
                    'amount' => $this->receivable_amount,
                    'paid_amount' => 0,
                    'status' => $this->receivable_status,
                    'due_date' => Carbon::parse($this->receivable_due_date),
                    'notes' => $this->receivable_notes,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                AuditLog::logCreate('receivables', $receivable->id, $receivable->toArray());
            });

            $this->dispatch('receivable-saved', [
                'message' => 'Piutang berhasil dicatat',
                'type' => 'success',
            ]);

            $this->closeModal();
            $this->loadData();
            $this->dispatch('refresh-financial-data');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('receivable-error', [
                'message' => 'Gagal menyimpan piutang: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function updatedPaymentScheduleId(): void
    {
        if ($this->payment_schedule_id) {
            $schedule = PaymentSchedule::find($this->payment_schedule_id);
            if ($schedule) {
                $this->payment_amount = max(0, (float) $schedule->amount - (float) $schedule->paid_amount);
            }
        }
    }

    public function render()
    {
        return view('livewire.financial-forms');
    }

    private function recordCashAdjustment(float $amount, string $direction, string $category, ?string $notes = null): CashBalance
    {
        $today = Carbon::today();

        $record = CashBalance::firstOrNew([
            'date' => $today,
            'type' => 'adjustment',
        ]);

        $original = $record->exists ? $record->getOriginal() : $record->toArray();

        if (! $record->exists) {
            $previousClosing = (float) CashBalance::orderByDesc('date')->value('closing_balance');

            $record->fill([
                'opening_balance' => $previousClosing,
                'cash_in' => 0,
                'cash_out' => 0,
                'closing_balance' => $previousClosing,
                'description' => null,
                'created_by' => Auth::id(),
            ]);
        }

        if ($direction === 'in') {
            $record->cash_in += $amount;
        } else {
            $record->cash_out += $amount;
        }

        $tag = strtoupper($direction) === 'IN' ? 'Masuk' : 'Keluar';
        $prefix = sprintf('[%s|%s] ', ucfirst($category), $tag);
        $record->description = trim(($record->description ? $record->description . PHP_EOL : '') . $prefix . ($notes ?: 'Penyesuaian kas'));

        $record->closing_balance = $record->opening_balance + $record->cash_in - $record->cash_out;
        $record->updated_by = Auth::id();
        $record->date = $today;
        $record->save();

        if ($record->wasRecentlyCreated) {
            AuditLog::logCreate('cash_balances', $record->id, $record->toArray());
        } else {
            AuditLog::logUpdate('cash_balances', $record->id, $original, $record->toArray());
        }

        return $record;
    }

    // CRUD Methods for Update and Delete

    public function editReceivable($id): void
    {
        $receivable = Receivable::findOrFail($id);
        
        $this->editMode = true;
        $this->editingId = $id;
        $this->editingType = 'receivable';
        $this->activeForm = 'receivable';
        
        $this->receivable_customer_name = $receivable->customer_name;
        $this->receivable_amount = $receivable->amount;
        $this->receivable_due_date = $receivable->due_date->format('Y-m-d');
        $this->receivable_notes = $receivable->notes;
        $this->receivable_status = $receivable->status;
        
        $this->showModal = true;
    }

    public function updateReceivable(): void
    {
        $this->validate($this->receivableRules);

        try {
            DB::transaction(function () {
                $receivable = Receivable::findOrFail($this->editingId);
                $original = $receivable->toArray();

                $receivable->update([
                    'customer_name' => $this->receivable_customer_name,
                    'amount' => $this->receivable_amount,
                    'due_date' => Carbon::parse($this->receivable_due_date),
                    'notes' => $this->receivable_notes,
                    'status' => $this->receivable_status,
                    'updated_by' => Auth::id(),
                ]);

                AuditLog::logUpdate('receivables', $receivable->id, $original, $receivable->toArray());
            });

            $this->dispatch('receivable-updated', [
                'message' => 'Piutang berhasil diperbarui',
                'type' => 'success',
            ]);

            $this->closeModal();
            $this->loadData();
            $this->dispatch('refresh-financial-data');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('receivable-error', [
                'message' => 'Gagal memperbarui piutang: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function deleteReceivable($id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $receivable = Receivable::findOrFail($id);
                
                // Check if receivable has payments
                if ($receivable->paid_amount > 0) {
                    throw new \InvalidArgumentException('Tidak dapat menghapus piutang yang sudah ada pembayaran');
                }

                $original = $receivable->toArray();
                $receivable->delete();

                AuditLog::logDelete('receivables', $id, $original);
            });

            $this->dispatch('receivable-deleted', [
                'message' => 'Piutang berhasil dihapus',
                'type' => 'success',
            ]);

            $this->loadData();
            $this->dispatch('refresh-financial-data');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('receivable-error', [
                'message' => 'Gagal menghapus piutang: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function editCashBalance($id): void
    {
        $cashBalance = CashBalance::findOrFail($id);
        
        $this->editMode = true;
        $this->editingId = $id;
        $this->editingType = 'cash';
        $this->activeForm = 'cash';
        
        // Parse the description to extract amount and type
        $description = $cashBalance->description;
        if (preg_match('/\[(.*?)\|(.*?)\]/', $description, $matches)) {
            $this->cash_category = strtolower($matches[1]);
            $this->cash_type = strtolower($matches[2]) === 'masuk' ? 'in' : 'out';
        }
        
        // Calculate the transaction amount from cash_in or cash_out
        $this->cash_amount = $this->cash_type === 'in' ? $cashBalance->cash_in : $cashBalance->cash_out;
        $this->cash_notes = preg_replace('/\[.*?\]\s*/', '', $description);
        
        $this->showModal = true;
    }

    public function updateCashBalance(): void
    {
        $this->validate($this->cashRules);

        try {
            DB::transaction(function () {
                $cashBalance = CashBalance::findOrFail($this->editingId);
                $original = $cashBalance->toArray();

                // Calculate the difference for balance adjustment
                $oldAmount = $this->cash_type === 'in' ? $cashBalance->cash_in : $cashBalance->cash_out;
                $difference = (float) $this->cash_amount - $oldAmount;

                if ($this->cash_type === 'in') {
                    $cashBalance->cash_in = $this->cash_amount;
                } else {
                    $cashBalance->cash_out = $this->cash_amount;
                }

                $tag = strtoupper($this->cash_type) === 'IN' ? 'Masuk' : 'Keluar';
                $prefix = sprintf('[%s|%s] ', ucfirst($this->cash_category), $tag);
                $cashBalance->description = $prefix . ($this->cash_notes ?: 'Penyesuaian kas');

                // Recalculate closing balance
                $cashBalance->closing_balance = $cashBalance->opening_balance + $cashBalance->cash_in - $cashBalance->cash_out;
                $cashBalance->updated_by = Auth::id();
                $cashBalance->save();

                AuditLog::logUpdate('cash_balances', $cashBalance->id, $original, $cashBalance->toArray());
            });

            $this->dispatch('cash-updated', [
                'message' => 'Saldo kas berhasil diperbarui',
                'type' => 'success',
            ]);

            $this->closeModal();
            $this->loadData();
            $this->dispatch('refresh-financial-data');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('cash-error', [
                'message' => 'Gagal memperbarui saldo kas: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function deleteCashBalance($id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $cashBalance = CashBalance::findOrFail($id);
                $original = $cashBalance->toArray();
                
                // Check if this is the only record for the date
                $sameDate = CashBalance::where('date', $cashBalance->date)
                    ->where('id', '!=', $id)
                    ->exists();
                
                if (!$sameDate) {
                    throw new \InvalidArgumentException('Tidak dapat menghapus satu-satunya catatan kas untuk tanggal ini');
                }

                $cashBalance->delete();

                AuditLog::logDelete('cash_balances', $id, $original);
            });

            $this->dispatch('cash-deleted', [
                'message' => 'Catatan kas berhasil dihapus',
                'type' => 'success',
            ]);

            $this->loadData();
            $this->dispatch('refresh-financial-data');
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->dispatch('cash-error', [
                'message' => 'Gagal menghapus catatan kas: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    // Filter and Search Methods
    public function updatedSearchTerm(): void
    {
        $this->loadData();
    }

    public function updatedDateFrom(): void
    {
        $this->loadData();
    }

    public function updatedDateTo(): void
    {
        $this->loadData();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadData();
    }

    public function updatedTypeFilter(): void
    {
        $this->loadData();
    }

    public function resetFilters(): void
    {
        $this->searchTerm = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->loadData();
    }

    public function sortBy($field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->loadData();
    }

    // Export Methods
    public function exportCashTransactions(): void
    {
        try {
            $transactions = CashBalance::with('creator')
                ->when($this->searchTerm, function ($query) {
                    $query->where(function ($q) {
                        $q->where('notes', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('category', 'like', '%' . $this->searchTerm . '%');
                    });
                })
                ->when($this->dateFrom, fn($query) => $query->where('date', '>=', $this->dateFrom))
                ->when($this->dateTo, fn($query) => $query->where('date', '<=', $this->dateTo))
                ->when($this->typeFilter, fn($query) => $query->where('type', $this->typeFilter))
                ->orderBy($this->sortBy, $this->sortDirection)
                ->get();

            $filename = 'cash_transactions_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($transactions) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fwrite($file, "\xEF\xBB\xBF");
                
                // Header
                fputcsv($file, [
                    'Tanggal',
                    'Tipe',
                    'Kategori', 
                    'Kas Masuk',
                    'Kas Keluar',
                    'Saldo Akhir',
                    'Keterangan',
                    'Dibuat Oleh',
                    'Dibuat Pada'
                ]);

                // Data
                foreach ($transactions as $transaction) {
                    fputcsv($file, [
                        $transaction->date,
                        $transaction->type === 'in' ? 'Masuk' : 'Keluar',
                        ucfirst($transaction->category),
                        $transaction->cash_in ? number_format($transaction->cash_in, 0, ',', '.') : '',
                        $transaction->cash_out ? number_format($transaction->cash_out, 0, ',', '.') : '',
                        number_format($transaction->closing_balance, 0, ',', '.'),
                        $transaction->description,
                        $transaction->creator->name ?? '',
                        $transaction->created_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Throwable $e) {
            $this->dispatch('export-error', [
                'message' => 'Gagal mengekspor data: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    public function exportReceivables(): void
    {
        try {
            $receivables = Receivable::query()
                ->when($this->searchTerm, function ($query) {
                    $query->where(function ($q) {
                        $q->where('customer_name', 'like', '%' . $this->searchTerm . '%')
                          ->orWhere('notes', 'like', '%' . $this->searchTerm . '%');
                    });
                })
                ->when($this->dateFrom, fn($query) => $query->where('due_date', '>=', $this->dateFrom))
                ->when($this->dateTo, fn($query) => $query->where('due_date', '<=', $this->dateTo))
                ->when($this->statusFilter, fn($query) => $query->where('status', $this->statusFilter))
                ->orderBy($this->sortBy, $this->sortDirection)
                ->get();

            $filename = 'receivables_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($receivables) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fwrite($file, "\xEF\xBB\xBF");
                
                // Header
                fputcsv($file, [
                    'Tanggal',
                    'Pelanggan',
                    'Jumlah',
                    'Jatuh Tempo',
                    'Status',
                    'Keterangan',
                    'Dibuat Pada'
                ]);

                // Data
                foreach ($receivables as $receivable) {
                    fputcsv($file, [
                        $receivable->created_at->format('Y-m-d'),
                        $receivable->customer_name,
                        number_format($receivable->amount, 0, ',', '.'),
                        $receivable->due_date,
                        ucfirst($receivable->status),
                        $receivable->notes,
                        $receivable->created_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Throwable $e) {
            $this->dispatch('export-error', [
                'message' => 'Gagal mengekspor data: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }
}
