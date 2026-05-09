<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ExportController;

// Redirect root to login or dashboard
Route::get('/', fn() => \Illuminate\Support\Facades\Auth::check() ? redirect()->route('dashboard') : redirect()->route('login'));

// Dashboard - accessible by all authenticated users (logic scoped by role inside controller)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

// Authentication routes (login, register, etc.)
require __DIR__.'/auth.php';

// Protected routes with role-based access control
//
// The RoleMiddleware uses a numeric hierarchy: admin=3, manager=2, employee=1.
// 'role:admin'    → only admin
// 'role:manager'  → manager AND admin (level >= 2)
// 'role:employee' → employee, manager AND admin (level >= 1, i.e. any authenticated user)
//
// To avoid duplicate named routes, each route is declared ONCE under the
// highest role that restricts it. Lower roles inherit access via the hierarchy.

Route::middleware(['auth'])->group(function () {

    // ===== ADMIN ONLY: Full CRUD, User Management, Exports =====
    Route::middleware(['role:admin'])->group(function () {
        // Products - full CRUD (managers/employees use read-only routes below)
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::get('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

        // Categories - full CRUD
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

        // Branches - full CRUD
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::patch('branches/{branch}', [BranchController::class, 'update']);
        Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');

        // Users - full management
        Route::resource('users', UserController::class);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');

        // Inventory report (admin only)
        Route::get('inventory/report', [InventoryController::class, 'report'])->name('inventory.report');
    });

    // ===== MANAGER+: Branch product management extras =====
    Route::middleware(['role:manager'])->group(function () {
        Route::patch('branches/{branch}/products/{product}/toggle-availability', [BranchController::class, 'toggleProductAvailability'])->name('branches.products.toggle-availability');
        Route::patch('branches/{branch}/products/{product}/update-stock', [BranchController::class, 'updateProductStock'])->name('branches.products.update-stock');
    });

    // ===== ALL AUTHENTICATED USERS (employee level 1+) =====
    // Each route is declared once here and protected by the role hierarchy.
    Route::middleware(['role:employee'])->group(function () {
        // Products - read only for employee, full access for admin (write routes declared above)
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

        // Categories - read only
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

        // Branches - read only (admin write routes declared above)
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
        Route::get('branches/{branch}/products', [BranchController::class, 'products'])->name('branches.products');
        Route::post('branches/{branch}/products', [BranchController::class, 'assignProduct'])->name('branches.products.assign');
        Route::delete('branches/{branch}/products/{product}', [BranchController::class, 'removeProduct'])->name('branches.products.remove');

        // Inventory - employees can adjust (type restricted inside controller)
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::match(['get', 'post'], 'inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    });
});

// Profile routes - accessible to all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===== NEW FEATURES =====

// Suppliers (Admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('suppliers', SupplierController::class);
    Route::patch('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
});

// Purchase Orders (Admin and Manager only — NOT employees)
Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchaseOrder}/items', [PurchaseOrderController::class, 'addItem'])->name('purchase-orders.add-item');
    Route::delete('purchase-orders/{purchaseOrder}/items/{item}', [PurchaseOrderController::class, 'removeItem'])->name('purchase-orders.remove-item');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
});

// Export/Import routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Excel exports
    Route::get('export/products', [ExportController::class, 'exportProducts'])->name('export.products');
    Route::get('export/categories', [ExportController::class, 'exportCategories'])->name('export.categories');
    Route::get('export/suppliers', [ExportController::class, 'exportSuppliers'])->name('export.suppliers');
    Route::get('export/branches', [ExportController::class, 'exportBranches'])->name('export.branches');
    Route::get('export/inventory', [ExportController::class, 'exportInventory'])->name('export.inventory');
    Route::get('export/movements', [ExportController::class, 'exportMovements'])->name('export.movements');
    Route::get('export/purchase-orders', [ExportController::class, 'exportPurchaseOrders'])->name('export.purchase-orders');

    // PDF exports
    Route::get('pdf/product/{product}', [ExportController::class, 'pdfProduct'])->name('pdf.product');
    Route::get('pdf/inventory', [ExportController::class, 'pdfInventory'])->name('pdf.inventory');
    Route::get('pdf/movements', [ExportController::class, 'pdfMovementReport'])->name('pdf.movements');
    Route::get('pdf/purchase-order/{purchaseOrder}', [ExportController::class, 'pdfPurchaseOrder'])->name('pdf.purchase-order');

    // Import
    Route::get('import/products', [ExportController::class, 'importProducts'])->name('import.products');
    Route::post('import/products', [ExportController::class, 'processProductsImport'])->name('import.products.process');

    // Settings
    Route::get('settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
});
