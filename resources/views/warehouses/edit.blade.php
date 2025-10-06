@extends('layouts.app')

@section('title', 'Edit Gudang')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">📦 Edit Gudang: {{ $warehouse->name }}</h3>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('warehouses.update', $warehouse) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Gudang <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $warehouse->name) }}" 
                                           placeholder="Contoh: Gudang Utama"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Kode Gudang <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $warehouse->code) }}" 
                                           placeholder="Contoh: GDG001"
                                           style="text-transform: uppercase;"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Kode unik untuk identifikasi gudang</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Tipe Gudang <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="">Pilih Tipe Gudang</option>
                                        <option value="store" {{ old('type', $warehouse->type) === 'store' ? 'selected' : '' }}>
                                            🏪 Toko
                                        </option>
                                        <option value="warehouse" {{ old('type', $warehouse->type) === 'warehouse' ? 'selected' : '' }}>
                                            📦 Gudang
                                        </option>
                                        <option value="kiosk" {{ old('type', $warehouse->type) === 'kiosk' ? 'selected' : '' }}>
                                            🏬 Kiosk
                                        </option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="branch" class="form-label">Cabang</label>
                                    <input type="text" 
                                           class="form-control @error('branch') is-invalid @enderror" 
                                           id="branch" 
                                           name="branch" 
                                           value="{{ old('branch', $warehouse->branch) }}" 
                                           placeholder="Contoh: Cabang Jakarta Pusat">
                                    @error('branch')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3" 
                                      placeholder="Masukkan alamat lengkap gudang">{{ old('address', $warehouse->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telepon</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $warehouse->phone) }}" 
                                           placeholder="Contoh: 021-12345678">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input @error('is_default') is-invalid @enderror" 
                                               type="checkbox" 
                                               id="is_default" 
                                               name="is_default" 
                                               value="1" 
                                               {{ old('is_default', $warehouse->is_default) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_default">
                                            <strong>Jadikan sebagai gudang default</strong>
                                        </label>
                                        @error('is_default')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Gudang default akan digunakan sebagai pilihan utama</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Gudang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('code').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});
</script>
@endpush