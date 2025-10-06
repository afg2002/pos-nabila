<div class="space-y-6">
    <!-- Header with Statistics -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Riwayat Stok</h2>
            @if(auth()->user()->hasPermission('inventory.export'))
                <button wire:click="exportData" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
            @endif
        </div>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600">Total Transaksi</p>
                        <p class="text-2xl font-semibold text-blue-900">{{ number_format($totalMovements) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600">Stok Masuk</p>
                        <p class="text-2xl font-semibold text-green-900">{{ number_format($this->stockIn) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-red-600">Stok Keluar</p>
                        <p class="text-2xl font-semibold text-red-900">{{ number_format($this->stockOut) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                       placeholder="Nama produk, SKU, atau catatan...">
            </div>
            
            <!-- Product Filter -->
            <div>
                <label for="productFilter" class="block text-sm font-medium text-gray-700 mb-1">Produk</label>
                <select wire:model.live="productFilter" id="productFilter" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Semua Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Warehouse Filter -->
            <div>
                <label for="warehouseFilter" class="block text-sm font-medium text-gray-700 mb-1">Gudang</label>
                <select wire:model.live="warehouseFilter" id="warehouseFilter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Movement Type Filter -->
            <div>
                <label for="movementTypeFilter" class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                <select wire:model.live="movementTypeFilter" id="movementTypeFilter" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Semua Jenis</option>
                    <option value="in">Stok Masuk</option>
                    <option value="out">Stok Keluar</option>
                    <option value="adjustment">Penyesuaian</option>
                </select>
            </div>
        </div>
        

        
        <!-- Date Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Date From -->
            <div>
                <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="dateFrom" id="dateFrom" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <!-- Date To -->
            <div>
                <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="dateTo" id="dateTo" 
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-4 flex justify-end space-x-2">
            <button wire:click="refreshData" 
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
            
            <button wire:click="resetFilters" 
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Reset Filter
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $movement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <!-- Product Photo -->
                                    <div class="w-10 h-10 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                        <img src="{{ $movement->product ? $movement->product->getPhotoUrl() : asset('storage/placeholders/no-image.svg') }}" 
                                             alt="{{ $movement->product ? $movement->product->name : 'No Product' }}"
                                             class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                             onclick="openImageModal({{ json_encode($movement->product ? $movement->product->getPhotoUrl() : asset('storage/placeholders/no-image.svg')) }}, {{ json_encode($movement->product ? $movement->product->name : 'No Product') }})">
                                    </div>
                                    
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $movement->product ? $movement->product->name : 'No Product' }}</div>
                                        <div class="text-sm text-gray-500">{{ $movement->product ? $movement->product->sku : '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $movement->warehouse->name ?? 'Tanpa Gudang' }}
                                <div class="text-xs text-gray-500">
                                    {{ $movement->warehouse->code ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($movement->type === 'IN')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                        </svg>
                                        Masuk
                                    </span>
                                @elseif($movement->type === 'OUT')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                        </svg>
                                        Keluar
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Penyesuaian
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($movement->type === 'IN')
                                    <span class="text-green-600">+{{ number_format($movement->qty) }}</span>
                                @elseif($movement->type === 'OUT')
                                    <span class="text-red-600">-{{ number_format($movement->qty) }}</span>
                                @else
                                    <span class="text-yellow-600">{{ number_format($movement->qty) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="text-gray-600">{{ number_format($movement->stock_before) }} → {{ number_format($movement->stock_after) }}</span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="text-sm font-medium text-gray-900">{{ $movement->performedBy->name ?? $movement->user->name ?? 'System' }}</div>
                                @if($movement->approvedBy)
                                    <div class="text-xs text-green-600">Disetujui: {{ $movement->approvedBy->name }}</div>
                                @elseif($movement->requires_approval && !$movement->approved_at)
                                    <div class="text-xs text-yellow-600">Menunggu persetujuan</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <!-- View Detail Button -->
                                    <button wire:click="openDetailModal({{ $movement->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                            title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    @if($movement->ref_type === 'manual')
                                        <!-- Edit Button -->
                                        @can('update', $movement)
                                            <button wire:click="openEditModal({{ $movement->id }})" 
                                                    class="text-yellow-600 hover:text-yellow-900 transition-colors duration-200"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Delete Button -->
                                        @can('delete', $movement)
                                            <button wire:click="confirmDeleteMovement({{ $movement->id }})" 
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    @else
                                        <span class="text-gray-400 text-xs">Auto</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-1">Tidak ada data riwayat stok</p>
                                <p>Belum ada transaksi stok yang tercatat dalam periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($movements->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- Modal Detail Stock Movement -->
    @if($showDetailModal && $selectedMovement)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            <i class="fas fa-eye mr-2"></i>Detail Pergerakan Stok
                        </h3>
                        <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="mt-4 space-y-6">
                        <!-- Informasi Dasar -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedMovement->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Pergerakan</label>
                                <p class="mt-1 text-sm">
                                    @if($selectedMovement->type === 'IN')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-arrow-up mr-1"></i> Stok Masuk
                                        </span>
                                    @elseif($selectedMovement->type === 'OUT')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-arrow-down mr-1"></i> Stok Keluar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-edit mr-1"></i> Penyesuaian
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Produk</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $selectedMovement->product->name }}</p>
                                <p class="text-xs text-gray-500">SKU: {{ $selectedMovement->product->sku }}</p>
                                <p class="text-xs text-gray-500">Gudang: {{ $selectedMovement->warehouse->name ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Informasi Stok -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Informasi Stok</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                                    <p class="mt-1 text-sm font-semibold">
                                        @if($selectedMovement->type === 'IN')
                                            <span class="text-green-600">+{{ number_format($selectedMovement->qty) }}</span>
                                        @elseif($selectedMovement->type === 'OUT')
                                            <span class="text-red-600">{{ number_format($selectedMovement->qty) }}</span>
                                        @else
                                            <span class="text-yellow-600">{{ number_format($selectedMovement->qty) }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Sebelum</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ number_format($selectedMovement->stock_before) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Sesudah</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ number_format($selectedMovement->stock_after) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ ucfirst($selectedMovement->ref_type ?? 'Manual') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Tambahan -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Informasi Tambahan</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Satuan</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <span class="text-gray-400">-</span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dilakukan Oleh</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedMovement->performedBy->name ?? $selectedMovement->user->name ?? 'System' }}</p>
                                    @if($selectedMovement->approvedBy)
                                        <p class="mt-1 text-xs text-green-600">Disetujui oleh: {{ $selectedMovement->approvedBy->name }}</p>
                                    @elseif($selectedMovement->requires_approval && !$selectedMovement->approved_at)
                                        <p class="mt-1 text-xs text-yellow-600">Menunggu persetujuan</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Referensi</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <span class="px-2 py-1 rounded-full text-xs {{ $selectedMovement->ref_type === 'manual' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($selectedMovement->ref_type) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedMovement->note ?: '-' }}</p>
                                </div>
                            </div>
                            

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end">
                        <button wire:click="closeDetailModal" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Edit Stock Movement -->
    @if($showEditModal && $selectedMovement)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeEditModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            <i class="fas fa-edit mr-2"></i>Edit Pergerakan Stok
                        </h3>
                        <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit.prevent="updateMovement" class="mt-4 space-y-4">
                        <!-- Product Info (Read Only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Produk</label>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $selectedMovement->product->name }}</p>
                                <p class="text-xs text-gray-500">SKU: {{ $selectedMovement->product->sku }}</p>
                                <p class="text-xs text-gray-500">Gudang: {{ $selectedMovement->warehouse->name ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Movement Type (Read Only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis Pergerakan</label>
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                @if($selectedMovement->type === 'IN')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-arrow-up mr-1"></i> Stok Masuk
                                    </span>
                                @elseif($selectedMovement->type === 'OUT')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-arrow-down mr-1"></i> Stok Keluar
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-edit mr-1"></i> Penyesuaian
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label for="editQty" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jumlah *</label>
                            <input type="number" 
                                   id="editQty"
                                   wire:model="editQty" 
                                   min="1" 
                                   step="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="Masukkan jumlah">
                            @error('editQty') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="editNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan</label>
                            <textarea id="editNotes"
                                      wire:model="editNotes" 
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                      placeholder="Catatan tambahan (opsional)"></textarea>
                            @error('editNotes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" 
                                    wire:click="closeEditModal"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-700">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Image Preview Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden" onclick="closeImageModal()">
        <div class="relative max-w-4xl max-h-full p-4" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-75">
                <i class="fas fa-times"></i>
            </button>
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
            <div id="modalImageName" class="text-white text-center mt-2 font-medium"></div>
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
