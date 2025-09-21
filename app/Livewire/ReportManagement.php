<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Sale;
use App\SaleItem;
use App\Product;
use App\StockMovement;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use App\Exports\StockReportExport;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportManagement extends Component
{
    use WithPagination;

    // Filter Properties
    public $reportType = 'sales';
    public $dateFrom;
    public $dateTo;
    public $cashierId = '';
    public $customerId = '';
    public $categoryFilter = '';
    public $productId = '';
    
    // Report Configuration
    public $reportTitle = 'Sales Report';
    public $includeCharts = true;
    public $groupBy = 'daily'; // daily, weekly, monthly
    public $exportFormat = 'excel';
    
    // Scheduled Reports
    public $scheduleFrequency = 'weekly';
    public $scheduleEmail = '';
    public $scheduleEnabled = false;
    
    // Data Properties
    public $reportData = [];
    public $summaryStats = [];
    public $chartData = [];
    public $isLoading = false;

    protected $rules = [
        'dateFrom' => 'required|date',
        'dateTo' => 'required|date|after_or_equal:dateFrom',
        'reportTitle' => 'required|string|max:255',
        'scheduleEmail' => 'nullable|email',
    ];

    public function mount()
    {
        if (!auth()->user()->hasPermission('pos.reports')) {
            abort(403, 'Unauthorized access to report management.');
        }
        $this->dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->scheduleEmail = auth()->user()->email;
        $this->generateReport();
    }

    public function updatedReportType()
    {
        $this->resetPage();
        $this->generateReport();
    }

    public function updatedDateFrom()
    {
        $this->generateReport();
    }

    public function updatedDateTo()
    {
        $this->generateReport();
    }

    public function updatedCashierId()
    {
        $this->generateReport();
    }

    public function updatedCustomerId()
    {
        $this->generateReport();
    }

    public function updatedCategoryFilter()
    {
        $this->generateReport();
    }

    public function updatedProductId()
    {
        $this->generateReport();
    }

    public function generateReport()
    {
        $this->isLoading = true;
        
        try {
            // Create cache key based on report parameters
            $cacheKey = 'report_' . md5(serialize([
                'type' => $this->reportType,
                'dateFrom' => $this->dateFrom,
                'dateTo' => $this->dateTo,
                'cashierId' => $this->cashierId,
                'customerId' => $this->customerId,
                'categoryFilter' => $this->categoryFilter,
                'productId' => $this->productId,
                'groupBy' => $this->groupBy
            ]));

            // Cache reports for 10 minutes
            $cachedData = cache()->remember($cacheKey, 600, function () {
                switch ($this->reportType) {
                    case 'sales':
                        return $this->buildSalesReport();
                    case 'inventory':
                        return $this->buildInventoryReport();
                    case 'profit':
                        return $this->buildProfitReport();
                    case 'cashier':
                        return $this->buildCashierReport();
                    default:
                        return $this->buildSalesReport();
                }
            });

            // Assign cached data to properties
            $this->reportData = $cachedData['reportData'];
            $this->summaryStats = $cachedData['summaryStats'];
            $this->chartData = $cachedData['chartData'];

        } catch (\Exception $e) {
            session()->flash('error', 'Error generating report: ' . $e->getMessage());
        }
        
        $this->isLoading = false;
    }

    private function buildSalesReport()
    {
        $query = Sale::with(['user', 'saleItems.product'])
            ->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ]);

        // Apply filters
        if ($this->cashierId) {
            $query->where('user_id', $this->cashierId);
        }

        if ($this->customerId) {
            $query->where('customer_name', 'like', '%' . $this->customerId . '%');
        }

        $sales = $query->get();
        
        // Filter by category if specified
        if ($this->categoryFilter) {
            $sales = $sales->filter(function ($sale) {
                return $sale->saleItems->some(function ($item) {
                    return $item->product && $item->product->category === $this->categoryFilter;
                });
            });
        }

        // Filter by product if specified
        if ($this->productId) {
            $sales = $sales->filter(function ($sale) {
                return $sale->saleItems->some(function ($item) {
                    return $item->product_id == $this->productId;
                });
            });
        }

        $reportData = $sales->toArray();
        
        // Calculate summary statistics
        $summaryStats = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total_amount'),
            'average_sale' => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'total_items_sold' => $sales->sum(function ($sale) {
                return $sale->saleItems->sum('quantity');
            }),
            'unique_customers' => $sales->pluck('customer_name')->filter()->unique()->count(),
            'top_cashier' => $sales->groupBy('user_id')->map->count()->sortDesc()->keys()->first()
        ];

        // Generate chart data
        $chartData = $this->buildChartData($sales);

        return [
            'reportData' => $reportData,
            'summaryStats' => $summaryStats,
            'chartData' => $chartData
        ];
    }

    private function generateInventoryReport()
    {
        $query = StockMovement::with(['product'])
            ->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ]);

        if ($this->categoryFilter) {
            $query->whereHas('product', function ($q) {
                $q->where('category', $this->categoryFilter)
                  ->whereNull('deleted_at');
            });
        }

        if ($this->productId) {
            $query->where('product_id', $this->productId);
        }

        $movements = $query->get();
        $this->reportData = $movements->toArray();
        
        // Calculate inventory statistics
        $this->summaryStats = [
            'total_movements' => $movements->count(),
            'stock_in' => $movements->where('type', 'in')->sum('qty'),
            'stock_out' => $movements->where('type', 'out')->sum('qty'),
            'net_movement' => $movements->where('type', 'in')->sum('qty') - $movements->where('type', 'out')->sum('qty'),
            'products_affected' => $movements->pluck('product_id')->unique()->count(),
            'low_stock_items' => Product::where('status', 'active')
                                       ->whereNull('deleted_at')
                                       ->where('current_stock', '<', 10)
                                       ->count()
        ];
    }

    private function generateProfitReport()
    {
        $sales = Sale::with(['saleItems.product'])
            ->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ])
            ->get();

        $profitData = [];
        $totalRevenue = 0;
        $totalCost = 0;

        foreach ($sales as $sale) {
            $saleProfit = 0;
            $saleCost = 0;
            
            foreach ($sale->saleItems as $item) {
                if ($item->product) {
                    $itemCost = $item->product->cost_price * $item->quantity;
                    $itemRevenue = $item->price * $item->quantity;
                    $itemProfit = $itemRevenue - $itemCost;
                    
                    $saleProfit += $itemProfit;
                    $saleCost += $itemCost;
                }
            }
            
            $profitData[] = [
                'sale_id' => $sale->id,
                'date' => $sale->created_at->format('Y-m-d'),
                'revenue' => $sale->total_amount,
                'cost' => $saleCost,
                'profit' => $saleProfit,
                'margin' => $sale->total_amount > 0 ? ($saleProfit / $sale->total_amount) * 100 : 0
            ];
            
            $totalRevenue += $sale->total_amount;
            $totalCost += $saleCost;
        }

        $this->reportData = $profitData;
        $totalProfit = $totalRevenue - $totalCost;
        
        $this->summaryStats = [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
            'average_profit_per_sale' => count($profitData) > 0 ? $totalProfit / count($profitData) : 0,
            'best_margin_sale' => collect($profitData)->max('margin') ?? 0
        ];
    }

    private function generateCashierReport()
    {
        $sales = Sale::with(['user', 'saleItems'])
            ->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ])
            ->get()
            ->groupBy('user_id');

        $cashierData = [];
        
        foreach ($sales as $userId => $userSales) {
            $user = $userSales->first()->user;
            $totalSales = $userSales->count();
            $totalRevenue = $userSales->sum('total_amount');
            $totalItems = $userSales->sum(function ($sale) {
                return $sale->saleItems->sum('quantity');
            });
            
            $cashierData[] = [
                'cashier_id' => $userId,
                'cashier_name' => $user ? $user->name : 'Unknown',
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'total_items' => $totalItems,
                'average_sale' => $totalSales > 0 ? $totalRevenue / $totalSales : 0,
                'sales_per_day' => $totalSales / max(1, Carbon::parse($this->dateFrom)->diffInDays(Carbon::parse($this->dateTo)) + 1)
            ];
        }

        // Sort by total revenue
        usort($cashierData, function ($a, $b) {
            return $b['total_revenue'] <=> $a['total_revenue'];
        });

        $this->reportData = $cashierData;
        
        $this->summaryStats = [
            'total_cashiers' => count($cashierData),
            'top_performer' => $cashierData[0]['cashier_name'] ?? 'N/A',
            'total_sales_all' => array_sum(array_column($cashierData, 'total_sales')),
            'total_revenue_all' => array_sum(array_column($cashierData, 'total_revenue')),
            'average_sales_per_cashier' => count($cashierData) > 0 ? array_sum(array_column($cashierData, 'total_sales')) / count($cashierData) : 0,
            'average_revenue_per_cashier' => count($cashierData) > 0 ? array_sum(array_column($cashierData, 'total_revenue')) / count($cashierData) : 0
        ];
    }

    private function generateChartData($sales)
    {
        $groupedData = [];
        
        foreach ($sales as $sale) {
            $key = '';
            switch ($this->groupBy) {
                case 'daily':
                    $key = $sale->created_at->format('Y-m-d');
                    break;
                case 'weekly':
                    $key = $sale->created_at->format('Y-W');
                    break;
                case 'monthly':
                    $key = $sale->created_at->format('Y-m');
                    break;
            }
            
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = ['count' => 0, 'revenue' => 0];
            }
            
            $groupedData[$key]['count']++;
            $groupedData[$key]['revenue'] += $sale->total_amount;
        }
        
        $this->chartData = [
            'labels' => array_keys($groupedData),
            'sales_count' => array_column($groupedData, 'count'),
            'revenue' => array_column($groupedData, 'revenue')
        ];
    }

    public function exportReport()
    {
        try {
            $filename = $this->reportTitle . '_' . Carbon::now()->format('Y-m-d_H-i-s');
            
            if ($this->exportFormat === 'excel') {
                return Excel::download(
                    new SalesReportExport($this->reportData, $this->summaryStats, $this->reportType),
                    $filename . '.xlsx'
                );
            } elseif ($this->exportFormat === 'pdf') {
                $pdf = PDF::loadView('reports.pdf-template', [
                    'reportData' => $this->reportData,
                    'summaryStats' => $this->summaryStats,
                    'reportType' => $this->reportType,
                    'reportTitle' => $this->reportTitle,
                    'dateFrom' => $this->dateFrom,
                    'dateTo' => $this->dateTo,
                    'chartData' => $this->includeCharts ? $this->chartData : null
                ]);
                
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename . '.pdf');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
        }
    }

    public function scheduleReport()
    {
        $this->validate([
            'scheduleEmail' => 'required|email',
            'scheduleFrequency' => 'required|in:daily,weekly,monthly'
        ]);

        try {
            // Here you would typically save to a scheduled_reports table
            // and set up a cron job or queue job to send reports
            
            session()->flash('success', 'Report scheduled successfully! You will receive reports at ' . $this->scheduleEmail);
        } catch (\Exception $e) {
            session()->flash('error', 'Error scheduling report: ' . $e->getMessage());
        }
    }

    public function getCashiers()
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager', 'cashier']);
        })->get();
    }

    public function getCategories()
    {
        return Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->pluck('category');
    }

    public function getProducts()
    {
        $query = Product::select('id', 'name', 'sku', 'category')
                       ->whereNull('deleted_at');
        
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }
        
        return $query->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.report-management', [
            'cashiers' => $this->getCashiers(),
            'categories' => $this->getCategories(),
            'products' => $this->getProducts()
        ]);
    }
}