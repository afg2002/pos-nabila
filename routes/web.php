<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Test route for Alpine.js debugging
Route::get('/test-alpine', App\Livewire\TestAlpine::class)->name('test.alpine');

// Dashboard Route (redirect /home to /dashboard)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// User Management Routes
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::get('/users', function () {
        return view('users.index');
    })->name('users.index');
});

// Role Management Routes  
Route::middleware(['auth', 'permission:roles.view'])->group(function () {
    Route::get('/roles', function () {
        return view('roles.index');
    })->name('roles.index');
});

// Product Management
Route::middleware(['auth', 'permission:products.view'])->group(function () {
    Route::get('/products', function () {
        return view('products.index');
    })->name('products.index');
    
    Route::get('/products/import', function () {
        return view('products.import');
    })->name('products.import')->middleware('permission:products.create');
});

// Product Unit Management
Route::middleware(['auth', 'permission:products.view'])->group(function () {
    Route::get('/product-units', function () {
        return view('product-units.index');
    })->name('product-units.index');
});

// Inventory Management
Route::middleware('auth')->group(function () {
    Route::get('/inventory', function () {
        return view('inventory.index');
    })->name('inventory.index');
});

// Customer Management
Route::middleware(['auth', 'permission:customers.view'])->group(function () {
    Route::get('/customers', function () {
        return view('customers.index');
    })->name('customers.index');
});

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

// Inventory Management System
Route::middleware(['auth', 'permission:inventory.view'])->group(function () {
    Route::get('/inventory', function () {
        return view('inventory.index');
    })->name('inventory.index');
});


// POS (Point of Sale) Management
Route::middleware(['auth', 'permission:pos.access'])->group(function () {
    Route::get('/pos', function () {
        return view('pos.index');
    })->name('pos.index');
});

// Capital Tracking Management
Route::middleware(['auth'])->group(function () {
    Route::get('/capital-tracking', function () {
        return view('capital-tracking.index');
    })->name('capital-tracking.index');
});


// Cash Ledger Management
Route::middleware(['auth'])->group(function () {
    Route::get('/cash-ledger', function () {
        return view('cash-ledger.index');
    })->name('cash-ledger.index');
    
    // Cash Ledger Export Routes
    Route::get('/cash-ledger/export/excel', [\App\Livewire\CashLedgerManagement::class, 'exportExcel'])->name('cash-ledger.export.excel');
    Route::get('/cash-ledger/export/pdf', [\App\Livewire\CashLedgerManagement::class, 'exportPdf'])->name('cash-ledger.export.pdf');
    Route::get('/cash-ledger/print', [\App\Livewire\CashLedgerManagement::class, 'printReport'])->name('cash-ledger.print');
});

// Incoming Goods Agenda Management
Route::middleware(['auth'])->group(function () {
    Route::get('/incoming-goods-agenda', function () {
        return view('incoming-goods-agenda.index');
    })->name('incoming-goods-agenda.index');
});

// Profile Settings Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');
    Route::get('/pos', function () {
        return view('pos.index');
    })->name('pos.index');
    
    Route::get('/inventory', function () {
        return view('inventory.index');
    })->name('inventory.index');
    Route::get('/products', function () {
        return view('products.index');
    })->name('products.index');
    Route::get('/categories', function () {
        return view('categories.index');
    })->name('categories.index');
    Route::get('/units', function () {
        return view('product-units.index');
    })->name('units.index');
    Route::get('/suppliers', function () {
        return view('suppliers.index');
    })->name('suppliers.index');
    Route::get('/customers', function () {
        return view('customers.index');
    })->name('customers.index');
    Route::get('/transactions', function () {
        return view('transactions.index');
    })->name('transactions.index');
    Route::get('/cash-ledger', function () {
        return view('cash-ledger.index');
    })->name('cash-ledger.index');
    Route::get('/incoming-goods', function () {
        return view('incoming-goods-agenda.index');
    })->name('incoming-goods.index');
    Route::get('/warehouses', function () {
        return view('warehouses.index');
    })->name('warehouses.index');
    
    // Warehouse management routes
    Route::resource('warehouses', 'App\\Http\\Controllers\\WarehouseController')->middleware('auth');
    

});

Auth::routes();

Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('home');
