<div class="relative" x-data="{ open: @entangle('showNotifications') }">
    <!-- Notification Bell Button -->
    <button @click="open = !open" 
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        <!-- Notification Badge -->
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notification Dropdown -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.away="open = false"
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border z-50 max-h-[80vh] overflow-hidden">
        
        <!-- Header -->
        <div class="p-4 border-b bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Notifikasi Agenda</h3>
                <div class="flex space-x-2">
                    <!-- Filter Toggle -->
                    <div class="relative" x-data="{ showFilters: false }">
                        <button @click="showFilters = !showFilters" 
                                class="p-1 text-gray-500 hover:text-gray-700 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                        </button>
                        
                        <!-- Filter Options -->
                        <div x-show="showFilters" 
                             x-transition
                             @click.away="showFilters = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                            <div class="p-3 space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="showIncoming" class="mr-2">
                                    <span class="text-sm">Barang Masuk</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="showPayments" class="mr-2">
                                    <span class="text-sm">Pembayaran</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="showOverdue" class="mr-2">
                                    <span class="text-sm">Terlambat</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="showFinancial" class="mr-2">
                                    <span class="text-sm">Keuangan</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mark All Read -->
                    @if($unreadCount > 0)
                        <button wire:click="markAllAsRead" 
                                class="text-xs text-blue-600 hover:text-blue-800">
                            Tandai Semua Dibaca
                        </button>
                    @endif
                </div>
            </div>
            
            @if($unreadCount > 0)
                <p class="text-sm text-gray-600 mt-1">{{ $unreadCount }} notifikasi baru</p>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div class="p-4 border-b hover:bg-gray-50 transition-colors
                    {{ $notification['priority'] === 'critical' ? 'border-l-4 border-red-500 bg-red-50' : '' }}
                    {{ $notification['priority'] === 'high' ? 'border-l-4 border-orange-500' : '' }}
                    {{ $notification['priority'] === 'medium' ? 'border-l-4 border-yellow-500' : '' }}">
                    
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            @switch($notification['icon'])
                                @case('truck')
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @break
                                @case('credit-card')
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    @break
                                @case('exclamation-triangle')
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    @break
                                @case('exclamation-circle')
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @break
                                @default
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                            @endswitch
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $notification['title'] }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification['message'] }}</p>
                                    
                                    @if(isset($notification['amount']))
                                        <p class="text-sm font-semibold text-gray-900 mt-1">
                                            Rp {{ number_format($notification['amount'], 0, ',', '.') }}
                                        </p>
                                    @endif
                                    
                                    <p class="text-xs text-gray-500 mt-2">
                                        {{ \Carbon\Carbon::parse($notification['time'])->diffForHumans() }}
                                    </p>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex space-x-2 ml-4">
                                    @if($notification['action'] !== 'view_detail')
                                        <button wire:click="handleAction('{{ $notification['id'] }}', '{{ $notification['action'] }}')"
                                                class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                            @switch($notification['action'])
                                                @case('mark_arrived')
                                                    Tandai Tiba
                                                    @break
                                                @case('add_payment')
                                                    Bayar
                                                    @break
                                                @case('urgent_payment')
                                                    Bayar Segera
                                                    @break
                                                @case('view_financial')
                                                    Lihat Detail
                                                    @break
                                                @default
                                                    Aksi
                                            @endswitch
                                        </button>
                                    @endif
                                    
                                    <button wire:click="markAsRead('{{ $notification['id'] }}')"
                                            class="text-xs text-gray-500 hover:text-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p class="text-sm">Tidak ada notifikasi</p>
                    <p class="text-xs text-gray-400 mt-1">Semua agenda sudah terkendali</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if(count($notifications) > 0)
            <div class="p-3 border-t bg-gray-50">
                <div class="flex justify-between items-center">
                    <button wire:click="loadNotifications" 
                            class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    
                    <a href="{{ route('agenda.index') }}" 
                       class="text-xs text-blue-600 hover:text-blue-800">
                        Lihat Semua Agenda →
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto refresh notifications every 2 minutes
    setInterval(function() {
        if (typeof Livewire !== 'undefined') {
            Livewire.dispatch('refresh-notifications');
        }
    }, 120000);
    
    // Listen for real-time updates
    document.addEventListener('livewire:init', () => {
        Livewire.on('item-arrived', (event) => {
            // Refresh notifications when item status changes
            Livewire.dispatch('refresh-notifications');
        });
        
        Livewire.on('payment-added', (event) => {
            // Refresh notifications when payment is made
            Livewire.dispatch('refresh-notifications');
        });
    });
</script>
@endpush
