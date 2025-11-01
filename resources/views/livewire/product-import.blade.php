<!-- Import Products Page -->
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <style>
        /* Custom animations */
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .slide-up {
            animation: slideUp 0.5s ease-out;
        }
        
        /* File upload styling */
        .file-upload-wrapper {
            transition: all 0.3s ease;
        }
        
        .file-upload-wrapper:hover {
            transform: translateY(-2px);
        }
        
        /* Progress steps */
        .step {
            transition: all 0.3s ease;
        }
        
        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .container {
                padding: 1rem 0.5rem;
            }
            
            .card {
                border-radius: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
        }
        
        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8 slide-up">
            <h1 class="text-4xl font-bold text-gray-800 mb-3">
                📦 Import Produk
            </h1>
            <p class="text-gray-600 text-lg">
                Upload file Excel untuk menambahkan produk ke sistem
            </p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between max-w-2xl mx-auto">
                <div class="step active flex-1 text-center p-3 rounded-lg text-white">
                    <div class="text-sm font-medium">1. Upload File</div>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="step flex-1 text-center p-3 rounded-lg bg-gray-200 text-gray-600">
                    <div class="text-sm font-medium">2. Preview & Konfigurasi</div>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="step flex-1 text-center p-3 rounded-lg bg-gray-200 text-gray-600">
                    <div class="text-sm font-medium">3. Import</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid gap-6">
            <!-- Upload Section -->
            <div class="card bg-white rounded-2xl shadow-lg p-6 file-upload-wrapper">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        📤 Upload File Excel
                    </h2>
                    <p class="text-gray-600">
                        Pilih file Excel yang berisi data produk yang akan diimport
                    </p>
                </div>

                <!-- File Upload Area -->
                <div class="mb-6">
                    <div class="border-2 border-dashed border-blue-300 rounded-xl p-8 text-center bg-blue-50 hover:bg-blue-100 transition-colors">
                        <input type="file" 
                               wire:model="file" 
                               id="file-upload"
                               accept=".xlsx,.xls,.csv"
                               class="hidden">
                        
                        <label for="file-upload" class="cursor-pointer">
                            @if($file)
                                <div class="text-green-600">
                                    <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <p class="text-lg font-semibold mb-2">{{ $file->getClientOriginalName() }}</p>
                                    <p class="text-sm text-gray-600">
                                        Ukuran: {{ number_format($file->getSize() / 1024, 2) }} KB
                                    </p>
                                    <button type="button" 
                                            wire:click="$set('file', null)"
                                            class="mt-3 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                        Hapus File
                                    </button>
                                </div>
                            @else
                                <div class="text-gray-600">
                                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-lg font-semibold mb-2">
                                        Klik untuk memilih file
                                    </p>
                                    <p class="text-sm">
                                        Atau drag and drop file Excel di sini
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Format: .xlsx, .xls, .csv (Maks. 10MB)
                                    </p>
                                </div>
                            @endif
                        </label>
                    </div>
                    
                    @error('file')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button wire:click="downloadTemplate" 
                            wire:loading.attr="disabled"
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors flex items-center justify-center gap-2 font-medium">
                        @if($downloading)
                            <div class="spinner"></div>
                            <span>Mengunduh...</span>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>📋 Download Template</span>
                        @endif
                    </button>
                    
                    <button wire:click="prepareImport" 
                            wire:loading.attr="disabled"
                            wire:target="prepareImport"
                            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all transform hover:scale-105 flex items-center justify-center gap-2 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        @if($loading ?? false)
                            <div class="spinner"></div>
                            <span>Memproses...</span>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>📊 Preview Data</span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    📖 Panduan Import Produk
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">
                            1
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800 mb-1">Download Template</h4>
                            <p class="text-gray-600 text-sm">
                                Gunakan template yang sudah disediakan untuk memastikan format data benar
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">
                            2
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800 mb-1">Isi Data Produk</h4>
                            <p class="text-gray-600 text-sm">
                                Lengkapi semua kolom yang bertanda * (wajib): SKU, Nama Produk, Kategori, dll
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">
                            3
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800 mb-1">Upload File</h4>
                            <p class="text-gray-600 text-sm">
                                Upload file Excel yang sudah diisi dengan data produk
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm">
                            4
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800 mb-1">Preview & Konfirmasi</h4>
                            <p class="text-gray-600 text-sm">
                                Periksa data yang akan diimport dan konfigurasi satuan jika diperlukan
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Format Guide -->
            <div class="card bg-gradient-to-r from-yellow-50 to-orange-50 rounded-2xl shadow-lg p-6 border border-yellow-200">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    ⚠️ Format File Wajib
                </h3>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Kolom Wajib (bertanda *):</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <strong>SKU</strong> - Kode produk (opsional, auto-generate jika kosong)</li>
                            <li>• <strong>Nama Produk*</strong> - Nama produk</li>
                            <li>• <strong>Kategori</strong> - Kategori produk</li>
                            <li>• <strong>Stok Awal</strong> - Jumlah stok awal</li>
                            <li>• <strong>Harga Pokok</strong> - Harga beli/modal</li>
                            <li>• <strong>Harga Retail*</strong> - Harga jual eceran</li>
                            <li>• <strong>Jenis Harga*</strong> - Default harga (Retail/Semi Grosir/Grosir)</li>
                            <li>• <strong>Status*</strong> - Status produk (Aktif/Non Aktif)</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Kolom Opsional:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <strong>Harga Semi Grosir</strong> - Harga jual semi grosir</li>
                            <li>• <strong>Harga Grosir</strong> - Harga jual grosir</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session()->has('success'))
            <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-xl shadow-lg slide-up z-50 flex items-center gap-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold">Berhasil!</p>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-xl shadow-lg slide-up z-50 flex items-center gap-3">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-semibold">Error!</p>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Loading Overlay -->
    <div wire:loading wire:target="prepareImport" 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-sm mx-4 text-center">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-700 font-medium">Memproses file...</p>
            <p class="text-gray-500 text-sm mt-1">Mohon tunggu sebentar</p>
        </div>
    </div>
</div>
