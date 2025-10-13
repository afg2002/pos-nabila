@extends('layouts.app')

@section('title', 'Agenda Cashflow')

@section('content')
<div class="space-y-6">
    <!-- Tab Navigation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('calendar')" id="calendar-tab" class="tab-button text-blue-600 border-b-2 border-blue-600 py-4 px-1 text-sm font-medium">
                    <i class="fas fa-calendar mr-2"></i>Kalender View
                </button>
                <button onclick="showTab('management')" id="management-tab" class="tab-button text-gray-500 border-b-2 border-transparent py-4 px-1 text-sm font-medium hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-table mr-2"></i>Management View
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="p-6">
            <div id="calendar-content" class="tab-content">
                @livewire('cashflow-agenda-calendar')
            </div>
            <div id="management-content" class="tab-content hidden">
                @livewire('cashflow-agenda-management')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
        button.classList.add('text-gray-500', 'border-b-2', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
    });
    
    // Show selected content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Activate selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('text-gray-500', 'border-b-2', 'border-transparent', 'hover:text-gray-700', 'hover:border-gray-300');
    activeTab.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
}
</script>
@endpush