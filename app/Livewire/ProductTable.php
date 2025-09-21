<?php

namespace App\Livewire;

use App\Product;
use App\ProductUnit;
use App\AuditLog;
use App\Shared\Traits\WithAlerts;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class ProductTable extends Component
{
    use WithPagination, AuthorizesRequests, WithFileUploads, WithAlerts;

    // Properties untuk search dan filter
    public $search = '';
    public $category = '';
    public $status = '';
    public $showDeleted = false;
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
    public $productStatus = 'active';

    // Properties untuk unit management
    public $showUnitModal = false;
    public $editingUnitId = null;
    public $newUnitName = '';
    public $newUnitAbbreviation = '';
    public $newUnitDescription = '';
    public $confirmingUnitDelete = false;
    public $unitToDelete = null;

    // Properties untuk bulk actions
    public $selectedProducts = [];
    public $selectAll = false;
    public $bulkPriceType = 'retail';
    
    // Properties untuk bulk price update
    public $showBulkPriceModal = false;
    public $bulkUpdateCategory = '';
    public $bulkUpdateType = 'percentage'; // percentage, fixed_amount, set_price
    public $bulkUpdateValue = 0;
    public $bulkPriceField = 'price_retail'; // price_retail, price_semi_grosir, price_grosir
    public $bulkUpdatePreview = [];

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
            'productStatus' => 'required|in:active,inactive,discontinued'
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
        $this->productStatus = $product->status;
        
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

    /**
     * Confirm remove product photo
     */
    public function confirmRemoveProductPhoto()
    {
        $this->showConfirm(
            'Hapus Foto Produk',
            'Yakin ingin menghapus foto ini?',
            'removeProductPhoto',
            [],
            'Ya, hapus!',
            'Batal'
        );
    }

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
                'status' => $this->productStatus
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

            // Clear cache setelah save
            $this->clearProductCache();
            
            $this->closeModal();
            $this->dispatch('productSaved');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Confirm soft delete product
     */
    public function confirmSoftDelete($productId)
    {
        $product = Product::findOrFail($productId);
        $this->showConfirm(
            'Hapus Produk',
            "Yakin ingin menghapus produk '{$product->name}'? (Dapat dikembalikan)",
            'softDelete',
            ['productId' => $productId],
            'Ya, hapus!',
            'Batal'
        );
    }

    /**
     * Soft delete product with status update
     */
    public function softDelete($params)
    {
        $productId = $params['productId'];
        try {
            $product = Product::findOrFail($productId);
            
            // Check authorization
            $this->authorize('delete', $product);
            
            $oldData = $product->toArray();
            
            // Soft delete with status update
            $product->softDeleteWithStatus();
            
            // Log audit
            AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
            
            session()->flash('message', 'Produk berhasil dihapus (soft delete)!');
            $this->dispatch('productDeleted');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm force delete product
     */
    public function confirmForceDelete($productId)
    {
        $product = Product::withTrashed()->findOrFail($productId);
        $this->showConfirm(
            'Hapus Permanen',
            "PERINGATAN: Ini akan menghapus produk '{$product->name}' secara permanen! Yakin ingin melanjutkan?",
            'forceDelete',
            ['productId' => $productId],
            'Ya, hapus permanen!',
            'Batal'
        );
    }
    
    /**
     * Force delete product (permanent)
     */
    public function forceDelete($params)
    {
        $productId = $params['productId'];
        try {
            $product = Product::withTrashed()->findOrFail($productId);
            
            // Check authorization
            $this->authorize('forceDelete', $product);
            
            $productData = $product->toArray();
            
            // Force delete (permanent)
            $product->forceDelete();
            
            // Log audit
            AuditLog::logDelete('products', $productId, $productData);
            
            session()->flash('message', 'Produk berhasil dihapus permanen!');
            $this->dispatch('productDeleted');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirm restore product
     */
    public function confirmRestore($productId)
    {
        $product = Product::withTrashed()->findOrFail($productId);
        $this->showConfirm(
            'Pulihkan Produk',
            "Yakin ingin memulihkan produk '{$product->name}'?",
            'restore',
            ['productId' => $productId],
            'Ya, pulihkan!',
            'Batal'
        );
    }
    
    /**
     * Restore soft deleted product
     */
    public function restore($params)
    {
        $productId = $params['productId'];
        try {
            $product = Product::withTrashed()->findOrFail($productId);
            
            // Check authorization
            $this->authorize('restore', $product);
            
            $oldData = $product->toArray();
            
            // Restore with active status
            $product->restoreWithStatus();
            
            // Log audit
            AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
            
            session()->flash('message', 'Produk berhasil dipulihkan!');
            $this->dispatch('productRestored');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Update product status
     */
    public function updateStatus($productId, $status)
    {
        try {
            $product = Product::findOrFail($productId);
            
            // Check authorization
            $this->authorize('update', $product);
            
            $oldData = $product->toArray();
            $product->update(['status' => $status]);
            
            // Log audit
            AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
            
            $statusName = $product->getStatusDisplayName();
            session()->flash('message', "Status produk berhasil diubah ke {$statusName}!");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }



    /**
     * Confirm bulk delete products
     */
    public function confirmBulkDelete()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Pilih produk yang akan dihapus!');
            return;
        }

        $count = count($this->selectedProducts);
        $this->showConfirm(
            'Hapus Produk Massal',
            "Yakin ingin menghapus {$count} produk terpilih?",
            'bulkDelete',
            [],
            'Ya, hapus!',
            'Batal'
        );
    }

    /**
     * Confirm bulk restore products
     */
    public function confirmBulkRestore()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Pilih produk yang akan dikembalikan!');
            return;
        }

        $count = count($this->selectedProducts);
        $this->showConfirm(
            'Kembalikan Produk Massal',
            "Yakin ingin mengembalikan {$count} produk terpilih?",
            'bulkRestore',
            [],
            'Ya, kembalikan!',
            'Batal'
        );
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
            }
            
            $deletedCount = 0;
            foreach ($products as $product) {
                $oldData = $product->toArray();
                
                // Use soft delete with status update (same as individual delete)
                $product->softDeleteWithStatus();
                
                // Log audit
                AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
                
                $deletedCount++;
            }
            
            session()->flash('message', "{$deletedCount} produk berhasil dihapus (soft delete)!");
            $this->dispatch('productDeleted');
            
            $this->selectedProducts = [];
            $this->selectAll = false;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bulkRestore()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Pilih produk yang akan dikembalikan!');
            return;
        }

        try {
            $products = Product::withTrashed()->whereIn('id', $this->selectedProducts)->get();
            
            foreach ($products as $product) {
                // Check authorization
                $this->authorize('restore', $product);
            }
            
            $restoredCount = 0;
            foreach ($products as $product) {
                $oldData = $product->toArray();
                
                // Use restore with status update
                $product->restoreWithStatus();
                
                // Log audit
                AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
                
                $restoredCount++;
            }
            
            session()->flash('message', "{$restoredCount} produk berhasil dikembalikan!");
            $this->dispatch('productRestored');
            
            $this->selectedProducts = [];
            $this->selectAll = false;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Confirm bulk hard delete products
     */
    public function confirmBulkHardDelete()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Pilih produk yang akan dihapus permanen!');
            return;
        }

        $count = count($this->selectedProducts);
        $this->showConfirm(
            'Hapus Permanen Produk Massal',
            "PERINGATAN: Yakin ingin menghapus permanen {$count} produk terpilih? Data tidak dapat dikembalikan!",
            'bulkHardDelete',
            [],
            'Ya, hapus permanen!',
            'Batal'
        );
    }

    /**
     * Bulk hard delete products (permanent deletion)
     */
    public function bulkHardDelete()
    {
        if (empty($this->selectedProducts)) {
            session()->flash('error', 'Pilih produk yang akan dihapus permanen!');
            return;
        }

        try {
            $products = Product::withTrashed()->whereIn('id', $this->selectedProducts)->get();
            
            foreach ($products as $product) {
                // Check authorization for force delete
                $this->authorize('forceDelete', $product);
            }
            
            $deletedCount = 0;
            foreach ($products as $product) {
                $oldData = $product->toArray();
                
                // Log audit before permanent deletion
                AuditLog::logDelete('products', $product->id, $oldData);
                
                // Permanent delete
                $product->forceDelete();
                
                $deletedCount++;
            }
            
            session()->flash('message', "{$deletedCount} produk berhasil dihapus permanen!");
            $this->dispatch('productHardDeleted');
            
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
        $this->productStatus = 'active';
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
        // Clear product-related caches
        cache()->forget('product_categories');
        cache()->forget('product_units');
        
        // Clear product listing cache with wildcard pattern
        $this->clearProductCache();
        
        $this->resetPage();
        session()->flash('success', 'Data produk berhasil diperbarui!');
    }
    
    /**
     * Clear product-related cache
     */
    private function clearProductCache()
    {
        // Clear all product cache keys
        $cacheKeys = cache()->getRedis()->keys('*products_*');
        if ($cacheKeys) {
            cache()->getRedis()->del($cacheKeys);
        }
    }

    // Unit Management Methods
    public function openUnitModal()
    {
        $this->resetUnitForm();
        $this->editingUnitId = null;
        $this->showUnitModal = true;
    }

    public function openEditUnitModal($unitId)
    {
        $unit = ProductUnit::findOrFail($unitId);
        $this->editingUnitId = $unit->id;
        $this->newUnitName = $unit->name;
        $this->newUnitAbbreviation = $unit->abbreviation;
        $this->newUnitDescription = $unit->description;
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
        $this->editingUnitId = null;
        $this->newUnitName = '';
        $this->newUnitAbbreviation = '';
        $this->newUnitDescription = '';
    }

    public function saveUnit()
    {
        $rules = [
            'newUnitName' => 'required|string|max:50',
            'newUnitAbbreviation' => 'required|string|max:10',
            'newUnitDescription' => 'nullable|string|max:255'
        ];

        // Add unique validation rules based on edit mode
        if ($this->editingUnitId) {
            $rules['newUnitName'] .= '|unique:product_units,name,' . $this->editingUnitId;
            $rules['newUnitAbbreviation'] .= '|unique:product_units,abbreviation,' . $this->editingUnitId;
        } else {
            $rules['newUnitName'] .= '|unique:product_units,name';
            $rules['newUnitAbbreviation'] .= '|unique:product_units,abbreviation';
        }

        $this->validate($rules);

        try {
            $data = [
                'name' => $this->newUnitName,
                'abbreviation' => $this->newUnitAbbreviation,
                'description' => $this->newUnitDescription,
                'is_active' => true
            ];

            if ($this->editingUnitId) {
                // Update existing unit
                $unit = ProductUnit::findOrFail($this->editingUnitId);
                $unit->update($data);
                session()->flash('message', 'Unit berhasil diperbarui!');
            } else {
                // Create new unit
                $data['sort_order'] = ProductUnit::max('sort_order') + 1;
                $unit = ProductUnit::create($data);
                
                // Set unit yang baru dibuat sebagai pilihan
                $this->unit_id = $unit->id;
                session()->flash('message', 'Unit berhasil ditambahkan!');
            }
            
            // Clear cache units setelah operasi CRUD
            cache()->forget('product_units');
            
            $this->closeUnitModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function confirmDeleteUnit($unitId)
    {
        $unit = ProductUnit::findOrFail($unitId);
        
        // Check if unit is being used by any products
        $productCount = Product::where('unit_id', $unitId)->count();
        
        if ($productCount > 0) {
            session()->flash('error', "Unit '{$unit->name}' tidak dapat dihapus karena masih digunakan oleh {$productCount} produk.");
            return;
        }

        $this->unitToDelete = $unitId;
        $this->confirmingUnitDelete = true;
        
        $this->showConfirm(
            'Hapus Unit',
            "Yakin ingin menghapus unit '{$unit->name}'?",
            'deleteUnit',
            ['unitId' => $unitId],
            'Ya, hapus!',
            'Batal'
        );
    }

    public function deleteUnit($params)
    {
        $unitId = $params['unitId'];
        
        try {
            $unit = ProductUnit::findOrFail($unitId);
            
            // Double check if unit is being used
            $productCount = Product::where('unit_id', $unitId)->count();
            
            if ($productCount > 0) {
                session()->flash('error', "Unit '{$unit->name}' tidak dapat dihapus karena masih digunakan oleh {$productCount} produk.");
                return;
            }
            
            $unit->delete();
            
            // Clear cache units setelah delete
            cache()->forget('product_units');
            
            session()->flash('message', 'Unit berhasil dihapus!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        } finally {
            $this->confirmingUnitDelete = false;
            $this->unitToDelete = null;
        }
    }

    public function getProducts()
    {
        // Temporarily disable caching to fix serialization issues
        return $this->buildProductQuery()->paginate($this->perPage);
    }

    private function buildProductQuery()
    {
        $query = Product::query()
            ->select([
                'id', 'sku', 'barcode', 'name', 'category', 'photo', 
                'unit_id', 'base_cost', 'price_retail', 'price_semi_grosir', 
                'price_grosir', 'current_stock', 'status', 'created_at', 
                'updated_at', 'deleted_at'
            ])
            ->with(['unit:id,name,abbreviation']);
        
        // Include soft deleted if requested
        if ($this->showDeleted) {
            $query->withTrashed();
        }

        // Search with optimized query
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('sku', 'like', $searchTerm)
                  ->orWhere('barcode', 'like', $searchTerm)
                  ->orWhere('category', 'like', $searchTerm);
            });
        }

        // Filter by category with index
        if ($this->category) {
            $query->where('category', $this->category);
        }

        // Filter by status with optimized conditions
        if ($this->status !== '') {
            switch ($this->status) {
                case 'active':
                    $query->where('status', 'active');
                    break;
                case 'inactive':
                    $query->where('status', 'inactive');
                    break;
                case 'discontinued':
                    $query->where('status', 'discontinued');
                    break;
                case 'deleted':
                    $query->onlyTrashed();
                    break;
            }
        }

        // Sort with index optimization
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query;
    }

    public function getCategories()
    {
        // Cache categories for better performance
        return cache()->remember('product_categories', 300, function () {
            return Product::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->orderBy('category')
                ->pluck('category');
        });
    }

    public function getUnits()
    {
        // Cache units for better performance
        $units = cache()->remember('product_units', 600, function () {
            return ProductUnit::select('id', 'name', 'abbreviation')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->toArray();
        });
        
        return collect($units);
    }

    public function render()
    {
        // Products already have unit relationship loaded via eager loading
        $products = $this->getProducts();
        $categories = $this->getCategories();
        $units = $this->getUnits();

        return view('livewire.product-table', [
            'products' => $products,
            'categories' => $categories,
            'units' => $units
        ]);
    }
    
    // Bulk Price Update Methods
    
    public function openBulkPriceModal()
    {
        $this->resetBulkPriceForm();
        $this->showBulkPriceModal = true;
    }
    
    public function closeBulkPriceModal()
    {
        $this->showBulkPriceModal = false;
        $this->resetBulkPriceForm();
        $this->resetValidation(['bulkUpdateCategory', 'bulkUpdateValue']);
    }
    
    public function resetBulkPriceForm()
    {
        $this->bulkUpdateCategory = '';
        $this->bulkUpdateType = 'percentage';
        $this->bulkUpdateValue = 0;
        $this->bulkPriceField = 'price_retail';
        $this->bulkUpdatePreview = [];
    }
    
    public function generateBulkPricePreview()
    {
        $this->validate([
            'bulkUpdateCategory' => 'required|string',
            'bulkUpdateValue' => 'required|numeric|min:0',
        ]);
        
        try {
            $products = Product::where('category', $this->bulkUpdateCategory)
                ->where('status', 'active')
                ->select('id', 'name', 'sku', $this->bulkPriceField)
                ->get();
                
            $this->bulkUpdatePreview = [];
            
            foreach ($products as $product) {
                $currentPrice = $product->{$this->bulkPriceField};
                $newPrice = $this->calculateNewPrice($currentPrice, $this->bulkUpdateType, $this->bulkUpdateValue);
                
                $this->bulkUpdatePreview[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_price' => $currentPrice,
                    'new_price' => $newPrice,
                    'difference' => $newPrice - $currentPrice,
                    'percentage_change' => $currentPrice > 0 ? (($newPrice - $currentPrice) / $currentPrice) * 100 : 0
                ];
            }
            
            if (empty($this->bulkUpdatePreview)) {
                session()->flash('error', 'Tidak ada produk aktif yang ditemukan untuk kategori ini.');
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function executeBulkPriceUpdate()
    {
        if (empty($this->bulkUpdatePreview)) {
            session()->flash('error', 'Silakan generate preview terlebih dahulu.');
            return;
        }
        
        try {
            $updatedCount = 0;
            
            foreach ($this->bulkUpdatePreview as $preview) {
                $product = Product::find($preview['id']);
                if ($product) {
                    // Check authorization
                    $this->authorize('update', $product);
                    
                    $oldData = $product->toArray();
                    
                    $product->update([
                        $this->bulkPriceField => $preview['new_price']
                    ]);
                    
                    // Log audit
                    AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());
                    
                    $updatedCount++;
                }
            }
            
            // Clear cache setelah bulk update
            $this->clearProductCache();
            
            session()->flash('message', "Berhasil memperbarui harga {$updatedCount} produk.");
            $this->closeBulkPriceModal();
            $this->dispatch('productSaved');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    private function calculateNewPrice($currentPrice, $type, $value)
    {
        switch ($type) {
            case 'percentage':
                return $currentPrice * (1 + ($value / 100));
            case 'fixed_amount':
                return $currentPrice + $value;
            case 'set_price':
                return $value;
            default:
                return $currentPrice;
        }
    }
    
    public function getBulkUpdateTypeOptions()
    {
        return [
            'percentage' => 'Persentase (%)',
            'fixed_amount' => 'Jumlah Tetap (Rp)',
            'set_price' => 'Set Harga Baru (Rp)'
        ];
    }
    



}
