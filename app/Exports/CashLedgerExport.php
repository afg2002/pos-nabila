<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashLedgerExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $cashLedgers;

    public function __construct($cashLedgers)
    {
        $this->cashLedgers = $cashLedgers;
    }

    public function collection()
    {
        return $this->cashLedgers;
    }

    public function headings(): array
    {
        return [
            'Tanggal Transaksi',
            'Tipe',
            'Kategori',
            'Deskripsi',
            'Jumlah',
            'Modal Tracking',
            'Gudang/Cabang',
            'Catatan',
            'Dibuat Oleh',
            'Tanggal Dibuat'
        ];
    }

    public function map($cashLedger): array
    {
        return [
            $cashLedger->transaction_date->format('d/m/Y'),
            $cashLedger->type === 'in' ? 'Pemasukan' : 'Pengeluaran',
            $cashLedger->category,
            $cashLedger->description,
            'Rp ' . number_format($cashLedger->amount, 0, ',', '.'),
            $cashLedger->capitalTracking->name ?? '-',
            $cashLedger->warehouse ? $cashLedger->warehouse->name . ($cashLedger->warehouse->branch ? ' - ' . $cashLedger->warehouse->branch : '') : '-',
            $cashLedger->notes ?? '-',
            $cashLedger->created_by_name ?? 'System',
            $cashLedger->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}