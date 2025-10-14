# 🗄️ Database Migration Instructions

## 🚨 **Important: Run Migrations Before Using the System**

Sebelum menggunakan sistem dengan fitur baru yang telah diimplementasikan, Anda harus menjalankan migration terlebih dahulu untuk membuat tabel-tabel baru yang diperlukan.

---

## 📋 **Migration Files yang Harus Dijalankan**

### **1. Migration Files untuk Fitur Baru**
- `2025_10_14_200000_add_purchase_order_integration_to_incoming_goods_agenda.php`
- `2025_10_14_200100_add_batch_expiration_to_incoming_goods_agenda.php`
- `2025_10_14_200200_create_sales_invoices_table.php`
- `2025_10_14_200300_create_invoice_payments_table.php`
- `2025_10_14_200400_create_batch_expirations_table.php`
- `2025_10_14_200500_update_cashflow_agenda_table.php`
- `2025_10_14_200600_update_existing_models_relationships.php`

---

## 🚀 **Cara Menjalankan Migration**

### **1. Via Artisan Command (Recommended)**
```bash
# Jalankan semua migration yang belum dijalankan
php artisan migrate

# Atau jalankan migration spesifik
php artisan migrate --path=database/migrations/2025_10_14_200000_add_purchase_order_integration_to_incoming_goods_agenda.php
php artisan migrate --path=database/migrations/2025_10_14_200100_add_batch_expiration_to_incoming_goods_agenda.php
php artisan migrate --path=database/migrations/2025_10_14_200200_create_sales_invoices_table.php
php artisan migrate --path=database/migrations/2025_10_14_200300_create_invoice_payments_table.php
php artisan migrate --path=database/migrations/2025_10_14_200400_create_batch_expirations_table.php
php artisan migrate --path=database/migrations/2025_10_14_200500_update_cashflow_agenda_table.php
php artisan migrate --path=database/migrations/2025_10_14_200600_update_existing_models_relationships.php
```

### **2. Via Laravel Tinker**
```bash
php artisan tinker
```
```php
// Jalankan semua migration
Artisan::call('migrate');

// Keluar dari tinker
exit
```

---

## 🔄 **Jika Ada Masalah dengan Migration**

### **1. Cek Status Migration**
```bash
# Lihat migration yang sudah dijalankan
php artisan migrate:status

# Lihat migration yang pending
php artisan migrate:status | grep "Pending"
```

### **2. Rollback Migration (Jika Perlu)**
```bash
# Rollback migration terakhir
php artisan migrate:rollback

# Rollback beberapa migration
php artisan migrate:rollback --step=5
```

### **3. Fresh Migration (Hati-hati!)**
```bash
# Hapus semua tabel dan jalankan migration dari awal
php artisan migrate:fresh

# Fresh migration dengan seeder
php artisan migrate:fresh --seed
```

---

## 📊 **Tabel yang Akan Dibuat**

### **1. Tabel Baru**
- `sales_invoices` - Untuk multiple bon/invoice
- `invoice_payments` - Untuk tracking pembayaran
- `batch_expirations` - Untuk management expired date

### **2. Tabel yang Akan Dimodifikasi**
- `incoming_goods_agenda` - Tambah field untuk batch expiration
- `cashflow_agenda` - Tambah field untuk payment methods
- `sales` - Tambah field untuk invoice number dan payment methods
- `sale_items` - Tambah field untuk batch expiration
- `stock_movements` - Tambah field untuk batch expiration dan agenda
- `products` - Tambah field untuk expiration tracking
- `cash_ledger` - Tambah field untuk cashflow agenda
- `capital_tracking` - Tambah field untuk reference type

---

## 🎯 **Setelah Migration Berhasil**

### **1. Update Permissions**
```bash
# Jalankan permission seeder untuk update permissions
php artisan db:seed --class=PermissionSeeder
```

### **2. Clear Cache**
```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

### **3. Test Fitur Baru**
- Akses Agenda Management: `/agenda-management`
- Test Purchase Order dengan input sederhana
- Test POS Kasir dengan multiple bon
- Test thermal printing
- Test batch expiration tracking

---

## 🚨 **Troubleshooting**

### **Error: "Base table or view not found"**
**Solusi:** Jalankan migration terlebih dahulu
```bash
php artisan migrate
```

### **Error: "Column not found"**
**Solusi:** Pastikan semua migration sudah dijalankan
```bash
php artisan migrate:status
```

### **Error: "Foreign key constraint"**
**Solusi:** Jalankan migration dalam urutan yang benar
```bash
php artisan migrate --step
```

### **Error: "Table already exists"**
**Solusi:** Rollback migration dan jalankan kembali
```bash
php artisan migrate:rollback
php artisan migrate
```

---

## 📞 **Bantuan**

Jika Anda mengalami masalah dengan migration, silakan:

1. Cek log error di `storage/logs/laravel.log`
2. Pastikan environment sudah dikonfigurasi dengan benar
3. Pastikan database sudah ada dan user memiliki permission
4. Hubungi development team untuk bantuan lebih lanjut

---

**Status: Migration Instructions Ready ✅**
**Run Migration Before Using New Features 🚀**