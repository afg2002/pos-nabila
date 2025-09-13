<?php

namespace App\Exports;

use App\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMovementExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $dateFrom;
    protected $dateTo;
    protected $productId;
    protected $type;
    protected $refType;
    
    public function __construct($dateFrom = null, $dateTo = null, $productId = null, $type = null, $refType = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->productId = $productId;
        $this->type = $type;
        $this->refType = $refType;
    }
    
    public function collection()
    {
        $query = StockMovement::with(['product', 'user'])
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('created_at', [
                $this->dateFrom . ' 00:00:00', 
                $this->dateTo . ' 23:59:59'
            ]);
        }
        
        if ($this->productId) {
            $query->where('product_id', $this->productId);
        }
        
        if ($this->type) {
            $query->where('type', $this->type);
        }
        
        if ($this->refType) {
            $query->where('ref_type', $this->refType);
        }
        
        return $query->get();
    }
    
    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu',
            'Produk (SKU)',
            'Nama Produk',
            'Tipe',
            'Jumlah',
            'Stok Sebelum',
            'Stok Sesudah',
            'Referensi',
            'ID Referensi',
            'Catatan',
            'User',
            'Dibuat'
        ];
    }
    
    public function map($movement): array
    {
        return [
            $movement->created_at->format('Y-m-d'),
            $movement->created_at->format('H:i:s'),
            $movement->product->sku ?? '-',
            $movement->product->name ?? '-',
            $movement->type === 'IN' ? 'Masuk' : 'Keluar',
            abs($movement->qty),
            $movement->stock_before,
            $movement->stock_after,
            ucfirst($movement->ref_type),
            $movement->ref_id ?? '-',
            $movement->note ?? '-',
            $movement->user->name ?? 'System',
            $movement->created_at->format('Y-m-d H:i:s')
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:M' => ['alignment' => ['horizontal' => 'left']],
        ];
    }
}