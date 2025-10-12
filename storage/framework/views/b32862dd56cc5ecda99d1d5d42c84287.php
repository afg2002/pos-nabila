<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Gudang</h2>
        <p class="text-gray-600 dark:text-gray-400">Kelola data gudang, toko, dan kiosk</p>
    </div>

    <!-- Flash Messages -->
    <!--[if BLOCK]><![endif]--><?php if(session()->has('message')): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Modal Detail Warehouse -->
    <!--[if BLOCK]><![endif]--><?php if($showDetailModal && $selectedWarehouse): ?>
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
                                <p class="mt-1 text-sm text-gray-900 dark:text-white font-mono"><?php echo e($selectedWarehouse ? $selectedWarehouse->code : '-'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                                <div class="mt-1">
                                    <!--[if BLOCK]><![endif]--><?php if($selectedWarehouse): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php echo e($selectedWarehouse->type === 'main' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                            <?php echo e($selectedWarehouse->type === 'branch' ? 'bg-green-100 text-green-800' : ''); ?>

                                            <?php echo e($selectedWarehouse->type === 'storage' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                                            <?php echo e($selectedWarehouse->type === 'transit' ? 'bg-purple-100 text-purple-800' : ''); ?>

                                            <!--[if BLOCK]><![endif]--><?php if($selectedWarehouse->type === 'main'): ?>
                                                <i class="fas fa-building mr-1"></i>
                                            <?php elseif($selectedWarehouse->type === 'branch'): ?>
                                                <i class="fas fa-code-branch mr-1"></i>
                                            <?php elseif($selectedWarehouse->type === 'storage'): ?>
                                                <i class="fas fa-archive mr-1"></i>
                                            <?php elseif($selectedWarehouse->type === 'transit'): ?>
                                                <i class="fas fa-truck mr-1"></i>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            <?php echo e(ucfirst($selectedWarehouse->type)); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Warehouse</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white font-semibold"><?php echo e($selectedWarehouse ? $selectedWarehouse->name : '-'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($selectedWarehouse ? ($selectedWarehouse->address ?: 'Tidak ada alamat') : '-'); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cabang</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($selectedWarehouse ? ($selectedWarehouse->branch ?: 'Tidak ada cabang') : '-'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($selectedWarehouse ? ($selectedWarehouse->phone ?: 'Tidak ada telepon') : '-'); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warehouse Default</label>
                                <div class="mt-1">
                                    <!--[if BLOCK]><![endif]--><?php if($selectedWarehouse): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php echo e($selectedWarehouse->is_default ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'); ?>">
                                            <i class="fas <?php echo e($selectedWarehouse->is_default ? 'fa-star' : 'fa-star-o'); ?> mr-1"></i>
                                            <?php echo e($selectedWarehouse->is_default ? 'Default' : 'Bukan Default'); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        </div>

                        <!-- Statistik Stok -->
                        <!--[if BLOCK]><![endif]--><?php if($selectedWarehouse && $selectedWarehouse->productStocks->count() > 0): ?>
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Statistik Stok</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-blue-900 dark:text-blue-100"><?php echo e($selectedWarehouse->productStocks->count()); ?></div>
                                        <div class="text-sm text-blue-700 dark:text-blue-300">Total Produk</div>
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-green-900 dark:text-green-100"><?php echo e(number_format($selectedWarehouse->productStocks->sum('stock_on_hand'))); ?></div>
                                        <div class="text-sm text-green-700 dark:text-green-300">Total Stok</div>
                                    </div>
                                    <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                                        <div class="text-2xl font-bold text-red-900 dark:text-red-100">
                                            <?php echo e($selectedWarehouse->productStocks->filter(function($stock) { return $stock->stock_on_hand <= ($stock->product->min_stock ?? 0); })->count()); ?>

                                        </div>
                                        <div class="text-sm text-red-700 dark:text-red-300">Stok Menipis</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Products -->
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Produk dengan Stok Terbanyak</h4>
                                <div class="space-y-3">
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedWarehouse->productStocks->sortByDesc('stock_on_hand')->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white"><?php echo e($stock->product->name); ?></div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($stock->product->sku); ?></div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-bold text-gray-900 dark:text-white"><?php echo e(number_format($stock->stock_on_hand)); ?></div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($stock->product->unit ? $stock->product->unit->abbreviation : 'pcs'); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                                <div class="text-center py-8">
                                    <i class="fas fa-box-open fa-3x text-gray-300 mb-4"></i>
                                    <p>Belum ada stok produk di warehouse ini</p>
                                </div>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!-- Informasi Tambahan -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dibuat</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <?php echo e($selectedWarehouse ? $selectedWarehouse->created_at->format('d/m/Y H:i') : '-'); ?>

                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Terakhir Diupdate</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        <?php echo e($selectedWarehouse ? $selectedWarehouse->updated_at->format('d/m/Y H:i') : '-'); ?>

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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Controls -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Search and Filters -->
        <div class="flex flex-col sm:flex-row gap-4 flex-1">
            <!-- Search -->
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Cari gudang..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <!-- Type Filter -->
            <div class="sm:w-48">
                <select wire:model.live="typeFilter" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Tipe</option>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $warehouseTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2">
            <!--[if BLOCK]><![endif]--><?php if(count($selectedWarehouses) > 0): ?>
                <button wire:click="confirmBulkDelete" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <i class="fas fa-trash mr-2"></i>Hapus Terpilih (<?php echo e(count($selectedWarehouses)); ?>)
                </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
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
                                <!--[if BLOCK]><![endif]--><?php if($sortField === 'name'): ?>
                                    <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                <?php else: ?>
                                    <i class="fas fa-sort text-gray-400"></i>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('code')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-100">
                                <span>Kode</span>
                                <!--[if BLOCK]><![endif]--><?php if($sortField === 'code'): ?>
                                    <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                <?php else: ?>
                                    <i class="fas fa-sort text-gray-400"></i>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <button wire:click="sortBy('type')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-100">
                                <span>Tipe</span>
                                <!--[if BLOCK]><![endif]--><?php if($sortField === 'type'): ?>
                                    <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?>"></i>
                                <?php else: ?>
                                    <i class="fas fa-sort text-gray-400"></i>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Telepon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model.live="selectedWarehouses" value="<?php echo e($warehouse->id); ?>"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo e($warehouse->name); ?>

                                    <!--[if BLOCK]><![endif]--><?php if($warehouse->is_default): ?>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-star mr-1"></i>Default
                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono text-gray-900 dark:text-white"><?php echo e($warehouse->code); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php switch($warehouse->type):
                                        case ('main'): ?>
                                            bg-blue-100 text-blue-800
                                            <?php break; ?>
                                        <?php case ('branch'): ?>
                                            bg-green-100 text-green-800
                                            <?php break; ?>
                                        <?php case ('warehouse'): ?>
                                            bg-green-100 text-green-800
                                            Gudang
                                            <?php break; ?>
                                        <?php case ('storage'): ?>
                                            bg-yellow-100 text-yellow-800
                                            <?php break; ?>
                                        <?php case ('transit'): ?>
                                            bg-purple-100 text-purple-800
                                            <?php break; ?>
                                        <?php default: ?>
                                            bg-gray-100 text-gray-800
                                    <?php endswitch; ?>">
                                    <?php echo e(ucfirst($warehouse->type)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?php echo e($warehouse->branch ?? '-'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div class="max-w-xs truncate" title="<?php echo e($warehouse->address); ?>">
                                    <?php echo e($warehouse->address ?? '-'); ?>

                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?php echo e($warehouse->phone ?? '-'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $warehouse)): ?>
                                        <button wire:click="openDetailModal(<?php echo e($warehouse->id); ?>)"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $warehouse)): ?>
                                        <button wire:click="openEditModal(<?php echo e($warehouse->id); ?>)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $warehouse)): ?>
                                        <!--[if BLOCK]><![endif]--><?php if(!$warehouse->is_default): ?>
                                            <button wire:click="confirmDelete(<?php echo e($warehouse->id); ?>)"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-warehouse fa-3x mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium mb-2">Tidak ada gudang ditemukan</p>
                                    <p class="text-sm">Belum ada data gudang yang sesuai dengan pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php echo e($warehouses->links('pagination::simple-tailwind')); ?>

                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Menampilkan
                            <span class="font-medium"><?php echo e($warehouses->firstItem() ?? 0); ?></span>
                            sampai
                            <span class="font-medium"><?php echo e($warehouses->lastItem() ?? 0); ?></span>
                            dari
                            <span class="font-medium"><?php echo e($warehouses->total()); ?></span>
                            hasil
                        </p>
                    </div>
                    <div>
                        <?php echo e($warehouses->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <!-- Modal dihapus sesuai permintaan -->

    <!-- Edit Modal -->
    <!--[if BLOCK]><![endif]--><?php if($showEditModal): ?>
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
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div>
                                <label for="edit_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Gudang</label>
                                <input type="text" wire:model="code" id="edit_code" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div>
                                <label for="edit_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe</label>
                                <select wire:model="type" id="edit_type" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Pilih Tipe</option>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $warehouseTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </select>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div>
                                <label for="edit_branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cabang</label>
                                <input type="text" wire:model="branch" id="edit_branch" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['branch'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div>
                                <label for="edit_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                                <textarea wire:model="address" id="edit_address" rows="3"
                                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div>
                                <label for="edit_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon</label>
                                <input type="text" wire:model="phone" id="edit_phone" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Delete Confirmation Modal -->
    <!--[if BLOCK]><![endif]--><?php if($showDeleteModal && $warehouseToDelete): ?>
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" wire:click.stop>
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Konfirmasi Hapus</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Apakah Anda yakin ingin menghapus gudang <strong><?php echo e($warehouseToDelete->name); ?></strong>?
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH C:\laragon\www\pos-nabila\resources\views/livewire/warehouse-table.blade.php ENDPATH**/ ?>