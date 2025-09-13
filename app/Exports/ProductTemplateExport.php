<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [
                'PROD001',
                '1234567890123',
                'Contoh Produk 1',
                'Elektronik',
                'Pcs',
                100,
                50000,
                75000,
                65000,
                10,
                'Aktif'
            ],
            [
                'PROD002',
                '1234567890124',
                'Contoh Produk 2',
                'Fashion',
                'Pcs',
                50,
                25000,
                40000,
                35000,
                15,
                'Aktif'
            ]
        ];
    }
    
    public function headings(): array
    {
        return [
            'SKU*',
            'Barcode',
            'Nama Produk*',
            'Kategori',
            'Unit*',
            'Stok Awal',
            'Harga Beli*',
            'Harga Jual*',
            'Harga Grosir',
            'Margin Min (%)',
            'Status*'
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ]
            ],
            'A:K' => ['alignment' => ['horizontal' => 'left']],
        ];
    }
}