<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Imports\ProductImport as ProductImportClass;
use App\Exports\ProductTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductImport extends Component
{
    use WithFileUploads, AuthorizesRequests;
    
    public $file;
    public $importing = false;
    public $importResults = null;
    
    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls|max:2048'
    ];
    
    protected $messages = [
        'file.required' => 'File Excel harus dipilih.',
        'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
        'file.max' => 'Ukuran file maksimal 2MB.'
    ];
    
    public function mount()
    {
        // Check authorization
        if (!auth()->user()->hasPermission('products.create')) {
            abort(403, 'Anda tidak memiliki izin untuk mengimpor produk.');
        }
    }
    
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new ProductTemplateExport(),
                'template-import-produk.xlsx'
            );
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }
    
    public function import()
    {
        $this->validate();
        
        // Check authorization
        if (!auth()->user()->hasPermission('products.create')) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengimpor produk.');
            return;
        }
        
        $this->importing = true;
        $this->importResults = null;
        
        try {
            $import = new ProductImportClass();
            Excel::import($import, $this->file->getRealPath());
            
            $this->importResults = [
                'success' => $import->getSuccessCount(),
                'skipped' => $import->getSkipCount(),
                'errors' => $import->getErrors()
            ];
            
            if ($import->getSuccessCount() > 0) {
                session()->flash('message', 
                    "Import berhasil! {$import->getSuccessCount()} produk berhasil diimpor." .
                    ($import->getSkipCount() > 0 ? " {$import->getSkipCount()} produk dilewati." : '')
                );
            }
            
            if (count($import->getErrors()) > 0) {
                session()->flash('error', 'Terdapat beberapa error saat import. Silakan periksa detail di bawah.');
            }
            
            // Reset file input
            $this->file = null;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengimpor file: ' . $e->getMessage());
        } finally {
            $this->importing = false;
        }
    }
    
    public function resetResults()
    {
        $this->importResults = null;
        $this->file = null;
    }
    
    public function render()
    {
        return view('livewire.product-import');
    }
}