# 🔧 Fix Goods Name Error - Complete Instructions

## 🚨 **Problem: Field 'goods_name' doesn't have a default value**

Error terjadi karena ketidakcocokan antara migration, model, dan view:
- Migration asli memiliki field `goods_name`
- Model menggunakan `item_name` di fillable
- View menggunakan `item_name` di form

---

## 🎯 **Root Cause Analysis**

### **1. Migration vs Model vs View Mismatch**
```php
// Migration (2025_09_20_201115_create_incoming_goods_agenda_table.php)
$table->string('goods_name')->comment('Nama barang');

// Model (IncomingGoodsAgenda.php) - BEFORE FIX
protected $fillable = [
    'item_name',  // ← Ini seharusnya 'goods_name'
    // ... other fields
];

// View (purchase-order-agenda-tab.blade.php) - BEFORE FIX
<input type="text" wire:model="item_name"  // ← Ini seharusnya 'goods_name'
```

### **2. Missing Field in Fillable**
Field `goods_name` tidak ada di `$fillable` sehingga tidak bisa diisi melalui model.

---

## ✅ **Complete Fix Applied**

### **1. Model Fixed**
✅ **File:** `app/Models/IncomingGoodsAgenda.php`
✅ **Changes:**
- Ganti `'item_name'` dengan `'goods_name'` di fillable
- Update accessor method `getEffectiveGoodsNameAttribute()`
- Update boot method untuk auto-populate `goods_name`

### **2. Livewire Component Fixed**
✅ **File:** `app/Livewire/PurchaseOrderAgendaTab.php`
✅ **Changes:**
- Ganti `$item_name` dengan `$goods_name` di properties
- Update validation rules
- Update create methods
- Update search query

### **3. View Fixed**
✅ **File:** `resources/views/livewire/purchase-order-agenda-tab.blade.php`
✅ **Changes:**
- Ganti `wire:model="item_name"` dengan `wire:model="goods_name"`
- Update error messages

---

## 🚀 **Testing Instructions**

### **1. Clear All Caches**
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

### **2. Test Create Agenda (Simplified Mode)**
1. Buka halaman Agenda Management
2. Pilih tab "Purchase Order"
3. Pastikan form dalam mode sederhana
4. Isi form:
   - Supplier: Pilih supplier yang ada
   - Jatuh Tempo: Pilih tanggal jatuh tempo
   - Total Jumlah Barang: Masukkan jumlah (contoh: 3)
   - Unit: Masukkan unit (contoh: pcs)
   - Total Belanja: Masukkan total belanja (contoh: 3000)
   - Tanggal Datang: Pilih tanggal
   - Expired Date: Pilih tanggal kadaluarsa
   - Catatan: Isi catatan (opsional)
5. Centang "Auto-generate Purchase Order"
6. Klik "Simpan Agenda"
7. **Expected Result:** Agenda berhasil dibuat tanpa error

### **3. Test Create Agenda (Detailed Mode)**
1. Klik tombol "Mode Detail"
2. Isi form:
   - Nama Supplier: Masukkan nama supplier
   - Nama Barang: Masukkan nama barang
   - Jumlah: Masukkan jumlah
   - Harga per Unit: Masukkan harga
   - Tanggal Datang: Pilih tanggal
   - Jatuh Tempo: Pilih tanggal jatuh tempo
   - Batch Number: Isi batch number (opsional)
   - Expired Date: Pilih tanggal kadaluarsa
   - Catatan: Isi catatan (opsional)
3. Klik "Simpan Agenda"
4. **Expected Result:** Agenda berhasil dibuat tanpa error

### **4. Test Search Functionality**
1. Di search box, cari nama barang yang baru dibuat
2. **Expected Result:** Barang muncul di hasil pencarian

---

## 🔍 **Verification Steps**

### **1. Check Database**
```php
// Test di tinker
php artisan tinker

// Cek apakah agenda berhasil dibuat
$agenda = \App\Models\IncomingGoodsAgenda::latest()->first();
echo $agenda->goods_name;
echo $agenda->effective_supplier_name;
echo $agenda->effective_quantity;
echo $agenda->effective_total_amount;
exit;
```

### **2. Check Model Methods**
```php
// Test di tinker
php artisan tinker

// Cek accessor methods
$agenda = \App\Models\IncomingGoodsAgenda::latest()->first();
echo $agenda->effective_goods_name;
echo $agenda->is_simplified;
echo $agenda->effective_total_amount;
exit;
```

### **3. Check Livewire Component**
1. Buka halaman Agenda Management
2. Pilih tab "Purchase Order"
3. Cek apakah form terisi dengan benar
4. Cek apakah data muncul di tabel

---

## 🚨 **Troubleshooting**

### **Issue 1: Still Getting Error After Fix**
**Solution:** Clear cache dan restart server
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart Laravel server
php artisan serve
```

### **Issue 2: Form Not Submitting**
**Solution:** Check browser console untuk JavaScript errors
```bash
# Buka browser console (F12)
# Cek apakah ada JavaScript errors
# Refresh halaman
```

### **Issue 3: Data Not Saving**
**Solution:** Check Laravel logs
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Cek error messages di logs
```

### **Issue 4: Validation Errors**
**Solution:** Check validation rules
```php
// Cek validation rules di PurchaseOrderAgendaTab.php
protected $rules = [
    'goods_name' => 'required|string|max:255',  // Pastikan ini ada
    // ... other rules
];
```

---

## 📋 **Files Modified**

### **1. Model Files**
- ✅ `app/Models/IncomingGoodsAgenda.php` - Fixed fillable and methods

### **2. Livewire Components**
- ✅ `app/Livewire/PurchaseOrderAgendaTab.php` - Fixed properties and methods

### **3. View Files**
- ✅ `resources/views/livewire/purchase-order-agenda-tab.blade.php` - Fixed form inputs

---

## 🎯 **Expected Results**

Setelah mengikuti instruksi di atas:

1. ✅ Error "Field 'goods_name' doesn't have a default value" akan hilang
2. ✅ Agenda bisa dibuat dengan sukses di mode sederhana
3. ✅ Agenda bisa dibuat dengan sukses di mode detail
4. ✅ Form validation bekerja dengan benar
5. ✅ Search functionality bekerja dengan benar
6. ✅ Data tersimpan dengan benar di database

---

## 🔧 **Additional Checks**

### **1. Check Migration Status**
```bash
# Cek status migration
php artisan migrate:status

# Pastikan migration incoming_goods_agenda sudah dijalankan
```

### **2. Check Database Structure**
```bash
# Cek struktur tabel
php artisan tinker

// Cek apakah field goods_name ada
\Schema::hasColumn('incoming_goods_agenda', 'goods_name');
exit;
```

### **3. Check Model Fillable**
```bash
# Cek fillable array
php artisan tinker

// Cek apakah goods_name ada di fillable
$fillable = (new \App\Models\IncomingGoodsAgenda())->getFillable();
in_array('goods_name', $fillable);
exit;
```

---

## 🎉 **Success Criteria**

✅ **Fix Complete** jika:
1. Semua file sudah diperbaiki
2. Cache sudah dibersihkan
3. Server sudah di-restart
4. Test create agenda berhasil
5. Test search functionality berhasil
6. Tidak ada error di logs
7. Data tersimpan dengan benar di database

---

**Status: Fix Applied ✅**
**Ready for Testing 🚀**
**Complete Instructions Provided 📋**