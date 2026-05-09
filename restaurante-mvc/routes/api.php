<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SupplierApiController;
use App\Http\Controllers\Api\PurchaseOrderApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group.
| API authentication via Laravel Sanctum (Bearer token)
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication with Rate Limiting (6 attempts per minute)
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::put('auth/password', [AuthController::class, 'updatePassword']);

    // Products - namespaced to avoid conflict with web routes
    Route::get('products', [ProductApiController::class, 'index'])->name('api.products.index');
    Route::get('products/{product}', [ProductApiController::class, 'show'])->name('api.products.show');
    Route::get('products/{product}/barcode', [ProductApiController::class, 'barcode'])->name('api.products.barcode');
    
    // Categories
    Route::get('categories', [CategoryApiController::class, 'index'])->name('api.categories.index');
    Route::get('categories/{category}', [CategoryApiController::class, 'show'])->name('api.categories.show');
    
    // Branches
    Route::get('branches', [BranchApiController::class, 'index'])->name('api.branches.index');
    Route::get('branches/{branch}', [BranchApiController::class, 'show'])->name('api.branches.show');
    Route::get('branches/{branch}/inventory', [BranchApiController::class, 'inventory'])->name('api.branches.inventory');

    // Inventory - Basic access
    Route::get('inventory/movements', [InventoryApiController::class, 'movements'])->name('api.inventory.movements');
    Route::get('inventory/low-stock', [InventoryApiController::class, 'lowStock'])->name('api.inventory.low-stock');
    Route::post('inventory/adjust', [InventoryApiController::class, 'adjust'])->name('api.inventory.adjust');

    // ===== MANAGER+ ROUTES =====
    Route::middleware('role:manager')->group(function () {
        Route::apiResource('purchase-orders', PurchaseOrderApiController::class)->names('api.purchase-orders');
        Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderApiController::class, 'approve'])->name('api.purchase-orders.approve');
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderApiController::class, 'receive'])->name('api.purchase-orders.receive');
        Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderApiController::class, 'cancel'])->name('api.purchase-orders.cancel');
    });

    // ===== ADMIN ONLY ROUTES =====
    Route::middleware('role:admin')->group(function () {
        // Full CRUD for core resources (Write actions)
        Route::post('products', [ProductApiController::class, 'store'])->name('api.products.store');
        Route::put('products/{product}', [ProductApiController::class, 'update'])->name('api.products.update');
        Route::delete('products/{product}', [ProductApiController::class, 'destroy'])->name('api.products.destroy');

        Route::post('categories', [CategoryApiController::class, 'store'])->name('api.categories.store');
        Route::put('categories/{category}', [CategoryApiController::class, 'update'])->name('api.categories.update');
        Route::delete('categories/{category}', [CategoryApiController::class, 'destroy'])->name('api.categories.destroy');

        Route::post('branches', [BranchApiController::class, 'store'])->name('api.branches.store');
        Route::put('branches/{branch}', [BranchApiController::class, 'update'])->name('api.branches.update');
        Route::delete('branches/{branch}', [BranchApiController::class, 'destroy'])->name('api.branches.destroy');

        // Suppliers
        Route::apiResource('suppliers', SupplierApiController::class)->names('api.suppliers');

        // User Management (Admin only)
        Route::post('auth/register', [AuthController::class, 'register'])->name('api.auth.register');
    });
});

// API Route for generating API tokens (web interface)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('tokens/create', function (Request $request) {
        $token = $request->user()->createToken($request->token_name ?? 'api-token');
        return ['token' => $token->plainTextToken];
    })->name('api.tokens.create');
});
