# Pending Issues & Improvements

## 🔴 Critical Issues

### 1. Cache Redis Error
- **Error**: `Call to undefined method Illuminate\Cache\DatabaseStore::getRedis()`
- **Impact**: System functionality affected
- **Priority**: HIGH
- **Status**: PENDING
- **Date Added**: 2025-01-27

### 2. Alert System Inconsistency
- **Issue**: Multiple alert types being used throughout the application
- **Required**: Standardize to single alert system with timer and auto-close
- **Priority**: MEDIUM
- **Status**: PENDING
- **Date Added**: 2025-01-27

## 📦 Inventory Management

### 1. Product Stock Management per Warehouse
- **Issue**: Products CRUD doesn't allow setting stock per warehouse during creation/editing
- **Required**: Add warehouse stock allocation in product form
- **Location**: `/products` page - ProductTable.php
- **Priority**: HIGH
- **Status**: PENDING
- **Date Added**: 2025-01-27

### 2. Inventory Stock Updates with Warehouse Selection
- **Issue**: Stock updates don't specify target warehouse - updates done without warehouse selection
- **Required**: Add warehouse selection dropdown for stock updates
- **Location**: Inventory management - StockForm.php
- **Priority**: HIGH
- **Status**: PENDING
- **Date Added**: 2025-01-27

### 3. Warehouse Management Issues
- **Error**: `Unable to call component method. Public method [closeModal] not found on component`
- **Missing**: No "Add Data" button for warehouses
- **Required**: Fix closeModal method and add create warehouse functionality for multiple stores/warehouses
- **Location**: WarehouseTable.php
- **Priority**: HIGH
- **Status**: PENDING
- **Date Added**: 2025-01-27

## 💰 POS System

### 1. POS Cashier System
- **Issue**: POS Cashier needs additional features
- **Required**: 
  - Receipt/invoice printing after transaction
  - Cashier transaction history
  - Daily/weekly/monthly sales reports
  - Integration with cashier printers
- **Location**: POS module
- **Priority**: HIGH
- **Status**: IN PROGRESS
- **Date Added**: 2025-01-27

## 📋 Agenda System Revisions

### Current: "Incoming Goods Agenda" → Split into 2 tabs:

#### Tab 1: Agenda Cashflow
- Total daily revenue (retail & wholesale)
- Wholesale payments (cash, QR, EDC)
- **Status**: PENDING
- **Date Added**: 2025-01-27

#### Tab 2: Agenda Purchase Order
- Company/PT
- Due Date (Jatuh Tempo)
- Total Purchase Amount
- No need for item-by-item input, just total amount
- **Status**: PENDING
- **Date Added**: 2025-01-27

## 🗑️ Removal Tasks

### Remove Separate Purchase Order Menu
- **Task**: Delete standalone purchase order menu
- **Reason**: Functionality moved to Agenda Purchase Order tab
- **Priority**: MEDIUM
- **Status**: PENDING
- **Date Added**: 2025-01-27

## 📊 Cash Book Enhancements

### Annual Level Financial Reporting
- **Issue**: Cash book doesn't show total income and expenses up to yearly level
- **Required**: Add yearly, monthly, daily income/expense totals display
- **Location**: Cash Ledger/Financial Reports
- **Priority**: MEDIUM
- **Status**: PENDING
- **Date Added**: 2025-01-27
- Due date
- Total purchase amount
- No need for item-by-item input, just total
- **Status**: PENDING

### Menu Changes
- **Remove**: Separate purchase order menu
- **Update**: Cash book to show income/expense totals up to yearly level
- **Status**: PENDING

## 📝 Implementation Notes
- All fixes should be tested before marking complete
- Update this file after each completed task
- Maintain consistent coding standards
- Ensure RBAC permissions are properly applied

---
**Created**: $(Get-Date)
**Last Updated**: $(Get-Date)
// No pending items: stock history patches applied.