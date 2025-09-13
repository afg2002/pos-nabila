<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        return Product::with(['stockMovements' => function($query) {
            $query->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
        }])->where('is_active', true)->get();
    }
    
    public function headings(): array
    {
        return [
            'SKU',
            'Barcode',
            'Nama Produk',
            'Kategori',
            'Unit',
            'Stok Saat Ini',
            'Harga Beli',
            'Harga Jual',
            'Harga Grosir',
            'Margin Min (%)',
            'Status',
            'Stok Masuk (Periode)',
            'Stok Keluar (Periode)'
        ];
    }
    
    public function map($product): array
    {
        $stockIn = $product->stockMovements->where('type', 'IN')->sum('qty');
            $stockOut = $product->stockMovements->where('type', 'OUT')->sum('qty');
        
        return [
            $product->sku,
            $product->barcode,
            $product->name,
            $product->category,
            $product->unit,
            $product->current_stock,
            $product->base_cost,
            $product->price_retail,
            $product->price_grosir,
            $product->min_margin_pct,
            $product->is_active ? 'Aktif' : 'Tidak Aktif',
            $stockIn,
            $stockOut
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}