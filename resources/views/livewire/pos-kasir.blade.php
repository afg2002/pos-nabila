<div>
    <div class="pos-kasir-container min-h-screen bg-gray-50">
        {{-- Enhanced Mobile-First CSS --}}
        <style>
            /* Base Styles */
            .pos-kasir-container {
                background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
                min-height: 100vh;
                position: relative;
            }

            /* Product Cards - Mobile Optimized */
            .product-card {
                background: white;
                border-radius: 12px;
                padding: 16px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                transition: all 0.2s ease;
                cursor: pointer;
                position: relative;
                overflow: hidden;
                min-height: 160px;
                display: flex;
                flex-direction: column;
            }

            .product-card:active {
                transform: scale(0.98);
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            }

            .product-card:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }

            /* Stock Status Indicator */
            .stock-indicator {
                position: absolute;
                top: 8px;
                right: 8px;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .stock-high { background: #dcfce7; color: #166534; }
            .stock-medium { background: #fef3c7; color: #92400e; }
            .stock-low { background: #fee2e2; color: #991b1b; }

            /* Mobile Cart Bottom Sheet - Enhanced */
            .mobile-cart-bottom-sheet {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                border-radius: 24px 24px 0 0;
                box-shadow: 0 -4px 24px rgba(0,0,0,0.12);
                z-index: 50;
                transform: translateY(100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                max-height: 85vh;
                display: flex;
                flex-direction: column;
            }

            .mobile-cart-bottom-sheet.open {
                transform: translateY(0);
            }

            .mobile-cart-handle {
                width: 40px;
                height: 4px;
                background: #e5e7eb;
                border-radius: 2px;
                margin: 12px auto 8px;
                flex-shrink: 0;
            }

            /* Floating Action Button */
            .fab-cart {
                position: fixed;
                bottom: 80px;
                right: 20px;
                width: 56px;
                height: 56px;
                border-radius: 50%;
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
                z-index: 40;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                border: 3px solid white;
            }

            .fab-cart:active {
                transform: scale(0.95);
            }

            .fab-cart.shake {
                animation: shake 0.5s ease-in-out;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0) rotate(0deg); }
                25% { transform: translateX(-8px) rotate(-5deg); }
                75% { transform: translateX(8px) rotate(5deg); }
            }

            /* Tab Navigation */
            .tab-nav {
                display: flex;
                background: white;
                border-radius: 12px;
                padding: 4px;
                gap: 4px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }

            .tab-nav::-webkit-scrollbar {
                display: none;
            }

            .tab-button {
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
                white-space: nowrap;
                transition: all 0.2s ease;
                flex-shrink: 0;
                min-width: 80px;
                text-align: center;
            }

            .tab-button.active {
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                color: white;
                box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
            }

            /* Input Fields - Mobile Optimized */
            .input-mobile {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                font-size: 16px;
                transition: all 0.2s ease;
                background: white;
            }

            .input-mobile:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            /* Button Styles */
            .btn-primary {
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                color: white;
                padding: 12px 24px;
                border-radius: 12px;
                font-weight: 600;
                font-size: 16px;
                border: none;
                transition: all 0.2s ease;
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            }

            .btn-primary:active {
                transform: translateY(1px);
                box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
            }

            .btn-primary:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                box-shadow: none;
            }

            .btn-secondary {
                background: white;
                color: #6b7280;
                padding: 12px 24px;
                border-radius: 12px;
                font-weight: 600;
                font-size: 16px;
                border: 2px solid #e5e7eb;
                transition: all 0.2s ease;
            }

            .btn-secondary:active {
                background: #f9fafb;
            }

            /* Bottom Navigation Bar */
            .bottom-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: white;
                border-top: 1px solid #e5e7eb;
                z-index: 45;
                padding: 8px 0 max(8px, env(safe-area-inset-bottom));
            }

            /* Cart Item */
            .cart-item {
                background: white;
                border-radius: 12px;
                padding: 16px;
                margin-bottom: 12px;
                border: 1px solid #e5e7eb;
            }

            /* Quantity Controls */
            .qty-control {
                display: flex;
                align-items: center;
                gap: 8px;
                background: #f3f4f6;
                border-radius: 8px;
                padding: 4px;
            }

            .qty-btn {
                width: 32px;
                height: 32px;
                border-radius: 6px;
                border: none;
                background: white;
                color: #374151;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: 600;
                transition: all 0.2s ease;
                cursor: pointer;
            }

            .qty-btn:active {
                background: #e5e7eb;
                transform: scale(0.95);
            }

            .qty-input {
                width: 50px;
                text-align: center;
                border: none;
                background: transparent;
                font-weight: 600;
                font-size: 16px;
            }

            /* Scroll Areas */
            .scroll-area {
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                scrollbar-color: #e5e7eb transparent;
            }

            .scroll-area::-webkit-scrollbar {
                width: 4px;
            }

            .scroll-area::-webkit-scrollbar-track {
                background: transparent;
            }

            .scroll-area::-webkit-scrollbar-thumb {
                background: #e5e7eb;
                border-radius: 2px;
            }

            /* Responsive */
            @media (max-width: 767px) {
                .mobile-hidden { display: none !important; }
                .mobile-only { display: block !important; }
                .product-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
                .main-content { padding-bottom: 180px; }
                .scroll-area-products {
                    max-height: calc(100vh - 280px);
                    padding-bottom: 80px;
                }
                .scroll-area-cart { max-height: calc(100vh - 200px); }
                .product-image { display: none !important; }
            }

            @media (min-width: 768px) {
                .mobile-only { display: none !important; }
                .mobile-hidden { display: block !important; }
                .product-grid { grid-template-columns: repeat(3, 1fr); gap: 20px; }
                .main-content { padding-bottom: 0; }
                .scroll-area-products { max-height: calc(100vh - 300px); }
                .scroll-area-cart { max-height: calc(100vh - 400px); }
                .product-image { display: block; }
            }

            /* Loading Skeleton */
            .skeleton {
                background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
                background-size: 200% 100%;
                animation: loading 1.5s infinite;
            }

            @keyframes loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }

            /* Badge */
            .badge {
                position: absolute;
                top: -4px;
                right: -4px;
                min-width: 20px;
                height: 20px;
                background: #ef4444;
                color: white;
                border-radius: 10px;
                font-size: 12px;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid white;
            }
        </style>

        <!-- Main Container -->
        <div class="main-content">
            <!-- Header Section -->
            <div class="bg-white shadow-sm sticky top-0 z-30">
                <div class="p-4">
                    <!-- Top Bar -->
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-store text-blue-600"></i>
                            Kasir
                        </h1>
                        <a href="{{ route('kasir.management') }}"
                           class="text-gray-600 hover:text-gray-900 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-history text-lg"></i>
                        </a>
                    </div>

                    <!-- Warehouse Info Card -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-3 mb-4 border border-blue-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-warehouse text-white"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-gray-900">{{ $activeWarehouseName ?? 'Toko Utama' }}</span>
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">POS</span>
                                </div>
                                <div class="text-xs text-gray-600">
                                    Stok: {{ $activeWarehouseTypeLabel ?? 'Toko' }} @if(!empty($activeWarehouseCode))• {{ $activeWarehouseCode }} @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Barcode Scanner -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text"
                                   id="barcode-input"
                                   wire:model.live="barcode"
                                   placeholder="Scan barcode..."
                                   class="input-mobile pr-12">
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                                <i class="fas fa-barcode text-gray-400 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Product Search with Autocomplete -->
                    <div class="mb-4">
                        <div class="relative">
                            <input type="text"
                                   id="product-search-input"
                                   wire:model.live="productSearch"
                                   wire:keydown.enter="selectFirstSearchResult"
                                   placeholder="Cari produk (Enter untuk pilih pertama)..."
                                   class="input-mobile pr-12">
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                                <i class="fas fa-search text-gray-400 text-xl"></i>
                            </div>
                        </div>

                        <!-- Search Results Dropdown -->
                        @if(!empty($productSearch) && $products->count() > 0)
                            <div class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                @foreach($products->take(5) as $product)
                                    <div class="search-result-item px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                         wire:click="addToCart({{ $product->id }})"
                                         wire:keydown.enter="addToCart({{ $product->id }})">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    @if($product->sku) SKU: {{ $product->sku }} @endif
                                                    <span class="ml-2">Rp {{ number_format($product->price_retail ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                            <div class="text-sm text-blue-600 font-medium">
                                                <i class="fas fa-plus-circle"></i> Tambah
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @if($products->count() > 5)
                                    <div class="px-4 py-2 text-center text-sm text-gray-500 bg-gray-50">
                                        Dan {{ $products->count() - 5 }} produk lagi...
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(!empty($productSearch))
                            <button type="button"
                                    wire:click="resetProductSearch"
                                    class="mt-2 text-sm text-gray-600 hover:text-gray-900">
                                <i class="fas fa-times mr-1"></i> Reset pencarian
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="p-4">
                @if($products->isEmpty())
                    <div class="text-center py-20">
                        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 font-medium">Produk tidak ditemukan</p>
                        <p class="text-gray-400 text-sm mt-1">Coba kata kunci lain atau scan barcode</p>
                    </div>
                @else
                    <div class="product-grid scroll-area scroll-area-products">
                        @foreach($products as $product)
                            @php
                                $warehouseStock = $warehouseId ?
                                    \App\ProductWarehouseStock::where('product_id', $product->id)
                                        ->where('warehouse_id', $warehouseId)
                                        ->value('stock_on_hand') ?? 0 : 0;
                                $stockClass = $warehouseStock > 10 ? 'stock-high' : ($warehouseStock > 5 ? 'stock-medium' : 'stock-low');
                                $stockText = $warehouseStock > 10 ? 'Tersedia' : ($warehouseStock > 5 ? 'Terbatas' : 'Hampir Habis');
                            @endphp

                            <div class="product-card"
                                 wire:click="addToCart({{ $product->id }})"
                                 role="button"
                                 tabindex="0">

                                <!-- Stock Indicator -->
                                <div class="stock-indicator {{ $stockClass }}">
                                    {{ $stockText }}
                                </div>

                                <!-- Product Image (Hidden on Mobile) -->
                                <div class="product-image w-full h-24 bg-gray-100 rounded-lg mb-3 overflow-hidden">
                                    <img src="{{ $product ? $product->getPhotoUrl() : asset('storage/placeholders/no-image.svg') }}"
                                         alt="{{ $product ? $product->name : 'No Product' }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                                </div>

                                <!-- Product Info -->
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 text-sm mb-1 line-clamp-2">
                                        {{ $product ? $product->name : 'No Product' }}
                                    </h3>

                                    <div class="flex items-baseline justify-between mb-2">
                                        <span class="text-lg font-bold text-blue-600">
                                            Rp {{ number_format($product->price_retail ?? 0, 0, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $product->unit ? $product->unit->abbreviation : 'pcs' }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-xs text-gray-600">
                                        <span>
                                            <i class="fas fa-box mr-1"></i>
                                            {{ number_format($warehouseStock) }}
                                        </span>
                                        @if($product->sku)
                                            <span class="text-gray-400">
                                                {{ $product->sku }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Floating Action Button for Cart -->
        <button class="fab-cart mobile-only"
                wire:click="toggleMobileCart"
                title="Buka keranjang">
            <i class="fas fa-shopping-cart text-white text-xl"></i>
            @php
                $fabCartItems = [];
                if (isset($activeTabId) && isset($carts[$activeTabId]) && isset($carts[$activeTabId]['cart'])) {
                    $fabCartItems = $carts[$activeTabId]['cart'];
                }
            @endphp
            @if(!empty($fabCartItems))
                <span class="badge">{{ count($fabCartItems) }}</span>
            @endif
        </button>

        <!-- Mobile Cart Bottom Sheet -->
        <div class="mobile-cart-bottom-sheet {{ $mobileCartOpen ? 'open' : '' }}">
            <div class="mobile-cart-handle"></div>

            <!-- Cart Header -->
            <div class="p-4 border-b border-gray-200">
                <!-- Tab Navigation -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex-1 mr-2">
                        <div class="tab-nav">
                            @foreach($carts as $tabId => $tab)
                                <button wire:click="switchToTab({{ $tabId }})"
                                        class="tab-button {{ $activeTabId === $tabId ? 'active' : '' }}">
                                    {{ $tab['name'] ?? 'Tab ' . $tabId }}
                                    @if(isset($tab['cart']) && count($tab['cart']) > 0)
                                        <span class="ml-1 text-xs bg-white bg-opacity-20 px-1.5 py-0.5 rounded-full">
                                            {{ count($tab['cart']) }}
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="createNewTab"
                                class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-plus"></i>
                        </button>
                        @if(count($carts) > 1)
                            <button wire:click="closeTab({{ $activeTabId }})"
                                    class="p-2 text-gray-600 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Cart Name -->
                <input type="text"
                       wire:model.blur="carts.{{ $activeTabId }}.name"
                       class="input-mobile text-sm"
                       placeholder="Nama keranjang...">

                <!-- Quick Actions -->
                <div class="flex gap-2 mt-3 overflow-x-auto">
                    <!-- Quick Actions -->
                    <div class="flex gap-2 mb-3 overflow-x-auto">
                        <button wire:click="confirmClearCart"
                                class="btn-secondary text-sm px-3 py-2 whitespace-nowrap flex-shrink-0"
                                title="Kosongkan keranjang">
                            <i class="fas fa-trash mr-1"></i> Kosongkan
                        </button>
                        <button wire:click="openCheckout"
                                class="btn-primary text-sm px-3 py-2 whitespace-nowrap flex-shrink-0"
                                title="Checkout">
                            <i class="fas fa-credit-card mr-1"></i> Bayar
                        </button>
                    </div>


            </div>

            <!-- Cart Items -->
            <div class="flex-1 p-4 scroll-area scroll-area-cart">
                @php
                    $sheetCartItems = [];
                    if (isset($activeTabId) && isset($carts[$activeTabId]) && isset($carts[$activeTabId]['cart'])) {
                        $sheetCartItems = $carts[$activeTabId]['cart'];
                    }
                @endphp
                @if(empty($sheetCartItems))
                    <div class="text-center py-12">
                        <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 font-medium">Keranjang kosong</p>
                        <p class="text-gray-400 text-sm mt-1">Tambahkan produk untuk memulai</p>
                    <div class="text-sm text-gray-500 mt-1">
                        Tambahkan produk untuk memulai
                    </div>
                </div>
            @else
                <!-- Custom Item Form (Mobile) -->
                <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <h5 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                        Tambah Item/Jasa Custom
                    </h5>
                    <div class="space-y-2">
                        <input type="text"
                               wire:model.defer="customItemName"
                               placeholder="Nama item/jasa"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="number"
                               wire:model.defer="customItemPrice"
                               placeholder="Harga"
                               min="0"
                               step="1"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="number"
                               wire:model.defer="customItemQuantity"
                               placeholder="Quantity"
                               min="1"
                               step="1"
                               value="1"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button wire:click="addCustomItem"
                                class="w-full btn-primary text-sm py-2">
                            <i class="fas fa-plus mr-1"></i> Tambah Item
                        </button>
                    </div>
                </div>

                <!-- Bulk Price Type Controls (Mobile) -->
                <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <label class="block text-xs font-medium text-blue-900 mb-2">
                        <i class="fas fa-tags mr-1"></i> Ubah Semua Harga Ke:
                    </label>
                    <select wire:change="bulkSetCartPriceType($event.target.value)"
                            class="w-full px-3 py-2 text-sm border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">-- Pilih Jenis Harga --</option>
                        @foreach(\App\Product::getPriceTypes() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3">
                    @foreach($sheetCartItems as $key => $item)
                        @php
                            $product = $cartProducts->get($item['product_id']);
                            $photoUrl = $product && method_exists($product, 'getPhotoUrl') ? $product->getPhotoUrl() : asset('storage/placeholders/no-image.svg');
                        @endphp

                        <div class="cart-item relative">
                            <div class="flex gap-3">
                                <!-- Product Image -->
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    <img src="{{ $photoUrl }}"
                                         alt="{{ $item['name'] }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.src='{{ asset('storage/placeholders/no-image.svg') }}'">
                                </div>

                                <!-- Product Details -->
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-2 pr-8">{{ $item['name'] }}</h4>

                                    <!-- Price Type Selector (Mobile) -->
                                    <div class="mb-2">
                                        <select wire:change="updateItemPriceType('{{ $key }}', $event.target.value)"
                                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            @foreach(\App\Product::getPriceTypes() as $value => $label)
                                                <option value="{{ $value }}" {{ $item['pricing_tier'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Mobile Item Details -->
                                    <div class="space-y-2">
                                        <!-- Quantity Controls -->
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs text-gray-600 font-medium w-12">Qty:</label>
                                            <div class="flex-1 qty-control">
                                                <button type="button"
                                                        wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                                        class="qty-btn">−</button>
                                                <input type="number"
                                                       wire:model.live="carts.{{ $activeTabId }}.cart.{{ $key }}.quantity"
                                                       class="qty-input"
                                                       min="1"
                                                       max="{{ $item['available_stock'] }}">
                                                <button type="button"
                                                        wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                                        class="qty-btn">+</button>
                                            </div>
                                            <span class="text-blue-600 font-bold text-sm">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                        </div>

                                        <!-- Unit Selector (Mobile) -->
                                        @php
                                            $product = $cartProducts->get($item['product_id']);
                                        @endphp
                                        @if($product)
                                            <div class="flex items-center gap-2">
                                                <label class="text-xs text-gray-600 font-medium w-12">Unit:</label>
                                                <select wire:change="updateCartItemUnit('{{ $key }}', $event.target.value)"
                                                        class="flex-1 px-2 py-1 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    @if($product && $product->unit)
                                                        <option value="{{ $product->unit_id }}" {{ ($item['selected_unit_id'] ?? $product->unit_id) == $product->unit_id ? 'selected' : '' }}>
                                                            {{ $product->unit->name ?? $product->unit->abbreviation ?? 'Base' }}
                                                        </option>
                                                    @endif
                                                    @if($product)
                                                        @foreach($product->unitScales as $scale)
                                                            <option value="{{ $scale->unit_id }}" {{ ($item['selected_unit_id'] ?? $product->unit_id) == $scale->unit_id ? 'selected' : '' }}>
                                                                {{ $scale->unit ? ($scale->unit->name ?? $scale->unit->abbreviation) : 'Unit' }} (×{{ $scale->to_base_qty }})
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        @endif

                                        <!-- Custom Price Input (Mobile) -->
                                        @if($item['pricing_tier'] === 'custom')
                                            <div class="flex items-center gap-2">
                                                <label class="text-xs text-gray-600 font-medium w-12">Harga:</label>
                                                <input type="number"
                                                       wire:change="updatePrice('{{ $key }}', $event.target.value)"
                                                       value="{{ $item['price'] }}"
                                                       min="{{ $item['base_cost'] * 1.1 }}"
                                                       inputmode="decimal"
                                                       class="flex-1 px-2 py-1 text-sm border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-blue-50">
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Subtotal and Stock -->
                                    <div class="flex items-center justify-between text-sm mt-2 pt-2 border-t border-gray-100">
                                        <div class="text-xs text-gray-500">
                                            <div>Stok: {{ $item['available_stock'] }}</div>
                                            @php
                                                $product = \App\Product::find($item['product_id']);
                                                $costPrice = $product ? $product->getEffectiveCostPrice() : 0;
                                                $margin = $costPrice > 0 ? (($item['price'] - $costPrice) / $item['price']) * 100 : 0;
                                            @endphp
                                            @if($margin > 0)
                                                <div class="text-green-600 font-medium">{{ number_format($margin, 1) }}% margin</div>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-blue-600">
                                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ number_format($item['quantity']) }} × Rp {{ number_format($item['price'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Remove Button -->
                            <button type="button"
                                    wire:click="removeFromCart('{{ $key }}')"
                                    class="absolute top-2 right-2 w-8 h-8 bg-red-50 text-red-600 rounded-full flex items-center justify-center hover:bg-red-100 transition-colors z-10">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
            </div>

            <!-- Cart Summary -->
            @php
                $summaryCartItems = [];
                if (isset($activeTabId) && isset($carts[$activeTabId]) && isset($carts[$activeTabId]['cart'])) {
                    $summaryCartItems = $carts[$activeTabId]['cart'];
                }
            @endphp
            @if(!empty($summaryCartItems))
                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                            <span>Total:</span>
                            <span class="text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <button wire:click="openCheckout"
                            class="btn-primary w-full text-center">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Checkout ({{ count($summaryCartItems) }} item)
                    </button>
                </div>
            @endif
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav mobile-only">
            <div class="flex items-center justify-around px-4">
                <button type="button"
                        onclick="document.getElementById('barcode-input').focus()"
                        class="flex flex-col items-center py-2 px-3 text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-barcode text-xl mb-1"></i>
                    <span class="text-xs">Scan</span>
                </button>
                <button type="button"
                        onclick="document.getElementById('product-search-input').focus()"
                        class="flex flex-col items-center py-2 px-3 text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-search text-xl mb-1"></i>
                    <span class="text-xs">Cari</span>
                </button>
                <button type="button"
                        wire:click="toggleMobileCart"
                        class="flex flex-col items-center py-2 px-3 text-gray-600 hover:text-blue-600 transition-colors relative">
                    <i class="fas fa-shopping-cart text-xl mb-1"></i>
                    <span class="text-xs">Keranjang</span>
                    @php
                        $navCartItems = [];
                        if (isset($activeTabId) && isset($carts[$activeTabId]) && isset($carts[$activeTabId]['cart'])) {
                            $navCartItems = $carts[$activeTabId]['cart'];
                        }
                    @endphp
                    @if(!empty($navCartItems))
                        <span class="absolute -top-1 right-2 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                            {{ count($navCartItems) }}
                        </span>
                    @endif
                </button>
                <button type="button"
                        wire:click="goToSalesHistory"
                        class="flex flex-col items-center py-2 px-3 text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-history text-xl mb-1"></i>
                    <span class="text-xs">Riwayat</span>
                </button>
                <button type="button"
                        wire:click="openCheckout"
                        class="flex flex-col items-center py-2 px-3 text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-credit-card text-xl mb-1"></i>
                    <span class="text-xs">Bayar</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    @if($showCheckoutModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-end z-50">
            <div class="bg-white rounded-t-2xl w-full max-h-[85vh] overflow-hidden animate-slide-up">
                <!-- Modal Header -->
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Checkout</h3>
                    <button wire:click="closeCheckout" class="p-2 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-times text-gray-600"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-4 scroll-area" style="max-height: calc(85vh - 120px);">
                    <form wire:submit.prevent="processCheckout" class="space-y-4">
                        <!-- Customer Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pelanggan</label>
                            <input type="text"
                                   wire:model="customerName"
                                   placeholder="Nama pelanggan"
                                   class="input-mobile">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                            <input type="text"
                                   wire:model="customerPhone"
                                   placeholder="No. telepon"
                                   class="input-mobile">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                            <select wire:model="paymentMethod" class="input-mobile">
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="edc">EDC/Kartu</option>
                                <option value="qr">QR Code</option>
                            </select>
                        </div>

                        <!-- Payment Rounding for Cash -->
                        @if($paymentMethod === 'cash')
                            <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-gray-700">Pembulatan Tunai</label>
                                    <input type="checkbox"
                                           id="roundingEnabled"
                                           wire:model="roundingEnabled"
                                           class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500">
                                </div>

                                @if($roundingEnabled)
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Langkah</label>
                                            <select wire:model="roundingStep" class="input-mobile text-sm">
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="500">500</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Mode</label>
                                            <select wire:model="roundingMode" class="input-mobile text-sm">
                                                <option value="nearest">Terdekat</option>
                                                <option value="up">Ke atas</option>
                                                <option value="down">Ke bawah</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="text-sm space-y-1">
                                        <div class="flex justify-between">
                                            <span>Sebelum pembulatan:</span>
                                            <span>Rp {{ number_format($roundedTotal - $roundingAdjustment, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between font-semibold text-lg">
                                            <span>Total akhir:</span>
                                            <span class="text-blue-600">Rp {{ number_format($roundedTotal, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Submit Button -->
                        @php
                            $checkoutDisabled = empty($carts[$activeTabId]['cart'] ?? [])
                                || empty($paymentMethod)
                                || empty($warehouseId)
                                || (($amountPaid ?? 0) < 0);
                        @endphp
                        <button type="submit"
                                class="btn-primary w-full py-4 text-lg"
                                wire:loading.attr="disabled" wire:target="processCheckout"
                                @disabled($checkoutDisabled)
                                aria-disabled="{{ $checkoutDisabled ? 'true' : 'false' }}">
                            <i class="fas fa-check mr-2"></i>
                            <span wire:loading.remove wire:target="processCheckout">Proses Pembayaran</span>
                            <span wire:loading wire:target="processCheckout"><i class="fas fa-spinner fa-spin mr-2"></i>Memproses...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Enhanced mobile interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Focus management
            const barcodeInput = document.getElementById('barcode-input');
            const searchInput = document.getElementById('product-search-input');

            // Auto focus barcode input on load
            if (barcodeInput) {
                setTimeout(() => barcodeInput.focus(), 500);
            }

            // Touch feedback for product cards
            document.querySelectorAll('.product-card').forEach(card => {
                card.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });

                card.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Swipe gestures for mobile cart
            let startY = 0;
            let cartSheet = document.querySelector('.mobile-cart-bottom-sheet');

            if (cartSheet) {
                cartSheet.addEventListener('touchstart', function(e) {
                    startY = e.touches[0].clientY;
                });

                cartSheet.addEventListener('touchmove', function(e) {
                    let currentY = e.touches[0].clientY;
                    let deltaY = currentY - startY;

                    // Only allow swipe down gesture
                    if (deltaY > 0 && deltaY < 200) {
                        this.style.transform = `translateY(${deltaY}px)`;
                    }
                });

                cartSheet.addEventListener('touchend', function(e) {
                    let currentY = e.changedTouches[0].clientY;
                    let deltaY = currentY - startY;

                    if (deltaY > 100) {
                        // Close cart if swiped down enough
                        @this.call('toggleMobileCart');
                    }
                    this.style.transform = '';
                });
            }

            // Keyboard shortcuts for mobile
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // Close mobile cart or modal
                    @this.call('toggleMobileCart');
                    @this.call('closeCheckout');
                }

                // F3 for barcode focus
                if (e.key === 'F3') {
                    e.preventDefault();
                    if (barcodeInput) {
                        barcodeInput.focus();
                        barcodeInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                // F4 for search focus
                if (e.key === 'F4') {
                    e.preventDefault();
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            // Smooth scroll behavior
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Prevent double-tap zoom on inputs
            document.querySelectorAll('input, select, textarea').forEach(element => {
                let lastTouchEnd = 0;
                element.addEventListener('touchend', function (event) {
                    const now = (new Date()).getTime();
                    if (now - lastTouchEnd <= 300) {
                        event.preventDefault();
                    }
                    lastTouchEnd = now;
                }, false);
            });

            // Haptic feedback simulation (visual feedback)
            document.querySelectorAll('button, .product-card, .qty-btn').forEach(element => {
                element.addEventListener('click', function() {
                    this.classList.add('clicked');
                    setTimeout(() => this.classList.remove('clicked'), 200);
                });
            });

            // Auto-hide/show bottom nav on scroll
            let lastScrollY = 0;
            let bottomNav = document.querySelector('.bottom-nav');

            if (bottomNav) {
                window.addEventListener('scroll', function() {
                    let currentScrollY = window.scrollY;

                    if (currentScrollY > lastScrollY && currentScrollY > 100) {
                        // Scrolling down - hide nav
                        bottomNav.style.transform = 'translateY(100%)';
                    } else {
                        // Scrolling up - show nav
                        bottomNav.style.transform = 'translateY(0)';
                    }

                    lastScrollY = currentScrollY;
                });
            }

            // Quantity input improvements
            document.querySelectorAll('.qty-input').forEach(input => {
                input.addEventListener('focus', function() {
                    this.select();
                });

                input.addEventListener('wheel', function(e) {
                    e.preventDefault();
                    let delta = e.deltaY > 0 ? -1 : 1;
                    let newValue = parseInt(this.value) + delta;
                    let min = parseInt(this.min) || 1;
                    let max = parseInt(this.max) || 999999;

                    if (newValue >= min && newValue <= max) {
                        this.value = newValue;
                        // Trigger Livewire update
                        this.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
            });

            // Barcode scanner sound feedback (optional)
            if (barcodeInput) {
                barcodeInput.addEventListener('input', function(e) {
                    if (this.value.length >= 8) { // Typical barcode length
                        // Play subtle sound feedback if supported
                        try {
                            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE');
                            audio.volume = 0.1;
                            audio.play().catch(() => {}); // Ignore errors
                        } catch (e) {}
                    }
                });

                // Auto add to cart on Enter key
                barcodeInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        // Trigger Livewire method to search and add first result
                        @this.call('searchAndAddFirstBarcode');
                    }
                });
            }

            // Enhanced search input interactions
            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        // Select first search result and add to cart
                        const firstResult = document.querySelector('.search-result-item');
                        if (firstResult) {
                            firstResult.click();
                        }
                    }
                });

                // Auto-focus and select text on focus
                searchInput.addEventListener('focus', function() {
                    this.select();
                });
            }
        });
    </script>

    <style>
        /* Additional styles for mobile interactions */
        .clicked {
            transform: scale(0.95) !important;
            opacity: 0.8 !important;
        }

        /* Haptic feedback animation */
        @keyframes tap {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        /* Smooth transitions for bottom nav */
        .bottom-nav {
            transition: transform 0.3s ease-out;
        }

        /* Mobile cart sheet transition */
        .mobile-cart-bottom-sheet {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Improved mobile modal */
        @media (max-width: 767px) {
            .modal-enhanced {
                align-items: flex-end;
                padding: 0;
            }

            .modal-content-enhanced {
                border-radius: 24px 24px 0 0;
                max-height: 90vh;
                width: 100%;
                animation: slideUp 0.3s ease-out;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }

        /* Quantity input styling */
        .qty-input::-webkit-inner-spin-button,
        .qty-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Focus states */
        .qty-btn:focus,
        button:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Loading states */
        .loading {
            pointer-events: none;
            opacity: 0.6;
        }

        /* Better mobile checkout modal */
        @media (max-width: 767px) {
            .checkout-modal {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                max-height: 85vh;
                border-radius: 24px 24px 0 0;
            }
        }

        /* Enhanced mobile cart styling */
        .cart-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            position: relative;
            transition: all 0.2s ease;
        }

        .cart-item:hover {
            border-color: #d1d5db;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .search-result-item {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .search-result-item:hover {
            background-color: #f9fafb;
            border-left: 3px solid #3b82f6;
            padding-left: 13px;
        }

        /* Mobile form improvements */
        @media (max-width: 767px) {
            .input-mobile {
                font-size: 16px; /* Prevent zoom on iOS */
                -webkit-appearance: none;
                -webkit-border-radius: 0;
            }

            select.input-mobile {
                background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 12px center;
                background-size: 16px;
                padding-right: 40px;
            }

            .qty-control {
                background: #f3f4f6;
                border-radius: 8px;
                padding: 4px;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .qty-btn {
                width: 32px;
                height: 32px;
                border-radius: 6px;
                border: none;
                background: white;
                color: #374151;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: 600;
                transition: all 0.2s ease;
                cursor: pointer;
            }

            .qty-btn:active {
                background: #e5e7eb;
                transform: scale(0.95);
            }

            .qty-input {
                width: 50px;
                text-align: center;
                border: none;
                background: transparent;
                font-weight: 600;
                font-size: 16px;
            }

            /* Custom scrollbars for mobile */
            .scroll-area::-webkit-scrollbar {
                width: 4px;
            }

            .scroll-area::-webkit-scrollbar-track {
                background: transparent;
            }

            .scroll-area::-webkit-scrollbar-thumb {
                background: #e5e7eb;
                border-radius: 2px;
            }
        }

        /* Price type selector styling */
        .price-type-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .price-retail { background: #dbeafe; color: #1e40af; }
        .price-semi-grosir { background: #fef3c7; color: #92400e; }
        .price-grosir { background: #dcfce7; color: #166534; }
        .price-custom { background: #fce7f3; color: #9f1239; }
    </style>
</div>
