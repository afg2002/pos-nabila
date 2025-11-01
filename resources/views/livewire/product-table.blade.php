<div>
    <div class="p-3 sm:p-6">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 text-green-800 rounded-lg shadow-sm flex items-center" 
             x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-x-full" 
             x-transition:enter-end="opacity-100 transform translate-x-0" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-x-0" 
             x-transition:leave-end="opacity-0 transform translate-x-full" 
             x-init="setTimeout(() => show = false, 4000)">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="flex-1">{{ session('message') }}</span>
            <button @click="show = false" class="ml-2 text-green-600 hover:text-green-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-800 rounded-lg shadow-sm flex items-center" 
             x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-x-full" 
             x-transition:enter-end="opacity-100 transform translate-x-0" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-x-0" 
             x-transition:leave-end="opacity-0 transform translate-x-full" 
             x-init="setTimeout(() => show = false, 5000)">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="flex-1">{{ session('error') }}</span>
            <button @click="show = false" class="ml-2 text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    <!-- Filters Section - Mobile First -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6">
        <div class="space-y-4">
            <!-- Search Bar -->
            <div class="w-full">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari Produk</label>
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Nama, SKU, atau barcode..."
                       class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Filter Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kategori</label>
                    <select wire:model.live="category" 
                            class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select wire:model.live="status" 
                            class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Tidak Aktif</option>
                        <option value="discontinued">Dihentikan</option>
                        <option value="deleted">Dihapus</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Per Halaman</label>
                    <select wire:model.live="perPage" 
                            class="w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Show Deleted Checkbox -->
                <div class="flex items-end">
                    @can('viewTrashed', App\Product::class)
                        <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <input type="checkbox" wire:model.live="showDeleted" 
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 w-4 h-4">
                            <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">Tampilkan yang dihapus</span>
                        </label>
                    @endcan
                </div>
            </div>

            <!-- Action Buttons - Responsive -->
            <div class="flex flex-wrap gap-3 justify-center sm:justify-start">
                <!-- Warehouse Toggle -->
                <button wire:click="toggleWarehouseColumns"
                        class="px-4 py-3 {{ $showWarehouseColumns ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-600 hover:bg-gray-700' }} text-white rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all transform hover:scale-105 w-full sm:w-auto">
                    <i class="fas fa-warehouse mr-2"></i>
                    {{ $showWarehouseColumns ? 'Sembunyikan Stok Gudang' : 'Tampilkan Stok Gudang' }}
                </button>

                @can('create', App\Product::class)
                    <!-- Add Product -->
                    <button wire:click="openCreateModal" 
                            class="px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all transform hover:scale-105 w-full sm:w-auto">
                        <i class="fas fa-plus mr-2"></i>Tambah Produk
                    </button>
                    
                    <!-- Import Excel -->
                    <a href="{{ route('products.import') }}" 
                       class="px-4 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all transform hover:scale-105 inline-flex items-center justify-center w-full sm:w-auto">
                        <i class="fas fa-file-import mr-2"></i>Import Excel
                    </a>
                    
                    <!-- Export Excel -->
                    <button wire:click="exportProducts" 
                            class="px-4 py-3 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all transform hover:scale-105 w-full sm:w-auto">
                        <i class="fas fa-file-export mr-2"></i>Export Excel
                    </button>
                    
                    <!-- Bulk Price -->
                    <button wire:click="openBulkPriceModal" 
                            class="px-4 py-3 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-all transform hover:scale-105 w-full sm:w-auto">
                        <i class="fas fa-tags mr-2"></i>Bulk Harga
                    </button>
                @endcan
            </div>

            <!-- Bulk Actions for Selected Items -->
            @can('bulkDelete', App\Product::class)
                @if(count($selectedProducts) > 0)
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-4 mt-4">
                        <div class="flex flex-wrap items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                <i class="fas fa-check-square mr-2"></i>{{ count($selectedProducts) }} produk dipilih
                            </span>
                            
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="confirmBulkDelete" 
                                        class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all transform hover:scale-105">
                                    <i class="fas fa-trash mr-1"></i>Hapus
                                </button>
                                
                                @if($status === 'deleted')
                                    <button wire:click="confirmBulkRestore" 
                                            class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all transform hover:scale-105">
                                        <i class="fas fa-undo mr-1"></i>Kembalikan
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endcan
        </div>
    </div>

    <!-- Products Container -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Mobile Card View -->
        <div class="lg:hidden">
            @forelse($products as $product)
                <div class="border-b border-gray-200 dark:border-gray-700 p-3 space-y-3">
                    <!-- Simplified Mobile Layout -->
                    <div class="flex items-start space-x-3">
                        <!-- Compact Product Photo -->
                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden border border-gray-300 dark:border-gray-600">
                                <img src="{{ $product->getPhotoUrl() }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-all duration-200"
                                     onclick="openImageModal('{{ $product->getPhotoUrl() }}', '{{ $product->name }}')"
                                     onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                            </div>
                            <!-- Stock Status Indicator -->
                            @php
                                $isLowStock = $product->isLowStock();
                            @endphp
                            <div class="absolute -top-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center text-xs font-bold {{ $isLowStock ? 'bg-red-500 text-white' : 'bg-green-500 text-white' }}">
                                {{ $isLowStock ? '!' : '✓' }}
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1">{{ $product->name }}</h3>
                                    <!-- Essential Tags Only -->
                                    <div class="flex items-center gap-1 mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                                            {{ $product->unit ? $product->unit->abbreviation : '-' }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded-full
                                            {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $product->status === 'inactive' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $product->status === 'discontinued' ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ $product->getStatusDisplayName() }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex items-center space-x-1">
                                    <input type="checkbox" wire:model.live="selectedProducts" value="{{ $product->id }}"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3 h-3">
                                    
                                    @if($product->trashed())
                                        @can('restore', $product)
                                            <button wire:click="confirmRestore({{ $product->id }})" 
                                                    class="p-1.5 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-800 transition-all"
                                                    title="Kembalikan Produk">
                                                <i class="fas fa-undo text-xs"></i>
                                            </button>
                                        @endcan
                                    @else
                                        @can('view', $product)
                                            <button wire:click="openDetailModal({{ $product->id }})" 
                                                    class="p-1.5 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition-all"
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>
                                        @endcan
                                        @can('update', $product)
                                            <button wire:click="openEditModal({{ $product->id }})" 
                                                    class="p-1.5 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded hover:bg-purple-200 dark:hover:bg-purple-800 transition-all"
                                                    title="Edit Produk">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </div>

                            <!-- Key Info Row -->
                            <div class="flex items-center justify-between mt-2 text-xs">
                                <div class="flex items-center space-x-2">
                                    <span class="text-gray-600 dark:text-gray-400">Stok:</span>
                                    <span class="font-medium {{ $isLowStock ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($product->current_stock) }}
                                    </span>
                                </div>
                                <div class="text-gray-900 dark:text-white font-medium">
                                    Rp {{ number_format($product->price_retail, 0, ',', '.') }}
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                {{ $product->category }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-box-open text-5xl mb-4"></i>
                    <p class="text-xl font-medium mb-2">Tidak ada produk ditemukan</p>
                    <p class="text-sm">Tambahkan produk baru atau ubah filter pencarian</p>
                </div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('sku')">
                            SKU
                            @if($sortField === 'sku')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('name')">
                            Nama Produk
                            @if($sortField === 'name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('category')">
                            Kategori
                            @if($sortField === 'category')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Satuan
                        </th>
                        @if($showWarehouseColumns)
                            @foreach($warehouses as $warehouse)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ $warehouse->code }}
                                </th>
                            @endforeach
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('current_stock')">
                            Stok
                            @if($sortField === 'current_stock')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('price_retail')">
                            Harga Retail
                            @if($sortField === 'price_retail')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <input type="checkbox" wire:model.live="selectedProducts" value="{{ $product->id }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $product->sku }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <!-- Product Photo -->
                                    <div class="w-10 h-10 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                        <img src="{{ $product->getPhotoUrl() }}" 
                                             alt="{{ $product->name }}"
                                             class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                             onclick="openImageModal('{{ $product->getPhotoUrl() }}', '{{ $product->name }}')"
                                             onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                                    </div>
                                    
                                    <!-- Product Info -->
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->unit ? $product->unit->name : '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs">
                                    {{ $product->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div class="space-y-1">
                                    <span class="px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-xs">
                                        Base: {{ $product->unit ? $product->unit->abbreviation : '-' }}
                                    </span>
                                    @foreach($product->unitScales as $scale)
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs">
                                                {{ $scale->unit->abbreviation }} = {{ rtrim(rtrim(number_format($scale->to_base_qty, 2, ',', '.'), '0'), ',') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            @if($showWarehouseColumns)
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
                            @endif
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @php
                                    $isLowStock = $product->isLowStock();
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs {{ $isLowStock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ number_format($product->current_stock) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                Rp {{ number_format($product->price_retail, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $product->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $product->status === 'inactive' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $product->status === 'discontinued' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $product->getStatusDisplayName() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if($product->trashed())
                                        @can('restore', $product)
                                            <button wire:click="confirmRestore({{ $product->id }})" 
                                                    class="text-green-600 hover:text-green-900 dark:text-green-400"
                                                    title="Kembalikan Produk">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endcan
                                    @else
                                        @can('view', $product)
                                            <button wire:click="openDetailModal({{ $product->id }})" 
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400"
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endcan
                                        @can('update', $product)
                                            <button wire:click="openEditModal({{ $product->id }})" 
                                                    class="text-purple-600 hover:text-purple-900 dark:text-purple-400"
                                                    title="Edit Produk">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endcan
                                        @can('delete', $product)
                                            <button wire:click="confirmSoftDelete({{ $product->id }})" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400"
                                                    title="Hapus Produk">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-box-open text-5xl mb-4"></i>
                                <p class="text-xl font-medium mb-2">Tidak ada produk ditemukan</p>
                                <p class="text-sm">Tambahkan produk baru atau ubah filter pencarian</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $products->links() }}
            </div>
        @endif
    </div>

        <!-- Create/Edit Product Modal -->
        @if($showModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeModal">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click.stop>
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full max-h-[90vh] overflow-y-auto">
                        <form wire:submit.prevent="save">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="mb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        {{ $editMode ? 'Edit Produk' : 'Tambah Produk Baru' }}
                                    </h3>
                                </div>

                                <div class="space-y-6">
                                    <!-- Product Information Section -->
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-semibold text-gray-900 border-b pb-2">Informasi Dasar</h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- SKU -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">SKU</label>
                                                <input type="text" wire:model="sku" 
                                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                       placeholder="Auto-generate jika kosong">
                                                @error('sku') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                            </div>

                                            <!-- Barcode -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                                                <input type="text" wire:model="barcode" 
                                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                @error('barcode') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <!-- Name -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Produk *</label>
                                            <input type="text" wire:model="name" 
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                   required>
                                            @error('name') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                        </div>

                                        <!-- Category -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                                            <input type="text" wire:model="categoryInput" 
                                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                   list="categories-list"
                                                   required>
                                            <datalist id="categories-list">
                                                @foreach($masterCategories as $cat)
                                                    <option value="{{ $cat }}">
                                                @endforeach
                                            </datalist>
                                            @error('categoryInput') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                        </div>

                                        <!-- Unit -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Satuan *</label>
                                            <div class="flex space-x-3">
                                                <select wire:model="unit_id" 
                                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                        required>
                                                    <option value="">Pilih Satuan</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" wire:click="openUnitModal" 
                                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm">
                                                    <i class="fas fa-plus mr-2"></i>Tambah Unit
                                                </button>
                                            </div>
                                            @error('unit_id') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <!-- Pricing Section -->
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-semibold text-gray-900 border-b pb-2">Informasi Harga</h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Beli *</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-2 text-gray-500 text-sm">Rp</span>
                                                    <input type="number" wire:model="base_cost" step="0.01" min="0"
                                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                           required>
                                                </div>
                                                @error('base_cost') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Retail *</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-2 text-gray-500 text-sm">Rp</span>
                                                    <input type="number" wire:model="price_retail" step="0.01" min="0"
                                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                           required>
                                                </div>
                                                @error('price_retail') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga Grosir</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-2 text-gray-500 text-sm">Rp</span>
                                                    <input type="number" wire:model="price_grosir" step="0.01" min="0"
                                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                </div>
                                                @error('price_grosir') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Information Section -->
                                    <div class="space-y-4">
                                        <h4 class="text-sm font-semibold text-gray-900 border-b pb-2">Informasi Tambahan</h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                                <select wire:model="productStatus" 
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                    <option value="active">Aktif</option>
                                                    <option value="inactive">Tidak Aktif</option>
                                                    <option value="discontinued">Dihentikan</option>
                                                </select>
                                                @error('productStatus') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <!-- Photo -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Produk</label>
                                            @if($currentPhoto)
                                                <div class="mt-2 flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                                                    <img src="{{ Storage::url('products/'.$currentPhoto) }}" 
                                                         alt="Current photo" 
                                                         class="h-20 w-20 object-cover rounded-lg border-2 border-gray-200">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-700">Foto saat ini</p>
                                                        <p class="text-xs text-gray-500">Klik untuk ganti</p>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file" wire:model="photo" 
                                                   class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                            @error('photo') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <!-- Initial Stock (only for create) -->
                                    @if(!$editMode)
                                        <div class="space-y-4 border-t pt-4">
                                            <h4 class="text-sm font-semibold text-gray-900">Stok Awal (Opsional)</h4>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Stok</label>
                                                    <input type="number" wire:model="initial_stock" step="1" min="0"
                                                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                    @error('initial_stock') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Gudang</label>
                                                    <select wire:model="initial_warehouse_id" 
                                                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                        <option value="">Pilih Gudang</option>
                                                        @foreach(\App\Warehouse::all() as $warehouse)
                                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('initial_warehouse_id') <div class="mt-1 text-red-500 text-xs">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" 
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    {{ $editMode ? 'Update' : 'Simpan' }}
                                </button>
                                <button type="button" wire:click="closeModal" 
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Detail Modal -->
        @if($showDetailModal && $selectedProduct)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeDetailModal">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click.stop>
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full max-h-[90vh] overflow-y-auto">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <div class="mb-6">
                                <h3 class="text-xl leading-6 font-medium text-gray-900 mb-4">Detail Produk</h3>
                                
                                <!-- Product Header with Photo -->
                                <div class="flex items-start space-x-6 mb-6">
                                    <div class="flex-shrink-0">
                                        @if($selectedProduct->photo)
                                            <img src="{{ $selectedProduct->getPhotoUrl() }}" 
                                                 alt="{{ $selectedProduct->name }}"
                                                 class="h-24 w-24 object-cover rounded-lg border-2 border-gray-200">
                                        @else
                                            <div class="h-24 w-24 bg-gray-200 rounded-lg border-2 border-gray-200 flex items-center justify-center">
                                                <i class="fas fa-box text-gray-400 text-2xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $selectedProduct->name }}</h4>
                                        <p class="text-sm text-gray-600 mt-1">SKU: {{ $selectedProduct->sku }}</p>
                                        <p class="text-sm text-gray-600">Barcode: {{ $selectedProduct->barcode ?: '-' }}</p>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $selectedProduct->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $selectedProduct->status === 'inactive' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $selectedProduct->status === 'discontinued' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ $selectedProduct->getStatusDisplayName() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Information Grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Basic Info -->
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900 mb-3">Informasi Dasar</h5>
                                        <dl class="space-y-2">
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">Kategori:</dt>
                                                <dd class="text-sm font-medium text-gray-900">{{ $selectedProduct->category }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">Satuan:</dt>
                                                <dd class="text-sm font-medium text-gray-900">{{ $selectedProduct->unit ? $selectedProduct->unit->name : '-' }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">Stok Saat Ini:</dt>
                                                <dd class="text-sm font-medium">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $selectedProduct->isLowStock() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                        {{ number_format($selectedProduct->current_stock) }}
                                                    </span>
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Pricing Info -->
                                    <div>
                                        <h5 class="text-sm font-medium text-gray-900 mb-3">Informasi Harga</h5>
                                        <dl class="space-y-2">
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">Harga Beli:</dt>
                                                <dd class="text-sm font-medium text-gray-900">Rp {{ number_format($selectedProduct->base_cost, 0, ',', '.') }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">Harga Retail:</dt>
                                                <dd class="text-sm font-medium text-gray-900">Rp {{ number_format($selectedProduct->price_retail, 0, ',', '.') }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">Harga Grosir:</dt>
                                                <dd class="text-sm font-medium text-gray-900">Rp {{ number_format($selectedProduct->price_grosir, 0, ',', '.') }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <!-- Unit Scales -->
                                @if($selectedProduct->unitScales->count() > 0)
                                    <div class="mt-6">
                                        <h5 class="text-sm font-medium text-gray-900 mb-3">Konversi Satuan</h5>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">
                                                Base: {{ $selectedProduct->unit ? $selectedProduct->unit->abbreviation : '-' }}
                                            </span>
                                            @foreach($selectedProduct->unitScales as $scale)
                                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                                    {{ $scale->unit->abbreviation }} = {{ rtrim(rtrim(number_format($scale->to_base_qty, 2, ',', '.'), '0'), ',') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="closeDetailModal" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Bulk Price Modal -->
        @if($showBulkPriceModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeBulkPriceModal">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click.stop>
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full max-h-[90vh] overflow-y-auto">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <div class="mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Update Harga Massal</h3>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                                        <select wire:model="bulkUpdateCategory" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">Pilih Kategori</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat }}">{{ $cat }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Field Harga</label>
                                        <select wire:model="bulkPriceField" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="price_retail">Harga Retail</option>
                                            <option value="price_semi_grosir">Harga Semi Grosir</option>
                                            <option value="price_grosir">Harga Grosir</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipe Update</label>
                                        <select wire:model="bulkUpdateType" 
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="percentage">Persentase (%)</option>
                                            <option value="fixed_amount">Jumlah Tetap (Rp)</option>
                                            <option value="set_price">Set Harga Baru (Rp)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nilai</label>
                                        <input type="number" wire:model="bulkUpdateValue" step="0.01" min="0"
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>

                                <div>
                                    <button type="button" wire:click="generateBulkPricePreview" 
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Generate Preview
                                    </button>
                                </div>

                                @if(!empty($bulkUpdatePreview))
                                    <div class="border rounded-lg overflow-hidden">
                                        <div class="bg-gray-50 px-4 py-3 border-b">
                                            <h4 class="text-sm font-medium text-gray-900">Preview Update Harga</h4>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Lama</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga Baru</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Perubahan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($bulkUpdatePreview as $preview)
                                                        <tr>
                                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $preview['name'] }}</td>
                                                            <td class="px-4 py-2 text-sm text-right text-gray-900">Rp {{ number_format($preview['current_price'], 0, ',', '.') }}</td>
                                                            <td class="px-4 py-2 text-sm text-right font-medium text-gray-900">Rp {{ number_format($preview['new_price'], 0, ',', '.') }}</td>
                                                            <td class="px-4 py-2 text-sm text-right">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $preview['difference'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                    {{ $preview['difference'] >= 0 ? '+' : '' }}Rp {{ number_format($preview['difference'], 0, ',', '.') }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="button" wire:click="executeBulkPriceUpdate" 
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Eksekusi Update Harga
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="closeBulkPriceModal" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Delete Confirmation Modal -->
        @if($showDeleteModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeDeleteModal">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click.stop>
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Hapus Produk
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Apakah Anda yakin ingin menghapus produk "<span class="font-semibold">{{ $deleteProductName ?? '' }}</span>"? 
                                            Produk akan dipindahkan ke trash dan dapat dikembalikan nanti.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="deleteProduct" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Ya, hapus
                            </button>
                            <button type="button" wire:click="closeDeleteModal" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Bulk Delete Confirmation Modal -->
        @if($showBulkDeleteModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeBulkDeleteModal">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click.stop>
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Hapus Banyak Produk
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Apakah Anda yakin ingin menghapus <span class="font-semibold">{{ count($selectedProducts) }}</span> produk? 
                                            Semua produk yang dipilih akan dipindahkan ke trash.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="bulkDelete" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Ya, hapus semua
                            </button>
                            <button type="button" wire:click="closeBulkDeleteModal" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Restore Confirmation Modal -->
        @if($showRestoreModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeRestoreModal">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click.stop>
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-undo text-green-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Kembalikan Produk
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Apakah Anda yakin ingin mengembalikan produk "<span class="font-semibold">{{ $restoreProductName ?? '' }}</span>"?
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="restoreProduct" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Ya, kembalikan
                            </button>
                            <button type="button" wire:click="closeRestoreModal" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Bulk Restore Confirmation Modal -->
        @if($showBulkRestoreModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeBulkRestoreModal">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0" wire:click.stop>
                    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-undo text-green-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Kembalikan Banyak Produk
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Apakah Anda yakin ingin mengembalikan <span class="font-semibold">{{ count($selectedProducts) }}</span> produk?
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="bulkRestore" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Ya, kembalikan semua
                            </button>
                            <button type="button" wire:click="closeBulkRestoreModal" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Image Modal -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden" onclick="closeImageModal()">
            <div class="relative max-w-4xl max-h-full p-4" onclick="event.stopPropagation()">
                <button onclick="closeImageModal()" class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-all">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
                <div id="modalImageName" class="text-white text-center mt-3 font-medium text-lg"></div>
            </div>
        </div>

        <script>
        function openImageModal(imageUrl, imageName) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const modalImageName = document.getElementById('modalImageName');
            
            modalImage.src = imageUrl;
            modalImage.alt = imageName;
            modalImageName.textContent = imageName;
            modal.classList.remove('hidden');
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            
            // Restore body scroll
            document.body.style.overflow = 'auto';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
        </script>
    </div>
