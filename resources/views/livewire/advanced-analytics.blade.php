<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
            <i class="fas fa-chart-line mr-3 text-blue-600"></i>
            Advanced Analytics Dashboard
        </h2>
        <p class="text-gray-600 dark:text-gray-400">Analisis mendalam penjualan, profit margin, dan trend bisnis</p>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Period Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fas fa-calendar-alt mr-1"></i>Periode
                </label>
                <select wire:model.live="selectedPeriod" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="7days">7 Hari Terakhir</option>
                    <option value="30days">30 Hari Terakhir</option>
                    <option value="90days">90 Hari Terakhir</option>
                    <option value="1year">1 Tahun Terakhir</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="dateFrom" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="dateTo" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    <i class="fas fa-tags mr-1"></i>Kategori
                </label>
                <select wire:model.live="selectedCategory" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Revenue</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-money-bill-wave text-3xl text-blue-200"></i>
            </div>
        </div>

        <!-- Total Profit -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total Profit</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-chart-line text-3xl text-green-200"></i>
            </div>
        </div>

        <!-- Average Margin -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Avg Margin</p>
                    <p class="text-2xl font-bold">{{ number_format($averageMargin, 1) }}%</p>
                </div>
                <i class="fas fa-percentage text-3xl text-purple-200"></i>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Transactions</p>
                    <p class="text-2xl font-bold">{{ number_format($totalTransactions) }}</p>
                </div>
                <i class="fas fa-shopping-cart text-3xl text-orange-200"></i>
            </div>
        </div>

        <!-- Growth Rate -->
        <div class="bg-gradient-to-r from-{{ $growthRate >= 0 ? 'emerald' : 'red' }}-500 to-{{ $growthRate >= 0 ? 'emerald' : 'red' }}-600 rounded-lg shadow p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-{{ $growthRate >= 0 ? 'emerald' : 'red' }}-100 text-sm">Growth Rate</p>
                    <p class="text-2xl font-bold flex items-center">
                        <i class="fas fa-{{ $growthRate >= 0 ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                        {{ number_format(abs($growthRate), 1) }}%
                    </p>
                </div>
                <i class="fas fa-trending-{{ $growthRate >= 0 ? 'up' : 'down' }} text-3xl text-{{ $growthRate >= 0 ? 'emerald' : 'red' }}-200"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales by Category Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                    Penjualan per Kategori
                </h3>
                <button wire:click="exportAnalytics" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-download"></i>
                </button>
            </div>
            <div class="h-80">
                <canvas id="salesByCategoryChart"></canvas>
            </div>
        </div>

        <!-- Profit Margin Analysis -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                    Analisis Profit Margin
                </h3>
            </div>
            <div class="h-80">
                <canvas id="profitMarginChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 gap-6 mb-6">
        <!-- Trend Analysis Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-chart-line mr-2 text-purple-600"></i>
                    Trend Analysis - Revenue & Transactions
                </h3>
                <div class="flex space-x-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-1"></span>
                        Revenue
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                        Transactions
                    </span>
                </div>
            </div>
            <div class="h-80">
                <canvas id="trendAnalysisChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-trophy mr-2 text-yellow-600"></i>
                Top 10 Produk Terlaris
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Produk</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($topProductsData as $index => $product)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $product->category }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ number_format($product->total_qty) }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Tidak ada data produk</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Category Performance Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-tags mr-2 text-indigo-600"></i>
                Performa per Kategori
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Produk</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Revenue</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($categoryPerformanceData as $category)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $category->category }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($category->items_sold) }} items sold</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $category->product_count }}
                                    @if($category->low_stock_count > 0)
                                        <span class="ml-1 px-1 py-0.5 text-xs bg-red-100 text-red-800 rounded">{{ $category->low_stock_count }} low</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($category->revenue, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ number_format($category->total_stock) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Tidak ada data kategori</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-900 dark:text-white font-medium">Memuat data analytics...</span>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let salesByCategoryChart, profitMarginChart, trendAnalysisChart;

    function initializeCharts() {
        // Sales by Category Chart (Doughnut)
        const salesCtx = document.getElementById('salesByCategoryChart');
        if (salesCtx) {
            if (salesByCategoryChart) {
                salesByCategoryChart.destroy();
            }
            
            const salesData = @json($salesByCategoryData);
            salesByCategoryChart = new Chart(salesCtx, {
                type: 'doughnut',
                data: salesData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return context.label + ': Rp ' + value.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Profit Margin Chart (Bar)
        const profitCtx = document.getElementById('profitMarginChart');
        if (profitCtx) {
            if (profitMarginChart) {
                profitMarginChart.destroy();
            }
            
            const profitData = @json($profitMarginData);
            profitMarginChart = new Chart(profitCtx, {
                type: 'bar',
                data: profitData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Trend Analysis Chart (Line)
        const trendCtx = document.getElementById('trendAnalysisChart');
        if (trendCtx) {
            if (trendAnalysisChart) {
                trendAnalysisChart.destroy();
            }
            
            const trendData = @json($trendAnalysisData);
            trendAnalysisChart = new Chart(trendCtx, {
                type: 'line',
                data: trendData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.datasetIndex === 0) {
                                        label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                    } else {
                                        label += context.parsed.y + ' transaksi';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Initialize charts when component loads
    document.addEventListener('livewire:init', function() {
        initializeCharts();
    });

    // Reinitialize charts when data updates
    Livewire.on('refreshAnalytics', function() {
        setTimeout(() => {
            initializeCharts();
        }, 100);
    });

    // Reinitialize charts after Livewire updates
    document.addEventListener('livewire:updated', function() {
        setTimeout(() => {
            initializeCharts();
        }, 100);
    });
</script>
@endpush