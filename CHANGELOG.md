# Changelog

## v0.1.1 (2025-10-19)

### UI & Aset
- Melokalkan aset di `resources/views/welcome.blade.php`:
  - Menonaktifkan Google Fonts (tidak lagi memuat dari CDN).
  - Menggunakan system font stack lokal: `ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, Liberation Sans, sans-serif`.
  - Mengubah latar belakang halaman menjadi biru solid `#0B66FF` dan menghapus kelas gradient pada `<body>`.

### Perbaikan
- Memperbaiki scroll di `resources/views/auth/login.blade.php` saat konten memanjang:
  - Mengizinkan scroll vertikal dengan `overflow-y: auto` pada `.gradient-bg`.
  - Menambahkan `pointer-events: none` pada `.hero-pattern` agar overlay tidak memblokir interaksi/scroll.

### Catatan
- Embed Google Maps masih memuat dari layanan eksternal. Jika diperlukan aset peta sepenuhnya lokal, pertimbangkan penggunaan gambar peta statis atau solusi peta offline (Leaflet + tiles lokal).