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

class Dashboard extends Component
{
    public $dateFrom;
    public $dateTo;
    public $reportType = 'sales';
    
    // Dashboard Stats
    public $totalProducts = 0;
    public $totalSales = 0;
    public $totalRevenue = 0;
    public $lowStockProducts = 0;
    
    // Chart Data
    public $salesData = [];
    public $stockData = [];
    public $topProductsData = [];
    public $recentSales = [];
    
    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->loadDashboardData();
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
        $this->loadDashboardData();
        session()->flash('success', 'Data dashboard berhasil diperbarui!');
    }
    
    public function loadDashboardData()
    {
        $this->loadStats();
        $this->loadChartData();
    }
    
    private function loadStats()
    {
        // Total Products
        $this->totalProducts = Product::where('status', 'active')
                                     ->whereNull('deleted_at')
                                     ->count();
        
        // Total Sales in date range
        $this->totalSales = Sale::whereBetween('created_at', [$this->dateFrom, $this->dateTo])->count();
        
        // Total Revenue in date range
        $this->totalRevenue = Sale::whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                                 ->sum('final_total');
        
        // Low Stock Products (stock < 10)
        $this->lowStockProducts = Product::where('status', 'active')
                                        ->whereNull('deleted_at')
                                        ->where('current_stock', '<', 10)
                                        ->count();
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
        session()->flash('success', 'Export top products berhasil!');
    }
    
    public function exportRecentSales()
    {
        session()->flash('success', 'Export recent sales berhasil!');
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
        // Calculate today's sales and revenue
        $todaySales = Sale::whereDate('created_at', Carbon::today())->sum('final_total');
        $todayRevenue = $todaySales; // Same as today's sales total
        $todayTransactions = Sale::whereDate('created_at', Carbon::today())->count();
        
        // Get top products data
        $topProductsData = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                              ->join('products', 'sale_items.product_id', '=', 'products.id')
                              ->whereBetween('sales.created_at', [Carbon::now()->subDays(7), Carbon::now()])
                              ->selectRaw('products.id, products.name, products.sku, SUM(sale_items.qty) as total_qty, SUM(sale_items.qty * sale_items.unit_price) as total_revenue')
                              ->groupBy('products.id', 'products.name', 'products.sku')
                              ->orderByDesc('total_qty')
                              ->limit(5)
                              ->get();
        
        // Get recent sales
        $recentSales = Sale::with(['saleItems'])
                          ->orderByDesc('created_at')
                          ->limit(5)
                          ->get();
        
        // Create sales trend chart (line + bar combination)
        $dailySalesChart = null;
        if (!empty($this->salesData) && $this->salesData->count() > 0) {
            $dailySalesChart = LarapexChart::lineChart()
                ->setTitle('Penjualan & Pendapatan Harian')
                ->setSubtitle('Trend penjualan dan pendapatan dalam periode yang dipilih')
                ->addData('Jumlah Transaksi', $this->salesData->pluck('count')->toArray())
                ->addData('Pendapatan (Rb)', $this->salesData->pluck('revenue')->map(function($revenue) {
                    return round($revenue / 1000, 1); // Convert to thousands for better chart readability
                })->toArray())
                ->setXAxis($this->salesData->pluck('date')->map(function($date) {
                    return Carbon::parse($date)->format('d/m');
                })->toArray())
                ->setColors(['#3B82F6', '#10B981'])
                ->setHeight(350)
                ->setGrid(true)
                ->setMarkers(['#3B82F6', '#10B981'], 6, 0);
        }
        
        // Create stock movement chart
        $stockMovementChart = null;
        if (!empty($this->stockData) && ($this->stockData['stockIn'] > 0 || $this->stockData['stockOut'] > 0)) {
            $stockMovementChart = LarapexChart::donutChart()
                ->setTitle('Pergerakan Stok')
                ->setSubtitle('Stok masuk vs keluar dalam periode ini')
                ->addData([$this->stockData['stockIn'], abs($this->stockData['stockOut'])])
                ->setLabels(['Stok Masuk', 'Stok Keluar'])
                ->setColors(['#10B981', '#EF4444'])
                ->setHeight(350);
        }
        
        // Create top products bar chart
        $topProductsChart = null;
        if ($topProductsData->count() > 0) {
            $topProductsChart = LarapexChart::barChart()
                ->setTitle('Top 5 Produk Terlaris')
                ->setSubtitle('Berdasarkan jumlah terjual dalam 7 hari terakhir')
                ->addData('Qty Terjual', $topProductsData->pluck('total_qty')->toArray())
                ->setXAxis($topProductsData->pluck('name')->map(function($name) {
                    return strlen($name) > 15 ? substr($name, 0, 15) . '...' : $name;
                })->toArray())
                ->setColors(['#8B5CF6'])
                ->setHeight(350)
                ->setGrid(true, true)
                ->setDataLabels(true);
        }
        
        // Create category performance pie chart
        $categoryChart = null;
        $categoryData = Product::selectRaw('category, SUM(current_stock) as total_stock, COUNT(*) as product_count')
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
                ->setHeight(350);
        }
        
        return view('livewire.dashboard', [
            'totalProducts' => $this->totalProducts,
            'totalSales' => $this->totalRevenue, // Total revenue in selected date range
            'lowStockCount' => $this->lowStockProducts,
            'todayTransactions' => $todayTransactions,
            'topProductsData' => $topProductsData,
            'recentSales' => $recentSales,
            'dailySalesChart' => $dailySalesChart,
            'stockMovementChart' => $stockMovementChart,
            'topProductsChart' => $topProductsChart,
            'categoryChart' => $categoryChart
        ]);
    }
}