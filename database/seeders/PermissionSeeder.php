<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Domains\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing permissions
        DB::table('permissions')->delete();
        
        // Define permissions
        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Role Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            
            // Product Management
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.import',
            
            // Product Unit Management
            'product_units.view',
            'product_units.create',
            'product_units.edit',
            'product_units.delete',
            
            // Inventory Management
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
            'inventory.adjust',
            
            // Supplier Management
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            
            // POS Management
            'pos.access',
            'pos.view_sales',
            'pos.create_sales',
            'pos.edit_sales',
            'pos.delete_sales',
            'pos.print_receipt',
            'pos.manage_invoices',
            'pos.view_reports',
            
            // Capital Tracking Management
            'capital_tracking.view',
            'capital_tracking.create',
            'capital_tracking.edit',
            'capital_tracking.delete',
            'capital_tracking.view_reports',
            
            // Cash Ledger Management
            'cash_ledger.view',
            'cash_ledger.create',
            'cash_ledger.edit',
            'cash_ledger.delete',
            'cash_ledger.export',
            'cash_ledger.print',
            'cash_ledger.view_reports',
            
            // Agenda Management (Enhanced)
            'agenda.view',
            'agenda.create',
            'agenda.edit',
            'agenda.delete',
            'agenda.manage_cashflow',
            'agenda.manage_purchase_order',
            'agenda.view_reports',
            'agenda.export',
            
            // Cashflow Agenda Management
            'cashflow_agenda.view',
            'cashflow_agenda.create',
            'cashflow_agenda.edit',
            'cashflow_agenda.delete',
            'cashflow_agenda.view_reports',
            
            // Incoming Goods Agenda Management
            'incoming_goods_agenda.view',
            'incoming_goods_agenda.create',
            'incoming_goods_agenda.edit',
            'incoming_goods_agenda.delete',
            'incoming_goods_agenda.receive',
            'incoming_goods_agenda.manage_payments',
            'incoming_goods_agenda.view_reports',
            
            // Purchase Order Management
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.edit',
            'purchase_orders.delete',
            'purchase_orders.approve',
            'purchase_orders.receive',
            'purchase_orders.view_reports',
            
            // Sales Invoice Management
            'sales_invoices.view',
            'sales_invoices.create',
            'sales_invoices.edit',
            'sales_invoices.delete',
            'sales_invoices.manage_payments',
            'sales_invoices.print',
            'sales_invoices.view_reports',
            
            // Batch Expiration Management
            'batch_expirations.view',
            'batch_expirations.create',
            'batch_expirations.edit',
            'batch_expirations.delete',
            'batch_expirations.manage_alerts',
            'batch_expirations.view_reports',
            
            // Warehouse Management
            'warehouses.view',
            'warehouses.create',
            'warehouses.edit',
            'warehouses.delete',
            'warehouses.manage_stock',
            'warehouses.view_reports',
            
            // Financial Management
            'financial.view',
            'financial.create',
            'financial.edit',
            'financial.delete',
            'financial.view_reports',
            'financial.export',
            
            // Report Management
            'reports.view',
            'reports.create',
            'reports.export',
            'reports.print',
            
            // Settings Management
            'settings.view',
            'settings.edit',
            'settings.manage_system',
            
            // Dashboard Access
            'dashboard.view',
            'dashboard.admin',
            'dashboard.financial',
            'dashboard.inventory',
            'dashboard.sales',
        ];
        
        // Create permissions using custom Domain model and fill required columns
        foreach ($permissions as $permissionName) {
            $group = explode('.', $permissionName)[0] ?? null;
            $displayName = ucwords(str_replace(['_', '.'], [' ', ' '], $permissionName));

            Permission::create([
                'name' => $permissionName,
                'display_name' => $displayName,
                'description' => null,
                'group' => $group,
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Permissions created successfully!');
    }
}
