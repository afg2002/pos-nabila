@if(session('alert'))
    <div x-data="{ 
        show: true, 
        timer: {{ session('alert.timer', 5000) }},
        init() {
            if (this.timer > 0) {
                setTimeout(() => { this.show = false }, this.timer);
            }
        }
    }" 
    x-show="show" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    class="alert alert-{{ session('alert.type', 'info') }} alert-dismissible fade show mb-3" 
    role="alert">
        
        <div class="d-flex align-items-center">
            @switch(session('alert.type', 'info'))
                @case('success')
                    <i class="fas fa-check-circle me-2"></i>
                    @break
                @case('error')
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    @break
                @case('warning')
                    <i class="fas fa-exclamation-circle me-2"></i>
                    @break
                @case('info')
                default:
                    <i class="fas fa-info-circle me-2"></i>
                    @break
            @endswitch
            
            <span>{{ session('alert.message') }}</span>
        </div>
        
        @if(session('alert.dismissible', true))
            <button type="button" 
                    class="btn-close" 
                    @click="show = false"
                    aria-label="Close">
            </button>
        @endif
        
        @if(session('alert.timer', 0) > 0)
            <div class="alert-timer-bar" 
                 style="position: absolute; bottom: 0; left: 0; height: 3px; background: rgba(255,255,255,0.3); animation: timer-countdown {{ session('alert.timer') }}ms linear forwards;">
            </div>
        @endif
    </div>

    <style>
        @keyframes timer-countdown {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        .alert {
            position: relative;
            overflow: hidden;
        }
        
        .alert-success {
            background-color: #d1edff;
            border-color: #bee5eb;
            color: #0c5460;
        }
        
        .alert-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
@endif

{{-- Backward compatibility for old session flash messages --}}
@if(session('success') && !session('alert'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 5000)"
         x-transition
         class="alert alert-success alert-dismissible fade show mb-3" 
         role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
    </div>
@endif

@if(session('error') && !session('alert'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 8000)"
         x-transition
         class="alert alert-danger alert-dismissible fade show mb-3" 
         role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
    </div>
@endif

@if(session('message') && !session('alert'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 5000)"
         x-transition
         class="alert alert-info alert-dismissible fade show mb-3" 
         role="alert">
        <i class="fas fa-info-circle me-2"></i>
        {{ session('message') }}
        <button type="button" class="btn-close" @click="show = false" aria-label="Close"></button>
    </div>
@endif