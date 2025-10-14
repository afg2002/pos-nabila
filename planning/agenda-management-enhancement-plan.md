# 📋 Agenda Management Enhancement Plan

## 🎯 Overview
Enhancement sistem agenda management untuk menggantikan menu Purchase Order terpisah dan menambah fitur-fitur baru sesuai requirements client.

## 📊 Current State Analysis

### Existing Tables:
- `cashflow_agenda` - sudah ada payment tracking (cash, QR, EDC)
- `incoming_goods_agenda` - sudah ada simplified input mode
- `purchase_orders` - akan diintegrasikan ke agenda
- `sales` - perlu enhancement untuk multiple bon

### Existing Features:
- Cashflow agenda dengan payment methods
- Incoming goods agenda dengan simplified/detailed mode
- POS Kasir basic
- Supplier management

## 🚀 Enhancement Requirements

### 1. Single Page Agenda Management
**Current:** Multiple pages terpisah
**Target:** Single page dengan 2 tab
- Tab 1: Agenda Cashflow
- Tab 2: Agenda Purchase Order (ganti incoming goods)

### 2. Agenda Barang Datang Enhancement
**Current:** Input detail per item
**Target:** Input sederhana total
- PT (dari supplier dropdown)
- Jatuh Tempo
- Jumlah Total Belanja
- **NEW:** Expired Date per batch
- **NEW:** Auto-generate PO number

### 3. POS Kasir Enhancement
**Current:** Single bon per transaksi
**Target:** Multiple bon per transaksi
- **NEW:** Multiple invoice per sale
- **NEW:** Cetak thermal 80x100mm (Kassen DT360)
- **NEW:** Payment status per nota (lunas/sebagian/belum)
- **NEW:** Customer info per nota

### 4. Integration Enhancement
**Current:** Partial integration
**Target:** Full integration
- Capital Tracking ↔ Cashflow Agenda
- Cash Ledger ↔ POS & Cashflow
- Supplier ↔ Agenda Barang Datang
- PO ↔ Agenda Barang Datang

## 🗄️ Database Schema Changes

### 1. Incoming Goods Agenda Enhancement
```sql
ALTER TABLE incoming_goods_agenda ADD COLUMN:
- batch_number VARCHAR(50) NULLABLE
- expired_date DATE NULLABLE
- is_purchase_order_generated BOOLEAN DEFAULT FALSE
- po_number VARCHAR(50) NULLABLE
- remaining_amount DECIMAL(15,2) DEFAULT 0
- payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid'
```

### 2. New Tables Needed

#### Sales Invoices (Multiple Bon)
```sql
CREATE TABLE sales_invoices (
    id BIGINT PRIMARY KEY,
    sale_id BIGINT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    customer_name VARCHAR(255) NULLABLE,
    customer_phone VARCHAR(20) NULLABLE,
    subtotal DECIMAL(15,2) NOT NULL,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    remaining_amount DECIMAL(15,2) DEFAULT 0,
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    payment_method ENUM('cash', 'qr', 'edc', 'transfer') NULLABLE,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id)
);
```

#### Invoice Payments
```sql
CREATE TABLE invoice_payments (
    id BIGINT PRIMARY KEY,
    invoice_id BIGINT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash', 'qr', 'edc', 'transfer') NOT NULL,
    payment_date TIMESTAMP NOT NULL,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES sales_invoices(id)
);
```

#### Batch Expirations
```sql
CREATE TABLE batch_expirations (
    id BIGINT PRIMARY KEY,
    incoming_goods_agenda_id BIGINT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    expired_date DATE NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    remaining_quantity DECIMAL(15,2) NOT NULL,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (incoming_goods_agenda_id) REFERENCES incoming_goods_agenda(id)
);
```

### 3. Cashflow Agenda Enhancement
```sql
ALTER TABLE cashflow_agenda ADD COLUMN:
- cash_ledger_id BIGINT NULLABLE
- total_expenses DECIMAL(15,2) DEFAULT 0
- net_cashflow DECIMAL(15,2) GENERATED ALWAYS AS (total_omset - total_expenses)
```

## 🎨 UI/UX Design

