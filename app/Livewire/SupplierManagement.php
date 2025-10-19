<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $showModal = false;
    public $editingId = null;

    public $name = '';
    public $email = '';
    public $contact_person = '';
    public $phone = '';
    public $address = '';
    public $status = 'active';

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function render()
    {
        $query = Supplier::query();

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('contact_person', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $suppliers = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.supplier-management', [
            'suppliers' => $suppliers,
        ]);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        if ($id) {
            $supplier = Supplier::findOrFail($id);
            $this->editingId = $supplier->id;
            $this->name = $supplier->name;
            $this->email = $supplier->email ?? '';
            $this->contact_person = $supplier->contact_person ?? '';
            $this->phone = $supplier->phone ?? '';
            $this->address = $supplier->address ?? '';
            $this->status = $supplier->status ?? 'active';
        } else {
            $this->resetForm();
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->editingId) {
            $supplier = Supplier::findOrFail($this->editingId);
            $supplier->update($data);
            session()->flash('message', 'Supplier berhasil diperbarui.');
        } else {
            Supplier::create($data);
            session()->flash('message', 'Supplier baru berhasil ditambahkan.');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        session()->flash('message', 'Supplier berhasil dihapus.');
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->contact_person = '';
        $this->phone = '';
        $this->address = '';
        $this->status = 'active';
    }
}