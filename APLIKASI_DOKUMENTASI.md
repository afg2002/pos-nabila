# 📋 Dokumentasi Lengkap Aplikasi POS Nabila

## 🎯 Overview Aplikasi
**POS Nabila** adalah sistem Point of Sale (POS) berbasis web yang dibangun menggunakan Laravel 12.21.0 dengan Livewire 3.6.4 untuk interaktivitas real-time. Aplikasi ini dirancang untuk mengelola penjualan, inventori, dan operasional bisnis retail.

## 🛠️ Teknologi Stack

### Backend
- **PHP**: 8.3.9
- **Laravel Framework**: 12.21.0
- **Database**: MySQL
- **Livewire**: 3.6.4 (untuk komponen interaktif)

### Frontend
- **TailwindCSS**: 4.1.11 (styling)
- **Alpine.js**: (JavaScript framework ringan)
- **Blade Templates**: (templating engine Laravel)

### Package Dependencies
```json
{
  "laravel/framework": "^12.0",
  "livewire/livewire": "^3.6.4",
  "spatie/laravel-permission": "^6.9.0",
  "maatwebsite/excel": "^3.1.58",
  "barryvdh/laravel-dompdf": "^3.0.0",
  "intervention/image": "^3.8.0",
  "pusher/pusher-php-server": "^7.2.4"
}
```

## 📊 Struktur Database

### Tabel Utama POS System

#### 1. **users** (Pengguna Sistem)
```sql
- id (bigint, primary key)
- name (varchar) - Nama pengguna
- email (varchar, unique) - Email login
- email_verified_at (timestamp)
- is_active (tinyint) - Status aktif user
- password (varchar) - Password terenkripsi
- remember_token (varchar)
- created_at, updated_at (timestamp)
```

#### 2. **products** (Produk)
```sql
- id (bigint, primary key)
- name (varchar) - Nama produk
- sku (varchar, unique) - Kode produk
- description (text) - Deskripsi produk
- category_id (bigint) - Kategori produk
- unit_id (bigint) - Satuan produk
- purchase_price (decimal) - Harga beli
- selling_price (decimal) - Harga jual
- stock_quantity (int) - Stok tersedia
- min_stock (int) - Stok minimum
- image (varchar) - Path gambar produk
- is_active (boolean) - Status aktif produk
- created_at, updated_at (timestamp)
```

#### 3. **warehouses** (Gudang)
```sql
- id (bigint, primary key)
- name (varchar) - Nama gudang
- code (varchar, unique) - Kode gudang
- address (text) - Alamat gudang
- phone (varchar) - Telepon gudang
- manager_name (varchar) - Nama manager
- is_active (boolean) - Status aktif
- created_at, updated_at (timestamp)
```

#### 4. **warehouse_stocks** (Stok Gudang)
```sql
- id (bigint, primary key)
- warehouse_id (bigint) - ID gudang
- product_id (bigint) - ID produk
- quantity (int) - Jumlah stok
- reserved_quantity (int) - Stok yang direservasi
- last_updated (timestamp) - Terakhir diupdate
- created_at, updated_at (timestamp)
```

#### 5. **suppliers** (Pemasok)
```sql
- id (bigint, primary key)
- name (varchar) - Nama supplier
- code (varchar, unique) - Kode supplier
- contact_person (varchar) - Nama kontak
- phone (varchar) - Telepon
- email (varchar) - Email
- address (text) - Alamat
- is_active (boolean) - Status aktif
- created_at, updated_at (timestamp)
```

#### 6. **purchase_orders** (Pesanan Pembelian)
```sql
- id (bigint, primary key)
- po_number (varchar, unique) - Nomor PO
- supplier_id (bigint) - ID supplier
- order_date (date) - Tanggal order
- expected_date (date) - Tanggal diharapkan
- status (enum) - pending, approved, received, cancelled
- total_amount (decimal) - Total nilai
- notes (text) - Catatan
- created_by (bigint) - Dibuat oleh user
- approved_by (bigint) - Disetujui oleh
- approved_at (timestamp) - Waktu persetujuan
- created_at, updated_at (timestamp)
```

