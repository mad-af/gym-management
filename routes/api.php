<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MembershipPackageController;
use App\Http\Controllers\Api\MembershipPackageItemController;
use App\Http\Controllers\Api\MembershipTransactionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:web'])->group(function () {
    Route::post('media', [MediaController::class, 'store'])->name('api.media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('api.media.destroy');

    Route::get('permissions', [\App\Http\Controllers\Api\RoleController::class, 'permissions']);

    // Custom activation routes
    Route::put('users/{user}/activate', [\App\Http\Controllers\Api\UserController::class, 'activate']);
    Route::put('roles/{role}/activate', [\App\Http\Controllers\Api\RoleController::class, 'activate']);

    // Selection routes (Must be defined before apiResource to avoid conflict with show/{id})
    Route::get('roles/selection', [\App\Http\Controllers\Api\RoleController::class, 'selection']);
    Route::get('users/selection', [\App\Http\Controllers\Api\UserController::class, 'selection']);
    Route::get('customers/selection', [CustomerController::class, 'selection']);
    Route::get('membership-packages/selection', [MembershipPackageController::class, 'selection']);
    Route::get('products/selection', [ProductController::class, 'selection']);

    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'stats']);
        Route::get('category-budget', [DashboardController::class, 'categoryBudget']);
        Route::get('asset-condition', [DashboardController::class, 'assetCondition']);
        Route::get('proposals', [DashboardController::class, 'proposals']);
        Route::get('maintenances', [DashboardController::class, 'maintenances']);
    });

    Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
    Route::apiResource('roles', \App\Http\Controllers\Api\RoleController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('membership-packages', MembershipPackageController::class);
    Route::apiResource('membership-package-items', MembershipPackageItemController::class);
    Route::apiResource('membership-transactions', MembershipTransactionController::class);
    Route::apiResource('visits', VisitController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show', 'destroy']);

});
