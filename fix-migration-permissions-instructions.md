# 🔧 Fix Migration and Permissions Issues

## 🚨 **Problems:**
1. Class "Spatie\Permission\Models\Permission" not found
2. Column 'guard_name' not found in permissions table

---

## 🎯 **Complete Solution Steps**

### **Step 1: Install Spatie Laravel Permission**
```bash
# Install package via composer
composer require spatie/laravel-permission

# Clear composer cache
composer clear-cache
composer dump-autoload
```

### **Step 2: Update Permissions Table Migration**
```bash
# Run the new migration to fix permissions table
php artisan migrate --path=database/migrations/2025_08_04_064717_update_permissions_table.php
```

### **Step 3: Run All New Migrations**
```bash
# Run all migrations for new features
php artisan migrate --path=database/migrations/2025_10_14_200000_add_purchase_order_integration_to_incoming_goods_agenda.php
php artisan migrate --path=database/migrations/2025_10_14_200100_add_batch_expiration_to_incoming_goods_agenda.php
php artisan migrate --path=database/migrations/2025_10_14_200200_create_sales_invoices_table.php
php artisan migrate --path=database/migrations/2025_10_14_200300_create_invoice_payments_table.php
php artisan migrate --path=database/migrations/2025_10_14_200400_create_batch_expirations_table.php
php artisan migrate --path=database/migrations/2025_10_14_200500_update_cashflow_agenda_table.php
php artisan migrate --path=database/migrations/2025_10_14_200600_update_existing_models_relationships.php

# Atau jalankan semua migration yang pending
php artisan migrate
```

### **Step 4: Run Role and Permission Seeders**
```bash
# Run role seeder
php artisan db:seed --class=RoleSeeder

# Run permission seeder
php artisan db:seed --class=PermissionSeeder

# Atau run all seeders
php artisan db:seed
```

### **Step 5: Clear All Caches**
```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

### **Step 6: Install Additional Dependencies**
```bash
# Install shoppingcart for POS
composer require gloudemans/shoppingcart

# Install chart.js for annual summary
npm install chart.js
npm run build
```

---

## 🔍 **Verify Installation**

### **1. Check Migration Status**
```bash
# Check migration status
php artisan migrate:status

# Look for these migrations:
# - 2025_08_04_064717_update_permissions_table
# - 2025_10_14_200000_add_purchase_order_integration_to_incoming_goods_agenda
# - 2025_10_14_200100_add_batch_expiration_to_incoming_goods_agenda
# - 2025_10_14_200200_create_sales_invoices_table
# - 2025_10_14_200300_create_invoice_payments_table
# - 2025_10_14_200400_create_batch_expirations_table
# - 2025_10_14_200500_update_cashflow_agenda_table
# - 2025_10_14_200600_update_existing_models_relationships
```

### **2. Check Database Tables**
```bash
# Check tables in database
php artisan tinker
>>> \Schema::hasTable('permissions');
>>> \Schema::hasColumn('permissions', 'guard_name');
>>> \Schema::hasTable('sales_invoices');
>>> \Schema::hasTable('invoice_payments');
>>> \Schema::hasTable('batch_expirations');
>>> exit
```

### **3. Check Models**
```bash
# Test models in tinker
php artisan tinker
>>> use Spatie\Permission\Models\Permission;
>>> use Spatie\Permission\Models\Role;
>>> use App\Models\SalesInvoice;
>>> use App\Models\InvoicePayment;
>>> use App\Models\BatchExpiration;
>>> Permission::count();
>>> Role::count();
>>> SalesInvoice::count();
>>> InvoicePayment::count();
>>> BatchExpiration::count();
>>> exit
```

### **4. Check User Permissions**
```bash
# Check user permissions in tinker
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->getAllPermissions()->pluck('name');
>>> $user->getRoleNames();
>>> exit
```

---

## 🚨 **Troubleshooting**

### **Error: "Class Permission not found"**
**Solusi:** Jalankan `composer dump-autoload`
```bash
composer dump-autoload
```

### **Error: "Column 'guard_name' not found"**
**Solusi:** Jalankan migration untuk update permissions table
```bash
php artisan migrate --path=database/migrations/2025_08_04_064717_update_permissions_table.php
```

### **Error: "Table 'permissions' doesn't exist"**
**Solusi:** Publish dan run migration Spatie\Permission
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### **Error: "Target class [RoleSeeder] does not exist"**
**Solusi:** Pastikan file seeder ada
```bash
# Cek file seeder
ls database/seeders/RoleSeeder.php
ls database/seeders/PermissionSeeder.php
```

### **Error: "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'laravel.batch_expirations' doesn't exist"**
**Solusi:** Jalankan migration untuk batch expirations
```bash
php artisan migrate --path=database/migrations/2025_10_14_200400_create_batch_expirations_table.php
```

### **Error: "Migration already executed"**
**Solusi:** Rollback dan jalankan kembali
```bash
# Rollback last migration
php artisan migrate:rollback

# Run migration again
php artisan migrate
```

---

## 📋 **Complete Installation Checklist**

- [ ] Spatie\Permission package installed
- [ ] Shoppingcart package installed
- [ ] Chart.js installed
- [ ] All migrations executed
- [ ] Permissions table updated with guard_name column
- [ ] New tables created (sales_invoices, invoice_payments, batch_expirations)
- [ ] Role seeder executed
- [ ] Permission seeder executed
- [ ] All caches cleared
- [ ] Super admin has all permissions
- [ ] All features accessible

---

## 🎯 **Expected Results**

Setelah mengikuti instruksi di atas:

1. ✅ Package Spatie\Permission terinstall
2. ✅ Tabel permissions memiliki kolom guard_name
3. ✅ Semua tabel baru tercreate
4. ✅ Super admin bisa akses semua fitur
5. ✅ Semua fitur baru bisa digunakan
6. ✅ Tidak ada error permission atau migration

---

## 📞 **Bantuan**

Jika masih ada masalah setelah mengikuti instruksi di atas:

1. Cek log error di `storage/logs/laravel.log`
2. Pastikan PHP dan Laravel version compatible
3. Pastikan database sudah ada dan user memiliki permission
4. Hubungi development team untuk bantuan lebih lanjut

---

**Status: Fix Instructions Ready ✅**
**Run All Steps in Order 🚀**