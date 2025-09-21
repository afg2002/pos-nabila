<div class="space-y-6">
    @if (session()->has('message'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-green-800">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-red-800">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Agenda Barang Masuk</h1>
                    <p class="text-gray-600">Kelola jadwal barang masuk dan reminder pembayaran</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <!-- View Mode Toggle -->
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button type="button" 
                            class="px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ $viewMode === 'calendar' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                            wire:click="switchView('calendar')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Kalender
                    </button>
                    <button type="button" 
                            class="px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ $viewMode === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                            wire:click="switchView('list')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Daftar
                    </button>
                </div>
                <button type="button" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center space-x-2"
                        wire:click="openModal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Agenda</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Agenda</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalAgendas }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Jatuh Tempo Hari Ini</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $paymentDueTodayCount }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Terlambat</p>
                    <p class="text-2xl font-bold text-red-600">{{ $overduePaymentCount }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    @if($viewMode === 'calendar')
        <!-- Calendar View -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Kalender Agenda</h2>
                    <div class="flex items-center space-x-4">
                        <button type="button" 
                                class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                wire:click="previousMonth">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <h3 class="text-lg font-medium text-gray-900">{{ \Carbon\Carbon::parse($filterMonth . '-01')->format('F Y') }}</h3>
                        <button type="button" 
                                class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                wire:click="nextMonth">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-7 gap-1 mb-4">
                    @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                        <div class="p-2 text-center text-sm font-medium text-gray-500">{{ $day }}</div>
                    @endforeach
                </div>
                
                <div class="grid grid-cols-7 gap-1">
                    @foreach($calendarData as $day)
                        <div class="min-h-[100px] p-2 border border-gray-100 {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-gray-50' }} {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}">
                            <div class="text-sm {{ $day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400' }} font-medium mb-1">
                                {{ $day['day'] }}
                            </div>
                            @if(isset($day['agendas']) && count($day['agendas']) > 0)
                                @foreach($day['agendas'] as $agenda)
                                    <div class="text-xs p-1 mb-1 rounded cursor-pointer {{ $agenda->isDueToday ? 'bg-orange-100 text-orange-800' : ($agenda->isOverdue ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}"
                                         wire:click="selectDate('{{ $day['date'] }}')">
                                        <div class="font-medium">{{ Str::limit($agenda->supplier_name, 12) }}</div>
                                        <div class="text-xs opacity-75">{{ Str::limit($agenda->goods_name ?? $agenda->item_name ?? 'Barang', 12) }}</div>
                                        @if($agenda->quantity && $agenda->unit)
                                            <div class="text-xs opacity-60">{{ $agenda->quantity }} {{ $agenda->unit }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <!-- List View -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Agenda</h2>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" 
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                   placeholder="Cari agenda..."
                                   wire:model.live="search">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier & Barang</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty & Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Masuk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($agendas as $agenda)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $agenda->supplier_name }}</div>
                                    <div class="text-sm text-green-600 font-medium">{{ $agenda->goods_name ?? $agenda->item_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $agenda->description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $agenda->quantity ?? '-' }} {{ $agenda->unit ?? '' }}</div>
                                    <div class="text-sm text-gray-500">@ Rp {{ number_format($agenda->unit_price ?? 0, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $agenda->scheduled_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $agenda->payment_due_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($agenda->total_amount ?? $agenda->amount ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $agenda->statusBadgeClass }}">
                                        {{ ucfirst($agenda->status) }}
                                    </span>
                                    @if($agenda->payment_status !== 'paid')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-1 {{ $agenda->paymentStatusBadgeClass }}">
                                            {{ $agenda->isDueToday ? 'Jatuh Tempo' : ($agenda->isOverdue ? 'Terlambat' : 'Belum Bayar') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        @if($agenda->payment_status !== 'paid')
                                            <button type="button" 
                                                    class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                                    wire:click="openPaymentModal({{ $agenda->id }})">
                                                Bayar
                                            </button>
                                        @endif
                                        <button type="button" 
                                                class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                wire:click="edit({{ $agenda->id }})">
                                            Edit
                                        </button>
                                        <button type="button" 
                                                class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                wire:click="confirmDelete({{ $agenda->id }})">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    Tidak ada agenda yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($agendas->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $agendas->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Modal Form -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $editingId ? 'Edit' : 'Tambah' }} Agenda</h3>
                    
                    <form wire:submit="save">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier</label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                       wire:model="supplier_name"
                                       required>
                                @error('supplier_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                       wire:model="goods_name"
                                       required>
                                @error('goods_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                                    <input type="number" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           wire:model="quantity"
                                           min="1"
                                           required>
                                    @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                                    <input type="text" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           wire:model="unit"
                                           placeholder="pcs, kg, liter, dll"
                                           required>
                                    @error('unit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga per Unit (Rp)</label>
                                    <input type="number" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           wire:model="unit_price"
                                           min="0"
                                           step="0.01"
                                           required>
                                    @error('unit_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Total (Rp)</label>
                                    <input type="number" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                           wire:model="total_amount"
                                           readonly>
                                    @error('total_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                          wire:model="description"
                                          rows="3"></textarea>
                                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Masuk</label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                       wire:model="scheduled_date"
                                       required>
                                @error('scheduled_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo Pembayaran</label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                       wire:model="payment_due_date"
                                       required>
                                @error('payment_due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Modal Usaha</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        wire:model="capital_tracking_id"
                                        required>
                                    <option value="">Pilih Modal Usaha</option>
                                    @foreach($capitalTrackings as $capital)
                                        <option value="{{ $capital->id }}">{{ $capital->name }} - Rp {{ number_format($capital->current_amount, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                                @error('capital_tracking_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" 
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200" 
                                    wire:click="closeModal">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                                {{ $editingId ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Pembayaran -->
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closePaymentModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Proses Pembayaran</h3>
                    
                    @if($selectedAgenda)
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600">Supplier: <span class="font-medium">{{ $selectedAgenda->supplier_name }}</span></p>
                            <p class="text-sm text-gray-600">Jumlah: <span class="font-medium">Rp {{ number_format($selectedAgenda->amount, 0, ',', '.') }}</span></p>
                        </div>
                    @endif
                    
                    <form wire:submit="processPayment">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Modal Usaha</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        wire:model="paymentCapitalId"
                                        required>
                                    <option value="">Pilih Modal Usaha</option>
                                    @foreach($businessModals as $modal)
                                        <option value="{{ $modal->id }}">{{ $modal->name }} (Saldo: Rp {{ number_format($modal->current_balance, 0, ',', '.') }})</option>
                                    @endforeach
                                </select>
                                @error('paymentCapitalId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                          wire:model="paymentNotes"
                                          rows="3"
                                          placeholder="Catatan pembayaran (opsional)"></textarea>
                                @error('paymentNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" 
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200" 
                                    wire:click="closePaymentModal">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                                Proses Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDeleteModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Agenda</h3>
                    <p class="text-sm text-gray-500 mb-4">Apakah Anda yakin ingin menghapus agenda ini? Tindakan ini tidak dapat dibatalkan.</p>
                    
                    <div class="flex justify-center space-x-3">
                        <button type="button" 
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200" 
                                wire:click="closeDeleteModal">
                            Batal
                        </button>
                        <button type="button" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200" 
                                wire:click="delete">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
