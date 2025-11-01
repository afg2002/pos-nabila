<?php

namespace App\Imports;

use App\Product;
use App\ProductUnit;
use App\ProductUnitScale;
use App\StockMovement;
use App\Warehouse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class ProductImport implements ToCollection
{
    // Track results so Livewire can show feedback
    protected array $errors = [];
    protected array $warnings = [];
    protected int $successCount = 0;
    protected int $skipCount = 0;
    // Audit trail dan preview
    protected array $skippedDetails = [];
    protected array $parsedRows = [];
    protected int $analyzedCount = 0;
    protected ?array $allowedIndexes = null; // indeks baris yang diizinkan untuk diimport (berbasis 0)
    // Tambahan: mode pra-validasi & validasi ketat + ringkasan kategori error
    protected bool $validateOnly = false;
    protected bool $strictPricing = false;
    protected bool $strictMandatory = false; // untuk field kritis seperti unit
    protected array $errorCategories = [
        'invalid_unit' => 0,
        'price_missing_retail' => 0,
        'price_missing_semi' => 0,
        'price_missing_grosir' => 0,
        'price_inconsistent' => 0,
        'format_error' => 0,
        'other' => 0,
        'empty_row' => 0,
    ];
    // Deteksi bentuk template (dengan/ tanpa kolom SKU di paling kiri)
    protected bool $hasSkuColumn = true;
    // Header Excel untuk preview dinamis
    protected array $headers = [];
    // Konfigurasi unit global (diterapkan ke semua produk hasil impor)
    protected ?int $globalDefaultUnitId = null;
    protected array $globalUnitScales = [];
    protected array $perRowUnitConfigs = [];

    public function __construct()
    {
        // Dapat diatur via env saat belum ada kontrol dari UI Livewire
        $this->strictPricing = (bool) env('IMPORT_STRICT_PRICING', false);
        $this->validateOnly = (bool) env('IMPORT_VALIDATE_ONLY', false);
        $this->strictMandatory = (bool) env('IMPORT_STRICT_MANDATORY', false);

    }

    public function collection(Collection $rows)
    {
        // Jalankan transaksi hanya saat benar-benar melakukan insert
        if (! $this->validateOnly) {
            DB::beginTransaction();
        }
        try {
            foreach ($rows as $index => $row) {
                // Expecting headers: [SKU?,] Nama Produk*, Kategori, Stok Awal, Harga Pokok, Harga Retail*, Harga Semi Grosir, Harga Grosir, Jenis Harga*, Status*
                if ($index === 0 && $this->isHeaderRow($row)) {
                    Log::info('[Import] Header row detected and skipped from processing');
                    // Deteksi bentuk template dari header
                    $this->hasSkuColumn = $this->detectHasSkuColumnFromHeader($row);
                    // Set header normalized agar preview mengikuti template, bukan jumlah kolom mentah
                    $this->headers = $this->normalizedTemplateHeaders();
                    // Audit trail
                    $this->skippedDetails[] = 'Baris header dilewati (deteksi kolom template).';
                    continue;
                }

                $data = $this->mapRow($row);

                // Pra-validasi: deteksi masalah sebelum insert
                $pre = $this->preValidateRow($data, $index);
                if ($this->validateOnly) {
                    // Abaikan baris kosong sepenuhnya dari preview & statistik
                    if (($pre['empty'] ?? false) === true) {
                        continue; // jangan tambah analyzedCount, jangan push parsedRows
                    }
                    // Mode preview: kumpulkan ringkasan baris tanpa insert
                    $this->analyzedCount++;
                    $statusPreview = ($pre['critical'] ?? false) ? 'error' : 'valid';
                    $this->parsedRows[] = [
                        'index' => $index,
                        'sku' => $data['sku'] ?? null,
                        'nama' => $data['nama_produk'] ?? null,
                        'nama_produk' => $data['nama_produk'] ?? null,
                        'kategori' => $data['kategori'] ?? null,
                        'stok' => $data['current_stock'] ?? 0,
                        'harga_beli' => $data['base_cost'] ?? 0,
                        'harga_pokok' => $data['base_cost'] ?? 0,
                        'harga_retail' => $data['price_retail'] ?? 0,
                        'harga_jual' => $data['price_retail'] ?? 0,
                        'harga_semi_grosir' => $data['price_semi_grosir'] ?? 0,
                        'harga_grosir' => $data['price_grosir'] ?? 0,
                        'jenis_harga' => $data['price_type'] ?? 'retail',
                        'status_produk' => $data['status'] ?? 'active',
                        'status' => $statusPreview,
                        'errors' => $this->errors,
                        // Nilai mentah per kolom untuk render preview dinamis
                        'cells' => array_values((array) $row->toArray()),
                    ];
                    if ($statusPreview === 'error') {
                        $this->skippedDetails[] = 'Baris #'.($index+1).': Ditandai error pada pra-validasi (akan dilewati jika impor final).';
                    }
                    continue;
                }
                if ($pre['critical'] ?? false) {
                    // Jika ada error kritis (mis. invalid unit saat strictMandatory/strictPricing), lewati baris
                    $this->skipCount++;
                    $this->skippedDetails[] = 'Baris #'.($index+1).': Dilewati karena error kritis pada pra-validasi.';
                    continue;
                }

                // Decide final SKU
                $inputSku = $data['sku'] ?? null;
                $finalSku = null;

                // If user provided SKU exists, replace with short UUID-like SKU to guarantee uniqueness and keep it short
                if ($inputSku && Product::where('sku', $inputSku)->exists()) {
                    $finalSku = Product::generateSkuShort($data['kategori'] ?? null, $data['nama_produk'] ?? null, 6);
                    $this->errors[] = "SKU '{$inputSku}' sudah ada, diganti otomatis menjadi '{$finalSku}'.";
                    Log::warning("[Import SKU] Duplicate provided SKU '{$inputSku}', replaced with '{$finalSku}'");
                }

                // If user didn't provide SKU, or provided SKU is new, still normalize to short unique SKU to avoid future collisions
                if (! $finalSku) {
                    // Guard: if provided SKU exists, we already handled above; otherwise keep provided SKU if truly unique
                    if ($inputSku && ! Product::where('sku', $inputSku)->exists()) {
                        $finalSku = $inputSku;
                    } else {
                        $finalSku = Product::generateSkuShort($data['kategori'] ?? null, $data['nama_produk'] ?? null, 6);
                    }
                }

                // Pre-insert uniqueness guard loop (covers extremely rare race conditions)
                $guardAttempts = 0;
                while (Product::where('sku', $finalSku)->exists() && $guardAttempts < 10) {
                    $finalSku = Product::generateSkuShort($data['kategori'] ?? null, $data['nama_produk'] ?? null, 6);
                    $guardAttempts++;
                }

                // Resolve unit_id to an existing ProductUnit ID. Fallback ke konfigurasi global jika tersedia, jika tidak ke default.
                $resolvedUnitId = $this->resolveUnitId($data['unit_id'] ?? null, $index);

                // Normalize enum-ish fields to valid values
                $priceType = $this->normalizePriceType($data['default_price_type'] ?? 'retail');
                $status = $this->normalizeStatus($data['status'] ?? 'active');

                // Ensure prices/base_cost are never NULL to satisfy DB constraints; apply friendly fallbacks
                $baseCost = $this->resolveBaseCostOrFallback($data['base_cost'] ?? null, $data['price_retail'] ?? null, $index);
                $priceRetail = $this->resolveRetailOrFallback($data['price_retail'] ?? null, $baseCost, $index);
                $priceSemi = $this->resolvePriceOrFallback($data['price_semi_grosir'] ?? null, $priceRetail, $baseCost, 'Semi Grosir', $index);
                $priceGrosir = $this->resolvePriceOrFallback($data['price_grosir'] ?? null, $priceRetail, $baseCost, 'Grosir', $index);

                // Pemeriksaan konsistensi harga setelah penetapan nilai
                if (!$this->isPriceConsistent($priceRetail, $priceSemi, $priceGrosir)) {
                    $this->errorCategories['price_inconsistent']++;
                    if ($this->strictPricing) {
                        $this->errors[] = 'Baris #'.($index+1).": Harga tidak konsisten (harus Retail >= Semi >= Grosir). Baris dilewati karena strictPricing aktif.";
                        $this->skipCount++;
                        $this->skippedDetails[] = 'Baris #'.($index+1).': Dilewati karena harga tidak konsisten (strictPricing).';
                        continue;
                    }
                    // Mode non-strict: catat kategori saja, tanpa duplikasi pesan per baris
                }

                // Filter berdasarkan pilihan user pada modal konfirmasi (jika ada)
                if (is_array($this->allowedIndexes) && !in_array($index, $this->allowedIndexes)) {
                    $this->skipCount++;
                    $this->skippedDetails[] = 'Baris #'.($index+1).': Tidak dipilih pada konfirmasi.';
                    continue;
                }

                try {
                    $product = Product::create([
                        'sku' => $finalSku,
                        'name' => $data['nama_produk'] ?? null,
                        'category' => $data['kategori'] ?? null,
                        'unit_id' => $resolvedUnitId,
                        'base_cost' => $baseCost,
                        'price_retail' => $priceRetail,
                        'price_semi_grosir' => $priceSemi,
                        'price_grosir' => $priceGrosir,
                        'default_price_type' => $priceType,
                        'current_stock' => 0,
                        'status' => $status,
                    ]);

                    // Apply initial stock to default warehouse via ADJUSTMENT movement
                    $initialStock = (int) ($data['current_stock'] ?? 0);
                    if ($initialStock > 0) {
                        StockMovement::createMovement(
                            $product->id,
                            $initialStock,
                            'ADJ',
                            [
                                'stock_after' => $initialStock,
                                'warehouse_id' => $this->resolveTargetWarehouseId(),
                            ]
                        );
                    }

                    // Terapkan konfigurasi multi-satuan (N) jika diberikan
                    if (is_array($this->globalUnitScales) && !empty($this->globalUnitScales)) {
                        foreach ($this->globalUnitScales as $scale) {
                            $unitId = $this->normalizeInt($scale['unit_id'] ?? null);
                            $qty    = $this->normalizeInt($scale['to_base_qty'] ?? null);
                            if ($unitId && $qty && ProductUnit::whereKey($unitId)->exists()) {
                                $existing = ProductUnitScale::where('product_id', $product->id)
                                    ->where('unit_id', $unitId)
                                    ->first();
                                if ($existing) {
                                    $existing->to_base_qty = $qty;
                                    $existing->notes = $scale['notes'] ?? $existing->notes;
                                    $existing->save();
                                } else {
                                    ProductUnitScale::create([
                                        'product_id'  => $product->id,
                                        'unit_id'     => $unitId,
                                        'to_base_qty' => $qty,
                                        'notes'       => $scale['notes'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }

                    // Terapkan konfigurasi multi-satuan per baris (override) jika ada
                    if (isset($this->perRowUnitConfigs[$index]['scales']) && is_array($this->perRowUnitConfigs[$index]['scales'])) {
                        foreach ($this->perRowUnitConfigs[$index]['scales'] as $scale) {
                            $unitId = $this->normalizeInt($scale['unit_id'] ?? null);
                            $qty    = $this->normalizeInt($scale['to_base_qty'] ?? null);
                            if ($unitId && $qty && ProductUnit::whereKey($unitId)->exists()) {
                                $existing = ProductUnitScale::where('product_id', $product->id)
                                    ->where('unit_id', $unitId)
                                    ->first();
                                if ($existing) {
                                    $existing->to_base_qty = $qty;
                                    $existing->notes = $scale['notes'] ?? $existing->notes;
                                    $existing->save();
                                } else {
                                    ProductUnitScale::create([
                                        'product_id'  => $product->id,
                                        'unit_id'     => $unitId,
                                        'to_base_qty' => $qty,
                                        'notes'       => $scale['notes'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }

                    Log::info("[Import SKU] Created product with SKU '{$product->sku}' (row #".($index+1).")");
                    $this->successCount++;
                } catch (QueryException $e) {
                    if ($this->isDuplicateKeyException($e)) {
                        Log::error("[Import SKU] Duplicate key on insert for SKU '{$finalSku}'. Retrying with a new short SKU.");
                        // Retry once with a fresh short unique SKU
                        $retrySku = Product::generateSkuShort($data['kategori'] ?? null, $data['nama_produk'] ?? null, 6);
                        $product = Product::create([
                            'sku' => $retrySku,
                            'name' => $data['nama_produk'] ?? null,
                            'category' => $data['kategori'] ?? null,
                            'unit_id' => $resolvedUnitId,
                            'base_cost' => $baseCost,
                            'price_retail' => $priceRetail,
                            'price_semi_grosir' => $priceSemi,
                            'price_grosir' => $priceGrosir,
                            'default_price_type' => $priceType,
                            'current_stock' => 0,
                            'status' => $status,
                        ]);
                        // Apply initial stock to default warehouse via ADJUSTMENT movement
                        $initialStock = (int) ($data['current_stock'] ?? 0);
                        if ($initialStock > 0) {
                            StockMovement::createMovement(
                                $product->id,
                                $initialStock,
                                'ADJ',
                                [
                                    'stock_after' => $initialStock,
                                    'warehouse_id' => $this->resolveTargetWarehouseId(),
                                ]
                            );
                        }
                        Log::info("[Import SKU] Retry create succeeded with SKU '{$product->sku}'");
                        $this->successCount++;
                        $this->errors[] = "Terjadi duplikasi SKU saat menyimpan, SKU diganti otomatis menjadi '{$product->sku}'.";
                    } else {
                        $this->errors[] = 'Gagal membuat produk pada baris #'.($index+1).': '.$e->getMessage();
                        throw $e;
                    }
                }
            }

            if (! $this->validateOnly) {
                DB::commit();
            }
        } catch (\Throwable $th) {
            if (! $this->validateOnly) {
                DB::rollBack();
            }
            Log::error('[Import] Failed: '.$th->getMessage(), ['trace' => $th->getTraceAsString()]);
            $this->errors[] = 'Kesalahan sistem: '.$th->getMessage();
            if (! $this->validateOnly) {
                throw $th;
            }
        }
    }

    // Expose results to Livewire component
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getSkipCount(): int
    {
        return $this->skipCount;
    }

    public function getErrorCategorySummary(): array
    {
        return $this->errorCategories;
    }

    public function setValidateOnly(bool $value): void { $this->validateOnly = $value; }
    public function setStrictPricing(bool $value): void { $this->strictPricing = $value; }
    public function setStrictMandatory(bool $value): void { $this->strictMandatory = $value; }
    // Preview & audit getters
    public function getSkippedDetails(): array { return $this->skippedDetails; }
    public function getParsedRows(): array { return $this->parsedRows; }
    public function getAnalyzedCount(): int { return $this->analyzedCount; }
    public function setAllowedIndexes(?array $indexes): void { $this->allowedIndexes = $indexes; }
    public function getHeaders(): array { return $this->headers; }
    public function setGlobalUnitConfig(?int $defaultUnitId, array $scales = []): void {
        $this->globalDefaultUnitId = $defaultUnitId;
        $this->globalUnitScales = $scales;
    }

    private function recordCategory(string $key): void
    {
        if (array_key_exists($key, $this->errorCategories)) {
            $this->errorCategories[$key]++;
        } else {
            $this->errorCategories['other']++;
        }
    }

    private function isHeaderRow($row): bool
    {
        $first = Str::lower((string) ($row[0] ?? ''));

        return in_array($first, ['sku', 'kode', 'kode_produk', 'kode barang', 'nama produk', 'nama produk*']);
    }

    private function detectHasSkuColumnFromHeader($row): bool
    {
        $first = Str::lower((string) ($row[0] ?? ''));
        // Jika kolom pertama adalah SKU/kode => ada kolom SKU di template
        if (in_array($first, ['sku', 'kode', 'kode_produk', 'kode barang'])) {
            return true;
        }
        // Jika kolom pertama adalah Nama Produk => tidak ada kolom SKU
        if (in_array($first, ['nama produk', 'nama produk*'])) {
            return false;
        }
        // Default: asumsikan ada SKU
        return true;
    }

    private function mapRow($row): array
    {
        // Menyesuaikan urutan kolom sesuai template pengguna:
        // Jika $hasSkuColumn = true:
        // 0: SKU
        // 1: Nama Produk*
        // 2: Kategori
        // 3: Stok Awal
        // 4: Harga Pokok (base_cost)
        // 5: Harga Retail*
        // 6: Harga Semi Grosir
        // 7: Harga Grosir
        // 8: Jenis Harga*
        // 9: Status*
        // Jika $hasSkuColumn = false (tanpa SKU):
        // 0: Nama Produk*
        // 1: Kategori
        // 2: Stok Awal
        // 3: Harga Pokok (base_cost)
        // 4: Harga Retail*
        // 5: Harga Semi Grosir
        // 6: Harga Grosir
        // 7: Jenis Harga*
        // 8: Status*
        if ($this->hasSkuColumn) {
            return [
                'sku' => trim((string) ($row[0] ?? '')),
                'nama_produk' => trim((string) ($row[1] ?? '')),
                'kategori' => trim((string) ($row[2] ?? '')),
                'current_stock' => $this->normalizeInt($row[3] ?? 0),
                'base_cost' => $this->normalizeInt($row[4] ?? null),
                'price_retail' => $this->normalizeInt($row[5] ?? null),
                'price_semi_grosir' => $this->normalizeInt($row[6] ?? null),
                'price_grosir' => $this->normalizeInt($row[7] ?? null),
                // Simpan dua key sekaligus agar pra-validasi dan normalisasi downstream tetap sinkron
                'price_type' => trim((string) ($row[8] ?? 'retail')),
                'default_price_type' => trim((string) ($row[8] ?? 'retail')),
                'status' => trim((string) ($row[9] ?? 'active')),
                // unit_id tidak ada di template; importer akan fallback ke default via resolveUnitId()
                'unit_id' => null,
            ];
        } else {
            return [
                'sku' => '',
                'nama_produk' => trim((string) ($row[0] ?? '')),
                'kategori' => trim((string) ($row[1] ?? '')),
                'current_stock' => $this->normalizeInt($row[2] ?? 0),
                'base_cost' => $this->normalizeInt($row[3] ?? null),
                'price_retail' => $this->normalizeInt($row[4] ?? null),
                'price_semi_grosir' => $this->normalizeInt($row[5] ?? null),
                'price_grosir' => $this->normalizeInt($row[6] ?? null),
                'price_type' => trim((string) ($row[7] ?? 'retail')),
                'default_price_type' => trim((string) ($row[7] ?? 'retail')),
                'status' => trim((string) ($row[8] ?? 'active')),
                'unit_id' => null,
            ];
        }
    }

    private function normalizeInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Remove thousand separators and spaces, handle both "." and "," cases
        $clean = preg_replace('/[^0-9\-]/', '', (string) $value);

        return is_numeric($clean) ? (int) $clean : null;
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        $code = $e->getCode(); // SQLSTATE code or driver-specific
        $message = $e->getMessage();
        return ($code === '23000') || (stripos($message, 'Duplicate entry') !== false);
    }

    private function preValidateRow(array $data, int $rowIndex): array
    {
        $critical = false;
        
        // Jika baris kosong (semua kolom inti kosong), lewati tanpa pesan pada preview.
        // Definisi "baris kosong": Nama Produk & Harga Retail kosong,
        // dan semua kolom angka kosong atau 0.
        $nameBlank   = !isset($data['nama_produk']) || trim((string) $data['nama_produk']) === '';
        $retailBlank = !isset($data['price_retail']) || trim((string) $data['price_retail']) === '';
        $semiBlank   = !isset($data['price_semi_grosir']) || $data['price_semi_grosir'] === '' || (int) $data['price_semi_grosir'] === 0;
        $grosBlank   = !isset($data['price_grosir']) || $data['price_grosir'] === '' || (int) $data['price_grosir'] === 0;
        $costBlank   = !isset($data['base_cost']) || $data['base_cost'] === '' || (int) $data['base_cost'] === 0;
        $stockBlank  = !isset($data['current_stock']) || $data['current_stock'] === '' || (int) $data['current_stock'] === 0;
        if ($nameBlank && $retailBlank && $semiBlank && $grosBlank && $costBlank && $stockBlank) {
            $this->recordCategory('empty_row');
            // tandai sebagai empty + critical agar impor final tetap melewati, tapi preview akan mengabaikan
            return ['critical' => true, 'empty' => true];
        }
        
        // Field wajib (sesuai template: Nama Produk*, Harga Retail*, Jenis Harga*, Status*)
        if (empty($data['nama_produk'])) {
            $this->recordCategory('format_error');
            $this->errors[] = 'Baris #'.($rowIndex+1).': Nama produk kosong. Harus diisi.';
            $critical = true;
        }
        if ($data['price_retail'] === null || $data['price_retail'] === '') {
            $this->recordCategory('price_missing_retail');
            $this->errors[] = 'Baris #'.($rowIndex+1).': Harga Retail kosong. Harus diisi.';
            $critical = true;
        }
        if (empty($data['price_type'])) {
            $this->recordCategory('format_error');
            $this->errors[] = 'Baris #'.($rowIndex+1).': Jenis Harga kosong. Harus diisi.';
            $critical = true;
        }
        if (empty($data['status'])) {
            $this->recordCategory('format_error');
            $this->errors[] = 'Baris #'.($rowIndex+1).': Status kosong. Harus diisi.';
            $critical = true;
        }

        // Validasi Unit ID: opsional, fallback ke default jika tidak valid; blokir hanya jika strictMandatory aktif
        $unitId = $data['unit_id'] ?? null;
        if ($unitId && ! ProductUnit::whereKey($unitId)->exists()) {
            $this->recordCategory('invalid_unit');
            $this->errors[] = 'Baris #'.($rowIndex+1).": Unit ID '".$unitId."' tidak valid atau tidak ditemukan. Akan difallback ke default.";
            if ($this->strictMandatory) {
                $critical = true; // blok impor untuk baris ini pada mode ketat
            }
        }

        // Validasi harga ketat jika diaktifkan (Semi/Grosir opsional, tetapi jika diisi harus konsisten)
        if ($this->strictPricing) {
            $retail = $data['price_retail'];
            $semi   = $data['price_semi_grosir'] ?? null;
            $grosir = $data['price_grosir'] ?? null;
            if ($retail !== null && $retail !== '' && $semi !== null && $grosir !== null) {
                if (! $this->isPriceConsistent($retail, $semi, $grosir)) {
                    $this->recordCategory('price_inconsistent');
                    $this->errors[] = 'Baris #'.($rowIndex+1).': Harga tidak konsisten (harus Retail ≥ Semi ≥ Grosir).';
                    $critical = true;
                }
            }
        } else {
            // Mode non-strict: tetap catat statistik untuk laporan
            if ($data['price_retail'] === null || $data['price_retail'] === '') { $this->recordCategory('price_missing_retail'); }
            if ($data['price_semi_grosir'] === null || $data['price_semi_grosir'] === '') { $this->recordCategory('price_missing_semi'); }
            if ($data['price_grosir'] === null || $data['price_grosir'] === '') { $this->recordCategory('price_missing_grosir'); }
            if ($data['price_retail'] !== null && $data['price_retail'] !== '' && $data['price_semi_grosir'] !== null && $data['price_grosir'] !== null) {
                if (! $this->isPriceConsistent($data['price_retail'], $data['price_semi_grosir'], $data['price_grosir'])) {
                    $this->recordCategory('price_inconsistent');
                    // Non-strict mode: catat kategori saja, tanpa duplikasi pesan per baris
                }
            }
        }

        return ['critical' => $critical];
    }

    private function resolveUnitId($rawUnitId, int $rowIndex): ?int
    {
        $unitId = $this->normalizeInt($rawUnitId);
        if ($unitId && ProductUnit::whereKey($unitId)->exists()) {
            return $unitId;
        }

        // Prefer per-row default unit override if provided and valid
        if (isset($this->perRowUnitConfigs[$rowIndex]['default_unit_id'])) {
            $override = $this->normalizeInt($this->perRowUnitConfigs[$rowIndex]['default_unit_id']);
            if ($override && ProductUnit::whereKey($override)->exists()) {
                return $override;
            }
        }

        // Prefer global default unit jika ada dan valid
        if ($this->globalDefaultUnitId && ProductUnit::whereKey($this->globalDefaultUnitId)->exists()) {
            return $this->globalDefaultUnitId;
        }

        $fallback = $this->defaultUnitId();

        // Jika kolom unit tidak ada di template (rawUnitId null/kosong), fallback diam-diam tanpa peringatan
        if ($rawUnitId === null || $rawUnitId === '') {
            return $fallback;
        }

        // Jika ada nilai unit diberikan tapi tidak valid, beri peringatan (bukan error)
        if ($unitId && $unitId !== $fallback) {
            $this->warnings[] = 'Baris #'.($rowIndex+1).": Unit ID '".$unitId."' tidak ditemukan, disetel ke unit default (ID {$fallback}).";
        } else {
            $this->warnings[] = 'Baris #'.($rowIndex+1).": Unit ID tidak valid, disetel ke unit default (ID {$fallback}).";
        }

        return $fallback;
    }

    private function defaultUnitId(): ?int
    {
        static $cachedId = null;
        if ($cachedId !== null) {
            return $cachedId;
        }

        $id = ProductUnit::where('abbreviation', 'pcs')->value('id');
        if (! $id) {
            $id = ProductUnit::where('name', 'Pieces')->value('id');
        }
        if (! $id) {
            $id = ProductUnit::active()->ordered()->value('id');
        }
        if (! $id) {
            $id = ProductUnit::query()->orderBy('id')->value('id');
        }

        $cachedId = $id ?: null;
        return $cachedId;
    }

    private function normalizePriceType($value): string
    {
        $v = Str::lower(trim((string) $value));
        $map = [
            'retail' => 'retail',
            'ritel' => 'retail',
            'ecer' => 'retail',
            'semi grosir' => 'semi_grosir',
            'semi_grosir' => 'semi_grosir',
            'grosir' => 'grosir',
        ];
        return $map[$v] ?? 'retail';
    }

    private function normalizeStatus($value): string
    {
        $v = Str::lower(trim((string) $value));
        $map = [
            'active' => 'active',
            'aktif' => 'active',
            'inactive' => 'inactive',
            'non aktif' => 'inactive',
            'nonaktif' => 'inactive',
        ];
        return $map[$v] ?? 'active';
    }

    private function isPriceConsistent(?int $retail, ?int $semi, ?int $grosir): bool
    {
        if ($retail === null || $semi === null || $grosir === null) {
            return true; // tidak bisa diuji konsistensi jika salah satu kosong
        }
        return ($retail >= $semi) && ($semi >= $grosir);
    }

    private function resolvePriceOrFallback(?int $raw, ?int $priceRetail, ?int $baseCost, string $label, int $rowIndex): int
    {
        if ($raw !== null) {
            return $raw;
        }

        if ($priceRetail !== null) {
            $this->warnings[] = 'Baris #'.($rowIndex+1).": Harga {$label} kosong, disetel ke harga Retail ({$priceRetail}).";
            return $priceRetail;
        }

        if ($baseCost !== null) {
            $this->warnings[] = 'Baris #'.($rowIndex+1).": Harga {$label} kosong, tidak ada Retail. Disetel ke harga modal ({$baseCost}).";
            return $baseCost;
        }

        $this->warnings[] = 'Baris #'.($rowIndex+1).": Harga {$label} kosong, tidak ada Retail/Modal. Disetel ke 0.";
        return 0;
    }

    private function resolveBaseCostOrFallback(?int $rawBaseCost, ?int $priceRetail, int $rowIndex): int
    {
        if ($rawBaseCost !== null) {
            return $rawBaseCost;
        }
        if ($priceRetail !== null) {
            $this->warnings[] = 'Baris #'.($rowIndex+1).": Harga modal (base_cost) kosong, disetel ke harga Retail ({$priceRetail}).";
            return $priceRetail;
        }
        $this->warnings[] = 'Baris #'.($rowIndex+1).": Harga modal (base_cost) kosong, tidak ada Retail. Disetel ke 0.";
        return 0;
    }

    private function resolveRetailOrFallback(?int $rawRetail, int $baseCost, int $rowIndex): int
    {
        if ($rawRetail !== null) {
            return $rawRetail;
        }
        if ($baseCost > 0) {
            $this->warnings[] = 'Baris #'.($rowIndex+1).": Harga Retail kosong, disetel ke harga modal ({$baseCost}).";
            return $baseCost;
        }
        $this->warnings[] = 'Baris #'.($rowIndex+1).": Harga Retail kosong dan modal = 0. Disetel ke 0.";
        return 0;
    }

    private function resolveTargetWarehouseId(): ?int
    {
        // Selalu arahkan ke gudang default jika ada
        $defaultId = optional(Warehouse::getDefault())->id;
        if ($defaultId) {
            return (int) $defaultId;
        }
        // Fallback terakhir: gudang pertama (agar tidak null)
        $anyId = Warehouse::query()->orderBy('id')->value('id');
        return $anyId ? (int) $anyId : null;
    }

    private function normalizedTemplateHeaders(): array
    {
        if ($this->hasSkuColumn) {
            return [
                'SKU',
                'Nama Produk*',
                'Kategori',
                'Stok Awal',
                'Harga Pokok',
                'Harga Retail*',
                'Harga Semi Grosir',
                'Harga Grosir',
                'Jenis Harga*',
                'Status*',
            ];
        }
        return [
            'Nama Produk*',
            'Kategori',
            'Stok Awal',
            'Harga Pokok',
            'Harga Retail*',
            'Harga Semi Grosir',
            'Harga Grosir',
            'Jenis Harga*',
            'Status*',
        ];
    }

    public function getHasSkuColumn(): bool
    {
        return $this->hasSkuColumn;
    }

    public function setPerRowUnitConfigs(array $configs): void
    {
        // Expect shape: [rowIndex => ['default_unit_id' => int|null, 'scales' => [ ['unit_id'=>int, 'to_base_qty'=>int, 'notes'=>string|null], ... ] ]]
        $this->perRowUnitConfigs = $configs;
    }
}