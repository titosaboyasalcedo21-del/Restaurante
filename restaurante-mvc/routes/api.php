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
    // Authentication
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::put('auth/password', [AuthController::class, 'updatePassword']);

    // Products - namespaced to avoid conflict with web routes
    Route::apiResource('products', ProductApiController::class)->names('api.products');
    Route::get('products/{product}/barcode', [ProductApiController::class, 'barcode'])->name('api.products.barcode');

    // Categories - namespaced to avoid conflict with web routes
    Route::apiResource('categories', CategoryApiController::class)->names('api.categories');

    // Branches - namespaced to avoid conflict with web routes
    Route::apiResource('branches', BranchApiController::class)->names('api.branches');
    Route::get('branches/{branch}/inventory', [BranchApiController::class, 'inventory'])->name('api.branches.inventory');

    // Inventory - namespaced to avoid conflict with web routes
    Route::get('inventory/movements', [InventoryApiController::class, 'movements'])->name('api.inventory.movements');
    Route::post('inventory/adjust', [InventoryApiController::class, 'adjust'])->name('api.inventory.adjust');
    Route::get('inventory/low-stock', [InventoryApiController::class, 'lowStock'])->name('api.inventory.low-stock');

    // Suppliers (admin only) - namespaced to avoid conflict with web routes
    Route::apiResource('suppliers', SupplierApiController::class)->names('api.suppliers')->middleware('role.api:admin');

    // Purchase Orders - namespaced to avoid conflict with web routes
    Route::apiResource('purchase-orders', PurchaseOrderApiController::class)->names('api.purchase-orders');
    Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderApiController::class, 'approve'])->name('api.purchase-orders.approve');
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderApiController::class, 'receive'])->name('api.purchase-orders.receive');
    Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderApiController::class, 'cancel'])->name('api.purchase-orders.cancel');
});

// API Route for generating API tokens (web interface)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('tokens/create', function (Request $request) {
        $token = $request->user()->createToken($request->token_name ?? 'api-token');
        return ['token' => $token->plainTextToken];
    })->name('api.tokens.create');
});
