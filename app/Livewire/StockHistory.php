<?php

namespace App\Livewire;

use App\Product;
use App\Shared\Traits\WithAlerts;
use App\StockMovement;
use App\Warehouse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class StockHistory extends Component
{
    use AuthorizesRequests, WithAlerts, WithPagination;

    public $search = '';

    public $productFilter = '';

    public $movementTypeFilter = '';

    public $warehouseFilter = '';

    public $warehouses = [];

    public $dateFrom = '';

    public $dateTo = '';

    public $perPage = 10;

    // Computed properties
    protected $computedPropertyCache = [];

    public $reasonCodeFilter = '';

    public $products = [];

    // Modal properties
    public $showDetailModal = false;

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $selectedMovement = null;

    // Edit form properties
    public $editQty = '';

    public $editNotes = '';

    protected $listeners = ['stock-updated' => '$refresh'];

    // Define computed properties
    public function getComputedPropertyNames()
    {
        return ['stockIn', 'stockOut', 'netMovement'];
    }

    public function mount()
    {
        $this->products = Product::where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
        $this->warehouses = Warehouse::ordered()->get();

        // Set default date range (last 30 days)
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingProductFilter()
    {
        $this->resetPage();
    }

    public function updatingMovementTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingWarehouseFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->warehouseFilter = '';
        $this->search = '';
        $this->productFilter = '';
        $this->movementTypeFilter = '';

        $this->reasonCodeFilter = '';
        $this->dateTo = now()->format('Y-m-d');
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->resetPage();
    }

    public function refreshData()
    {
        // Force refresh the component data
        $this->resetPage();
        session()->flash('message', 'Data berhasil diperbarui!');
    }

    public function exportData()
    {
        // Check authorization
        $this->authorize('export', StockMovement::class);

        try {
            $fileName = 'stock-movement-history-'.now()->format('Y-m-d-H-i-s').'.xlsx';

            return Excel::download(
                new \App\Exports\StockMovementExport(
                    $this->dateFrom,
                    $this->dateTo,
                    $this->productFilter ?: null,
                    $this->movementTypeFilter ? strtoupper($this->movementTypeFilter) : null,
                    null,
                    $this->warehouseFilter ?: null,
                    $this->reasonCodeFilter ?: null
                ),
                $fileName
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data: '.$e->getMessage());
        }
    }

    public function openDetailModal($movementId)
    {
        $this->selectedMovement = StockMovement::with(['product', 'user', 'warehouse'])->find($movementId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedMovement = null;
    }

    public function openEditModal($movementId)
    {
        try {
            $this->selectedMovement = StockMovement::with(['product', 'warehouse'])->find($movementId);

            if (!$this->selectedMovement) {
                session()->flash('error', 'Pergerakan stok tidak ditemukan.');
                return;
            }

            // Only allow editing manual movements
            if ($this->selectedMovement->ref_type !== 'manual') {
                session()->flash('error', 'Hanya pergerakan stok manual yang dapat diedit.');
                return;
            }

            // Check authorization
            $this->authorize('update', $this->selectedMovement);

            // Fill edit form
            $this->editQty = abs($this->selectedMovement->qty); // Always show positive value
            $this->editNotes = $this->selectedMovement->note;

            $this->showEditModal = true;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengedit pergerakan stok ini.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            \Log::error('Error opening edit modal: ' . $e->getMessage(), [
                'movement_id' => $movementId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedMovement = null;
        $this->editQty = '';
        $this->editNotes = '';
    }

    public function updateMovement()
    {
        try {
            $this->validate([
                'editQty' => 'required|numeric|min:1',
                'editNotes' => 'nullable|string|max:255',
            ]);

            if (!$this->selectedMovement) {
                session()->flash('error', 'Tidak ada pergerakan stok yang dipilih.');
                return;
            }

            // Only allow updating manual movements
            if ($this->selectedMovement->ref_type !== 'manual') {
                session()->flash('error', 'Hanya pergerakan stok manual yang dapat diperbarui.');
                return;
            }

            $this->authorize('update', $this->selectedMovement);

            DB::beginTransaction();

            $movement = $this->selectedMovement;
            $product = $movement->product;

            // Calculate old and new stock changes
            $oldQtyChange = $movement->qty;
            $newQtyChange = $movement->type === 'IN' ? $this->editQty : -$this->editQty;

            // For adjustment type, calculate differently
            if ($movement->type === 'ADJ') {
                // For adjustment, we need to reverse the old adjustment and apply new one
                $currentStock = $product->current_stock;
                $stockBeforeOldAdjustment = $currentStock - $oldQtyChange;
                $newQtyChange = $this->editQty - $stockBeforeOldAdjustment;
            }

            // Validate stock for OUT movements
            if ($movement->type === 'OUT') {
                $availableStock = $product->current_stock - $oldQtyChange; // Remove old effect
                if ($availableStock < $this->editQty) {
                    $this->addError('editQty', 'Stok tidak mencukupi. Stok tersedia: '.$availableStock);
                    DB::rollBack();
                    return;
                }
            }

            // Update product stock
            $stockDifference = $newQtyChange - $oldQtyChange;
            $product->update([
                'current_stock' => $product->current_stock + $stockDifference,
            ]);

            // Update movement record
            $movement->update([
                'qty' => $newQtyChange,
                'note' => $this->editNotes,
                'updated_at' => now(),
            ]);

            // Log audit
            if (class_exists('\App\AuditLog')) {
                \App\AuditLog::logUpdate(
                    'stock_movements',
                    $movement->id,
                    ['qty' => $oldQtyChange, 'note' => $movement->note],
                    ['qty' => $newQtyChange, 'note' => $this->editNotes],
                    auth()->id()
                );
            }

            DB::commit();

            session()->flash('message', 'Pergerakan stok berhasil diperbarui!');
            $this->closeEditModal();
            $this->dispatch('stock-updated');

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            session()->flash('error', 'Anda tidak memiliki izin untuk memperbarui pergerakan stok ini.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Validation exceptions are handled automatically by Livewire
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
            \Log::error('Error updating stock movement: ' . $e->getMessage(), [
                'movement_id' => $this->selectedMovement?->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function confirmDeleteMovement($movementId)
    {
        $this->selectedMovement = StockMovement::find($movementId);
        
        if (!$this->selectedMovement) {
            $this->addError('general', 'Stock movement tidak ditemukan.');
            return;
        }

        // Check authorization
        if (!auth()->user()->can('delete', $this->selectedMovement)) {
            $this->addError('general', 'Anda tidak memiliki izin untuk menghapus stock movement ini.');
            return;
        }

        // Check if it's a manual movement
        if (!$this->selectedMovement->is_manual) {
            $this->addError('general', 'Hanya stock movement manual yang dapat dihapus.');
            return;
        }

        $this->showDeleteModal = true;
    }

    public function deleteMovement()
    {
        if (!$this->selectedMovement) {
            $this->addError('general', 'Stock movement tidak ditemukan.');
            return;
        }

        try {
            DB::beginTransaction();

            $movement = $this->selectedMovement;
            $product = $movement->product;

            // Check if deleting this movement would result in negative stock
            $currentStock = $product->current_stock ?? 0;
            $qtyChange = $movement->type === 'IN' ? -$movement->qty : $movement->qty;
            $newStock = $currentStock + $qtyChange;

            if ($newStock < 0) {
                $this->addError('general', 'Tidak dapat menghapus pergerakan ini karena akan mengakibatkan stok negatif.');
                DB::rollBack();
                return;
            }

            // Update product's current stock
            $product->update(['current_stock' => $newStock]);

            // Log audit before deletion
            if (class_exists('\App\AuditLog')) {
                \App\AuditLog::logDelete(
                    'stock_movements',
                    $movement->id,
                    [
                        'product_id' => $movement->product_id,
                        'type' => $movement->type,
                        'qty' => $movement->qty,
                        'note' => $movement->note,
                    ],
                    auth()->id()
                );
            }

            // Delete the movement
            $movement->delete();

            DB::commit();

            $this->showDeleteModal = false;
            $this->selectedMovement = null;
            $this->addSuccess('Stock movement berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Terjadi kesalahan saat menghapus stock movement: ' . $e->getMessage());
            \Log::error('Error deleting stock movement: ' . $e->getMessage(), [
                'movement_id' => $this->selectedMovement->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->selectedMovement = null;
    }

    public function getStockInProperty()
    {
        return $this->getTotalIn();
    }

    public function getStockOutProperty()
    {
        return $this->getTotalOut();
    }

    public function getNetMovementProperty()
    {
        return $this->getNetMovement();
    }

    public function render()
    {
        // Create cache key based on filters
        $cacheKey = 'stock_movements_'.md5(serialize([
            'search' => $this->search,
            'productFilter' => $this->productFilter,
            'movementTypeFilter' => $this->movementTypeFilter,
            'warehouseFilter' => $this->warehouseFilter,
            'reasonCodeFilter' => $this->reasonCodeFilter,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'page' => $this->getPage(),
            'perPage' => $this->perPage,
        ]));

        // For search queries or date filters, don't cache to ensure real-time results
        if ($this->search || $this->dateFrom || $this->dateTo) {
            $movements = $this->buildStockMovementQuery()->paginate($this->perPage);
        } else {
            // Cache for 3 minutes for non-search/non-filtered requests
            $movements = cache()->remember($cacheKey, 180, function () {
                return $this->buildStockMovementQuery()->paginate($this->perPage);
            });
        }

        return view('livewire.stock-history', [
            'movements' => $movements,
            'warehouses' => $this->warehouses,
            'products' => $this->getProducts(),
            'reasonCodes' => $this->getReasonCodes(),
            'movementTypes' => $this->getMovementTypes(),
            'totalMovements' => $movements->total(),
        ]);
    }

    private function buildStockMovementQuery()
    {
        return StockMovement::with(['product', 'performedBy', 'approvedBy', 'warehouse'])
            ->whereHas('product', function ($productQuery) {
                $productQuery->whereNull('deleted_at');
            })
            ->when($this->search, function ($q) {
                $q->whereHas('product', function ($productQuery) {
                    $productQuery->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%');
                })
                    ->orWhere('note', 'like', '%'.$this->search.'%');
            })
            ->when($this->productFilter, function ($q) {
                $q->where('product_id', $this->productFilter);
            })
            ->when($this->movementTypeFilter, function ($q) {
                $q->where('type', strtoupper($this->movementTypeFilter));
            })
            ->when($this->reasonCodeFilter, function ($q) {
                $q->where('reason_code', $this->reasonCodeFilter);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->warehouseFilter, function ($q) {
                $q->where('warehouse_id', $this->warehouseFilter);
            })
            ->orderBy('created_at', 'desc');
    }

    private function getProducts()
    {
        return Product::where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    private function getReasonCodes()
    {
        return [
            'manual' => 'Manual Adjustment',
            'sale' => 'Sale',
            'purchase' => 'Purchase',
            'return' => 'Return',
            'damage' => 'Damage',
            'expired' => 'Expired',
            'transfer' => 'Transfer',
            'other' => 'Other',
        ];
    }

    private function getMovementTypes()
    {
        return [
            'IN' => 'Stock In',
            'OUT' => 'Stock Out',
            'ADJ' => 'Adjustment',
        ];
    }

    public function getTotalIn()
    {
        return StockMovement::where('type', 'IN')
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->warehouseFilter, function ($q) {
                $q->where('warehouse_id', $this->warehouseFilter);
            })
            ->sum('qty');
    }

    public function getTotalOut()
    {
        return abs(StockMovement::where('type', 'OUT')
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->warehouseFilter, function ($q) {
                $q->where('warehouse_id', $this->warehouseFilter);
            })
            ->sum('qty'));
    }

    public function getNetMovement()
    {
        return $this->getTotalIn() - $this->getTotalOut();
    }
}