#### 7. **purchase_order_items** (Item Pesanan Pembelian)
```sql
- id (bigint, primary key)
- purchase_order_id (bigint) - ID purchase order
- product_id (bigint) - ID produk
- quantity (int) - Jumlah dipesan
- unit_price (decimal) - Harga satuan
- total_price (decimal) - Total harga
- received_quantity (int) - Jumlah diterima
- created_at, updated_at (timestamp)
```

#### 8. **stock_movements** (Pergerakan Stok)
```sql
- id (bigint, primary key)
- product_id (bigint) - ID produk
- warehouse_id (bigint) - ID gudang
- movement_type (enum) - in, out, transfer, adjustment
- quantity (int) - Jumlah pergerakan
- reference_type (varchar) - Tipe referensi (sale, purchase, etc)
- reference_id (bigint) - ID referensi
- notes (text) - Catatan
- created_by (bigint) - Dibuat oleh
- movement_date (timestamp) - Tanggal pergerakan
- created_at, updated_at (timestamp)
```

#### 9. **agenda_events** (Agenda Kegiatan)
```sql
- id (bigint, primary key)
- title (varchar) - Judul agenda
- description (text) - Deskripsi
- event_date (date) - Tanggal acara
- event_time (time) - Waktu acara
- event_type (enum) - meeting, delivery, reminder, other
- priority (enum) - low, medium, high, urgent
- status (enum) - pending, in_progress, completed, cancelled
- location (varchar) - Lokasi
- attendees (json) - Peserta
- notes (text) - Catatan
- reminder_minutes (int) - Pengingat (menit)
- created_by (bigint) - Dibuat oleh
- created_at, updated_at (timestamp)
```

#### 10. **incoming_goods_agendas** (Agenda Barang Masuk)
```sql
- id (bigint, primary key)
- supplier_id (bigint) - ID supplier
- expected_date (date) - Tanggal diharapkan
- description (text) - Deskripsi
- status (enum) - scheduled, arrived, processed, completed
- notes (text) - Catatan
- created_by (bigint) - Dibuat oleh
- processed_at (timestamp) - Waktu diproses
- created_at, updated_at (timestamp)
```

## 🎨 Komponen Livewire

### 1. **PosKasir** (Komponen Utama POS)
**File**: `app/Livewire/PosKasir.php`

**Fitur Utama**:
- ✅ Pencarian produk real-time
- ✅ Manajemen keranjang belanja
- ✅ Sistem pricing tier (Harga Eceran, Grosir, Distributor)
- ✅ Item custom (tambah item manual)
- ✅ Perhitungan otomatis (subtotal, diskon, pajak, total)
- ✅ Metode pembayaran (Cash, Transfer, Kartu)
- ✅ Print receipt
- ✅ Riwayat transaksi

**Properties Utama**:
```php
public $searchTerm = '';
public $cart = [];
public $selectedPricingTier = 'eceran';
public $discount = 0;
public $tax = 0;
public $paymentMethod = 'cash';
public $amountPaid = 0;
public $showCustomItemModal = false;
public $customItemName = '';
public $customItemPrice = 0;
public $customItemQuantity = 1;
```

**Methods Penting**:
- `addToCart($productId)` - Tambah produk ke keranjang
- `updateQuantity($index, $quantity)` - Update jumlah item
- `removeFromCart($index)` - Hapus item dari keranjang
- `showCustomItemModal()` - Tampilkan modal item custom
- `addCustomItem()` - Tambah item custom ke keranjang
- `processPayment()` - Proses pembayaran
- `printReceipt()` - Print struk

### 2. **ProductTable** (Manajemen Produk)
**File**: `app/Livewire/ProductTable.php`

**Fitur**:
- CRUD produk lengkap
- Upload gambar produk
- Manajemen kategori dan satuan
- Filter dan pencarian
- Export data

### 3. **WarehouseTable** (Manajemen Gudang)
**File**: `app/Livewire/WarehouseTable.php`

**Fitur**:
- CRUD gudang
- Manajemen stok per gudang
- Transfer stok antar gudang

