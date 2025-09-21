<!-- Modal Form Event -->
@if($showEventModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($editMode)
                            <i class="fas fa-edit me-2"></i>Edit Event
                        @else
                            <i class="fas fa-plus me-2"></i>Tambah Event Baru
                        @endif
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeEventModal"></button>
                </div>
                
                <form wire:submit.prevent="{{ $editMode ? 'updateEvent' : 'saveEvent' }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Judul Event <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="title" placeholder="Masukkan judul event">
                                    @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Tipe Event <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model="event_type">
                                        <option value="">Pilih Tipe</option>
                                        <option value="meeting">Meeting</option>
                                        <option value="reminder">Reminder</option>
                                        <option value="task">Task</option>
                                        <option value="appointment">Appointment</option>
                                        <option value="deadline">Deadline</option>
                                    </select>
                                    @error('event_type') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" wire:model="description" rows="3" placeholder="Deskripsi event (opsional)"></textarea>
                            @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Event <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" wire:model="event_date">
                                    @error('event_date') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Waktu Event</label>
                                    <input type="time" class="form-control" wire:model="event_time">
                                    @error('event_time') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Prioritas</label>
                                    <select class="form-select" wire:model="priority">
                                        <option value="low">Rendah</option>
                                        <option value="medium">Sedang</option>
                                        <option value="high">Tinggi</option>
                                        <option value="urgent">Mendesak</option>
                                    </select>
                                    @error('priority') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" wire:model="status">
                                        <option value="scheduled">Terjadwal</option>
                                        <option value="in_progress">Sedang Berlangsung</option>
                                        <option value="completed">Selesai</option>
                                        <option value="cancelled">Dibatalkan</option>
                                        <option value="postponed">Ditunda</option>
                                    </select>
                                    @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi</label>
                            <input type="text" class="form-control" wire:model="location" placeholder="Lokasi event (opsional)">
                            @error('location') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Peserta</label>
                            <textarea class="form-control" wire:model="attendees" rows="2" placeholder="Daftar peserta (pisahkan dengan koma)"></textarea>
                            @error('attendees') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" wire:model="notes" rows="2" placeholder="Catatan tambahan"></textarea>
                            @error('notes') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEventModal">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            @if($editMode)
                                <i class="fas fa-save me-1"></i>Update Event
                            @else
                                <i class="fas fa-plus me-1"></i>Simpan Event
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

                    <!-- Type and Priority -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Event *</label>
                            <select wire:model="event_type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="reminder">Reminder</option>
                                <option value="meeting">Meeting</option>
                                <option value="task">Task</option>
                                <option value="appointment">Appointment</option>
                                <option value="deadline">Deadline</option>
                                <option value="other">Lainnya</option>
                            </select>
                            @error('event_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Prioritas *</label>
                            <select wire:model="priority" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="low">Rendah</option>
                                <option value="medium">Sedang</option>
                                <option value="high">Tinggi</option>
                                <option value="urgent">Mendesak</option>
                            </select>
                            @error('priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select wire:model="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending">Pending</option>
                            <option value="in_progress">Dalam Proses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                        <input type="text" wire:model="location" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Lokasi event (opsional)">
                        @error('location') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Attendees -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Peserta</label>
                        <input type="text" wire:model="attendees" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nama peserta (pisahkan dengan koma)">
                        @error('attendees') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Contoh: John Doe, Jane Smith, Admin</p>
                    </div>

                    <!-- Reminder -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reminder (menit sebelum event)</label>
                        <select wire:model="reminder_minutes" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="0">Tidak ada reminder</option>
                            <option value="5">5 menit</option>
                            <option value="15">15 menit</option>
                            <option value="30">30 menit</option>
                            <option value="60">1 jam</option>
                            <option value="120">2 jam</option>
                            <option value="1440">1 hari</option>
                        </select>
                        @error('reminder_minutes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea wire:model="notes" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Catatan tambahan (opsional)"></textarea>
                        @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="closeEventModal" 
                                class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ $editMode ? 'Update Event' : 'Simpan Event' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif