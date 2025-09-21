<?php

namespace App\Livewire;

use App\DebtReminder;
use App\CapitalTracking;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class DebtReminderManagement extends Component
{
    use WithPagination;

    // Form properties
    public $debtor_name = '';
    public $amount = '';
    public $description = '';
    public $due_date = '';
    public $capital_tracking_id = '';
    public $notes = '';
    public $contact_info = '';

    // Modal states
    public $showModal = false;
    public $confirmingDelete = false;
    public $editingId = null;
    public $deleteId = null;

    // Filters
    public $filterStatus = '';
    public $filterDueDate = '';
    public $filterCapitalTracking = '';

    protected $rules = [
        'debtor_name' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'description' => 'required|string|max:500',
        'due_date' => 'required|date|after_or_equal:today',
        'capital_tracking_id' => 'required|exists:capital_tracking,id',
        'notes' => 'nullable|string|max:1000',
        'contact_info' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'debtor_name.required' => 'Nama debitur harus diisi.',
        'amount.required' => 'Jumlah hutang harus diisi.',
        'amount.numeric' => 'Jumlah hutang harus berupa angka.',
        'amount.min' => 'Jumlah hutang tidak boleh negatif.',
        'description.required' => 'Deskripsi harus diisi.',
        'due_date.required' => 'Tanggal jatuh tempo harus diisi.',
        'due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh kurang dari hari ini.',
        'capital_tracking_id.required' => 'Modal usaha harus dipilih.',
        'capital_tracking_id.exists' => 'Modal usaha tidak valid.',
    ];

    public function render()
    {
        $query = DebtReminder::with('capitalTracking');

        // Apply filters
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterDueDate) {
            $query->whereDate('reminder_date', $this->filterDueDate);
        }

        if ($this->filterCapitalTracking) {
            $query->where('capital_tracking_id', $this->filterCapitalTracking);
        }

        $debtReminders = $query->orderBy('reminder_date', 'asc')
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);

        $capitalTrackings = CapitalTracking::where('is_active', true)->get();

        // Get overdue reminders count
        $overdueCount = DebtReminder::where('status', 'pending')
                                   ->where('reminder_date', '<', now())
                                   ->count();

        // Get due today count
        $dueTodayCount = DebtReminder::where('status', 'pending')
                                    ->whereDate('reminder_date', today())
                                    ->count();

        // Get total pending amount
        $totalPendingAmount = DebtReminder::where('status', 'pending')
                                         ->sum('amount');

        return view('livewire.debt-reminder-management', [
            'debtReminders' => $debtReminders,
            'capitalTrackings' => $capitalTrackings,
            'overdueCount' => $overdueCount,
            'dueTodayCount' => $dueTodayCount,
            'totalPendingAmount' => $totalPendingAmount,
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
        $this->debtor_name = '';
        $this->amount = '';
        $this->description = '';
        $this->due_date = '';
        $this->capital_tracking_id = '';
        $this->notes = '';
        $this->contact_info = '';
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editingId) {
                $debtReminder = DebtReminder::findOrFail($this->editingId);
                $debtReminder->update([
                    'debtor_name' => $this->debtor_name,
                    'amount' => $this->amount,
                    'description' => $this->description,
                    'due_date' => $this->due_date,
                    'capital_tracking_id' => $this->capital_tracking_id,
                    'notes' => $this->notes,
                    'contact_info' => $this->contact_info,
                ]);
                session()->flash('message', 'Pengingat hutang berhasil diperbarui.');
            } else {
                DebtReminder::create([
                    'debtor_name' => $this->debtor_name,
                    'amount' => $this->amount,
                    'description' => $this->description,
                    'due_date' => $this->due_date,
                    'capital_tracking_id' => $this->capital_tracking_id,
                    'notes' => $this->notes,
                    'contact_info' => $this->contact_info,
                    'status' => 'pending',
                ]);
                session()->flash('message', 'Pengingat hutang berhasil ditambahkan.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $debtReminder = DebtReminder::findOrFail($id);
        
        $this->editingId = $id;
        $this->debtor_name = $debtReminder->debtor_name;
        $this->amount = $debtReminder->amount;
        $this->description = $debtReminder->description;
        $this->due_date = $debtReminder->due_date ? $debtReminder->due_date->format('Y-m-d') : '';
        $this->capital_tracking_id = $debtReminder->capital_tracking_id;
        $this->notes = $debtReminder->notes;
        $this->contact_info = $debtReminder->contact_info;
        
        $this->showModal = true;
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        try {
            DebtReminder::findOrFail($this->deleteId)->delete();
            session()->flash('message', 'Pengingat hutang berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function markAsPaid($id)
    {
        try {
            $debtReminder = DebtReminder::findOrFail($id);
            $debtReminder->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            session()->flash('message', 'Hutang berhasil ditandai sebagai lunas.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function markAsPending($id)
    {
        try {
            $debtReminder = DebtReminder::findOrFail($id);
            $debtReminder->update([
                'status' => 'pending',
                'paid_at' => null,
            ]);
            session()->flash('message', 'Status hutang berhasil dikembalikan ke pending.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function resetFilters()
    {
        $this->filterStatus = '';
        $this->filterDueDate = '';
        $this->filterCapitalTracking = '';
    }

    public function getDaysUntilDue($dueDate)
    {
        return Carbon::parse($dueDate)->diffInDays(now(), false);
    }

    public function getStatusBadgeClass($status, $dueDate)
    {
        if ($status === 'paid') {
            return 'bg-success';
        }

        if ($status === 'cancelled') {
            return 'bg-secondary';
        }

        // For pending status
        $daysUntilDue = $this->getDaysUntilDue($dueDate);
        
        if ($daysUntilDue > 0) {
            return 'bg-danger'; // Overdue
        } elseif ($daysUntilDue === 0) {
            return 'bg-warning'; // Due today
        } else {
            return 'bg-primary'; // Future due date
        }
    }
}
