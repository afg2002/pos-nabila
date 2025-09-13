@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
            <p class="text-gray-600 mt-1">Kelola stok dan pergerakan inventory produk</p>
        </div>
        
        <div class="p-6">
            <!-- Stock Form Component -->
            @livewire('stock-form')
            
            <!-- Stock History Component -->
            <div class="mt-8">
                @livewire('stock-history')
            </div>
        </div>
    </div>
</div>
@endsection