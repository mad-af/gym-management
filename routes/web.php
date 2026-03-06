<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/q/{type}/{qr_id}', [\App\Http\Controllers\QrRedirectController::class, 'redirect'])->name('qr.redirect');

// General
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('General/Dashboard');
    })->name('dashboard');

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
