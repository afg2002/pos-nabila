<?php

namespace App\Livewire;

use App\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WarehouseTable extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $typeFilter = '';
    public $selectedWarehouses = [];
    public $selectAll = false;
    public $selectedWarehouse = null;

    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showBulkDeleteModal = false;
    public $showDetailModal = false;
    public $warehouseToDelete = null;

    // Form fields
    public $name = '';
    public $code = '';
    public $type = 'warehouse';
    public $branch = '';
    public $address = '';
    public $phone = '';
    public $is_default = false;
    public $editingWarehouseId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
        'typeFilter' => ['except' => ''],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10|unique:warehouses,code',
        'type' => 'required|in:store,warehouse,kiosk',
        'branch' => 'nullable|string|max:255',
        'address' => 'nullable|string',
        'phone' => 'nullable|string|max:20',
        'is_default' => 'boolean',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedWarehouses = Warehouse::pluck('id')->toArray();
        } else {
            $this->selectedWarehouses = [];
        }
    }

    public function updatedSelectedWarehouses()
    {
        $this->selectAll = count($this->selectedWarehouses) === Warehouse::count();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal()
    {
        $this->authorize('create', Warehouse::class);
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal($warehouseId)
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        $this->authorize('update', $warehouse);
        
        $this->editingWarehouseId = $warehouse->id;
        $this->name = $warehouse->name;
        $this->code = $warehouse->code;
        $this->type = $warehouse->type;
        $this->branch = $warehouse->branch;
        $this->address = $warehouse->address;
        $this->phone = $warehouse->phone;
        $this->is_default = $warehouse->is_default;
        
        $this->showEditModal = true;
    }

    public function createWarehouse()
    {
        $this->authorize('create', Warehouse::class);
        
        $this->validate();

        // If setting as default, unset other defaults
        if ($this->is_default) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        Warehouse::create([
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'branch' => $this->branch,
            'address' => $this->address,
            'phone' => $this->phone,
            'is_default' => $this->is_default,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('message', 'Gudang berhasil ditambahkan! 🎉');
    }

    public function updateWarehouse()
    {
        $warehouse = Warehouse::findOrFail($this->editingWarehouseId);
        $this->authorize('update', $warehouse);

        $rules = $this->rules;
        $rules['code'] = 'required|string|max:10|unique:warehouses,code,' . $this->editingWarehouseId;
        
        $this->validate($rules);

        // If setting as default, unset other defaults
        if ($this->is_default && !$warehouse->is_default) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse->update([
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'branch' => $this->branch,
            'address' => $this->address,
            'phone' => $this->phone,
            'is_default' => $this->is_default,
        ]);

        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('message', 'Gudang berhasil diperbarui! ✨');
    }

    public function confirmDelete($warehouseId)
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        $this->authorize('delete', $warehouse);
        
        $this->warehouseToDelete = $warehouse;
        $this->showDeleteModal = true;
    }

    public function confirmBulkDelete()
    {
        if (count($this->selectedWarehouses) > 0) {
            $this->showDeleteModal = true;
        }
    }

    public function deleteWarehouse()
    {
        if (!$this->warehouseToDelete) {
            // Handle bulk delete
            $warehouses = Warehouse::whereIn('id', $this->selectedWarehouses)->get();
            
            foreach ($warehouses as $warehouse) {
                $this->authorize('delete', $warehouse);
                
                // Check if warehouse has stock or movements
                if ($warehouse->productStocks()->exists() || $warehouse->stockMovements()->exists()) {
                    session()->flash('error', "Gudang {$warehouse->name} tidak dapat dihapus karena masih memiliki data stock atau movement!");
                    $this->showDeleteModal = false;
                    return;
                }

                // Cannot delete default warehouse
                if ($warehouse->is_default) {
                    session()->flash('error', "Gudang default {$warehouse->name} tidak dapat dihapus!");
                    $this->showDeleteModal = false;
                    return;
                }
            }

            // Delete all selected warehouses
            Warehouse::whereIn('id', $this->selectedWarehouses)->delete();
            $this->selectedWarehouses = [];
            $this->selectAll = false;
            session()->flash('message', 'Gudang terpilih berhasil dihapus! 🗑️');
        } else {
            // Single delete
            // Check if warehouse has stock or movements
            if ($this->warehouseToDelete->productStocks()->exists() || $this->warehouseToDelete->stockMovements()->exists()) {
                session()->flash('error', 'Gudang tidak dapat dihapus karena masih memiliki data stock atau movement!');
                $this->showDeleteModal = false;
                $this->warehouseToDelete = null;
                return;
            }

            // Cannot delete default warehouse
            if ($this->warehouseToDelete->is_default) {
                session()->flash('error', 'Gudang default tidak dapat dihapus!');
                $this->showDeleteModal = false;
                $this->warehouseToDelete = null;
                return;
            }

            $this->warehouseToDelete->delete();
            session()->flash('message', 'Gudang berhasil dihapus! 🗑️');
        }
        
        $this->showDeleteModal = false;
        $this->warehouseToDelete = null;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->code = '';
        $this->type = 'warehouse';
        $this->branch = '';
        $this->address = '';
        $this->phone = '';
        $this->is_default = false;
        $this->editingWarehouseId = null;
        $this->resetErrorBag();
    }

    // Detail Modal Methods
    public function openDetailModal($warehouseId)
    {
        $this->selectedWarehouse = Warehouse::with(['productStocks.product'])->find($warehouseId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedWarehouse = null;
    }

    public function closeModal()
    {
        // Generic closer for all modals used in the Blade
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showBulkDeleteModal = false;
        $this->showDetailModal = false;
        $this->warehouseToDelete = null;
        $this->resetErrorBag();
    }

    public function getWarehouseTypesProperty()
    {
        return [
            'warehouse' => 'Gudang',
            'store' => 'Toko',
            'kiosk' => 'Kiosk'
        ];
    }

    public function render()
    {
        $query = Warehouse::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('type', 'like', '%' . $this->search . '%')
                  ->orWhere('branch', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        $warehouses = $query->orderBy($this->sortField, $this->sortDirection)
                           ->paginate($this->perPage);

        return view('livewire.warehouse-table', [
            'warehouses' => $warehouses,
            'warehouseTypes' => $this->warehouseTypes
        ]);
    }
}