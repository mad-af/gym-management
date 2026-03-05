<?php

use App\Http\Controllers\Api\AssetAdditionalInfoController;
use App\Http\Controllers\Api\AssetEmployeeController;
use App\Http\Controllers\Api\AssetHistoryController;
use App\Http\Controllers\Api\AssetProposalController;
use App\Http\Controllers\Api\CurrentOpdController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DisposalController;
use App\Http\Controllers\Api\FundingSourceController;
use App\Http\Controllers\Api\MembershipPackageController;
use App\Http\Controllers\Api\MembershipPackageItemController;
use App\Http\Controllers\Api\MembershipTransactionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PublicAssetController;
use App\Http\Controllers\PublicRoomInventoryController;
use Illuminate\Support\Facades\Route;

Route::get('public/room-inventory/{room}', [PublicRoomInventoryController::class, 'data'])->name('public.room-inventory.data');
Route::get('public/room-inventory/{room}/assets', [PublicRoomInventoryController::class, 'assets'])->name('public.room-inventory.assets');
Route::get('public/room-inventory/{room}/pdf', [PublicRoomInventoryController::class, 'pdf'])->name('public.room-inventory.pdf');
Route::get('public/assets/{id}', [PublicAssetController::class, 'detail'])->name('public.asset.detail');

