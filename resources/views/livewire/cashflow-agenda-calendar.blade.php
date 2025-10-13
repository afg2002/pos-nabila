<div class="space-y-6">
    <!-- Header with Month Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Kalender Cashflow</h2>
                    <p class="text-gray-600">{{ $currentMonth->format('F Y') }}</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <button wire:click="previousMonth" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button wire:click="nextMonth" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    @if($monthStats && $monthStats->total_days > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Omset</p>
                        <p class="text-xl font-bold text-blue-600">Rp {{ number_format($monthStats->total_omset, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Ecer</p>
                        <p class="text-xl font-bold text-green-600">Rp {{ number_format($monthStats->total_ecer, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Grosir</p>
                        <p class="text-xl font-bold text-purple-600">Rp {{ number_format($monthStats->total_grosir, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Payments</p>
                        <p class="text-xl font-bold text-orange-600">Rp {{ number_format($monthStats->total_cash + $monthStats->total_qr + $monthStats->total_edc, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-2 bg-orange-100 rounded-lg">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Calendar Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <!-- Weekday Headers -->
        <div class="grid grid-cols-7 gap-2 mb-4">
            <div class="text-center text-sm font-medium text-gray-700 py-2">Sen</div>
            <div class="text-center text-sm font-medium text-gray-700 py-2">Sel</div>
            <div class="text-center text-sm font-medium text-gray-700 py-2">Rab</div>
            <div class="text-center text-sm font-medium text-gray-700 py-2">Kam</div>
            <div class="text-center text-sm font-medium text-gray-700 py-2">Jum</div>
            <div class="text-center text-sm font-medium text-gray-700 py-2">Sab</div>
            <div class="text-center text-sm font-medium text-gray-700 py-2">Min</div>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7 gap-2">
            @foreach($calendarData as $day)
                <div class="calendar-day min-h-[100px] border rounded-lg p-2 cursor-pointer transition-all duration-200
                    {{ $day['isCurrentMonth'] ? 'bg-white hover:bg-gray-50' : 'bg-gray-50 opacity-50' }}
                    {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}
                    {{ $day['isSelected'] ? 'bg-blue-50 border-blue-500' : 'border-gray-200' }}"
                     wire:click="selectDate('{{ $day['date'] }}')">
                    
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-sm font-medium {{ $day['isToday'] ? 'text-blue-600' : 'text-gray-900' }}">
                            {{ $day['day'] }}
                        </span>
                        @if($day['agendas']->count() > 0)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $day['agendas']->count() }}
                            </span>
                        @endif
                    </div>

                    @if($day['agendas']->count() > 0)
                        <div class="space-y-1">
                            @foreach($day['agendas']->take(2) as $agenda)
                                <div class="text-xs p-1 bg-blue-50 rounded text-blue-700 truncate">
                                    Rp {{ number_format($agenda->total_omset, 0, ',', '.') }}
                                </div>
                            @endforeach
                            @if($day['agendas']->count() > 2)
                                <div class="text-xs text-gray-500 text-center">
                                    +{{ $day['agendas']->count() - 2 }} lagi
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Create Agenda Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeCreateModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Agenda Cashflow - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</h3>
                    
                    <form wire:submit.prevent="saveAgenda">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Modal Tracking</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        wire:model="createForm.capital_tracking_id"
                                        required>
                                    <option value="">Pilih Modal</option>
                                    @foreach($capitalTrackings as $capital)
                                        <option value="{{ $capital->id }}">{{ $capital->name }}</option>
                                    @endforeach
                                </select>
                                @error('createForm.capital_tracking_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Ecer</label>
                                    <input type="number"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           wire:model="createForm.total_ecer"
                                           step="0.01"
                                           min="0"
                                           required>
                                    @error('createForm.total_ecer') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Grosir</label>
                                    <input type="number"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           wire:model="createForm.total_grosir"
                                           step="0.01"
                                           min="0"
                                           required>
                                    @error('createForm.total_grosir') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Grosir Cash</label>
                                    <input type="number"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           wire:model="createForm.grosir_cash_hari_ini"
                                           step="0.01"
                                           min="0"
                                           required>
                                    @error('createForm.grosir_cash_hari_ini') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">QR Payment</label>
                                    <input type="number"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           wire:model="createForm.qr_payment_amount"
                                           step="0.01"
                                           min="0"
                                           required>
                                    @error('createForm.qr_payment_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">EDC Payment</label>
                                <input type="number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       wire:model="createForm.edc_payment_amount"
                                       step="0.01"
                                       min="0"
                                       required>
                                @error('createForm.edc_payment_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          wire:model="createForm.notes"
                                          rows="3"
                                          placeholder="Catatan opsional..."></textarea>
                                @error('createForm.notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200"
                                    wire:click="closeCreateModal">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Date Details Modal -->
    @if($showDetailsModal && $selectedDateAgendas->count() > 0)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeDetailsModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Agenda Cashflow - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</h3>
                    
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach($selectedDateAgendas as $agenda)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total Omset</label>
                                        <p class="text-lg font-bold text-blue-600">Rp {{ number_format($agenda->total_omset, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Modal</label>
                                        <p class="text-gray-900">{{ $agenda->capital_name ?? '-' }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total Ecer</label>
                                        <p class="text-gray-900">Rp {{ number_format($agenda->total_ecer, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total Grosir</label>
                                        <p class="text-gray-900">Rp {{ number_format($agenda->total_grosir, 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Grosir Cash</label>
                                        <p class="text-gray-900">Rp {{ number_format($agenda->grosir_cash_hari_ini, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">QR Payment</label>
                                        <p class="text-gray-900">Rp {{ number_format($agenda->qr_payment_amount, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">EDC Payment</label>
                                        <p class="text-gray-900">Rp {{ number_format($agenda->edc_payment_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                @if($agenda->notes)
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                                        <p class="text-gray-900">{{ $agenda->notes }}</p>
                                    </div>
                                @endif

                                <div class="pt-4 border-t mt-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Net Cashflow:</span>
                                        <span class="text-lg font-bold {{ ($agenda->total_omset - ($agenda->grosir_cash_hari_ini + $agenda->qr_payment_amount + $agenda->edc_payment_amount)) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            Rp {{ number_format($agenda->total_omset - ($agenda->grosir_cash_hari_ini + $agenda->qr_payment_amount + $agenda->edc_payment_amount), 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end mt-6">
                        <button wire:click="closeDetailsModal"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Agenda Detail Modal -->
    @if($showModal && $selectedAgenda)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Cashflow Agenda</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                <p class="text-gray-900">{{ \Carbon\Carbon::parse($selectedAgenda->date)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Dibuat Oleh</label>
                                <p class="text-gray-900">{{ $selectedAgenda->created_by_name ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Omset</label>
                                <p class="text-lg font-bold text-blue-600">Rp {{ number_format($selectedAgenda->total_omset, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Modal</label>
                                <p class="text-gray-900">{{ $selectedAgenda->capital_name ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Ecer</label>
                                <p class="text-gray-900">Rp {{ number_format($selectedAgenda->total_ecer, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Grosir</label>
                                <p class="text-gray-900">Rp {{ number_format($selectedAgenda->total_grosir, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Grosir Cash</label>
                                <p class="text-gray-900">Rp {{ number_format($selectedAgenda->grosir_cash_hari_ini, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">QR Payment</label>
                                <p class="text-gray-900">Rp {{ number_format($selectedAgenda->qr_payment_amount, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">EDC Payment</label>
                                <p class="text-gray-900">Rp {{ number_format($selectedAgenda->edc_payment_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        @if($selectedAgenda->notes)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Catatan</label>
                                <p class="text-gray-900">{{ $selectedAgenda->notes }}</p>
                            </div>
                        @endif

                        <div class="pt-4 border-t">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Net Cashflow:</span>
                                <span class="text-lg font-bold {{ ($selectedAgenda->total_omset - ($selectedAgenda->grosir_cash_hari_ini + $selectedAgenda->qr_payment_amount + $selectedAgenda->edc_payment_amount)) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($selectedAgenda->total_omset - ($selectedAgenda->grosir_cash_hari_ini + $selectedAgenda->qr_payment_amount + $selectedAgenda->edc_payment_amount), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button wire:click="closeModal"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors duration-200">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>