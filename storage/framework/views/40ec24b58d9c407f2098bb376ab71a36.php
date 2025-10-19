<div>
    <div class="pos-kasir-container h-full bg-gray-50">
    <!-- Flash Messages -->
    <!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
        <div id="success-alert" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center justify-between transition-all duration-300 ease-in-out">
            <span><?php echo e(session('success')); ?></span>
            <button onclick="closeAlert('success-alert')" class="ml-4 text-white hover:text-gray-200 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php if(session()->has('error')): ?>
        <div id="error-alert" class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center justify-between transition-all duration-300 ease-in-out">
            <span><?php echo e(session('error')); ?></span>
            <button onclick="closeAlert('error-alert')" class="ml-4 text-white hover:text-gray-200 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <style>
        /* Scroll containers for POS */
        @media (max-width: 767px) {
            .pos-products-scroll {
                max-height: calc(100vh - 320px);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
            .pos-cart-scroll {
                max-height: calc(100vh - 280px);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }
        @media (min-width: 768px) {
            .pos-products-scroll {
                max-height: calc(100vh - 260px);
                overflow-y: auto;
            }
            .pos-cart-scroll {
                max-height: calc(100vh - 300px);
                overflow-y: auto;
            }
            .desktop-sticky-summary {
                position: sticky;
                top: 0;
                z-index: 30;
            }
        }
    </style>
    <div class="flex flex-col md:flex-row h-full md:min-h-0">
        <!-- Left Panel - Product Selection -->
        <div class="<?php echo e($transactionFullWidth ? 'w-full md:hidden' : 'w-full md:w-1/2'); ?> bg-white md:border-r border-gray-200 flex flex-col min-h-0">
            <!-- Header -->
            <div class="p-4 lg:p-6 border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Pilih Produk</h2>
                    <a href="<?php echo e(route('kasir.management')); ?>" 
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center"
                       title="Lihat Riwayat Transaksi">
                        <i class="fas fa-history mr-2"></i>
                        Riwayat
                    </a>
                </div>
                
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
                <div class="mb-6">
                    <div class="p-4 lg:p-5 rounded-xl border border-gray-200 bg-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-start space-x-3">
                                <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-gray-900">Gudang Aktif</h3>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700" title="POS terkunci ke stok Toko">
                                            <i class="fas fa-lock"></i>
                                            <span>Toko</span>
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-900 font-medium"><?php echo e($activeWarehouseName ?? 'Toko Utama'); ?></p>
                                    <div class="mt-1 text-xs text-gray-600">
                                        <span><?php echo e($activeWarehouseTypeLabel ?? 'Toko'); ?></span>
                                        <!--[if BLOCK]><![endif]--><?php if(!empty($activeWarehouseCode)): ?>
                                            <span class="mx-1">•</span>
                                            <span>Kode: <?php echo e($activeWarehouseCode); ?></span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                    <!--[if BLOCK]><![endif]--><?php if(!empty($activeWarehouseAddress)): ?>
                                        <div class="mt-1 text-xs text-gray-600"><?php echo e($activeWarehouseAddress); ?></div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1.5 text-xs rounded-lg bg-gray-50 border border-gray-200 text-gray-700 inline-flex items-center gap-1">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Stok POS: Toko saja</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Search -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-search mr-2"></i>Cari Produk
                    </label>
                    <div class="flex gap-2">
                        <input type="text" 
                               id="product-search-input"
                               wire:model.live="productSearch" 
                               placeholder="Cari nama produk atau SKU..."
                               class="flex-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <!--[if BLOCK]><![endif]--><?php if(!empty($productSearch)): ?>
                            <button type="button"
                                    wire:click="resetProductSearch"
                                    class="px-3 py-2 text-sm text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
                                Reset
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Ketik minimal 1 huruf untuk mulai mencari.</p>
                </div>
                

            </div>

            <!-- Product Grid -->
            <div class="flex-1 p-4 lg:p-6 pb-24 md:pb-6 pos-products-scroll overflow-y-auto">
                <!--[if BLOCK]><![endif]--><?php if($products->isEmpty()): ?>
                    <div class="text-center text-gray-500 py-10">Tidak ada produk ditemukan. Gunakan pencarian untuk menemukan produk.</div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="product-card relative bg-white rounded-xl p-4 border border-gray-200 hover:border-gray-300 hover:shadow-sm cursor-pointer transition-all"
                             wire:click="addToCart(<?php echo e($product->id); ?>)">
                            <!-- Product Image -->
                            <div class="w-full h-28 mb-3 bg-gray-100 rounded-lg overflow-hidden">
                                <img src="<?php echo e($product ? $product->getPhotoUrl() : asset('storage/placeholders/no-image.svg')); ?>" 
                                     alt="<?php echo e($product ? $product->name : 'No Product'); ?>"
                                     class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                     onclick="openImageModal('<?php echo e($product ? $product->getPhotoUrl() : asset('storage/placeholders/no-image.svg')); ?>', '<?php echo e($product ? addslashes($product->name) : 'No Product'); ?>')"
                                     onerror="this.src='<?php echo e(asset('storage/placeholders/no-image.svg')); ?>'">
                            </div>
                            
                            <div class="text-sm font-medium text-gray-900 truncate mb-1"><?php echo e($product ? $product->name : 'No Product'); ?></div>
                            <div class="mt-2">
                                <div class="font-semibold text-gray-900 text-base">Rp <?php echo e(number_format($product->price_retail ?? 0, 0, ',', '.')); ?></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                <?php
                                    $warehouseStock = $warehouseId ? 
                                        \App\ProductWarehouseStock::where('product_id', $product->id)
                                            ->where('warehouse_id', $warehouseId)
                                            ->value('stock_on_hand') ?? 0 : 0;
                                ?>
                                <div>Stok: <?php echo e(number_format($warehouseStock)); ?> <?php echo e($product->unit ? $product->unit->abbreviation : 'pcs'); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>

        <!-- Right Panel - Cart & Checkout -->
        <div class="<?php echo e($transactionFullWidth ? 'w-full' : 'w-full lg:w-2/5 xl:w-1/3'); ?> bg-white flex flex-col min-h-0">
            <!-- Responsive Multi-Tab Navigation -->
            <div class="border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between p-3 lg:p-4">
                    <!-- Tab Buttons - Responsive Scrolling -->
                    <div class="flex-1 overflow-x-auto">
                        <div class="flex space-x-2 min-w-max pb-1">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $carts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabId => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button wire:click="switchToTab(<?php echo e($tabId); ?>)"
                                        class="px-3 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap flex items-center <?php echo e($activeTabId === $tabId ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'); ?>">
                                    <?php echo e($tab['name'] ?? 'Tab ' . $tabId); ?>

                                    <!--[if BLOCK]><![endif]--><?php if(isset($tab['cart']) && count($tab['cart']) > 0): ?>
                                        <span class="ml-2 px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full">
                                            <?php echo e(count($tab['cart'])); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
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
                        <!--[if BLOCK]><![endif]--><?php if(count($carts) > 1): ?>
                            <button wire:click="closeTab(<?php echo e($activeTabId); ?>)" 
                                    class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Tutup Tab (Ctrl+W)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!-- Toggle Full Width Right Panel -->
                        <button wire:click="toggleTransactionFullWidth"
                                class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                title="<?php echo e($transactionFullWidth ? 'Kembali ke tampilan dua kolom' : 'Lebarkan panel transaksi (full width)'); ?>">
                            <!--[if BLOCK]><![endif]--><?php if($transactionFullWidth): ?>
                                <i class="fas fa-compress"></i>
                            <?php else: ?>
                                <i class="fas fa-expand"></i>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </button>
                    </div>
                </div>

                <!-- Tab Actions Bar -->
                <div class="px-3 lg:px-4 pb-3 flex items-center justify-between">
                    <!-- Cart Name Input -->
                    <div class="flex-1 mr-3">
                        <input type="text" 
                               wire:model.blur="carts.<?php echo e($activeTabId); ?>.name"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
                               placeholder="Nama keranjang...">
                    </div>
                    
                    <!-- Actions: Custom Item + Clear Cart -->
                    <div class="flex items-center space-x-2">

                        
                        <!-- Clear Cart Button -->
                        <!--[if BLOCK]><![endif]--><?php if(isset($carts[$activeTabId]) && !empty($carts[$activeTabId]['cart'])): ?>
                            <button wire:click="confirmClearCart" 
                                    class="px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Kosongkan Keranjang (F2)">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Kosongkan
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <!-- Bulk Price Type Controls -->
                <!--[if BLOCK]><![endif]--><?php if(isset($carts[$activeTabId]) && !empty($carts[$activeTabId]['cart'])): ?>
                    <div class="px-3 lg:px-4 pb-3">
                        <div class="pricing-tier-selector">
                            <label class="block text-xs font-medium text-gray-700 mb-2">Ubah Semua Harga Ke:</label>
                            <select wire:change="updateAllItemsPriceType($event.target.value)"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">-- Pilih Jenis Harga --</option>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = \App\Product::getPriceTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </select>
                        </div>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                
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
            <div class="flex-1 overflow-y-auto pb-24 md:pb-0 pos-cart-scroll">
                <!--[if BLOCK]><![endif]--><?php if(!isset($carts[$activeTabId]) || empty($carts[$activeTabId]['cart'])): ?>
                    <div class="flex items-center justify-center h-full text-gray-500">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5M7 13l-1.1 5m0 0h9.1M6 18a2 2 0 100 4 2 2 0 000-4zm12 0a2 2 0 100 4 2 2 0 000-4z"></path>
                            </svg>
                            <p class="mt-2">Keranjang masih kosong</p>
                            <p class="text-sm">Scan barcode atau pilih produk</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-4 lg:p-6 space-y-4">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = ($carts[$activeTabId]['cart'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-white border border-gray-200 rounded-xl p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3 flex-1">
                                        <!-- Product Image -->
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            <?php
                                                $product = $cartProducts->get($item['product_id']);
                                                $photoUrl = $product && method_exists($product, 'getPhotoUrl') ? $product->getPhotoUrl() : asset('storage/placeholders/no-image.svg');
                                            ?>
                                            <img src="<?php echo e($photoUrl); ?>" 
                                                 alt="<?php echo e($item['name']); ?>"
                                                 class="w-full h-full object-cover cursor-pointer hover:opacity-80 transition-opacity"
                                                 onclick="openImageModal('<?php echo e($photoUrl); ?>', '<?php echo e($item['name']); ?>')"
                                                 onerror="this.src='<?php echo e(asset('storage/placeholders/no-image.svg')); ?>'">
                                        </div>
                                        
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900"><?php echo e($item['name']); ?></h4>
                                            <p class="text-sm text-gray-500"><?php echo e($item['sku']); ?></p>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <?php
                                                    $selectedWarehouseStock = 0;
                                                    if (! empty($warehouseId) && $product) {
                                                        $selectedWarehouseStock = \App\ProductWarehouseStock::where('product_id', $product->id)
                                                            ->where('warehouse_id', $warehouseId)
                                                            ->value('stock_on_hand') ?? 0;
                                                    }
                                                ?>
                                                Stok gudang (Toko Utama): <?php echo e(number_format($selectedWarehouseStock)); ?>

                                            </div>
                                        </div>
                                    </div>
                                    <button wire:click="removeFromCart('<?php echo e($key); ?>')"
                                            class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <details class="md:open mb-3">
                                    <summary class="text-xs text-gray-600 cursor-pointer select-none py-1">Atur jumlah & harga</summary>
                                    <div class="grid grid-cols-2 gap-3">
                                        <!-- Quantity -->
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Jumlah</label>
                                            <input type="number" 
                                                   wire:change="updateQuantity('<?php echo e($key); ?>', $event.target.value)"
                                                   value="<?php echo e($item['quantity']); ?>"
                                                   min="1"
                                                   max="<?php echo e($item['available_stock']); ?>"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        
                                        <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Harga</label>
                                                <select wire:change="updateItemPriceType('<?php echo e($key); ?>', $event.target.value)"
                                                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = \App\Product::getPriceTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($value); ?>" <?php echo e($item['pricing_tier'] === $value ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                </select>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <?php
                                                        $product = \App\Product::find($item['product_id']);
                                                        $currentPriceType = $item['pricing_tier'];
                                                    ?>
                                                    <!--[if BLOCK]><![endif]--><?php if($product): ?>
                                                        <!--[if BLOCK]><![endif]--><?php if($currentPriceType === 'retail'): ?>
                                                            <span class="text-blue-600">Rp <?php echo e(number_format($product->price_retail ?? 0, 0, ',', '.')); ?></span>
                                                        <?php elseif($currentPriceType === 'semi_grosir'): ?>
                                                            <span class="text-yellow-600">Rp <?php echo e(number_format($product->price_semi_grosir ?? 0, 0, ',', '.')); ?></span>
                                                        <?php elseif($currentPriceType === 'grosir'): ?>
                                                            <span class="text-green-600">Rp <?php echo e(number_format($product->price_grosir ?? 0, 0, '.')); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-purple-600">Custom</span>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    <?php else: ?>
                                                        <span class="text-gray-500">Product not found</span>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                            </div>
                                        
                                        <!-- Custom Price Input -->
                                        <!--[if BLOCK]><![endif]--><?php if($item['pricing_tier'] === 'custom'): ?>
                                            <div class="col-span-2">
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Harga Custom</label>
                                                <input type="number" 
                                                       wire:change="updatePrice('<?php echo e($key); ?>', $event.target.value)"
                                                       value="<?php echo e($item['price']); ?>"
                                                       min="<?php echo e($item['base_cost'] * 1.1); ?>"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </details>
                                
                                <div class="flex justify-between items-center">
                                    <div class="text-xs text-gray-500">
                                        <div>Stok: <?php echo e($item['available_stock']); ?></div>
                                        <?php
                                            $product = \App\Product::find($item['product_id']);
                                            $costPrice = $product ? $product->getEffectiveCostPrice() : 0;
                                            $profit = ($item['price'] - $costPrice) * $item['quantity'];
                                            $margin = $costPrice > 0 ? (($item['price'] - $costPrice) / $item['price']) * 100 : 0;
                                        ?>
                                        <div class="text-orange-600">Modal: Rp <?php echo e(number_format($costPrice, 0, ',', '.')); ?></div>
                                        <div class="text-green-600">Profit: Rp <?php echo e(number_format($profit, 0, ',', '.')); ?> (<?php echo e(number_format($margin, 1)); ?>%)</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">
                                            <?php echo e(number_format($item['quantity'])); ?> × Rp <?php echo e(number_format($item['price'], 0, ',', '.')); ?>

                                        </div>
                                        <span class="font-semibold text-blue-600 text-lg">
                                            Rp <?php echo e(number_format($item['price'] * $item['quantity'], 0, ',', '.')); ?>

                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>

            <!-- Cart Summary & Checkout - Now Sticky on Desktop -->
            <!--[if BLOCK]><![endif]--><?php if(isset($carts[$activeTabId]) && !empty($carts[$activeTabId]['cart'])): ?>
                <div class="border-t border-gray-200 bg-white p-4 lg:p-6 desktop-sticky-summary">
                    <!-- Summary -->
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">Rp <?php echo e(number_format($subtotal, 0, ',', '.')); ?></span>
                        </div>
                        <?php
                            $totalCost = 0;
                            $totalProfit = 0;
                            foreach(($carts[$activeTabId]['cart'] ?? []) as $item) {
                                $product = \App\Product::find($item['product_id']);
                                $costPrice = $product ? $product->getEffectiveCostPrice() : 0;
                                $itemCost = $costPrice * $item['quantity'];
                                $itemProfit = ($item['price'] - $costPrice) * $item['quantity'];
                                $totalCost += $itemCost;
                                $totalProfit += $itemProfit;
                            }
                            $profitMargin = $subtotal > 0 ? ($totalProfit / $subtotal) * 100 : 0;
                        ?>
                        <div class="flex justify-between text-sm">
                            <span class="text-orange-600">Total Modal:</span>
                            <span class="font-medium text-orange-600">Rp <?php echo e(number_format($totalCost, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600">Total Profit:</span>
                            <span class="font-medium text-green-600">Rp <?php echo e(number_format($totalProfit, 0, ',', '.')); ?> (<?php echo e(number_format($profitMargin, 1)); ?>%)</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-3 border-gray-200">
                            <span class="text-gray-900">Total:</span>
                            <span class="text-blue-600">Rp <?php echo e(number_format($total, 0, ',', '.')); ?></span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <div class="space-y-3">
                        <button wire:click="openCheckout" 
                                class="w-full bg-blue-600 text-white py-4 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl"
                                title="Shortcut: F1">
                            <i class="fas fa-cash-register mr-2"></i>
                            Checkout (F1)
                        </button>
                        
                        <a href="<?php echo e(route('kasir.management')); ?>" 
                           class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-gray-700 transition-colors shadow-lg hover:shadow-xl flex items-center justify-center">
                            <i class="fas fa-history mr-2"></i>
                            Riwayat Transaksi
                        </a>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    <!-- Floating menu dihapus sesuai permintaan -->


    <!-- Checkout Modal -->
    <!--[if BLOCK]><![endif]--><?php if($showCheckoutModal): ?>
        <div class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="closeCheckout">
            <div class="bg-white rounded-t-2xl sm:rounded-lg p-4 sm:p-6 w-full sm:max-w-md mx-0 sm:mx-4 h-[90vh] sm:h-auto overflow-y-auto" onclick="event.stopPropagation()">
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
                            <input type="number" wire:model.live.debounce.300ms="amountPaid" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea wire:model="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    
                    <!-- Summary -->
                    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
                        <div class="flex justify-between text-sm mb-2">
                            <span>Total:</span>
                            <span class="font-semibold">Rp <?php echo e(number_format($total, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span>Bayar:</span>
                            <span>Rp <?php echo e(number_format($amountPaid, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold border-t pt-2">
                            <span>Kembalian:</span>
                            <span class="<?php echo e($change >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                Rp <?php echo e(number_format($change, 0, ',', '.')); ?>

                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm mt-2">
                            <span>Status Pembayaran:</span>
                            <div class="flex gap-2">
                                <button type="button" wire:click="$set('paymentStatus', 'UNPAID')" class="px-2 py-1 text-xs rounded-md border <?php echo e($paymentStatus === 'UNPAID' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'); ?>">
                                    Belum dibayar
                                </button>
                                <button type="button" wire:click="$set('paymentStatus', 'PARTIAL')" class="px-2 py-1 text-xs rounded-md border <?php echo e($paymentStatus === 'PARTIAL' ? 'bg-yellow-500 text-white border-yellow-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'); ?>">
                                    Sebagian
                                </button>
                                <button type="button" wire:click="$set('paymentStatus', 'PAID')" class="px-2 py-1 text-xs rounded-md border <?php echo e($paymentStatus === 'PAID' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100'); ?>">
                                    Lunas
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" wire:click="closeCheckout" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Receipt Modal -->
    <!--[if BLOCK]><![endif]--><?php if($showReceiptModal && $lastSale): ?>
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4" id="receipt">
                <div class="text-center mb-6">
                    <h3 class="text-lg font-bold">STRUK PENJUALAN</h3>
                    <p class="text-sm text-gray-600"><?php echo e($lastSale->sale_number); ?></p>
                    <p class="text-xs text-gray-500"><?php echo e($lastSale->created_at->format('d/m/Y H:i:s')); ?></p>
                </div>
                
                <!-- Customer Info -->
                <!--[if BLOCK]><![endif]--><?php if($lastSale->customer_name): ?>
                    <div class="mb-4 text-sm">
                        <p><strong>Pelanggan:</strong> <?php echo e($lastSale->customer_name); ?></p>
                        <!--[if BLOCK]><![endif]--><?php if($lastSale->customer_phone): ?>
                            <p><strong>Telepon:</strong> <?php echo e($lastSale->customer_phone); ?></p>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                
                <!-- Items -->
                <div class="border-t border-b border-gray-300 py-3 mb-4">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $lastSale->saleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex justify-between text-sm mb-1">
                            <div class="flex-1">
                                <div><?php echo e(optional($item->product)->name ?? ($item->custom_item_name ?? '-')); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php echo e($item->qty); ?> x Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?>

                                </div>
                            </div>
                            <div class="text-right">
                                Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                
                <!-- Summary -->
                <div class="text-sm space-y-1 mb-4">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>Rp <?php echo e(number_format($lastSale->subtotal, 0, ',', '.')); ?></span>
                    </div>
                    
                    <div class="flex justify-between font-semibold border-t pt-1">
                        <span>Total:</span>
                        <span>Rp <?php echo e(number_format($lastSale->final_total, 0, ',', '.')); ?></span>
                    </div>
                    <?php
                        $paid = ($lastSale->cash_amount ?? 0) + ($lastSale->qr_amount ?? 0) + ($lastSale->edc_amount ?? 0);
                    ?>
                    <div class="flex justify-between">
                        <span>Bayar (<?php echo e(ucfirst($lastSale->payment_method)); ?>):</span>
                        <span>Rp <?php echo e(number_format($paid, 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span>Kembalian:</span>
                        <span>Rp <?php echo e(number_format($lastSale->change_amount, 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Status Pembayaran:</span>
                        <?php
                            $statusLabel = match($lastSale->status) {
                                'PAID' => 'Lunas',
                                'PARTIAL' => 'Sebagian',
                                'UNPAID' => 'Belum dibayar',
                                default => ucfirst(strtolower($lastSale->status ?? ''))
                            };
                            $statusClass = match($lastSale->status) {
                                'PAID' => 'text-green-600',
                                'PARTIAL' => 'text-yellow-600',
                                'UNPAID' => 'text-red-600',
                                default => 'text-gray-600'
                            };
                        ?>
                        <span class="<?php echo e($statusClass); ?> font-medium"><?php echo e($statusLabel); ?></span>
                    </div>
                </div>
                
                <!--[if BLOCK]><![endif]--><?php if($lastSale->notes): ?>
                    <div class="text-xs text-gray-600 mb-4">
                        <strong>Catatan:</strong> <?php echo e($lastSale->notes); ?>

                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                
                <div class="text-center text-xs text-gray-500 mb-6">
                    <p>Terima kasih atas kunjungan Anda!</p>
                    <p>Kasir: <?php echo e($lastSale->cashier->name ?? 'System'); ?></p>
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
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
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('createNewTab');
            }
            
            // Ctrl+W - Close current tab
            if (e.ctrlKey && e.key === 'w') {
                e.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('closeTab', <?php echo e($activeTabId); ?>);
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
                    
                    const currentTabId = <?php echo e($activeTabId); ?>;
                    const currentIndex = tabIds.indexOf(currentTabId);
                    const nextIndex = currentIndex >= 0 ? (currentIndex + 1) % tabIds.length : 0;
                    
                    if (tabIds[nextIndex]) {
                        window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('switchToTab', tabIds[nextIndex]);
                    }
                }
            }
            
            // F1 - Open Checkout (if cart not empty)
            if (e.key === 'F1') {
                e.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('openCheckout');
            }
            
            // F2 - Clear Cart
            if (e.key === 'F2') {
                e.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('confirmClearCart');
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
                const searchInput = document.getElementById('product-search-input') || document.querySelector('input[wire\\:model\\.live="productSearch"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // ESC - Close modals or clear search
            if (e.key === 'Escape') {
                if (document.querySelector('.modal')) {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('closeCheckout');
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('closeReceipt');
                } else {
                    // Clear search and focus barcode only if not interacting with form elements
                    const activeElement = document.activeElement;
                    const isFormInteraction = activeElement && (
                        activeElement.tagName === 'SELECT' ||
                        activeElement.closest('.pricing-tier-selector')
                    );
                    
                    if (!isFormInteraction) {
                        window.Livewire.find('<?php echo e($_instance->getId()); ?>').set('productSearch', '');
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
                            window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('addProductByBarcode');
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


    <script>
        function focusProductSearch() {
             const searchInput = document.getElementById('product-search-input') || document.querySelector('input[wire\:model\.live="productSearch"]');
             if (searchInput) {
                 searchInput.focus();
                 searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
             }
         }
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
<?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/livewire/pos-kasir.blade.php ENDPATH**/ ?>