Route::middleware(['auth:web'])->group(function () {
    Route::post('media', [MediaController::class, 'store'])->name('api.media.store');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('api.media.destroy');

    Route::get('permissions', [\App\Http\Controllers\Api\RoleController::class, 'permissions']);

    // Custom activation routes
    Route::put('users/{user}/activate', [\App\Http\Controllers\Api\UserController::class, 'activate']);
    Route::put('rooms/{room}/activate', [\App\Http\Controllers\Api\RoomController::class, 'activate']);
    Route::put('roles/{role}/activate', [\App\Http\Controllers\Api\RoleController::class, 'activate']);
    Route::put('opds/{opd}/activate', [\App\Http\Controllers\Api\OpdController::class, 'activate']);
    Route::put('employees/{employee}/activate', [\App\Http\Controllers\Api\EmployeeController::class, 'activate']);
    Route::put('funding-sources/{funding_source}/activate', [FundingSourceController::class, 'activate']);

    // Selection routes (Must be defined before apiResource to avoid conflict with show/{id})
    Route::get('opds/selection', [\App\Http\Controllers\Api\OpdController::class, 'selection']);
    Route::get('employees/selection', [\App\Http\Controllers\Api\EmployeeController::class, 'selection']);
    Route::get('roles/selection', [\App\Http\Controllers\Api\RoleController::class, 'selection']);
    Route::get('users/selection', [\App\Http\Controllers\Api\UserController::class, 'selection']);
    Route::get('rooms/selection', [\App\Http\Controllers\Api\RoomController::class, 'selection']);
    Route::get('assets/selection', [App\Http\Controllers\Api\AssetController::class, 'selection']);
    Route::get('asset-categories/selection', [App\Http\Controllers\Api\AssetCategoryController::class, 'selection']);
    Route::get('funding-sources/selection', [FundingSourceController::class, 'selection']);
    Route::get('customers/selection', [CustomerController::class, 'selection']);
    Route::get('membership-packages/selection', [MembershipPackageController::class, 'selection']);
    Route::get('products/selection', [ProductController::class, 'selection']);

    Route::post('current-opd', [CurrentOpdController::class, 'update']);

    Route::get('asset-histories', [AssetHistoryController::class, 'index']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/category-budget', [DashboardController::class, 'categoryBudget']);
    Route::get('dashboard/asset-condition', [DashboardController::class, 'assetCondition']);
    Route::get('dashboard/proposals', [DashboardController::class, 'proposals']);
    Route::get('dashboard/maintenances', [DashboardController::class, 'maintenances']);
    Route::get('assets/stats', [App\Http\Controllers\Api\AssetController::class, 'stats']);
    Route::post('assets/bulk-label-pdf', [App\Http\Controllers\Api\AssetController::class, 'bulkLabelPdf']);
    Route::get('assets/{asset}/label-pdf', [App\Http\Controllers\Api\AssetController::class, 'labelPdf']);
    Route::get('transfers', [TransferController::class, 'index']);
    Route::get('transfers/stats', [TransferController::class, 'stats']);
    Route::post('transfers', [TransferController::class, 'store']);
    Route::get('transfers/{transfer}', [TransferController::class, 'show']);
    Route::post('transfers/{transfer}/approve', [TransferController::class, 'approve']);
    Route::post('transfers/{transfer}/reject', [TransferController::class, 'reject']);
    Route::post('transfers/{transfer}/cancel', [TransferController::class, 'cancel']);

    Route::get('disposals', [DisposalController::class, 'index']);
    Route::get('disposals/stats', [DisposalController::class, 'stats']);
    Route::post('disposals', [DisposalController::class, 'store']);
    Route::get('disposals/{disposal}', [DisposalController::class, 'show']);

    Route::get('room-inventory/rooms', [\App\Http\Controllers\Api\RoomInventoryController::class, 'index']);
    Route::get('room-inventory/rooms/{room}', [\App\Http\Controllers\Api\RoomInventoryController::class, 'show']);
    Route::get('room-inventory/rooms/{room}/assets', [\App\Http\Controllers\Api\RoomInventoryController::class, 'assets']);
    Route::get('room-inventory/rooms/{room}/pdf', [\App\Http\Controllers\Api\RoomInventoryController::class, 'pdf']);

    Route::get('asset-maintenances', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'index']);
    Route::get('asset-maintenances/stats', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'stats']);
    Route::get('asset-maintenances/{maintenance}', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'show']);
    Route::post('asset-maintenances', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'store']);
    Route::put('asset-maintenances/{maintenance}', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'update']);
    Route::post('asset-maintenances/{maintenance}/start', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'start']);
    Route::post('asset-maintenances/{maintenance}/complete', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'complete']);
    Route::post('asset-maintenances/{maintenance}/cancel', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'cancel']);
    Route::get('asset-maintenances/{maintenance}/logs', [\App\Http\Controllers\Api\AssetMaintenanceController::class, 'logs']);

    Route::get('asset-proposals', [AssetProposalController::class, 'index']);
    Route::get('asset-proposals/stats', [AssetProposalController::class, 'stats']);
    Route::post('asset-proposals', [AssetProposalController::class, 'store']);
    Route::get('asset-proposals/{proposal}', [AssetProposalController::class, 'show']);
    Route::put('asset-proposals/{proposal}', [AssetProposalController::class, 'update']);
    Route::delete('asset-proposals/{proposal}', [AssetProposalController::class, 'destroy']);
    Route::post('asset-proposals/{proposal}/approve', [AssetProposalController::class, 'approve']);
    Route::post('asset-proposals/{proposal}/reject', [AssetProposalController::class, 'reject']);
    Route::post('asset-proposals/{proposal}/complete', [AssetProposalController::class, 'complete']);

    Route::get('assets/{asset}/additional-info', [AssetAdditionalInfoController::class, 'show']);
    Route::post('assets/{asset}/additional-info', [AssetAdditionalInfoController::class, 'store']);
    Route::put('asset-additional-infos/{assetAdditionalInfo}', [AssetAdditionalInfoController::class, 'update']);

    Route::get('assets/{asset}/employee', [AssetEmployeeController::class, 'show']);
    Route::put('assets/{asset}/employee', [AssetEmployeeController::class, 'update']);
    Route::put('assets/{asset}/status', [App\Http\Controllers\Api\AssetController::class, 'updateStatus']);
    Route::put('assets/{asset}/condition', [App\Http\Controllers\Api\AssetController::class, 'updateCondition']);

    Route::get('whatsapp-config/', [\App\Http\Controllers\Api\WhatsappConfigController::class, 'index'])->name('data');
    Route::post('whatsapp-config/', [\App\Http\Controllers\Api\WhatsappConfigController::class, 'update'])->name('update');
    Route::delete('whatsapp-config/', [\App\Http\Controllers\Api\WhatsappConfigController::class, 'destroy'])->name('destroy');
    Route::post('whatsapp-config/test', [\App\Http\Controllers\Api\WhatsappConfigController::class, 'test'])->name('test');
    Route::get('whatsapp-config/qr', [\App\Http\Controllers\Api\WhatsappConfigController::class, 'getQr'])->name('qr');
    Route::get('whatsapp-config/check', [\App\Http\Controllers\Api\WhatsappConfigController::class, 'check'])->name('check');

    Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
    Route::apiResource('rooms', \App\Http\Controllers\Api\RoomController::class);
    Route::apiResource('roles', \App\Http\Controllers\Api\RoleController::class);
    Route::apiResource('opds', \App\Http\Controllers\Api\OpdController::class);
    Route::apiResource('employees', \App\Http\Controllers\Api\EmployeeController::class);
    Route::apiResource('assets', App\Http\Controllers\Api\AssetController::class);
    Route::apiResource('asset-categories', App\Http\Controllers\Api\AssetCategoryController::class);
    Route::apiResource('funding-sources', FundingSourceController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('membership-packages', MembershipPackageController::class);
    Route::apiResource('membership-package-items', MembershipPackageItemController::class);
    Route::apiResource('membership-transactions', MembershipTransactionController::class);
    Route::apiResource('visits', VisitController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show', 'destroy']);

});
