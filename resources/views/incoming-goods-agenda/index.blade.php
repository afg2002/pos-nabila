@extends('layouts.app')

@section('title', 'Incoming Goods Agenda')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Incoming Goods Agenda</h1>
                <p class="text-gray-600">Manage incoming goods schedules and tracking</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('cashflow-agenda.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-arrow-right mr-2"></i>Go to Cashflow Agenda
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        @livewire('incoming-goods-agenda-management')
    </div>
</div>
@endsection