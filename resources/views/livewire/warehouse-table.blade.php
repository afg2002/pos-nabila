<div class="min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-xl shadow-xl border-l-4 border-emerald-400 flex items-center backdrop-blur-sm" 
             x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-500" 
             x-transition:enter-start="opacity-0 transform translate-y-4 scale-95" 
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100" 
             x-transition:leave="transition ease-in duration-300" 
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 transform translate-y-4 scale-95" 
             x-init="setTimeout(() => show = false, 4000)">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="flex-1 font-medium">{{ session('message') }}</span>
            <button @click="show = false" class="ml-3 text-white hover:text-emerald-200 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-500 to-cyan-600 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-black">Manajemen Gudang</h1>
                <p class="text-teal-100 mt-1">Kelola data gudang, toko, dan kiosk dengan mudah</p>
            </div>
            @can('create', App\Warehouse::class)
                <button wire:click="openCreateModal" 
                        class="mt-4 sm:mt-0 px-4 py-2 bg-white bg-opacity-20 backdrop-blur-sm text-white rounded-lg hover:bg-opacity-30 transition-all duration-200 border border-white border-opacity-30">
                    <i class="fas fa-plus mr-2"></i>Tambah Gudang
                </button>
            @endcan
        </div>
    </div>

    <!-- Modal Detail Warehouse -->
    @if($showDetailModal && $selectedWarehouse)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            <i class="fas fa-warehouse mr-2"></i>Detail Warehouse
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
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Warehouse</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $selectedWarehouse ? $selectedWarehouse->code : '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                                <div class="mt-1">
                                    @if($selectedWarehouse)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $selectedWarehouse->type === 'main' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $selectedWarehouse->type === 'branch' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $selectedWarehouse->type === 'storage' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $selectedWarehouse->type === 'transit' ? 'bg-purple-100 text-purple-800' : '' }}
                                            @if($selectedWarehouse->type === 'main')
                                                <i class="fas fa-building mr-1"></i>
                                            @elseif($selectedWarehouse->type === 'branch')
                                                <i class="fas fa-code-branch mr-1"></i>
                                            @elseif($selectedWarehouse->type === 'storage')
                                                <i class="fas fa-archive mr-1"></i>
                                            @elseif($selectedWarehouse->type === 'transit')
                                                <i class="fas fa-truck mr-1"></i>
                                            @endif
                                            {{ ucfirst($selectedWarehouse->type) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Warehouse</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $selectedWarehouse ? $selectedWarehouse->name : '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedWarehouse ? ($selectedWarehouse->address ?: 'Tidak ada alamat') : '-' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cabang</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedWarehouse ? ($selectedWarehouse->branch ?: 'Tidak ada cabang') : '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedWarehouse ? ($selectedWarehouse->phone ?: 'Tidak ada telepon') : '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warehouse Default</label>
                                <div class="mt-1">
                                    @if($selectedWarehouse)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $selectedWarehouse->is_default ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            <i class="fas {{ $selectedWarehouse->is_default ? 'fa-star' : 'fa-star-o' }} mr-1"></i>
                                            {{ $selectedWarehouse->is_default ? 'Default' : 'Bukan Default' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Statistik Stok -->
                        @if($selectedWarehouse && $selectedWarehouse->productStocks->count() > 0)
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Statistik Stok</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $selectedWarehouse->productStocks->count() }}</div>
                                        <div class="text-sm text-blue-700 dark:text-blue-300">Total Produk</div>
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-green-900 dark:text-green-100">{{ number_format($selectedWarehouse->productStocks->sum('stock_on_hand')) }}</div>
                                        <div class="text-sm text-green-700 dark:text-green-300">Total Stok</div>
                                    </div>
                                    <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-red-900 dark:text-red-100">
                                            {{ $selectedWarehouse->productStocks->filter(function($stock) { return $stock->stock_on_hand <= ($stock->product->min_stock ?? 0); })->count() }}
                                        </div>
                                        <div class="text-sm text-red-700 dark:text-red-300">Stok Menipis</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Products -->
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Produk dengan Stok Terbanyak</h4>
                                <div class="space-y-3">
                                    @foreach($selectedWarehouse->productStocks->sortByDesc('stock_on_hand')->take(10) as $stock)
                                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $stock->product->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $stock->product->sku }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-bold text-gray-900 dark:text-white">{{ number_format($stock->stock_on_hand) }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $stock->product->unit ? $stock->product->unit->abbreviation : 'pcs' }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                                <div class="text-center py-8">
                                    <i class="fas fa-box-open fa-3x text-gray-300 mb-4"></i>
                                    <p>Belum ada stok produk di warehouse ini</p>
                                </div>
                            </div>
                        @endif

                        <!-- Informasi Tambahan -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dibuat</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $selectedWarehouse ? $selectedWarehouse->created_at->format('d/m/Y H:i') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Terakhir Diupdate</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $selectedWarehouse ? $selectedWarehouse->updated_at->format('d/m/Y H:i') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end">
                        <button wire:click="closeDetailModal" 
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Controls -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Search and Filters -->
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                <!-- Search -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Cari gudang..." 
                           class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200">
                </div>
                
                <!-- Type Filter -->
                <div class="sm:w-48">
                    <select wire:model.live="typeFilter" 
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200">
                        <option value="">Semua Tipe</option>
                        @foreach($warehouseTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                @can('create', App\Warehouse::class)
                    <button wire:click="openCreateModal"
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transform hover:scale-105 transition-all duration-200 shadow-md">
                        <i class="fas fa-plus mr-2"></i>Tambah Gudang
                    </button>
                @endcan
                @if(count($selectedWarehouses) > 0)
                    <button wire:click="confirmBulkDelete" 
                            class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 transform hover:scale-105 transition-all duration-200 shadow-md">
                        <i class="fas fa-trash mr-2"></i>Hapus Terpilih ({{ count($selectedWarehouses) }})
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Warehouses Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll" 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('name')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-100">
                                <span>Nama Gudang</span>
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('code')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-100">
                                <span>Kode</span>
                                @if($sortField === 'code')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('type')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-100">
                                <span>Tipe</span>
                                @if($sortField === 'type')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-gray-400"></i>
                                @endif
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Telepon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($warehouses as $warehouse)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedWarehouses" value="{{ $warehouse->id }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $warehouse->name }}
                                    @if($warehouse->is_default)
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-star mr-1"></i>Default
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-gray-900 dark:text-white">{{ $warehouse->code }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($warehouse->type)
                                        @case('main')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @case('branch')
                                            bg-green-100 text-green-800
                                            @break
                                        @case('warehouse')
                                            bg-green-100 text-green-800
                                            Gudang
                                            @break
                                        @case('storage')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('transit')
                                            bg-purple-100 text-purple-800
                                            @break
                                        @default
                                            bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ ucfirst($warehouse->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $warehouse->branch ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div class="max-w-xs truncate" title="{{ $warehouse->address }}">
                                    {{ $warehouse->address ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $warehouse->phone ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    @can('view', $warehouse)
                                        <button wire:click="openDetailModal({{ $warehouse->id }})"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('update', $warehouse)
                                        <button wire:click="openEditModal({{ $warehouse->id }})"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    
                                    @can('delete', $warehouse)
                                        @if(!$warehouse->is_default)
                                            <button wire:click="confirmDelete({{ $warehouse->id }})"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-warehouse fa-3x mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium mb-2">Tidak ada gudang ditemukan</p>
                                    <p class="text-sm">Belum ada data gudang yang sesuai dengan pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    {{ $warehouses->links('pagination::simple-tailwind') }}
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Menampilkan
                            <span class="font-medium">{{ $warehouses->firstItem() ?? 0 }}</span>
                            sampai
                            <span class="font-medium">{{ $warehouses->lastItem() ?? 0 }}</span>
                            dari
                            <span class="font-medium">{{ $warehouses->total() }}</span>
                            hasil
                        </p>
                    </div>
                    <div>
                        {{ $warehouses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tambah Gudang</h3>
                    <form wire:submit.prevent="createWarehouse">
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="create_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Gudang</label>
                                <input type="text" wire:model="name" id="create_name"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="create_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Gudang</label>
                                <input type="text" wire:model="code" id="create_code"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="create_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                                <select wire:model="type" id="create_type"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Pilih Tipe</option>
                                    @foreach($warehouseTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="create_branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cabang</label>
                                <input type="text" wire:model="branch" id="create_branch"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('branch') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="create_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                <textarea wire:model="address" id="create_address" rows="3"
                                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="create_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon</label>
                                <input type="text" wire:model="phone" id="create_phone"
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_default" id="create_is_default"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="create_is_default" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Jadikan sebagai gudang default
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" wire:click="closeModal"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Gudang</h3>
                    <form wire:submit.prevent="updateWarehouse">
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Gudang</label>
                                <input type="text" wire:model="name" id="edit_name" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Gudang</label>
                                <input type="text" wire:model="code" id="edit_code" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                                <select wire:model="type" id="edit_type" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Pilih Tipe</option>
                                    @foreach($warehouseTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cabang</label>
                                <input type="text" wire:model="branch" id="edit_branch" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('branch') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                <textarea wire:model="address" id="edit_address" rows="3"
                                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="edit_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon</label>
                                <input type="text" wire:model="phone" id="edit_phone" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_default" id="edit_is_default" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="edit_is_default" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                    Jadikan sebagai gudang default
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" wire:click="closeModal" 
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal && $warehouseToDelete)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Konfirmasi Hapus</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Apakah Anda yakin ingin menghapus gudang <strong>{{ $warehouseToDelete->name }}</strong>?
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-3 mt-4">
                        <button wire:click="closeModal" 
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Batal
                        </button>
                        <button wire:click="deleteWarehouse"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
