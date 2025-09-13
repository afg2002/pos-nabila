<?php

namespace App\Livewire;

use App\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CustomerTable extends Component
{
    use WithPagination;
    
    // Search & Filter Properties
    public $search = '';
    public $typeFilter = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    // Form Properties
    public $showModal = false;
    public $editMode = false;
    public $customerId;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $type = 'regular';
    public $discount_percentage = 0;
    public $birth_date = '';
    public $gender = '';
    public $notes = '';
    public $is_active = true;
    
    // Bulk Actions
    public $selectAll = false;
    public $selectedCustomers = [];
    
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
        'type' => 'required|in:regular,member,vip',
        'discount_percentage' => 'required|numeric|min:0|max:100',
        'birth_date' => 'nullable|date',
        'gender' => 'nullable|in:male,female',
        'notes' => 'nullable|string',
        'is_active' => 'boolean'
    ];
    
    protected $messages = [
        'name.required' => 'Nama pelanggan wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'type.required' => 'Tipe pelanggan wajib dipilih.',
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
    
    public function openEditModal($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->type = $customer->type;
        $this->discount_percentage = $customer->discount_percentage;
        $this->birth_date = $customer->birth_date ? $customer->birth_date->format('Y-m-d') : '';
        $this->gender = $customer->gender;
        $this->notes = $customer->notes;
        $this->is_active = $customer->is_active;
        
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
                'phone' => $this->phone ?: null,
                'address' => $this->address ?: null,
                'type' => $this->type,
                'discount_percentage' => $this->discount_percentage,
                'birth_date' => $this->birth_date ?: null,
                'gender' => $this->gender ?: null,
                'notes' => $this->notes ?: null,
                'is_active' => $this->is_active
            ];
            
            if ($this->editMode) {
                $customer = Customer::findOrFail($this->customerId);
                $customer->update($data);
                session()->flash('success', 'Data pelanggan berhasil diperbarui!');
            } else {
                Customer::create($data);
                session()->flash('success', 'Pelanggan baru berhasil ditambahkan!');
            }
            
            $this->closeModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function delete($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            
            // Check if customer has sales
            if ($customer->sales()->count() > 0) {
                session()->flash('error', 'Tidak dapat menghapus pelanggan yang memiliki riwayat transaksi!');
                return;
            }
            
            $customer->delete();
            session()->flash('success', 'Pelanggan berhasil dihapus!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function toggleStatus($customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            $customer->update(['is_active' => !$customer->is_active]);
            
            $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('success', "Pelanggan berhasil {$status}!");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCustomers = $this->getCustomers()->get()->pluck('id')->toArray();
        } else {
            $this->selectedCustomers = [];
        }
    }

    public function updatedSelectedCustomers()
    {
        $totalCustomers = $this->getCustomers()->count();
        $selectedCount = count($this->selectedCustomers);
        
        if ($selectedCount === 0) {
            $this->selectAll = false;
        } elseif ($selectedCount === $totalCustomers) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }
    
    public function deleteSelected()
    {
        try {
            $customers = Customer::whereIn('id', $this->selectedCustomers)->get();
            
            foreach ($customers as $customer) {
                if ($customer->sales()->count() > 0) {
                    session()->flash('error', 'Beberapa pelanggan tidak dapat dihapus karena memiliki riwayat transaksi!');
                    return;
                }
            }
            
            Customer::whereIn('id', $this->selectedCustomers)->delete();
            
            $count = count($this->selectedCustomers);
            session()->flash('success', "{$count} pelanggan berhasil dihapus!");
            
            $this->selectedCustomers = [];
            $this->selectAll = false;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function resetForm()
    {
        $this->customerId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->type = 'regular';
        $this->discount_percentage = 0;
        $this->birth_date = '';
        $this->gender = '';
        $this->notes = '';
        $this->is_active = true;
    }
    
    public function getCustomers()
    {
        return Customer::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
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
    
    public function render()
    {
        return view('livewire.customer-table', [
            'customers' => $this->getCustomers()
        ]);
    }
}
