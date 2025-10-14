# 📦 Install Dependencies for Agenda Management Enhancement

## 🚨 **Problem: Class "Spatie\Permission\Models\Permission" not found**

Package Spatie\Permission belum terinstall, sehingga tidak bisa menggunakan fitur roles dan permissions.

---

## 🎯 **Solution: Install Required Dependencies**

### **1. Install Spatie Laravel Permission**
```bash
# Install package via composer
composer require spatie/laravel-permission

# Publish migration (jika belum ada)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migration
php artisan migrate
```

### **2. Install Shoppingcart untuk POS**
```bash
# Install package via composer
composer require gloudemans/shoppingcart
```

### **3. Install Chart.js untuk Annual Summary**
```bash
# Install via npm
npm install chart.js

# Atau via CDN (sudah include di view)
```

---

## 📋 **Complete Installation Steps**

### **Step 1: Update Composer**
```bash
# Update composer
composer update

# Clear composer cache
composer clear-cache
composer dump-autoload
```

### **Step 2: Install All Dependencies**
```bash
# Install Laravel Permission
composer require spatie/laravel-permission

# Install Shoppingcart
composer require gloudemans/shoppingcart

# Install Chart.js
npm install chart.js
npm run build
```

### **Step 3: Publish and Run Migrations**
```bash
# Publish permission migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run all migrations (termasuk yang baru)
php artisan migrate
```

### **Step 4: Update Config**
```bash
# Publish config (jika perlu)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"

# Clear config cache
php artisan config:clear
```

### **Step 5: Run Seeders**
```bash
# Run role seeder
php artisan db:seed --class=RoleSeeder

# Run permission seeder
php artisan db:seed --class=PermissionSeeder

# Atau run all seeders
php artisan db:seed
```

### **Step 6: Clear All Caches**
```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear compiled views
php artisan view:clear
```

---

## 🔍 **Verify Installation**

### **1. Check Installed Packages**
```bash
# Check composer.json
cat composer.json | grep -E "(spatie|gloudemans)"

# Check vendor directory
ls vendor/spatie/
ls vendor/gloudemans/
```

### **2. Check Database Tables**
```bash
# Check migration status
php artisan migrate:status

# Check tables in database
php artisan tinker
>>> \Schema::hasTable('permissions');
>>> \Schema::hasTable('roles');
>>> \Schema::hasTable('role_has_permissions');
>>> exit
```

### **3. Check Models**
```bash
# Test models in tinker
php artisan tinker
>>> use Spatie\Permission\Models\Permission;
>>> use Spatie\Permission\Models\Role;
>>> Permission::count();
>>> Role::count();
>>> exit
```

---

## 🚨 **Troubleshooting**

### **Error: "Class Permission not found"**
**Solusi:** Jalankan `composer dump-autoload`
```bash
composer dump-autoload
```

### **Error: "Table 'permissions' doesn't exist"**
**Solusi:** Jalankan migration
```bash
php artisan migrate
```

### **Error: "Target class [PermissionSeeder] does not exist"**
**Solusi:** Pastikan file seeder ada
```bash
# Cek file seeder
ls database/seeders/PermissionSeeder.php
ls database/seeders/RoleSeeder.php
```

### **Error: "Permission denied"**
**Solusi:** Pastikan user memiliki permission
```bash
# Assign role ke user
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->assignRole('super_admin');
>>> exit
```

---

## 📁 **Required Dependencies**

### **Composer Packages**
- `spatie/laravel-permission` - Roles and permissions
- `gloudemans/shoppingcart` - Shopping cart for POS

### **NPM Packages**
- `chart.js` - Charts for annual summary

### **Laravel Version**
- PHP 8.0+
- Laravel 9.x+

---

## 🎯 **Expected Results**

Setelah mengikuti instruksi di atas:

1. ✅ Package Spatie\Permission terinstall
2. ✅ Tabel roles dan permissions tercreate
3. ✅ Super admin bisa akses semua fitur
4. ✅ Semua migration berjalan dengan sukses
5. ✅ Semua fitur baru bisa digunakan

---

## 📞 **Bantuan**

Jika masih ada masalah setelah mengikuti instruksi di atas:

1. Cek log error di `storage/logs/laravel.log`
2. Pastikan PHP dan Laravel version compatible
3. Pastikan database sudah ada dan user memiliki permission
4. Hubungi development team untuk bantuan lebih lanjut

---

**Status: Installation Instructions Ready ✅**
**Install Dependencies and Run Migration 🚀**