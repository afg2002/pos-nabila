<?php

namespace App\Imports;

use App\Product;
use App\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skipCount = 0;
    
    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header row and 0-based index
                
                // Validate required fields
                $validator = Validator::make($row->toArray(), [
                    'sku' => 'required|string|max:50',
                    'nama_produk' => 'required|string|max:255',
                    'unit' => 'required|string|max:20',
                    'harga_beli' => 'required|numeric|min:0',
                    'harga_jual' => 'required|numeric|min:0',
                    'status' => 'required|in:Aktif,Tidak Aktif,aktif,tidak aktif,AKTIF,TIDAK AKTIF'
                ]);
                
                if ($validator->fails()) {
                    $this->errors[] = "Baris {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    $this->skipCount++;
                    continue;
                }
                
                // Check if product already exists
                $existingProduct = Product::where('sku', $row['sku'])->first();
                
                if ($existingProduct) {
                    $this->errors[] = "Baris {$rowNumber}: Produk dengan SKU '{$row['sku']}' sudah ada";
                    $this->skipCount++;
                    continue;
                }
                
                // Find unit by name or create default
                $unitId = 1; // Default to 'Pieces'
                if (!empty($row['unit'])) {
                    $unit = \App\ProductUnit::where('name', 'LIKE', '%' . trim($row['unit']) . '%')
                                            ->orWhere('abbreviation', 'LIKE', '%' . trim($row['unit']) . '%')
                                            ->first();
                    if ($unit) {
                        $unitId = $unit->id;
                    }
                }
                
                // Create product
                $product = Product::create([
                    'sku' => $row['sku'],
                    'barcode' => $row['barcode'] ?? null,
                    'name' => $row['nama_produk'],
                    'category' => $row['kategori'] ?? 'Umum',
                    'unit_id' => $unitId,
                    'current_stock' => $row['stok_awal'] ?? 0,
                    'base_cost' => $row['harga_beli'],
                    'price_retail' => $row['harga_jual'],
                    'price_grosir' => $row['harga_grosir'] ?? $row['harga_jual'],
                    'min_margin_pct' => $row['margin_min'] ?? 0,
                    'status' => in_array(strtolower($row['status']), ['aktif', 'active']) ? 'active' : 'inactive',
                ]);
                
                // Create initial stock movement if stock > 0
                if (($row['stok_awal'] ?? 0) > 0) {
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'IN',
                        'qty' => $row['stok_awal'],
                        'stock_before' => 0,
                        'stock_after' => $row['stok_awal'],
                        'ref_type' => 'import',
                        'ref_id' => null,
                        'note' => 'Stok awal dari import Excel',
                        'performed_by' => auth()->id(),
                    ]);
                }
                
                $this->successCount++;
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = 'Error sistem: ' . $e->getMessage();
        }
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getSuccessCount()
    {
        return $this->successCount;
    }
    
    public function getSkipCount()
    {
        return $this->skipCount;
    }
}