# Instruksi Testing Detail untuk Fungsi Hapus dan Update Pergerakan Stok

## Panduan Singkat untuk User

### 📋 Persiapan Sebelum Testing

1. **Login Requirements**:
   - Pastikan Anda login dengan akun yang memiliki permission:
     - `inventory.view` (untuk melihat riwayat stok)
     - `inventory.update` (untuk edit pergerakan stok)
     - `inventory.delete` (untuk hapus pergerakan stok)

2. **Akses Halaman**:
   - Dari menu sidebar, navigasi ke **Inventory** → **Riwayat Stok**
   - Atau langsung akses URL: `[your-domain]/inventory/stock-history`

---

## 🔍 Langkah 1: Mencari Pergerakan Stok yang Bisa Diedit

### Cara Mengidentifikasi Pergerakan Manual:

1. **Lihat Kolom "Aksi"** di tabel riwayat stok:
   - ✅ **Bisa diedit/dihapus**: Jika ada icon 📝 (edit) dan 🗑️ (hapus)
   - ❌ **Tidak bisa diedit**: Jika ada teks "Auto" (abu-abu)

2. **Filter untuk Mempermudah Pencarian**:
   - Gunakan filter **Produk** untuk memilih produk spesifik
   - Gunakan filter **Tanggal** untuk periode tertentu
   - Cari pergerakan dengan catatan seperti "penyesuaian manual", "stok opname", dll.

3. **Contoh Visual**:
   ```
   | Tanggal    | Produk     | Jenis    | Jumlah | Aksi    |
   |------------|------------|----------|--------|---------|
   | 12/10/2025 | Paracetamol| Masuk    | +100   | 📝 🗑️   |  <-- Bisa diedit
   | 11/10/2025 | Amoxicillin| Keluar   | -50    | Auto    |  <-- Tidak bisa diedit
   ```

---

## 🗑️ Langkah 2: Testing Fungsi Hapus

### Test Case 2.1: Membatalkan Penghapusan

1. **Klik icon hapus (🗑️)** pada pergerakan stok manual
2. **Pastikan dialog konfirmasi muncul** dengan:
   - Judul: "Konfirmasi Hapus Pergerakan Stok"
   - Pesan: "Apakah Anda yakin ingin menghapus pergerakan stok ini? Stok produk akan dikembalikan ke kondisi sebelumnya."
   - Tombol: "Yes, proceed!" (biru) dan "Cancel" (abu-abu)

3. **Klik tombol "Cancel"**
4. **Verifikasi**:
   - Dialog tertutup tanpa pesan error
   - Pergerakan stok masih ada di tabel
   - Stok produk tidak berubah

### Test Case 2.2: Melakukan Penghapusan

1. **Catat data sebelum penghapusan**:
   - Nama produk dan stok saat ini (dari halaman Produk)
   - Jenis dan jumlah pergerakan yang akan dihapus

2. **Klik icon hapus (🗑️)** pada pergerakan stok manual
3. **Klik tombol "Yes, proceed!"** pada dialog konfirmasi
4. **Verifikasi keberhasilan**:
   - Muncul pesan sukses: "Pergerakan stok berhasil dihapus!"
   - Pergerakan stok hilang dari tabel
   - Refresh halaman (F5) dan pastikan pergerakan tetap tidak ada

5. **Verifikasi stok produk**:
   - Buka halaman **Products** → cari produk yang sama
   - Bandingkan stok sebelum dan sesudah:
     - Jika menghapus "Stok Masuk" 100 unit → stok harus **berkurang 100**
     - Jika menghapus "Stok Keluar" 50 unit → stok harus **bertambah 50**
     - Jika menghapus "Penyesuaian" +25 unit → stok harus **berkurang 25**

---

## ✏️ Langkah 3: Testing Fungsi Update

### Test Case 3.1: Membatalkan Update

1. **Klik icon edit (📝)** pada pergerakan stok manual
2. **Pastikan modal edit muncul** dengan data existing
3. **Ubah data di form** (contoh: ubah jumlah)
4. **Klik tombol "Batal"**
5. **Verifikasi**:
   - Modal tertutup tanpa pesan error
   - Pergerakan stok tidak berubah di tabel
   - Stok produk tidak berubah

### Test Case 3.2: Update Berhasil

1. **Catat data sebelum update**:
   - Stok produk saat ini
   - Data pergerakan yang akan diedit

2. **Klik icon edit (📝)** pada pergerakan stok manual
3. **Ubah data di form**:
   - Ubah **Jumlah** (misal: dari 100 menjadi 150)
   - Ubah **Catatan** (misal: tambah " - updated")
4. **Klik tombol "Update"**
5. **Verifikasi keberhasilan**:
   - Muncul pesan sukses: "Pergerakan stok berhasil diperbarui!"
   - Data di tabel berubah sesuai update
   - Refresh halaman dan pastikan perubahan tetap ada

