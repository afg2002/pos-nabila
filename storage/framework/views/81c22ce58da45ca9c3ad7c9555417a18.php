<div>
    <!-- Trigger Button -->
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage', App\StockMovement::class)): ?>
        <button wire:click="openModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Update Stok
        </button>
    <?php else: ?>
        <div class="text-gray-500 text-sm">
            <i class="fas fa-lock mr-1"></i>
            Anda tidak memiliki akses untuk mengelola stok
        </div>
    <?php endif; ?>

    <!-- Modal -->
    <!--[if BLOCK]><![endif]--><?php if($showModal): ?>
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
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($warehouse->id); ?>">
                                                    <?php echo e($warehouse->name); ?> (<?php echo e($warehouse->code); ?>)
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </select>
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['warehouse_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>

                                    <!-- Product Selection with Search -->
                    <div>
                        <label for="product_search" class="block text-sm font-medium text-gray-700 mb-1">
                            Produk *
                        </label>
                        <div class="relative" x-data="{ showResults: <?php if ((object) ('showSearchResults') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showSearchResults'->value()); ?>')<?php echo e('showSearchResults'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showSearchResults'); ?>')<?php endif; ?>, showDropdown: <?php if ((object) ('showDropdown') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showDropdown'->value()); ?>')<?php echo e('showDropdown'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showDropdown'); ?>')<?php endif; ?> }">
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
                                    <!--[if BLOCK]><![endif]--><?php if($productSearch): ?>
                                        <!-- Clear button when there's text -->
                                        <svg wire:click.stop="clearProductSearch" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    <?php else: ?>
                                        <!-- Dropdown arrow when no text -->
                                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': showDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </button>
                            </div>
                            
                            <!-- Search Results Dropdown -->
                            <!--[if BLOCK]><![endif]--><?php if($showSearchResults && count($searchResults) > 0): ?>
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $searchResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <button type="button" 
                                                wire:click="selectProduct(<?php echo e($product->id); ?>)"
                                                class="w-full px-3 py-2 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none border-b border-gray-100 last:border-b-0">
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <div class="font-medium text-gray-900"><?php echo e($product->name); ?></div>
                                                    <div class="text-sm text-gray-500">SKU: <?php echo e($product->sku); ?></div>
                                                    <!--[if BLOCK]><![endif]--><?php if($product->barcode): ?>
                                                        <div class="text-xs text-gray-400">Barcode: <?php echo e($product->barcode); ?></div>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo e($product->category ?? 'No Category'); ?>

                                                </div>
                                            </div>
                                        </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php elseif($showSearchResults && strlen($productSearch) >= 2): ?>
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                    <div class="px-3 py-2 text-gray-500 text-sm">
                                        Tidak ada produk ditemukan untuk "<?php echo e($productSearch); ?>"
                                    </div>
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <!-- All Products Dropdown (when no search) -->
                            <!--[if BLOCK]><![endif]--><?php if($showDropdown && empty($productSearch)): ?>
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <!--[if BLOCK]><![endif]--><?php if(count($allProducts) > 0): ?>
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $allProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button type="button" 
                                                    wire:click="selectProduct(<?php echo e($product->id); ?>)"
                                                    class="w-full px-3 py-2 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none border-b border-gray-100 last:border-b-0">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <div class="font-medium text-gray-900"><?php echo e($product->name); ?></div>
                                                        <div class="text-sm text-gray-500">SKU: <?php echo e($product->sku); ?></div>
                                                        <!--[if BLOCK]><![endif]--><?php if($product->barcode): ?>
                                                            <div class="text-xs text-gray-400">Barcode: <?php echo e($product->barcode); ?></div>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo e($product->category ?? 'No Category'); ?>

                                                    </div>
                                                </div>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                    <?php else: ?>
                                        <div class="px-3 py-2 text-gray-500 text-sm">
                                            Tidak ada produk tersedia
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                        
                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> 
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        
                        <!--[if BLOCK]><![endif]--><?php if($selectedProduct): ?>
                            <div class="mt-2 p-2 bg-blue-50 rounded-md">
                                <div class="text-sm text-blue-800">
                                    <strong><?php echo e($selectedProduct->name); ?></strong> (<?php echo e($selectedProduct->sku); ?>)
                                </div>
                                <div class="text-xs text-blue-600 mt-1">
                                    Stok lokasi terpilih: <span class="font-semibold"><?php echo e(number_format($currentWarehouseStock)); ?></span>
                                </div>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
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
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['movement_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> 
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                  
                                    </div>

                                    <!-- Quantity -->
                                    <div>
                                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                            Jumlah *
                                        </label>
                                        <input type="number" id="quantity" wire:model.live="quantity"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               min="1" placeholder="Masukkan jumlah">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> 
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>

                                    <!-- Reason Code -->
                                    <div>
                                        <label for="reason_code" class="block text-sm font-medium text-gray-700 mb-1">
                                            Kode Alasan
                                        </label>
                                        <input type="text" id="reason_code" wire:model.live="reason_code"
                                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="Contoh: stock_opname, retur, expired">
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['reason_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> 
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                            Catatan
                                        </label>
                                        <textarea wire:model="notes" id="notes" rows="3"
                                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                  placeholder="Catatan tambahan (opsional)"></textarea>
                                        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> 
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Flash Messages -->
    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span><?php echo e(session('message')); ?></span>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php if(session()->has('error')): ?>
        <div class="fixed top-4 right-4 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span><?php echo e(session('error')); ?></span>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\laragon\www\pos-nabila\resources\views/livewire/stock-form.blade.php ENDPATH**/ ?>