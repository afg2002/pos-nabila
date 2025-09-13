<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $products;
    
    public function __construct(Collection $products)
    {
        $this->products = $products;
    }
    
    public function collection()
    {
        return $this->products;
    }
    
    public function headings(): array
    {
        return [
            'SKU',
            'Barcode',
            'Nama Produk',
            'Kategori',
            'Unit',
            'Harga Pokok',
            'Harga Retail',
            'Harga Grosir',
            'Min Margin (%)',
            'Status',
            'Stok Saat Ini',
            'Tanggal Dibuat',
            'Terakhir Diupdate'
        ];
    }
    
    public function map($product): array
    {
        // Hitung stok saat ini
        $currentStock = $product->stockMovements()
            ->selectRaw('SUM(CASE WHEN type = "in" THEN quantity ELSE -quantity END) as current_stock')
            ->value('current_stock') ?? 0;
            
        return [
            $product->sku,
            $product->barcode,
            $product->name,
            $product->category,
            $product->unit,
            number_format($product->base_cost, 0, ',', '.'),
            number_format($product->price_retail, 0, ',', '.'),
            number_format($product->price_grosir, 0, ',', '.'),
            $product->min_margin_pct . '%',
            $product->is_active ? 'Aktif' : 'Tidak Aktif',
            number_format($currentStock, 0, ',', '.'),
            $product->created_at->format('d/m/Y H:i'),
            $product->updated_at->format('d/m/Y H:i')
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}