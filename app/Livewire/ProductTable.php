<?php

namespace App\Livewire;

use App\Product;
use App\ProductUnit;
use App\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class ProductTable extends Component
{
    use WithPagination, AuthorizesRequests, WithFileUploads;

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
    public $photo;
    public $currentPhoto = '';
    public $unit_id = '';
    public $base_cost = 0;
    public $price_retail = 0;
    public $price_semi_grosir = 0;
    public $price_grosir = 0;
    public $min_margin_pct = 0;
    public $default_price_type = 'retail';
    public $is_active = true;

    // Properties untuk unit management
    public $showUnitModal = false;
    public $newUnitName = '';
    public $newUnitAbbreviation = '';
    public $newUnitDescription = '';

    // Properties untuk bulk actions
    public $selectedProducts = [];
    public $selectAll = false;
    public $bulkPriceType = 'retail';

    // Properties untuk photo editing dalam detail modal
    public $showPhotoEditMode = false;
    public $newPhoto;
    public $tempPhoto = null;
    public $isUpdatingPhoto = false;
    public $isUploadingPhoto = false;

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
            'photo' => 'nullable|image|max:2048', // Max 2MB
            'newPhoto' => 'nullable|image|max:2048', // Max 2MB for photo updates
            'unit_id' => 'required|exists:product_units,id',
            'base_cost' => 'required|numeric|min:0',
            'price_retail' => 'required|numeric|min:0',
            'price_semi_grosir' => 'nullable|numeric|min:0',
            'price_grosir' => 'required|numeric|min:0',
            'min_margin_pct' => 'required|numeric|min:0|max:100',
            'default_price_type' => 'required|in:retail,semi_grosir,grosir,custom',
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
        $this->currentPhoto = $product->photo;
        $this->unit_id = $product->unit_id;
        $this->base_cost = $product->base_cost;
        $this->price_retail = $product->price_retail;
        $this->price_semi_grosir = $product->price_semi_grosir;
        $this->price_grosir = $product->price_grosir;
        $this->min_margin_pct = $product->min_margin_pct;
        $this->default_price_type = $product->default_price_type;
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
        $this->showPhotoEditMode = false;
        $this->newPhoto = null;
        $this->tempPhoto = null;
        $this->isUpdatingPhoto = false;
        $this->isUploadingPhoto = false;
        $this->isRemovingPhoto = false;
    }

    public function togglePhotoEditMode()
    {
        $this->showPhotoEditMode = !$this->showPhotoEditMode;
        if (!$this->showPhotoEditMode) {
            $this->newPhoto = null;
            $this->tempPhoto = null;
            $this->isUpdatingPhoto = false;
            $this->isUploadingPhoto = false;
        }
    }

    public function updatedNewPhoto()
    {
        $this->isUploadingPhoto = true;
        
        $this->validate([
            'newPhoto' => 'image|max:2048'
        ]);
        
        $this->tempPhoto = $this->newPhoto->temporaryUrl();
        $this->isUploadingPhoto = false;
    }

    public function updateProductPhoto()
    {
        $this->isUpdatingPhoto = true;
        
        $this->validate([
            'newPhoto' => 'required|image|max:2048'
        ]);

        try {
            $this->authorize('update', $this->selectedProduct);
            
            // Create products directory if it doesn't exist
            if (!Storage::disk('public')->exists('products')) {
                Storage::disk('public')->makeDirectory('products');
            }
            
            // Delete old photo if exists
            if ($this->selectedProduct->photo) {
                Storage::disk('public')->delete('products/' . $this->selectedProduct->photo);
            }
            
            // Store new photo
            $filename = time() . '_' . $this->newPhoto->getClientOriginalName();
            $this->newPhoto->storeAs('products', $filename, 'public');
            
            // Update product
            $oldPhoto = $this->selectedProduct->photo;
            $this->selectedProduct->update(['photo' => $filename]);
            
            // Log audit
            AuditLog::logUpdate('products', $this->selectedProduct->id, 
                ['photo' => $oldPhoto], 
                ['photo' => $filename]
            );
            
            // Reset form
            $this->showPhotoEditMode = false;
            $this->newPhoto = null;
            $this->tempPhoto = null;
            $this->isUpdatingPhoto = false;
            
            session()->flash('message', 'Foto produk berhasil diperbarui!');
            
        } catch (\Exception $e) {
            $this->isUpdatingPhoto = false;
            session()->flash('error', 'Gagal memperbarui foto: ' . $e->getMessage());
        }
    }

    public $isRemovingPhoto = false;

    public function removeProductPhoto()
    {
        $this->isRemovingPhoto = true;
        
        try {
            $this->authorize('update', $this->selectedProduct);
            
            // Delete photo file if exists
            if ($this->selectedProduct->photo) {
                Storage::disk('public')->delete('products/' . $this->selectedProduct->photo);
            }
            
            // Update product
            $oldPhoto = $this->selectedProduct->photo;
            $this->selectedProduct->update(['photo' => null]);
            
            // Log audit
            AuditLog::logUpdate('products', $this->selectedProduct->id, 
                ['photo' => $oldPhoto], 
                ['photo' => null]
            );
            
            $this->isRemovingPhoto = false;
            session()->flash('message', 'Foto produk berhasil dihapus!');
            
        } catch (\Exception $e) {
            $this->isRemovingPhoto = false;
            session()->flash('error', 'Gagal menghapus foto: ' . $e->getMessage());
        }
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
                'unit_id' => $this->unit_id,
                'base_cost' => $this->base_cost,
                'price_retail' => $this->price_retail,
                'price_semi_grosir' => $this->price_semi_grosir,
                'price_grosir' => $this->price_grosir,
                'min_margin_pct' => $this->min_margin_pct,
                'default_price_type' => $this->default_price_type,
                'is_active' => $this->is_active
            ];
            
            // Handle photo upload
            if ($this->photo) {
                // Create products directory if it doesn't exist
                if (!Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }
                
                // Delete old photo if editing and has current photo
                if ($this->editMode && $this->currentPhoto) {
                    Storage::disk('public')->delete('products/' . $this->currentPhoto);
                }
                
                // Store new photo
                $filename = time() . '_' . $this->photo->getClientOriginalName();
                $this->photo->storeAs('products', $filename, 'public');
                $data['photo'] = $filename;
            }

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
            $this->dispatch('productSaved');
            
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
            $this->dispatch('productDeleted');
            
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

    public function bulkSetPriceType()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Pilih produk yang akan diubah jenis harganya!');
            return;
        }

        try {
            $products = Product::whereIn('id', $this->selectedProducts)->get();
            
            foreach ($products as $product) {
                // Check authorization
                $this->authorize('update', $product);
            }
            
            $updateCount = 0;
            foreach ($products as $product) {
                $oldData = $product->toArray();
                $product->update(['default_price_type' => $this->bulkPriceType]);
                
                // Log audit
                AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
                $updateCount++;
            }
            
            $priceTypeName = Product::getPriceTypes()[$this->bulkPriceType];
            session()->flash('message', "{$updateCount} produk berhasil diubah ke jenis harga {$priceTypeName}!");
            
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
        $this->photo = null;
        $this->currentPhoto = '';
        $this->unit_id = '';
        $this->base_cost = 0;
        $this->price_retail = 0;
        $this->price_semi_grosir = 0;
        $this->price_grosir = 0;
        $this->min_margin_pct = 0;
        $this->default_price_type = 'retail';
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

    // Unit Management Methods
    public function openUnitModal()
    {
        $this->resetUnitForm();
        $this->showUnitModal = true;
    }

    public function closeUnitModal()
    {
        $this->showUnitModal = false;
        $this->resetUnitForm();
        $this->resetValidation(['newUnitName', 'newUnitAbbreviation']);
    }

    public function resetUnitForm()
    {
        $this->newUnitName = '';
        $this->newUnitAbbreviation = '';
        $this->newUnitDescription = '';
    }

    public function saveUnit()
    {
        $this->validate([
            'newUnitName' => 'required|string|max:50|unique:product_units,name',
            'newUnitAbbreviation' => 'required|string|max:10|unique:product_units,abbreviation',
            'newUnitDescription' => 'nullable|string|max:255'
        ]);

        try {
            $unit = ProductUnit::create([
                'name' => $this->newUnitName,
                'abbreviation' => $this->newUnitAbbreviation,
                'description' => $this->newUnitDescription,
                'is_active' => true,
                'sort_order' => ProductUnit::max('sort_order') + 1
            ]);

            // Set unit yang baru dibuat sebagai pilihan
            $this->unit_id = $unit->id;
            
            $this->closeUnitModal();
            session()->flash('message', 'Unit berhasil ditambahkan!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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

    public function getUnits()
    {
        return ProductUnit::active()->ordered()->get();
    }

    public function render()
    {
        $products = $this->getProducts()->with('unit')->paginate($this->perPage);
        $categories = $this->getCategories();
        $units = $this->getUnits();

        return view('livewire.product-table', [
            'products' => $products,
            'categories' => $categories,
            'units' => $units
        ]);
    }
}
