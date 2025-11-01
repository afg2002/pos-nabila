<?php

namespace App\Livewire;

use App\Imports\ProductImport as Importer;
use App\ProductUnit;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ProductImportConfirm extends Component
{
    // Preview data dari session
    public array $previewRows = [];
    public array $previewHeaders = [];
    public string $tempPath = '';
    public string $originalName = '';

    // Selection state
    public array $selectedIndexes = [];

    // Global unit configuration
    public ?int $defaultUnitId = null;
    public array $unitScales = [];

    // Per-row unit configuration overrides
    public array $rowUnitConfigs = [];

    public array $unitsList = [];

    // Tampilan sederhana vs lanjutan + state panel editor per-baris
    public bool $advancedMode = false;
    public array $rowEditorsOpen = [];

    protected $rules = [
        'defaultUnitId' => 'nullable|integer',
        'unitScales' => 'array',
        'unitScales.*.unit_id' => 'nullable|integer',
        'unitScales.*.base_unit_id' => 'nullable|integer',
        'unitScales.*.to_base_qty' => 'nullable|numeric|min:0.01',
        'unitScales.*.notes' => 'nullable|string',
    ];

    public function mount()
    {
        // Ambil data dari session
        $previewData = session('import_preview_data');
        
        \Log::info('ProductImportConfirm mount - Session data exists: ' . ($previewData ? 'YES' : 'NO'));
        
        if (!$previewData) {
            \Log::error('ProductImportConfirm - No preview data in session');
            session()->flash('error', 'Data preview tidak ditemukan. Silakan upload file kembali.');
            return $this->redirect('/products/import', navigate: true);
        }

        $this->previewRows = $previewData['previewRows'] ?? [];
        $this->previewHeaders = $previewData['previewHeaders'] ?? [];
        $this->tempPath = $previewData['tempPath'] ?? '';
        $this->originalName = $previewData['originalName'] ?? '';

        \Log::info('ProductImportConfirm mount - Temp path: ' . $this->tempPath);
        \Log::info('ProductImportConfirm mount - Preview rows count: ' . count($this->previewRows));

        $this->unitsList = ProductUnit::select('id','name')->orderBy('name')->get()->toArray();

        // Default: pilih semua baris valid
        $this->selectAllValid();
    }

    public function confirmImport()
    {
        // Validasi dasar konfigurasi unit
        $this->validate();

        // Debug: Check if temp path exists
        if (empty($this->tempPath)) {
            \Log::error('ProductImportConfirm - Empty temp path');
            session()->flash('error', 'Path file temporary tidak ditemukan. Silakan upload file kembali.');
            return $this->redirect('/products/import', navigate: true);
        }

        // Pastikan file masih ada di storage menggunakan Storage facade untuk cross-platform compatibility
        $tempPath = str_replace('\\', '/', $this->tempPath); // Normalize path separator
        
        // Debug: Log path untuk troubleshooting
        \Log::info('Import attempt - Temp Path: ' . $tempPath);
        
        if (!\Storage::disk('local')->exists($tempPath)) {
            // Try to recreate from session
            $previewData = session('import_preview_data');
            if ($previewData && isset($previewData['tempPath'])) {
                $tempPath = str_replace('\\', '/', $previewData['tempPath']);
                \Log::info('Retry with session path: ' . $tempPath);
            }
            
            if (!\Storage::disk('local')->exists($tempPath)) {
                \Log::error('File not found in storage at: ' . $tempPath);
                \Log::error('Available files in imports: ' . json_encode(\Storage::disk('local')->files('imports')));
                session()->flash('error', 'File import tidak ditemukan. Silakan upload kembali. Path: ' . $tempPath);
                return $this->redirect('/products/import', navigate: true);
            }
        }
        
        \Log::info('File exists in storage: YES');

        $allowed = $this->selectedIndexes;

        // Debug: Log selected indexes
        \Log::info('Selected indexes: ' . json_encode($allowed));
        \Log::info('Unit configs: ' . json_encode($this->rowUnitConfigs));

        try {
            $importer = new Importer();
            $importer->setAllowedIndexes($allowed);
            $importer->setValidateOnly(false);
            $importer->setGlobalUnitConfig($this->defaultUnitId, $this->unitScales);
            $importer->setPerRowUnitConfigs($this->rowUnitConfigs);

            Excel::import($importer, $tempPath);

            // Hapus file setelah import menggunakan Storage facade
            if (\Storage::disk('local')->exists($tempPath)) {
                \Storage::disk('local')->delete($tempPath);
                \Log::info('File deleted after import: ' . $tempPath);
            }

            // Clear session data setelah import berhasil
            session()->forget('import_preview_data');

            session()->flash('success', 'Import produk berhasil diproses. ' . count($allowed) . ' produk telah diimport.');
            return $this->redirect('/products/import', navigate: true);
            
        } catch (\Exception $e) {
            \Log::error('Import failed: ' . $e->getMessage());
            \Log::error('Import failed trace: ' . $e->getTraceAsString());
            session()->flash('error', 'Import gagal: ' . $e->getMessage());
            return $this->redirect('/products/import/confirm', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.product-import-confirm')->layout('layouts.app');
    }

    public function selectAllValid()
    {
        $this->selectedIndexes = [];
        foreach ($this->previewRows as $index => $row) {
            if (($row['status'] ?? '') === 'valid') {
                $this->selectedIndexes[] = $index;
            }
        }
    }

    public function deselectAll()
    {
        $this->selectedIndexes = [];
    }

    public function cancel()
    {
        // Hapus file jika dibatalkan menggunakan Storage facade
        $tempPath = str_replace('\\', '/', $this->tempPath); // Normalize path separator
        if (\Storage::disk('local')->exists($tempPath)) {
            \Storage::disk('local')->delete($tempPath);
            \Log::info('File deleted on cancel: ' . $tempPath);
        }

        // Clear session data
        session()->forget('import_preview_data');

        return $this->redirect('/products/import', navigate: true);
    }

    public function setMode(string $mode): void
    {
        $this->advancedMode = ($mode === 'advanced');
    }

    public function toggleRowEditor(int $rowIndex): void
    {
        $this->rowEditorsOpen[$rowIndex] = !($this->rowEditorsOpen[$rowIndex] ?? false);
    }

    public function addRowUnitScale(int $rowIndex): void
    {
        if (!isset($this->rowUnitConfigs[$rowIndex])) {
            $this->rowUnitConfigs[$rowIndex] = [
                'default_unit_id' => null,
                'scales' => [],
            ];
        }
        $this->rowUnitConfigs[$rowIndex]['scales'][] = [
            'unit_id' => null,
            'to_base_qty' => null,
            'notes' => null,
        ];
    }

    public function resetRowUnitScales(int $rowIndex): void
    {
        if (!isset($this->rowUnitConfigs[$rowIndex])) {
            $this->rowUnitConfigs[$rowIndex] = [
                'default_unit_id' => null,
                'scales' => [],
            ];
        } else {
            $this->rowUnitConfigs[$rowIndex]['scales'] = [];
        }
    }

    public function removeRowUnitScale(int $rowIndex, int $scaleIndex): void
    {
        if (isset($this->rowUnitConfigs[$rowIndex]['scales'][$scaleIndex])) {
            unset($this->rowUnitConfigs[$rowIndex]['scales'][$scaleIndex]);
            // Re-index array untuk maintain proper indexing
            $this->rowUnitConfigs[$rowIndex]['scales'] = array_values($this->rowUnitConfigs[$rowIndex]['scales']);
        }
    }

    public function addUnitScale(): void
    {
        $this->unitScales[] = [
            'unit_id' => null,
            'to_base_qty' => null,
            'notes' => null,
        ];
    }

    public function removeUnitScale(int $index): void
    {
        if (isset($this->unitScales[$index])) {
            unset($this->unitScales[$index]);
            // Re-index array untuk maintain proper indexing
            $this->unitScales = array_values($this->unitScales);
        }
    }

    public function resetUnitScales(): void
    {
        $this->unitScales = [];
    }
}
