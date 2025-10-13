<?php

namespace App\Livewire;

use App\CashBalance;
use App\PaymentSchedule;
use App\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class FinancialReports extends Component
{
    public $reportType = 'receivables';
    public $dateFrom;
    public $dateTo;
    public $status = 'all';
    public $exportFormat = 'excel';

    public $receivablesData;
    public $cashFlowData;
    public $paymentsData;
    public $summaryData = [];

    public $showFilters = false;
    public $selectedSupplier = '';
    public $amountMin;
    public $amountMax;

    public function mount(): void
    {
        if (! Gate::allows('agenda.export')) {
            abort(403, 'Unauthorized access to financial reports');
        }

        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');

        $this->loadReportData();
    }

    public function updatedReportType(): void
    {
        $this->loadReportData();
    }

    public function updatedDateFrom(): void
    {
        $this->loadReportData();
    }

    public function updatedDateTo(): void
    {
        $this->loadReportData();
    }

    public function updatedStatus(): void
    {
        $this->loadReportData();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function loadReportData(): void
    {
        try {
            $dateFrom = Carbon::parse($this->dateFrom)->startOfDay();
            $dateTo = Carbon::parse($this->dateTo)->endOfDay();

            match ($this->reportType) {
                'receivables' => $this->loadReceivablesReport($dateFrom, $dateTo),
                'cashflow' => $this->loadCashFlowReport($dateFrom, $dateTo),
                'payments' => $this->loadPaymentsReport($dateFrom, $dateTo),
                'summary' => $this->loadSummaryReport($dateFrom, $dateTo),
                default => $this->loadReceivablesReport($dateFrom, $dateTo),
            };
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal memuat data laporan: ' . $e->getMessage());
        }
    }

    private function loadReceivablesReport(Carbon $dateFrom, Carbon $dateTo): void
    {
        $query = Receivable::query()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->selectedSupplier) {
            $query->where('customer_name', 'like', '%' . $this->selectedSupplier . '%');
        }

        if ($this->amountMin !== null) {
            $query->where('amount', '>=', $this->amountMin);
        }

        if ($this->amountMax !== null) {
            $query->where('amount', '<=', $this->amountMax);
        }

        $this->receivablesData = $query->orderByDesc('created_at')->get();
    }

    private function loadCashFlowReport(Carbon $dateFrom, Carbon $dateTo): void
    {
        $this->cashFlowData = CashBalance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->orderByDesc('date')
            ->orderByDesc('type')
            ->get();
    }

    private function loadPaymentsReport(Carbon $dateFrom, Carbon $dateTo): void
    {
        $query = PaymentSchedule::with('incomingGoods')
            ->whereBetween('due_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);

        if ($this->status !== 'all') {
            if ($this->status === 'pending') {
                $query->whereIn('status', ['pending', 'partial']);
            } else {
                $query->where('status', $this->status);
            }
        }

        $this->paymentsData = $query->orderBy('due_date')->get();
    }

    private function loadSummaryReport(Carbon $dateFrom, Carbon $dateTo): void
    {
        $receivables = Receivable::whereBetween('created_at', [$dateFrom, $dateTo]);

        $receivableCollection = (clone $receivables)->get();

        $this->summaryData = [
            'total_receivables' => $receivableCollection->sum(fn($rec) => max(0, $rec->amount - $rec->paid_amount)),
            'paid_receivables' => $receivableCollection->where('status', 'paid')->sum('paid_amount'),
            'pending_receivables' => $receivableCollection->whereIn('status', ['pending', 'partial'])->sum(fn($rec) => max(0, $rec->amount - $rec->paid_amount)),
            'overdue_receivables' => $receivableCollection->where('status', 'overdue')->sum(fn($rec) => max(0, $rec->amount - $rec->paid_amount)),
            'total_cash_in' => CashBalance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])->sum('cash_in'),
            'total_cash_out' => CashBalance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])->sum('cash_out'),
            'pending_payments' => PaymentSchedule::whereBetween('due_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->whereIn('status', ['pending', 'partial'])
                ->get()->sum(fn($pay) => max(0, $pay->amount - $pay->paid_amount)),
            'completed_payments' => PaymentSchedule::whereBetween('due_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->where('status', 'paid')
                ->sum('paid_amount'),
        ];
    }

    public function exportReport()
    {
        try {
            if (! Gate::allows('agenda.export')) {
                session()->flash('error', 'Tidak memiliki akses untuk mengekspor laporan');
                return null;
            }

            $filename = $this->reportType . '_report_' . Carbon::now()->format('Y-m-d_H-i-s');

            return $this->exportFormat === 'excel'
                ? $this->exportToCsv($filename)
                : $this->exportToHtml($filename);
        } catch (\Throwable $e) {
            session()->flash('error', 'Export gagal: ' . $e->getMessage());
            return null;
        }
    }

    private function exportToCsv(string $filename)
    {
        $data = $this->getExportData();

        $csv = "Report Type: " . ucfirst($this->reportType) . "\n";
        $csv .= "Date Range: {$this->dateFrom} to {$this->dateTo}\n";
        $csv .= "Generated: " . Carbon::now()->format('Y-m-d H:i:s') . "\n\n";

        if (! empty($data)) {
            $headers = array_keys($data[0]);
            $csv .= implode(',', $headers) . "\n";

            foreach ($data as $row) {
                $csv .= implode(',', array_map(fn($value) => '"' . str_replace('"', '""', $value) . '"', $row)) . "\n";
            }
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
    }

    private function exportToHtml(string $filename)
    {
        $data = $this->getExportData();

        $html = view('exports.financial-report', [
            'reportType' => $this->reportType,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'data' => $data,
            'summaryData' => $this->summaryData,
        ])->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.html"');
    }

    private function getExportData(): array
    {
        return match ($this->reportType) {
            'receivables' => $this->receivablesData->map(function ($item) {
                $remaining = max(0, $item->amount - $item->paid_amount);

                return [
                    'Supplier' => $item->customer_name,
                    'Status' => ucfirst($item->status),
                    'Total' => number_format($item->amount, 0, ',', '.'),
                    'Paid' => number_format($item->paid_amount, 0, ',', '.'),
                    'Outstanding' => number_format($remaining, 0, ',', '.'),
                    'Due Date' => $item->due_date?->format('d/m/Y') ?? '-',
                    'Notes' => $item->notes ?? '-',
                ];
            })->toArray(),
            'cashflow' => $this->cashFlowData->map(function ($item) {
                return [
                    'Date' => $item->date?->format('d/m/Y') ?? '-',
                    'Type' => ucfirst($item->type),
                    'Cash In' => number_format($item->cash_in, 0, ',', '.'),
                    'Cash Out' => number_format($item->cash_out, 0, ',', '.'),
                    'Closing Balance' => number_format($item->closing_balance, 0, ',', '.'),
                    'Description' => $item->description ?? '-',
                ];
            })->toArray(),
            'payments' => $this->paymentsData->map(function ($item) {
                $remaining = max(0, $item->amount - $item->paid_amount);

                return [
                    'Supplier' => $item->incomingGoods->supplier_name ?? 'Supplier',
                    'Invoice' => $item->incomingGoods->invoice_number ?? '-',
                    'Due Date' => $item->due_date?->format('d/m/Y') ?? '-',
                    'Status' => ucfirst($item->status),
                    'Total' => number_format($item->amount, 0, ',', '.'),
                    'Paid' => number_format($item->paid_amount, 0, ',', '.'),
                    'Outstanding' => number_format($remaining, 0, ',', '.'),
                ];
            })->toArray(),
            'summary' => [
                ['Metric' => 'Total Receivables', 'Amount' => number_format($this->summaryData['total_receivables'] ?? 0, 0, ',', '.')],
                ['Metric' => 'Paid Receivables', 'Amount' => number_format($this->summaryData['paid_receivables'] ?? 0, 0, ',', '.')],
                ['Metric' => 'Pending Receivables', 'Amount' => number_format($this->summaryData['pending_receivables'] ?? 0, 0, ',', '.')],
                ['Metric' => 'Overdue Receivables', 'Amount' => number_format($this->summaryData['overdue_receivables'] ?? 0, 0, ',', '.')],
                ['Metric' => 'Total Cash In', 'Amount' => number_format($this->summaryData['total_cash_in'] ?? 0, 0, ',', '.')],
                ['Metric' => 'Total Cash Out', 'Amount' => number_format($this->summaryData['total_cash_out'] ?? 0, 0, ',', '.')],
                ['Metric' => 'Pending Supplier Payments', 'Amount' => number_format($this->summaryData['pending_payments'] ?? 0, 0, ',', '.')],
                ['Metric' => 'Completed Supplier Payments', 'Amount' => number_format($this->summaryData['completed_payments'] ?? 0, 0, ',', '.')],
            ],
            default => [],
        };
    }

    public function render()
    {
        return view('livewire.financial-reports');
    }
}
