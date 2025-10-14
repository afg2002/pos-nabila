# 🔧 Fix IncomingGoodsAgenda Error Solution

## 🚨 **Problem: Field 'goods_name' doesn't have a default value**

Error terjadi karena ada ketidakcocokan antara migration dan model:
- Migration asli memiliki field `goods_name`
- Model menggunakan `item_name` di fillable
- Saat create agenda, field `goods_name` tidak terisi

---

## 🎯 **Root Cause Analysis**

### **1. Migration vs Model Mismatch**
```php
// Migration (2025_09_20_201115_create_incoming_goods_agenda_table.php)
$table->string('goods_name')->comment('Nama barang');

// Model (IncomingGoodsAgenda.php)
protected $fillable = [
    'item_name',  // ← Ini seharusnya 'goods_name'
    // ... other fields
];
```

### **2. Missing Field in Fillable**
Field `goods_name` tidak ada di `$fillable` sehingga tidak bisa diisi melalui model.

---

## 🔧 **Solution Options**

### **Option 1: Update Model Fillable (Recommended)**
Edit `app/Models/IncomingGoodsAgenda.php`:

```php
protected $fillable = [
    'supplier_id',
    'batch_number',
    'expired_date',
    'supplier_name',
    'supplier_contact',
    'goods_name',  // ← Ganti dari 'item_name'
    'description',
    'quantity',
    'unit',
    'unit_price',
    'total_amount',
    'total_quantity',
    'quantity_unit',
    'total_purchase_amount',
    'scheduled_date',
    'payment_due_date',
    'status',
    'payment_status',
    'paid_amount',
    'remaining_amount',
    'notes',
    'contact_person',
    'phone_number',
    'is_purchase_order_generated',
    'po_number',
    'purchase_order_id',
    'received_date',
    'received_at',
    'paid_at',
    'business_modal_id',
    'source',
    'warehouse_id',
    'product_id',
    'created_by',
];
```

### **Option 2: Update Accessor Method**
Edit `getEffectiveGoodsNameAttribute()` method:

```php
public function getEffectiveGoodsNameAttribute()
{
    return $this->is_simplified ? 'Barang Various (Input Sederhana)' : $this->goods_name;  // ← Ganti dari $this->item_name
}
```

### **Option 3: Add Default Value in Migration**
Edit migration untuk menambah default value:

```php
$table->string('goods_name')->default('Barang Various')->comment('Nama barang');
```

---

## 🚀 **Complete Fix Steps**

### **Step 1: Update Model**
Edit `app/Models/IncomingGoodsAgenda.php`:

1. Ganti `'item_name'` dengan `'goods_name'` di fillable
2. Update accessor method `getEffectiveGoodsNameAttribute()`
3. Update boot method untuk handle `goods_name`

### **Step 2: Update Livewire Components**
Cek semua Livewire components yang menggunakan `item_name`:
- `PurchaseOrderAgendaTab.php`
- `AgendaManagement.php`
- `IncomingGoodsAgendaManagement.php`

Ganti `item_name` dengan `goods_name`:
```php
// Sebelum
$agenda->item_name = $request->get('item_name');

// Sesudah
$agenda->goods_name = $request->get('goods_name');
```

### **Step 3: Update Views**
Cek semua view files yang menggunakan `item_name`:
- `purchase-order-agenda-tab.blade.php`
- `agenda-management.blade.php`
- `incoming-goods-agenda-management.blade.php`

Ganti `item_name` dengan `goods_name`:
```php
// Sebelum
<input wire:model="item_name">

// Sesudah
<input wire:model="goods_name">
```

---

## 📋 **Files to Update**

### **1. Model Files**
- `app/Models/IncomingGoodsAgenda.php`

### **2. Livewire Components**
- `app/Livewire/PurchaseOrderAgendaTab.php`
- `app/Livewire/AgendaManagement.php`
- `app/Livewire/IncomingGoodsAgendaManagement.php`

### **3. View Files**
- `resources/views/livewire/purchase-order-agenda-tab.blade.php`
- `resources/views/livewire/agenda-management.blade.php`
- `resources/views/livewire/incoming-goods-agenda-management.blade.php`

---

## 🔍 **Verification Steps**

### **1. Test Create Agenda**
```php
// Test di tinker
php artisan tinker

$agenda = new \App\Models\IncomingGoodsAgenda();
$agenda->goods_name = 'Test Barang';
$agenda->supplier_name = 'Test Supplier';
$agenda->quantity = 10;
$agenda->unit = 'pcs';
$agenda->unit_price = 1000;
$agenda->total_amount = 10000;
$agenda->scheduled_date = now();
$agenda->payment_due_date = now()->addDays(7);
$agenda->status = 'scheduled';
$agenda->created_by = 1;
$agenda->save();

// Cek berhasil
echo $agenda->id ? "Success" : "Failed";
exit;
```

### **2. Test Livewire Component**
1. Buka halaman agenda management
2. Coba create agenda baru
3. Pastikan tidak ada error

### **3. Test Form Validation**
1. Coba submit form kosong
2. Pastikan validation bekerja
3. Coba submit form dengan data lengkap
4. Pastikan agenda berhasil dibuat

---

## 🚨 **Common Issues & Solutions**

### **Issue 1: Still Getting Error After Fix**
**Solution:** Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### **Issue 2: Field Not Found**
**Solution:** Check migration status
```bash
php artisan migrate:status
```

### **Issue 3: Mass Assignment Exception**
**Solution:** Check fillable array
```php
// Pastikan 'goods_name' ada di fillable
protected $fillable = [
    'goods_name',
    // ... other fields
];
```

---

## 📊 **Expected Results**

Setelah mengikuti solusi di atas:

1. ✅ Error "Field 'goods_name' doesn't have a default value" akan hilang
2. ✅ Agenda bisa dibuat dengan sukses
3. ✅ Form validation bekerja dengan benar
4. ✅ Data tersimpan dengan benar di database

---

## 🎯 **Best Practices**

### **1. Consistent Naming**
Gunakan nama field yang konsisten antara migration dan model:
- Migration: `goods_name`
- Model: `goods_name`
- View: `goods_name`

### **2. Proper Validation**
Tambahkan validation rules:
```php
protected $rules = [
    'goods_name' => 'required|string|max:255',
    'supplier_name' => 'required|string|max:255',
    // ... other rules
];
```

### **3. Default Values**
Set default values untuk field yang penting:
```php
protected $attributes = [
    'goods_name' => 'Barang Various',
    'status' => 'scheduled',
    'payment_status' => 'unpaid',
];
```

---

**Status: Solution Documented ✅**
**Ready for Implementation 🚀**