# Rencana Testing Manual untuk Fungsi Hapus dan Update Pergerakan Stok

## Ringkasan Verifikasi Kode

Berdasarkan pemeriksaan kode yang telah dilakukan, berikut adalah status implementasi:

✅ **Event Listener**: `livewire-confirm-action` sudah terdaftar dengan benar di `app/Livewire/StockHistory.php` (line 56)
✅ **Method Handler**: `handleConfirmedAction()` sudah diimplementasikan dengan benar (lines 357-365)
✅ **Fungsi Hapus**: `deleteMovement()` sudah menggunakan transaksi database dan validasi stok (lines 287-355)
✅ **Fungsi Update**: `updateMovement()` sudah menggunakan transaksi database dan validasi stok (lines 193-267)
✅ **UI Integration**: Konfirmasi dialog sudah terintegrasi dengan SweetAlert2 di `app.blade.php` (lines 629-643)

## Rencana Testing Manual

### 1. Cara Mencari Pergerakan Stok yang Bisa Diedit (ref_type = 'manual')

**Langkah-langkah:**
1. Login ke aplikasi dengan user yang memiliki permission `inventory.update` dan `inventory.delete`
2. Navigasi ke menu **Inventory** → **Riwayat Stok**
3. Di halaman Riwayat Stok, cari pergerakan stok dengan kolom "Aksi" menampilkan:
   - Icon edit (📝) dan hapus (🗑️) untuk pergerakan manual
   - Teks "Auto" untuk pergerakan otomatis (tidak bisa diedit/dihapus)
4. **Filter khusus**: Gunakan filter untuk menemukan pergerakan manual:
   - Filter berdasarkan produk yang sering ada penyesuaian manual
   - Filter berdasarkan tanggal untuk periode tertentu
   - Cari pergerakan dengan catatan yang mengindikasikan input manual

### 2. Langkah-langkah Testing Fungsi Hapus

**Persiapan:**
- Catat ID produk dan stok sebelum pengujian
- Pilih pergerakan stok manual yang akan dihapus

**Proses Testing:**
1. Klik icon hapus (🗑️) pada pergerakan stok manual
2. **Verifikasi Dialog Konfirmasi**:
   - Pastikan dialog konfirmasi muncul dengan judul "Konfirmasi Hapus Pergerakan Stok"
   - Pastikan pesan berisi: "Apakah Anda yakin ingin menghapus pergerakan stok ini? Stok produk akan dikembalikan ke kondisi sebelumnya."
   - Pastikan ada tombol "Yes, proceed!" dan "Cancel"
3. **Test Scenario 1 - Cancel**:
   - Klik tombol "Cancel"
   - Pastikan pergerakan stok TIDAK terhapus
   - Pastikan stok produk TIDAK berubah
4. **Test Scenario 2 - Confirm Delete**:
   - Klik tombol "Yes, proceed!"
   - Pastikan pergerakan stok terhapus dari tabel
   - Pastikan muncul pesan sukses: "Pergerakan stok berhasil dihapus!"
   - Refresh halaman dan pastikan pergerakan tetap tidak ada
5. **Verifikasi Stok**:
   - Cek stok produk di halaman produk atau dashboard
   - Pastikan stok produk telah dikembalikan (bertambah atau berkurang sesuai pergerakan yang dihapus)
   - Contoh: Jika menghapus "Stok Masuk" 10 unit, stok produk harus berkurang 10 unit

### 3. Langkah-langkah Testing Fungsi Update

**Persiapan:**
- Catat ID produk, stok sebelum, dan data pergerakan yang akan diedit
- Siapkan data uji (jumlah baru dan catatan baru)

**Proses Testing:**
1. Klik icon edit (📝) pada pergerakan stok manual
2. **Verifikasi Modal Edit**:
   - Pastikan modal edit muncul dengan informasi produk yang benar
   - Pastikan field jumlah dan catatan terisi dengan data existing
   - Pastikan jenis pergerakan (IN/OUT/ADJUSTMENT) tidak bisa diubah
3. **Test Scenario 1 - Cancel**:
   - Ubah data di form
   - Klik tombol "Batal"
   - Pastikan perubahan TIDAK disimpan
   - Pastikan stok produk TIDAK berubah
4. **Test Scenario 2 - Update Valid**:
   - Ubah jumlah dan/atau catatan
   - Klik tombol "Update"
   - Pastikan pergerakan stok diperbarui di tabel
   - Pastikan muncul pesan sukses: "Pergerakan stok berhasil diperbarui!"
   - Refresh halaman dan pastikan perubahan tetap ada
5. **Test Scenario 3 - Validation Error**:
   - Coba update dengan jumlah 0 atau negatif
   - Pastikan muncul error validation di field jumlah
   - Pastikan perubahan TIDAK disimpan
6. **Test Scenario 4 - Stock Insufficient**:
   - Untuk pergerakan "Stok Keluar", coba ubah jumlah melebihi stok tersedia
   - Pastikan muncul error: "Stok tidak mencukupi. Stok tersedia: [jumlah]"
   - Pastikan perubahan TIDAK disimpan
