<div>
    <!-- Trigger Button -->
    @can('manage', App\StockMovement::class)
        <button wire:click="openModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Update Stok
        </button>
    @else
        <div class="text-gray-500 text-sm">
            <i class="fas fa-lock mr-1"></i>
            Anda tidak memiliki akses untuk mengelola stok
        </div>
    @endcan

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Update Stok Produk
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <!-- Warehouse Selection -->
                                    <div>
                                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">
                                            Lokasi Gudang *
                                        </label>
                                        <select wire:model.live="warehouse_id" id="warehouse_id"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">Pilih Gudang</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">
                                                    {{ $warehouse->name }} ({{ $warehouse->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('warehouse_id')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Product Selection with Search -->
                    <div>
                        <label for="product_search" class="block text-sm font-medium text-gray-700 mb-1">
                            Produk *
                        </label>
                        <div class="relative" x-data="{ showResults: @entangle('showSearchResults'), showDropdown: @entangle('showDropdown') }">
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.300ms="productSearch" 
                                       id="product_search"
                                       placeholder="Cari nama produk, SKU, atau barcode..."
                                       class="block w-full px-3 py-2 pr-8 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                       autocomplete="off">
                                
                                <!-- Dropdown Arrow Button -->
                                <button type="button" 
                                        wire:click="toggleDropdown"
                                        class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                                    @if($productSearch)
                                        <!-- Clear button when there's text -->
                                        <svg wire:click.stop="clearProductSearch" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    @else
                                        <!-- Dropdown arrow when no text -->
                                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </div>
                            
                            <!-- Search Results Dropdown -->
                            @if($showSearchResults && count($searchResults) > 0)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    @foreach($searchResults as $product)
                                        <button type="button" 
                                                wire:click="selectProduct({{ $product->id }})"
                                                class="w-full px-3 py-2 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none border-b border-gray-100 last:border-b-0">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                                    <div class="text-sm text-gray-500">SKU: {{ $product->sku }}</div>
                                                    @if($product->barcode)
                                                        <div class="text-xs text-gray-400">Barcode: {{ $product->barcode }}</div>
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $product->category ?? 'No Category' }}
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @elseif($showSearchResults && strlen($productSearch) >= 2)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                    <div class="px-3 py-2 text-gray-500 text-sm">
                                        Tidak ada produk ditemukan untuk "{{ $productSearch }}"
                                    </div>
                                </div>
                            @endif
                            
                            <!-- All Products Dropdown (when no search) -->
                            @if($showDropdown && empty($productSearch))
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    @if(count($allProducts) > 0)
                                        @foreach($allProducts as $product)
                                            <button type="button" 
                                                    wire:click="selectProduct({{ $product->id }})"
                                                    class="w-full px-3 py-2 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none border-b border-gray-100 last:border-b-0">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                                        <div class="text-sm text-gray-500">SKU: {{ $product->sku }}</div>
                                                        @if($product->barcode)
                                                            <div class="text-xs text-gray-400">Barcode: {{ $product->barcode }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $product->category ?? 'No Category' }}
                                                    </div>
                                                </div>
                                            </button>
                                        @endforeach
                                    @else
                                        <div class="px-3 py-2 text-gray-500 text-sm">
                                            Tidak ada produk tersedia
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        @error('product_id') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                        @enderror
                        
                        @if($selectedProduct)
                            <div class="mt-2 p-2 bg-blue-50 rounded-md">
                                <div class="text-sm text-blue-800">
                                    <strong>{{ $selectedProduct->name }}</strong> ({{ $selectedProduct->sku }})
                                </div>
                                <div class="text-xs text-blue-600 mt-1">
                                    Stok lokasi terpilih: <span class="font-semibold">{{ number_format($currentWarehouseStock) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                                    <!-- Movement Type -->
                                    <div>
                                        <label for="movement_type" class="block text-sm font-medium text-gray-700 mb-1">
                                            Jenis Perubahan *
                                        </label>
                                        <select wire:model.live="movement_type" id="movement_type" 
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="in">Stok Masuk</option>
                                            <option value="out">Stok Keluar</option>
                                            <option value="adjustment">Penyesuaian Stok</option>
                                        </select>
                                        @error('movement_type') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                  
                                    </div>

                                    <!-- Quantity -->
                                    <div>
                                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                            Jumlah *
                                        </label>
                                        <input type="number" id="quantity" wire:model.live="quantity"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               min="1" placeholder="Masukkan jumlah">
                                        @error('quantity') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <!-- Reason Code -->
                                    <div>
                                        <label for="reason_code" class="block text-sm font-medium text-gray-700 mb-1">
                                            Kode Alasan
                                        </label>
                                        <input type="text" id="reason_code" wire:model.live="reason_code"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="Contoh: stock_opname, retur, expired">
                                        @error('reason_code') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                            Catatan
                                        </label>
                                        <textarea wire:model="notes" id="notes" rows="3"
                                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                  placeholder="Catatan tambahan (opsional)"></textarea>
                                        @error('notes') 
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="closeModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

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

    @if (session()->has('error'))
        <div class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif
</div>
