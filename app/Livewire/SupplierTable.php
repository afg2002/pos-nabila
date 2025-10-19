<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class SupplierTable extends Component
{
    use WithPagination, WithAlerts;
    
    // Search & Filter Properties
    public $search = '';
    public $typeFilter = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Form Properties
    public $showModal = false;
    public $editMode = false;
    public $supplierId;
    public $name = '';
    public $email = '';
    public $contact_person = '';
    public $phone = '';
    public $address = '';
    public $type = 'regular';
    public $discount_percentage = 0;
    public $birth_date = '';
    public $gender = '';
    public $notes = '';
    public $is_active = true;
    public $status = 'active';
    
    // Bulk Actions
    public $selectAll = false;
    public $selectedSuppliers = [];
    
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'contact_person' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
        'type' => 'required|in:regular,member,vip,preferred',
        'discount_percentage' => 'required|numeric|min:0|max:100',
        'birth_date' => 'nullable|date',
        'gender' => 'nullable|in:male,female',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
        'status' => 'required|in:active,inactive'
    ];
    
    protected $messages = [
        'name.required' => 'Nama supplier wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'type.required' => 'Tipe supplier wajib dipilih.',
        'discount_percentage.numeric' => 'Diskon harus berupa angka.',
        'discount_percentage.min' => 'Diskon minimal 0%.',
        'discount_percentage.max' => 'Diskon maksimal 100%.'
    ];
    
    public function mount()
    {
        $this->resetPage();
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingTypeFilter()
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
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }
    
    public function openEditModal($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        
        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->email = $supplier->email;
        $this->contact_person = $supplier->contact_person;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->type = $supplier->type;
        $this->discount_percentage = $supplier->discount_percentage;
        $this->birth_date = $supplier->birth_date ? $supplier->birth_date->format('Y-m-d') : '';
        $this->gender = $supplier->gender;
        $this->notes = $supplier->notes;
        $this->is_active = $supplier->is_active;
        $this->status = $supplier->status;
        
        $this->editMode = true;
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }
    
    public function save()
    {
        $this->validate();
        
        try {
            $data = [
                'name' => $this->name,
                'email' => $this->email ?: null,
                'contact_person' => $this->contact_person ?: null,
                'phone' => $this->phone ?: null,
                'address' => $this->address ?: null,
                'type' => $this->type,
                'discount_percentage' => $this->discount_percentage,
                'birth_date' => $this->birth_date ?: null,
                'gender' => $this->gender ?: null,
                'notes' => $this->notes ?: null,
                'is_active' => $this->is_active,
                'status' => $this->status
            ];
            
            if ($this->editMode) {
                $supplier = Supplier::findOrFail($this->supplierId);
                $supplier->update($data);
                session()->flash('message', 'Data supplier berhasil diperbarui!');
            } else {
                Supplier::create($data);
                session()->flash('message', 'Supplier baru berhasil ditambahkan!');
            }
            // Clear cached supplier lists so UI reflects changes immediately
            $this->clearSupplierCache();
            $this->resetPage();
            
            $this->closeModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm delete supplier
     */
    public function confirmDelete($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $this->showConfirm(
            'Hapus Supplier',
            "Yakin ingin menghapus supplier '{$supplier->name}'?",
            'delete',
            ['supplierId' => $supplierId],
            'Ya, hapus!',
            'Batal'
        );
    }

    public function delete($params)
    {
        $supplierId = $params['supplierId'];
        try {
            $supplier = Supplier::findOrFail($supplierId);
            
            // Check if supplier has purchase orders
            if ($supplier->purchaseOrders()->count() > 0) {
                session()->flash('error', 'Tidak dapat menghapus supplier yang memiliki riwayat transaksi!');
                return;
            }
            
            $supplier->delete();
            session()->flash('message', 'Supplier berhasil dihapus!');
            // Clear cache so the list updates immediately
            $this->clearSupplierCache();
            $this->resetPage();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function toggleStatus($supplierId)
    {
        try {
            $supplier = Supplier::findOrFail($supplierId);
            $supplier->update(['is_active' => !$supplier->is_active]);
            
            $status = $supplier->is_active ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('message', "Supplier berhasil {$status}!");
            // Clear cache so status change is reflected
            $this->clearSupplierCache();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedSuppliers = $this->getSuppliers()->get()->pluck('id')->toArray();
        } else {
            $this->selectedSuppliers = [];
        }
    }

    public function updatedSelectedSuppliers()
    {
        $totalSuppliers = $this->getSuppliers()->count();
        $selectedCount = count($this->selectedSuppliers);
        
        if ($selectedCount === 0) {
            $this->selectAll = false;
        } elseif ($selectedCount === $totalSuppliers) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }
    
    /**
     * Confirm bulk delete suppliers
     */
    public function confirmDeleteSelected()
    {
        if (empty($this->selectedSuppliers)) {
            session()->flash('error', 'Pilih supplier yang akan dihapus!');
            return;
        }

        $count = count($this->selectedSuppliers);
        $this->showConfirm(
            'Hapus Supplier Massal',
            "Yakin ingin menghapus {$count} supplier terpilih?",
            'deleteSelected',
            [],
            'Ya, hapus!',
            'Batal'
        );
    }

    public function deleteSelected()
    {
        try {
            $suppliers = Supplier::whereIn('id', $this->selectedSuppliers)->get();
            
            foreach ($suppliers as $supplier) {
                if ($supplier->purchaseOrders()->count() > 0) {
                    session()->flash('error', 'Beberapa supplier tidak dapat dihapus karena memiliki riwayat transaksi!');
                    return;
                }
            }
            
            Supplier::whereIn('id', $this->selectedSuppliers)->delete();
            
            $count = count($this->selectedSuppliers);
            session()->flash('message', "{$count} supplier berhasil dihapus!");
            
            $this->selectedSuppliers = [];
            $this->selectAll = false;
            // Clear cache so bulk delete is reflected
            $this->clearSupplierCache();
            $this->resetPage();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function resetForm()
    {
        $this->supplierId = null;
        $this->name = '';
        $this->email = '';
        $this->contact_person = '';
        $this->phone = '';
        $this->address = '';
        $this->type = 'regular';
        $this->discount_percentage = 0;
        $this->birth_date = '';
        $this->gender = '';
        $this->notes = '';
        $this->is_active = true;
        $this->status = 'active';
    }
    
    public function getSuppliers()
    {
        // Create cache key based on filters
        $cacheKey = 'suppliers_' . md5(serialize([
            'search' => $this->search,
            'typeFilter' => $this->typeFilter,
            'statusFilter' => $this->statusFilter,
            'page' => $this->getPage(),
            'perPage' => $this->perPage
        ]));

        // For search queries, don't cache to ensure real-time results
        if ($this->search) {
            return $this->buildSupplierQuery();
        }

        // Cache for 5 minutes for non-search requests
        return cache()->remember($cacheKey, 300, function () {
            return $this->buildSupplierQuery();
        });
    }

    private function buildSupplierQuery()
    {
        return Supplier::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('contact_person', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    // Add cache clearing method (safe across cache drivers)
    private function clearSupplierCache()
    {
        try {
            // Try Redis connection via store
            $store = cache()->getStore();
            if (method_exists($store, 'connection')) {
                $redis = $store->connection();
                $keys = $redis->keys('*suppliers_*');
                if (!empty($keys)) {
                    $redis->del($keys);
                }
                return;
            }

            // Try direct redis via cache()->getRedis()
            if (method_exists(cache(), 'getRedis')) {
                $keys = cache()->getRedis()->keys('*suppliers_*');
                if (!empty($keys)) {
                    cache()->getRedis()->del($keys);
                }
                return;
            }

            // Fallback: flush cache to ensure UI updates (may affect other caches)
            cache()->flush();
        } catch (\Throwable $e) {
            // As last resort, flush cache
            try { cache()->flush(); } catch (\Throwable $ignored) {}
        }
    }
    
    public function render()
    {
        return view('livewire.supplier-table', [
            'suppliers' => $this->getSuppliers()
        ]);
    }
}