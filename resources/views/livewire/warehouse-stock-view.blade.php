<div class="space-y-6">
    <!-- Header with Warehouse Selection -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-warehouse mr-3 text-blue-600"></i>
                    Stok per Gudang
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Kelola dan pantau stok berdasarkan gudang</p>
            </div>
            @if($selectedWarehouse)
                <div class="mt-3 sm:mt-0">
                    <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                        <i class="fas fa-filter mr-2"></i>
                        {{ $warehouses->firstWhere('id', $selectedWarehouse)?->name }}
                    </span>
                </div>
            @endif
        </div>
        
        <!-- Warehouse Selection -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative">
                <label for="warehouseSelect" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-warehouse mr-1 text-gray-400"></i>Pilih Gudang
                </label>
                <select wire:model.live="selectedWarehouse" id="warehouseSelect"
                        class="w-full px-4 py-3 pr-10 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200 appearance-none">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">
                            {{ $warehouse->name }} ({{ $warehouse->code }})
                            @if($warehouse->is_default) - Default @endif
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-8">
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
            
            <div class="relative">
                <label for="searchStock" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-search mr-1 text-gray-400"></i>Cari Produk
                </label>
                <input type="text" wire:model.live.debounce.300ms="search" id="searchStock"
                       placeholder="Nama, SKU, atau barcode..."
                       class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none mt-8">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
            
            <div class="relative">
                <label for="categoryFilter" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-tags mr-1 text-gray-400"></i>Kategori
                </label>
                <select wire:model.live="categoryFilter" id="categoryFilter"
                        class="w-full px-4 py-3 pr-10 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200 appearance-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-8">
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
            
            <div class="relative">
                <label for="stockFilter" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-filter mr-1 text-gray-400"></i>Filter Stok
                </label>
                <select wire:model.live="stockFilter" id="stockFilter"
                        class="w-full px-4 py-3 pr-10 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200 appearance-none">
                    <option value="all">Semua Stok</option>
                    <option value="in_stock">Ada Stok</option>
                    <option value="low_stock">Stok Menipis</option>
                    <option value="out_of_stock">Stok Habis</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-8">
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    @if($selectedWarehouse)
        <!-- Stock Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-5 border border-blue-200 dark:border-blue-700/50 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-box text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 text-right">
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Produk</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($stockSummary['total_products']) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-5 border border-green-200 dark:border-green-700/50 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-arrow-up text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 text-right">
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Total Stok</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ number_format($stockSummary['total_stock']) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 rounded-xl p-5 border border-yellow-200 dark:border-yellow-700/50 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 text-right">
                        <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Stok Menipis</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ number_format($stockSummary['low_stock']) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-5 border border-red-200 dark:border-red-700/50 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-times-circle text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 text-right">
                        <p class="text-sm font-medium text-red-600 dark:text-red-400">Stok Habis</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ number_format($stockSummary['out_of_stock']) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-5 border border-purple-200 dark:border-purple-700/50 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-rupiah-sign text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1 text-right">
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Nilai Stok</p>
                        <p class="text-lg font-bold text-purple-900 dark:text-purple-100 break-words" title="Rp {{ number_format($stockSummary['total_value'], 0, ',', '.') }}">
                            Rp {{ number_format($stockSummary['total_value'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stock Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">
                    @if($selectedWarehouse)
                        Stok Gudang: {{ $warehouses->firstWhere('id', $selectedWarehouse)?->name }}
                    @else
                        Semua Stok Gudang
                    @endif
                </h3>
                <div class="text-sm text-gray-500">
                    {{ $stockData->total() }} produk ditemukan
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        @if(!$selectedWarehouse)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($stockData as $stock)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ $stock->product->photo ? asset('storage/' . $stock->product->photo) : asset('storage/placeholders/no-image.svg') }}" 
                                             alt="{{ $stock->product->name }}"
                                             onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $stock->product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $stock->product->unit?->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 bg-gray-100 rounded-full text-xs font-mono">
                                    {{ $stock->product->sku }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                    {{ $stock->product->category ?? '-' }}
                                </span>
                            </td>
                            @if(!$selectedWarehouse)
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        {{ $stock->warehouse->name }}
                                    </span>
                                </td>
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @php
                                    $stockLevel = $stock->stock_on_hand;
                                    $minStock = $stock->product->min_stock ?? 10;
                                    $stockClass = 'text-gray-900';
                                    if ($stockLevel <= 0) {
                                        $stockClass = 'text-red-600';
                                    } elseif ($stockLevel <= $minStock) {
                                        $stockClass = 'text-yellow-600';
                                    } else {
                                        $stockClass = 'text-green-600';
                                    }
                                @endphp
                                <span class="{{ $stockClass }}">{{ number_format($stockLevel) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($stock->product->min_stock ?? 10) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp {{ number_format($stock->product->base_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                Rp {{ number_format($stock->stock_on_hand * $stock->product->base_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stock->stock_on_hand <= 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        Habis
                                    </span>
                                @elseif($stock->stock_on_hand <= ($stock->product->min_stock ?? 10))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Menipis
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Aman
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $selectedWarehouse ? '8' : '9' }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">Tidak ada data stok</p>
                                    <p class="text-gray-400 text-sm mt-1">Pilih gudang atau ubah filter pencarian</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($stockData->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $stockData->links() }}
            </div>
        @endif
    </div>
</div>