7. **Verifikasi Stok**:
   - Cek stok produk setelah update
   - Pastikan stok berubah sesuai dengan selisih antara jumlah lama dan baru
   - Contoh: Jika update dari "Stok Masuk" 10 unit menjadi 15 unit, stok harus bertambah 5 unit

### 4. Cara Memverifikasi Stok Produk Telah Dikembalikan dengan Benar

**Metode Verifikasi:**
1. **Sebelum Aksi**:
   - Catat stok produk dari dashboard atau halaman produk
   - Catat data pergerakan yang akan dihapus/diubah (jenis dan jumlah)
2. **Sesudah Aksi**:
   - Refresh halaman produk dan bandingkan stok sebelumnya
   - **Untuk Penghapusan**: 
     - Jika menghapus "Stok Masuk" X unit: stok baru = stok lama - X
     - Jika menghapus "Stok Keluar" X unit: stok baru = stok lama + X
     - Jika menghapus "Penyesuaian" X unit: stok baru = stok lama - X
   - **Untuk Update**:
     - Hitung selisih: selisih = jumlah baru - jumlah lama
     - Stok baru = stok lama + selisih
3. **Cross-Check**:
   - Cek di riwayat stok untuk pergerakan terbaru
   - Pastikan tidak ada pergerakan stok yang tidak diinginkan

### 5. Cara Memverifikasi Perubahan Telah Tersimpan dengan Benar

**Metode Verifikasi:**
1. **Database Verification**:
   - Buka table `stock_movements` di database
   - Cari pergerakan yang diedit berdasarkan ID
   - Pastikan kolom `qty` dan `note` sudah terupdate
   - Pastikan kolom `updated_at` berubah
2. **Audit Log Verification**:
   - Buka table `audit_logs`
   - Cari log dengan `model` = 'StockMovement' dan `model_id` = ID pergerakan
   - Pastikan ada log untuk aksi 'update' atau 'delete'
   - Pastikan field `changes` mencatat perubahan yang dilakukan
3. **UI Verification**:
   - Refresh halaman riwayat stok
   - Pastikan data di tabel sudah terupdate
   - Pastikan jumlah dan catatan sudah berubah
   - Cari dengan filter produk yang sama untuk memastikan konsistensi

## Test Cases Tambalian

### Edge Cases Testing
1. **Concurrent Operations**:
   - Coba edit/hapus pergerakan yang sedang diedit user lain
   - Pastikan tidak terjadi inkonsistensi data
2. **Large Quantities**:
   - Test dengan jumlah yang sangat besar
   - Pastikan tidak ada overflow atau error
3. **Special Characters**:
   - Test catatan dengan karakter khusus (', ", <, >, dll)
   - Pastikan tidak ada XSS atau SQL injection

### Permission Testing
1. **Unauthorized Access**:
   - Login dengan user tanpa permission `inventory.update`
   - Pastikan icon edit tidak muncul
   - Coba akses langsung via URL, pastikan error 403
2. **Unauthorized Delete**:
   - Login dengan user tanpa permission `inventory.delete`
   - Pastikan icon hapus tidak muncul
   - Coba akses langsung via URL, pastikan error 403

## Troubleshooting Guide

### Jika Testing Gagal

**Dialog Konfirmasi Tidak Muncul**:
- Pastikan SweetAlert2 sudah dimuat dengan benar
- Cek console browser untuk error JavaScript
- Pastikan event listener `livewire-confirm-action` terdaftar

**Stok Tidak Berubah**:
- Cek database transaction berhasil
- Verifikasi log error di Laravel logs
- Pastikan tidak ada exception yang tidak tertangkap

**Permission Error**:
- Verifikasi policy `StockMovementPolicy` sudah benar
- Cek user memiliki permission yang diperlukan
- Test dengan user yang berbeda

### Log Locations
- Laravel Logs: `storage/logs/laravel.log`
- Browser Console: Developer Tools → Console
- Network Tab: Developer Tools → Network untuk melihat request yang gagal

## Checklist Testing

- [ ] Cari pergerakan stok manual
- [ ] Test dialog konfirmasi hapus (cancel dan confirm)
- [ ] Verifikasi stok kembali setelah penghapusan
- [ ] Test modal edit (cancel dan update)
- [ ] Test validation error (jumlah invalid, stok tidak cukup)
- [ ] Verifikasi stok berubah setelah update
- [ ] Verifikasi data tersimpan di database
- [ ] Verifikasi audit log tercatat
- [ ] Test dengan user tanpa permission
- [ ] Test edge cases (karakter khusus, jumlah besar)
- [ ] Cek error log untuk masalah yang tidak terlihat

---

**Catatan**: Lakukan testing satu per satu dan dokumentasikan hasilnya. Jika menemukan masalah, catat langkah-langkah untuk mereproduksi dan screenshot error jika memungkinkan.