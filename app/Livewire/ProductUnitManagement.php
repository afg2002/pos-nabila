<?php

namespace App\Livewire;

use App\ProductUnit;
use Livewire\Component;
use Livewire\WithPagination;

class ProductUnitManagement extends Component
{
    use WithPagination;

    // Form properties
    public $name = '';
    public $abbreviation = '';
    public $description = '';
    public $is_active = true;
    public $sort_order = 0;
    public $editingId = null;

    // Modal states
    public $showModal = false;
    public $showDeleteModal = false;
    public $deleteId = null;

    // Search and filter
    public $search = '';
    public $filterStatus = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'abbreviation' => 'required|string|max:10',
        'description' => 'nullable|string|max:500',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];

    protected $messages = [
        'name.required' => 'Nama satuan harus diisi.',
        'name.max' => 'Nama satuan maksimal 255 karakter.',
        'abbreviation.required' => 'Singkatan harus diisi.',
        'abbreviation.max' => 'Singkatan maksimal 10 karakter.',
        'description.max' => 'Deskripsi maksimal 500 karakter.',
        'sort_order.integer' => 'Urutan harus berupa angka.',
        'sort_order.min' => 'Urutan tidak boleh negatif.',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        $query = ProductUnit::query();

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('abbreviation', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus === '1');
        }

        $units = $query->orderBy('sort_order')
                      ->orderBy('name')
                      ->paginate(10);

        return view('livewire.product-unit-management', [
            'units' => $units
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
        $this->resetValidation();
    }

    public function edit($id)
    {
        $unit = ProductUnit::findOrFail($id);
        $this->editingId = $id;
        $this->name = $unit->name;
        $this->abbreviation = $unit->abbreviation;
        $this->description = $unit->description;
        $this->is_active = $unit->is_active;
        $this->sort_order = $unit->sort_order;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Check for duplicate name or abbreviation
        $existingQuery = ProductUnit::where(function($q) {
            $q->where('name', $this->name)
              ->orWhere('abbreviation', $this->abbreviation);
        });

        if ($this->editingId) {
            $existingQuery->where('id', '!=', $this->editingId);
        }

        if ($existingQuery->exists()) {
            $this->addError('name', 'Nama atau singkatan satuan sudah ada.');
            return;
        }

        // Set default sort_order if not provided
        if (!$this->sort_order) {
            $this->sort_order = ProductUnit::max('sort_order') + 1;
        }

        $data = [
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editingId) {
            ProductUnit::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Satuan berhasil diperbarui.');
        } else {
            ProductUnit::create($data);
            session()->flash('message', 'Satuan berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->deleteId) {
            $unit = ProductUnit::findOrFail($this->deleteId);
            
            // Check if unit is being used by products
            if ($unit->products()->count() > 0) {
                session()->flash('error', 'Satuan tidak dapat dihapus karena masih digunakan oleh produk.');
            } else {
                $unit->delete();
                session()->flash('message', 'Satuan berhasil dihapus.');
            }
        }

        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function toggleStatus($id)
    {
        $unit = ProductUnit::findOrFail($id);
        $unit->update(['is_active' => !$unit->is_active]);
        
        $status = $unit->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('message', "Satuan berhasil {$status}.");
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->abbreviation = '';
        $this->description = '';
        $this->is_active = true;
        $this->sort_order = 0;
        $this->editingId = null;
    }
}