<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Purchase Order</h2>
        <p class="text-gray-600 dark:text-gray-400">Kelola purchase order, supplier, dan pembayaran</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters and Actions -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cari PO</label>
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="No. PO, supplier..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select wire:model.live="statusFilter" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="ordered">Ordered</option>
                    <option value="received">Received</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status Bayar</label>
                <select wire:model.live="paymentStatusFilter" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="unpaid">Belum Bayar</option>
                    <option value="partial">Sebagian</option>
                    <option value="paid">Lunas</option>
                </select>
            </div>

            <!-- Per Page -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Per Halaman</label>
                <select wire:model.live="perPage" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2">
            <button wire:click="openModal" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i>Buat PO Baru
            </button>
            
            <button wire:click="exportPurchaseOrders" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-file-export mr-2"></i>Export Excel
            </button>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('po_number')">
                            No. PO
                            @if($sortField === 'po_number')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('supplier_name')">
                            Supplier
                            @if($sortField === 'supplier_name')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Modal Usaha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total_amount')">
                            Total Amount
                            @if($sortField === 'total_amount')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('expected_date')">
                            Tanggal Masuk
                            @if($sortField === 'expected_date')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('payment_due_date')">
                            Jatuh Tempo
                            @if($sortField === 'payment_due_date')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Status Bayar
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                    {{ $po->po_number }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $po->supplier_name }}
                                </div>
                                @if($po->supplier_contact)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $po->supplier_contact }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $po->capitalTracking->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $po->expected_date ? $po->expected_date->format('d/m/Y') : 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $po->is_overdue ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $po->payment_due_date ? $po->payment_due_date->format('d/m/Y') : 'N/A' }}
                                </span>
                                @if($po->is_overdue)
                                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">
                                        Terlambat {{ $po->days_until_due }} hari
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="relative inline-block text-left">
                                    <button type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600" 
                                            onclick="toggleDropdown('status-{{ $po->id }}')">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $po->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                               ($po->status === 'ordered' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                               ($po->status === 'received' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                               'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200')) }}">
                                            {{ ucfirst($po->status) }}
                                        </span>
                                        <i class="fas fa-chevron-down ml-2"></i>
                                    </button>
                                    <div id="status-{{ $po->id }}" class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 z-10">
                                        <div class="py-1">
                                            <a href="#" wire:click="updateStatus({{ $po->id }}, 'pending')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Pending</a>
                                            <a href="#" wire:click="updateStatus({{ $po->id }}, 'ordered')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Ordered</a>
                                            <a href="#" wire:click="updateStatus({{ $po->id }}, 'received')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Received</a>
                                            <a href="#" wire:click="updateStatus({{ $po->id }}, 'cancelled')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Cancelled</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="relative inline-block text-left">
                                    <button type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600" 
                                            onclick="toggleDropdown('payment-{{ $po->id }}')">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $po->payment_status === 'unpaid' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                               ($po->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                               'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') }}">
                                            {{ ucfirst($po->payment_status) }}
                                        </span>
                                        <i class="fas fa-chevron-down ml-2"></i>
                                    </button>
                                    <div id="payment-{{ $po->id }}" class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 z-10">
                                        <div class="py-1">
                                            <a href="#" wire:click="updatePaymentStatus({{ $po->id }}, 'unpaid')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Unpaid</a>
                                            <a href="#" wire:click="updatePaymentStatus({{ $po->id }}, 'partial')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Partial</a>
                                            <a href="#" wire:click="updatePaymentStatus({{ $po->id }}, 'paid')" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">Paid</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button wire:click="viewDetail({{ $po->id }})" 
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" 
                                            title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="editPurchaseOrder({{ $po->id }})" 
                                            class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300" 
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="deletePurchaseOrder({{ $po->id }})" 
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" 
                                            title="Hapus"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus PO ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center py-8">
                                    <i class="fas fa-clipboard-list text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                    <p class="text-lg font-medium">Belum ada purchase order</p>
                                    <p class="text-sm">Klik "Buat PO Baru" untuk membuat purchase order pertama</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($purchaseOrders->hasPages())
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Form -->
@if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <form wire:submit.prevent="save">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modal-title">
                                {{ $editingId ? 'Edit Purchase Order' : 'Buat Purchase Order Baru' }}
                            </h3>
                            <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" wire:click="closeModal">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="supplier_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Supplier <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('supplier_name') border-red-500 @enderror" 
                                       id="supplier_name" wire:model="supplier_name" placeholder="Nama supplier">
                                @error('supplier_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="supplier_contact" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kontak Supplier</label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" 
                                       id="supplier_contact" wire:model="supplier_contact" placeholder="No. telepon atau email">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="capital_tracking_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Modal Usaha <span class="text-red-500">*</span></label>
                                <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('capital_tracking_id') border-red-500 @enderror" 
                                        id="capital_tracking_id" wire:model="capital_tracking_id">
                                    <option value="">Pilih Modal Usaha</option>
                                    @foreach($capitalTrackings as $capital)
                                        <option value="{{ $capital->id }}">
                                            {{ $capital->name }} (Rp {{ number_format($capital->current_amount, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('capital_tracking_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="expected_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Masuk <span class="text-red-500">*</span></label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('expected_date') border-red-500 @enderror" 
                                       id="expected_date" wire:model="expected_date">
                                @error('expected_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="payment_due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jatuh Tempo <span class="text-red-500">*</span></label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('payment_due_date') border-red-500 @enderror" 
                                       id="payment_due_date" wire:model="payment_due_date">
                                @error('payment_due_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="payment_schedule_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Agenda Pembayaran</label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('payment_schedule_date') border-red-500 @enderror" 
                                       id="payment_schedule_date" wire:model="payment_schedule_date" 
                                       placeholder="Pilih tanggal agenda pembayaran">
                                @error('payment_schedule_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tanggal rencana pembayaran untuk reminder</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aktifkan Reminder</label>
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                           id="reminder_enabled" wire:model="reminder_enabled">
                                    <label for="reminder_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                        Buat reminder otomatis
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Reminder akan dibuat 3 hari sebelum, pada tanggal, dan 1 hari setelah agenda</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" 
                                      id="notes" wire:model="notes" rows="3" placeholder="Catatan tambahan"></textarea>
                        </div>

                        <!-- Items Section -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white">Item Purchase Order</h4>
                                <button type="button" wire:click="addItem" 
                                        class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                                    <i class="fas fa-plus mr-1"></i>Tambah Item
                                </button>
                            </div>

                            @if(count($items) > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Produk</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qty</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Harga</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($items as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-2">
                                                        <input type="text" 
                                                               class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm" 
                                                               wire:model="items.{{ $index }}.product_name" placeholder="Nama produk">
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input type="number" 
                                                               class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm" 
                                                               wire:model="items.{{ $index }}.quantity" min="1" step="1">
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input type="number" 
                                                               class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm" 
                                                               wire:model="items.{{ $index }}.unit_price" min="0" step="0.01">
                                                    </td>
                                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">
                                                        Rp {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <button type="button" wire:click="removeItem({{ $index }})" 
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                            <i class="fas fa-trash text-sm"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    <p>Belum ada item. Klik "Tambah Item" untuk menambahkan produk.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $editingId ? 'Update' : 'Simpan' }}
                        </button>
                        <button type="button" wire:click="closeModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.classList.toggle('hidden');
    
    // Close other dropdowns
    document.querySelectorAll('[id^="status-"], [id^="payment-"]').forEach(el => {
        if (el.id !== id) {
            el.classList.add('hidden');
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.relative')) {
        document.querySelectorAll('[id^="status-"], [id^="payment-"]').forEach(el => {
            el.classList.add('hidden');
        });
    }
});
</script>
