<?php

use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PublicAssetController;
use App\Http\Controllers\PublicRoomInventoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/q/{type}/{qr_id}', [\App\Http\Controllers\QrRedirectController::class, 'redirect'])->name('qr.redirect');

Route::get('/public/asset/{id}', [PublicAssetController::class, 'show'])->name('public.asset.show');
Route::get('/public/room-inventory/{room}', [PublicRoomInventoryController::class, 'show'])->name('public.room-inventory.show');

// General
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('General/Dashboard');
    })->name('dashboard');

    Route::get('asset-management/assets', function () {
        return Inertia::render('General/Asset/AssetData/Index');
    })->name('asset.management.assets');

    Route::get('asset-management/assets/{asset}', function (Asset $asset) {
        return Inertia::render('General/Asset/AssetData/Detail', [
            'assetId' => $asset->id,
        ]);
    })->name('asset.management.assets.show');

    Route::get('asset-management/transfers', function () {
        return Inertia::render('General/Asset/AssetTransfer/Index');
    })->name('asset.management.transfers');

    Route::get('asset-management/transfers/{transfer}', function (\App\Models\TransferRequest $transfer) {
        return Inertia::render('General/Asset/AssetTransfer/Detail', [
            'transferId' => $transfer->id,
        ]);
    })->name('asset.management.transfers.show');

    Route::get('asset-management/disposal', function () {
        return Inertia::render('General/Asset/AssetDisposal/Index');
    })->name('asset.management.disposal');

    Route::get('asset-management/disposals/{disposal}', function (\App\Models\DisposalDocument $disposal) {
        return Inertia::render('General/Asset/AssetDisposal/Detail', [
            'disposalId' => $disposal->id,
        ]);
    })->name('asset.management.disposals.show');

    Route::get('asset-management/history', function () {
        return Inertia::render('General/Asset/AssetHistory');
    })->name('asset.management.history');

    Route::get('room-inventory', function () {
        return Inertia::render('General/RoomInventory/Index');
    })->name('room.inventory');

    Route::get('room-inventory/{room}', function (\App\Models\Room $room) {
        return Inertia::render('General/RoomInventory/Detail', [
            'roomId' => $room->id,
        ]);
    })->name('room.inventory.show');

    Route::get('maintenance', function () {
        return Inertia::render('General/Maintenance/Index');
    })->name('maintenance');

    Route::get('maintenance/{maintenance}', function (AssetMaintenance $maintenance) {
        return Inertia::render('General/Maintenance/Detail', [
            'maintenanceId' => $maintenance->id,
        ]);
    })->name('maintenance.show');

    // Whatsapp Config
    Route::get('whatsapp-config', [\App\Http\Controllers\WhatsappConfigController::class, 'index'])->name('whatsapp.config.index');

    Route::get('proposals', function () {
        return Inertia::render('General/Proposals/Index');
    })->name('proposals');

    Route::get('proposals/{proposal}', function (\App\Models\AssetProposal $proposal) {
        return Inertia::render('General/Proposals/Detail', [
            'proposalId' => $proposal->id,
        ]);
    })->name('proposals.show');

    // Gym Management
    Route::get('gym/customers', function () {
        return Inertia::render('Gym/Customers/Index');
    })->name('gym.customers');
    Route::get('gym/customers/{customer}', function (Customer $customer) {
        return Inertia::render('Gym/Customers/Detail', [
            'customerId' => $customer->id,
        ]);
    })->name('gym.customers.show');

    Route::get('gym/membership/packages', function () {
        return Inertia::render('Gym/Membership/Packages');
    })->name('gym.membership.packages');
    Route::get('gym/membership/transactions', function () {
        return Inertia::render('Gym/Membership/Transactions');
    })->name('gym.membership.transactions');
    Route::get('gym/membership/items', function () {
        return Inertia::render('Gym/Membership/Items');
    })->name('gym.membership.items');

    Route::get('gym/visits', function () {
        return Inertia::render('Gym/Visits/Index');
    })->name('gym.visits');

    Route::get('gym/inventory/products', function () {
        return Inertia::render('Gym/Inventory/Products');
    })->name('gym.inventory.products');
    Route::get('gym/inventory/stock-movements', function () {
        return Inertia::render('Gym/Inventory/StockMovements');
    })->name('gym.inventory.stock-movements');

    Route::get('gym/sales', function () {
        return Inertia::render('Gym/Sales/Index');
    })->name('gym.sales');
    Route::get('gym/sales/{sale}', function (Sale $sale) {
        return Inertia::render('Gym/Sales/Detail', [
            'saleId' => $sale->id,
        ]);
    })->name('gym.sales.show');

    Route::get('gym/reports/daily-revenue', function () {
        return Inertia::render('Gym/Reports/DailyRevenue');
    })->name('gym.reports.daily-revenue');
    Route::get('gym/reports/visits', function () {
        return Inertia::render('Gym/Reports/VisitReport');
    })->name('gym.reports.visits');
    Route::get('gym/reports/memberships', function () {
        return Inertia::render('Gym/Reports/MembershipReport');
    })->name('gym.reports.memberships');
    Route::get('gym/reports/product-sales', function () {
        return Inertia::render('Gym/Reports/ProductSalesReport');
    })->name('gym.reports.product-sales');
});

// Master
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('opd', [OpdController::class, 'index'])->name('opd.index');
    Route::get('rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('asset-categories', [AssetCategoryController::class, 'index'])->name('asset-categories.index');
    Route::get('funding-sources', function () {
        return Inertia::render('Master/FundingSource');
    })->name('funding-sources.index');
});

// Authentication Routes (Fortify handles these, but we can define custom ones)
Route::middleware(['guest'])->group(function () {
    Route::get('login', function () {
        return Inertia::render('Auth/Signin');
    })->name('login');

    Route::get('forgot-password', function () {
        return Inertia::render('Auth/ForgotPassword');
    })->name('password.request');
});

require __DIR__.'/settings.php';
