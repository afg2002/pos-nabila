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
                '', // SKU optional (auto-generate when empty)
                'Contoh Produk 1',
                'Elektronik',
                100, // Stok awal di gudang toko
                60000, // Harga Pokok
                75000, // Harga Retail
                70000, // Harga Semi Grosir
                65000, // Harga Grosir
                'Retail', // Jenis Harga default
                'Aktif'
            ],
            [
                'PROD002',
                'Contoh Produk 2',
                'Fashion',
                50,
                32000, // Harga Pokok
                40000,
                38000,
                35000,
                'Grosir',
                'Aktif'
            ]
        ];
    }
    
    public function headings(): array
    {
        return [
            'SKU', // optional
            'Nama Produk*',
            'Kategori',
            'Stok Awal',
            'Harga Pokok',
            'Harga Retail*',
            'Harga Semi Grosir',
            'Harga Grosir',
            'Jenis Harga*',
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
            'A:J' => ['alignment' => ['horizontal' => 'left']],
        ];
    }
}