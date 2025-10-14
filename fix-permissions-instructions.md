# 🔧 Fix Permissions for Super Admin

## 🚨 **Problem: Super Admin Cannot Access All Features**

Super admin hanya bisa akses product dan POS kasir, tidak bisa akses fitur lain yang baru diimplementasikan.

---

## 🎯 **Solution: Update Roles and Permissions**

### **1. Run Role Seeder**
```bash
# Jalankan role seeder untuk update permissions
php artisan db:seed --class=RoleSeeder
```

### **2. Clear Cache**
```bash
# Clear permission cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear
```

### **3. Logout dan Login Kembali**
```bash
# Super admin harus logout dan login kembali
# agar permissions terupdate dengan benar
```

---

## 📋 **Roles dan Permissions yang Dibuat**

### **1. Super Admin Role**
- **Permissions:** Semua permissions (100% access)
- **Can Access:** Semua fitur termasuk system settings

### **2. Admin Role**
- **Permissions:** Sebagian besar permissions (kecuali system settings)
- **Can Access:** Semua fitur kecuali manage system

### **3. Manager Role**
- **Permissions:** Business operations permissions
- **Can Access:** Dashboard, Products, Inventory, POS, Capital Tracking, Cash Ledger, Agenda, Reports

### **4. Kasir Role**
- **Permissions:** POS dan sales permissions
- **Can Access:** Dashboard (sales), Products (view), Inventory (view), POS, Sales Invoices, Reports (view)

### **5. Gudang Role**
- **Permissions:** Inventory dan warehouse permissions
- **Can Access:** Dashboard (inventory), Products (view), Inventory, Suppliers, Incoming Goods, Purchase Orders, Batch Expirations, Warehouses, Reports (view)

### **6. Keuangan Role**
- **Permissions:** Financial reports permissions
- **Can Access:** Dashboard (financial), POS (view), Capital Tracking, Cash Ledger, Agenda (view), Sales Invoices (view), Batch Expirations (view), Financial, Reports

---

## 🔍 **Cek Permissions Setelah Update**

### **1. Cek Role Super Admin**
```php
// Jalankan di tinker
php artisan tinker

// Cek user super admin
$user = \App\Models\User::where('email', 'superadmin@example.com')->first();
$user->getAllPermissions()->pluck('name');
```

### **2. Cek Menu Navigation**
- Login sebagai super admin
- Pastikan semua menu muncul:
  - Dashboard
  - POS Kasir
  - Agenda (dropdown dengan semua sub-menu)
  - Inventory (dropdown dengan semua sub-menu)
  - Financial (dropdown dengan semua sub-menu)
  - Suppliers
  - Reports (dropdown dengan semua sub-menu)

### **3. Cek Akses Fitur Baru**
- Coba akses Agenda Management: `/agenda-management`
- Coba akses Capital Tracking: `/capital-tracking`
- Coba akses Cash Ledger: `/cash-ledger`
- Coba akses fitur lainnya

---

## 🚨 **Jika Masih Ada Masalah**

### **1. Pastikan Migration Sudah Dijalankan**
```bash
# Cek status migration
php artisan migrate:status

# Jalankan migration jika ada yang pending
php artisan migrate
```

### **2. Pastikan User Memiliki Role yang Benar**
```php
// Jalankan di tinker
php artisan tinker

// Cek role user
$user = \App\Models\User::where('email', 'superadmin@example.com')->first();
$user->roles->pluck('name');
```

### **3. Assign Role Manual (Jika Perlu)**
```php
// Jalankan di tinker
php artisan tinker

// Assign role super admin
$user = \App\Models\User::where('email', 'superadmin@example.com')->first();
$user->assignRole('super_admin');
```

### **4. Clear Application Cache**
```bash
# Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart queue worker (jika menggunakan)
php artisan queue:restart
```

---

## 📞 **Bantuan**

Jika masalah masih berlanjut setelah mengikuti instruksi di atas:

1. Cek log error di `storage/logs/laravel.log`
2. Pastikan environment sudah dikonfigurasi dengan benar
3. Pastikan database sudah ada dan user memiliki permission
4. Hubungi development team untuk bantuan lebih lanjut

---

## 🎯 **Expected Results**

Setelah mengikuti instruksi di atas, super admin seharusnya bisa:

1. ✅ Akses semua menu di navigation
2. ✅ Akses semua fitur baru yang diimplementasikan
3. ✅ Melihat dan mengelola Agenda Management
4. ✅ Melihat dan mengelola Capital Tracking
5. ✅ Melihat dan mengelola Cash Ledger
6. ✅ Mengakses semua fitur tanpa error permission

---

**Status: Fix Instructions Ready ✅**
**Run Role Seeder and Clear Cache 🚀**