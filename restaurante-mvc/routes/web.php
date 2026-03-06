<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ExportController;

// Redirect root to login or dashboard
Route::get('/', fn() => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

// Dashboard - accessible by Admin and Manager
Route::get('/dashboard', function() {
    $user = auth()->user();

    if ($user->isAdmin() || $user->isManager()) {
        // Get dashboard stats
        $stats = [];

        // Products active count
        $stats['products'] = \App\Models\Product::when(!$user->isAdmin(), function($query) {
            return $query->active();
        })->count();

        // Branches count (admin only)
        $stats['branches'] = $user->isAdmin() ? \App\Models\Branch::count() : 1;

        // Low stock products
        $stats['lowStock'] = \App\Models\Product::whereHas('branches', function($q) {
            $q->whereRaw('branch_product.stock <= products.minimum_stock');
        })->count();

        // Today's movements
        $stats['movementsToday'] = \App\Models\InventoryMovement::whereDate('created_at', today())
            ->when(!$user->isAdmin(), function($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->count();

        // Recent movements
        $recentMovements = \App\Models\InventoryMovement::with(['product', 'branch', 'user'])
            ->when(!$user->isAdmin(), function($query) use ($user) {
                return $query->where('branch_id', $user->branch_id);
            })
            ->latest()
            ->limit(8)
            ->get();

        // Low stock items for panel
        $lowStockItems = \App\Models\Product::with('category')
            ->whereHas('branches', function($q) {
                $q->whereRaw('branch_product.stock <= products.minimum_stock');
            })
            ->get()
            ->map(function($product) {
                $totalStock = $product->branches->sum('pivot.stock');
                $product->total_stock = $totalStock;
                return $product;
            })
            ->sortBy('total_stock')
            ->take(5);

        return view('dashboard', compact('stats', 'recentMovements', 'lowStockItems'));
    }

    // Employee goes to inventory
    return redirect()->route('inventory.index');
})->name('dashboard')->middleware('auth');

// Authentication routes (login, register, etc.)
require __DIR__.'/auth.php';

// Protected routes with role-based access control
Route::middleware(['auth'])->group(function () {

    // ===== ADMIN: Full Access =====
    Route::middleware(['role:admin'])->group(function () {
        // Products - full CRUD
        Route::resource('products', ProductController::class);
        Route::get('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

        // Categories - full CRUD
        Route::resource('categories', CategoryController::class);
        Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

        // Branches - full CRUD
        Route::resource('branches', BranchController::class);
        Route::get('branches/{branch}/products', [BranchController::class, 'products'])->name('branches.products');
        Route::post('branches/{branch}/products', [BranchController::class, 'assignProduct'])->name('branches.products.assign');
        Route::delete('branches/{branch}/products/{product}', [BranchController::class, 'removeProduct'])->name('branches.products.remove');

        // Inventory - full access
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::match(['get', 'post'], 'inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('inventory/report', [InventoryController::class, 'report'])->name('inventory.report');

        // Users - full management
        Route::resource('users', UserController::class);
        // Reset password by admin
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->middleware(['auth', 'role:admin'])
            ->name('users.reset-password');
    });

    // ===== MANAGER: Limited Access (their branch only) =====
    Route::middleware(['role:manager'])->group(function () {
        // Categories - VIEW ONLY
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

        // Products - VIEW ALL, but can only edit stock/availability in their branch
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

        // Branches - VIEW their branch, EDIT contact info only
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/{branch}', [BranchController::class, 'show'])->name('branches.show');

        // Manager can only access their assigned branch
        Route::get('branches/{branch}/products', [BranchController::class, 'products'])->name('branches.products');
        Route::post('branches/{branch}/products', [BranchController::class, 'assignProduct'])->name('branches.products.assign');
        Route::patch('branches/{branch}/products/{product}/toggle-availability', [BranchController::class, 'toggleProductAvailability'])->name('branches.products.toggle-availability');
        Route::patch('branches/{branch}/products/{product}/update-stock', [BranchController::class, 'updateProductStock'])->name('branches.products.update-stock');
        Route::delete('branches/{branch}/products/{product}', [BranchController::class, 'removeProduct'])->name('branches.products.remove');

        // Inventory - in, out, adjust, transfer (only their branch)
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::match(['get', 'post'], 'inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('inventory/report', [InventoryController::class, 'report'])->name('inventory.report');

        // Users - VIEW employees of their branch only
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    // ===== EMPLOYEE: Very Limited Access =====
    Route::middleware(['role:employee'])->group(function () {
        // Categories - VIEW ONLY
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

        // Products - VIEW ONLY (active products in their branch)
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

        // Branches - VIEW ONLY (name and address)
        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/{branch}', [BranchController::class, 'show'])->name('branches.show');

        // Inventory - VIEW stock, REGISTER OUT movements only
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::match(['get', 'post'], 'inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');

        // No access to users module
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

// Purchase Orders (Admin and Manager)
Route::middleware(['auth'])->group(function () {
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
