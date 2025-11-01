<?php

namespace App\Livewire;

use App\Imports\ProductImport as Importer;
use App\ProductUnit;
use App\Exports\ProductTemplateExport;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ProductImport extends Component
{
    use WithFileUploads;

    public $file;
    public bool $showConfirm = false;

    // Preview state
    public array $previewRows = [];
    public array $previewHeaders = [];

    // Selection in confirm modal
    public array $selectedIndexes = [];

    // Global unit configuration (unit default dan N skala unit)
    public ?int $defaultUnitId = null;
    public array $unitScales = [
        // Example schema:
        // [ ['unit_id' => 2, 'to_base_qty' => 12, 'notes' => 'Dus => 12 pcs'], ... ]
    ];

    // Per-row unit configuration overrides: [rowIndex => ['default_unit_id'=>int|null, 'scales'=>[...]]]
    public array $rowUnitConfigs = [];

    public array $unitsList = [];

    // Header button state
    public bool $downloading = false;

    // Tampilan sederhana vs lanjutan + state panel editor per-baris
    public bool $advancedMode = false;
    public array $rowEditorsOpen = [];

    protected $rules = [
        'defaultUnitId' => 'nullable|integer',
        'unitScales' => 'array',
        'unitScales.*.unit_id' => 'nullable|integer',
        'unitScales.*.to_base_qty' => 'nullable|integer',
        'unitScales.*.notes' => 'nullable|string',
    ];

    public function mount()
    {
        $this->unitsList = ProductUnit::select('id','name')->orderBy('name')->get()->toArray();
    }

    public function render()
    {
        return view('livewire.product-import');
    }

    public function downloadTemplate()
    {
        $this->downloading = true;
        try {
            $this->downloading = false;
            $this->dispatch('show-toast', [
                'type' => 'success',
                'title' => 'Template berhasil diunduh!'
            ]);
            return Excel::download(new ProductTemplateExport(), 'template-import-produk.xlsx');
        } catch (\Throwable $e) {
            $this->downloading = false;
            $this->dispatch('show-alert', [
                'type' => 'error',
                'title' => 'Gagal Mengunduh Template',
                'text' => $e->getMessage()
            ]);
        }
    }

    public function prepareImport()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $importer = new Importer();
            $importer->setValidateOnly(true);

            // Jalankan import dalam mode preview untuk mengisi parsedRows
            Excel::import($importer, $this->file);

            $previewRows = $importer->getParsedRows();
            $previewHeaders = $importer->getHeaders();

            // Simpan file ke temporary location yang lebih reliable
            $fileName = 'import_' . time() . '_' . uniqid() . '.' . $this->file->getClientOriginalExtension();
            $tempPath = 'imports' . DIRECTORY_SEPARATOR . $fileName;
            
            // Store file using Laravel's built-in storage system
            $storedPath = $this->file->storeAs('imports', $fileName, 'local');
            
            \Log::info('File stored via Laravel at: ' . $storedPath);
            
            // Verify file was stored successfully
            if (!\Storage::disk('local')->exists($tempPath)) {
                \Log::error('File not found after storage: ' . $tempPath);
                $this->dispatch('show-timer-alert', [
                    'type' => 'error',
                    'title' => 'Gagal Menyimpan File',
                    'message' => 'File tidak berhasil disimpan. Silakan coba lagi.',
                    'duration' => 5000
                ]);
                return;
            }
            
            \Log::info('File successfully stored and verified at: ' . $tempPath);

            // Simpan data ke session untuk halaman konfirmasi
            session()->put('import_preview_data', [
                'previewRows' => $previewRows,
                'previewHeaders' => $previewHeaders,
                'tempPath' => $tempPath,
                'originalName' => $this->file->getClientOriginalName(),
            ]);

            // Show success message before redirect
            $this->dispatch('show-timer-alert', [
                'type' => 'success',
                'title' => 'File Berhasil Diupload',
                'message' => 'File berhasil diproses. Mengalihkan ke halaman konfirmasi...',
                'duration' => 2000
            ]);

            // Redirect ke halaman konfirmasi setelah delay
            $this->dispatch('redirect-after-delay', [
                'url' => '/products/import/confirm',
                'delay' => 2000
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to prepare import: ' . $e->getMessage());
            \Log::error('Exception trace: ' . $e->getTraceAsString());
            
            $this->dispatch('show-timer-alert', [
                'type' => 'error',
                'title' => 'Gagal Memproses File',
                'message' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function selectAllValid()
    {
        $this->selectedIndexes = [];
        foreach ($this->previewRows as $row) {
            if (($row['status'] ?? '') === 'valid') {
                $this->selectedIndexes[] = $row['index'];
            }
        }
    }

    public function deselectAll()
    {
        $this->selectedIndexes = [];
    }

    public function confirmImportSelected()
    {
        // Validasi dasar konfigurasi unit
        $this->validate();

        $allowed = $this->selectedIndexes;

        $importer = new Importer();
        $importer->setAllowedIndexes($allowed);
        $importer->setValidateOnly(false);
        // Set global unit config agar diterapkan ke setiap produk
        $importer->setGlobalUnitConfig($this->defaultUnitId, $this->unitScales);
        // Set per-row unit config overrides agar bisa konfigurasi satuan per produk
        $importer->setPerRowUnitConfigs($this->rowUnitConfigs);

        Excel::import($importer, $this->file);

        // Reset UI setelah impor
        $this->showConfirm = false;
        $this->previewRows = [];
        $this->previewHeaders = [];
        $this->selectedIndexes = [];
        $this->defaultUnitId = null;
        $this->unitScales = [];

        // Show success message with timer-based alert
        $this->dispatch('show-toast', [
            'type' => 'success',
            'title' => 'Import Produk Berhasil!'
        ]);
        
        // Redirect to products index after showing success message
        return $this->redirect('/products', navigate: true);
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

    public function addUnitScale(): void
    {
        $this->unitScales[] = [
            'unit_id' => null,
            'to_base_qty' => null,
            'notes' => null,
        ];
    }

    public function resetUnitScales(): void
    {
        $this->unitScales = [];
    }
}