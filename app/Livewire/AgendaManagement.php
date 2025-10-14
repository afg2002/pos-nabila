<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CashflowAgenda;
use App\Models\IncomingGoodsAgenda;
use App\Models\BatchExpiration;
use App\Models\SalesInvoice;
use Carbon\Carbon;

class AgendaManagement extends Component
{
    public $activeTab = 'cashflow';
    public $selectedDate;
    public $filterMonth;
    public $search = '';
    
    // Cashflow Tab Data
    public $cashflowData = [];
    public $todayCashflow;
    public $monthlySummary;
    
    // Purchase Order Tab Data
    public $purchaseOrderData = [];
    public $pendingAgendas;
    public $receivedAgendas;
    public $overdueAgendas;
    
    // Statistics
    public $totalRevenue;
    public $totalExpenses;
    public $netCashflow;
    public $totalPendingPO;
    public $totalOverduePO;

    protected $listeners = [
        'refreshCashflow' => 'loadCashflowData',
        'refreshPurchaseOrder' => 'loadPurchaseOrderData',
        'dateSelected' => 'handleDateSelection',
        'show-message' => 'handleShowMessage',
    ];

    public function mount()
    {
        $this->selectedDate = today()->format('Y-m-d');
        $this->filterMonth = now()->format('Y-m');
        $this->loadCashflowData();
        $this->loadPurchaseOrderData();
        $this->calculateStatistics();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        
        if ($tab === 'cashflow') {
            $this->loadCashflowData();
        } else {
            $this->loadPurchaseOrderData();
        }
    }

    public function handleDateSelection($date)
    {
        $this->selectedDate = $date;
        $this->loadCashflowData();
    }

    public function previousMonth()
    {
        $currentMonth = Carbon::parse($this->filterMonth . '-01');
        $this->filterMonth = $currentMonth->subMonth()->format('Y-m');
        $this->loadCashflowData();
    }

    public function nextMonth()
    {
        $currentMonth = Carbon::parse($this->filterMonth . '-01');
        $this->filterMonth = $currentMonth->addMonth()->format('Y-m');
        $this->loadCashflowData();
    }

    public function loadCashflowData()
    {
        // Load today's cashflow
        $this->todayCashflow = CashflowAgenda::whereDate('date', $this->selectedDate)->first();
        
        if (!$this->todayCashflow) {
            $this->todayCashflow = new CashflowAgenda([
                'date' => $this->selectedDate,
                'total_omset' => 0,
                'total_ecer' => 0,
                'total_grosir' => 0,
                'grosir_cash_hari_ini' => 0,
                'qr_payment_amount' => 0,
                'edc_payment_amount' => 0,
                'total_expenses' => 0,
            ]);
        }
        
        // Load monthly data for calendar
        $this->cashflowData = $this->getMonthlyCashflowData();
        
        // Calculate monthly summary
        $this->monthlySummary = $this->calculateMonthlySummary();
    }

    public function loadPurchaseOrderData()
    {
        $query = IncomingGoodsAgenda::with(['supplier', 'batchExpirations']);
        
        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('batch_number', 'like', '%' . $this->search . '%')
                  ->orWhere('supplier_name', 'like', '%' . $this->search . '%')
                  ->orWhere('item_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('supplier', function ($subQuery) {
                      $subQuery->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }
        
        // Load different status agendas
        $this->pendingAgendas = $query->where('status', 'scheduled')->get();
        $this->receivedAgendas = $query->where('status', 'received')->get();
        $this->overdueAgendas = $query->where('payment_due_date', '<', today())
            ->where('payment_status', '!=', 'paid')
            ->get();
        
        $this->purchaseOrderData = [
            'pending' => $this->pendingAgendas,
            'received' => $this->receivedAgendas,
            'overdue' => $this->overdueAgendas,
        ];
    }

