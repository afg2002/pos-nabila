<?php

namespace App\Livewire;

use App\Product;
use App\ProductWarehouseStock;
use App\StockMovement;
use App\Warehouse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class StockForm extends Component
{
    use AuthorizesRequests;

    #[Validate('required|exists:products,id')]
    public $product_id = '';

    #[Validate('required|in:in,out,adjustment')]
    public $movement_type = 'in';

    #[Validate('required|numeric|min:1')]
    public $quantity = '';

    #[Validate('nullable|string|max:255')]
    public $notes = '';

    #[Validate('nullable|string|max:50')]
    public $reason_code = '';

    public $showModal = false;

    public $products = [];

    public $warehouses = [];

    #[Validate('required|exists:warehouses,id')]
    public $warehouse_id = '';

    public $selectedProduct = null;

    public $currentWarehouseStock = 0;

    public $productSearch = '';

    public $searchResults = [];

    public $showSearchResults = false;

    public $showDropdown = false;

    public $allProducts = [];

    public function mount(): void
    {
        $this->products = Product::where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $this->allProducts = $this->products; // Copy for dropdown display

        $this->warehouses = Warehouse::ordered()->get();
        $defaultWarehouse = $this->warehouses->firstWhere('is_default', true) ?? $this->warehouses->first();
        $this->warehouse_id = $defaultWarehouse?->id ?? '';

        $this->updateSelectedContext();
    }

    public function updatedProductSearch($value): void
    {
        if (strlen($value) >= 2) {
            $this->searchResults = Product::where('status', 'active')
                ->whereNull('deleted_at')
                ->where(function ($query) use ($value) {
                    $query->where('name', 'like', '%' . $value . '%')
                          ->orWhere('sku', 'like', '%' . $value . '%')
                          ->orWhere('barcode', 'like', '%' . $value . '%');
                })
                ->orderBy('name')
                ->limit(10)
                ->get();
            $this->showSearchResults = true;
            $this->showDropdown = false; // Hide dropdown when searching
        } else {
            $this->searchResults = [];
            $this->showSearchResults = false;
            $this->showDropdown = false;
        }
    }

    public function selectProduct($productId): void
    {
        $this->product_id = $productId;
        $this->selectedProduct = Product::find($productId);
        $this->productSearch = $this->selectedProduct ? $this->selectedProduct->name . ' (' . $this->selectedProduct->sku . ')' : '';
        $this->showSearchResults = false;
        $this->showDropdown = false;
        $this->updateSelectedContext();
    }

    public function clearProductSearch(): void
    {
        $this->productSearch = '';
        $this->product_id = '';
        $this->selectedProduct = null;
        $this->searchResults = [];
        $this->showSearchResults = false;
        $this->showDropdown = false;
        $this->currentWarehouseStock = 0;
    }

    public function updatedProductId($value): void
    {
        $this->selectedProduct = $value ? Product::find($value) : null;

        $this->updateSelectedContext();
    }

    public function updatedWarehouseId($value): void
    {
        $this->updateSelectedContext();
    }

    private function updateSelectedContext(): void
    {
        if ($this->selectedProduct && $this->warehouse_id) {
            $stockRow = $this->selectedProduct
                ->warehouseStocks()
                ->where('warehouse_id', $this->warehouse_id)
                ->first();

            $this->currentWarehouseStock = $stockRow?->stock_on_hand ?? 0;

            return;
        }

        $this->currentWarehouseStock = 0;
    }

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset([
            'product_id',
            'movement_type',
            'quantity',
            'notes',
            'reason_code',
            'productSearch',
        ]);
        $this->selectedProduct = null;
        $this->currentWarehouseStock = 0;
        $this->searchResults = [];
        $this->showSearchResults = false;
        $this->showDropdown = false;
        $this->updateSelectedContext();
    }

    public function save(): void
    {
        $this->validate();

        $this->authorize('manage', StockMovement::class);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($this->product_id);
            $warehouse = Warehouse::findOrFail($this->warehouse_id);

            $stockRow = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'stock_on_hand' => 0,
                    'reserved_stock' => 0,
                    'safety_stock' => 0,
                ]
            );

            $availableStock = $stockRow->stock_on_hand;

            if ($this->movement_type === 'out' && $availableStock < $this->quantity) {
                $this->addError('quantity', 'Stok tidak mencukupi. Stok tersedia: '.$availableStock);

                return;
            }

            $stockChange = 0;

            switch ($this->movement_type) {
                case 'in':
                    $stockChange = (int) $this->quantity;
                    break;
                case 'out':
                    $stockChange = -1 * (int) $this->quantity;
                    break;
                case 'adjustment':
                    $stockChange = (int) $this->quantity - $availableStock;
                    break;
            }

            $stockBefore = $availableStock;
            $stockAfter = $this->movement_type === 'adjustment'
                ? (int) $this->quantity
                : $stockBefore + $stockChange;

            $movementType = match($this->movement_type) {
                'in' => 'IN',
                'out' => 'OUT', 
                'adjustment' => 'ADJ',
                default => 'IN'
            };

            StockMovement::createMovement($product->id, $stockChange, $movementType, [
                'ref_type' => 'manual',
                'ref_id' => null,
                'note' => $this->notes,
                'reason_code' => $this->reason_code,
                'performed_by' => Auth::id() ?? 1, // Fallback to user ID 1 for tests
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'warehouse_id' => $warehouse->id,
                'warehouse' => $warehouse->code,
            ]);

            DB::commit();

            session()->flash('message', 'Stok berhasil diperbarui!');
            $this->closeModal();
            $this->dispatch('stock-updated');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function toggleDropdown(): void
    {
        if (empty($this->productSearch)) {
            $this->showDropdown = !$this->showDropdown;
            $this->showSearchResults = false;
        }
    }

    public function render()
    {
        return view('livewire.stock-form');
    }
}
