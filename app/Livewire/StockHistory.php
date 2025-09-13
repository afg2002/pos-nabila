<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Product;
use App\StockMovement;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StockHistory extends Component
{
    use WithPagination, AuthorizesRequests;
    
    public $search = '';
    public $productFilter = '';
    public $movementTypeFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 10;
    
    public $products = [];
    
    // Modal properties
    public $showDetailModal = false;
    public $showEditModal = false;
    public $selectedMovement = null;
    
    // Edit form properties
    public $editQty = '';
    public $editNotes = '';
    
    protected $listeners = ['stock-updated' => '$refresh'];
    
    public function mount()
    {
        $this->products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();
            
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
        $this->search = '';
        $this->productFilter = '';
        $this->movementTypeFilter = '';
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
            $fileName = 'stock-movement-history-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
            
            return Excel::download(
                new \App\Exports\StockMovementExport(
                    $this->dateFrom,
                    $this->dateTo,
                    $this->selectedProduct,
                    $this->selectedType,
                    $this->selectedRefType
                ),
                $fileName
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }
    
    public function openDetailModal($movementId)
    {
        $this->selectedMovement = StockMovement::with(['product', 'user'])->find($movementId);
        $this->showDetailModal = true;
    }
    
    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedMovement = null;
    }
    
    public function openEditModal($movementId)
    {
        $this->selectedMovement = StockMovement::with(['product'])->find($movementId);
        
        $this->authorize('update', $this->selectedMovement);
        
        // Only allow editing manual movements
        if ($this->selectedMovement->ref_type !== 'manual') {
            session()->flash('error', 'Hanya pergerakan stok manual yang dapat diedit.');
            return;
        }
        
        // Fill edit form
        $this->editQty = abs($this->selectedMovement->qty); // Always show positive value
        $this->editNotes = $this->selectedMovement->note;
        
        $this->showEditModal = true;
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
        $this->validate([
            'editQty' => 'required|numeric|min:1',
            'editNotes' => 'nullable|string|max:255',
        ]);
        
        $this->authorize('update', $this->selectedMovement);
        
        try {
            DB::beginTransaction();
            
            $movement = $this->selectedMovement;
            $product = $movement->product;
            
            // Calculate old and new stock changes
            $oldQtyChange = $movement->qty;
            $newQtyChange = $movement->type === 'IN' ? $this->editQty : -$this->editQty;
            
            // For adjustment type, calculate differently
            if ($movement->type === 'ADJUSTMENT') {
                // For adjustment, we need to reverse the old adjustment and apply new one
                $currentStock = $product->current_stock;
                $stockBeforeOldAdjustment = $currentStock - $oldQtyChange;
                $newQtyChange = $this->editQty - $stockBeforeOldAdjustment;
            }
            
            // Validate stock for OUT movements
            if ($movement->type === 'OUT') {
                $availableStock = $product->current_stock - $oldQtyChange; // Remove old effect
                if ($availableStock < $this->editQty) {
                    $this->addError('editQty', 'Stok tidak mencukupi. Stok tersedia: ' . $availableStock);
                    return;
                }
            }
            
            // Update product stock
            $stockDifference = $newQtyChange - $oldQtyChange;
            $product->update([
                'current_stock' => $product->current_stock + $stockDifference
            ]);
            
            // Update movement record
            $movement->update([
                'qty' => $newQtyChange,
                'note' => $this->editNotes,
                'updated_at' => now(),
            ]);
            
            // Log audit
            \App\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'model' => 'StockMovement',
                'model_id' => $movement->id,
                'changes' => [
                    'old' => ['qty' => $oldQtyChange, 'note' => $movement->note],
                    'new' => ['qty' => $newQtyChange, 'note' => $this->editNotes]
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            DB::commit();
            
            session()->flash('message', 'Pergerakan stok berhasil diperbarui!');
            $this->closeEditModal();
            $this->dispatch('stock-updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function deleteMovement($movementId)
    {
        try {
            DB::beginTransaction();
            
            $movement = StockMovement::with('product')->find($movementId);
            
            $this->authorize('delete', $movement);
            
            if (!$movement) {
                session()->flash('error', 'Pergerakan stok tidak ditemukan.');
                return;
            }
            
            // Only allow deleting manual movements
            if ($movement->ref_type !== 'manual') {
                session()->flash('error', 'Hanya pergerakan stok manual yang dapat dihapus.');
                return;
            }
            
            // Reverse the stock change
            $product = $movement->product;
            $reversedStock = $product->current_stock - $movement->qty;
            
            // Validate that stock won't go negative
            if ($reversedStock < 0) {
                session()->flash('error', 'Tidak dapat menghapus pergerakan ini karena akan membuat stok menjadi negatif.');
                return;
            }
            
            // Update product stock
            $product->update(['current_stock' => $reversedStock]);
            
            // Log audit before deletion
            \App\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'model' => 'StockMovement',
                'model_id' => $movement->id,
                'changes' => [
                    'deleted' => [
                        'product_id' => $movement->product_id,
                        'type' => $movement->type,
                        'qty' => $movement->qty,
                        'note' => $movement->note,
                    ]
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // Delete the movement
            $movement->delete();
            
            DB::commit();
            
            session()->flash('message', 'Pergerakan stok berhasil dihapus!');
            $this->dispatch('stock-updated');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        $query = StockMovement::with(['product', 'user'])
            ->when($this->search, function ($q) {
                $q->whereHas('product', function ($productQuery) {
                    $productQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%');
                })
                ->orWhere('notes', 'like', '%' . $this->search . '%');
            })
            ->when($this->productFilter, function ($q) {
                $q->where('product_id', $this->productFilter);
            })
            ->when($this->movementTypeFilter, function ($q) {
                $q->where('type', strtoupper($this->movementTypeFilter));
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('created_at', 'desc');
            
        $movements = $query->paginate($this->perPage);
        
        // Get summary statistics
        $totalMovements = $query->count();
        $stockIn = StockMovement::where('type', 'IN')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->sum('qty');
        $stockOut = StockMovement::where('type', 'OUT')
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->sum('qty');
        
        return view('livewire.stock-history', [
            'movements' => $movements,
            'totalMovements' => $totalMovements,
            'stockIn' => $stockIn,
            'stockOut' => $stockOut,
        ]);
    }
}
