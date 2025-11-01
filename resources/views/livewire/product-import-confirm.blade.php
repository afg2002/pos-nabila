<!-- Confirmation Import Page -->
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
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .container {
                padding: 1rem 0.5rem;
            }
            
            .card {
                border-radius: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            
            .table-container {
                font-size: 0.75rem;
            }
            
            .table-container th,
            .table-container td {
                padding: 0.25rem;
            }
        }
        
        /* Table styling for mobile */
        .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
        
        /* Checkbox styling */
        .custom-checkbox {
            width: 1.25rem;
            height: 1.25rem;
        }
        
        /* Unit configuration panel */
        .unit-panel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .unit-panel.open {
            max-height: 500px;
        }
        
        /* Sticky header */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
        }
        
        /* Success/Error animations */
        .message-toast {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <!-- Header -->
        <div class="mb-6 slide-up">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        📊 Konfirmasi Import
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Periksa dan konfigurasi data sebelum import
                    </p>
                </div>
                <div class="text-sm text-gray-500">
                    📁 {{ $originalName }}
                </div>
            </div>
            
            <!-- Progress Steps -->
            <div class="flex items-center justify-between max-w-2xl">
                <div class="step flex-1 text-center p-2 rounded-lg bg-green-500 text-white">
                    <div class="text-xs sm:text-sm font-medium">✓ Upload File</div>
                </div>
                <div class="w-8 h-0.5 bg-green-500"></div>
                <div class="step active flex-1 text-center p-2 rounded-lg bg-blue-500 text-white">
                    <div class="text-xs sm:text-sm font-medium">2. Preview & Konfigurasi</div>
                </div>
                <div class="w-8 h-0.5 bg-gray-300"></div>
                <div class="step flex-1 text-center p-2 rounded-lg bg-gray-200 text-gray-600">
                    <div class="text-xs sm:text-sm font-medium">3. Import</div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="card bg-white rounded-xl p-4 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Data</p>
                        <p class="text-2xl font-bold text-gray-800">{{ count($previewRows) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        📄
                    </div>
                </div>
            </div>
            
            <div class="card bg-green-50 rounded-xl p-4 shadow-lg border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-600 text-sm">Data Valid</p>
                        <p class="text-2xl font-bold text-green-700">
                            {{ collect($previewRows)->where('status', 'valid')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        ✅
                    </div>
                </div>
            </div>
            
            <div class="card bg-red-50 rounded-xl p-4 shadow-lg border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-600 text-sm">Data Error</p>
                        <p class="text-2xl font-bold text-red-700">
                            {{ collect($previewRows)->where('status', '!=', 'valid')->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        ❌
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card bg-white rounded-xl p-4 shadow-lg mb-6">
            <div class="flex flex-wrap gap-3 justify-center">
                <button wire:click="selectAllValid" 
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pilih Semua Valid
                </button>
                
                <button wire:click="deselectAll" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Hapus Pilihan
                </button>
                
                <button wire:click="confirmImport" 
                        wire:loading.attr="disabled"
                        wire:target="confirmImport"
                        class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2 disabled:opacity-50">
                    @if($loading ?? false)
                        <div class="spinner"></div>
                        <span>Importing...</span>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span>🚀 Import {{ count($selectedIndexes) }} Produk</span>
                    @endif
                </button>
                
                <button wire:click="cancel" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Batal
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="sticky-header border-b border-gray-200 p-4">
                <h2 class="text-xl font-bold text-gray-800">
                    📋 Preview Data Produk
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Centang produk yang akan diimport
                </p>
            </div>
            
            <div class="table-scroll">
                <table class="w-full table-container">
                    <thead class="bg-gray-50 sticky-header">
                        <tr>
                            <th class="px-2 py-3 text-left">
                                <input type="checkbox" 
                                       wire:model="selectAll"
                                       class="custom-checkbox">
                            </th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">No</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">SKU</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Nama Produk</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Kategori</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Stok Awal</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Harga Pokok</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Harga Retail</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Harga Semi Grosir</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Harga Grosir</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Jenis Harga</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                            <th class="px-2 py-3 text-left text-xs font-medium text-gray-700 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($previewRows as $index => $row)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-2 py-3">
                                    @if($row['status'] === 'valid')
                                        <input type="checkbox" 
                                               wire:model="selectedIndexes"
                                               value="{{ $loop->index }}"
                                               class="custom-checkbox">
                                    @endif
                                </td>
                                <td class="px-2 py-3 text-sm font-medium text-gray-900">
                                    {{ $row['index'] }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-600">
                                    {{ $row['sku'] ?? '-' }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-900 font-medium">
                                    {{ $row['nama'] ?? '-' }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-600">
                                    {{ $row['kategori'] ?? '-' }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-900">
                                    {{ $row['stok'] ?? 0 }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-900">
                                    {{ number_format($row['harga_beli'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-900">
                                    {{ number_format($row['harga_retail'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-900">
                                    {{ number_format($row['harga_semi_grosir'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-900">
                                    {{ number_format($row['harga_grosir'] ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-600">
                                    {{ $row['jenis_harga'] ?? '-' }}
                                </td>
                                <td class="px-2 py-3 text-sm text-gray-600">
                                    {{ $row['status_produk'] ?? '-' }}
                                </td>
                                <td class="px-2 py-3">
                                    @if($row['status'] === 'valid')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            ✅ Valid
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 cursor-help" 
                                              title="{{ is_array($row['errors'] ?? []) ? implode(', ', $row['errors']) : '' }}">
                                            ❌ Error
                                        </span>
                                    @endif
                                </td>
                                <td class="px-2 py-3">
                                    @if($row['status'] === 'valid')
                                        <button wire:click="toggleRowEditor({{ $index }})" 
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            ⚙️ Konfigurasi
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            
                            <!-- Unit Configuration Row -->
                            @if($row['status'] === 'valid' && ($rowEditorsOpen[$index] ?? false))
                                <tr>
                                    <td colspan="13" class="px-4 py-3 bg-gray-50">
                                        <div class="unit-panel open">
                                            <h4 class="font-semibold text-gray-800 mb-3">
                                                ⚙️ Konfigurasi Satuan: {{ $row['nama'] }}
                                            </h4>
                                            
                                            <div class="grid md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        Satuan Default
                                                    </label>
                                                    <select wire:model="rowUnitConfigs.{{ $index }}.default_unit_id" 
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                                        <option value="">-- Pilih Satuan --</option>
                                                        @foreach($unitsList as $unit)
                                                            <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        Konversi Satuan
                                                    </label>
                                                    <div class="space-y-2">
                                                        @foreach(($rowUnitConfigs[$index]['scales'] ?? []) as $scaleIndex => $scale)
                                                            <div class="flex gap-2 items-center">
                                                                <select wire:model="rowUnitConfigs.{{ $index }}.scales.{{ $scaleIndex }}.unit_id" 
                                                                        class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm">
                                                                    <option value="">Satuan</option>
                                                                    @foreach($unitsList as $unit)
                                                                        <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <input type="number" 
                                                                       wire:model="rowUnitConfigs.{{ $index }}.scales.{{ $scaleIndex }}.to_base_qty" 
                                                                       placeholder="Qty" 
                                                                       class="w-20 px-2 py-1 border border-gray-300 rounded text-sm">
                                                                <input type="text" 
                                                                       wire:model="rowUnitConfigs.{{ $index }}.scales.{{ $scaleIndex }}.notes" 
                                                                       placeholder="Catatan" 
                                                                       class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm">
                                                                <button wire:click="removeRowUnitScale({{ $index }}, {{ $scaleIndex }})" 
                                                                        class="text-red-500 hover:text-red-700">
                                                                    ❌
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                        
                                                        <div class="flex gap-2">
                                                            <button wire:click="addRowUnitScale({{ $index }})" 
                                                                    class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
                                                                + Tambah Konversi
                                                            </button>
                                                            <button wire:click="resetRowUnitScales({{ $index }})" 
                                                                    class="px-3 py-1 bg-gray-500 text-white rounded text-sm hover:bg-gray-600">
                                                                Reset
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="13" class="px-4 py-8 text-center text-gray-500">
                                    <div class="text-6xl mb-4">📭</div>
                                    <p class="text-lg font-medium">Tidak ada data</p>
                                    <p class="text-sm">File import tidak mengandung data produk</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading wire:target="confirmImport" 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-sm mx-4 text-center">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-gray-700 font-medium">Mengimport produk...</p>
            <p class="text-gray-500 text-sm mt-1">Mohon tunggu sebentar</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-xl shadow-lg message-toast z-50 flex items-center gap-3">
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
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-xl shadow-lg message-toast z-50 flex items-center gap-3">
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
