<?php

namespace App\Livewire;

use Livewire\Component;
use App\Product;
use App\Sale;
use App\SaleItem;
use App\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use ArielMejiaDev\LarapexCharts\Facades\LarapexChart;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SalesReportExport;
use App\Exports\StockReportExport;
use Illuminate\Support\Facades\Cache;

class Dashboard extends Component
{
    public $dateFrom;
    public $dateTo;
    public $reportType = 'sales';
    public $refreshInterval = 30; // seconds
    public $autoRefresh = false;
    
    // Enhanced Dashboard Stats
    public $totalProducts = 0;
    public $totalSales = 0;
    public $totalRevenue = 0;
    public $lowStockProducts = 0;
    public $lowStockProductsList = [];
    public $criticalStockProducts = 0;
    public $outOfStockProducts = 0;
    public $averageOrderValue = 0;
    public $salesGrowth = 0;
    public $revenueGrowth = 0;
    public $topSellingCategory = '';
    public $profitMargin = 0;
    
    // Performance Metrics
    public $todayStats = [];
    public $weekStats = [];
    public $monthStats = [];
    public $yearStats = [];
    
    // Chart Data
    public $salesData = [];
    public $stockData = [];
    public $topProductsData = [];
    public $recentSales = [];
    public $performanceAlerts = [];
    
    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->loadDashboardData();
        $this->generatePerformanceAlerts();
    }
    
    public function updatedDateFrom()
    {
        $this->loadDashboardData();
    }
    
    public function updatedDateTo()
    {
        $this->loadDashboardData();
    }
    
    public function refreshData()
    {
        // Clear cache for fresh data
        Cache::forget('dashboard_stats_' . auth()->id());
        Cache::forget('dashboard_charts_' . auth()->id());
        
        $this->loadDashboardData();
        $this->generatePerformanceAlerts();
        session()->flash('success', 'Data dashboard berhasil diperbarui!');
        
        // Dispatch browser event for real-time updates
        $this->dispatch('dashboard-refreshed');
    }
    
    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        $message = $this->autoRefresh ? 'Auto refresh diaktifkan' : 'Auto refresh dinonaktifkan';
        session()->flash('success', $message);
    }
    
    public function loadDashboardData()
    {
        $this->loadEnhancedStats();
        $this->loadPerformanceMetrics();
        $this->loadChartData();
    }
    
    private function loadEnhancedStats()
    {
        // Use caching for better performance
        $cacheKey = 'dashboard_stats_' . auth()->id() . '_' . $this->dateFrom . '_' . $this->dateTo;
        
        $stats = Cache::remember($cacheKey, 300, function () { // 5 minutes cache
            $dateRange = [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'];
            $previousPeriod = $this->getPreviousPeriodRange();
            
            return [
                'totalProducts' => Product::where('status', 'active')->whereNull('deleted_at')->count(),
                'totalSales' => Sale::whereBetween('created_at', $dateRange)->count(),
                'totalRevenue' => Sale::whereBetween('created_at', $dateRange)->sum('final_total'),
                'lowStockProducts' => Product::where('status', 'active')->whereNull('deleted_at')->where('current_stock', '<', 10)->count(),
                'lowStockProductsList' => Product::where('status', 'active')->whereNull('deleted_at')->where('current_stock', '<', 10)->get(),
                'criticalStockProducts' => Product::where('status', 'active')->whereNull('deleted_at')->where('current_stock', '<', 5)->count(),
                'outOfStockProducts' => Product::where('status', 'active')->whereNull('deleted_at')->where('current_stock', '<=', 0)->count(),
                'averageOrderValue' => Sale::whereBetween('created_at', $dateRange)->avg('final_total') ?? 0,
                'previousSales' => Sale::whereBetween('created_at', $previousPeriod)->count(),
                'previousRevenue' => Sale::whereBetween('created_at', $previousPeriod)->sum('final_total'),
                'topCategory' => $this->getTopSellingCategory($dateRange),
                'profitMargin' => $this->calculateProfitMargin($dateRange)
            ];
        });
        
        // Assign stats to properties
        $this->totalProducts = $stats['totalProducts'];
        $this->totalSales = $stats['totalSales'];
        $this->totalRevenue = $stats['totalRevenue'];
        $this->lowStockProducts = $stats['lowStockProducts'];
        $this->lowStockProductsList = $stats['lowStockProductsList'];
        $this->criticalStockProducts = $stats['criticalStockProducts'];
        $this->outOfStockProducts = $stats['outOfStockProducts'];
        $this->averageOrderValue = $stats['averageOrderValue'];
        $this->topSellingCategory = $stats['topCategory'];
        $this->profitMargin = $stats['profitMargin'];
        
        // Calculate growth percentages
        $this->salesGrowth = $stats['previousSales'] > 0 
            ? (($stats['totalSales'] - $stats['previousSales']) / $stats['previousSales']) * 100 
            : 0;
            
        $this->revenueGrowth = $stats['previousRevenue'] > 0 
            ? (($stats['totalRevenue'] - $stats['previousRevenue']) / $stats['previousRevenue']) * 100 
            : 0;
    }
    
    private function loadPerformanceMetrics()
    {
        $now = Carbon::now();
        
        // Today's performance
        $this->todayStats = [
            'sales' => Sale::whereDate('created_at', $now->toDateString())->count(),
            'revenue' => Sale::whereDate('created_at', $now->toDateString())->sum('final_total'),
            'transactions' => Sale::whereDate('created_at', $now->toDateString())->count(),
            'avg_order' => Sale::whereDate('created_at', $now->toDateString())->avg('final_total') ?? 0
        ];
        
        // This week's performance
        $weekStart = $now->copy()->startOfWeek();
        $this->weekStats = [
            'sales' => Sale::whereBetween('created_at', [$weekStart, $now])->count(),
            'revenue' => Sale::whereBetween('created_at', [$weekStart, $now])->sum('final_total'),
            'avg_daily' => Sale::whereBetween('created_at', [$weekStart, $now])->sum('final_total') / 7
        ];
        
        // This month's performance
        $monthStart = $now->copy()->startOfMonth();
        $this->monthStats = [
            'sales' => Sale::whereBetween('created_at', [$monthStart, $now])->count(),
            'revenue' => Sale::whereBetween('created_at', [$monthStart, $now])->sum('final_total'),
            'target_progress' => $this->calculateMonthlyTargetProgress()
        ];
        
        // This year's performance
        $yearStart = $now->copy()->startOfYear();
        $this->yearStats = [
            'sales' => Sale::whereBetween('created_at', [$yearStart, $now])->count(),
            'revenue' => Sale::whereBetween('created_at', [$yearStart, $now])->sum('final_total'),
            'monthly_avg' => Sale::whereBetween('created_at', [$yearStart, $now])->sum('final_total') / $now->month
        ];
    }
    
    private function getPreviousPeriodRange()
    {
        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);
        $diff = $from->diffInDays($to);
        
        $previousFrom = $from->copy()->subDays($diff + 1);
        $previousTo = $from->copy()->subDay();
        
        return [$previousFrom->format('Y-m-d H:i:s'), $previousTo->format('Y-m-d H:i:s')];
    }
    
    private function getTopSellingCategory($dateRange)
    {
        $topCategory = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', $dateRange)
            ->selectRaw('products.category, SUM(sale_items.qty) as total_qty')
            ->groupBy('products.category')
            ->orderByDesc('total_qty')
            ->first();
            
        return $topCategory ? $topCategory->category : 'N/A';
    }
    
    private function calculateProfitMargin($dateRange)
    {
        $salesData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', $dateRange)
            ->selectRaw('SUM(sale_items.unit_price * sale_items.qty) as total_revenue, SUM(products.base_cost * sale_items.qty) as total_cost')
            ->first();
            
        if ($salesData && $salesData->total_revenue > 0) {
            return (($salesData->total_revenue - $salesData->total_cost) / $salesData->total_revenue) * 100;
        }
        
        return 0;
    }
    
    private function calculateMonthlyTargetProgress()
    {
        // Assuming a monthly target of 10,000,000 (10 million)
        $monthlyTarget = 10000000;
        $currentRevenue = $this->monthStats['revenue'] ?? 0;
        
        return $monthlyTarget > 0 ? ($currentRevenue / $monthlyTarget) * 100 : 0;
    }
    
    private function generatePerformanceAlerts()
    {
        $this->performanceAlerts = [];
        
        // Critical stock alert
        if ($this->criticalStockProducts > 0) {
            $this->performanceAlerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-exclamation-triangle',
                'title' => 'Stok Kritis',
                'message' => "{$this->criticalStockProducts} produk memiliki stok sangat rendah (< 5 unit)",
                'action' => 'Lihat Produk',
                'url' => '/products?filter=critical_stock'
            ];
        }
        
        // Out of stock alert
        if ($this->outOfStockProducts > 0) {
            $this->performanceAlerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-box-open',
                'title' => 'Stok Habis',
                'message' => "{$this->outOfStockProducts} produk kehabisan stok",
                'action' => 'Restok Sekarang',
                'url' => '/products?filter=out_of_stock'
            ];
        }
        
        // Sales growth alert
        if ($this->salesGrowth < -10) {
            $this->performanceAlerts[] = [
                'type' => 'info',
                'icon' => 'fas fa-chart-line',
                'title' => 'Penjualan Menurun',
                'message' => "Penjualan turun {$this->salesGrowth}% dibanding periode sebelumnya",
                'action' => 'Analisis Trend',
                'url' => '/reports?type=sales_analysis'
            ];
        } elseif ($this->salesGrowth > 20) {
            $this->performanceAlerts[] = [
                'type' => 'success',
                'icon' => 'fas fa-trophy',
                'title' => 'Penjualan Meningkat',
                'message' => "Penjualan naik {$this->salesGrowth}% dibanding periode sebelumnya",
                'action' => 'Lihat Detail',
                'url' => '/reports?type=growth_analysis'
            ];
        }
        
        // Profit margin alert
        if ($this->profitMargin < 15) {
            $this->performanceAlerts[] = [
                'type' => 'warning',
                'icon' => 'fas fa-percentage',
                'title' => 'Margin Keuntungan Rendah',
                'message' => "Margin keuntungan hanya {$this->profitMargin}%",
                'action' => 'Optimasi Harga',
                'url' => '/products?action=optimize_pricing'
            ];
        }
    }
    
    private function loadChartData()
    {
        // Sales Chart Data (Daily sales in date range)
        $salesData = Sale::whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                        ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(final_total) as revenue')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get();
        
        // Store sales data for chart creation in render method
        $this->salesData = $salesData;
        
        // Create LarapexChart for revenue (optional - can be combined with sales)
        // $this->revenueChart = LarapexChart::barChart()
        //     ->setTitle('Pendapatan Harian')
        //     ->addData('Pendapatan', $salesData->pluck('revenue')->toArray())
        //     ->setXAxis($salesData->pluck('date')->map(function($date) {
        //         return Carbon::parse($date)->format('d/m');
        //     })->toArray())
        //     ->setColors(['#10B981']);
        
        // Top Products Data
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                              ->join('products', 'sale_items.product_id', '=', 'products.id')
                              ->whereBetween('sales.created_at', [$this->dateFrom, $this->dateTo])
                              ->selectRaw('products.name, SUM(sale_items.qty) as total_qty, SUM(sale_items.qty * sale_items.unit_price) as total_revenue')
                              ->groupBy('products.id', 'products.name')
                              ->orderByDesc('total_qty')
                              ->limit(5)
                              ->get();
        
        // Top products data (keep for table display)
        $this->topProductsData = $topProducts;
        
        // Stock Movement Data (In vs Out)
        $stockIn = StockMovement::where('type', 'IN')
                               ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
                               ->sum('qty');
        
        $stockOut = StockMovement::where('type', 'OUT')
                                ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
                                ->sum('qty');
        
        // Store stock data for chart creation in render method
        $this->stockData = [
            'stockIn' => $stockIn,
            'stockOut' => $stockOut
        ];
        
        // Load recent sales
        $this->recentSales = Sale::with('saleItems')
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    public function exportExcel()
    {
        try {
            if ($this->reportType === 'sales') {
                return Excel::download(
                    new SalesReportExport($this->dateFrom, $this->dateTo),
                    'laporan-penjualan-' . $this->dateFrom . '-to-' . $this->dateTo . '.xlsx'
                );
            } else {
                return Excel::download(
                    new StockReportExport($this->dateFrom, $this->dateTo),
                    'laporan-stok-' . $this->dateFrom . '-to-' . $this->dateTo . '.xlsx'
                );
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }
    
    public function exportPdf()
    {
        try {
            if ($this->reportType === 'sales') {
                $sales = Sale::with(['items.product', 'cashier'])
                           ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                           ->orderBy('created_at', 'desc')
                           ->get();
                
                $pdf = Pdf::loadView('reports.sales-pdf', [
                    'sales' => $sales,
                    'dateFrom' => $this->dateFrom,
                    'dateTo' => $this->dateTo,
                    'totalRevenue' => $this->totalRevenue,
                    'totalSales' => $this->totalSales
                ]);
                
                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    'laporan-penjualan-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf'
                );
            } else {
                $products = Product::with(['stockMovements' => function($query) {
                    $query->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
                }])->where('is_active', true)->get();
                
                $pdf = Pdf::loadView('reports.stock-pdf', [
                    'products' => $products,
                    'dateFrom' => $this->dateFrom,
                    'dateTo' => $this->dateTo
                ]);
                
                return response()->streamDownload(
                    fn () => print($pdf->output()),
                    'laporan-stok-' . $this->dateFrom . '-to-' . $this->dateTo . '.pdf'
                );
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
    
    public function exportSalesChart()
    {
        session()->flash('success', 'Export sales chart berhasil!');
    }
    
    public function exportStockChart()
    {
        session()->flash('success', 'Export stock chart berhasil!');
    }
    
    public function exportTopProducts()
    {
        try {
            // Get top products data
            $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                              ->join('products', 'sale_items.product_id', '=', 'products.id')
                              ->whereBetween('sales.created_at', [Carbon::now()->subDays(7), Carbon::now()])
                              ->selectRaw('products.name, products.sku, SUM(sale_items.qty) as total_qty, SUM(sale_items.qty * sale_items.unit_price) as total_revenue')
                              ->groupBy('products.id', 'products.name', 'products.sku')
                              ->orderByDesc('total_qty')
                              ->limit(10)
                              ->get();
            
            $data = [];
            $data[] = ['Nama Produk', 'SKU', 'Qty Terjual', 'Total Revenue'];
            
            foreach ($topProducts as $product) {
                $data[] = [
                    $product->name,
                    $product->sku,
                    $product->total_qty,
                    'Rp ' . number_format($product->total_revenue, 0, ',', '.')
                ];
            }
            
            return Excel::download(
                new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                    private $data;
                    public function __construct($data) { $this->data = $data; }
                    public function array(): array { return $this->data; }
                },
                'top-products-' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal export top products: ' . $e->getMessage());
        }
    }
    
    public function exportRecentSales()
    {
        try {
            // Get recent sales data
            $recentSales = Sale::with(['saleItems.product'])
                              ->orderByDesc('created_at')
                              ->limit(50)
                              ->get();
            
            $pdf = Pdf::loadView('reports.recent-sales-pdf', [
                'sales' => $recentSales,
                'generatedAt' => now()->format('d/m/Y H:i:s')
            ]);
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                'recent-sales-' . date('Y-m-d') . '.pdf'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal export recent sales: ' . $e->getMessage());
        }
    }
    
    public function exportDashboardPDF()
    {
        return $this->exportPdf();
    }
    
    public function exportDashboardExcel()
    {
        return $this->exportExcel();
    }
    
    public function exportSalesReport()
    {
        $this->reportType = 'sales';
        return $this->exportExcel();
    }
    
    public function exportStockReport()
    {
        $this->reportType = 'stock';
        return $this->exportExcel();
    }
    
    public function exportCategoryChart()
    {
        session()->flash('success', 'Export category chart berhasil!');
    }
    
    public function render()
    {
        // Get cached chart data
        $chartCacheKey = 'dashboard_charts_' . auth()->id() . '_' . $this->dateFrom . '_' . $this->dateTo;
        $chartData = Cache::remember($chartCacheKey, 300, function () {
            return $this->generateChartData();
        });
        
        return view('livewire.dashboard', array_merge([
            'totalProducts' => $this->totalProducts,
            'totalSales' => $this->totalSales,
            'totalRevenue' => $this->totalRevenue,
            'lowStockProducts' => $this->lowStockProducts,
            'criticalStockProducts' => $this->criticalStockProducts,
            'outOfStockProducts' => $this->outOfStockProducts,
            'averageOrderValue' => $this->averageOrderValue,
            'salesGrowth' => $this->salesGrowth,
            'revenueGrowth' => $this->revenueGrowth,
            'topSellingCategory' => $this->topSellingCategory,
            'profitMargin' => $this->profitMargin,
            'todayStats' => $this->todayStats,
            'weekStats' => $this->weekStats,
            'monthStats' => $this->monthStats,
            'yearStats' => $this->yearStats,
            'performanceAlerts' => $this->performanceAlerts,
            'autoRefresh' => $this->autoRefresh,
            'refreshInterval' => $this->refreshInterval
        ], $chartData));
    }
    
    private function generateChartData()
    {
        $dateRange = [Carbon::now()->subDays(7), Carbon::now()];
        
        // Get top products data
        $topProductsData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                              ->join('products', 'sale_items.product_id', '=', 'products.id')
                              ->whereBetween('sales.created_at', $dateRange)
                              ->selectRaw('products.id, products.name, products.sku, SUM(sale_items.qty) as total_qty, SUM(sale_items.unit_price * sale_items.qty) as total_revenue')
                              ->groupBy('products.id', 'products.name', 'products.sku')
                              ->orderByDesc('total_qty')
                              ->limit(5)
                              ->get();
        
        // Get recent sales
        $recentSales = Sale::with(['saleItems.product', 'cashier'])
                          ->orderByDesc('created_at')
                          ->limit(5)
                          ->get();
        
        // Create enhanced sales trend chart
        $dailySalesChart = null;
        if (!empty($this->salesData) && $this->salesData->count() > 0) {
            $dailySalesChart = LarapexChart::lineChart()
                ->setTitle('Trend Penjualan & Pendapatan')
                ->setSubtitle('Analisis performa harian dalam periode yang dipilih')
                ->addData('Transaksi', $this->salesData->pluck('count')->toArray())
                ->addData('Pendapatan (Rb)', $this->salesData->pluck('revenue')->map(function($revenue) {
                    return round($revenue / 1000, 1);
                })->toArray())
                ->setXAxis($this->salesData->pluck('date')->map(function($date) {
                    return Carbon::parse($date)->format('d/m');
                })->toArray())
                ->setColors(['#3B82F6', '#10B981'])
                ->setHeight(400)
                ->setGrid(true)
                ->setMarkers(['#3B82F6', '#10B981'], 6, 0)
                ->setDataLabels(true);
        }
        
        // Create enhanced stock movement chart
        $stockMovementChart = null;
        if (!empty($this->stockData) && ($this->stockData['stockIn'] > 0 || $this->stockData['stockOut'] > 0)) {
            $stockMovementChart = LarapexChart::donutChart()
                ->setTitle('Pergerakan Stok')
                ->setSubtitle('Analisis stok masuk vs keluar')
                ->addData([$this->stockData['stockIn'], abs($this->stockData['stockOut'])])
                ->setLabels(['Stok Masuk', 'Stok Keluar'])
                ->setColors(['#10B981', '#EF4444'])
                ->setHeight(350)
                ->setDataLabels(true);
        }
        
        // Create top products performance chart
        $topProductsChart = null;
        if ($topProductsData->count() > 0) {
            $topProductsChart = LarapexChart::barChart()
                ->setTitle('Top 5 Produk Terlaris')
                ->setSubtitle('Berdasarkan volume penjualan 7 hari terakhir')
                ->addData('Qty Terjual', $topProductsData->pluck('total_qty')->toArray())
                ->setXAxis($topProductsData->pluck('name')->map(function($name) {
                    return strlen($name) > 15 ? substr($name, 0, 15) . '...' : $name;
                })->toArray())
                ->setColors(['#8B5CF6'])
                ->setHeight(350)
                ->setGrid(true, true)
                ->setDataLabels(true);
        }
        
        // Create category performance chart
        $categoryChart = null;
        $categoryData = Product::selectRaw('category, COUNT(*) as product_count, SUM(current_stock) as total_stock')
                             ->where('status', 'active')
                             ->whereNull('deleted_at')
                             ->groupBy('category')
                             ->get();
        
        if ($categoryData->count() > 0) {
            $categoryChart = LarapexChart::pieChart()
                ->setTitle('Distribusi Produk per Kategori')
                ->setSubtitle('Berdasarkan jumlah produk aktif')
                ->addData($categoryData->pluck('product_count')->toArray())
                ->setLabels($categoryData->pluck('category')->toArray())
                ->setColors(['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'])
                ->setHeight(350)
                ->setDataLabels(true);
        }
        
        // Create hourly sales pattern chart
        $hourlySalesChart = null;
        $hourlySales = Sale::whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, SUM(final_total) as revenue')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
            
        if ($hourlySales->count() > 0) {
            $hourlySalesChart = LarapexChart::areaChart()
                ->setTitle('Pola Penjualan per Jam')
                ->setSubtitle('Distribusi transaksi berdasarkan jam dalam sehari')
                ->addData('Transaksi', $hourlySales->pluck('count')->toArray())
                ->setXAxis($hourlySales->pluck('hour')->map(function($hour) {
                    return $hour . ':00';
                })->toArray())
                ->setColors(['#06B6D4'])
                ->setHeight(300)
                ->setGrid(true);
        }
        
        return [
            'topProductsData' => $topProductsData,
            'recentSales' => $recentSales,
            'dailySalesChart' => $dailySalesChart,
            'stockMovementChart' => $stockMovementChart,
            'topProductsChart' => $topProductsChart,
            'categoryChart' => $categoryChart,
            'hourlySalesChart' => $hourlySalesChart
        ];
    }
}