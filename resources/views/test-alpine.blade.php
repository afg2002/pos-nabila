@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold mb-4">Alpine.js Test Page</h1>
        
        <!-- Basic Alpine.js test -->
        <div x-data="{ message: 'Hello Alpine!' }" class="mb-4">
            <p x-text="message" class="text-green-600"></p>
        </div>
        
        <!-- Livewire test without $wire -->
        <div class="mb-4">
            <p class="text-blue-600">Basic Livewire test (no $wire expressions)</p>
        </div>
        
        <!-- Test various $wire expressions -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold">$wire Expression Tests:</h2>
            
            <!-- Test 1: Simple property binding -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Test Input:</label>
                <input type="text" wire:model="testProperty" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Type something...">
            </div>
            
            <!-- Test 2: Method call -->
            <div>
                <button wire:click="testMethod" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Test Method Call
                </button>
            </div>
            
            <!-- Test 3: Loading state -->
            <div>
                <button wire:click="slowMethod" wire:loading.attr="disabled" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <span wire:loading.remove>Slow Method</span>
                    <span wire:loading>Loading...</span>
                </button>
            </div>
        </div>
        
        <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <p class="text-yellow-800">This page is designed to test Alpine.js and Livewire integration to isolate any syntax errors.</p>
        </div>
    </div>
</div>
@endsection