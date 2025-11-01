<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50/30 to-purple-50/30">
    <!-- Header -->
    <div class="mb-6 animate-fadeInUp">
        <div class="bg-white rounded-2xl shadow-modern-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Manajemen Kasir
                    </h1>
                    <p class="text-gray-600 mt-1">Kelola dan lihat riwayat transaksi POS</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?php echo e(route('pos.index')); ?>"
                       class="btn-primary px-4 py-3 text-sm font-medium text-white rounded-xl hover:shadow-modern transition-all duration-300">
                        <i class="fas fa-cash-register mr-2"></i>
                        Kembali ke POS
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-modern p-6 hover-glow transition-all duration-300 animate-slideInRight" style="animation-delay: 0.1s">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-receipt text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo e(number_format($totalTransactions)); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-modern p-6 hover-glow transition-all duration-300 animate-slideInRight" style="animation-delay: 0.2s">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">Total Penjualan</p>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900">Rp <?php echo e(number_format($totalSales, 0, ',', '.')); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-modern p-6 hover-glow transition-all duration-300 animate-slideInRight sm:col-span-2 lg:col-span-1" style="animation-delay: 0.3s">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600">Rata-rata per Transaksi</p>
                    <p class="text-2xl md:text-3xl font-bold text-gray-900">
                        Rp <?php echo e($totalTransactions > 0 ? number_format($totalSales / $totalTransactions, 0, ',', '.') : '0'); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Transaksi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="No. transaksi, nama, telepon..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="dateFrom"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="dateTo"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Cashier -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kasir</label>
                    <select wire:model.live="cashierId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kasir</option>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $cashiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cashier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cashier->id); ?>"><?php echo e($cashier->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Bayar</label>
                    <select wire:model.live="paymentMethod"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Metode</option>
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="edc">EDC/Kartu</option>
                        <option value="qr">QR Code</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button wire:click="resetFilters"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="fas fa-undo mr-2"></i>
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Transaction Cards for Mobile / Table for Desktop -->
    <div class="bg-white rounded-2xl shadow-modern-lg overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-blue-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                            No. Transaksi
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                            Tanggal & Waktu
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                            Pelanggan
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                            Kasir
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                            Total
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                            Metode Bayar
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                            Status
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-blue-50 transition-colors duration-150 <?php if($sale->status === 'CANCELLED'): ?> opacity-60 <?php endif; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900"><?php echo e($sale->sale_number); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($sale->created_at->format('d/m/Y')); ?></div>
                                <div class="text-sm text-gray-500"><?php echo e($sale->created_at->format('H:i:s')); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($sale->customer_name ?: '-'); ?></div>
                                <!--[if BLOCK]><![endif]--><?php if($sale->customer_phone): ?>
                                    <div class="text-xs text-gray-500"><?php echo e($sale->customer_phone); ?></div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-user text-blue-600 text-xs"></i>
                                    </div>
                                    <div class="text-sm text-gray-900"><?php echo e($sale->cashier->name ?? 'System'); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-blue-600">
                                    Rp <?php echo e(number_format($sale->final_total, 0, ',', '.')); ?>

                                </div>
                                <div class="text-xs text-gray-500">
                                    <?php echo e($sale->saleItems->count()); ?> item
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1.5 text-xs font-bold rounded-full
                                    <?php if($sale->payment_method === 'cash'): ?> bg-green-100 text-green-800 border border-green-200
                                    <?php elseif($sale->payment_method === 'transfer'): ?> bg-blue-100 text-blue-800 border border-blue-200
                                    <?php elseif($sale->payment_method === 'edc'): ?> bg-purple-100 text-purple-800 border border-purple-200
                                    <?php elseif($sale->payment_method === 'qr'): ?> bg-orange-100 text-orange-800 border border-orange-200
                                    <?php else: ?> bg-gray-100 text-gray-800 border border-gray-200 <?php endif; ?>">
                                    <?php echo e(ucfirst($sale->payment_method)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                    $paymentStatus = strtoupper($sale->payment_status ?? '');
                                    $statusLabel = match($paymentStatus) {
                                        'PAID' => 'Lunas',
                                        'PARTIAL' => 'Sebagian',
                                        'UNPAID' => 'Belum dibayar',
                                        default => ucfirst(strtolower($sale->payment_status ?? ''))
                                    };
                                    $statusClass = match($paymentStatus) {
                                        'PAID' => 'bg-green-100 text-green-800 border border-green-200',
                                        'PARTIAL' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                        'UNPAID' => 'bg-red-100 text-red-800 border border-red-200',
                                        default => 'bg-gray-100 text-gray-800 border border-gray-200'
                                    };
                                ?>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo e($statusClass); ?>">
                                        <?php echo e($statusLabel); ?>

                                    </span>
                                    <!--[if BLOCK]><![endif]--><?php if($sale->status === 'CANCELLED'): ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                            Dibatalkan
                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <button wire:click="showDetail(<?php echo e($sale->id); ?>)"
                                            class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                                            title="Detail Transaksi">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- Print Dropdown -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                                class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition-colors"
                                                title="Cetak Struk">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <div x-show="open"
                                             @click.away="open = false"
                                             x-cloak
                                             x-transition
                                             class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                                            <button wire:click="printReceiptThermal(<?php echo e($sale->id); ?>); open = false"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50 flex items-center gap-2 first:rounded-t-lg transition-colors">
                                                <i class="fas fa-print text-blue-600 w-4"></i>
                                                <span>Thermal</span>
                                            </button>
                                            <button wire:click="exportReceiptPNG(<?php echo e($sale->id); ?>); open = false"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-green-50 flex items-center gap-2 border-t transition-colors">
                                                <i class="fas fa-image text-green-600 w-4"></i>
                                                <span>PNG</span>
                                            </button>
                                            <button wire:click="exportReceiptPDFThermal(<?php echo e($sale->id); ?>); open = false"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-purple-50 flex items-center gap-2 border-t transition-colors">
                                                <i class="fas fa-file-pdf text-purple-600 w-4"></i>
                                                <span>PDF</span>
                                            </button>
                                            <button wire:click="exportInvoiceA4(<?php echo e($sale->id); ?>); open = false"
                                                    class="w-full px-3 py-2 text-left text-sm hover:bg-orange-50 flex items-center gap-2 border-t last:rounded-b-lg transition-colors">
                                                <i class="fas fa-file-invoice text-orange-600 w-4"></i>
                                                <span>Invoice</span>
                                            </button>
                                        </div>
                                    </div>

                                    <button wire:click="confirmDelete(<?php echo e($sale->id); ?>)"
                                            class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                                            title="Hapus Transaksi"
                                            <?php if($sale->status === 'CANCELLED'): ?> disabled <?php endif; ?>>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-receipt text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">Tidak ada transaksi</p>
                                    <p class="text-sm">Belum ada transaksi yang sesuai dengan filter</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden">
            <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-4 border-b border-gray-200 hover:bg-gray-50 transition-colors animate-slideInUp">
                    <!-- Transaction Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900"><?php echo e($sale->sale_number); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e($sale->created_at->format('d/m/Y H:i')); ?></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php
                                $paymentStatus = strtoupper($sale->payment_status ?? '');
                                $statusLabel = match($paymentStatus) {
                                    'PAID' => 'Lunas',
                                    'PARTIAL' => 'Sebagian',
                                    'UNPAID' => 'Belum dibayar',
                                    default => ucfirst(strtolower($sale->payment_status ?? ''))
                                };
                                $statusClass = match($paymentStatus) {
                                    'PAID' => 'bg-green-100 text-green-800',
                                    'PARTIAL' => 'bg-yellow-100 text-yellow-800',
                                    'UNPAID' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo e($statusClass); ?>">
                                <?php echo e($statusLabel); ?>

                            </span>
                            <!--[if BLOCK]><![endif]--><?php if($sale->status === 'CANCELLED'): ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Dibatalkan
                                </span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <!--[if BLOCK]><![endif]--><?php if($sale->customer_name || $sale->customer_phone): ?>
                        <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                            <div class="text-xs font-medium text-gray-600 mb-1">Pelanggan</div>
                            <div class="text-sm text-gray-900"><?php echo e($sale->customer_name ?: '-'); ?></div>
                            <!--[if BLOCK]><![endif]--><?php if($sale->customer_phone): ?>
                                <div class="text-xs text-gray-500"><?php echo e($sale->customer_phone); ?></div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                    <!-- Transaction Details -->
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600 mb-1">Total</div>
                            <div class="text-lg font-bold text-blue-600">
                                Rp <?php echo e(number_format($sale->final_total, 0, ',', '.')); ?>

                            </div>
                            <div class="text-xs text-gray-500"><?php echo e($sale->saleItems->count()); ?> item</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600 mb-1">Metode</div>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                <?php if($sale->payment_method === 'cash'): ?> bg-green-100 text-green-800
                                <?php elseif($sale->payment_method === 'transfer'): ?> bg-blue-100 text-blue-800
                                <?php elseif($sale->payment_method === 'edc'): ?> bg-purple-100 text-purple-800
                                <?php elseif($sale->payment_method === 'qr'): ?> bg-orange-100 text-orange-800
                                <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                <?php echo e(ucfirst($sale->payment_method)); ?>

                            </span>
                        </div>
                    </div>

                    <!-- Cashier Info -->
                    <div class="mb-3 p-3 bg-blue-50 rounded-lg">
                        <div class="text-xs font-medium text-gray-600 mb-1">Kasir</div>
                        <div class="text-sm text-gray-900"><?php echo e($sale->cashier->name ?? 'System'); ?></div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button wire:click="showDetail(<?php echo e($sale->id); ?>)"
                                class="flex-1 px-3 py-2 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            Detail
                        </button>

                        <!-- Print Dropdown (Mobile) -->
                        <div class="flex-1 relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="w-full px-3 py-2 text-sm text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                <i class="fas fa-print mr-2"></i>
                                Cetak
                            </button>
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-cloak
                                 x-transition
                                 class="absolute bottom-full mb-1 left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                                <button wire:click="printReceiptThermal(<?php echo e($sale->id); ?>)"
                                        @click="open = false"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50 flex items-center gap-2 first:rounded-t-lg transition-colors">
                                    <i class="fas fa-print text-blue-600 w-4"></i>
                                    <span>Thermal</span>
                                </button>
                                <button wire:click="exportReceiptPNG(<?php echo e($sale->id); ?>)"
                                        @click="open = false"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-green-50 flex items-center gap-2 border-t transition-colors">
                                    <i class="fas fa-image text-green-600 w-4"></i>
                                    <span>PNG</span>
                                </button>
                                <button wire:click="exportReceiptPDFThermal(<?php echo e($sale->id); ?>)"
                                        @click="open = false"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-purple-50 flex items-center gap-2 border-t transition-colors">
                                    <i class="fas fa-file-pdf text-purple-600 w-4"></i>
                                    <span>PDF</span>
                                </button>
                                <button wire:click="exportInvoiceA4(<?php echo e($sale->id); ?>)"
                                        @click="open = false"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-orange-50 flex items-center gap-2 border-t last:rounded-b-lg transition-colors">
                                    <i class="fas fa-file-invoice text-orange-600 w-4"></i>
                                    <span>Invoice</span>
                                </button>
                            </div>
                        </div>

                        <button wire:click="confirmDelete(<?php echo e($sale->id); ?>)"
                                class="flex-1 px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
                                <?php if($sale->status === 'CANCELLED'): ?> disabled <?php endif; ?>>
                            <i class="fas fa-trash mr-2"></i>
                            Hapus
                        </button>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-8 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <i class="fas fa-receipt text-6xl mb-4 text-gray-300"></i>
                        <p class="text-xl font-medium">Tidak ada transaksi</p>
                        <p class="text-sm">Belum ada transaksi yang sesuai dengan filter</p>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!-- Pagination -->
        <!--[if BLOCK]><![endif]--><?php if($sales->hasPages()): ?>
            <div class="px-4 py-4 border-t border-gray-200">
                <?php echo e($sales->links()); ?>

            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!-- Detail Modal -->
    <!--[if BLOCK]><![endif]--><?php if($showDetailModal && $selectedSale): ?>
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">Detail Transaksi</h3>
                    <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Transaction Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Transaksi</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">No. Transaksi:</span>
                                <span class="font-medium"><?php echo e($selectedSale->sale_number); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tanggal:</span>
                                <span><?php echo e($selectedSale->created_at->format('d/m/Y H:i:s')); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kasir:</span>
                                <span><?php echo e($selectedSale->cashier->name ?? 'System'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <?php
                                    $detailStatusLabel = match($selectedSale->status) {
                                        'PAID' => 'Lunas',
                                        'PARTIAL' => 'Sebagian',
                                        'UNPAID' => 'Belum dibayar',
                                        'CANCELLED' => 'Dibatalkan',
                                        default => ucfirst(strtolower($selectedSale->status ?? ''))
                                    };
                                    $detailStatusClass = match($selectedSale->status) {
                                        'PAID' => 'bg-green-100 text-green-800',
                                        'PARTIAL' => 'bg-yellow-100 text-yellow-800',
                                        'UNPAID' => 'bg-red-100 text-red-800',
                                        'CANCELLED' => 'bg-red-200 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo e($detailStatusClass); ?>">
                                    <?php echo e($detailStatusLabel); ?>

                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Pelanggan</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nama:</span>
                                <span><?php echo e($selectedSale->customer_name ?: '-'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Telepon:</span>
                                <span><?php echo e($selectedSale->customer_phone ?: '-'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Metode Bayar:</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    <?php if($selectedSale->payment_method === 'cash'): ?> bg-green-100 text-green-800
                                    <?php elseif($selectedSale->payment_method === 'transfer'): ?> bg-blue-100 text-blue-800
                                    <?php elseif($selectedSale->payment_method === 'edc'): ?> bg-purple-100 text-purple-800
                                    <?php elseif($selectedSale->payment_method === 'qr'): ?> bg-orange-100 text-orange-800
                                    <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                    <?php echo e(ucfirst($selectedSale->payment_method)); ?>

                                </span>
                            </div>
                            <!--[if BLOCK]><![endif]--><?php if($selectedSale->payment_notes): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Catatan Bayar:</span>
                                    <span><?php echo e($selectedSale->payment_notes); ?></span>
                                </div>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Item Transaksi</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedSale->saleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-2 text-sm">
                                            <div class="font-medium text-gray-900">
                                                <?php echo e(optional($item->product)->name ?? ($item->custom_item_name ?? 'Unknown Product')); ?>

                                            </div>
                                            <div class="text-gray-500">
                                                <?php echo e(optional($item->product)->sku ?? ($item->custom_item_description ?? '-')); ?>

                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900"><?php echo e(number_format($item->qty)); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-900">Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></td>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                            Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary -->
                <div class="border-t pt-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span>Rp <?php echo e(number_format($selectedSale->subtotal, 0, ',', '.')); ?></span>
                        </div>
                        <!--[if BLOCK]><![endif]--><?php if($selectedSale->discount_total > 0): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Diskon:</span>
                                <span class="text-red-600">-Rp <?php echo e(number_format($selectedSale->discount_total, 0, ',', '.')); ?></span>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <div class="flex justify-between font-semibold text-lg border-t pt-2">
                            <span>Total:</span>
                            <span>Rp <?php echo e(number_format($selectedSale->final_total, 0, ',', '.')); ?></span>
                        </div>
                        <?php
                            $paid = ($selectedSale->cash_amount ?? 0) + ($selectedSale->qr_amount ?? 0) + ($selectedSale->edc_amount ?? 0);
                        ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bayar:</span>
                            <span>Rp <?php echo e(number_format($paid, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Kembalian:</span>
                            <span>Rp <?php echo e(number_format($selectedSale->change_amount, 0, ',', '.')); ?></span>
                        </div>
                    </div>
                </div>

                <!--[if BLOCK]><![endif]--><?php if($selectedSale->notes): ?>
                    <div class="mt-4 p-3 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-600"><strong>Catatan:</strong> <?php echo e($selectedSale->notes); ?></p>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!-- Actions -->
                <div class="mt-6">
                    <!-- Print Options -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-3">
                        <p class="text-sm font-medium text-gray-700 mb-3">
                            <i class="fas fa-print mr-2 text-blue-600"></i>
                            Cetak / Export Struk
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button wire:click="printReceiptThermal(<?php echo e($selectedSale->id); ?>)"
                                    class="flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                                <i class="fas fa-print"></i>
                                <span>Thermal</span>
                            </button>
                            <button wire:click="exportReceiptPNG(<?php echo e($selectedSale->id); ?>)"
                                    class="flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                                <i class="fas fa-image"></i>
                                <span>PNG</span>
                            </button>
                            <button wire:click="exportReceiptPDFThermal(<?php echo e($selectedSale->id); ?>)"
                                    class="flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 transition-colors">
                                <i class="fas fa-file-pdf"></i>
                                <span>PDF</span>
                            </button>
                            <button wire:click="exportInvoiceA4(<?php echo e($selectedSale->id); ?>)"
                                    class="flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700 transition-colors">
                                <i class="fas fa-file-invoice"></i>
                                <span>Invoice</span>
                            </button>
                        </div>
                    </div>

                    <!-- Other Actions -->
                    <div class="flex flex-wrap justify-end gap-2">
                        <button wire:click="closeDetail"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                            <i class="fas fa-times mr-2"></i>
                            Tutup
                        </button>
                        <button wire:click="confirmDelete(<?php echo e($selectedSale->id); ?>)"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                                <?php if($selectedSale->status === 'CANCELLED'): ?> disabled <?php endif; ?>>
                            <i class="fas fa-trash mr-2"></i>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Delete Confirm Modal -->
    <!--[if BLOCK]><![endif]--><?php if($showDeleteConfirmModal): ?>
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]" x-data="{ showConfirm: false }">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 transform transition-all">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
                            <p class="text-sm text-gray-600">Tindakan tidak dapat dibatalkan</p>
                        </div>
                    </div>
                    <button wire:click="cancelDelete" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Warning Message -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-red-600 mt-0.5"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-red-800">Perhatian!</h4>
                            <p class="text-sm text-red-700 mt-1">Transaksi ini akan dibatalkan dan tidak dapat dipulihkan kembali. Stok yang sudah terpotong tidak akan dikembalikan secara otomatis.</p>
                        </div>
                    </div>
                </div>

                <!-- Transaction Info (if available) -->
                <!--[if BLOCK]><![endif]--><?php if($confirmDeleteId): ?>
                    <?php
                        $saleToDelete = \App\Sale::find($confirmDeleteId);
                    ?>
                    <!--[if BLOCK]><![endif]--><?php if($saleToDelete): ?>
                        <div class="bg-gray-50 rounded-lg p-3 mb-4">
                            <div class="text-sm text-gray-600 mb-1">Transaksi yang akan dibatalkan:</div>
                            <div class="font-medium text-gray-900"><?php echo e($saleToDelete->sale_number); ?></div>
                            <div class="text-sm text-gray-600">Total: Rp <?php echo e(number_format($saleToDelete->final_total, 0, ',', '.')); ?></div>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!-- Confirmation Checkbox -->
                <div class="mb-4">
                    <label class="flex items-start">
                        <input type="checkbox" x-model="showConfirm" class="mt-1 rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <span class="ml-2 text-sm text-gray-700">Saya yakin ingin membatalkan transaksi ini dan memahami konsekuensinya</span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDelete"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </button>
                    <button wire:click="deleteSale"
                            :disabled="!showConfirm"
                            :class="showConfirm ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-400 cursor-not-allowed'"
                            class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors">
                        <i class="fas fa-trash mr-2"></i>
                        Batalkan Transaksi
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Hidden Print Receipt (Thermal Style) -->
    <!--[if BLOCK]><![endif]--><?php if($selectedSale): ?>
        <div id="print-receipt-area" class="hidden">
            <div class="receipt-paper">
                <!-- Store Header -->
                <div class="text-center mb-3" style="line-height: 1.4;">
                    <h2 class="text-lg font-bold mb-1"><?php echo e(config('app.name', 'TOKO')); ?></h2>
                    <p class="text-sm mb-1">Struk Pembayaran</p>
                    <p class="text-xs"><?php echo e($selectedSale->created_at->format('d/m/Y H:i:s')); ?></p>
                </div>

                <div class="border-t border-b border-dashed py-2 mb-2">
                    <table class="w-full text-sm" style="line-height: 1.4;">
                        <tr>
                            <td class="pb-0.5">No. Transaksi</td>
                            <td class="text-right pb-0.5"><?php echo e($selectedSale->sale_number); ?></td>
                        </tr>
                        <tr>
                            <td class="pb-0.5">Kasir</td>
                            <td class="text-right pb-0.5"><?php echo e($selectedSale->cashier->name ?? 'System'); ?></td>
                        </tr>
                        <!--[if BLOCK]><![endif]--><?php if($selectedSale->customer_name): ?>
                            <tr>
                                <td>Pelanggan</td>
                                <td class="text-right"><?php echo e($selectedSale->customer_name); ?></td>
                            </tr>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </table>
                </div>

                <!-- Items -->
                <div class="mb-2">
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $selectedSale->saleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-1.5" style="page-break-inside: avoid;">
                            <div class="font-medium" style="margin-bottom: 2px;">
                                <?php echo e(optional($item->product)->name ?? ($item->custom_item_name ?? 'Item')); ?>

                            </div>
                            <div class="flex justify-between text-sm">
                                <span><?php echo e(number_format($item->qty)); ?> x Rp <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></span>
                                <span class="font-medium">Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </div>

                <!-- Summary -->
                <div class="border-t border-dashed pt-2 mt-2">
                    <table class="w-full text-sm" style="line-height: 1.4;">
                        <tr>
                            <td class="pb-0.5">Subtotal</td>
                            <td class="text-right pb-0.5">Rp <?php echo e(number_format($selectedSale->subtotal, 0, ',', '.')); ?></td>
                        </tr>
                        <!--[if BLOCK]><![endif]--><?php if($selectedSale->discount_total > 0): ?>
                            <tr>
                                <td class="pb-0.5">Diskon</td>
                                <td class="text-right text-red-600 pb-0.5">-Rp <?php echo e(number_format($selectedSale->discount_total, 0, ',', '.')); ?></td>
                            </tr>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <tr class="font-bold text-base">
                            <td class="py-1">TOTAL</td>
                            <td class="text-right py-1">Rp <?php echo e(number_format($selectedSale->final_total, 0, ',', '.')); ?></td>
                        </tr>
                        <tr>
                            <td class="pb-0.5">Bayar (<?php echo e(ucfirst($selectedSale->payment_method)); ?>)</td>
                            <td class="text-right pb-0.5">Rp <?php echo e(number_format(($selectedSale->cash_amount ?? 0) + ($selectedSale->qr_amount ?? 0) + ($selectedSale->edc_amount ?? 0), 0, ',', '.')); ?></td>
                        </tr>
                        <tr>
                            <td>Kembalian</td>
                            <td class="text-right">Rp <?php echo e(number_format($selectedSale->change_amount, 0, ',', '.')); ?></td>
                        </tr>
                    </table>
                </div>

                <!--[if BLOCK]><![endif]--><?php if($selectedSale->notes): ?>
                    <div class="text-xs mt-2 border-t border-dashed pt-2" style="line-height: 1.4;">
                        <strong>Catatan:</strong> <?php echo e($selectedSale->notes); ?>

                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <div class="text-center text-xs mt-3" style="line-height: 1.4;">
                    <p class="mb-0.5">Terima Kasih</p>
                    <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!-- Print CSS -->
    <style>
        /* Hide everything except receipt when printing */
        @media print {
            body * {
                visibility: hidden;
            }
            #print-receipt-area,
            #print-receipt-area * {
                visibility: visible;
            }
            #print-receipt-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                display: block !important;
            }

            /* Remove browser header/footer */
            @page {
                margin: 0;
                size: 80mm auto;
            }

            .receipt-paper {
                width: 80mm;
                padding: 10mm 8mm;
                margin: 0 auto;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.4;
                page-break-inside: avoid;
                page-break-before: auto;
                page-break-after: auto;
            }

            .page-break {
                page-break-after: always;
                break-after: page;
            }
        }

        /* Screen styling for receipt */
        .receipt-paper {
            font-family: 'Courier New', monospace;
            max-width: 80mm;
            padding: 12mm 10mm;
            margin: 0 auto;
            background: white;
        }
    </style>

    <!-- External Libraries for Export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Print & Export Receipt Scripts -->
    <script>
        document.addEventListener('livewire:init', () => {
            // Print Receipt - Thermal
            Livewire.on('print-receipt-thermal', () => {
                setTimeout(() => {
                    window.print();
                }, 100);
            });

            // Export to PNG
            Livewire.on('export-receipt-png', () => {
                setTimeout(() => {
                    exportReceiptToImage();
                }, 100);
            });

            // Export to PDF - Thermal
            Livewire.on('export-receipt-pdf-thermal', () => {
                setTimeout(() => {
                    exportReceiptToPDF();
                }, 100);
            });

            // Export Invoice A4
            Livewire.on('export-invoice-a4', () => {
                setTimeout(() => {
                    exportInvoiceA4();
                }, 100);
            });
        });

        // Helper: prepare receipt DOM for accurate capture (fix cropping)
        function prepareReceiptForCapture(receiptElement, targetPixelWidth = 302) {
            const receiptPaper = receiptElement.querySelector('.receipt-paper');
            const prev = {
                display: receiptElement.style.display,
                position: receiptElement.style.position,
                left: receiptElement.style.left,
                width: receiptPaper ? receiptPaper.style.width : undefined,
                padding: receiptPaper ? receiptPaper.style.padding : undefined,
                boxSizing: receiptPaper ? receiptPaper.style.boxSizing : undefined,
                background: receiptPaper ? receiptPaper.style.backgroundColor : undefined,
            };

            // Ensure element is rendered off-screen for capture
            receiptElement.style.display = 'block';
            receiptElement.style.position = 'absolute';
            receiptElement.style.left = '-9999px';

            if (receiptPaper) {
                receiptPaper.style.width = targetPixelWidth + 'px';
                receiptPaper.style.padding = '30px 25px';
                receiptPaper.style.boxSizing = 'border-box';
                receiptPaper.style.backgroundColor = '#ffffff';
            }

            // Return cleanup function
            return () => {
                receiptElement.style.display = prev.display || 'none';
                receiptElement.style.position = prev.position || '';
                receiptElement.style.left = prev.left || '';
                if (receiptPaper) {
                    receiptPaper.style.width = prev.width || '';
                    receiptPaper.style.padding = prev.padding || '';
                    receiptPaper.style.boxSizing = prev.boxSizing || '';
                    receiptPaper.style.backgroundColor = prev.background || '';
                }
            };
        }

        function exportReceiptToImage() {
            const receiptElement = document.getElementById('print-receipt-area');
            if (!receiptElement) {
                alert('Struk tidak ditemukan');
                return;
            }

            const cleanup = prepareReceiptForCapture(receiptElement, 302);

            html2canvas(receiptElement, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: 0,
            }).then(canvas => {
                cleanup();

                // Download as PNG
                const link = document.createElement('a');
                const saleNumber = receiptElement.querySelector('.receipt-paper h2')?.nextElementSibling?.nextElementSibling?.textContent || 'struk';
                link.download = `Struk-${saleNumber.trim()}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            }).catch(error => {
                cleanup();
                console.error('Error exporting to PNG:', error);
                alert('Gagal export ke PNG');
            });
        }

        function exportReceiptToPDF() {
            const receiptElement = document.getElementById('print-receipt-area');
            if (!receiptElement) {
                alert('Struk tidak ditemukan');
                return;
            }

            const cleanup = prepareReceiptForCapture(receiptElement, 302);

            html2canvas(receiptElement, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: 0,
            }).then(canvas => {
                cleanup();

                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;

                // Create PDF with thermal receipt size (80mm width)
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: [80, canvas.height * 80 / canvas.width]
                });

                const imgWidth = 80;
                const imgHeight = canvas.height * imgWidth / canvas.width;

                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);

                const saleNumber = receiptElement.querySelector('.receipt-paper h2')?.nextElementSibling?.nextElementSibling?.textContent || 'struk';
                pdf.save(`Struk-${saleNumber.trim()}.pdf`);
            }).catch(error => {
                cleanup();
                console.error('Error exporting to PDF:', error);
                alert('Gagal export ke PDF');
            });
        }

        function exportInvoiceA4() {
            const receiptElement = document.getElementById('print-receipt-area');
            if (!receiptElement) {
                alert('Struk tidak ditemukan');
                return;
            }

            const cleanup = prepareReceiptForCapture(receiptElement, 794); // A4 width at 96dpi

            html2canvas(receiptElement, {
                scale: 3,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: 0,
                width: 794,
            }).then(canvas => {
                cleanup();

                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;

                // Create A4 PDF
                const pdf = new jsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'a4'
                });

                // A4 dimensions: 210mm x 297mm
                const pageWidth = 210;
                const pageHeight = 297;
                const margin = 10;
                const contentWidth = pageWidth - (margin * 2);

                // Calculate image dimensions to fit A4
                const imgWidth = contentWidth;
                const imgHeight = (canvas.height * contentWidth) / canvas.width;

                // Add image centered on page
                pdf.addImage(imgData, 'PNG', margin, margin, imgWidth, imgHeight);

                const saleNumber = receiptElement.querySelector('.receipt-paper h2')?.nextElementSibling?.nextElementSibling?.textContent || 'invoice';
                pdf.save(`Invoice-${saleNumber.trim()}.pdf`);
            }).catch(error => {
                cleanup();
                console.error('Error exporting Invoice A4:', error);
                alert('Gagal export Invoice A4');
            });
        }
    </script>
</div>
<?php /**PATH C:\laragon\www\laravel_livewire_RBAC_boilerplate\resources\views/livewire/kasir-management.blade.php ENDPATH**/ ?>