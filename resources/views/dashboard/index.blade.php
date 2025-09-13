@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
               
            </div>
        </div>
    </div>

    @livewire('dashboard')
</div>
@endsection

@push('styles')
<style>
    .stats-card {
        transition: transform 0.2s ease-in-out;
    }
    .stats-card:hover {
        transform: translateY(-2px);
    }
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
    .export-buttons .btn {
        margin-right: 8px;
        margin-bottom: 8px;
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
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto refresh dashboard setiap 5 menit
    setInterval(function() {
        Livewire.emit('refreshDashboard');
    }, 300000); // 5 menit
    
    // Handle export loading states
    document.addEventListener('livewire:load', function () {
        Livewire.on('exportStarted', function() {
            // Show loading state
            const exportButtons = document.querySelectorAll('.export-btn');
            exportButtons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
            });
        });
        
        Livewire.on('exportCompleted', function() {
            // Reset button states
            setTimeout(() => {
                const exportButtons = document.querySelectorAll('.export-btn');
                exportButtons.forEach(btn => {
                    btn.disabled = false;
                    if (btn.classList.contains('btn-success')) {
                        btn.innerHTML = '<i class="fas fa-file-excel me-1"></i>Export Excel';
                    } else if (btn.classList.contains('btn-danger')) {
                        btn.innerHTML = '<i class="fas fa-file-pdf me-1"></i>Export PDF';
                    }
                });
            }, 1000);
        });
    });
</script>
@endpush