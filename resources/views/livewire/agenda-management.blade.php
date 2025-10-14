<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Agenda Management</h1>
                    <p class="text-gray-600">Kelola cashflow dan purchase order dalam satu tempat</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification for messages -->
    @if(session()->has('message'))
        <div class="rounded-md p-4 mb-4 {{ session('message.type') === 'success' ? 'bg-green-50 border border-green-200' : (session('message.type') === 'error' ? 'bg-red-50 border border-red-200' : 'bg-blue-50 border border-blue-200') }}">
            <div class="flex">
                <div class="flex-shrink-0">
                    @if(session('message.type') === 'success')
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    @elseif(session('message.type') === 'error')
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium {{ session('message.type') === 'success' ? 'text-green-800' : (session('message.type') === 'error' ? 'text-red-800' : 'text-blue-800') }}">
                        {{ session('message.message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button 
                    wire:click="switchTab('cashflow')"
                    class="{{ $activeTab === 'cashflow' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300' }} py-4 px-1 text-sm font-medium transition-colors duration-200">
                    <i class="fas fa-money-bill-wave mr-2"></i>Agenda Cashflow
                </button>
                <button 
                    wire:click="switchTab('purchase-order')"
                    class="{{ $activeTab === 'purchase-order' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300' }} py-4 px-1 text-sm font-medium transition-colors duration-200">
                    <i class="fas fa-box mr-2"></i>Agenda Barang Datang
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="p-6">
            <!-- Cashflow Tab -->
            @if($activeTab === 'cashflow')
                <div class="space-y-6">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($monthlySummary['total_omset'], 0, ',', '.') }}</p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Expenses</p>
                                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($monthlySummary['total_expenses'], 0, ',', '.') }}</p>
                                </div>
                                <div class="p-3 bg-red-100 rounded-lg">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Net Cashflow</p>
                                    <p class="text-2xl font-bold {{ $netCashflow >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($netCashflow, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Active Days</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $monthlySummary['days_with_data'] }}/{{ $monthlySummary['total_days'] }}</p>
                                </div>
                                <div class="p-3 bg-purple-100 rounded-lg">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar View -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Cashflow Calendar</h3>
                                <div class="flex items-center space-x-2">
                                    <button wire:click="previousMonth" class="p-2 text-gray-600 hover:text-gray-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium text-gray-900 min-w-[120px] text-center">
                                        {{ \Carbon\Carbon::parse($filterMonth . '-01')->format('F Y') }}
                                    </span>
                                    <button wire:click="nextMonth" class="p-2 text-gray-600 hover:text-gray-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <!-- Day Headers -->
                            <div class="grid grid-cols-7 gap-2 mb-4">
                                @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                                    <div class="text-center text-sm font-medium text-gray-600 py-2">{{ $day }}</div>
                                @endforeach
                            </div>
                            
                            <!-- Calendar Grid -->
                            <div class="grid grid-cols-7 gap-2">
                                @foreach($cashflowData as $day)
                                    <div class="min-h-[80px] p-2 border rounded-lg cursor-pointer transition-all duration-200
                                                {{ $day['isCurrentMonth'] ? 'bg-white border-gray-200 hover:border-blue-300' : 'bg-gray-50 border-gray-100' }} 
                                                {{ $day['isToday'] ? 'ring-2 ring-blue-500 border-blue-300 bg-blue-50' : '' }}
                                                {{ $day['isSelected'] ? 'ring-2 ring-green-500 border-green-300 bg-green-50' : '' }}"
                                         wire:click="handleDateSelection('{{ $day['date'] }}')">
                                        
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="text-sm font-medium {{ $day['isCurrentMonth'] ? ($day['isToday'] ? 'text-blue-700' : 'text-gray-900') : 'text-gray-400' }}">
                                                {{ $day['day'] }}
                                            </span>
                                            
                                            @if($day['hasData'])
                                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            @endif
                                        </div>
                                        
                                        @if($day['hasData'] && $day['cashflow'])
                                            <div class="text-xs text-gray-600">
                                                <div>Rp {{ number_format($day['cashflow']->total_omset, 0, ',', '.') }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Today's Cashflow Detail -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Cashflow Detail - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Revenue</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Total Ecer:</span>
                                            <span class="text-sm font-medium">Rp {{ number_format($todayCashflow->total_ecer, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Total Grosir:</span>
                                            <span class="text-sm font-medium">Rp {{ number_format($todayCashflow->total_grosir, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between pt-2 border-t">
                                            <span class="text-sm font-medium text-gray-700">Total Omset:</span>
                                            <span class="text-sm font-bold text-blue-600">Rp {{ number_format($todayCashflow->total_omset, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Payment Methods</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Cash:</span>
                                            <span class="text-sm font-medium">Rp {{ number_format($todayCashflow->grosir_cash_hari_ini, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">QR Payment:</span>
                                            <span class="text-sm font-medium">Rp {{ number_format($todayCashflow->qr_payment_amount, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">EDC Payment:</span>
                                            <span class="text-sm font-medium">Rp {{ number_format($todayCashflow->edc_payment_amount, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between pt-2 border-t">
                                            <span class="text-sm font-medium text-gray-700">Total Payments:</span>
                                            <span class="text-sm font-bold text-green-600">Rp {{ number_format($paymentBreakdown['total'], 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Annual Summary -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Annual Summary - {{ now()->year }}</h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="mb-6">
                                <canvas id="annualCashflowChart" width="400" height="200"></canvas>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Total Revenue</p>
                                    <p class="text-xl font-bold text-green-600">
                                        Rp {{ number_format(array_sum(array_column($annualData, 'revenue')), 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="text-center p-4 bg-red-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Total Expenses</p>
                                    <p class="text-xl font-bold text-red-600">
                                        Rp {{ number_format(array_sum(array_column($annualData, 'expenses')), 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-gray-600">Net Profit</p>
                                    <p class="text-xl font-bold text-blue-600">
                                        Rp {{ number_format(array_sum(array_column($annualData, 'net_cashflow')), 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Purchase Order Tab -->
            @if($activeTab === 'purchase-order')
                @livewire('purchase-order-agenda-tab')
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Annual Cashflow Chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('annualCashflowChart');
        if (ctx && window.agendaManagementData) {
            const chartData = window.agendaManagementData.annualData;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.map(item => item.month_name),
                    datasets: [
                        {
                            label: 'Revenue',
                            data: chartData.map(item => item.revenue),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.1
                        },
                        {
                            label: 'Expenses',
                            data: chartData.map(item => item.expenses),
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.1
                        },
                        {
                            label: 'Net Cashflow',
                            data: chartData.map(item => item.net_cashflow),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

@push('scripts')
<script>
    // Pass data to JavaScript
    window.agendaManagementData = {
        annualData: @json($annualData),
    };
</script>
@endpush