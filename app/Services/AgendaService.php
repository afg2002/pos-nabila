<?php

namespace App\Services;

use App\Models\IncomingGoodsAgenda;
use App\Models\CashflowAgenda;
use App\Models\SalesInvoice;
use App\Models\InvoicePayment;
use App\Models\CapitalTracking;
use App\Models\CashLedger;
use App\Models\BatchExpiration;
use Carbon\Carbon;

class AgendaService
{
    /**
     * Create purchase order agenda with simplified input
     */
    public function createPurchaseOrderAgenda(array $data): IncomingGoodsAgenda
    {
        $agenda = IncomingGoodsAgenda::create([
            'supplier_id' => $data['supplier_id'],
            'total_quantity' => $data['total_quantity'],
            'quantity_unit' => $data['quantity_unit'],
            'total_purchase_amount' => $data['total_purchase_amount'],
            'scheduled_date' => $data['scheduled_date'],
            'payment_due_date' => $data['payment_due_date'],
            'expired_date' => $data['expired_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'scheduled',
            'payment_status' => 'unpaid',
            'created_by' => auth()->id(),
        ]);

        // Auto-generate batch number if expired date is set
        if ($data['expired_date']) {
            $batchNumber = 'BATCH-' . date('Ymd') . '-' . uniqid();
            $agenda->update(['batch_number' => $batchNumber]);
            
            // Create batch expiration record
            BatchExpiration::create([
                'incoming_goods_agenda_id' => $agenda->id,
                'batch_number' => $batchNumber,
                'expired_date' => $data['expired_date'],
                'quantity' => $data['total_quantity'],
                'remaining_quantity' => $data['total_quantity'],
                'created_by' => auth()->id(),
            ]);
        }

        // Auto-generate PO if requested
        if ($data['auto_generate_po'] ?? true) {
            $agenda->autoGeneratePurchaseOrder();
        }

        return $agenda;
    }

    /**
     * Update cashflow from sale payment
     */
    public function updateCashflowFromSale(SalesInvoice $invoice): void
    {
        $today = now()->format('Y-m-d');
        
        // Find or create cashflow agenda for today
        $cashflow = CashflowAgenda::firstOrCreate([
            'date' => $today,
            'capital_tracking_id' => $this->getDefaultCapitalTracking(),
        ], [
            'total_omset' => 0,
            'total_ecer' => 0,
            'total_grosir' => 0,
            'grosir_cash_hari_ini' => 0,
            'qr_payment_amount' => 0,
            'edc_payment_amount' => 0,
            'total_expenses' => 0,
        ]);

        // Update payment method totals
        $this->updateCashflowPaymentMethods($cashflow, $invoice);
        
        // Update total omset
        $this->recalculateCashflowTotals($cashflow);
        
        // Create cash ledger entries
        $this->createCashLedgerFromInvoice($invoice);
    }

    /**
     * Update cashflow payment methods from invoice
     */
    private function updateCashflowPaymentMethods(CashflowAgenda $cashflow, SalesInvoice $invoice): void
    {
        foreach ($invoice->payments as $payment) {
            switch ($payment->payment_method) {
                case 'cash':
                    $cashflow->increment('grosir_cash_hari_ini', $payment->amount);
                    break;
                case 'qr':
                    $cashflow->increment('qr_payment_amount', $payment->amount);
                    break;
                case 'edc':
                    $cashflow->increment('edc_payment_amount', $payment->amount);
                    break;
            }
        }
    }

    /**
     * Recalculate cashflow totals
     */
    private function recalculateCashflowTotals(CashflowAgenda $cashflow): void
    {
        // Update total omset
        $cashflow->total_omset = $cashflow->total_ecer + $cashflow->total_grosir;
        
        // Update total payments
        $totalPayments = $cashflow->grosir_cash_hari_ini + $cashflow->qr_payment_amount + $cashflow->edc_payment_amount;
        
        // Update grosir based on payments
        $cashflow->total_grosir = $totalPayments;
        
        $cashflow->save();
    }

    /**
     * Create cash ledger entries from invoice
     */
    private function createCashLedgerFromInvoice(SalesInvoice $invoice): void
    {
        foreach ($invoice->payments as $payment) {
            CashLedger::create([
                'date' => $payment->payment_date->format('Y-m-d'),
                'type' => 'income',
                'category' => 'sales_payment',
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'description' => "Pembayaran Invoice {$invoice->invoice_number} - {$invoice->customer_name}",
                'reference_id' => $payment->id,
                'reference_type' => 'invoice_payment',
                'cashflow_agenda_id' => $this->getCashflowAgendaForDate($payment->payment_date),
                'created_by' => $payment->created_by,
            ]);
        }
    }

    /**
     * Update cashflow from agenda payment
     */
    public function updateCashflowFromAgendaPayment(IncomingGoodsAgenda $agenda, float $amount, string $method): void
    {
        $date = $agenda->payment_due_date->format('Y-m-d');
        
        // Find or create cashflow agenda for the date
        $cashflow = CashflowAgenda::firstOrCreate([
            'date' => $date,
            'capital_tracking_id' => $this->getDefaultCapitalTracking(),
        ], [
            'total_omset' => 0,
            'total_ecer' => 0,
            'total_grosir' => 0,
            'grosir_cash_hari_ini' => 0,
            'qr_payment_amount' => 0,
            'edc_payment_amount' => 0,
            'total_expenses' => 0,
        ]);

        // Add to expenses (this is a payment to supplier)
        $cashflow->increment('total_expenses', $amount);
        
        // Update net cashflow
        $cashflow->net_cashflow = $cashflow->total_omset - $cashflow->total_expenses;
        $cashflow->save();
        
        // Create cash ledger entry
        CashLedger::create([
            'date' => $date,
            'type' => 'expense',
            'category' => 'supplier_payment',
            'amount' => $amount,
            'payment_method' => $method,
            'description' => "Pembayaran Supplier {$agenda->effective_supplier_name} - PO: {$agenda->po_number}",
            'reference_id' => $agenda->id,
            'reference_type' => 'incoming_goods_agenda',
            'cashflow_agenda_id' => $cashflow->id,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Get annual cashflow summary
     */
    public function getAnnualCashflowSummary(int $year = null): array
    {
        $year = $year ?? now()->year;
        $annualData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthData = CashflowAgenda::whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();
            
            $annualData[] = [
                'month' => $month,
                'month_name' => Carbon::create($year, $month, 1)->format('F'),
                'revenue' => $monthData->sum('total_omset'),
                'expenses' => $monthData->sum('total_expenses'),
                'net_cashflow' => $monthData->sum('total_omset') - $monthData->sum('total_expenses'),
                'cash_payments' => $monthData->sum('grosir_cash_hari_ini'),
                'qr_payments' => $monthData->sum('qr_payment_amount'),
                'edc_payments' => $monthData->sum('edc_payment_amount'),
                'total_payments' => $monthData->sum('grosir_cash_hari_ini') + $monthData->sum('qr_payment_amount') + $monthData->sum('edc_payment_amount'),
            ];
        }
        
        return $annualData;
    }

    /**
     * Get buku kas (cash book) summary for a period
     */
    public function getCashBookSummary(string $startDate, string $endDate): array
    {
        $cashflows = CashflowAgenda::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
        
        $summary = [
            'period_start' => $startDate,
            'period_end' => $endDate,
            'total_revenue' => 0,
            'total_expenses' => 0,
            'net_cashflow' => 0,
            'cash_payments' => 0,
            'qr_payments' => 0,
            'edc_payments' => 0,
            'total_payments' => 0,
            'daily_data' => [],
        ];
        
        foreach ($cashflows as $cashflow) {
            $dayData = [
                'date' => $cashflow->date,
                'revenue' => $cashflow->total_omset,
                'expenses' => $cashflow->total_expenses,
                'net_cashflow' => $cashflow->total_omset - $cashflow->total_expenses,
                'cash_payments' => $cashflow->grosir_cash_hari_ini,
                'qr_payments' => $cashflow->qr_payment_amount,
                'edc_payments' => $cashflow->edc_payment_amount,
                'total_payments' => $cashflow->grosir_cash_hari_ini + $cashflow->qr_payment_amount + $cashflow->edc_payment_amount,
            ];
            
            $summary['daily_data'][] = $dayData;
            $summary['total_revenue'] += $dayData['revenue'];
            $summary['total_expenses'] += $dayData['expenses'];
            $summary['cash_payments'] += $dayData['cash_payments'];
            $summary['qr_payments'] += $dayData['qr_payments'];
            $summary['edc_payments'] += $dayData['edc_payments'];
            $summary['total_payments'] += $dayData['total_payments'];
        }
        
        $summary['net_cashflow'] = $summary['total_revenue'] - $summary['total_expenses'];
        
        return $summary;
    }

    /**
     * Get expiring batches report
     */
    public function getExpiringBatchesReport(int $days = 30): array
    {
        $expiringBatches = BatchExpiration::with(['incomingGoodsAgenda.supplier'])
            ->whereBetween('expired_date', [today(), today()->addDays($days)])
            ->where('remaining_quantity', '>', 0)
            ->orderBy('expired_date')
            ->get();
        
        $report = [
            'period_days' => $days,
            'total_batches' => $expiringBatches->count(),
            'total_value' => 0,
            'expired_count' => 0,
            'expiring_soon_count' => 0,
            'batches' => [],
        ];
        
        foreach ($expiringBatches as $batch) {
            $batchData = [
                'batch_number' => $batch->batch_number,
                'expired_date' => $batch->expired_date->format('Y-m-d'),
                'days_until_expiration' => $batch->days_until_expiration,
                'quantity' => $batch->quantity,
                'remaining_quantity' => $batch->remaining_quantity,
                'supplier_name' => $batch->incomingGoodsAgenda->effective_supplier_name,
                'status' => $batch->expiration_status,
            ];
            
            $report['batches'][] = $batchData;
            
            if ($batch->is_expired) {
                $report['expired_count']++;
            } else {
                $report['expiring_soon_count']++;
            }
            
            // Calculate value (would need product price data)
            // $report['total_value'] += $batch->remaining_quantity * $productPrice;
        }
        
        return $report;
    }

    /**
     * Get default capital tracking
     */
    private function getDefaultCapitalTracking(): int
    {
        return CapitalTracking::where('is_active', true)->first()->id ?? 1;
    }

    /**
     * Get cashflow agenda for date
     */
    private function getCashflowAgendaForDate($date): ?int
    {
        $cashflow = CashflowAgenda::whereDate('date', $date)->first();
        return $cashflow?->id;
    }

    /**
     * Sync agenda with capital tracking
     */
    public function syncAgendaWithCapitalTracking(IncomingGoodsAgenda $agenda): void
    {
        $capitalTracking = $this->getDefaultCapitalTracking();
        
        // Create capital tracking entry for the agenda
        $agenda->capitalTrackings()->create([
            'capital_tracking_id' => $capitalTracking,
            'type' => 'expense',
            'amount' => $agenda->effective_total_amount,
            'description' => "Purchase Order {$agenda->po_number} - {$agenda->effective_supplier_name}",
            'date' => $agenda->payment_due_date,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Generate cashflow report for dashboard
     */
    public function generateCashflowReport(string $period = 'month'): array
    {
        $endDate = now();
        $startDate = null;
        
        switch ($period) {
            case 'week':
                $startDate = now()->subWeek();
                break;
            case 'month':
                $startDate = now()->subMonth();
                break;
            case 'quarter':
                $startDate = now()->subQuarter();
                break;
            case 'year':
                $startDate = now()->subYear();
                break;
            default:
                $startDate = now()->subMonth();
        }
        
        return $this->getCashBookSummary(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );
    }
}