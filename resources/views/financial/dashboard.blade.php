@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Dashboard Keuangan</h1>
            <p class="text-muted">Monitoring kondisi keuangan real-time</p>
        </div>
        <div class="d-flex align-items-center space-x-3">
            <!-- Notification Component -->
            @livewire('agenda-notifications')
            
            <!-- Quick Actions -->
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-plus me-2"></i>Aksi Cepat
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('agenda.index') }}">
                        <i class="fas fa-calendar me-2"></i>Lihat Agenda
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="openCashModal()">
                        <i class="fas fa-money-bill me-2"></i>Catat Kas
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="openPaymentModal()">
                        <i class="fas fa-credit-card me-2"></i>Bayar Tagihan
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('financial.reports') }}">
                        <i class="fas fa-chart-line me-2"></i>Laporan Keuangan
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Financial Dashboard Component -->
    @livewire('financial-dashboard')
    
    <!-- Quick Info Cards -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Formula Perhitungan
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                Posisi Kas Bersih = Saldo Kas - Total Piutang
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Auto Refresh
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                Data diperbarui setiap 30 detik
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Cash Modal -->
<div class="modal fade" id="cashModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Saldo Kas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cashForm">
                    <div class="mb-3">
                        <label class="form-label">Jumlah Kas</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveCash()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bayar Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <div class="mb-3">
                        <label class="form-label">Pilih Tagihan</label>
                        <select class="form-select" name="payment_schedule_id" required>
                            <option value="">Pilih tagihan...</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Bayar</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select class="form-select" name="payment_method" required>
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="check">Cek</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="savePayment()">Bayar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCashModal() {
    new bootstrap.Modal(document.getElementById('cashModal')).show();
}

function openPaymentModal() {
    // Load pending payments
    fetch('/api/pending-payments')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('#paymentModal select[name="payment_schedule_id"]');
            select.innerHTML = '<option value="">Pilih tagihan...</option>';
            data.forEach(payment => {
                select.innerHTML += `<option value="${payment.id}" data-amount="${payment.amount}">
                    ${payment.supplier_name} - Rp ${payment.amount.toLocaleString('id-ID')} 
                    (Jatuh tempo: ${payment.due_date})
                </option>`;
            });
        });
    
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}

function saveCash() {
    const form = document.getElementById('cashForm');
    const formData = new FormData(form);
    
    fetch('/api/cash-balance', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('cashModal')).hide();
            Livewire.dispatch('refresh-financial-data');
            // Show success notification
        }
    });
}

function savePayment() {
    const form = document.getElementById('paymentForm');
    const formData = new FormData(form);
    
    fetch('/api/payments', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            Livewire.dispatch('refresh-financial-data');
            // Show success notification
        }
    });
}

// Auto-populate payment amount when selecting payment schedule
document.addEventListener('change', function(e) {
    if (e.target.name === 'payment_schedule_id') {
        const selectedOption = e.target.selectedOptions[0];
        if (selectedOption.dataset.amount) {
            document.querySelector('#paymentModal input[name="amount"]').value = selectedOption.dataset.amount;
        }
    }
});
</script>
@endpush