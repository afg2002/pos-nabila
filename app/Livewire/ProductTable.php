<?php

namespace App\Livewire;

use App\AuditLog;
use App\Product;
use App\Models\ProductUnit;
use App\Models\Category;
use App\Shared\Traits\WithAlerts;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProductTable extends Component
{
    use AuthorizesRequests, WithAlerts, WithFileUploads, WithPagination;

    // Properties untuk search dan filter
    public $search = '';

    public $category = '';

    public $status = '';

    public $showDeleted = false;

    public $sortField = 'name';

    public $sortDirection = 'asc';

    public $perPage = 10;

    // Properties for warehouse columns toggle
    public $showWarehouseColumns = false;

    // Cache warehouse data for performance
    protected $warehouses = [];
    protected $warehouseStocksCache = [];

    // Properties untuk modal
    public $showModal = false;

    public $showDetailModal = false;

    public $showDeleteModal = false;

    public $showBulkDeleteModal = false;

    public $showRestoreModal = false;

    public $showBulkRestoreModal = false;

    public $editMode = false;

    public $productId = null;

    public $selectedProduct = null;

    public $deleteProductId = null;

    public $deleteProductName = null;

    public $restoreProductId = null;

    public $restoreProductName = null;

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


    public $default_price_type = 'retail';

    public $productStatus = 'active';

    // ===== Stok awal saat tambah produk =====
    public $initial_stock = 0;
    public $initial_warehouse_id = '';

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
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'productSaved' => 'refreshProducts',
        'productDeleted' => 'refreshProducts',
        // Refresh stok setelah komponen StockForm melakukan update
        'stock-updated' => 'refreshProducts',
    ];

    public function rules()
    {
        return [
            'sku' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($this->productId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($this->productId),
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
                        'default_price_type' => 'required|in:retail,semi_grosir,grosir,custom',
            'productStatus' => 'required|in:active,inactive,discontinued',
            // stok awal opsional; validasi tambahan akan dijalankan saat create
            'initial_stock' => 'nullable|numeric|min:0',
            'initial_warehouse_id' => 'nullable|exists:warehouses,id',
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

    /**
     * Toggle warehouse columns view
     */
    public function toggleWarehouseColumns()
    {
        $this->showWarehouseColumns = !$this->showWarehouseColumns;
        $this->resetPage(); // Reset pagination when changing view
        $this->warehouseStocksCache = []; // Clear cache when toggling
    }

    /**
     * Get warehouses for column headers
     */
    public function getWarehouses()
    {
        if (empty($this->warehouses)) {
            $this->warehouses = \App\Warehouse::ordered()->get();
        }
        return $this->warehouses;
    }

    /**
     * Get warehouse stock for a product with caching
     */
    public function getWarehouseStock($productId, $warehouseId)
    {
        $key = "{$productId}-{$warehouseId}";
        
        if (!isset($this->warehouseStocksCache[$key])) {
            $stock = \App\ProductWarehouseStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->value('stock_on_hand') ?? 0;
            
            $this->warehouseStocksCache[$key] = $stock;
        }
        
        return $this->warehouseStocksCache[$key];
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
        $this->default_price_type = $product->default_price_type;
        $this->productStatus = $product->status;
        // stok awal tidak relevan saat edit
        $this->initial_stock = 0;
        $this->initial_warehouse_id = '';
        $this->editMode = true;
        $this->showModal = true;
    }

    public function openDetailModal($productId)
    {
        $product = Product::with(['unit', 'stockMovements' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($productId);
        $this->selectedProduct = $product;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedProduct = null;
    }

    public function resetForm()
    {
        $this->productId = null;
        $this->sku = '';
        $this->barcode = '';
        $this->name = '';
        $this->categoryInput = '';
        $this->photo = null;
        $this->currentPhoto = null;
        $this->unit_id = '';
        $this->base_cost = '';
        $this->price_retail = '';
        $this->price_semi_grosir = '';
        $this->price_grosir = '';
        $this->default_price_type = 'retail';
        $this->productStatus = 'active';
        // reset stok awal
        $this->initial_stock = 0;
        $this->initial_warehouse_id = '';
        $this->resetValidation();
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
                                'default_price_type' => $this->default_price_type,
                'status' => $this->productStatus,
            ];

            // Auto-generate SKU when empty for create, keep original on edit
            if (! $this->editMode) {
                if (!isset($data['sku']) || trim((string) $data['sku']) === '') {
                    $data['sku'] = Product::generateSku($this->categoryInput, $this->name);
                }
            } else {
                if (!isset($data['sku']) || trim((string) $data['sku']) === '') {
                    // Preserve existing SKU when editing if left blank
                    $data['sku'] = $product->sku;
                }
            }

            // Handle photo upload
            if ($this->photo) {
                // Create products directory if it doesn't exist
                if (! Storage::disk('public')->exists('products')) {
                    Storage::disk('public')->makeDirectory('products');
                }

                // Delete old photo if editing and has current photo
                if ($this->editMode && $this->currentPhoto) {
                    Storage::disk('public')->delete('products/'.$this->currentPhoto);
                }

                // Store new photo
                $filename = time().'_'.$this->photo->getClientOriginalName();
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

                // Jika stok awal diisi (>0), wajib pilih gudang dan catat movement IN
                if ((float) $this->initial_stock > 0) {
                    $this->validate([
                        'initial_warehouse_id' => 'required|exists:warehouses,id',
                    ]);

                    // Hitung stok sebelum dan sesudah untuk gudang terpilih
                    $stockBefore = \App\ProductWarehouseStock::where('product_id', $product->id)
                        ->where('warehouse_id', (int) $this->initial_warehouse_id)
                        ->value('stock_on_hand') ?? 0;
                    $stockAfter = $stockBefore + (int) $this->initial_stock;

                    // Buat movement stok masuk (IN) sebagai stok awal dengan stock_before/after
                    \App\StockMovement::createMovement(
                        $product->id,
                        (int) $this->initial_stock,
                        'IN',
                        [
                            'note' => 'Stok awal saat tambah produk',
                            'reason_code' => 'INIT',
                            'warehouse_id' => (int) $this->initial_warehouse_id,
                            'stock_before' => (int) $stockBefore,
                            'stock_after' => (int) $stockAfter,
                        ]
                    );
                }

                session()->flash('message', 'Produk berhasil ditambahkan!');
            }

            // Clear cache setelah save
            $this->clearProductCache();

            $this->closeModal();
            $this->dispatch('productSaved');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Confirm soft delete product
     */
    public function confirmSoftDelete($productId)
    {
        $product = Product::findOrFail($productId);
        $this->deleteProductId = $productId;
        $this->deleteProductName = $product->name;
        $this->showDeleteModal = true;
    }

    /**
     * Close delete modal
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteProductId = null;
        $this->deleteProductName = null;
    }

    /**
     * Delete product
     */
    public function deleteProduct()
    {
        if (!$this->deleteProductId) {
            return;
        }

        try {
            $product = Product::findOrFail($this->deleteProductId);
            $this->authorize('delete', $product);

            $oldData = $product->toArray();
            $product->softDeleteWithStatus();

            // Log audit
            AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());

            session()->flash('message', 'Produk berhasil dihapus!');
            $this->closeDeleteModal();
            $this->dispatch('productDeleted');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
            $this->closeDeleteModal();
        }
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    /**
     * Confirm restore product
     */
    public function confirmRestore($productId)
    {
        $product = Product::withTrashed()->findOrFail($productId);
        $this->restoreProductId = $productId;
        $this->restoreProductName = $product->name;
        $this->showRestoreModal = true;
    }

    /**
     * Close restore modal
     */
    public function closeRestoreModal()
    {
        $this->showRestoreModal = false;
        $this->restoreProductId = null;
        $this->restoreProductName = null;
    }

    /**
     * Restore product
     */
    public function restoreProduct()
    {
        if (!$this->restoreProductId) {
            return;
        }

        try {
            $product = Product::withTrashed()->findOrFail($this->restoreProductId);
            $this->authorize('restore', $product);

            $oldData = $product->toArray();
            $product->restoreWithStatus();

            // Log audit
            AuditLog::logUpdate('products', $product->id, $oldData, $product->fresh()->toArray());

            session()->flash('message', 'Produk berhasil dikembalikan!');
            $this->closeRestoreModal();
            $this->dispatch('productRestored');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
            $this->closeRestoreModal();
        }
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
        $this->showBulkDeleteModal = true;
    }

    /**
     * Close bulk delete modal
     */
    public function closeBulkDeleteModal()
    {
        $this->showBulkDeleteModal = false;
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
        $this->showBulkRestoreModal = true;
    }

    /**
     * Close bulk restore modal
     */
    public function closeBulkRestoreModal()
    {
        $this->showBulkRestoreModal = false;
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $products = $this->getProducts();
            $this->selectedProducts = $products->pluck('id')->toArray();
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

    


    public function exportProducts()
    {
        $this->authorize('export', Product::class);

        try {
            $products = $this->getProducts()->get();

            $export = new \App\Exports\ProductExport($products);

            return \Maatwebsite\Excel\Facades\Excel::download(
                $export,
                'products_'.now()->format('Y-m-d_H-i-s').'.xlsx'
            );

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data: '.$e->getMessage());
        }
    }

    public function refreshProducts()
    {
        // Clear product-related caches
        cache()->forget('product_categories');
        cache()->forget('product_units');

        // Clear product listing cache with wildcard pattern
        $this->clearProductCache();

        // Clear warehouse stock cache so tabel stok terupdate
        $this->warehouseStocksCache = [];

        $this->resetPage();
        session()->flash('success', 'Data produk berhasil diperbarui!');
    }

    /**
     * Clear product-related cache
     */
    private function clearProductCache()
    {
        // Clear specific cache keys that we know exist
        cache()->forget('product_categories');
        cache()->forget('product_units');
        
        // Clear any product listing cache by using a version key
        $versionKey = 'products_cache_version';
        cache()->increment($versionKey);
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
            'newUnitDescription' => 'nullable|string|max:255',
        ];

        // Add unique validation rules based on edit mode
        if ($this->editingUnitId) {
            $rules['newUnitName'] .= '|unique:product_units,name,'.$this->editingUnitId;
            $rules['newUnitAbbreviation'] .= '|unique:product_units,abbreviation,'.$this->editingUnitId;
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
                'is_active' => true,
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        } finally {
            $this->confirmingUnitDelete = false;
            $this->unitToDelete = null;
        }
    }

    /**
     * Per-product unit scales management
     */
    public function addUnitScale(int $productId, int $unitId, $qty): void
    {
        $qty = (float) $qty;
        // Enforce a sensible minimum conversion value
        if ($qty < 0.0001) {
            session()->flash('error', 'Konversi minimal adalah 0.0001 dan harus lebih dari 0.');
            return;
        }
        $product = Product::findOrFail($productId);
        if ($unitId === (int) $product->unit_id) {
            session()->flash('error', 'Unit dasar produk tidak perlu ditambahkan.');
            return;
        }
        // Ensure unit exists and is active
        $unit = \App\ProductUnit::where('id', $unitId)->where('is_active', true)->first();
        if (! $unit) {
            session()->flash('error', 'Unit tidak valid atau tidak aktif.');
            return;
        }

        // Prevent duplicate scales per product
        $exists = \App\ProductUnitScale::where('product_id', $product->id)
            ->where('unit_id', $unitId)
            ->exists();
        if ($exists) {
            session()->flash('error', 'Satuan tersebut sudah dikonfigurasi untuk produk ini. Gunakan edit untuk mengubah konversi.');
            return;
        }

        \App\ProductUnitScale::create([
            'product_id' => $product->id,
            'unit_id' => $unitId,
            'to_base_qty' => $qty,
        ]);

        // Refresh products list to reflect changes
        $this->refreshProducts();
        session()->flash('message', 'Satuan tambahan berhasil disimpan.');
    }

    public function updateUnitScale(int $scaleId, $qty): void
    {
        $qty = (float) $qty;
        if ($qty < 0.0001) {
            session()->flash('error', 'Konversi minimal adalah 0.0001 dan harus lebih dari 0.');
            return;
        }
        $scale = \App\ProductUnitScale::findOrFail($scaleId);
        // Extra safeguard: prevent accidentally updating a base unit as a scale
        $product = Product::find($scale->product_id);
        if ($product && (int) $scale->unit_id === (int) $product->unit_id) {
            session()->flash('error', 'Unit dasar tidak boleh dikonfigurasi sebagai skala.');
            return;
        }
        $scale->update(['to_base_qty' => $qty]);
        $this->refreshProducts();
        session()->flash('message', 'Konversi satuan diperbarui.');
    }

    public function removeUnitScale(int $scaleId): void
    {
        $scale = \App\ProductUnitScale::findOrFail($scaleId);
        $scale->delete();
        $this->refreshProducts();
        session()->flash('message', 'Satuan tambahan dihapus.');
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
                'updated_at', 'deleted_at',
            ])
            ->with([
                'unit:id,name,abbreviation',
                'unitScales' => function ($q) {
                    $q->select('id', 'product_id', 'unit_id', 'to_base_qty');
                },
                'unitScales.unit:id,name,abbreviation',
            ]);

        // Eager load warehouse stocks when columns are shown
        if ($this->showWarehouseColumns) {
            $query->with(['warehouseStocks' => function ($q) {
                $q->select('product_id', 'warehouse_id', 'stock_on_hand')
                  ->with('warehouse:id,code,name');
            }]);
        }

        // Include soft deleted if requested
        if ($this->showDeleted) {
            $query->withTrashed();
        }

        // Search with optimized query
        if ($this->search) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
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
                ->get();
        });

        return $units;
    }

    public function getMasterCategories()
    {
        // Use master Category table for selection options in add/edit forms
        return cache()->remember('master_categories', 600, function () {
            return Category::query()
                ->select('name')
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name');
        });
    }

    public function render()
    {
        // Products already have unit relationship loaded via eager loading
        $products = $this->getProducts();
        $categories = $this->getCategories();
        $units = $this->getUnits();
        $warehouses = $this->getWarehouses();
        $masterCategories = $this->getMasterCategories();

        return view('livewire.product-table', [
            'products' => $products,
            'categories' => $categories,
            'units' => $units,
            'warehouses' => $warehouses,
            'masterCategories' => $masterCategories,
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
                    'percentage_change' => $currentPrice > 0 ? (($newPrice - $currentPrice) / $currentPrice) * 100 : 0,
                ];
            }

            if (empty($this->bulkUpdatePreview)) {
                session()->flash('error', 'Tidak ada produk aktif yang ditemukan untuk kategori ini.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
                        $this->bulkPriceField => $preview['new_price'],
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
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
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
            'set_price' => 'Set Harga Baru (Rp)',
        ];
    }
}
