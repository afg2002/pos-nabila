<div class="space-y-6">
    <!-- Header with Warehouse Selection -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">📦 Stok per Gudang</h2>
            <div class="text-sm text-gray-500">
                Kelola dan pantau stok berdasarkan gudang
            </div>
        </div>
        
        <!-- Warehouse Selection -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="warehouseSelect" class="block text-sm font-medium text-gray-700 mb-1">Pilih Gudang</label>
                <select wire:model.live="selectedWarehouse" id="warehouseSelect"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Semua Gudang</option>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($warehouse->id); ?>">
                            <?php echo e($warehouse->name); ?> (<?php echo e($warehouse->code); ?>)
                            <!--[if BLOCK]><![endif]--><?php if($warehouse->is_default): ?> - Default <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </select>
            </div>
            
            <div>
                <label for="searchStock" class="block text-sm font-medium text-gray-700 mb-1">Cari Produk</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="searchStock"
                       placeholder="Nama, SKU, atau barcode..."
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <div>
                <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select wire:model.live="categoryFilter" id="categoryFilter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Semua Kategori</option>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category); ?>"><?php echo e($category); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </select>
            </div>
            
            <div>
                <label for="stockFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter Stok</label>
                <select wire:model.live="stockFilter" id="stockFilter"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="all">Semua Stok</option>
                    <option value="in_stock">Ada Stok</option>
                    <option value="low_stock">Stok Menipis</option>
                    <option value="out_of_stock">Stok Habis</option>
                </select>
            </div>
        </div>
    </div>

    <!--[if BLOCK]><![endif]--><?php if($selectedWarehouse): ?>
        <!-- Stock Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <p class="text-sm font-medium text-blue-600 truncate">Total Produk</p>
                        <p class="text-xl lg:text-2xl font-semibold text-blue-900 truncate"><?php echo e(number_format($stockSummary['total_products'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <p class="text-sm font-medium text-green-600 truncate">Total Stok</p>
                        <p class="text-xl lg:text-2xl font-semibold text-green-900 truncate"><?php echo e(number_format($stockSummary['total_stock'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <p class="text-sm font-medium text-yellow-600 truncate">Stok Menipis</p>
                        <p class="text-xl lg:text-2xl font-semibold text-yellow-900 truncate"><?php echo e(number_format($stockSummary['low_stock'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <p class="text-sm font-medium text-red-600 truncate">Stok Habis</p>
                        <p class="text-xl lg:text-2xl font-semibold text-red-900 truncate"><?php echo e(number_format($stockSummary['out_of_stock'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <p class="text-sm font-medium text-purple-600 truncate">Nilai Stok</p>
                        <p class="text-lg lg:text-xl font-semibold text-purple-900 break-words" title="Rp <?php echo e(number_format($stockSummary['total_value'], 0, ',', '.')); ?>">
                            Rp <?php echo e(number_format($stockSummary['total_value'], 0, ',', '.')); ?>

                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Stock Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">
                    <!--[if BLOCK]><![endif]--><?php if($selectedWarehouse): ?>
                        Stok Gudang: <?php echo e($warehouses->firstWhere('id', $selectedWarehouse)?->name); ?>

                    <?php else: ?>
                        Semua Stok Gudang
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </h3>
                <div class="text-sm text-gray-500">
                    <?php echo e($stockData->total()); ?> produk ditemukan
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
                        <!--[if BLOCK]><![endif]--><?php if(!$selectedWarehouse): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min. Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $stockData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="<?php echo e($stock->product->photo ? asset('storage/' . $stock->product->photo) : asset('storage/placeholders/no-image.svg')); ?>" 
                                             alt="<?php echo e($stock->product->name); ?>"
                                             onerror="this.src='<?php echo e(asset('storage/placeholders/no-image.svg')); ?>'">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($stock->product->name); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo e($stock->product->unit?->name ?? '-'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 bg-gray-100 rounded-full text-xs font-mono">
                                    <?php echo e($stock->product->sku); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                    <?php echo e($stock->product->category ?? '-'); ?>

                                </span>
                            </td>
                            <!--[if BLOCK]><![endif]--><?php if(!$selectedWarehouse): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        <?php echo e($stock->warehouse->name); ?>

                                    </span>
                                </td>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php
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
                                ?>
                                <span class="<?php echo e($stockClass); ?>"><?php echo e(number_format($stockLevel)); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e(number_format($stock->product->min_stock ?? 10)); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp <?php echo e(number_format($stock->product->base_cost, 0, ',', '.')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                Rp <?php echo e(number_format($stock->stock_on_hand * $stock->product->base_cost, 0, ',', '.')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!--[if BLOCK]><![endif]--><?php if($stock->stock_on_hand <= 0): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        Habis
                                    </span>
                                <?php elseif($stock->stock_on_hand <= ($stock->product->min_stock ?? 10)): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Menipis
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Aman
                                    </span>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e($selectedWarehouse ? '8' : '9'); ?>" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium">Tidak ada data stok</p>
                                    <p class="text-gray-400 text-sm mt-1">Pilih gudang atau ubah filter pencarian</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <!--[if BLOCK]><![endif]--><?php if($stockData->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($stockData->links()); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/livewire/warehouse-stock-view.blade.php ENDPATH**/ ?>