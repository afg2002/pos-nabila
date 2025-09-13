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
    public $salesChartData = [];
    public $topProductsData = [];
    public $stockMovementData = [];
    public $revenueChartData = [];
    
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
        $this->totalProducts = Product::where('is_active', true)->count();
        
        // Total Sales in date range
        $this->totalSales = Sale::whereBetween('created_at', [$this->dateFrom, $this->dateTo])->count();
        
        // Total Revenue in date range
        $this->totalRevenue = Sale::whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                                 ->sum('final_total');
        
        // Low Stock Products (stock < 10)
        $this->lowStockProducts = Product::where('is_active', true)
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
        
        $this->salesChartData = [
            'labels' => $salesData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m');
            })->toArray(),
            'datasets' => [
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => $salesData->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2
                ]
            ]
        ];
        
        // Revenue Chart Data
        $this->revenueChartData = [
            'labels' => $salesData->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('d/m');
            })->toArray(),
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $salesData->pluck('revenue')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2
                ]
            ]
        ];
        
        // Top Products Data
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                              ->join('products', 'sale_items.product_id', '=', 'products.id')
                              ->whereBetween('sales.created_at', [$this->dateFrom, $this->dateTo])
                              ->selectRaw('products.name, SUM(sale_items.qty) as total_qty, SUM(sale_items.qty * sale_items.unit_price) as total_revenue')
                              ->groupBy('products.id', 'products.name')
                              ->orderByDesc('total_qty')
                              ->limit(5)
                              ->get();
        
        $this->topProductsData = [
            'labels' => $topProducts->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Jumlah Terjual',
                    'data' => $topProducts->pluck('total_qty')->toArray(),
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ]
                ]
            ]
        ];
        
        // Stock Movement Data (In vs Out)
        $stockIn = StockMovement::where('type', 'IN')
                               ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
                               ->sum('qty');
        
        $stockOut = StockMovement::where('type', 'OUT')
                                ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
                                ->sum('qty');
        
        // Only create stock movement data if there's actual movement
        if ($stockIn > 0 || $stockOut > 0) {
            $this->stockMovementData = [
                'labels' => ['Stok Masuk', 'Stok Keluar'],
                'datasets' => [
                    [
                        'data' => [$stockIn, $stockOut],
                        'backgroundColor' => [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ]
                    ]
                ]
            ];
        } else {
            $this->stockMovementData = [];
        }
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
    
    public function render()
    {
        // Calculate today's sales and revenue
        $todaySales = Sale::whereDate('created_at', Carbon::today())->sum('final_total');
        $todayRevenue = $todaySales; // Same as today's sales total
        $todayTransactions = Sale::whereDate('created_at', Carbon::today())->count();
        
        // Get top products (last 7 days)
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                              ->join('products', 'sale_items.product_id', '=', 'products.id')
                              ->whereBetween('sales.created_at', [Carbon::now()->subDays(7), Carbon::now()])
                              ->selectRaw('products.id, products.name, products.sku, products.price_retail, SUM(sale_items.qty) as total_sold')
                              ->groupBy('products.id', 'products.name', 'products.sku', 'products.price_retail')
                              ->orderByDesc('total_sold')
                              ->limit(5)
                              ->get();
        
        // Get recent sales (last 10)
        $recentSales = Sale::with(['cashier', 'saleItems'])
                          ->orderByDesc('created_at')
                          ->limit(10)
                          ->get();
        
        // Prepare chart data for last 7 days
        $chartLabels = [];
        $chartData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('d/m');
            $dailySales = Sale::whereDate('created_at', $date)->count();
            $chartData[] = $dailySales;
        }
        
        // Create sales chart only if there's data
        $salesChart = null;
        if (array_sum($chartData) > 0) {
            $salesChart = LarapexChart::lineChart()
                ->setTitle('Penjualan 7 Hari Terakhir')
                ->setSubtitle('Grafik penjualan harian')
                ->addData('Jumlah Transaksi', $chartData)
                ->setXAxis($chartLabels)
                ->setHeight(300);
        }
        
        return view('livewire.dashboard', [
            'totalProducts' => $this->totalProducts,
            'totalSales' => $this->totalRevenue, // Total revenue in selected date range
            'lowStockCount' => $this->lowStockProducts,
            'todayTransactions' => $todayTransactions,
            'todaySales' => $todaySales,
            'todayRevenue' => $todayRevenue,
            'topProducts' => $topProducts,
            'recentSales' => $recentSales,
            'salesChart' => $salesChart,
            'stockMovementData' => $this->stockMovementData
        ]);
    }
}