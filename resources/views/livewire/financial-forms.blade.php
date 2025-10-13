<div class="financial-forms-container">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Form Pencatatan Keuangan</h6>
                <div class="btn-group" role="group">
                    <button type="button"
                            class="btn {{ $activeForm === 'cash' ? 'btn-primary' : 'btn-outline-primary' }}"
                            wire:click="setActiveForm('cash')">
                        <i class="fas fa-money-bill-wave me-1"></i>Kas
                    </button>
                    <button type="button"
                            class="btn {{ $activeForm === 'payment' ? 'btn-primary' : 'btn-outline-primary' }}"
                            wire:click="setActiveForm('payment')">
                        <i class="fas fa-credit-card me-1"></i>Pembayaran
                    </button>
                    <button type="button"
                            class="btn {{ $activeForm === 'receivable' ? 'btn-primary' : 'btn-outline-primary' }}"
                            wire:click="setActiveForm('receivable')">
                        <i class="fas fa-file-invoice-dollar me-1"></i>Piutang
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card-body border-bottom">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Pencarian</label>
                    <input type="text" class="form-control" wire:model.live="searchTerm" placeholder="Cari...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" wire:model="dateFrom">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" wire:model="dateTo">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" wire:model="statusFilter">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Lunas</option>
                        <option value="overdue">Terlambat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipe</label>
                    <select class="form-select" wire:model="typeFilter">
                        <option value="">Semua Tipe</option>
                        <option value="in">Masuk</option>
                        <option value="out">Keluar</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-outline-secondary d-block" wire:click="resetFilters">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if($activeForm === 'cash')
                <div class="cash-form">
                    <h5 class="mb-3">Pencatatan Saldo Kas</h5>
                    <form wire:submit.prevent="{{ $editMode && $editingType === 'cash' ? 'updateCashBalance' : 'saveCashBalance' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Transaksi</label>
                                    <select class="form-select" wire:model="cash_type">
                                        <option value="in">Kas Masuk</option>
                                        <option value="out">Kas Keluar</option>
                                    </select>
                                    @error('cash_type') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select class="form-select" wire:model="cash_category">
                                        <option value="operational">Operasional</option>
                                        <option value="sales">Penjualan</option>
                                        <option value="purchase">Pembelian</option>
                                        <option value="investment">Investasi</option>
                                        <option value="loan">Pinjaman</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                    @error('cash_category') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah (Rp)</label>
                            <input type="number" class="form-control" wire:model="cash_amount"
                                   placeholder="0" min="0" step="0.01">
                            @error('cash_amount') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" wire:model="cash_notes"
                                      rows="3" placeholder="Keterangan transaksi..."></textarea>
                            @error('cash_notes') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            @if($editMode && $editingType === 'cash')
                                <button type="button" class="btn btn-secondary" wire:click="resetForm">
                                    <i class="fas fa-times me-1"></i>Batal
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>Update Kas
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Kas
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            @endif

            @if($activeForm === 'payment')
                <div class="payment-form">
                    <h5 class="mb-3">Pencatatan Pembayaran</h5>
                    <form wire:submit.prevent="savePayment">
                        <div class="mb-3">
                            <label class="form-label">Pilih Tagihan</label>
                            <select class="form-select" wire:model="payment_schedule_id">
                                <option value="">Pilih tagihan yang akan dibayar...</option>
                                @foreach($pendingPayments as $payment)
                                    @php
                                        $outstanding = max(0, $payment->amount - $payment->paid_amount);
                                    @endphp
                                    <option value="{{ $payment->id }}">
                                        {{ $payment->incomingGoods->supplier_name }} - {{ $payment->incomingGoods->invoice_number }}
                                        (Sisa: Rp {{ number_format($outstanding, 0, ',', '.') }}) - Jatuh tempo {{ $payment->due_date->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_schedule_id') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jumlah Bayar (Rp)</label>
                                    <input type="number" class="form-control" wire:model="payment_amount"
                                           placeholder="0" min="0" step="0.01">
                                    @error('payment_amount') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Metode Pembayaran</label>
                                    <select class="form-select" wire:model="payment_method">
                                        <option value="cash">Tunai</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="check">Cek</option>
                                        <option value="credit">Kredit</option>
                                    </select>
                                    @error('payment_method') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" wire:model="payment_notes"
                                      rows="3" placeholder="Keterangan pembayaran..."></textarea>
                            @error('payment_notes') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-credit-card me-1"></i>Bayar
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if($activeForm === 'receivable')
                <div class="receivable-form">
                    <h5 class="mb-3">Pencatatan Piutang</h5>
                    <form wire:submit.prevent="{{ $editMode && $editingType === 'receivable' ? 'updateReceivable' : 'saveReceivable' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Supplier</label>
                                    <input type="text" class="form-control" wire:model="receivable_supplier_name"
                                           placeholder="Nama supplier...">
                                    @error('receivable_supplier_name') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" wire:model="receivable_status">
                                        <option value="pending">Pending</option>
                                        <option value="partial">Sebagian</option>
                                        <option value="paid">Lunas</option>
                                        <option value="overdue">Terlambat</option>
                                    </select>
                                    @error('receivable_status') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jumlah Piutang (Rp)</label>
                                    <input type="number" class="form-control" wire:model="receivable_amount"
                                           placeholder="0" min="0" step="0.01">
                                    @error('receivable_amount') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Jatuh Tempo</label>
                                    <input type="date" class="form-control" wire:model="receivable_due_date">
                                    @error('receivable_due_date') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" wire:model="receivable_notes"
                                      rows="3" placeholder="Keterangan piutang..."></textarea>
                            @error('receivable_notes') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            @if($editMode && $editingType === 'receivable')
                                <button type="button" class="btn btn-secondary" wire:click="resetForm">
                                    <i class="fas fa-times me-1"></i>Batal
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>Update Piutang
                                </button>
                            @else
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-file-invoice-dollar me-1"></i>Simpan Piutang
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Tabel Piutang -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Piutang</h6>
                <button type="button" class="btn btn-success btn-sm" wire:click="exportReceivables">
                    <i class="fas fa-download me-1"></i>Export CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Jumlah</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivables as $receivable)
                            <tr>
                                <td>{{ $receivable->created_at->format('d/m/Y') }}</td>
                                <td>{{ $receivable->customer_name }}</td>
                                <td class="text-success">Rp {{ number_format($receivable->amount, 0, ',', '.') }}</td>
                                <td>{{ $receivable->due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $receivable->status === 'paid' ? 'success' : ($receivable->status === 'overdue' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($receivable->status) }}
                                    </span>
                                </td>
                                <td>{{ $receivable->notes ?: '-' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                wire:click="editReceivable({{ $receivable->id }})"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                wire:click="deleteReceivable({{ $receivable->id }})"
                                                onclick="return confirm('Yakin ingin menghapus piutang ini?')"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data piutang</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Rekap Kas Terbaru</h6>
                <button type="button" class="btn btn-success btn-sm" wire:click="exportCashTransactions">
                    <i class="fas fa-download me-1"></i>Export CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('date')" class="text-decoration-none">
                                    Tanggal
                                    @if($sortBy === 'date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('type')" class="text-decoration-none">
                                    Jenis
                                    @if($sortBy === 'type')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Kas Masuk</th>
                            <th>Kas Keluar</th>
                            <th>Saldo Akhir</th>
                            <th>Keterangan</th>
                            <th>Pencatat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-info text-uppercase">{{ $transaction->type }}</span>
                                </td>
                                <td class="text-success">Rp {{ number_format($transaction->cash_in, 0, ',', '.') }}</td>
                                <td class="text-danger">Rp {{ number_format($transaction->cash_out, 0, ',', '.') }}</td>
                                <td class="fw-semibold">Rp {{ number_format($transaction->closing_balance, 0, ',', '.') }}</td>
                                <td>{{ $transaction->description ?: '-' }}</td>
                                <td>{{ $transaction->creator->name ?? 'System' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                wire:click="editCashBalance({{ $transaction->id }})"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                wire:click="deleteCashBalance({{ $transaction->id }})"
                                                onclick="return confirm('Yakin ingin menghapus transaksi kas ini?')"
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada catatan kas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('cash-saved', (event) => showNotification(event.message, event.type));
    Livewire.on('cash-error', (event) => showNotification(event.message, event.type));
    Livewire.on('payment-saved', (event) => showNotification(event.message, event.type));
    Livewire.on('payment-error', (event) => showNotification(event.message, event.type));
    Livewire.on('receivable-saved', (event) => showNotification(event.message, event.type));
    Livewire.on('receivable-error', (event) => showNotification(event.message, event.type));
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
@endpush