### 1. Single Page Layout
```
┌─────────────────────────────────────────────────────────┐
│ 📊 Agenda Management                                    │
├─────────────────────────────────────────────────────────┤
│ [💰 Cashflow] [📦 Purchase Order]                       │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Tab Content Area                                       │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### 2. Cashflow Tab
- Calendar view untuk input harian
- Summary cards (omset, ecer, grosir, payments)
- Payment method breakdown
- Annual summary chart

### 3. Purchase Order Tab
- Simplified input form
- Supplier dropdown dengan search
- Calendar view untuk tracking
- Status indicators (scheduled, received, paid)

### 4. POS Enhancement
- Multiple invoice buttons
- Thermal print preview
- Payment status indicators
- Customer info form

## 🔧 Implementation Steps

### Phase 1: Database Setup
1. Create migration files
2. Update existing tables
3. Create new tables
4. Update models

### Phase 2: Backend Logic
1. Update IncomingGoodsAgenda model
2. Create SalesInvoice model
3. Update CashflowAgenda model
4. Create services for integration

### Phase 3: Frontend Development
1. Create single page agenda management
2. Implement tab navigation
3. Update POS interface
4. Add thermal print functionality

### Phase 4: Integration
1. Link Capital Tracking
2. Link Cash Ledger
3. Update Supplier integration
4. Test all workflows

### Phase 5: Testing & Deployment
1. Unit testing
2. Integration testing
3. User acceptance testing
4. Deployment

## 📋 File Structure

### New Files to Create
```
database/migrations/
├── 2025_10_14_200000_add_purchase_order_integration_to_incoming_goods_agenda.php
├── 2025_10_14_200100_add_batch_expiration_to_incoming_goods_agenda.php
├── 2025_10_14_200200_create_sales_invoices_table.php
├── 2025_10_14_200300_create_invoice_payments_table.php
├── 2025_10_14_200400_create_batch_expirations_table.php
└── 2025_10_14_200500_update_cashflow_agenda_table.php

app/Models/
├── SalesInvoice.php
├── InvoicePayment.php
└── BatchExpiration.php

app/Livewire/
├── AgendaManagement.php (new single page)
├── CashflowAgendaTab.php
├── PurchaseOrderAgendaTab.php
└── PosKasirEnhanced.php

app/Services/
├── AgendaService.php
├── InvoiceService.php
└── ThermalPrintService.php

resources/views/
├── agenda-management/index.blade.php (new single page)
├── livewire/agenda-management.blade.php
├── livewire/cashflow-agenda-tab.blade.php
├── livewire/purchase-order-agenda-tab.blade.php
└── livewire/pos-kasir-enhanced.blade.php
```

### Files to Modify
```
routes/web.php (remove PO route, update agenda route)
app/Models/IncomingGoodsAgenda.php
app/Models/CashflowAgenda.php
app/Models/Sale.php
app/Livewire/PosKasir.php
resources/views/layouts/app.blade.php (navigation)
```

## 🎯 Success Criteria

1. ✅ Single page agenda management dengan 2 tab
2. ✅ Input sederhana untuk agenda barang datang
3. ✅ Expired date tracking per batch
4. ✅ Multiple bon per transaksi POS
5. ✅ Cetak thermal 80x100mm
6. ✅ Payment status tracking
7. ✅ Full integration dengan capital tracking & cash ledger
8. ✅ Annual summary buku kas
9. ✅ Hapus menu PO terpisah

## 🚨 Risks & Mitigations

### Risk 1: Data Migration
- **Mitigation:** Backup existing data, gradual migration

### Risk 2: User Adoption
- **Mitigation:** Training sessions, documentation

### Risk 3: Performance
- **Mitigation:** Database indexing, caching

### Risk 4: Integration Complexity
- **Mitigation:** Step-by-step testing, rollback plan

## 📅 Timeline Estimate

- **Phase 1 (Database):** 2-3 days
- **Phase 2 (Backend):** 4-5 days  
- **Phase 3 (Frontend):** 5-7 days
- **Phase 4 (Integration):** 3-4 days
- **Phase 5 (Testing):** 2-3 days

**Total Estimated:** 16-22 days