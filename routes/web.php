<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get("/", fn() => view("welcome"))->name("welcome");

// Dashboard Route (redirect /home to /dashboard)
Route::get("/dashboard", function () {
    return view("dashboard");
})
    ->middleware(["auth"])
    ->name("dashboard");

// User Management Routes
Route::middleware(["auth", "permission:users.view"])->group(function () {
    Route::get("/users", fn() => view("users.index"))->name(
        "users.index",
    );
});

// Role Management Routes
Route::middleware(["auth", "permission:roles.view"])->group(function () {
    Route::get("/roles", fn() => view("roles.index"))->name(
        "roles.index",
    );
});

// Product Management
Route::middleware(["auth", "permission:products.view"])->group(function () {
    Route::get("/products", fn() => view("products.index"))->name(
        "products.index",
    );

    Route::get("/products/import", fn() => view("products.import"))
        ->name("products.import")
        ->middleware("permission:products.create");

    Route::get("/products/import/confirm", App\Livewire\ProductImportConfirm::class)
        ->name("products.import.confirm")
        ->middleware("permission:products.create");

    Route::get("/categories", App\Livewire\CategoryManagement::class)->name(
        "categories.index",
    );
});

// Product Unit Management
Route::middleware(["auth", "permission:products.view"])->group(function () {
    Route::get("/product-units", fn() => view("product-units.index"))->name(
        "product-units.index",
    );
});

// Inventory Management
Route::middleware("auth")->group(function () {
    Route::get("/inventory", fn() => view("inventory.index"))->name(
        "inventory.index",
    );
});

// Supplier Management
Route::middleware(["auth", "permission:suppliers.view"])->group(function () {
    Route::get("/suppliers", function () {
        return view("suppliers.index");
    })->name("suppliers.index");
});

// POS (Point of Sale) Management
Route::middleware(["auth", "permission:pos.access"])->group(function () {
    Route::get("/pos", App\Livewire\PosResponsive::class)->name("pos.index");
    Route::get("/pos/responsive", App\Livewire\PosResponsive::class)->name("pos.responsive");
    Route::get("/pos/kasir", App\Livewire\PosKasir::class)->name("pos.kasir");
});

// Kasir Management
Route::middleware(["auth", "permission:pos.access"])->group(function () {
    Route::get("/kasir", App\Livewire\KasirManagement::class)->name(
        "kasir.management",
    );
});

// Capital Tracking Management
Route::middleware(["auth"])->group(function () {
    Route::get("/capital-tracking", App\Livewire\CapitalTrackingManagement::class)->name(
        "capital-tracking.index",
    );
});

// Cash Ledger Management
Route::middleware(["auth"])->group(function () {
    Route::get("/cash-ledger", App\Livewire\CashLedgerManagement::class)->name(
        "cash-ledger.index",
    );

    // Cash Ledger Export Routes
    Route::get("/cash-ledger/export/excel", [
        \App\Livewire\CashLedgerManagement::class,
        "exportExcel",
    ])->name("cash-ledger.export.excel");
    Route::get("/cash-ledger/export/pdf", [
        \App\Livewire\CashLedgerManagement::class,
        "exportPdf",
    ])->name("cash-ledger.export.pdf");
    Route::get("/cash-ledger/print", [
        \App\Livewire\CashLedgerManagement::class,
        "printReport",
    ])->name("cash-ledger.print");
});

// Cashflow Agenda Management
Route::middleware(["auth", "permission:cashflow_agenda.view"])->group(function () {
    Route::get("/cashflow-agenda", fn() => view("cashflow-agenda.index"))
        ->name("cashflow-agenda.index");
});

// Incoming Goods Agenda Management (Legacy - for backward compatibility)
Route::middleware(["auth", "permission:incoming_goods_agenda.view"])->group(function () {
    Route::get("/incoming-goods-agenda", fn() => view("incoming-goods-agenda.index"))
        ->name("incoming-goods-agenda.index");
});

// Agenda Management (halaman dihapus sesuai instruksi; menggunakan halaman legacy incoming-goods-agenda)

// Warehouse Management
Route::middleware(["auth", "permission:warehouses.view"])->group(function () {
    

    // Warehouse management routes
    Route::resource(
        "warehouses",
        "App\\Http\\Controllers\\WarehouseController",
    )->middleware("auth");
});

// Profile Settings Routes
Route::middleware(["auth"])->group(function () {
    Route::get("/profile", App\Livewire\ProfileForm::class)->name(
        "profile.index",
    );
});

Auth::routes();

Route::get("/home", function () {
    return redirect()->route("dashboard");
})
    ->middleware(["auth"])
    ->name("home");
