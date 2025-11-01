<?php

namespace App\Livewire;

use App\Product;
use App\ProductWarehouseStock;
use App\Warehouse;
use Livewire\Component;
use Livewire\WithPagination;

class WarehouseStockView extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedWarehouse = '';
    public $categoryFilter = '';
    public $stockFilter = 'all'; // all, low_stock, out_of_stock, in_stock
    public $perPage = 15;
    
    public $warehouses = [];
    public $categories = [];

    public function mount()
    {
        $this->warehouses = Warehouse::ordered()->get();
        $this->categories = Product::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
            
        // Set default warehouse to the default one
        $defaultWarehouse = $this->warehouses->firstWhere('is_default', true);
        $this->selectedWarehouse = $defaultWarehouse?->id ?? '';
    }

    public function updatedSelectedWarehouse()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatedStockFilter()
    {
        $this->resetPage();
    }

    public function getStockDataProperty()
    {
        $query = ProductWarehouseStock::with(['product', 'warehouse'])
            ->when($this->selectedWarehouse, function ($q) {
                $q->where('warehouse_id', $this->selectedWarehouse);
            })
            ->when($this->search, function ($q) {
                $q->whereHas('product', function ($productQuery) {
                    $productQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($q) {
                $q->whereHas('product', function ($productQuery) {
                    $productQuery->where('category', $this->categoryFilter);
                });
            })
            ->when($this->stockFilter !== 'all', function ($q) {
                switch ($this->stockFilter) {
                    case 'out_of_stock':
                        $q->where('stock_on_hand', '<=', 0);
                        break;
                    case 'low_stock':
                        $q->whereRaw('stock_on_hand <= COALESCE((SELECT min_stock FROM products WHERE products.id = product_warehouse_stock.product_id), 10)');
                        break;
                    case 'in_stock':
                        $q->where('stock_on_hand', '>', 0);
                        break;
                }
            });

        return $query->paginate($this->perPage);
    }

    public function getStockSummaryProperty()
    {
        if (!$this->selectedWarehouse) {
            return [
                'total_products' => 0,
                'total_stock' => 0,
                'out_of_stock' => 0,
                'low_stock' => 0,
                'total_value' => 0
            ];
        }

        $stocks = ProductWarehouseStock::with('product')
            ->where('warehouse_id', $this->selectedWarehouse)
            ->whereHas('product', function ($q) {
                // Exclude orphaned or soft-deleted products to avoid null relationship access
                $q->whereNull('deleted_at');
            })
            ->get();

        $totalProducts = $stocks->count();
        $totalStock = $stocks->sum('stock_on_hand');
        $outOfStock = $stocks->where('stock_on_hand', '<=', 0)->count();
        $lowStock = $stocks->filter(function ($stock) {
            return $stock->stock_on_hand > 0 && 
                   $stock->stock_on_hand <= ($stock->product->min_stock ?? 10);
        })->count();
        
        $totalValue = $stocks->sum(function ($stock) {
            $baseCost = $stock->product->base_cost ?? 0; // Guard against null product
            return $stock->stock_on_hand * $baseCost;
        });

        return [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'out_of_stock' => $outOfStock,
            'low_stock' => $lowStock,
            'total_value' => $totalValue
        ];
    }

    public function render()
    {
        return view('livewire.warehouse-stock-view', [
            'stockData' => $this->stockData,
            'stockSummary' => $this->stockSummary,
        ]);
    }
}