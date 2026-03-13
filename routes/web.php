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

    Route::get('operations', function () {
        return Inertia::render('General/Operations');
    })->name('general.operations');

    // Gym Management
    Route::get('customers', function () {
        return Inertia::render('Master/Customers');
    })->name('gym.customers');

    Route::get('customers/{customer}', function (Customer $customer) {
        return Inertia::render('Gym/Customers/Detail', [
            'customerId' => $customer->id,
        ]);
    })->name('gym.customers.show');

    Route::get('membership/packages', function () {
        return Inertia::render('Master/MembershipPackages');
    })->name('gym.membership.packages');

    Route::get('membership/transactions', function () {
        return Inertia::render('Transaction/MembershipTransactions');
    })->name('gym.membership.transactions');

    Route::get('visits', function () {
        return Inertia::render('Transaction/Visits');
    })->name('gym.visits');

    Route::get('inventory/products', function () {
        return Inertia::render('Master/Products');
    })->name('gym.inventory.products');

    Route::get('inventory/stock-movements', function () {
        return Inertia::render('Transaction/StockMovements');
    })->name('gym.inventory.stock-movements');

    Route::get('sales', function () {
        return Inertia::render('Transaction/Sales');
    })->name('gym.sales');

    Route::get('sales/{sale}', function (Sale $sale) {
        return Inertia::render('Transaction/Sales/Detail', [
            'saleId' => $sale->id,
        ]);
    })->name('gym.sales.show');

    Route::get('reports/daily-revenue', function () {
        return Inertia::render('Gym/Reports/DailyRevenue');
    })->name('gym.reports.daily-revenue');

    Route::get('reports/visits', function () {
        return Inertia::render('Gym/Reports/VisitReport');
    })->name('gym.reports.visits');

    Route::get('reports/memberships', function () {
        return Inertia::render('Gym/Reports/MembershipReport');
    })->name('gym.reports.memberships');

    Route::get('reports/product-sales', function () {
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
