<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Kasir</h1>
                <p class="text-gray-600">Kelola dan lihat riwayat transaksi POS</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('pos.kasir') }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-cash-register mr-2"></i>
                    Kembali ke POS
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-receipt text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalTransactions) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Penjualan</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata per Transaksi</p>
                    <p class="text-2xl font-bold text-gray-900">
                        Rp {{ $totalTransactions > 0 ? number_format($totalSales / $totalTransactions, 0, ',', '.') : '0' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Transaksi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="No. transaksi, nama, telepon..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="dateFrom"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="dateTo"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Cashier -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kasir</label>
                    <select wire:model.live="cashierId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Kasir</option>
                        @foreach($cashiers as $cashier)
                            <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Bayar</label>
                    <select wire:model.live="paymentMethod"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Metode</option>
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="edc">EDC/Kartu</option>
                        <option value="qr">QR Code</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button wire:click="resetFilters"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="fas fa-undo mr-2"></i>
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No. Transaksi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal & Waktu
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pelanggan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kasir
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Metode Bayar
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $sale->sale_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $sale->created_at->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $sale->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $sale->customer_name ?: '-' }}</div>
                                @if($sale->customer_phone)
                                    <div class="text-sm text-gray-500">{{ $sale->customer_phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $sale->cashier->name ?? 'System' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    Rp {{ number_format($sale->final_total, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $sale->saleItems->count() }} item(s)
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($sale->payment_method === 'cash') bg-green-100 text-green-800
                                    @elseif($sale->payment_method === 'transfer') bg-blue-100 text-blue-800
                                    @elseif($sale->payment_method === 'edc') bg-purple-100 text-purple-800
                                    @elseif($sale->payment_method === 'qr') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($sale->payment_method) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusLabel = match($sale->status) {
                                        'PAID' => 'Lunas',
                                        'PARTIAL' => 'Sebagian',
                                        'UNPAID' => 'Belum dibayar',
                                        default => ucfirst(strtolower($sale->status ?? ''))
                                    };
                                    $statusClass = match($sale->status) {
                                        'PAID' => 'bg-green-100 text-green-800',
                                        'PARTIAL' => 'bg-yellow-100 text-yellow-800',
                                        'UNPAID' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button wire:click="showDetail({{ $sale->id }})"
                                            class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="printReceipt({{ $sale->id }})"
                                            class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-receipt text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">Tidak ada transaksi</p>
                                    <p class="text-sm">Belum ada transaksi yang sesuai dengan filter</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($sales->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedSale)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">Detail Transaksi</h3>
                    <button wire:click="closeDetail" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Transaction Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Transaksi</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">No. Transaksi:</span>
                                <span class="font-medium">{{ $selectedSale->sale_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tanggal:</span>
                                <span>{{ $selectedSale->created_at->format('d/m/Y H:i:s') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kasir:</span>
                                <span>{{ $selectedSale->cashier->name ?? 'System' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                @php
                                    $detailStatusLabel = match($selectedSale->status) {
                                        'PAID' => 'Lunas',
                                        'PARTIAL' => 'Sebagian',
                                        'UNPAID' => 'Belum dibayar',
                                        default => ucfirst(strtolower($selectedSale->status ?? ''))
                                    };
                                    $detailStatusClass = match($selectedSale->status) {
                                        'PAID' => 'bg-green-100 text-green-800',
                                        'PARTIAL' => 'bg-yellow-100 text-yellow-800',
                                        'UNPAID' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $detailStatusClass }}">
                                    {{ $detailStatusLabel }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Pelanggan</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nama:</span>
                                <span>{{ $selectedSale->customer_name ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Telepon:</span>
                                <span>{{ $selectedSale->customer_phone ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Metode Bayar:</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($selectedSale->payment_method === 'cash') bg-green-100 text-green-800
                                    @elseif($selectedSale->payment_method === 'transfer') bg-blue-100 text-blue-800
                                    @elseif($selectedSale->payment_method === 'edc') bg-purple-100 text-purple-800
                                    @elseif($selectedSale->payment_method === 'qr') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($selectedSale->payment_method) }}
                                </span>
                            </div>
                            @if($selectedSale->payment_notes)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Catatan Bayar:</span>
                                    <span>{{ $selectedSale->payment_notes }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Item Transaksi</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedSale->saleItems as $item)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">
                                            <div class="font-medium text-gray-900">
                                                {{ optional($item->product)->name ?? ($item->custom_item_name ?? 'Unknown Product') }}
                                            </div>
                                            <div class="text-gray-500">
                                                {{ optional($item->product)->sku ?? ($item->custom_item_description ?? '-') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ number_format($item->qty) }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary -->
                <div class="border-t pt-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span>Rp {{ number_format($selectedSale->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($selectedSale->discount_total > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Diskon:</span>
                                <span class="text-red-600">-Rp {{ number_format($selectedSale->discount_total, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-semibold text-lg border-t pt-2">
                            <span>Total:</span>
                            <span>Rp {{ number_format($selectedSale->final_total, 0, ',', '.') }}</span>
                        </div>
                        @php
                            $paid = ($selectedSale->cash_amount ?? 0) + ($selectedSale->qr_amount ?? 0) + ($selectedSale->edc_amount ?? 0);
                        @endphp
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bayar:</span>
                            <span>Rp {{ number_format($paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Kembalian:</span>
                            <span>Rp {{ number_format($selectedSale->change_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                @if($selectedSale->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded-md">
                        <p class="text-sm text-gray-600"><strong>Catatan:</strong> {{ $selectedSale->notes }}</p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end space-x-3 mt-6">
                    <button wire:click="closeDetail"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Tutup
                    </button>
                    <button wire:click="printReceipt({{ $selectedSale->id }})"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        <i class="fas fa-print mr-2"></i>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Print Receipt Script -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('print-receipt', () => {
                window.print();
            });
        });
    </script>
</div>