    public function calculateStatistics()
    {
        // Cashflow statistics
        $monthlyCashflow = CashflowAgenda::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        
        $this->totalRevenue = $monthlyCashflow->sum('total_omset');
        $this->totalExpenses = $monthlyCashflow->sum('total_expenses');
        $this->netCashflow = $this->totalRevenue - $this->totalExpenses;
        
        // Purchase Order statistics
        $this->totalPendingPO = IncomingGoodsAgenda::where('status', 'scheduled')->count();
        $this->totalOverduePO = IncomingGoodsAgenda::where('payment_due_date', '<', today())
            ->where('payment_status', '!=', 'paid')
            ->count();
    }

    private function getMonthlyCashflowData()
    {
        $currentMonth = Carbon::parse($this->filterMonth . '-01');
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        
        // Get cashflow data for the month
        $monthlyData = CashflowAgenda::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');
        
        // Generate calendar grid
        $calendarData = [];
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);
        
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $cashflow = $monthlyData->get($dateStr);
            
            $calendarData[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'isCurrentMonth' => $current->month === $currentMonth->month,
                'isToday' => $current->isToday(),
                'isSelected' => $dateStr === $this->selectedDate,
                'cashflow' => $cashflow,
                'hasData' => $cashflow && ($cashflow->total_omset > 0 || $cashflow->total_expenses > 0),
            ];
            
            $current->addDay();
        }
        
        return $calendarData;
    }

    private function calculateMonthlySummary()
    {
        $currentMonth = Carbon::parse($this->filterMonth . '-01');
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();
        
        $monthlyData = CashflowAgenda::whereBetween('date', [$startOfMonth, $endOfMonth])->get();
        
        return [
            'total_omset' => $monthlyData->sum('total_omset'),
            'total_ecer' => $monthlyData->sum('total_ecer'),
            'total_grosir' => $monthlyData->sum('total_grosir'),
            'total_cash' => $monthlyData->sum('grosir_cash_hari_ini'),
            'total_qr' => $monthlyData->sum('qr_payment_amount'),
            'total_edc' => $monthlyData->sum('edc_payment_amount'),
            'total_expenses' => $monthlyData->sum('total_expenses'),
            'net_cashflow' => $monthlyData->sum('total_omset') - $monthlyData->sum('total_expenses'),
            'days_with_data' => $monthlyData->where('total_omset', '>', 0)->count(),
            'total_days' => $currentMonth->daysInMonth,
        ];
    }

    public function getPaymentMethodBreakdown()
    {
        return [
            'cash' => $this->monthlySummary['total_cash'],
            'qr' => $this->monthlySummary['total_qr'],
            'edc' => $this->monthlySummary['total_edc'],
            'total' => $this->monthlySummary['total_cash'] + $this->monthlySummary['total_qr'] + $this->monthlySummary['total_edc'],
        ];
    }

    public function getAnnualCashflowData()
    {
        $year = now()->year;
        $annualData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthData = CashflowAgenda::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();
            
            $annualData[] = [
                'month' => Carbon::create($year, $month, 1)->format('M'),
                'month_name' => Carbon::create($year, $month, 1)->format('F'),
                'revenue' => $monthData->sum('total_omset'),
                'expenses' => $monthData->sum('total_expenses'),
                'net_cashflow' => $monthData->sum('total_omset') - $monthData->sum('total_expenses'),
            ];
        }
        
        return $annualData;
    }

    public function getExpiringBatches()
    {
        return BatchExpiration::with(['incomingGoodsAgenda.supplier'])
            ->whereBetween('expired_date', [today(), today()->addDays(30)])
            ->where('remaining_quantity', '>', 0)
            ->orderBy('expired_date')
            ->limit(10)
            ->get();
    }

    public function getRecentInvoices()
    {
        return SalesInvoice::with(['sale'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function handleShowMessage($data)
    {
        // This method will handle the show-message event
        // The actual display will be handled by the parent component or a global notification system
        session()->flash('message', $data);
    }

    public function render()
    {
        return view('livewire.agenda-management', [
            'paymentBreakdown' => $this->getPaymentMethodBreakdown(),
            'annualData' => $this->getAnnualCashflowData(),
            'expiringBatches' => $this->getExpiringBatches(),
            'recentInvoices' => $this->getRecentInvoices(),
        ]);
    }
}