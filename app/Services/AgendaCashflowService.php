<?php

namespace App\Services;

use App\Models\IncomingGoodsAgenda;
use App\Sale;
use App\CashLedger;
use App\Models\IncomingGoodsAgenda;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AgendaCashflowService
{
    /**
     * Get daily aggregate totals for agenda view
     */
    public function getDailyTotals($date = null): array
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        
        // Get sales data for the day
        $dailySales = Sale::whereDate('created_at', $date)
            ->where('status', 'PAID')
            ->get();
            
        // Calculate total omset (revenue)
        $totalOmset = $dailySales->sum('final_total');
        
        // Split by sales type (ecer vs grosir based on amount)
        // Assuming grosir is sales > 100000, ecer is <= 100000
        $ecerSales = $dailySales->where('final_total', '<=', 100000);
        $grosirSales = $dailySales->where('final_total', '>', 100000);
        
        $totalEcer = $ecerSales->sum('final_total');
        $totalGrosir = $grosirSales->sum('final_total');
        
        // Split grosir by payment channels
        $grosirChannels = $this->splitGrosirByChannels($grosirSales);
        
        // Get cash ledger data
        $cashLedgerData = $this->getCashLedgerSummary($date);
        
        return [
            'date' => $date->format('Y-m-d'),
            'total_omset' => $totalOmset,
            'total_ecer' => $totalEcer,
            'total_grosir' => $totalGrosir,
            'grosir_channels' => $grosirChannels,
            'cash_ledger' => $cashLedgerData,
            'transaction_count' => $dailySales->count(),
            'ecer_count' => $ecerSales->count(),
            'grosir_count' => $grosirSales->count(),
        ];
    }
    
    /**
     * Split grosir sales by payment channels
     */
    private function splitGrosirByChannels(Collection $grosirSales): array
    {
        $channels = [
            'cash' => 0,
            'qr' => 0,
            'edc' => 0,
        ];
        
        foreach ($grosirSales as $sale) {
            $method = strtolower($sale->payment_method ?? 'cash');
            
            switch ($method) {
                case 'cash':
                    $channels['cash'] += $sale->final_total;
                    break;
                case 'qr':
                case 'qris':
                case 'gopay':
                case 'ovo':
                case 'dana':
                    $channels['qr'] += $sale->final_total;
                    break;
                case 'edc':
                case 'debit':
                case 'credit':
                case 'card':
                    $channels['edc'] += $sale->final_total;
                    break;
                default:
                    $channels['cash'] += $sale->final_total;
            }
        }
        
        return $channels;
    }
    
    /**
     * Get cash ledger summary for the day
     */
    private function getCashLedgerSummary($date): array
    {
        $entries = CashLedger::whereDate('transaction_date', $date)->get();
        
        $income = $entries->where('type', 'in')->sum('amount');
        $expense = $entries->where('type', 'out')->sum('amount');
        
        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net_amount' => $income - $expense,
            'transaction_count' => $entries->count(),
        ];
    }
    
    /**
     * Get weekly aggregate data
     */
    public function getWeeklyTotals($startDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();
        
        $weeklyData = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $weeklyData[] = $this->getDailyTotals($current);
            $current->addDay();
        }
        
        return [
            'week_start' => $startDate->format('Y-m-d'),
            'week_end' => $endDate->format('Y-m-d'),
            'daily_data' => $weeklyData,
            'weekly_summary' => $this->calculateWeeklySummary($weeklyData),
        ];
    }
    
    /**
     * Calculate weekly summary from daily data
     */
    private function calculateWeeklySummary(array $dailyData): array
    {
        $summary = [
            'total_omset' => 0,
            'total_ecer' => 0,
            'total_grosir' => 0,
            'grosir_channels' => ['cash' => 0, 'qr' => 0, 'edc' => 0],
            'cash_ledger' => ['total_income' => 0, 'total_expense' => 0, 'net_amount' => 0],
            'transaction_count' => 0,
        ];
        
        foreach ($dailyData as $day) {
            $summary['total_omset'] += $day['total_omset'];
            $summary['total_ecer'] += $day['total_ecer'];
            $summary['total_grosir'] += $day['total_grosir'];
            $summary['transaction_count'] += $day['transaction_count'];
            
            foreach ($day['grosir_channels'] as $channel => $amount) {
                $summary['grosir_channels'][$channel] += $amount;
            }
            
            $summary['cash_ledger']['total_income'] += $day['cash_ledger']['total_income'];
            $summary['cash_ledger']['total_expense'] += $day['cash_ledger']['total_expense'];
            $summary['cash_ledger']['net_amount'] += $day['cash_ledger']['net_amount'];
        }
        
        return $summary;
    }
    
    /**
     * Get monthly aggregate data
     */
    public function getMonthlyTotals($month = null): array
    {
        $month = $month ? Carbon::parse($month.'-01') : Carbon::now()->startOfMonth();
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        
        $monthlyData = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $monthlyData[] = $this->getDailyTotals($current);
            $current->addDay();
        }
        
        return [
            'month' => $month->format('Y-m'),
            'month_name' => $month->format('F Y'),
            'daily_data' => $monthlyData,
            'monthly_summary' => $this->calculateWeeklySummary($monthlyData), // Same calculation logic
        ];
    }
}