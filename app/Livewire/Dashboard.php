<?php

namespace App\Livewire;

use App\Product;
use App\Sale;
use App\SaleItem;
use App\StockMovement;
use App\Models\IncomingGoodsAgenda;
use App\PurchaseOrder;
use App\PurchaseOrderItem;
use App\CashLedger;
use App\Exports\SalesReportExport;
use App\Exports\StockReportExport;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends Component
{
    public $dateFrom;
    public $dateTo;
    public $reportType = 'sales';
    public $autoRefresh = false;
    public $refreshInterval = 30;

    // Basic Stats
    public $totalProducts = 0;
    public $totalSales = 0;
    public $totalRevenue = 0;
    public $lowStockProducts = 0;
    public $criticalStockProducts = 0;
    public $outOfStockProducts = 0;
    public $averageOrderValue = 0;
    public $salesGrowth = 0;
    public $revenueGrowth = 0;
    public $topSellingCategory = '';
    public $profitMargin = 0;

    // Enhanced Stats for Ecer/Grosir
    public $totalEcer = 0;
    public $totalGrosir = 0;
    public $ecerCount = 0;
    public $grosirCount = 0;
    public $grossProfit = 0;
    public $grossProfitMargin = 0;

    // Performance Metrics
    public $todayStats = [];
    public $weekStats = [];
    public $monthStats = [];
    public $yearStats = [];
    public $performanceAlerts = [];

    // Chart Data
    public $salesData = [];
    public $stockData = [];
    public $topProductsData = [];
    public $recentSales = [];

    // Monthly Trend Chart Properties
    public $monthlyTrendChart;
    public $monthlyTrendData = [];

    public function mount()
    {
        $this->dateFrom = Carbon::now()->subDays(7)->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->refreshData();
    }

    public function updatedDateFrom()
    {
        $this->refreshData();
    }

    public function updatedDateTo()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->loadEnhancedStats();
        $this->loadPerformanceMetrics();
        $this->loadMonthlyTrendData();
        $this->generatePerformanceAlerts();
        $this->loadChartData();
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    private function loadDashboardData()
    {
        $dateRange = [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'];

        // Basic product stats
        $this->totalProducts = Product::where('is_active', true)->count();
        $this->lowStockProducts = Product::where('is_active', true)->where('current_stock', '<=', 10)->count();
        $this->criticalStockProducts = Product::where('is_active', true)->where('current_stock', '<', 5)->count();
        $this->outOfStockProducts = Product::where('is_active', true)->where('current_stock', 0)->count();

        // Sales stats
        $salesData = Sale::whereBetween('created_at', $dateRange)
            ->selectRaw('COUNT(*) as count, SUM(final_total) as revenue')
            ->first();

        $this->totalSales = $salesData->count ?? 0;
        $this->totalRevenue = $salesData->revenue ?? 0;
        $this->averageOrderValue = $this->totalSales > 0 ? $this->totalRevenue / $this->totalSales : 0;

        // Growth calculations
        $previousPeriod = [
            Carbon::parse($this->dateFrom)->subDays(7)->format('Y-m-d H:i:s'),
            Carbon::parse($this->dateTo)->subDays(7)->format('Y-m-d H:i:s')
        ];

        $previousSalesData = Sale::whereBetween('created_at', $previousPeriod)
            ->selectRaw('COUNT(*) as count, SUM(final_total) as revenue')
            ->first();

        $previousSales = $previousSalesData->count ?? 0;
        $previousRevenue = $previousSalesData->revenue ?? 0;

        $this->salesGrowth = $previousSales > 0 ? (($this->totalSales - $previousSales) / $previousSales) * 100 : 0;
        $this->revenueGrowth = $previousRevenue > 0 ? (($this->totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        // Top selling category - menggunakan kolom category langsung dari products
        $topCategory = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', $dateRange)
            ->selectRaw('products.category, SUM(sale_items.qty) as total_qty')
            ->groupBy('products.category')
            ->orderByDesc('total_qty')
            ->first();

        $this->topSellingCategory = $topCategory->category ?? 'N/A';

        // Profit margin calculation
        $this->profitMargin = $this->calculateProfitMargin($dateRange);
    }

    private function loadEnhancedStats()
    {
        $this->loadEcerGrosirBreakdown();
    }

    private function loadPerformanceMetrics()
    {
        // Today's stats
        $today = Carbon::today();
        $this->todayStats = [
            'sales' => Sale::whereDate('created_at', $today)->count(),
            'transactions' => Sale::whereDate('created_at', $today)->count(),
            'revenue' => Sale::whereDate('created_at', $today)->sum('final_total'),
            'avg_order' => Sale::whereDate('created_at', $today)->avg('final_total') ?? 0,
        ];

        // This week's stats
        $weekStart = Carbon::now()->startOfWeek();
        $this->weekStats = [
            'sales' => Sale::whereBetween('created_at', [$weekStart, Carbon::now()])->count(),
            'revenue' => Sale::whereBetween('created_at', [$weekStart, Carbon::now()])->sum('final_total'),
            'avg_order' => Sale::whereBetween('created_at', [$weekStart, Carbon::now()])->avg('final_total') ?? 0,
        ];

        // This month's stats
        $monthStart = Carbon::now()->startOfMonth();
        $this->monthStats = [
            'sales' => Sale::whereBetween('created_at', [$monthStart, Carbon::now()])->count(),
            'revenue' => Sale::whereBetween('created_at', [$monthStart, Carbon::now()])->sum('final_total'),
            'avg_order' => Sale::whereBetween('created_at', [$monthStart, Carbon::now()])->avg('final_total') ?? 0,
        ];

        // This year's stats
        $yearStart = Carbon::now()->startOfYear();
        $this->yearStats = [
            'sales' => Sale::whereBetween('created_at', [$yearStart, Carbon::now()])->count(),
            'revenue' => Sale::whereBetween('created_at', [$yearStart, Carbon::now()])->sum('final_total'),
            'avg_order' => Sale::whereBetween('created_at', [$yearStart, Carbon::now()])->avg('final_total') ?? 0,
        ];
    }

    private function calculateProfitMargin($dateRange)
    {
        $salesData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', $dateRange)
            ->selectRaw('
                SUM(sale_items.unit_price * sale_items.qty) as total_revenue,
                SUM(products.base_cost * sale_items.qty) as total_cost
            ')
            ->first();

        if ($salesData && $salesData->total_revenue > 0) {
            return (($salesData->total_revenue - $salesData->total_cost) / $salesData->total_revenue) * 100;
        }

        return 0;
    }

    private function loadEcerGrosirBreakdown()
    {
        $dateRange = [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'];
        
        // Get ecer sales (retail) - based on price_tier in sale_items
        $ecerSales = Sale::whereBetween('created_at', $dateRange)
            ->whereHas('saleItems', function($query) {
                $query->where('price_tier', 'ecer');
            })
            ->selectRaw('COUNT(*) as count, SUM(final_total) as total')
            ->first();
        
        // Get grosir sales (wholesale) - based on price_tier in sale_items
        $grosirSales = Sale::whereBetween('created_at', $dateRange)
            ->whereHas('saleItems', function($query) {
                $query->where('price_tier', 'grosir');
            })
            ->selectRaw('COUNT(*) as count, SUM(final_total) as total')
            ->first();
        
        $this->ecerCount = $ecerSales->count ?? 0;
        $this->totalEcer = $ecerSales->total ?? 0;
        $this->grosirCount = $grosirSales->count ?? 0;
        $this->totalGrosir = $grosirSales->total ?? 0;
        
        // Calculate gross profit using PO costs with base_cost fallback
        $this->calculateGrossProfit($dateRange);
    }

    private function calculateGrossProfit($dateRange)
    {
        // Get sales with cost data from PO or base_cost
        $salesData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('purchase_order_items', function($join) {
                $join->on('products.id', '=', 'purchase_order_items.product_id')
                     ->whereRaw('purchase_order_items.created_at = (
                         SELECT MAX(poi2.created_at) 
                         FROM purchase_order_items poi2 
                         WHERE poi2.product_id = products.id
                     )');
            })
            ->leftJoin('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->whereBetween('sales.created_at', $dateRange)
            ->selectRaw('
                SUM(sale_items.unit_price * sale_items.qty) as total_revenue,
                SUM(
                    COALESCE(purchase_order_items.unit_cost, products.base_cost, 0) * sale_items.qty
                ) as total_cost
            ')
            ->first();
        
        $totalRevenue = $salesData->total_revenue ?? 0;
        $totalCost = $salesData->total_cost ?? 0;
        
        $this->grossProfit = $totalRevenue - $totalCost;
        $this->grossProfitMargin = $totalRevenue > 0 ? (($this->grossProfit / $totalRevenue) * 100) : 0;
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
                'url' => '/products?filter=critical_stock',
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
                'url' => '/products?filter=out_of_stock',
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
                'url' => '/reports?type=sales_analysis',
            ];
        } elseif ($this->salesGrowth > 20) {
            $this->performanceAlerts[] = [
                'type' => 'success',
                'icon' => 'fas fa-trophy',
                'title' => 'Penjualan Meningkat',
                'message' => "Penjualan naik {$this->salesGrowth}% dibanding periode sebelumnya",
                'action' => 'Lihat Detail',
                'url' => '/reports?type=growth_analysis',
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
                'url' => '/products?action=optimize_pricing',
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

        // Top Products Data
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$this->dateFrom, $this->dateTo])
            ->selectRaw('products.id, products.name, SUM(sale_items.qty) as total_qty, SUM(sale_items.qty * sale_items.unit_price) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Top products data (keep for table display)
        $this->topProductsData = $topProducts;

        // Stock Movement Data (In vs Out)
        $stockIn = StockMovement::where('type', 'IN')
            ->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'])
            ->sum('qty');

        $stockOut = StockMovement::where('type', 'OUT')
            ->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'])
            ->sum('qty');

        // Store stock data for chart creation in render method
        $this->stockData = [
            'stockIn' => $stockIn,
            'stockOut' => $stockOut,
        ];

        // Load recent sales
        $this->recentSales = Sale::with('saleItems')
            ->whereBetween('created_at', [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function exportMonthlyTrend()
    {
        try {
            $filename = 'monthly-trend-' . Carbon::now()->format('Y-m-d') . '.pdf';
            
            $pdf = Pdf::loadView('reports.monthly-trend', [
                'monthlyData' => $this->monthlyTrendData,
                'dateGenerated' => Carbon::now()->format('d/m/Y H:i'),
            ]);
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengexport data trend bulanan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Always generate fresh chart data to avoid cache issues
        $chartData = $this->generateChartData();

        return view('livewire.dashboard', [
            // Basic Stats
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

            // Enhanced Stats
            'totalEcer' => $this->totalEcer,
            'totalGrosir' => $this->totalGrosir,
            'ecerCount' => $this->ecerCount,
            'grosirCount' => $this->grosirCount,
            'grossProfit' => $this->grossProfit,
            'grossProfitMargin' => $this->grossProfitMargin,

            // Performance Metrics
            'todayStats' => $this->todayStats,
            'weekStats' => $this->weekStats,
            'monthStats' => $this->monthStats,
            'yearStats' => $this->yearStats,
            'performanceAlerts' => $this->performanceAlerts,

            // Settings
            'autoRefresh' => $this->autoRefresh,
            'refreshInterval' => $this->refreshInterval,

            // Chart Data
            'topProductsData' => $chartData['topProductsData'],
            'recentSales' => $chartData['recentSales'],
            'dailySalesChart' => $chartData['dailySalesChart'],
            'stockMovementChart' => $chartData['stockMovementChart'],
            'topProductsChart' => $chartData['topProductsChart'],
        ]);
    }

    private function generateChartData()
    {
        // Use the selected date range from the component
        $dateRange = [$this->dateFrom.' 00:00:00', $this->dateTo.' 23:59:59'];

        // Get top products data
        $topProductsData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', $dateRange)
            ->selectRaw('products.id, products.name, products.sku, SUM(sale_items.qty) as total_qty, SUM(sale_items.unit_price * sale_items.qty) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Get recent sales with proper date filtering
        $recentSales = Sale::with(['saleItems.product', 'cashier'])
            ->whereBetween('created_at', $dateRange)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Create enhanced sales trend chart
        $dailySalesChart = null;
        if (!empty($this->salesData) && $this->salesData->count() > 0) {
            $dailySalesChart = (new LarapexChart)->lineChart()
                ->setTitle('Trend Penjualan & Pendapatan')
                ->setSubtitle('Analisis performa harian dalam periode yang dipilih')
                ->addData('Transaksi', $this->salesData->pluck('count')->toArray())
                ->addData('Pendapatan (Rb)', $this->salesData->pluck('revenue')->map(function ($revenue) {
                    return round($revenue / 1000, 1);
                })->toArray())
                ->setXAxis($this->salesData->pluck('date')->map(function ($date) {
                    return Carbon::parse($date)->format('d/m');
                })->toArray())
                ->setColors(['#3B82F6', '#10B981'])
                ->setHeight(400)
                ->setGrid(true)
                ->setMarkers(['#3B82F6', '#10B981'], 6, 0)
                ->setDataLabels(true);
        }

        // Create enhanced stock movement chart focused on inventory management
        $stockMovementChart = null;
        if (!empty($this->stockData) && ($this->stockData['stockIn'] > 0 || $this->stockData['stockOut'] > 0)) {
            $stockInValue = (int) $this->stockData['stockIn'];
            $stockOutValue = (int) abs($this->stockData['stockOut']);

            if ($stockInValue > 0 || $stockOutValue > 0) {
                $stockMovementChart = (new LarapexChart)->donutChart()
                    ->setTitle('Pergerakan Stok Inventory')
                    ->setSubtitle('Analisis stok masuk vs keluar untuk manajemen inventory')
                    ->setDataset([$stockInValue, $stockOutValue])
                    ->setLabels([
                        'Stok Masuk ('.number_format($stockInValue).' unit)',
                        'Stok Keluar ('.number_format($stockOutValue).' unit)',
                    ])
                    ->setColors(['#10B981', '#EF4444'])
                    ->setHeight(350)
                    ->setDataLabels(true);
            }
        }

        // Create top products performance chart
        $topProductsChart = null;
        if ($topProductsData->count() > 0) {
            $topProductsChart = (new LarapexChart)->barChart()
                ->setTitle('Top 5 Produk Terlaris')
                ->setSubtitle('Berdasarkan volume penjualan dalam periode yang dipilih')
                ->addData('Qty Terjual', $topProductsData->pluck('total_qty')->toArray())
                ->setXAxis($topProductsData->pluck('name')->map(function ($name) {
                    return strlen($name) > 15 ? substr($name, 0, 15).'...' : $name;
                })->toArray())
                ->setColors(['#8B5CF6'])
                ->setHeight(350)
                ->setGrid(true, true)
                ->setDataLabels(true);
        }

        return [
            'topProductsData' => $topProductsData,
            'recentSales' => $recentSales,
            'dailySalesChart' => $dailySalesChart,
            'stockMovementChart' => $stockMovementChart,
            'topProductsChart' => $topProductsChart,
            'monthlyTrendChart' => $this->monthlyTrendChart,
        ];
    }

    private function loadMonthlyTrendData()
    {
        try {
            // Get monthly sales data for the last 12 months
            $monthlyData = collect();
            $currentDate = Carbon::now();
            
            for ($i = 11; $i >= 0; $i--) {
                $month = $currentDate->copy()->subMonths($i);
                $startOfMonth = $month->copy()->startOfMonth();
                $endOfMonth = $month->copy()->endOfMonth();
                
                // Get total sales for the month
                $monthlySales = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('total_amount');
                
                // Get transaction count for the month
                $monthlyTransactions = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count();
                
                // Get ecer and grosir breakdown
                $monthlyEcer = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->where('sale_type', 'ecer')
                    ->sum('total_amount');
                
                $monthlyGrosir = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->where('sale_type', 'grosir')
                    ->sum('total_amount');
                
                $monthlyData->push([
                    'month' => $month->format('M Y'),
                    'month_short' => $month->format('M'),
                    'total_sales' => $monthlySales,
                    'transactions' => $monthlyTransactions,
                    'ecer' => $monthlyEcer,
                    'grosir' => $monthlyGrosir,
                ]);
            }
            
            $this->monthlyTrendData = $monthlyData;
            
            // Create the chart
            if ($monthlyData->count() > 0) {
                $this->monthlyTrendChart = (new LarapexChart)->lineChart()
                    ->setTitle('Trend Omset Bulanan')
                    ->setSubtitle('Perbandingan omset 12 bulan terakhir')
                    ->addData('Total Omset (Juta)', $monthlyData->pluck('total_sales')->map(function ($sales) {
                        return round($sales / 1000000, 2);
                    })->toArray())
                    ->addData('Ecer (Juta)', $monthlyData->pluck('ecer')->map(function ($sales) {
                        return round($sales / 1000000, 2);
                    })->toArray())
                    ->addData('Grosir (Juta)', $monthlyData->pluck('grosir')->map(function ($sales) {
                        return round($sales / 1000000, 2);
                    })->toArray())
                    ->setXAxis($monthlyData->pluck('month_short')->toArray())
                    ->setColors(['#3B82F6', '#10B981', '#8B5CF6'])
                    ->setHeight(400)
                    ->setGrid(true)
                    ->setMarkers(['#3B82F6', '#10B981', '#8B5CF6'], 4, 0)
                    ->setDataLabels(true);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error loading monthly trend data: ' . $e->getMessage());
            $this->monthlyTrendData = collect();
            $this->monthlyTrendChart = null;
        }
    }
}
