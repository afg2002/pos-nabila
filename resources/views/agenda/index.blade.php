@extends('layouts.app')

@section('title', 'Agenda Barang Masuk')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @livewire('agenda-calendar')
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Custom styles untuk kalender */
    .calendar-day {
        transition: all 0.2s ease;
    }
    
    .calendar-day:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .event-indicator {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }
    
    .modal-backdrop {
        backdrop-filter: blur(4px);
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto refresh setiap 5 menit untuk update real-time
    setInterval(function() {
        if (typeof Livewire !== 'undefined') {
            Livewire.emit('refreshCalendar');
        }
    }, 300000); // 5 menit
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            Livewire.emit('closeModal');
        }
        if (e.key === 'ArrowLeft' && e.ctrlKey) {
            Livewire.emit('previousMonth');
        }
        if (e.key === 'ArrowRight' && e.ctrlKey) {
            Livewire.emit('nextMonth');
        }
    });
</script>
@endpush