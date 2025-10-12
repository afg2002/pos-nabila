# Warehouse Columns Toggle Design Document

## Overview
This document outlines the design for implementing toggleable warehouse stock columns in the ProductTable Livewire component, allowing users to switch between a compact total stock view and a detailed warehouse breakdown view.

## Current Implementation Analysis

### Current State
- Products table shows total stock with warehouse breakdown below (lines 254-276 in product-table.blade.php)
- Warehouse stocks are fetched individually for each product using N+1 queries
- 4 warehouses exist in the system: GU001 (Gudang Utama), MAIN (Main Store), TEST001 (Test Warehouse), GC001 (Gudang Cabang)
- Stock data is displayed in a nested format within the stock column

### Performance Issues
- N+1 query problem: Each product triggers separate queries for warehouse stocks
- No caching mechanism for warehouse stock data
- Redundant data loading when warehouse view is not needed

## Design Solution

### 1. Component Modifications (ProductTable.php)

#### New Properties
```php
// Toggle property for warehouse columns view
public $showWarehouseColumns = false;

// Cache warehouse data for performance
public $warehouses = [];
public $warehouseStocksCache = [];
```

#### New Methods
```php
/**
 * Toggle warehouse columns view
 */
public function toggleWarehouseColumns()
{
    $this->showWarehouseColumns = !$this->showWarehouseColumns;
    $this->resetPage(); // Reset pagination when changing view
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
```

#### Modified Query Method
```php
private function buildProductQuery()
{
    $query = Product::query()
        ->select([
            'id', 'sku', 'barcode', 'name', 'category', 'photo',
            'unit_id', 'base_cost', 'price_retail', 'price_semi_grosir',
            'price_grosir', 'current_stock', 'status', 'created_at',
            'updated_at', 'deleted_at',
        ])
        ->with(['unit:id,name,abbreviation']);

    // Eager load warehouse stocks when columns are shown
    if ($this->showWarehouseColumns) {
        $query->with(['warehouseStocks' => function ($q) {
            $q->select('product_id', 'warehouse_id', 'stock_on_hand')
              ->with('warehouse:id,code,name');
        }]);
    }

    // ... rest of existing query logic
    return $query;
}
```

### 2. View Changes (product-table.blade.php)

#### Toggle Button
Add toggle button in the filters section (after line 78):
```blade
<!-- Warehouse Columns Toggle -->
<div class="flex items-center space-x-2">
    <button wire:click="toggleWarehouseColumns" 
            class="px-4 py-2 {{ $showWarehouseColumns ? 'bg-indigo-600' : 'bg-gray-600' }} text-white rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <i class="fas fa-warehouse mr-2"></i>
        {{ $showWarehouseColumns ? 'Sembunyikan Stok Gudang' : 'Tampilkan Stok Gudang' }}
    </button>
</div>
```

#### Modified Table Header
Replace stock column header (lines 175-181) with conditional headers:
```blade
@if($showWarehouseColumns)
    <!-- Warehouse Stock Columns -->
    @foreach($warehouses as $warehouse)
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            {{ $warehouse->code }}
            <span class="block text-xs text-gray-400">{{ Str::limit($warehouse->name, 15) }}</span>
        </th>
    @endforeach
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
        wire:click="sortBy('current_stock')">
        Total Stok
        @if($sortField === 'current_stock')
            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
        @endif
    </th>
@else
    <!-- Single Stock Column -->
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
        wire:click="sortBy('current_stock')">
        Stok
        @if($sortField === 'current_stock')
            <i class="fas fa-sort-{{ $sortField === 'asc' ? 'up' : 'down' }} ml-1"></i>
        @endif
    </th>
@endif
```