6. **Verifikasi stok produk**:
   - Buka halaman **Products** → cari produk yang sama
   - Hitung perubahan stok:
     - Contoh: Update dari 100 menjadi 150 (+50)
     - Jika "Stok Masuk": stok harus bertambah 50
     - Jika "Stok Keluar": stok harus berkurang 50

### Test Case 3.3: Test Validasi Error

1. **Klik icon edit (📝)** pada pergerakan stok manual
2. **Coba input data invalid**:
   - Jumlah: 0 atau kosong
   - Jumlah: angka negatif
   - Jumlah: huruf atau karakter khusus
3. **Klik tombol "Update"**
4. **Verifikasi error**:
   - Muncul pesan error di bawah field jumlah
   - Data tidak tersimpan
   - Modal tetap terbuka untuk koreksi

### Test Case 3.4: Test Stok Tidak Mencukupi

1. **Pilih pergerakan "Stok Keluar"**
2. **Klik icon edit (📝)**
3. **Ubah jumlah ke nilai yang sangat besar** (lebih dari stok tersedia)
4. **Klik tombol "Update"**
5. **Verifikasi error**:
   - Muncul pesan: "Stok tidak mencukupi. Stok tersedia: [jumlah]"
   - Data tidak tersimpan

---

## 🔍 Langkah 4: Verifikasi Data di Database (Optional)

Jika Anda memiliki akses ke database:

### Cek Stock Movements Table
```sql
SELECT * FROM stock_movements WHERE ref_type = 'manual' ORDER BY created_at DESC LIMIT 10;
```

### Cek Audit Logs
```sql
SELECT * FROM audit_logs WHERE model = 'StockMovement' ORDER BY created_at DESC LIMIT 10;
```

### Cek Products Table
```sql
SELECT id, name, current_stock FROM products WHERE name LIKE '%nama_produk%';
```

---

## 📊 Langkah 5: Testing Permission (Jika Memiliki Multiple User)

### Test dengan User Tanpa Permission
1. Login dengan user yang TIDAK memiliki permission `inventory.update` atau `inventory.delete`
2. Navigasi ke halaman Riwayat Stok
3. **Verifikasi**:
   - Icon edit (📝) tidak muncul
   - Icon hapus (🗑️) tidak muncul
   - Hanya icon lihat detail (👁️) yang muncul

---

## 🐞 Troubleshooting Guide

### Jika Dialog Konfirmasi Tidak Muncul
- **Cek browser console**: Tekan F12 → tab Console
- **Refresh halaman**: Tekan F5
- **Clear cache browser**: Ctrl+Shift+Delete

### Jika Stok Tidak Berubah
- **Tunggu beberapa detik**: Mungkin ada delay
- **Refresh halaman produk**: Tekan F5
- **Cek error message**: Lihat apakah ada pesan error

### Jika Muncul Error 500
- **Screenshot error**: Dokumentasikan pesan error
- **Catat langkah-langkah**: Tulis langkah yang menyebabkan error
- **Hubungi developer**: Laporkan dengan detail error

---

## ✅ Checklist Testing

### Fungsi Hapus
- [ ] Dialog konfirmasi muncul saat klik hapus
- [ ] Tombol cancel berfungsi dengan benar
- [ ] Penghapusan berhasil dengan konfirmasi
- [ ] Pesan sukses muncul
- [ ] Pergerakan hilang dari tabel
- [ ] Stok produk kembali dengan benar
- [ ] Data tetap hilang setelah refresh

### Fungsi Update
- [ ] Modal edit muncul dengan data existing
- [ ] Tombol batal berfungsi dengan benar
- [ ] Update berhasil dengan data valid
- [ ] Pesan sukses muncul
- [ ] Data berubah di tabel
- [ ] Stok produk berubah dengan benar
- [ ] Data tetap berubah setelah refresh
- [ ] Validasi error berfungsi (jumlah kosong/negatif)
- [ ] Validasi stok tidak mencukupi berfungsi

### Verifikasi Tambahan
- [ ] Audit log tercatat dengan benar
- [ ] Permission berfungsi untuk user berbeda
- [ ] Tidak ada error di browser console
- [ ] Performance tetap baik dengan data besar

---

## 📝 Laporkan Hasil Testing

Setelah selesai testing, laporkan hasil dengan format:

```
## Hasil Testing
**Tanggal**: [tanggal testing]
**User**: [nama user]

### Fungsi Hapus
- Status: ✅ Berhasil / ❌ Gagal
- Kendala: [deskripsi kendala jika ada]

### Fungsi Update
- Status: ✅ Berhasil / ❌ Gagal
- Kendala: [deskripsi kendala jika ada]

### Temuan Lain
- [deskripsi temuan lain]

### Screenshots
- [lampirkan screenshot jika ada error atau hasil testing]
```

---

**💡 Tips**: Lakukan testing dengan data yang tidak penting terlebih dahalu. Jika ragu, konsultasikan dengan developer sebelum melakukan perubahan pada data produksi.