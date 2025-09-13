<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Produk</h2>
        <p class="text-gray-600 dark:text-gray-400">Kelola data produk, stok, dan harga</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters and Actions -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cari Produk</label>
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Nama, SKU, atau barcode..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                <select wire:model.live="category" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select wire:model.live="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak Aktif</option>
                </select>
            </div>

            <!-- Per Page -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Per Halaman</label>
                <select wire:model.live="perPage" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2">
            @can('create', App\Product::class)
                <button wire:click="openCreateModal" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-plus mr-2"></i>Tambah Produk
                </button>
                
                <a href="{{ route('products.import') }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-file-import mr-2"></i>Import Excel
                </a>
                
                <button wire:click="exportProducts" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <i class="fas fa-file-export mr-2"></i>Export Excel
                </button>
            @endcan
            
            @can('bulkDelete', App\Product::class)
                @if(count($selectedProducts) > 0)
                    <!-- Bulk Price Type Change -->
                    <div class="flex items-center space-x-2">
                        <select wire:model="bulkPriceType" 
                                class="text-sm border border-gray-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(\App\Product::getPriceTypes() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button wire:click="bulkSetPriceType" 
                                class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <i class="fas fa-tag mr-1"></i>Set Jenis Harga
                        </button>
                    </div>
                    
                    <button wire:click="bulkDelete" 
                            onclick="return confirm('Yakin ingin menghapus {{ count($selectedProducts) }} produk terpilih?')"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <i class="fas fa-trash mr-2"></i>Hapus Terpilih ({{ count($selectedProducts) }})
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
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
                            Stok
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('price_retail')">
                            Harga Retail
                            @if($sortField === 'price_retail')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('price_semi_grosir')">
                            Harga Semi Grosir
                            @if($sortField === 'price_semi_grosir')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('price_grosir')">
                            Harga Grosir
                            @if($sortField === 'price_grosir')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" 
                            wire:click="sortBy('default_price_type')">
                            Jenis Harga
                            @if($sortField === 'default_price_type')
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
                                @if($product->barcode)
                                    <div class="text-xs text-gray-500">{{ $product->barcode }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <!-- Product Photo -->
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
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
                                @php
                                    $currentStock = $product->current_stock;
                                    $isLowStock = $product->isLowStock();
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs {{ $isLowStock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                    {{ number_format($currentStock) }}
                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                Rp {{ number_format($product->price_retail, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @if($product->price_semi_grosir)
                                    Rp {{ number_format($product->price_semi_grosir, 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                Rp {{ number_format($product->price_grosir, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $product->default_price_type === 'retail' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $product->default_price_type === 'semi_grosir' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $product->default_price_type === 'grosir' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $product->default_price_type === 'custom' ? 'bg-purple-100 text-purple-800' : '' }}">
                                    {{ $product->getPriceTypeDisplayName() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleStatus({{ $product->id }})" 
                                        class="px-2 py-1 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @can('view', $product)
                                        <button wire:click="openDetailModal({{ $product->id }})" 
                                                class="text-green-600 hover:text-green-900 dark:text-green-400"
                                                title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endcan
                                    @can('update', $product)
                                        <button wire:click="openEditModal({{ $product->id }})" 
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400"
                                                title="Edit Produk">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    @can('delete', $product)
                                        <button wire:click="delete({{ $product->id }})" 
                                                onclick="return confirm('Yakin ingin menghapus produk ini?')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400"
                                                title="Hapus Produk">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                    @if(!Gate::allows('view', $product) && !Gate::allows('update', $product) && !Gate::allows('delete', $product))
                                        <span class="text-gray-400 text-sm">Tidak ada aksi</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-box-open text-4xl mb-4"></i>
                                <p class="text-lg">Tidak ada produk ditemukan</p>
                                <p class="text-sm">Tambahkan produk baru atau ubah filter pencarian</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create/Edit Product -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ $editMode ? 'Edit Produk' : 'Tambah Produk Baru' }}
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- SKU -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU *</label>
                                    <input type="text" wire:model="sku" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('sku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Barcode -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                                    <input type="text" wire:model="barcode" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('barcode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Name -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Produk *</label>
                                    <input type="text" wire:model="name" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori *</label>
                                    <input type="text" wire:model="categoryInput" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('categoryInput') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Photo -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Produk</label>
                                    <div class="space-y-2">
                                        @if($editMode && $currentPhoto)
                                            <div class="flex items-center space-x-3">
                                                <img src="{{ asset('storage/products/' . $currentPhoto) }}" 
                                                     alt="Current Photo" 
                                                     class="w-16 h-16 object-cover rounded-lg border">
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    <p>Foto saat ini</p>
                                                    <p class="text-xs">Upload foto baru untuk menggantinya</p>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($photo)
                                            <div class="flex items-center space-x-3">
                                                <img src="{{ $photo->temporaryUrl() }}" 
                                                     alt="Preview" 
                                                     class="w-16 h-16 object-cover rounded-lg border">
                                                <div class="text-sm text-green-600">
                                                    <p>Foto baru (preview)</p>
                                                    <button type="button" wire:click="$set('photo', null)" 
                                                            class="text-red-500 hover:text-red-700 text-xs">
                                                        Hapus preview
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <input type="file" wire:model="photo" 
                                               accept="image/*"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        <p class="text-xs text-gray-500">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB</p>
                                    </div>
                                    @error('photo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Unit -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Satuan *</label>
                                    <div class="flex gap-2">
                                        <select wire:model="unit_id" 
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Pilih Satuan</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="openUnitModal" 
                                                class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                title="Tambah Unit Baru">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    @error('unit_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Base Cost -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Pokok *</label>
                                    <input type="number" wire:model="base_cost" step="0.01" min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('base_cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Price Retail -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Retail *</label>
                                    <input type="number" wire:model="price_retail" step="0.01" min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('price_retail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Price Semi Grosir -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Semi Grosir</label>
                                    <input type="number" wire:model="price_semi_grosir" step="0.01" min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('price_semi_grosir') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Price Grosir -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Grosir *</label>
                                    <input type="number" wire:model="price_grosir" step="0.01" min="0"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('price_grosir') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Min Margin -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Margin Minimum (%) *</label>
                                    <input type="number" wire:model="min_margin_pct" step="0.01" min="0" max="100"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('min_margin_pct') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Default Price Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Harga Default *</label>
                                    <select wire:model="default_price_type" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                        @foreach(\App\Product::getPriceTypes() as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('default_price_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Status -->
                                <div class="md:col-span-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="is_active" 
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Produk Aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $editMode ? 'Update' : 'Simpan' }}
                            </button>
                            <button type="button" wire:click="closeModal" 
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-700">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Detail Produk -->
    @if($showDetailModal && $selectedProduct)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3">
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            <i class="fas fa-eye mr-2"></i>Detail Produk
                        </h3>
                        <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="mt-4 space-y-6">
                        <!-- Product Photo -->
                        <div class="flex flex-col items-center space-y-4">
                            <div class="relative">
                                @if($showPhotoEditMode && $tempPhoto)
                                    <img src="{{ $tempPhoto }}" 
                                         alt="Preview"
                                         class="w-32 h-32 object-cover rounded-lg shadow-md">
                                    <div class="absolute top-2 right-2 bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">
                                        Preview
                                    </div>
                                @else
                                    <img src="{{ $selectedProduct->getPhotoUrl() }}" 
                                         alt="{{ $selectedProduct->name }}"
                                         class="w-32 h-32 object-cover rounded-lg shadow-md cursor-pointer hover:opacity-80 transition-opacity"
                                         wire:click.stop
                                         onclick="event.stopPropagation(); openImageModal('{{ $selectedProduct->getPhotoUrl() }}', '{{ addslashes($selectedProduct->name) }}')">
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity bg-black bg-opacity-20 rounded-lg pointer-events-none">
                                        <i class="fas fa-search-plus text-white text-xl"></i>
                                    </div>
                                @endif
                                
                                <!-- Loading Overlay -->
                                <div wire:loading wire:target="updateProductPhoto,removeProductPhoto" 
                                     class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg">
                                    <div class="text-center">
                                        <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-xs text-gray-600 mt-1">Memproses...</p>
                                    </div>
                                </div>
                            </div>
                            
                            @can('update', $selectedProduct)
                                @if($showPhotoEditMode)
                                    <!-- Photo Edit Form -->
                                    <div class="w-full max-w-sm space-y-3">
                                        <div>
                                            <input type="file" wire:model="newPhoto" accept="image/*" 
                                                   @if($isUploadingPhoto || $isUpdatingPhoto) disabled @endif
                                                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 disabled:opacity-50">
                                            @error('newPhoto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            
                                            <!-- Upload Progress -->
                                            <div wire:loading wire:target="newPhoto" class="mt-2">
                                                <div class="flex items-center text-blue-600 text-xs">
                                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Mengunggah foto...
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex space-x-2">
                                            <button wire:click="updateProductPhoto" 
                                                    wire:loading.attr="disabled" 
                                                    wire:target="updateProductPhoto"
                                                    @if(!$newPhoto || $isUploadingPhoto) disabled @endif
                                                    class="px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center">
                                                <span wire:loading.remove wire:target="updateProductPhoto">
                                                    <i class="fas fa-save mr-1"></i>Simpan
                                                </span>
                                                <span wire:loading wire:target="updateProductPhoto" class="flex items-center">
                                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Menyimpan...
                                                </span>
                                            </button>
                                            <button wire:click="togglePhotoEditMode" 
                                                    @if($isUpdatingPhoto) disabled @endif
                                                    class="px-3 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                                <i class="fas fa-times mr-1"></i>Batal
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <!-- Photo Action Buttons -->
                                    <div class="flex space-x-2">
                                        <button wire:click="togglePhotoEditMode" 
                                                class="px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                            <i class="fas fa-camera mr-1"></i>{{ $selectedProduct->photo ? 'Ubah Foto' : 'Tambah Foto' }}
                                        </button>
                                        @if($selectedProduct->photo)
                                            <button wire:click="removeProductPhoto" 
                                                    wire:loading.attr="disabled" 
                                                    wire:target="removeProductPhoto"
                                                    wire:confirm="Yakin ingin menghapus foto ini?"
                                                    class="px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center">
                                                <span wire:loading.remove wire:target="removeProductPhoto">
                                                    <i class="fas fa-trash mr-1"></i>Hapus Foto
                                                </span>
                                                <span wire:loading wire:target="removeProductPhoto" class="flex items-center">
                                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Menghapus...
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            @endcan
                        </div>
                        
                        <!-- Informasi Dasar -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedProduct->sku }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Barcode</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedProduct->barcode ?: '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Produk</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold">{{ $selectedProduct->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedProduct->category }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $selectedProduct->unit ? $selectedProduct->unit->name . ' (' . $selectedProduct->unit->abbreviation . ')' : '-' }}</p>
                            </div>
                        </div>

                        <!-- Informasi Harga -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Informasi Harga</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Dasar</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">Rp {{ number_format($selectedProduct->base_cost, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Retail</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold text-blue-600">Rp {{ number_format($selectedProduct->price_retail, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Semi Grosir</label>
                                    @if($selectedProduct->price_semi_grosir)
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold text-yellow-600">Rp {{ number_format($selectedProduct->price_semi_grosir, 0, ',', '.') }}</p>
                                    @else
                                        <p class="mt-1 text-sm text-gray-400">Tidak diset</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Grosir</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold text-green-600">Rp {{ number_format($selectedProduct->price_grosir, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Harga Default</label>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                                        {{ $selectedProduct->default_price_type === 'retail' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $selectedProduct->default_price_type === 'semi_grosir' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $selectedProduct->default_price_type === 'grosir' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $selectedProduct->default_price_type === 'custom' ? 'bg-purple-100 text-purple-800' : '' }}">
                                        {{ $selectedProduct->getPriceTypeDisplayName() }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Price Comparison -->
                            <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Perbandingan Margin</h5>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                    @php
                                        $baseCost = $selectedProduct->base_cost;
                                        $retailMargin = $baseCost > 0 ? (($selectedProduct->price_retail - $baseCost) / $baseCost) * 100 : 0;
                                        $semiGrosirMargin = $selectedProduct->price_semi_grosir && $baseCost > 0 ? (($selectedProduct->price_semi_grosir - $baseCost) / $baseCost) * 100 : 0;
                                        $grosirMargin = $baseCost > 0 ? (($selectedProduct->price_grosir - $baseCost) / $baseCost) * 100 : 0;
                                    @endphp
                                    
                                    <div class="bg-blue-50 dark:bg-blue-900 p-2 rounded">
                                        <span class="font-medium text-blue-700 dark:text-blue-300">Retail:</span>
                                        <span class="text-blue-600 dark:text-blue-400">{{ number_format($retailMargin, 1) }}%</span>
                                    </div>
                                    
                                    @if($selectedProduct->price_semi_grosir)
                                        <div class="bg-yellow-50 dark:bg-yellow-900 p-2 rounded">
                                            <span class="font-medium text-yellow-700 dark:text-yellow-300">Semi Grosir:</span>
                                            <span class="text-yellow-600 dark:text-yellow-400">{{ number_format($semiGrosirMargin, 1) }}%</span>
                                        </div>
                                    @endif
                                    
                                    <div class="bg-green-50 dark:bg-green-900 p-2 rounded">
                                        <span class="font-medium text-green-700 dark:text-green-300">Grosir:</span>
                                        <span class="text-green-600 dark:text-green-400">{{ number_format($grosirMargin, 1) }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Stok -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Informasi Stok</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Saat Ini</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <span class="px-2 py-1 rounded-full text-xs {{ $selectedProduct->isLowStock() ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ number_format($selectedProduct->current_stock) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <p class="mt-1 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs {{ $selectedProduct->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $selectedProduct->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Riwayat Stock Movement (10 terakhir) -->
                        @if($selectedProduct->stockMovements->count() > 0)
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Riwayat Pergerakan Stok (10 Terakhir)</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipe</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                            @foreach($selectedProduct->stockMovements as $movement)
                                                <tr>
                                                    <td class="px-3 py-2 text-xs text-gray-900 dark:text-white">
                                                        {{ $movement->created_at->format('d/m/Y H:i') }}
                                                    </td>
                                                    <td class="px-3 py-2 text-xs">
                                                        <span class="px-2 py-1 rounded-full text-xs {{ $movement->type === 'IN' ? 'bg-green-100 text-green-800' : ($movement->type === 'OUT' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                                            {{ $movement->type }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-xs text-gray-900 dark:text-white">
                                                        {{ $movement->qty > 0 ? '+' : '' }}{{ number_format($movement->qty) }}
                                                    </td>
                                                    <td class="px-3 py-2 text-xs text-gray-900 dark:text-white">
                                                        {{ $movement->note ?: '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
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

    <!-- Modal Create Unit -->
    @if($showUnitModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="unit-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeUnitModal"></div>
                
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="saveUnit">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="unit-modal-title">
                                    Tambah Unit Baru
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Buat satuan produk baru untuk digunakan dalam sistem
                                </p>
                            </div>

                            <div class="space-y-4">
                                <!-- Unit Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Unit *</label>
                                    <input type="text" wire:model="newUnitName" placeholder="Contoh: Pieces, Kilogram, Liter"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('newUnitName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Unit Abbreviation -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Singkatan *</label>
                                    <input type="text" wire:model="newUnitAbbreviation" placeholder="Contoh: pcs, kg, ltr"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    @error('newUnitAbbreviation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Unit Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                                    <textarea wire:model="newUnitDescription" rows="3" placeholder="Deskripsi opsional untuk unit ini"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                    @error('newUnitDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Unit
                            </button>
                            <button type="button" wire:click="closeUnitModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-700">
                                Batal
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

    <!-- Toast Notifications -->
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg" 
             x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-x-full" 
             x-transition:enter-end="opacity-100 transform translate-x-0" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-x-0" 
             x-transition:leave-end="opacity-0 transform translate-x-full" 
             x-init="setTimeout(() => show = false, 4000)">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>{{ session('message') }}</span>
                <button @click="show = false" class="ml-4 text-green-500 hover:text-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg" 
             x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-x-full" 
             x-transition:enter-end="opacity-100 transform translate-x-0" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-x-0" 
             x-transition:leave-end="opacity-0 transform translate-x-full" 
             x-init="setTimeout(() => show = false, 5000)">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="ml-4 text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>
