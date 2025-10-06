<div class="p-6">
    <!-- Header dengan kontrol navigasi -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 space-y-4 lg:space-y-0">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Agenda Barang Masuk</h2>
            <p class="text-gray-600 mt-1">Kelola jadwal kedatangan barang dan pembayaran supplier</p>
        </div>
        
        <!-- View Mode Toggle -->
        <div class="flex space-x-2">
            <button wire:click="changeViewMode('month')" 
                    class="px-4 py-2 rounded-lg {{ $viewMode === 'month' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Bulan
            </button>
            <button wire:click="changeViewMode('week')" 
                    class="px-4 py-2 rounded-lg {{ $viewMode === 'week' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                Minggu
            </button>
            <button wire:click="toggleCashflowTab" 
                    class="px-4 py-2 rounded-lg {{ $showCashflowTab ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                💰 Cashflow
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <!-- Kalender Utama -->
        <div class="xl:col-span-3">
            <div class="bg-white rounded-lg shadow-sm border">
                <!-- Header Kalender -->
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="flex items-center space-x-4">
                        @if($viewMode === 'month')
                            <button wire:click="previousMonth" class="p-2 hover:bg-gray-100 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <h3 class="text-lg font-semibold">{{ $currentMonthName }}</h3>
                            <button wire:click="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @else
                            <button wire:click="previousWeek" class="p-2 hover:bg-gray-100 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <h3 class="text-lg font-semibold">{{ $currentWeekRange }}</h3>
                            <button wire:click="nextWeek" class="p-2 hover:bg-gray-100 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                    
                    <button wire:click="openEventModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Tambah Agenda
                    </button>
                </div>

                <!-- Kalender Grid -->
                @if($showCashflowTab)
                    <!-- Cashflow Tab Content -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                            <!-- Daily Totals -->
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
                                <h3 class="text-lg font-semibold mb-4">📊 Total Hari Ini</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Omset Total:</span>
                                        <span class="font-bold">Rp {{ number_format($dailyCashflow['total_omset'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Ecer:</span>
                                        <span class="font-bold">Rp {{ number_format($dailyCashflow['total_ecer'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Grosir:</span>
                                        <span class="font-bold">Rp {{ number_format($dailyCashflow['total_grosir'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Transaksi:</span>
                                        <span>{{ $dailyCashflow['transaction_count'] ?? 0 }} (E: {{ $dailyCashflow['ecer_count'] ?? 0 }}, G: {{ $dailyCashflow['grosir_count'] ?? 0 }})</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Weekly Totals -->
                            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
                                <h3 class="text-lg font-semibold mb-4">📈 Total Minggu Ini</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Omset Total:</span>
                                        <span class="font-bold">Rp {{ number_format($weeklyCashflow['total_omset'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Ecer:</span>
                                        <span class="font-bold">Rp {{ number_format($weeklyCashflow['total_ecer'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Grosir:</span>
                                        <span class="font-bold">Rp {{ number_format($weeklyCashflow['total_grosir'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Transaksi:</span>
                                        <span>{{ $weeklyCashflow['transaction_count'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Totals -->
                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
                                <h3 class="text-lg font-semibold mb-4">📅 Total Bulan Ini</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Omset Total:</span>
                                        <span class="font-bold">Rp {{ number_format($monthlyCashflow['total_omset'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Ecer:</span>
                                        <span class="font-bold">Rp {{ number_format($monthlyCashflow['total_ecer'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Grosir:</span>
                                        <span class="font-bold">Rp {{ number_format($monthlyCashflow['total_grosir'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Transaksi:</span>
                                        <span>{{ $monthlyCashflow['transaction_count'] ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grosir Payment Channels -->
                        <div class="bg-white rounded-lg border p-6 mb-6">
                            <h3 class="text-lg font-semibold mb-4">💳 Grosir by Payment Channel</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @if(isset($dailyCashflow['grosir_channels']))
                                    @foreach($dailyCashflow['grosir_channels'] as $channel => $amount)
                                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                                            <div class="text-sm text-gray-600 mb-1">
                                                @if($channel === 'cash')
                                                    💵 Cash
                                                @elseif($channel === 'qr')
                                                    📱 QR Code
                                                @elseif($channel === 'edc')
                                                    💳 EDC
                                                @else
                                                    {{ ucfirst($channel) }}
                                                @endif
                                            </div>
                                            <div class="text-lg font-bold text-gray-900">
                                                Rp {{ number_format($amount, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-span-3 text-center text-gray-500 py-4">
                                        Belum ada data grosir hari ini
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Cash Ledger Summary -->
                        <div class="bg-white rounded-lg border p-6">
                            <h3 class="text-lg font-semibold mb-4">💰 Ringkasan Kas</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Kas Masuk Hari Ini</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Total Masuk:</span>
                                            <span class="font-semibold text-green-600">
                                                Rp {{ number_format($dailyCashflow['cash_ledger']['total_income'] ?? 0, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-3">Kas Keluar Hari Ini</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Total Keluar:</span>
                                            <span class="font-semibold text-red-600">
                                                Rp {{ number_format($dailyCashflow['cash_ledger']['total_expense'] ?? 0, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t pt-4 mt-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-700">Saldo Hari Ini:</span>
                                    <span class="text-lg font-bold {{ ($dailyCashflow['cash_ledger']['net_amount'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($dailyCashflow['cash_ledger']['net_amount'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center mt-2 text-sm text-gray-600">
                                    <span>Total Transaksi:</span>
                                    <span>{{ $dailyCashflow['cash_ledger']['transaction_count'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($viewMode === 'month')
                    <!-- Month View -->
                    <div class="p-4">
                        <!-- Header Hari -->
                        <div class="grid grid-cols-7 gap-1 mb-2">
                            @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                                <div class="p-2 text-center text-sm font-medium text-gray-500">{{ $day }}</div>
                            @endforeach
                        </div>
                        
                        <!-- Tanggal -->
                        @foreach($calendarDays as $week)
                            <div class="grid grid-cols-7 gap-1 mb-1">
                                @foreach($week as $day)
                                    <div wire:click="selectDate('{{ $day['date'] }}')" 
                                         class="min-h-[100px] p-2 border rounded-lg cursor-pointer hover:bg-gray-50 
                                                {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-gray-50' }}
                                                {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="text-sm {{ $day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $day['day'] }}
                                            </span>
                                            @if($day['events']->count() > 0)
                                                <span class="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                                    {{ $day['events']->count() }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Event indicators -->
                                        <div class="space-y-1">
                                            @foreach($day['events'] as $event)
                                            <div class="text-xs p-1 rounded truncate relative group
                                                {{ $event['type'] === 'incoming' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $event['type'] === 'arrived' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $event['type'] === 'payment' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $event['type'] === 'reminder' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $event['type'] === 'meeting' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                {{ $event['type'] === 'task' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ $event['title'] }}
                                                @if(isset($event['source']) && $event['source'] === 'agenda_event')
                                                    <div class="absolute top-0 right-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <button wire:click.stop="editEvent({{ $event['id'] }})" 
                                                                class="text-xs bg-blue-500 text-white px-1 rounded hover:bg-blue-600">
                                                            Edit
                                                        </button>
                                                        <button wire:click.stop="deleteEvent({{ $event['id'] }})" 
                                                                class="text-xs bg-red-500 text-white px-1 rounded hover:bg-red-600 ml-1">
                                                            Del
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                            @if($day['events']->count() > 2)
                                                <div class="text-xs text-gray-500">+{{ $day['events']->count() - 2 }} lainnya</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Week View -->
                    <div class="p-4">
                        <div class="grid grid-cols-7 gap-4">
                            @foreach($weekDays as $day)
                                <div class="border rounded-lg p-4 min-h-[300px]
                                            {{ $day['isToday'] ? 'ring-2 ring-blue-500 bg-blue-50' : 'bg-white' }}">
                                    <div class="text-center mb-3">
                                        <div class="text-sm text-gray-500">{{ $day['dayName'] }}</div>
                                        <div class="text-lg font-semibold {{ $day['isToday'] ? 'text-blue-600' : 'text-gray-900' }}">
                                            {{ $day['day'] }}
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        @foreach($day['events'] as $event)
                                            <div wire:click="selectDate('{{ $day['date'] }}')" 
                                                 class="text-xs p-2 rounded cursor-pointer hover:opacity-80 relative group
                                                 {{ $event['type'] === 'incoming' ? 'bg-blue-100 text-blue-800 border-l-4 border-blue-500' : '' }}
                                                 {{ $event['type'] === 'arrived' ? 'bg-green-100 text-green-800 border-l-4 border-green-500' : '' }}
                                                 {{ $event['type'] === 'payment' ? 'bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500' : '' }}
                                                 {{ $event['type'] === 'reminder' ? 'bg-purple-100 text-purple-800 border-l-4 border-purple-500' : '' }}
                                                 {{ $event['type'] === 'meeting' ? 'bg-indigo-100 text-indigo-800 border-l-4 border-indigo-500' : '' }}
                                                 {{ $event['type'] === 'task' ? 'bg-gray-100 text-gray-800 border-l-4 border-gray-500' : '' }}">
                                                <div class="font-medium">{{ $event['title'] }}</div>
                                                <div class="text-xs opacity-75">{{ $event['description'] }}</div>
                                                @if(isset($event['amount']))
                                                    <div class="text-xs font-medium mt-1">Rp {{ number_format($event['amount'], 0, ',', '.') }}</div>
                                                @endif
                                                @if(isset($event['time']) && $event['time'] !== 'All Day')
                                                    <div class="text-xs font-medium mt-1">{{ $event['time'] }}</div>
                                                @endif
                                                
                                                @if(isset($event['source']) && $event['source'] === 'agenda_event')
                                                    <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <button wire:click.stop="editEvent({{ $event['id'] }})" 
                                                                class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">
                                                            Edit
                                                        </button>
                                                        <button wire:click.stop="deleteEvent({{ $event['id'] }})" 
                                                                class="text-xs bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 ml-1">
                                                            Del
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar - Upcoming Events -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <h3 class="text-lg font-semibold mb-4">Ringkasan Hari Ini</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Barang Datang</span>
                        <span class="font-semibold text-blue-600">{{ $upcomingEvents->where('type', 'incoming')->where('date', today())->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Jatuh Tempo</span>
                        <span class="font-semibold text-yellow-600">{{ $upcomingEvents->where('type', 'payment')->where('date', today())->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white rounded-lg shadow-sm border p-4">
                <h3 class="text-lg font-semibold mb-4">Agenda Mendatang</h3>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($upcomingEvents as $event)
                        <div class="border-l-4 pl-3 py-2
                            {{ $event['type'] === 'incoming' ? 'border-blue-500' : 'border-yellow-500' }}
                            {{ $event['urgency'] === 'high' ? 'bg-red-50' : ($event['urgency'] === 'medium' ? 'bg-yellow-50' : 'bg-gray-50') }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">{{ $event['title'] }}</div>
                                    <div class="text-xs text-gray-600 mt-1">{{ $event['description'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $event['date']->format('d M Y') }}
                                        @if($event['date']->isToday())
                                            <span class="text-red-600 font-medium">(Hari ini)</span>
                                        @elseif($event['date']->isTomorrow())
                                            <span class="text-yellow-600 font-medium">(Besok)</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-medium">Rp {{ number_format($event['amount'], 0, ',', '.') }}</div>
                                    <span class="inline-block px-2 py-1 text-xs rounded-full
                                        {{ $event['status'] === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $event['status'] === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $event['status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        {{ ucfirst($event['status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p>Tidak ada agenda mendatang</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Event -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="closeModal">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-semibold">Agenda {{ \Carbon\Carbon::parse($selectedDate)->format('d F Y') }}</h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    @if(count($selectedEvents) > 0)
                        <div class="space-y-4">
                            @foreach($selectedEvents as $event)
                                <div class="border rounded-lg p-4 relative group
                                    {{ $event['type'] === 'incoming' ? 'border-blue-200 bg-blue-50' : '' }}
                                    {{ $event['type'] === 'arrived' ? 'border-green-200 bg-green-50' : '' }}
                                    {{ $event['type'] === 'payment' ? 'border-yellow-200 bg-yellow-50' : '' }}
                                    {{ $event['type'] === 'reminder' ? 'border-purple-200 bg-purple-50' : '' }}
                                    {{ $event['type'] === 'meeting' ? 'border-indigo-200 bg-indigo-50' : '' }}
                                    {{ $event['type'] === 'task' ? 'border-gray-200 bg-gray-50' : '' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900">{{ $event['title'] }}</h4>
                                            <p class="text-gray-600 mt-1">{{ $event['description'] }}</p>
                                            @if(isset($event['time']) && $event['time'] !== 'All Day')
                                                <p class="text-sm text-gray-500 mt-1">Waktu: {{ $event['time'] }}</p>
                                            @endif
                                            <div class="mt-2 text-sm text-gray-500">
                                                <span class="inline-block px-2 py-1 rounded-full text-xs
                                                    {{ $event['status'] === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $event['status'] === 'arrived' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $event['status'] === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $event['status'] === 'partial_paid' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $event['status'] === 'fully_paid' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $event['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $event['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $event['status'])) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            @if(isset($event['amount']))
                                                <div class="text-lg font-semibold text-gray-900">
                                                    Rp {{ number_format($event['amount'], 0, ',', '.') }}
                                                </div>
                                            @endif
                                            
                                            @if(isset($event['source']) && $event['source'] === 'agenda_event')
                                                <div class="mt-2 space-x-2">
                                                    <button wire:click="editEvent({{ $event['id'] }})" 
                                                            class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">
                                                        Edit
                                                    </button>
                                                    <button wire:click="deleteEvent({{ $event['id'] }})" 
                                                            class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">
                                                        Delete
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>Tidak ada agenda untuk tanggal ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Include Event Modal -->
    @include('livewire.agenda-event-modal')

    <!-- JavaScript untuk event handling -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('event-saved', (event) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: event.message,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    alert(event.message);
                }
            });

            Livewire.on('event-updated', (event) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: event.message,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    alert(event.message);
                }
            });

            Livewire.on('event-deleted', (event) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: event.message,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    alert(event.message);
                }
            });

            Livewire.on('event-error', (event) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error!',
                        text: event.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(event.message);
                }
            });
        });
    </script>
</div>
