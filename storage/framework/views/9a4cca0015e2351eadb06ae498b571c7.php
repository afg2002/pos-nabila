<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Flash Messages -->
    <!--[if BLOCK]><![endif]--><?php if(session()->has('success')): ?>
        <div class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <?php if(session()->has('error')): ?>
        <div class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h2>
            <p class="text-gray-600 dark:text-gray-400">Overview sistem POS dan inventory</p>
        </div>

        <!-- Date Filter -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="dateFrom" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="dateTo" class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <button wire:click="refreshData" class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Products -->
            <div class="stats-card bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Produk</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($totalProducts)); ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Sales -->
            <div class="stats-card bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Penjualan</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($totalSales, 0, ',', '.')); ?></p>
                    </div>
                </div>
            </div>

            <!-- Low Stock Products -->
            <div class="stats-card bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Stok Menipis</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($lowStockProducts); ?></p>
                    </div>
                </div>
            </div>

            <!-- Today's Transactions -->
            <div class="stats-card bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Transaksi Hari Ini</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($todayStats['transactions']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ecer/Grosir Breakdown Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Ecer Sales -->
            <div class="stats-card bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Penjualan Ecer</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($totalEcer, 0, ',', '.')); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(number_format($ecerCount)); ?> transaksi</p>
                    </div>
                </div>
            </div>

            <!-- Total Grosir Sales -->
            <div class="stats-card bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-indigo-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Penjualan Grosir</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($totalGrosir, 0, ',', '.')); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(number_format($grosirCount)); ?> transaksi</p>
                    </div>
                </div>
            </div>

            <!-- Gross Profit -->
            <div class="stats-card bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-emerald-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Gross Profit</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($grossProfit, 0, ',', '.')); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(number_format($grossProfitMargin, 2)); ?>% margin</p>
                    </div>
                </div>
            </div>

            <!-- Revenue Growth -->
            <div class="stats-card bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-teal-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pertumbuhan Pendapatan</p>
                        <p class="text-2xl font-bold <?php echo e($revenueGrowth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'); ?>">
                            <?php echo e($revenueGrowth >= 0 ? '+' : ''); ?><?php echo e(number_format($revenueGrowth, 1)); ?>%
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">vs periode sebelumnya</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Sales & Revenue Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Penjualan & Pendapatan</h3>
                    <div class="flex space-x-2">
                        <button wire:click="exportSalesChart" class="text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition-colors">
                            <i class="fas fa-download mr-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="h-64 sm:h-80">
                    <!--[if BLOCK]><![endif]--><?php if(isset($dailySalesChart)): ?>
                        <?php echo $dailySalesChart->container(); ?>

                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <i class="fas fa-chart-line text-4xl mb-2"></i>
                            <p>Belum ada data untuk ditampilkan</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <!-- Stock Movement Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pergerakan Stok</h3>
                    <div class="flex space-x-2">
                        <button wire:click="exportStockChart" class="text-sm bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition-colors">
                            <svg class="w-4 h-4 mr-1 inline" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>Export
                        </button>
                    </div>
                </div>
                <div class="h-64 sm:h-80">
                    <!--[if BLOCK]><![endif]--><?php if(isset($stockMovementChart)): ?>
                        <?php echo $stockMovementChart->container(); ?>

                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <svg class="w-16 h-16 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                            </svg>
                            <p>Belum ada data untuk ditampilkan</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>

        <!-- Additional Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Top Products Bar Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Produk Terlaris</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">7 hari terakhir</p>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="exportTopProducts" class="text-sm bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded transition-colors">
                            <i class="fas fa-chart-bar mr-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="h-64 sm:h-80">
                    <!--[if BLOCK]><![endif]--><?php if(isset($topProductsChart)): ?>
                        <?php echo $topProductsChart->container(); ?>

                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <i class="fas fa-chart-bar text-4xl mb-2"></i>
                            <p>Belum ada data penjualan</p>
                            <p class="text-xs mt-1">Data akan muncul setelah ada transaksi</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <!-- Incoming Goods Agenda Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Incoming Goods</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Agenda barang masuk yang dijadwalkan</p>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="exportIncomingGoods" class="text-sm bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded transition-colors">
                            <i class="fas fa-truck mr-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="h-64 sm:h-80">
                    <!--[if BLOCK]><![endif]--><?php if(isset($incomingGoodsChart)): ?>
                        <?php echo $incomingGoodsChart->container(); ?>

                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <i class="fas fa-truck text-4xl mb-2"></i>
                            <p>Belum ada agenda barang masuk</p>
                            <p class="text-xs mt-1">Data akan muncul setelah ada jadwal barang masuk</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <!-- Purchase Order Status Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Purchase Order</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Status pembayaran purchase order</p>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="exportPurchaseOrders" class="text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition-colors">
                            <i class="fas fa-file-invoice mr-1"></i>Export
                        </button>
                    </div>
                </div>
                <div class="h-64 sm:h-80">
                    <!--[if BLOCK]><![endif]--><?php if(isset($purchaseOrderChart)): ?>
                        <?php echo $purchaseOrderChart->container(); ?>

                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <i class="fas fa-file-invoice text-4xl mb-2"></i>
                            <p>Belum ada purchase order</p>
                            <p class="text-xs mt-1">Data akan muncul setelah ada PO yang dibuat</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Trend Omset Bulanan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Perbandingan omset ecer dan grosir 12 bulan terakhir</p>
                </div>
                <div class="flex space-x-2">
                    <button wire:click="exportMonthlyTrend" class="text-sm bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded transition-colors">
                        <i class="fas fa-chart-line mr-1"></i>Export
                    </button>
                </div>
            </div>
            <div class="h-96">
                <!--[if BLOCK]><![endif]--><?php if(isset($monthlyTrendChart)): ?>
                    <?php echo $monthlyTrendChart->container(); ?>

                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                        <i class="fas fa-chart-line text-4xl mb-2"></i>
                        <p>Belum ada data trend bulanan</p>
                        <p class="text-xs mt-1">Data akan muncul setelah ada transaksi penjualan</p>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!-- Hourly Sales Pattern Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pola Penjualan per Jam</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Distribusi transaksi berdasarkan jam</p>
                </div>
                <div class="flex space-x-2">
                    <button wire:click="exportHourlySales" class="text-sm bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded transition-colors">
                        <i class="fas fa-clock mr-1"></i>Export
                    </button>
                </div>
            </div>
            <div class="h-64 sm:h-80">
                <!--[if BLOCK]><![endif]--><?php if(isset($hourlySalesChart)): ?>
                    <?php echo $hourlySalesChart->container(); ?>

                <?php else: ?>
                    <div class="flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400">
                        <i class="fas fa-clock text-4xl mb-2"></i>
                        <p>Belum ada data penjualan per jam</p>
                        <p class="text-xs mt-1">Data akan muncul setelah ada transaksi</p>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!-- Recent Sales Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Penjualan Terbaru</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">5 transaksi terakhir dalam periode yang dipilih</p>
                </div>
                <button wire:click="exportRecentSales" class="text-sm bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition-colors">
                    <i class="fas fa-receipt mr-1"></i>Export
                </button>
            </div>
            <div class="space-y-3">
                <!--[if BLOCK]><![endif]--><?php if($recentSales && $recentSales->count() > 0): ?>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recentSales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div wire:key="recent-sale-<?php echo e($sale->id); ?>" class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border-l-4 border-green-500">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-shopping-cart text-green-600 dark:text-green-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">#<?php echo e($sale->sale_number); ?></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <?php echo e($sale->saleItems->count()); ?> item<?php echo e($sale->saleItems->count() > 1 ? 's' : ''); ?> • 
                                        <?php echo e($sale->cashier->name ?? 'Unknown'); ?>

                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">
                                        <?php echo e($sale->created_at->format('d/m/Y H:i')); ?>

                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-lg text-gray-900 dark:text-white">
                                    Rp <?php echo e(number_format($sale->final_total, 0, ',', '.')); ?>

                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <!--[if BLOCK]><![endif]--><?php if($sale->discount > 0): ?>
                                        <span class="text-red-500">-Rp <?php echo e(number_format($sale->discount, 0, ',', '.')); ?></span>
                                    <?php else: ?>
                                        <span class="text-green-600">Tanpa diskon</span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    <?php if($sale->status === 'completed'): ?> bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    <?php elseif($sale->status === 'pending'): ?> bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    <?php else: ?> bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                                    <?php endif; ?>">
                                    <?php echo e(ucfirst($sale->status)); ?>

                                </span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-8 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-receipt text-4xl mb-3"></i>
                        <p class="text-lg font-medium">Belum ada penjualan</p>
                        <p class="text-sm">Penjualan akan muncul setelah ada transaksi dalam periode yang dipilih</p>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Top Products -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Produk Terlaris</h3>
                    <button wire:click="exportTopProducts" class="text-sm bg-indigo-500 hover:bg-indigo-600 text-white px-2 sm:px-3 py-1 rounded transition-colors">
                        <svg class="w-4 h-4 mr-1 inline" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg><span class="hidden sm:inline">Export Excel</span><span class="sm:hidden">Excel</span>
                    </button>
                </div>
                <div class="space-y-4">
                    <!--[if BLOCK]><![endif]--><?php if($topProductsData->count() > 0): ?>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $topProductsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div wire:key="top-product-<?php echo e($product->id ?? $loop->index); ?>" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-box text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white"><?php echo e($product->name); ?></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">SKU: <?php echo e($product->sku ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo e($product->total_qty ?? 0); ?> terjual</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Rp <?php echo e(number_format($product->total_revenue ?? 0, 0, ',', '.')); ?></p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="w-16 h-16 mb-2 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM9 9a1 1 0 012 0v6a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                            </svg>
                            <p>Belum ada data penjualan</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Penjualan Terbaru</h3>
                    <button wire:click="exportRecentSales" class="text-sm bg-green-500 hover:bg-green-600 text-white px-2 sm:px-3 py-1 rounded transition-colors">
                        <svg class="w-4 h-4 mr-1 inline" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg><span class="hidden sm:inline">Export PDF</span><span class="sm:hidden">PDF</span>
                    </button>
                </div>
                <div class="space-y-4">
                    <!--[if BLOCK]><![endif]--><?php if($recentSales->count() > 0): ?>
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recentSales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div wire:key="recent-sale-<?php echo e($sale->id); ?>" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">#<?php echo e($sale->id); ?></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($sale->created_at->format('d/m/Y H:i')); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($sale->final_total, 0, ',', '.')); ?></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($sale->saleItems->count()); ?> item</p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="w-16 h-16 mb-2 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                            </svg>
                            <p>Belum ada transaksi</p>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        </div>

        <!-- Export Buttons -->
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Export Laporan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button wire:click="exportDashboardPDF" class="bg-red-500 hover:bg-red-600 text-white px-3 sm:px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-1 sm:mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg><span class="hidden sm:inline">Export Dashboard PDF</span><span class="sm:hidden">PDF</span>
                </button>
                <button wire:click="exportDashboardExcel" class="bg-green-500 hover:bg-green-600 text-white px-3 sm:px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-1 sm:mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg><span class="hidden sm:inline">Export Dashboard Excel</span><span class="sm:hidden">Excel</span>
                </button>
                <button wire:click="exportSalesReport" class="bg-blue-500 hover:bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-1 sm:mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3m-6-3v9a1 1 0 001 1h4a1 1 0 001-1v-9M5 5h10a1 1 0 011 1v12a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"></path>
                    </svg><span class="hidden sm:inline">Export Sales Report</span><span class="sm:hidden">Sales</span>
                </button>
                <button wire:click="exportStockReport" class="bg-purple-500 hover:bg-purple-600 text-white px-3 sm:px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-1 sm:mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                    </svg><span class="hidden sm:inline">Export Stock Report</span><span class="sm:hidden">Stock</span>
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<?php $__env->startPush('scripts'); ?>
<!-- Chart Scripts -->
<!--[if BLOCK]><![endif]--><?php if(isset($dailySalesChart)): ?>
    <?php echo $dailySalesChart->script(); ?>

<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<!--[if BLOCK]><![endif]--><?php if(isset($stockMovementChart)): ?>
    <?php echo $stockMovementChart->script(); ?>

<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<!--[if BLOCK]><![endif]--><?php if(isset($topProductsChart)): ?>
    <?php echo $topProductsChart->script(); ?>

<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<!--[if BLOCK]><![endif]--><?php if(isset($incomingGoodsChart)): ?>
    <?php echo $incomingGoodsChart->script(); ?>

<?php endif; ?><!--[if ENDBLOCK]><![endif]-->


<!--[if BLOCK]><![endif]--><?php if(isset($hourlySalesChart)): ?>
    <?php echo $hourlySalesChart->script(); ?>

<?php endif; ?><!--[if ENDBLOCK]><![endif]-->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto refresh every 5 minutes
        setInterval(function() {
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('refreshData');
        }, 300000);
        
        // Export button loading states
        document.querySelectorAll('button[wire\\:click^="export"]').forEach(button => {
            button.addEventListener('click', function() {
                // Add loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
                this.disabled = true;
                
                // Remove loading state after 3 seconds
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            });
        });
        
        // Statistics cards hover effect
        document.querySelectorAll('.stats-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
    
    // Livewire hooks
    document.addEventListener('livewire:initialized', function () {
        // Show success message for exports
        Livewire.on('exportSuccess', (event) => {
            // You can add toast notification here
            console.log('Export successful:', event.detail);
        });
    });
</script>
<?php $__env->stopPush(); ?><?php /**PATH C:\laragon\www\pos-nabila\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>