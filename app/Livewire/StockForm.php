<?php

namespace App\Livewire;

use App\Product;
use App\Warehouse;
use App\StockMovement;
use App\ProductWarehouseStock;
use App\Shared\Traits\WithAlerts;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class StockForm extends Component
{
    use AuthorizesRequests, WithAlerts;

    public $showModal = false;

    public $productSearch = '';

    public $selectedProductId = null;

    public $selectedWarehouseId = null;

    public $movementType = 'in'; // in, out, adjustment

    public $quantity = 0;

    public $notes = '';

    public $reasonCode = '';

    public $products = [];

    public $warehouses = [];

    public $searchResults = [];

    public $currentStock = 0;

    public $isLoading = false;

    public function mount()
    {
        $this->products = Product::orderBy('name')->get();
        $this->warehouses = Warehouse::ordered()->get();
    }

    public function updatedSelectedProductId()
    {
        $this->updateCurrentStock();
    }

    public function updatedSelectedWarehouseId()
    {
        $this->updateCurrentStock();
    }

    public function updateCurrentStock()
    {
        if ($this->selectedProductId && $this->selectedWarehouseId) {
            $this->currentStock = ProductWarehouseStock::where('product_id', $this->selectedProductId)
                ->where('warehouse_id', $this->selectedWarehouseId)
                ->value('stock_on_hand') ?? 0;
        } else {
            $this->currentStock = 0;
        }
    }

    public function searchProducts()
    {
        if (strlen($this->productSearch) < 2) {
            $this->searchResults = [];
            return;
        }
        $this->searchResults = Product::where('name', 'like', '%'.$this->productSearch.'%')
            ->orWhere('sku', 'like', '%'.$this->productSearch.'%')
            ->orWhere('barcode', 'like', '%'.$this->productSearch.'%')
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function resetForm()
    {
        $this->productSearch = '';
        $this->selectedProductId = null;
        $this->selectedWarehouseId = null;
        $this->movementType = 'in';
        $this->quantity = 0;
        $this->notes = '';
        $this->reasonCode = '';
        $this->searchResults = [];
        $this->currentStock = 0;
        $this->resetValidation();
    }

    #[On('openStockFormForProduct')]
    public function openForProduct($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $this->resetForm();
            $this->selectedProductId = $product->id;
            $this->showModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Produk tidak ditemukan atau tidak valid.');
        }
    }

    public function save()
    {
        $this->validate([
            'selectedProductId' => 'required|exists:products,id',
            'selectedWarehouseId' => 'required|exists:warehouses,id',
            'movementType' => 'required|in:in,out,adjustment',
            'quantity' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string|max:500',
            'reasonCode' => 'nullable|string|max:100',
        ]);

        $this->authorize('manage', StockMovement::class);

        DB::beginTransaction();
        try {
            $quantity = (float) $this->quantity;
            if ($this->movementType === 'out') {
                $quantity = -abs($quantity);
            }

            $movement = StockMovement::create([
                'product_id' => $this->selectedProductId,
                'warehouse_id' => $this->selectedWarehouseId,
                'movement_type' => $this->movementType,
                'quantity' => $quantity,
                'notes' => $this->notes,
                'reason_code' => $this->reasonCode,
                'performed_by' => auth()->id(),
            ]);

            $pws = ProductWarehouseStock::firstOrCreate([
                'product_id' => $this->selectedProductId,
                'warehouse_id' => $this->selectedWarehouseId,
            ], [
                'stock_on_hand' => 0,
            ]);

            $pws->increment('stock_on_hand', $quantity);

            DB::commit();

            $this->dispatch('stock-updated');
            $this->showModal = false;
            session()->flash('success', 'Stok berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal update stok: '.$e->getMessage());
            session()->flash('error', 'Gagal memperbarui stok: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.stock-form');
    }
}
