<?php

namespace App\Livewire;

use App\Product;
use App\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductTable extends Component
{
    use WithPagination, AuthorizesRequests;

    // Properties untuk search dan filter
    public $search = '';
    public $category = '';
    public $status = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Properties untuk modal
    public $showModal = false;
    public $showDetailModal = false;
    public $editMode = false;
    public $productId = null;
    public $selectedProduct = null;

    // Properties untuk form
    public $sku = '';
    public $barcode = '';
    public $name = '';
    public $categoryInput = '';
    public $unit = '';
    public $base_cost = 0;
    public $price_retail = 0;
    public $price_grosir = 0;
    public $min_margin_pct = 0;
    public $is_active = true;

    // Properties untuk bulk actions
    public $selectedProducts = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'status' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10]
    ];

    protected $listeners = [
        'productSaved' => 'refreshProducts',
        'productDeleted' => 'refreshProducts'
    ];

    public function rules()
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($this->productId)
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($this->productId)
            ],
            'name' => 'required|string|max:255',
            'categoryInput' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'base_cost' => 'required|numeric|min:0',
            'price_retail' => 'required|numeric|min:0',
            'price_grosir' => 'required|numeric|min:0',
            'min_margin_pct' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean'
        ];
    }

    public function mount()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingStatus()
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

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($productId)
    {
        $product = Product::findOrFail($productId);
        $this->productId = $product->id;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode;
        $this->name = $product->name;
        $this->categoryInput = $product->category;
        $this->unit = $product->unit;
        $this->base_cost = $product->base_cost;
        $this->price_retail = $product->price_retail;
        $this->price_grosir = $product->price_grosir;
        $this->min_margin_pct = $product->min_margin_pct;
        $this->is_active = $product->is_active;
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function openDetailModal($productId)
    {
        $this->selectedProduct = Product::with(['stockMovements' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }, 'saleItems.sale'])->findOrFail($productId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedProduct = null;
    }

    public function save()
    {
        $this->validate();

        try {
            // Check authorization based on mode
            if ($this->editMode) {
                $product = Product::findOrFail($this->productId);
                $this->authorize('update', $product);
            } else {
                $this->authorize('create', Product::class);
            }
            $data = [
                'sku' => $this->sku,
                'barcode' => $this->barcode ?: null,
                'name' => $this->name,
                'category' => $this->categoryInput,
                'unit' => $this->unit,
                'base_cost' => $this->base_cost,
                'price_retail' => $this->price_retail,
                'price_grosir' => $this->price_grosir,
                'min_margin_pct' => $this->min_margin_pct,
                'is_active' => $this->is_active
            ];

            if ($this->editMode) {
                // Product already found above for authorization
                $oldData = $product->toArray();
                $product->update($data);
                
                // Log audit
                AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
                
                session()->flash('message', 'Produk berhasil diperbarui!');
            } else {
                $product = Product::create($data);
                
                // Log audit
                AuditLog::logCreate('products', $product->id, $product->toArray());
                
                session()->flash('message', 'Produk berhasil ditambahkan!');
            }

            $this->closeModal();
            $this->emit('productSaved');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            
            // Check authorization
            $this->authorize('delete', $product);
            
            $productData = $product->toArray();
            
            // Check if product has stock movements or sales
            if ($product->stockMovements()->exists() || $product->saleItems()->exists()) {
                session()->flash('error', 'Produk tidak dapat dihapus karena memiliki riwayat transaksi!');
                return;
            }
            
            $product->delete();
            
            // Log audit
            AuditLog::logDelete('products', $productId, $productData);
            
            session()->flash('message', 'Produk berhasil dihapus!');
            $this->emit('productDeleted');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function toggleStatus($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $oldData = $product->toArray();
            $product->update(['is_active' => !$product->is_active]);
            
            // Log audit
            AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
            
            $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
            session()->flash('message', "Produk berhasil {$status}!");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bulkDelete()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Pilih produk yang akan dihapus!');
            return;
        }

        try {
            $products = Product::whereIn('id', $this->selectedProducts)->get();
            
            foreach ($products as $product) {
                // Check authorization
                $this->authorize('delete', $product);
                
                // Check if product has stock movements or sales
                if ($product->stockMovements()->exists() || $product->saleItems()->exists()) {
                    session()->flash('error', "Produk {$product->name} tidak dapat dihapus karena memiliki riwayat transaksi!");
                    return;
                }
            }
            
            foreach ($products as $product) {
                $productData = $product->toArray();
                $product->delete();
                
                // Log audit
                AuditLog::logDelete('products', $product->id, $productData);
            }
            
            $count = count($this->selectedProducts);
            session()->flash('message', "{$count} produk berhasil dihapus!");
            
            $this->selectedProducts = [];
            $this->selectAll = false;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProducts = $this->getProducts()->get()->pluck('id')->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function updatedSelectedProducts()
    {
        $totalProducts = $this->getProducts()->count();
        $selectedCount = count($this->selectedProducts);
        
        if ($selectedCount === 0) {
            $this->selectAll = false;
        } elseif ($selectedCount === $totalProducts) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function resetForm()
    {
        $this->productId = null;
        $this->sku = '';
        $this->barcode = '';
        $this->name = '';
        $this->categoryInput = '';
        $this->unit = '';
        $this->base_cost = 0;
        $this->price_retail = 0;
        $this->price_grosir = 0;
        $this->min_margin_pct = 0;
        $this->is_active = true;
    }

    public function exportProducts()
    {
        $this->authorize('export', Product::class);
        
        try {
            $products = $this->getProducts()->get();
            
            $export = new \App\Exports\ProductExport($products);
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                $export, 
                'products_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }
    
    public function refreshProducts()
    {
        // Method untuk refresh data setelah operasi CRUD
    }

    public function getProducts()
    {
        $query = Product::query();

        // Search
        if ($this->search) {
            $query->search($this->search);
        }

        // Filter by category
        if ($this->category) {
            $query->where('category', $this->category);
        }

        // Filter by status
        if ($this->status !== '') {
            if ($this->status === 'active') {
                $query->active();
            } elseif ($this->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query;
    }

    public function getCategories()
    {
        return Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');
    }

    public function render()
    {
        $products = $this->getProducts()->paginate($this->perPage);
        $categories = $this->getCategories();

        return view('livewire.product-table', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
}
