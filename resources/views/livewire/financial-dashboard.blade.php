<div class="space-y-6" wire:poll.30s="refreshData">
    @if($criticalAlerts->isNotEmpty())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-sm font-medium text-red-800">Peringatan Keuangan</h3>
            </div>
            <div class="mt-2 space-y-1">
                @foreach($criticalAlerts as $alert)
                    <p class="text-sm text-red-700">
                        {{ $alert['message'] }}
                        @if(isset($alert['amount']))
                            - Rp {{ number_format(abs($alert['amount']), 0, ',', '.') }}
                        @endif
                    </p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border p-6 cursor-pointer hover:shadow-md transition-shadow"
             wire:click="showDetailModal('cash')">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Saldo Kas</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($cashBalance, 0, ',', '.') }}</p>
                    <p class="text-xs mt-1 {{ $cashStatus['status'] === 'critical' ? 'text-red-600' : ($cashStatus['status'] === 'warning' ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $cashStatus['message'] }}
                    </p>
                </div>
                <div class="p-3 rounded-full {{ $cashStatus['status'] === 'critical' ? 'bg-red-100 text-red-600' : ($cashStatus['status'] === 'warning' ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6 cursor-pointer hover:shadow-md transition-shadow"
             wire:click="showDetailModal('receivables')">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Piutang</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Customer belum melunasi</p>
                </div>
                <div class="p-3 bg-blue-100 text-blue-600 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Posisi Kas Bersih</p>
                    <p class="text-2xl font-bold {{ $netPositionStatus['status'] === 'negative' ? 'text-red-600' : ($netPositionStatus['status'] === 'warning' ? 'text-yellow-600' : 'text-green-600') }}">
                        Rp {{ number_format($netCashPosition, 0, ',', '.') }}
                    </p>
                    <p class="text-xs mt-1 {{ $netPositionStatus['status'] === 'negative' ? 'text-red-600' : ($netPositionStatus['status'] === 'warning' ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $netPositionStatus['message'] }}
                    </p>
                </div>
                <div class="p-3 bg-purple-100 text-purple-600 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4M9 13l3 3L22 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pembayaran Supplier</p>
                    <p class="text-lg font-bold text-gray-900">Pending: Rp {{ number_format($pendingPayments, 0, ',', '.') }}</p>
                    <p class="text-xs text-red-600 mt-1">Overdue: Rp {{ number_format($overduePayments, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-orange-100 text-orange-600 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m0 0h-5m5 0v5"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Pendapatan Hari Ini</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <p class="text-sm text-gray-500">Pembayaran Supplier Pending</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($pendingPayments + $overduePayments, 0, ',', '.') }}</p>
        </div>
    </div>

    @php
        $cashMax = max(1, collect($cashTrend)->pluck('amount')->map(fn($value) => abs($value))->max() ?? 1);
        $revenueMax = max(1, collect($revenueTrend)->pluck('amount')->max() ?? 1);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Kas (7 Hari)</h3>
            <div class="h-64 flex items-end space-x-2">
                @foreach($cashTrend as $data)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-blue-200 rounded-t" style="height: {{ max(5, ($cashMax ? (abs($data['amount']) / $cashMax) : 0) * 200) }}px;"></div>
                        <p class="text-xs text-gray-600 mt-2 transform -rotate-45">{{ $data['date'] }}</p>
                        <p class="text-[10px] text-gray-500">Rp {{ number_format($data['amount'], 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Pendapatan (7 Hari)</h3>
            <div class="h-64 flex items-end space-x-2">
                @foreach($revenueTrend as $data)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-green-200 rounded-t" style="height: {{ max(5, ($revenueMax ? $data['amount'] / $revenueMax : 0) * 200) }}px;"></div>
                        <p class="text-xs text-gray-600 mt-2 transform -rotate-45">{{ $data['date'] }}</p>
                        <p class="text-[10px] text-gray-500">Rp {{ number_format($data['amount'], 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Pembayaran Supplier (7 Hari)</h3>
                <span class="text-sm text-gray-500">{{ $upcomingPayments->count() }} item</span>
            </div>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($upcomingPayments as $payment)
                    @php $outstanding = max(0, $payment->amount - $payment->paid_amount); @endphp
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $payment->incomingGoods->supplier_name ?? 'Supplier' }}</p>
                            <p class="text-xs text-gray-600">Jatuh tempo: {{ $payment->due_date?->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($outstanding, 0, ',', '.') }}</p>
                            <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">{{ $payment->due_date?->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">Tidak ada pembayaran mendatang</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Pembayaran Terlambat</h3>
                <span class="text-sm text-red-600 font-medium">Rp {{ number_format($overduePayments, 0, ',', '.') }}</span>
            </div>
            <div class="space-y-3 max-h-64 overflow-y-auto">
                @if($overduePayments > 0)
                    <div class="cursor-pointer" wire:click="showDetailModal('overdue')">
                        <div class="p-3 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Ada pembayaran yang terlambat</p>
                                    <p class="text-xs text-red-600">Klik untuk melihat detail</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">Tidak ada pembayaran terlambat</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="flex justify-center">
        <button wire:click="refreshData"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh Data
        </button>
    </div>

    @if($showDetails)
        <div class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4">
                <div class="p-6 border-b flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Detail {{ ucfirst($detailType) }}</h3>
                        <p class="text-sm text-gray-500">Ringkasan data saat ini</p>
                    </div>
                    <button wire:click="closeDetailModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    @if($detailData->count() > 0)
                        <div class="space-y-3">
                            @foreach($detailData as $item)
                                <div class="p-3 border rounded-lg">
                                    @switch($detailType)
                                        @case('cash')
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="font-medium">{{ $item->date?->format('d M Y') ?? '-' }} • {{ strtoupper($item->type) }}</p>
                                                    <p class="text-sm text-gray-600">Pencatat: {{ $item->creator->name ?? 'System' }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm text-green-600">Kas Masuk: Rp {{ number_format($item->cash_in, 0, ',', '.') }}</p>
                                                    <p class="text-sm text-red-600">Kas Keluar: Rp {{ number_format($item->cash_out, 0, ',', '.') }}</p>
                                                    <p class="text-sm font-semibold text-gray-900">Saldo Akhir: Rp {{ number_format($item->closing_balance, 0, ',', '.') }}</p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-2">{{ $item->description ?: 'Tidak ada keterangan' }}</p>
                                            @break
                                        @case('receivables')
                                            @php $remaining = max(0, $item->amount - $item->paid_amount); @endphp
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="font-medium">{{ $item->customer_name }}</p>
                                                    <p class="text-sm text-gray-600">Jatuh tempo: {{ $item->due_date?->format('d M Y') }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold text-gray-900">Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                                                    <span class="text-xs px-2 py-1 rounded-full {{ $item->status === 'paid' ? 'bg-green-100 text-green-800' : ($item->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                        {{ strtoupper($item->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-2">{{ $item->notes ?: 'Tidak ada catatan' }}</p>
                                            @break
                                        @case('overdue')
                                            @php $remaining = max(0, $item->amount - $item->paid_amount); @endphp
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="font-medium">{{ $item->incomingGoods->supplier_name ?? 'Supplier' }}</p>
                                                    <p class="text-sm text-gray-600">Invoice: {{ $item->incomingGoods->invoice_number ?? '-' }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold text-gray-900">Sisa: Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                                                    <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full">Jatuh tempo {{ $item->due_date?->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            @break
                                        @case('revenue')
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="font-medium">Penjualan #{{ $item->id }}</p>
                                                    <p class="text-sm text-gray-600">Kasir: {{ $item->cashier->name ?? 'Kasir' }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($item->final_total, 0, ',', '.') }}</p>
                                                    <p class="text-xs text-gray-500">{{ $item->created_at?->format('H:i') }}</p>
                                                </div>
                                            </div>
                                            @break
                                    @endswitch
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>Tidak ada data</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('financial-data-refreshed', () => console.log('Financial data refreshed'));
    });
</script>
@endpush
