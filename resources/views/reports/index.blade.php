@extends('layouts.app')

@section('content')
    @livewire('report-management')
@endsection

@push('styles')
<style>
    /* Custom styles for report management */
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    
    .report-card {
        transition: all 0.3s ease;
    }
    
    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .filter-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .export-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: all 0.3s ease;
    }
    
    .export-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .report-card {
            background-color: #1f2937;
            border-color: #374151;
        }
        
        .chart-container {
            background-color: #1f2937;
        }
    }
    
    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        
        .report-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
        
        .chart-container {
            height: 300px;
        }
    }
    
    /* Animation for loading states */
    .pulse-animation {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
    
    /* Custom scrollbar for tables */
    .table-container {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }
    
    .table-container::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }
    
    .table-container::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 4px;
    }
    
    .table-container::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 4px;
    }
    
    .table-container::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
</style>
@endpush