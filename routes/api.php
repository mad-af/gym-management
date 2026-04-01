<?php

use App\Enums\Permission;
use App\Http\Controllers\Api\AppSettingController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MembershipCardController;
use App\Http\Controllers\Api\MembershipPackageController;
use App\Http\Controllers\Api\MembershipPackageItemController;
use App\Http\Controllers\Api\MembershipTransactionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\WhatsappConfigController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:web'])->group(function () {
    Route::post('media', [MediaController::class, 'store'])->name('api.media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('api.media.destroy');

    Route::get('permissions', [\App\Http\Controllers\Api\RoleController::class, 'permissions']);
    Route::get('app-settings', [AppSettingController::class, 'index'])
        ->middleware('permission:view_app_settings')
        ->name('api.app-settings.index');
    Route::put('app-settings', [AppSettingController::class, 'update'])
        ->middleware('permission:manage_app_settings')
        ->name('api.app-settings.update');
    Route::get('whatsapp-config', [WhatsappConfigController::class, 'index'])->name('api.whatsapp-config.index');
    Route::post('whatsapp-config', [WhatsappConfigController::class, 'update'])->name('api.whatsapp-config.update');
    Route::delete('whatsapp-config', [WhatsappConfigController::class, 'destroy'])->name('api.whatsapp-config.destroy');
    Route::post('whatsapp-config/test', [WhatsappConfigController::class, 'test'])->name('api.whatsapp-config.test');
    Route::get('whatsapp-config/qr', [WhatsappConfigController::class, 'getQr'])->name('api.whatsapp-config.qr');
    Route::get('whatsapp-config/check', [WhatsappConfigController::class, 'check'])->name('api.whatsapp-config.check');
    Route::get('membership-cards/print', [MembershipCardController::class, 'print'])->name('api.membership-cards.print');
    Route::post('membership-cards/send-whatsapp', [MembershipCardController::class, 'sendWhatsapp'])->name('api.membership-cards.send-whatsapp');

    // Custom activation routes
    Route::put('users/{user}/activate', [\App\Http\Controllers\Api\UserController::class, 'activate']);
    Route::put('roles/{role}/activate', [\App\Http\Controllers\Api\RoleController::class, 'activate']);
    Route::put('membership-packages/{membership_package}/activate', [MembershipPackageController::class, 'activate']);
    Route::put('products/{product}/activate', [ProductController::class, 'activate']);

    // Selection routes (Must be defined before apiResource to avoid conflict with show/{id})
    Route::get('roles/selection', [\App\Http\Controllers\Api\RoleController::class, 'selection']);
    Route::get('users/selection', [\App\Http\Controllers\Api\UserController::class, 'selection']);
    Route::get('customers/selection', [CustomerController::class, 'selection']);
    Route::get('customers/stats', [CustomerController::class, 'stats']);
    Route::get('membership-packages/selection', [MembershipPackageController::class, 'selection']);
    Route::get('membership-packages/stats', [MembershipPackageController::class, 'stats']);
    Route::get('membership-transactions/stats', [MembershipTransactionController::class, 'stats']);
    Route::get('membership-transactions/export', [MembershipTransactionController::class, 'export'])->name('api.membership-transactions.export');
    Route::get('products/selection', [ProductController::class, 'selection']);
    Route::get('products/stats', [ProductController::class, 'stats']);
    Route::get('sales/stats', [SaleController::class, 'stats']);
    Route::get('sales/export', [SaleController::class, 'export'])->name('api.sales.export');
    Route::get('sales/export/pdf', [SaleController::class, 'exportPdf'])->name('api.sales.export-pdf');
    Route::get('stock-movements/stats', [StockMovementController::class, 'stats']);
    Route::get('stock-movements/export', [StockMovementController::class, 'export'])->name('api.stock-movements.export');
    Route::get('stock-movements/export/pdf', [StockMovementController::class, 'exportPdf'])->name('api.stock-movements.export-pdf');
    Route::get('visits/stats', [VisitController::class, 'stats']);
    Route::get('visits/export', [VisitController::class, 'export'])->name('api.visits.export');
    Route::get('visits/export/pdf', [VisitController::class, 'exportPdf'])->name('api.visits.export-pdf');
    Route::get('membership-transactions/export/pdf', [MembershipTransactionController::class, 'exportPdf'])->name('api.membership-transactions.export-pdf');

    Route::prefix('dashboard')->group(function () {
        Route::get('stats', [DashboardController::class, 'stats']);
    });
    Route::get('operations/stats-today', [DashboardController::class, 'operationsToday']);

    Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
    Route::apiResource('roles', \App\Http\Controllers\Api\RoleController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('membership-packages', MembershipPackageController::class);
    Route::apiResource('membership-package-items', MembershipPackageItemController::class);
    Route::apiResource('membership-transactions', MembershipTransactionController::class);
    Route::post('membership-transactions/{membershipTransaction}/cancel', [MembershipTransactionController::class, 'cancel'])
        ->middleware('permission:'.Permission::DELETE_MEMBERSHIP_TRANSACTIONS->value);
    Route::apiResource('visits', VisitController::class);
    Route::post('visits/{visit}/cancel', [VisitController::class, 'cancel'])
        ->middleware('permission:'.Permission::DELETE_VISITS->value);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])
        ->middleware('permission:'.Permission::DELETE_SALES->value);

});
