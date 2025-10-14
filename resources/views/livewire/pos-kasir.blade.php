<div>
    <div class="pos-kasir-container min-h-screen bg-gray-50">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div id="success-alert" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center justify-between transition-all duration-300 ease-in-out">
            <span>{{ session('success') }}</span>
            <button onclick="closeAlert('success-alert')" class="ml-4 text-white hover:text-gray-200 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div id="error-alert" class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center justify-between transition-all duration-300 ease-in-out">
            <span>{{ session('error') }}</span>
            <button onclick="closeAlert('error-alert')" class="ml-4 text-white hover:text-gray-200 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:h-screen">
        <!-- Left Panel - Product Selection -->
        <div class="w-full md:w-1/2 bg-white md:border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="p-4 lg:p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Pilih Produk</h2>
                
                <!-- Barcode Scanner -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-barcode mr-2"></i>Scan Barcode
                        <span class="text-xs text-gray-500 ml-2">(F3 untuk focus)</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="barcode-input"
                               wire:model.live="barcode" 
                               placeholder="Scan atau ketik barcode..."
                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               autocomplete="off"
                               maxlength="50">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <i class="fas fa-qrcode text-gray-400"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Mendukung: EAN-13, UPC-A, Code 128, QR Code
                    </div>
                </div>

                <!-- Warehouse Info -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-blue-900">Gudang Aktif</h3>
                            <p class="text-sm text-blue-700">{{ $selectedWarehouse ?: 'Pilih Gudang' }}</p>
                        </div>
                        <i class="fas fa-warehouse text-blue-500"></i>
                    </div>
                </div>

                <!-- Product Search -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-search mr-2"></i>Cari Produk
                    </label>
                    <input type="text" 
                           wire:model.live="productSearch" 
                           placeholder="Cari nama produk atau SKU..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <!-- Custom Item Button -->
                <div>
                    <button type="button"
                            wire:click="showCustomItemModal"
                            class="w-full bg-green-600 text-white font-medium py-3 px-4 rounded-lg hover:bg-green-700 flex items-center justify-center transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Item Custom
                    </button>
                    <p class="text-xs text-gray-500 mt-2">Gunakan untuk menjual jasa/produk tanpa SKU</p>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-4 lg:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($products as $product)
                        <div class="product-card relative bg-gray-50 rounded-lg p-4 hover:bg-gray-100 cursor-pointer transition-colors border border-gray-200 hover:border-blue-300"
                             wire:click="addToCart({{ $product->id }})">
                            <!-- Product Image -->
                            <div class="w-full h-28 mb-3 bg-gray-200 rounded-lg overflow-hidden">
                                <img src="{{ $product ? $product->getPhotoUrl() : asset('storage/placeholders/no-image.svg') }}" 
                                     alt="{{ $product ? $product->name : 'No Product' }}"
                                     class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                     onclick="openImageModal('{{ $product ? $product->getPhotoUrl() : asset('storage/placeholders/no-image.svg') }}', '{{ $product ? addslashes($product->name) : 'No Product' }}')"
                                     onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                            </div>
                            
                            <div class="absolute top-3 right-3 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-md">
                                {{ $product ? $product->barcode : '-' }}
                            </div>
                            <div class="text-sm font-medium text-gray-900 truncate mb-1">{{ $product ? $product->name : 'No Product' }}</div>
                            <div class="text-xs text-gray-500 mb-3">{{ $product ? $product->sku : '-' }}</div>
                            <div class="text-sm font-semibold text-blue-600 mt-2">
                                @php
                                    $displayPrice = $product ? match($pricingTier) {
                                        'retail' => $product->getPriceByType('retail'),
                                        'semi_grosir' => $product->price_semi_grosir ?? $product->price_retail,
                                        'grosir' => $product->price_grosir,
                                        'custom' => $product->price_retail,
                                        default => $product->getPriceByType()
                                    } : 0;
                                @endphp
                                <div class="font-bold text-base">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                                
                                <!-- Show all available prices -->
                                <div class="text-xs space-y-1 mt-1">
                                    @if($pricingTier !== 'retail')
                                        <div class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $pricingTier)) }}</div>
                                    @else
                                        <div class="text-gray-500">{{ $product ? $product->getPriceTypeDisplayName() : '-' }}</div>
                                    @endif
                                    
                                    <!-- Price breakdown -->
                                    @if($product)
                                        <div class="bg-gray-50 rounded p-2 text-xs">
                                            <div class="text-blue-600">R: Rp {{ number_format($product->price_retail, 0, ',', '.') }}</div>
                                            @if($product->price_semi_grosir)
                                                <div class="text-yellow-600">SG: Rp {{ number_format($product->price_semi_grosir, 0, ',', '.') }}</div>
                                            @endif
                                            <div class="text-green-600">G: Rp {{ number_format($product->price_grosir, 0, ',', '.') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                @php
                                    $warehouseStock = $warehouseId ? 
                                        \App\ProductWarehouseStock::where('product_id', $product->id)
                                            ->where('warehouse_id', $warehouseId)
                                            ->value('stock_on_hand') ?? 0 : 
                                        $product->current_stock;
                                    $costPrice = $product ? $product->getEffectiveCostPrice() : 0;
                                    $profitMargin = $costPrice > 0 && $displayPrice > 0 ? (($displayPrice - $costPrice) / $displayPrice) * 100 : 0;
                                @endphp
                                <div>Stok: {{ number_format($warehouseStock) }} {{ $product->unit ? $product->unit->abbreviation : 'pcs' }}</div>
                                @if($warehouseId && $warehouseStock != $product->current_stock)
                                    <div class="text-blue-600">(Total: {{ number_format($product->current_stock) }})</div>
                                @endif
                                @if($costPrice > 0)
                                    <div class="text-orange-600 mt-1">Modal: Rp {{ number_format($costPrice, 0, ',', '.') }}</div>
                                    <div class="text-green-600">Profit: {{ number_format($profitMargin, 1) }}%</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Panel - Cart & Checkout -->
        <div class="w-full lg:w-2/5 xl:w-1/3 bg-white flex flex-col">
            <!-- Responsive Multi-Tab Navigation -->
            <div class="border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between p-3 lg:p-4">
                    <!-- Tab Buttons - Responsive Scrolling -->
                    <div class="flex-1 overflow-x-auto">
                        <div class="flex space-x-2 min-w-max pb-1">
                            @foreach($carts as $tabId => $tab)
                                <button wire:click="switchToTab({{ $tabId }})"
                                        class="px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap flex items-center {{ $activeTabId === $tabId ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                                    {{ $tab['name'] ?? 'Tab ' . $tabId }}
                                    @if(isset($tab['cart']) && count($tab['cart']) > 0)
                                        <span class="ml-2 px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full">
                                            {{ count($tab['cart']) }}
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Tab Actions -->
                    <div class="flex items-center space-x-2 ml-3">
                        <button wire:click="createNewTab" 
                                class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                title="Tab Baru (Ctrl+T)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </button>
                        @if(count($carts) > 1)
                            <button wire:click="closeTab({{ $activeTabId }})" 
                                    class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Tutup Tab (Ctrl+W)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Tab Actions Bar -->
                <div class="px-3 lg:px-4 pb-3 flex items-center justify-between">
                    <!-- Cart Name Input -->
                    <div class="flex-1 mr-3">
                        <input type="text" 
                               wire:model.blur="tabs.{{ $activeTabId }}.name"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="Nama keranjang...">
                    </div>
                    
                    <!-- Clear Cart Button -->
                    @if(isset($carts[$activeTabId]) && !empty($carts[$activeTabId]['cart']))
                        <button wire:click="confirmClearCart" 
                                class="px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                title="Kosongkan Keranjang (F2)">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Kosongkan
                        </button>
                    @endif
                </div>

                <!-- Bulk Price Type Controls -->
                @if(isset($carts[$activeTabId]) && !empty($carts[$activeTabId]['cart']))
                    <div class="px-3 lg:px-4 pb-3">
                        <div class="pricing-tier-selector">
                            <label class="block text-xs font-medium text-gray-700 mb-2">Ubah Semua Harga Ke:</label>
                            <select wire:change="updateAllItemsPriceType($event.target.value)"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">-- Pilih Jenis Harga --</option>
                                @foreach(\App\Product::getPriceTypes() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
                
                <!-- Keyboard Shortcuts Info -->
                <div class="px-3 lg:px-4 pb-3 text-xs text-gray-500 hidden lg:block">
                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                        <span>F1: Checkout</span>
                        <span>F2: Kosongkan</span>
                        <span>F3: Focus Barcode</span>
                        <span>F4: Cari Produk</span>
                        <span>Ctrl+T: Tab Baru</span>
                        <span>Ctrl+W: Tutup Tab</span>
                        <span>ESC: Batal/Tutup</span>
                    </div>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto">
                @if(!isset($carts[$activeTabId]) || empty($carts[$activeTabId]['cart']))
                    <div class="flex items-center justify-center h-full text-gray-500">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5M7 13l-1.1 5m0 0h9.1M6 18a2 2 0 100 4 2 2 0 000-4zm12 0a2 2 0 100 4 2 2 0 000-4z"></path>
                            </svg>
                            <p class="mt-2">Keranjang masih kosong</p>
                            <p class="text-sm">Scan barcode atau pilih produk</p>
                        </div>
                    </div>
                @else
                    <div class="p-4 lg:p-6 space-y-4">
                        @foreach(($carts[$activeTabId]['cart'] ?? []) as $key => $item)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3 flex-1">
                                        <!-- Product Image -->
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                            @if(isset($item['is_custom']) && $item['is_custom'])
                                                <!-- Custom item icon -->
                                                <div class="w-full h-full flex items-center justify-center bg-green-100">
                                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </div>
                                            @else
                                                @php
                                                    $product = $cartProducts->get($item['product_id']);
                                                    $photoUrl = $product && method_exists($product, 'getPhotoUrl') ? $product->getPhotoUrl() : asset('storage/placeholders/no-image.svg');
                                                @endphp
                                                <img src="{{ $photoUrl }}" 
                                                     alt="{{ $item['name'] }}"
                                                     class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                                     onclick="openImageModal('{{ $photoUrl }}', '{{ $item['name'] }}')"
                                                     onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                                            @endif
                                        </div>
                                        
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ $item['name'] }}</h4>
                                            @if(isset($item['is_custom']) && $item['is_custom'])
                                                <p class="text-sm text-green-600">Item Custom</p>
                                            @else
                                                <p class="text-sm text-gray-500">{{ $item['sku'] }}</p>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    @php
                                                        $selectedWarehouseStock = null;
                                                        if (! empty($warehouseId)) {
                                                            $selectedWarehouseStock = optional($product->warehouseStocks->first())->stock_on_hand ?? 0;
                                                        }
                                                    @endphp
                                                    Stok gudang: {{ number_format($selectedWarehouseStock ?? $product->current_stock) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <button wire:click="removeFromCart('{{ $key }}')"
                                            class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <!-- Quantity -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Jumlah</label>
                                        <input type="number" 
                                               wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                                               value="{{ $item['quantity'] }}"
                                               min="1" 
                                               @if(!isset($item['is_custom']) || !$item['is_custom'])
                                                   max="{{ $item['available_stock'] }}"
                                               @endif
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    
                                    <!-- Price Type Selector (only for regular products, not custom items) -->
                                    @if(!isset($item['is_custom']) || !$item['is_custom'])
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Harga</label>
                                            <select wire:change="updateItemPriceType('{{ $key }}', $event.target.value)"
                                                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                @foreach(\App\Product::getPriceTypes() as $value => $label)
                                                    <option value="{{ $value }}" {{ $item['pricing_tier'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <div class="text-xs text-gray-500 mt-1">
                                                @php
                                                    $product = \App\Product::find($item['product_id']);
                                                    $currentPriceType = $item['pricing_tier'];
                                                @endphp
                                                @if($product)
                                                    @if($currentPriceType === 'retail')
                                                        <span class="text-blue-600">Rp {{ number_format($product->price_retail ?? 0, 0, ',', '.') }}</span>
                                                    @elseif($currentPriceType === 'semi_grosir')
                                                        <span class="text-yellow-600">Rp {{ number_format($product->price_semi_grosir ?? 0, 0, ',', '.') }}</span>
                                                    @elseif($currentPriceType === 'grosir')
                                                        <span class="text-green-600">Rp {{ number_format($product->price_grosir ?? 0, 0, '.') }}</span>
                                                    @else
                                                        <span class="text-purple-600">Custom</span>
                                                    @endif
                                                @else
                                                    <span class="text-gray-500">Product not found</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <!-- Custom Item Price Display -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Harga</label>
                                            <div class="px-3 py-2 text-sm bg-green-50 border border-green-200 rounded-lg">
                                                <span class="text-green-600 font-medium">Item Custom</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Custom Price Input (only show for custom price type on regular products) -->
                                    @if((!isset($item['is_custom']) || !$item['is_custom']) && $item['pricing_tier'] === 'custom')
                                        <div class="col-span-2">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Harga Custom</label>
                                            <input type="number" 
                                                   wire:change="updatePrice('{{ $key }}', $event.target.value)"
                                                   value="{{ $item['price'] }}"
                                                   min="{{ $item['base_cost'] * 1.1 }}"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    @if(isset($item['is_custom']) && $item['is_custom'])
                                        <span class="text-xs text-green-600 font-medium">Item Custom</span>
                                    @else
                                        <div class="text-xs text-gray-500">
                                            <div>Stok: {{ $item['available_stock'] }}</div>
                                            @php
                                                $product = \App\Product::find($item['product_id']);
                                                $costPrice = $product ? $product->getEffectiveCostPrice() : 0;
                                                $profit = ($item['price'] - $costPrice) * $item['quantity'];
                                                $margin = $costPrice > 0 ? (($item['price'] - $costPrice) / $item['price']) * 100 : 0;
                                            @endphp
                                            <div class="text-orange-600">Modal: Rp {{ number_format($costPrice, 0, ',', '.') }}</div>
                                            <div class="text-green-600">Profit: Rp {{ number_format($profit, 0, ',', '.') }} ({{ number_format($margin, 1) }}%)</div>
                                        </div>
                                    @endif
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">
                                            {{ number_format($item['quantity']) }} × Rp {{ number_format($item['price'], 0, ',', '.') }}
                                        </div>
                                        <span class="font-semibold text-blue-600 text-lg">
                                            Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Cart Summary & Checkout - Moved to Bottom -->
            @if(isset($carts[$activeTabId]) && !empty($carts[$activeTabId]['cart']))
                <div class="border-t border-gray-200 bg-white p-4 lg:p-6">
                    <!-- Summary -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @php
                            $totalCost = 0;
                            $totalProfit = 0;
                            foreach(($carts[$activeTabId]['cart'] ?? []) as $item) {
                                if (!isset($item['is_custom']) || !$item['is_custom']) {
                                    $product = \App\Product::find($item['product_id']);
                                    $costPrice = $product ? $product->getEffectiveCostPrice() : 0;
                                    $itemCost = $costPrice * $item['quantity'];
                                    $itemProfit = ($item['price'] - $costPrice) * $item['quantity'];
                                    $totalCost += $itemCost;
                                    $totalProfit += $itemProfit;
                                }
                            }
                            $profitMargin = $subtotal > 0 ? ($totalProfit / $subtotal) * 100 : 0;
                        @endphp
                        <div class="flex justify-between text-sm">
                            <span class="text-orange-600">Total Modal:</span>
                            <span class="font-medium text-orange-600">Rp {{ number_format($totalCost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600">Total Profit:</span>
                            <span class="font-medium text-green-600">Rp {{ number_format($totalProfit, 0, ',', '.') }} ({{ number_format($profitMargin, 1) }}%)</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-3 border-gray-200">
                            <span class="text-gray-900">Total:</span>
                            <span class="text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <button wire:click="openCheckout" 
                            class="w-full bg-blue-600 text-white py-4 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl"
                            title="Shortcut: F1">
                        <i class="fas fa-cash-register mr-2"></i>
                        Checkout (F1)
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Checkout Modal -->
    @if($showCheckoutModal)
        <div class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold mb-4">Checkout</h3>
                
                <form wire:submit="processCheckout">
                    <!-- Customer Info -->
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggan</label>
                            <input type="text" wire:model="customerName" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" wire:model="customerPhone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                            <select wire:model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="edc">EDC/Kartu</option>
                                <option value="qr">QR Code</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Pembayaran</label>
                            <input type="text" wire:model="paymentNotes" placeholder="Opsional..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Bayar</label>
                            <input type="number" wire:model.live="amountPaid" min="{{ $total }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    
                    <!-- Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex justify-between text-sm mb-2">
                            <span>Total:</span>
                            <span class="font-semibold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span>Bayar:</span>
                            <span>Rp {{ number_format($amountPaid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold border-t pt-2">
                            <span>Kembalian:</span>
                            <span class="{{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($change, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" wire:click="closeCheckout" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" {{ $amountPaid < $total ? 'disabled' : '' }}>
                            Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Receipt Modal -->
    @if($showReceiptModal && $lastSale)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4" id="receipt">
                <div class="text-center mb-6">
                    <h3 class="text-lg font-bold">STRUK PENJUALAN</h3>
                    <p class="text-sm text-gray-600">{{ $lastSale->sale_number }}</p>
                    <p class="text-xs text-gray-500">{{ $lastSale->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
                
                <!-- Customer Info -->
                @if($lastSale->customer_name)
                    <div class="mb-4 text-sm">
                        <p><strong>Pelanggan:</strong> {{ $lastSale->customer_name }}</p>
                        @if($lastSale->customer_phone)
                            <p><strong>Telepon:</strong> {{ $lastSale->customer_phone }}</p>
                        @endif
                    </div>
                @endif
                
                <!-- Items -->
                <div class="border-t border-b border-gray-300 py-3 mb-4">
                    @foreach($lastSale->saleItems as $item)
                        <div class="flex justify-between text-sm mb-1">
                            <div class="flex-1">
                                <div>{{ $item->is_custom ? $item->custom_item_name : $item->product->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $item->qty }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-right">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Summary -->
                <div class="text-sm space-y-1 mb-4">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($lastSale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between font-semibold border-t pt-1">
                        <span>Total:</span>
                        <span>Rp {{ number_format($lastSale->final_total, 0, ',', '.') }}</span>
                    </div>
                    @php
                        $paid = ($lastSale->cash_amount ?? 0) + ($lastSale->qr_amount ?? 0) + ($lastSale->edc_amount ?? 0);
                    @endphp
                    <div class="flex justify-between">
                        <span>Bayar ({{ ucfirst($lastSale->payment_method) }}):</span>
                        <span>Rp {{ number_format($paid, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span>Kembalian:</span>
                        <span>Rp {{ number_format($lastSale->change_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                
                @if($lastSale->notes)
                    <div class="text-xs text-gray-600 mb-4">
                        <strong>Catatan:</strong> {{ $lastSale->notes }}
                    </div>
                @endif
                
                <div class="text-center text-xs text-gray-500 mb-6">
                    <p>Terima kasih atas kunjungan Anda!</p>
                    <p>Kasir: {{ $lastSale->cashier->name ?? 'System' }}</p>
                </div>
                
                <!-- Buttons -->
                <div class="flex space-x-3">
                    <button wire:click="closeReceipt" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Tutup
                    </button>
                    <button wire:click="printReceipt" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Print
                    </button>
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
    document.addEventListener('livewire:init', () => {
        // Track user interaction to prevent unwanted auto-focus
        let userInteractingWithForm = false;
        let pricingTierUpdating = false;
        
        // Listen for pricing tier update events
        Livewire.on('pricing-tier-updating', () => {
            pricingTierUpdating = true;
            userInteractingWithForm = true;
        });
        
        Livewire.on('pricing-tier-updated', () => {
            setTimeout(() => {
                pricingTierUpdating = false;
                userInteractingWithForm = false;
            }, 300); // Give some time for the DOM to update
        });
        
        // Add event listeners to track form interactions
        document.addEventListener('mousedown', function(e) {
            if (e.target.closest('.pricing-tier-selector') || 
                e.target.tagName === 'SELECT' || 
                e.target.tagName === 'INPUT' || 
                e.target.tagName === 'TEXTAREA') {
                userInteractingWithForm = true;
                setTimeout(() => {
                    if (!pricingTierUpdating) {
                        userInteractingWithForm = false;
                    }
                }, 1000); // Reset after 1 second
            }
        });
        
        document.addEventListener('focusin', function(e) {
            if (e.target.closest('.pricing-tier-selector') || 
                e.target.tagName === 'SELECT') {
                userInteractingWithForm = true;
                setTimeout(() => {
                    if (!pricingTierUpdating) {
                        userInteractingWithForm = false;
                    }
                }, 500);
            }
        });
        
        Livewire.on('print-receipt', () => {
            window.print();
        });
        
        // Auto-focus disabled - user can manually focus barcode input when needed
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Don't trigger shortcuts if user is typing in input fields, select, or textarea
            // Exception: Allow shortcuts when barcode input is focused
            if ((e.target.tagName === 'INPUT' && e.target.id !== 'barcode-input' && !e.ctrlKey && !e.altKey) ||
                (e.target.tagName === 'SELECT' && !e.ctrlKey && !e.altKey) ||
                (e.target.tagName === 'TEXTAREA' && !e.ctrlKey && !e.altKey)) {
                return;
            }
            
            // Don't trigger shortcuts if modal is open (except ESC)
            if (document.querySelector('.modal') && e.key !== 'Escape') {
                return;
            }
            
            // Ctrl+T - Create new tab
            if (e.ctrlKey && e.key === 't') {
                e.preventDefault();
                @this.call('createNewTab');
            }
            
            // Ctrl+W - Close current tab
            if (e.ctrlKey && e.key === 'w') {
                e.preventDefault();
                @this.call('closeTab', {{ $activeTabId }});
            }
            
            // Ctrl+Tab - Switch to next tab
            if (e.ctrlKey && e.key === 'Tab') {
                e.preventDefault();
                // Get current tab IDs dynamically
                const tabButtons = document.querySelectorAll('[wire\\:click*="switchToTab"]');
                if (tabButtons.length > 0) {
                    const tabIds = Array.from(tabButtons).map(btn => {
                        const match = btn.getAttribute('wire:click').match(/switchToTab\((\d+)\)/);
                        return match ? parseInt(match[1]) : null;
                    }).filter(id => id !== null);
                    
                    const currentTabId = {{ $activeTabId }};
                    const currentIndex = tabIds.indexOf(currentTabId);
                    const nextIndex = currentIndex >= 0 ? (currentIndex + 1) % tabIds.length : 0;
                    
                    if (tabIds[nextIndex]) {
                        @this.call('switchToTab', tabIds[nextIndex]);
                    }
                }
            }
            
            // F1 - Open Checkout (if cart not empty)
            if (e.key === 'F1') {
                e.preventDefault();
                @this.call('openCheckout');
            }
            
            // F2 - Clear Cart
            if (e.key === 'F2') {
                e.preventDefault();
                @this.call('confirmClearCart');
            }
            
            // F3 - Focus barcode input
            if (e.key === 'F3') {
                e.preventDefault();
                const barcodeInput = document.getElementById('barcode-input');
                if (barcodeInput) {
                    barcodeInput.focus();
                    barcodeInput.select();
                }
            }
            
            // F4 - Focus product search
            if (e.key === 'F4') {
                e.preventDefault();
                const searchInput = document.querySelector('input[wire\\:model\\.live="productSearch"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // ESC - Close modals or clear search
            if (e.key === 'Escape') {
                if (document.querySelector('.modal')) {
                    @this.call('closeCheckout');
                    @this.call('closeReceipt');
                } else {
                    // Clear search and focus barcode only if not interacting with form elements
                    const activeElement = document.activeElement;
                    const isFormInteraction = activeElement && (
                        activeElement.tagName === 'SELECT' ||
                        activeElement.closest('.pricing-tier-selector')
                    );
                    
                    if (!isFormInteraction) {
                        @this.set('productSearch', '');
                        setTimeout(() => {
                            const barcodeInput = document.getElementById('barcode-input');
                            if (barcodeInput) {
                                barcodeInput.focus();
                            }
                        }, 50);
                    }
                }
            }
            
            // Enter - Quick add first product from search results
            if (e.key === 'Enter' && e.target.id !== 'barcode-input') {
                const firstProduct = document.querySelector('.product-card');
                if (firstProduct) {
                    firstProduct.click();
                }
            }
        });
        
        // Barcode validation and formatting
        const barcodeInput = document.getElementById('barcode-input');
        if (barcodeInput) {
            barcodeInput.addEventListener('input', function(e) {
                // Remove non-alphanumeric characters
                let value = e.target.value.replace(/[^a-zA-Z0-9]/g, '');
                
                // Auto-submit when barcode length is typical (8, 12, 13 digits)
                if (value.length >= 8 && /^\d+$/.test(value)) {
                    // Typical barcode lengths: EAN-8 (8), UPC-A (12), EAN-13 (13)
                    if ([8, 12, 13].includes(value.length)) {
                        setTimeout(() => {
                            @this.call('addProductByBarcode');
                        }, 100);
                    }
                }
            });
            
            // Clear barcode input after successful scan
            barcodeInput.addEventListener('blur', function(e) {
                // If any modal is open, do not refocus barcode input
                if (document.querySelector('.modal')) {
                    return;
                }
                // Don't auto-focus if user is interacting with other form elements or pricing tier is updating
                const activeElement = document.activeElement;
                const isFormInteraction = activeElement && (
                    activeElement.tagName === 'SELECT' ||
                    activeElement.tagName === 'INPUT' ||
                    activeElement.tagName === 'TEXTAREA' ||
                    activeElement.closest('.pricing-tier-selector')
                );
                
                // Check if auto-focus is specifically prevented
                const preventFocus = this.hasAttribute('data-prevent-focus');
                
                if (this.value.trim() === '' && !isFormInteraction && !userInteractingWithForm && !pricingTierUpdating && !preventFocus) {
                    setTimeout(() => {
                        // Double check that user isn't interacting with form elements and pricing tier isn't updating
                        const currentActive = document.activeElement;
                        const stillPreventFocus = this.hasAttribute('data-prevent-focus');
                        if (!userInteractingWithForm && !pricingTierUpdating && !stillPreventFocus &&
                            (!currentActive || (!currentActive.closest('.pricing-tier-selector') && 
                            currentActive.tagName !== 'SELECT' && 
                            currentActive.tagName !== 'INPUT' && 
                            currentActive.tagName !== 'TEXTAREA'))) {
                            this.focus();
                        }
                    }, 150);
                }
            });
        }
    });
    
    // User interaction tracking removed - no longer needed without auto-focus
        
        // Flash message auto-hide
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('#success-alert, #error-alert');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    closeAlert(message.id);
                }, 5000); // Auto close after 5 seconds
            });
        });
        
        // Function to close alert manually
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }
    </script>

    <!-- Custom Item Modal -->
    @if($showCustomItemModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold mb-4">Tambah Item Custom</h3>
                <form wire:submit.prevent="addCustomItem">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
                            <input type="text" wire:model="customItemName" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('customItemName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (opsional)</label>
                            <textarea wire:model="customItemDescription" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            @error('customItemDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                                <input type="number" wire:model="customItemPrice" min="0.01" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('customItemPrice') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                                <input type="number" wire:model="customItemQuantity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('customItemQuantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-3 mt-6">
                        <button type="button" wire:click="hideCustomItemModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

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
