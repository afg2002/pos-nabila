<?php

namespace App\Exports;

use App\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $dateFrom;
    protected $dateTo;
    
    public function __construct($dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }
    
    public function collection()
    {
        return Sale::with(['items.product', 'cashier'])
                  ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
                  ->orderBy('created_at', 'desc')
                  ->get();
    }
    
    public function headings(): array
    {
        return [
            'No. Penjualan',
            'Tanggal',
            'Pelanggan',
            'Telepon',
            'Subtotal',
            'Diskon',
            'Total',
            'Metode Bayar',
            'Jumlah Bayar',
            'Kembalian',
            'Kasir',
            'Catatan'
        ];
    }
    
    public function map($sale): array
    {
        return [
            $sale->sale_number,
            $sale->created_at->format('d/m/Y H:i'),
            $sale->customer_name ?? '-',
            $sale->customer_phone ?? '-',
            $sale->subtotal,
            $sale->discount_amount,
            $sale->final_total,
            ucfirst($sale->payment_method),
            $sale->amount_paid,
            $sale->change_amount,
            $sale->cashier->name ?? 'System',
            $sale->notes ?? '-'
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}