### 4. **PurchaseOrderManagement** (Manajemen Purchase Order)
**File**: `app/Livewire/PurchaseOrderManagement.php`

**Fitur**:
- Buat PO baru
- Approval workflow
- Tracking status pengiriman
- Penerimaan barang

### 5. **Dashboard** (Dashboard Utama)
**File**: `app/Livewire/Dashboard.php`

**Fitur**:
- Statistik penjualan
- Grafik performa
- Notifikasi stok menipis
- Agenda hari ini

## 🗂️ Struktur File Views

### Layout Utama
```
resources/views/
├── layouts/
│   ├── app.blade.php (Layout utama)
│   └── guest.blade.php (Layout untuk guest)
├── livewire/
│   ├── pos-kasir.blade.php (Interface POS)
│   ├── product-table.blade.php (Tabel produk)
│   ├── warehouse-table.blade.php (Tabel gudang)
│   ├── dashboard.blade.php (Dashboard)
│   └── ... (komponen lainnya)
└── components/
    ├── navigation.blade.php (Menu navigasi)
    └── sidebar.blade.php (Sidebar)
```

## 🔐 Sistem Permission

Menggunakan **Spatie Laravel Permission** untuk role-based access control:

### Permissions Utama:
- `users.view`, `users.create`, `users.edit`, `users.delete`
- `products.view`, `products.create`, `products.edit`, `products.delete`
- `inventory.view`, `inventory.manage`
- `pos.access` (akses ke sistem POS)
- `reports.view`, `reports.export`
- `customers.view`, `customers.create`, `customers.edit`

## 🛣️ Routing Structure

### Route Groups:
```php
// Dashboard & Main
Route::get('/dashboard') -> Dashboard utama
Route::get('/pos') -> Interface POS Kasir

// Product Management
Route::get('/products') -> Manajemen produk
Route::get('/product-units') -> Manajemen satuan
Route::get('/categories') -> Manajemen kategori

// Inventory Management  
Route::get('/inventory') -> Manajemen inventori
Route::get('/warehouses') -> Manajemen gudang
Route::get('/suppliers') -> Manajemen supplier

// Purchase & Supply Chain
Route::get('/purchase-orders') -> Purchase orders
Route::get('/incoming-goods-agenda') -> Agenda barang masuk

// Financial Management
Route::get('/cash-ledger') -> Buku kas
Route::get('/capital-tracking') -> Tracking modal
Route::get('/debt-reminders') -> Pengingat hutang

// Reports & Analytics
Route::get('/reports') -> Laporan & analitik

// User Management
Route::get('/users') -> Manajemen user
Route::get('/roles') -> Manajemen role
Route::get('/profile') -> Profil user
```

## 🎯 Fitur Utama Aplikasi

### 1. **Point of Sale (POS) System** 🛒
- Interface kasir yang user-friendly
- Pencarian produk real-time dengan barcode scanner support
- Sistem pricing tier (Eceran, Grosir, Distributor)
- Manajemen keranjang belanja dengan update otomatis
- Custom item untuk produk tidak terdaftar
- Multiple payment methods (Cash, Transfer, Kartu)
- Print receipt otomatis
- Riwayat transaksi lengkap

### 2. **Inventory Management System** 📦
- Multi-warehouse inventory tracking
- Real-time stock monitoring
- Stock movement history
- Low stock alerts
- Stock transfer antar gudang
- Batch tracking dan expiry date management

### 3. **Product Management** 🏷️
- CRUD produk lengkap dengan gambar
- Kategori dan sub-kategori produk
- Multiple unit of measure (UOM)
- Pricing tier management
- Barcode generation
- Product variants support

### 4. **Purchase Order Management** 📋
- Create dan manage purchase orders
- Supplier management
- Approval workflow
- Delivery tracking
- Goods receiving process
- Cost analysis dan reporting

### 5. **Financial Management** 💰
- Cash ledger (buku kas)
- Capital tracking
- Debt reminder system
- Profit/loss analysis
- Tax calculation
- Financial reports export (Excel/PDF)