#### Modified Table Body
Replace stock column content (lines 254-276) with conditional display:
```blade
@if($showWarehouseColumns)
    <!-- Warehouse Stock Columns -->
    @foreach($warehouses as $warehouse)
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
            @php
                $stock = 0;
                if (isset($product->warehouseStocks)) {
                    $warehouseStock = $product->warehouseStocks->firstWhere('warehouse_id', $warehouse->id);
                    $stock = $warehouseStock ? $warehouseStock->stock_on_hand : 0;
                } else {
                    $stock = $this->getWarehouseStock($product->id, $warehouse->id);
                }
                $isLowStock = $stock < 5;
            @endphp
            <span class="px-2 py-1 rounded-full text-xs {{ $isLowStock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                {{ number_format($stock) }}
            </span>
        </td>
    @endforeach
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
        @php
            $currentStock = $product->current_stock;
            $isLowStock = $product->isLowStock();
        @endphp
        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $isLowStock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
            {{ number_format($currentStock) }}
        </span>
    </td>
@else
    <!-- Single Stock Column -->
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
        @php
            $currentStock = $product->current_stock;
            $isLowStock = $product->isLowStock();
            $warehouseStocks = \App\ProductWarehouseStock::where('product_id', $product->id)
                ->with('warehouse')
                ->get();
        @endphp
        <div class="space-y-1">
            <span class="px-2 py-1 rounded-full text-xs {{ $isLowStock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                Total: {{ number_format($currentStock) }}
            </span>
            @if($warehouseStocks->count() > 0)
                <div class="text-xs text-gray-500 space-y-0.5">
                    @foreach($warehouseStocks as $warehouseStock)
                        <div class="flex justify-between">
                            <span>{{ $warehouseStock->warehouse->code }}:</span>
                            <span class="font-medium">{{ number_format($warehouseStock->stock_on_hand) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </td>
@endif
```

#### Update Empty State Column Span
Update the empty state colspan (line 401) to account for dynamic columns:
```blade
<td colspan="{{ $showWarehouseColumns ? 10 + $warehouses->count() : 10 }}" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
```

### 3. User Interaction Flow

#### User Flow Diagram
```mermaid
graph TD
    A[User visits products page] --> B[Default: Compact view with total stock]
    B --> C[User clicks "Tampilkan Stok Gudang" button]
    C --> D[Table expands with warehouse columns]
    D --> E[Warehouse stocks loaded efficiently]
    E --> F[User can sort by total stock]
    F --> G[User clicks "Sembunyikan Stok Gudang"]
    G --> B
    
    H[User performs search/filter] --> I[View mode preserved]
    I --> J[Results displayed in current view mode]
```

#### Interaction States
1. **Default State**: Compact table with total stock and warehouse breakdown below
2. **Expanded State**: Wide table with individual columns for each warehouse
3. **Loading State**: Brief loading indicator when toggling views
4. **Responsive State**: Table remains scrollable horizontally on mobile

### 4. Performance Optimizations

#### Database Optimizations
1. **Eager Loading**: Load warehouse stocks only when columns are visible
2. **Selective Fields**: Only load necessary fields from database
3. **Indexed Queries**: Leverage existing indexes on product_warehouse_stock table
4. **Caching**: Cache warehouse list and frequently accessed stock data

#### Frontend Optimizations
1. **Conditional Rendering**: Only render warehouse columns when needed
2. **Lazy Loading**: Cache warehouse stocks in component to avoid repeated queries
3. **Efficient Sorting**: Maintain sorting functionality for total stock
4. **Responsive Design**: Horizontal scrolling for wide table on mobile

### 5. Implementation Steps

1. Add new properties to ProductTable component
2. Implement toggleWarehouseColumns() method
3. Modify buildProductQuery() to conditionally eager load warehouse stocks
4. Add caching methods for warehouse data
5. Update view file with conditional table headers and body
6. Add toggle button to filters section
7. Update empty state column span
8. Test functionality and performance

### 6. Testing Strategy

#### Unit Tests
- Test toggleWarehouseColumns() method
- Test warehouse stock caching
- Test query optimization with and without warehouse columns

#### Integration Tests
- Test complete user flow
- Test performance with large datasets
- Test responsive behavior on different screen sizes

#### Performance Tests
- Measure query execution time with warehouse columns
- Verify N+1 query elimination
- Test memory usage with large product sets

### 7. Future Enhancements

#### Potential Improvements
1. **User Preference Persistence**: Save view preference in user profile
2. **Warehouse Selection**: Allow users to select which warehouses to display
3. **Stock Alerts**: Visual indicators for low stock in specific warehouses
4. **Export Functionality**: Export with selected warehouse columns
5. **Real-time Updates**: Live stock updates via WebSocket

#### Scalability Considerations
1. **Dynamic Warehouse Count**: Handle varying numbers of warehouses
2. **Pagination Efficiency**: Maintain performance with large datasets
3. **Cache Invalidation**: Proper cache clearing when stock changes
4. **Database Indexing**: Additional indexes for complex queries

## Conclusion

This design provides a flexible, performant solution for displaying warehouse stock information in toggleable columns. The implementation balances functionality with performance, ensuring optimal user experience while maintaining system efficiency.