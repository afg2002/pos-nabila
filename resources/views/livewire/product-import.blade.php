<div class="space-y-6" x-data="{ downloading: @entangle('downloading') }">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Import Produk</h2>
                <p class="text-sm text-gray-600 mt-1">Upload file Excel untuk mengimpor data produk secara massal</p>
            </div>
            <button wire:click="downloadTemplate" 
                    :disabled="downloading"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                <div wire:loading wire:target="downloadTemplate" class="mr-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <svg wire:loading.remove wire:target="downloadTemplate" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span wire:loading.remove wire:target="downloadTemplate">Download Template</span>
                <span wire:loading wire:target="downloadTemplate">Mengunduh...</span>
            </button>
        </div>
        
        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Petunjuk Import:</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data produk sesuai format yang disediakan</li>
                            <li>Kolom dengan tanda (*) wajib diisi</li>
                            <li>SKU harus unik untuk setiap produk</li>
                            <li>Status harus berisi "Aktif" atau "Tidak Aktif"</li>
                            <li>File maksimal 2MB dengan format .xlsx atau .xls</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upload Form -->
        <form wire:submit.prevent="import" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Pilih File Excel
                </label>
                <div class="flex items-center space-x-4">
                    <input type="file" 
                           wire:model="file" 
                           accept=".xlsx,.xls"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    
                    <button type="submit" 
                            wire:loading.attr="disabled" 
                            wire:target="import"
                            x-bind:disabled="!$wire.file"
                            class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white text-sm font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <div wire:loading wire:target="import" class="mr-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <span wire:loading.remove wire:target="import">Import Produk</span>
                        <span wire:loading wire:target="import">Mengimpor...</span>
                    </button>
                </div>
                @error('file') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror
            </div>
        </form>
    </div>
    
    <!-- Import Results -->
    @if($importResults)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Hasil Import</h3>
                <button wire:click="resetResults" 
                        class="text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Tutup
                </button>
            </div>
            
            <!-- Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-600">Berhasil</p>
                            <p class="text-2xl font-semibold text-green-900">{{ $importResults['success'] }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-yellow-600">Dilewati</p>
                            <p class="text-2xl font-semibold text-yellow-900">{{ $importResults['skipped'] }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-red-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-red-600">Error</p>
                            <p class="text-2xl font-semibold text-red-900">{{ count($importResults['errors']) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Error Details -->
            @if(count($importResults['errors']) > 0)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-red-800 mb-2">Detail Error:</h4>
                    <div class="text-sm text-red-700 space-y-1 max-h-60 overflow-y-auto">
                        @foreach($importResults['errors'] as $error)
                            <div class="flex items-start">
                                <svg class="h-4 w-4 text-red-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>