### 6. **Agenda & Scheduling** 📅
- Event calendar management
- Incoming goods scheduling
- Reminder notifications
- Task management
- Meeting scheduler

### 7. **Reporting & Analytics** 📊
- Sales reports (daily, weekly, monthly)
- Inventory reports
- Financial statements
- Performance analytics
- Export capabilities (Excel, PDF)
- Print functionality

### 8. **User Management** 👥
- Role-based access control
- User permissions management
- Activity logging
- Profile management
- Multi-user support

## 🔧 Konfigurasi Environment

### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

### Application Settings
```env
APP_NAME="POS Nabila"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000
```

## 🚀 Instalasi & Setup

### Prerequisites
- PHP 8.3+
- MySQL 5.7+
- Composer
- Node.js & NPM

### Langkah Instalasi
```bash
# Clone repository
git clone [repository-url]
cd pos-nabila

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Start server
php artisan serve
```

## 🧪 Testing

### Unit Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=PosKasirTest
```

### Feature Tests
- POS transaction flow
- Inventory management
- User authentication
- Permission system
- Report generation

## 📱 Mobile Responsiveness

Aplikasi didesain responsive menggunakan TailwindCSS dengan breakpoints:
- Mobile: 320px - 768px
- Tablet: 768px - 1024px  
- Desktop: 1024px+

## 🔒 Security Features

- CSRF Protection
- SQL Injection Prevention
- XSS Protection
- Role-based Access Control
- Password Hashing (bcrypt)
- Session Management
- Input Validation & Sanitization

## 📈 Performance Optimization

- Database indexing
- Query optimization
- Livewire lazy loading
- Image optimization
- Caching strategies
- Asset minification

## 🐛 Known Issues & Troubleshooting

### Current Issues:
1. **Alpine.js Syntax Error**: `SyntaxError: Unexpected token '}'` di beberapa komponen
2. **Custom Item Modal**: Modal tidak muncul pada beberapa browser
3. **JavaScript Conflicts**: Konflik antara Livewire dan Alpine.js

### Solutions:
1. Periksa syntax Alpine.js expressions
2. Clear view cache: `php artisan view:clear`
3. Update Livewire ke versi terbaru

## 🔄 Future Enhancements

### Planned Features:
- [ ] Barcode scanner integration
- [ ] Multi-currency support
- [ ] Advanced reporting dashboard
- [ ] Mobile app (React Native/Flutter)
- [ ] API for third-party integrations
- [ ] Automated backup system
- [ ] Advanced inventory forecasting
- [ ] Customer loyalty program
- [ ] Multi-location support
- [ ] Real-time notifications

## 📞 Support & Maintenance

### Development Team:
- **Lead Developer**: [Your Name]
- **Backend Developer**: Laravel Specialist
- **Frontend Developer**: Livewire/Alpine.js Expert
- **Database Administrator**: MySQL Expert

### Maintenance Schedule:
- **Daily**: Database backup
- **Weekly**: Performance monitoring
- **Monthly**: Security updates
- **Quarterly**: Feature updates

---

## 📋 Summary untuk AI Development

**Aplikasi POS Nabila** adalah sistem Point of Sale komprehensif yang dibangun dengan:

- **Backend**: Laravel 12.21.0 + PHP 8.3.9
- **Frontend**: Livewire 3.6.4 + TailwindCSS 4.1.11 + Alpine.js
- **Database**: MySQL dengan 13 model utama
- **Fitur**: POS, Inventory, Purchase Orders, Financial Management, Reporting
- **Architecture**: MVC dengan Livewire components untuk interaktivitas
- **Security**: Spatie Permission untuk role-based access control

**Key Components**:
1. **PosKasir** - Main POS interface
2. **ProductTable** - Product management
3. **WarehouseTable** - Inventory management
4. **PurchaseOrderManagement** - Supply chain
5. **Dashboard** - Analytics & overview

**Current Status**: Fully functional dengan beberapa minor issues di Alpine.js yang perlu diperbaiki.

Aplikasi siap untuk full-stack development dengan dokumentasi lengkap dan struktur yang terorganisir! 🚀✨