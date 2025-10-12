# Livewire Frontend Debugging Guide

## Masalah
Tombol edit dan delete pada halaman stock history tidak merespon saat diklik. Tidak ada action yang terjadi setelah klik.

## Langkah-Langkah Debugging

### 1. Buka Halaman Stock History
Buka halaman stock history di browser Anda.

### 2. Buka Developer Tools
Tekan F12 atau klik kanan dan pilih "Inspect" untuk membuka developer tools.

### 3. Periksa Console
- Pergi ke tab Console
- Cari pesan error yang ditandai dengan warna merah
- Refresh halaman dan periksa kembali apakah ada error baru

### 4. Periksa Livewire
- Di tab Console, jalankan perintah: `console.log(window.Livewire)`
- Jika Livewire sudah dimuat, Anda akan melihat objek dengan metode Livewire
- Jika Livewire tidak dimuat, ada masalah dengan JavaScript

### 5. Periksa Network Request
- Pergi ke tab Network
- Klik tombol edit atau delete
- Cari request XHR ke `/livewire/message`
- Periksa apakah request dikirim dan apa responsenya

### 6. Periksa HTML Structure
- Pergi ke tab Elements
- Cari tombol edit dan delete
- Periksa apakah tombol memiliki atribut `wire:click` yang benar
- Periksa apakah ada syntax error di HTML

### 7. Test dengan Console
- Di tab Console, coba jalankan: `Livewire.find('stock-history').openEditModal(1)`
- Ganti 1 dengan ID stock movement yang valid
- Periksa apakah ada error atau response

### 8. Periksa Browser Compatibility
- Coba gunakan browser berbeda (Chrome, Firefox, Safari)
- Periksa apakah masalah spesifik ke satu browser

## Solusi yang Telah Diterapkan

### 1. Menambahkan Type pada Tombol
Menambahkan `type="button"` pada tombol untuk mencegah form submission:

```html
<button type="button" wire:click="openEditModal({{ $movement->id }})">
```

### 2. Menambahkan Loading Indicators
Menambahkan `wire:loading.attr="disabled"` dan `wire:loading.class="fa-spin"` untuk memberikan indikator visual saat proses berjalan.

### 3. Menambahkan Debugging Script
Menambahkan script untuk memeriksa apakah Livewire sudah dimuat:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Stock History page loaded');
    console.log('Livewire available:', typeof window.Livewire !== 'undefined');
    
    if (typeof window.Livewire !== 'undefined') {
        console.log('Livewire version:', window.Livewire.version);
    }
});
```

## Jika Masalah Masih Ada

Jika tombol masih tidak merespon setelah perbaikan di atas, silakan:

1. **Clear Browser Cache**
   - Tekan Ctrl+Shift+R (Windows/Linux) atau Cmd+Shift+R (Mac)
   - Atau clear cache melalui browser settings

2. **Periksa Error di Laravel Log**
   - Buka file `storage/logs/laravel.log`
   - Cari error terkait Livewire atau stock movement

3. **Restart Laravel Services**
   - Jalankan `php artisan cache:clear`
   - Jalankan `php artisan config:clear`
   - Jalankan `php artisan view:clear`

4. **Periksa Livewire Configuration**
   - Buka file `config/livewire.php`
   - Pastikan konfigurasi sudah benar

5. **Update Livewire**
   - Jalankan `composer update livewire/livewire`

## Informasi yang Diperlukan untuk Bantuan Lebih Lanjut

Jika masalah masih belum teratasi, silakan berikan:

1. Nama dan versi browser
2. Error message dari console (jika ada)
3. Detail network request (jika ada)
4. Screenshot halaman dan developer tools
5. Versi Livewire yang digunakan

## Tips Tambahan

- Pastikan Anda sudah login dengan user yang memiliki permission untuk edit/delete stock movement
- Pastikan stock movement yang ingin diedit/dihapus memiliki `ref_type = 'manual'`
- Periksa apakah ada JavaScript conflict dengan library lain
- Pastikan tidak ada CSS yang menghalangi tombol