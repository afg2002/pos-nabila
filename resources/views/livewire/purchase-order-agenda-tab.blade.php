<div class="space-y-6">
    <!-- Form Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Tambah Agenda Barang Datang</h3>
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="toggleFormMode"
                        class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-200">
                        {{ $showSimplifiedForm ? 'Mode Detail' : 'Mode Sederhana' }}
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Simplified Form -->
            @if($showSimplifiedForm)
                <form wire:submit.prevent="createSimplifiedAgenda" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier (PT)</label>
                            <select wire:model="supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                            <input type="date" wire:model="payment_due_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('payment_due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Jumlah Barang</label>
                            <div class="flex space-x-2">
                                <input type="number" wire:model="total_quantity" step="0.01" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Jumlah">
                                <input type="text" wire:model="quantity_unit" class="w-20 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Unit">
                            </div>
                            @error('total_quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Belanja</label>
                            <input type="number" wire:model="total_purchase_amount" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Rp 0">
                            @error('total_purchase_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Datang</label>
                            <input type="date" wire:model="scheduled_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('scheduled_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expired Date</label>
                            <input type="date" wire:model="expired_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('expired_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                            <input type="text" wire:model="batch_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Akan auto-generate jika kosong">
                            @error('batch_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan tambahan..."></textarea>
                        @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="auto_generate_po" id="auto_generate_po" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="auto_generate_po" class="ml-2 block text-sm text-gray-700">
                            Auto-generate Purchase Order
                        </label>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>Simpan Agenda
                        </button>
                    </div>
                </form>
            @else
                <!-- Detailed Form -->
                <form wire:submit.prevent="createDetailedAgenda" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier</label>
                            <input type="text" wire:model="supplier_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Nama supplier">
                            @error('supplier_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                            <input type="text" wire:model="goods_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Nama barang">
                            @error('goods_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                            <input type="number" wire:model="quantity" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Jumlah">
                            @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga per Unit</label>
                            <input type="number" wire:model="unit_price" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Rp 0">
                            @error('unit_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Datang</label>
                            <input type="date" wire:model="scheduled_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('scheduled_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo</label>
                            <input type="date" wire:model="payment_due_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('payment_due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                            <input type="text" wire:model="batch_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Opsional">
                            @error('batch_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expired Date</label>
                            <input type="date" wire:model="expired_date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('expired_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan tambahan..."></textarea>
                        @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>Simpan Agenda
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex-1 max-w-lg">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           wire:model.live="search" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="Cari agenda...">
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div>
                    <select wire:model.live="filterStatus" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="all">Semua Status</option>
                        <option value="scheduled">Pending</option>
                        <option value="received">Diterima</option>
                        <option value="overdue">Jatuh Tempo</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Agendas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Agenda Pending ({{ $pendingAgendas->count() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch & Expired</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pendingAgendas as $agenda)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $agenda->scheduled_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $agenda->effective_supplier_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $agenda->effective_goods_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($agenda->batch_number)
                                    <div class="font-medium text-blue-600">{{ $agenda->batch_number }}</div>
                                @else
                                    <div class="text-gray-400 italic">Belum ada batch</div>
                                @endif
                                @if($agenda->expired_date)
                                    <div class="text-xs {{ $agenda->expired_date->isPast() ? 'text-red-600 font-medium' : ($agenda->expired_date->diffInDays() <= 30 ? 'text-yellow-600' : 'text-gray-500') }}">
                                        Exp: {{ $agenda->expired_date->format('d/m/Y') }}
                                        @if($agenda->expired_date->isPast())
                                            <span class="ml-1 text-red-500">⚠️ EXPIRED</span>
                                        @elseif($agenda->expired_date->diffInDays() <= 30)
                                            <span class="ml-1 text-yellow-500">⚠️ {{ $agenda->expired_date->diffInDays() }} hari lagi</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-gray-400 italic">Tidak ada expired date</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($agenda->effective_quantity, 2) }} {{ $agenda->effective_unit }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp {{ number_format($agenda->effective_total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="{{ $agenda->is_overdue ? 'text-red-600 font-medium' : '' }}">
                                    {{ $agenda->payment_due_date->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $agenda->status_badge_class }}">
                                    {{ ucfirst($agenda->status) }}
                                </span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $agenda->payment_status_badge_class }} ml-1">
                                    {{ ucfirst($agenda->payment_status) }}
                                </span>
                                @if($agenda->is_purchase_order_generated)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 ml-1">
                                        PO: {{ $agenda->po_number }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @if(!$agenda->is_purchase_order_generated)
                                        <button wire:click="generatePurchaseOrder({{ $agenda->id }})" class="text-blue-600 hover:text-blue-900" title="Generate PO">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    @endif
                                    
                                    <button wire:click="markAsReceived({{ $agenda->id }})" class="text-green-600 hover:text-green-900" title="Terima Barang">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    
                                    <button wire:click="openPaymentModal({{ $agenda->id }})" class="text-yellow-600 hover:text-yellow-900" title="Bayar">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada agenda pending
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Received Agendas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Barang Diterima ({{ $receivedAgendas->count() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Diterima</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch & Expired</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($receivedAgendas as $agenda)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $agenda->received_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $agenda->effective_supplier_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $agenda->effective_goods_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($agenda->batch_number)
                                    <div class="font-medium text-blue-600">{{ $agenda->batch_number }}</div>
                                @else
                                    <div class="text-gray-400 italic">Tidak ada batch</div>
                                @endif
                                @if($agenda->expired_date)
                                    <div class="text-xs {{ $agenda->is_expired ? 'text-red-600 font-medium' : ($agenda->is_expiring_soon ? 'text-yellow-600' : 'text-green-600') }}">
                                        Exp: {{ $agenda->expired_date->format('d/m/Y') }}
                                        @if($agenda->is_expired)
                                            <span class="ml-1 text-red-500">⚠️ EXPIRED</span>
                                        @elseif($agenda->is_expiring_soon)
                                            <span class="ml-1 text-yellow-500">⚠️ Segera expired</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs text-gray-400 italic">Tidak ada expired date</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($agenda->effective_quantity, 2) }} {{ $agenda->effective_unit }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp {{ number_format($agenda->effective_total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $agenda->payment_status_badge_class }}">
                                        {{ ucfirst($agenda->payment_status) }}
                                    </span>
                                    <div class="text-xs text-gray-600">
                                        Rp {{ number_format($agenda->paid_amount, 0, ',', '.') }} / 
                                        Rp {{ number_format($agenda->effective_total_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($agenda->payment_status !== 'paid')
                                    <button wire:click="openPaymentModal({{ $agenda->id }})" class="text-yellow-600 hover:text-yellow-900" title="Bayar">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                Belum ada barang yang diterima
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expiring Batches Alert -->
    @if($expiringBatches->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Peringatan Kadaluarsa ({{ $expiringBatches->count() }})</h3>
            </div>
            
            <div class="p-6">
                <div class="space-y-3">
                    @foreach($expiringBatches as $batch)
                        <div class="flex items-center justify-between p-3 border rounded-lg {{ $batch->expiration_status === 'expired' ? 'border-red-200 bg-red-50' : 'border-yellow-200 bg-yellow-50' }}">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 {{ $batch->expiration_status === 'expired' ? 'bg-red-500' : 'bg-yellow-500' }} rounded-full"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</p>
                                    <p class="text-xs text-gray-600">{{ $batch->incomingGoodsAgenda->effective_supplier_name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ $batch->formatted_expired_date }}</p>
                                <p class="text-xs text-gray-600">{{ $batch->formatted_remaining_quantity }} / {{ $batch->formatted_quantity }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Payment Modal -->
@if($showPaymentModal)
<div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closePaymentModal"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Pembayaran Agenda
                        </h3>
                       
                        @if($selectedAgenda)
                            <div class="mt-4 space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600">Supplier</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $selectedAgenda->effective_supplier_name }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600">Total Belanja</p>
                                    <p class="text-sm font-medium text-gray-900">Rp {{ number_format($selectedAgenda->effective_total_amount, 0, ',', '.') }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600">Sudah Dibayar</p>
                                    <p class="text-sm font-medium text-gray-900">Rp {{ number_format($selectedAgenda->paid_amount, 0, ',', '.') }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600">Sisa Pembayaran</p>
                                    <p class="text-sm font-medium text-red-600">Rp {{ number_format($selectedAgenda->remaining_amount, 0, ',', '.') }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran</label>
                                    <input type="number" wire:model="paymentAmount" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    @error('paymentAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                                    <select wire:model="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="cash">Tunai</option>
                                        <option value="qr">QR Code</option>
                        <option value="edc">EDC/Kartu</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                    @error('paymentMethod') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                                    <textarea wire:model="paymentNotes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan pembayaran..."></textarea>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
           
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="makePayment" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Bayar
                </button>
                <button wire:click="closePaymentModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
@endif