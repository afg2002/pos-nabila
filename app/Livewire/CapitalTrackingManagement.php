<?php

namespace App\Livewire;

use App\CapitalTracking;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CapitalTrackingManagement extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $statusFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $name;
    public $initial_amount;
    public $description;
    public $is_active = true;
    public $editingId = null;
    public $showModal = false;
    public $confirmingDelete = false;
    public $deleteId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'initial_amount' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Nama modal harus diisi',
        'initial_amount.required' => 'Jumlah modal awal harus diisi',
        'initial_amount.numeric' => 'Jumlah modal harus berupa angka',
        'initial_amount.min' => 'Jumlah modal tidak boleh negatif',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
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
        $capitalTrackings = CapitalTracking::with('creator')
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.capital-tracking-management', [
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
        $this->name = '';
        $this->initial_amount = '';
        $this->description = '';
        $this->is_active = true;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $capitalTracking = CapitalTracking::findOrFail($this->editingId);
            $capitalTracking->update([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
            
            session()->flash('message', 'Modal berhasil diperbarui!');
        } else {
            CapitalTracking::create([
                'name' => $this->name,
                'initial_amount' => $this->initial_amount,
                'current_amount' => $this->initial_amount,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'created_by' => Auth::id(),
            ]);
            
            session()->flash('message', 'Modal berhasil ditambahkan!');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        $capitalTracking = CapitalTracking::findOrFail($id);
        
        $this->editingId = $id;
        $this->name = $capitalTracking->name;
        $this->initial_amount = $capitalTracking->initial_amount;
        $this->description = $capitalTracking->description;
        $this->is_active = $capitalTracking->is_active;
        
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
            $capitalTracking = CapitalTracking::findOrFail($this->deleteId);
            
            // Check if there are related purchase orders
            if ($capitalTracking->purchaseOrders()->count() > 0) {
                session()->flash('error', 'Tidak dapat menghapus modal yang sudah memiliki purchase order!');
            } else {
                $capitalTracking->delete();
                session()->flash('message', 'Modal berhasil dihapus!');
            }
        }
        
        $this->confirmingDelete = false;
        $this->deleteId = null;
    }

    public function toggleStatus($id)
    {
        $capitalTracking = CapitalTracking::findOrFail($id);
        $capitalTracking->update(['is_active' => !$capitalTracking->is_active]);
        
        $status = $capitalTracking->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('message', "Modal berhasil {$status}!");
    }
}
