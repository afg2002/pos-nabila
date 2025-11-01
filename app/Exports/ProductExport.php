<?php

namespace App\Exports;

use App\Warehouse;
use App\ProductWarehouseStock;
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
            'Nama Produk',
            'Kategori',
            'Stok',
            'Harga Retail',
            'Harga Semi Grosir',
            'Harga Grosir',
            'Jenis Harga',
            'Status',
        ];
    }
    
    public function map($product): array
    {
        // Ambil stok untuk gudang default (stok toko)
        $defaultWarehouse = Warehouse::getDefault();
        $storeStock = 0;
        if ($defaultWarehouse) {
            $pws = ProductWarehouseStock::query()
                ->where('product_id', $product->id)
                ->where('warehouse_id', $defaultWarehouse->id)
                ->first();
            $storeStock = (int)($pws->stock_on_hand ?? 0);
        }
        
        // Jenis harga human readable
        $priceTypes = \App\Product::getPriceTypes();
        $jenisHarga = $priceTypes[$product->default_price_type] ?? 'Retail';
        
        return [
            $product->sku,
            $product->name,
            $product->category,
            $storeStock,
            (float)($product->price_retail ?? 0),
            (float)($product->price_semi_grosir ?? 0),
            (float)($product->price_grosir ?? 0),
            $jenisHarga,
            method_exists($product, 'getStatusDisplayName') ? $product->getStatusDisplayName() : ($product->status === 'active' ? 'Aktif' : 'Tidak Aktif'),
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}