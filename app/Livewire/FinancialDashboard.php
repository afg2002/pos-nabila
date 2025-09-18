<?php

namespace App\Livewire;

use App\CashBalance;
use App\PaymentSchedule;
use App\Receivable;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;

class FinancialDashboard extends Component
{
    public $selectedPeriod = 'today';
    public $showDetails = false;
    public $detailType = '';
    public $refreshInterval = 30;

    public $cashBalance = 0;
    public $totalReceivables = 0;
    public $netCashPosition = 0;
    public $todayRevenue = 0;
    public $monthlyRevenue = 0;
    public $pendingPayments = 0;
    public $overduePayments = 0;

    public $cashTrend = [];
    public $revenueTrend = [];
    public $receivablesTrend = [];

    public $lowCashThreshold = 1_000_000;
    public $criticalCashThreshold = 500_000;

    public function mount(): void
    {
        if (! Gate::allows('financial.dashboard')) {
            abort(403, 'Unauthorized access to financial dashboard');
        }

        $this->loadFinancialData();
    }

    #[On('refresh-financial-data')]
    public function loadFinancialData(): void
    {
        $this->calculateCashBalance();
        $this->calculateReceivables();
        $this->calculatePendingPayments();
        $this->calculateRevenue();
        $this->calculateNetPosition();
        $this->loadTrendData();
    }

    private function calculateCashBalance(): void
    {
        $latest = CashBalance::orderByDesc('date')
            ->orderByDesc('type')
            ->first();

        $this->cashBalance = $latest?->closing_balance ?? 0;
    }

    private function calculateReceivables(): void
    {
        $this->totalReceivables = Receivable::outstanding()
            ->sum(DB::raw('amount - paid_amount'));
    }

    private function calculatePendingPayments(): void
    {
        $pending = PaymentSchedule::whereIn('status', ['pending', 'partial', 'overdue']);

        $this->pendingPayments = (clone $pending)
            ->where('due_date', '>=', Carbon::today())
            ->sum(DB::raw('amount - paid_amount'));

        $this->overduePayments = (clone $pending)
            ->where('due_date', '<', Carbon::today())
            ->sum(DB::raw('amount - paid_amount'));
    }

    private function calculateRevenue(): void
    {
        $this->todayRevenue = Sale::whereDate('created_at', Carbon::today())
            ->sum('final_total');

        $this->monthlyRevenue = Sale::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->sum('final_total');
    }

    private function calculateNetPosition(): void
    {
        $this->netCashPosition = $this->cashBalance + $this->totalReceivables - ($this->pendingPayments + $this->overduePayments);
    }

    private function loadTrendData(): void
    {
        $days = 7;
        $startDate = Carbon::now()->subDays($days - 1);

        $this->cashTrend = [];
        $this->revenueTrend = [];
        $this->receivablesTrend = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);

            $cashRecord = CashBalance::whereDate('date', $date)
                ->orderByDesc('type')
                ->first();

            $this->cashTrend[] = [
                'date' => $date->format('d M'),
                'amount' => $cashRecord?->closing_balance ?? 0,
            ];

            $revenue = Sale::whereDate('created_at', $date)->sum('final_total');
            $this->revenueTrend[] = [
                'date' => $date->format('d M'),
                'amount' => $revenue,
            ];

            $receivable = Receivable::whereDate('due_date', '<=', $date)
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->sum(DB::raw('amount - paid_amount'));

            $this->receivablesTrend[] = [
                'date' => $date->format('d M'),
                'amount' => $receivable,
            ];
        }
    }

    public function showDetailModal(string $type): void
    {
        $this->detailType = $type;
        $this->showDetails = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetails = false;
        $this->detailType = '';
    }

    public function getDetailDataProperty()
    {
        return match ($this->detailType) {
            'cash' => CashBalance::with('creator')
                ->orderByDesc('date')
                ->orderByDesc('type')
                ->limit(10)
                ->get(),
            'receivables' => Receivable::outstanding()
                ->orderBy('due_date')
                ->get(),
            'overdue' => PaymentSchedule::with('incomingGoods')
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->where('due_date', '<', Carbon::today())
                ->orderBy('due_date')
                ->get(),
            'revenue' => Sale::with(['cashier', 'saleItems.product'])
                ->whereDate('created_at', Carbon::today())
                ->orderByDesc('created_at')
                ->get(),
            default => collect(),
        };
    }

    public function getCashStatusProperty(): array
    {
        return match (true) {
            $this->cashBalance <= $this->criticalCashThreshold => ['status' => 'critical', 'message' => 'Kas sangat rendah!'],
            $this->cashBalance <= $this->lowCashThreshold => ['status' => 'warning', 'message' => 'Kas rendah'],
            default => ['status' => 'good', 'message' => 'Kas sehat'],
        };
    }

    public function getNetPositionStatusProperty(): array
    {
        return match (true) {
            $this->netCashPosition < 0 => ['status' => 'negative', 'message' => 'Posisi kas negatif'],
            $this->netCashPosition < $this->lowCashThreshold => ['status' => 'warning', 'message' => 'Posisi kas rendah'],
            default => ['status' => 'positive', 'message' => 'Posisi kas positif'],
        };
    }

    public function getUpcomingPaymentsProperty()
    {
        return PaymentSchedule::with('incomingGoods')
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->orderBy('due_date')
            ->get();
    }

    public function getCriticalAlertsProperty()
    {
        $alerts = collect();

        $cashStatus = $this->cashStatus;
        if ($cashStatus['status'] !== 'good') {
            $alerts->push([
                'type' => 'cash',
                'priority' => $cashStatus['status'] === 'critical' ? 'critical' : 'warning',
                'message' => $cashStatus['message'],
                'amount' => $this->cashBalance,
            ]);
        }

        if ($this->overduePayments > 0) {
            $alerts->push([
                'type' => 'overdue',
                'priority' => 'critical',
                'message' => 'Ada pembayaran supplier yang terlambat',
                'amount' => $this->overduePayments,
            ]);
        }

        if ($this->netCashPosition < 0) {
            $alerts->push([
                'type' => 'net_position',
                'priority' => 'warning',
                'message' => 'Posisi kas negatif setelah memperhitungkan piutang dan hutang',
                'amount' => $this->netCashPosition,
            ]);
        }

        return $alerts;
    }

    public function refreshData(): void
    {
        $this->loadFinancialData();
        $this->dispatch('financial-data-refreshed');
    }

    public function render()
    {
        return view('livewire.financial-dashboard', [
            'detailData' => $this->detailData,
            'cashStatus' => $this->cashStatus,
            'netPositionStatus' => $this->netPositionStatus,
            'upcomingPayments' => $this->upcomingPayments,
            'criticalAlerts' => $this->criticalAlerts,
        ]);
    }
}
