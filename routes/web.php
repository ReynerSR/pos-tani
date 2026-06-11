<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembershipRuleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // ---- ADMIN + PEMILIK ----
    Route::middleware(['role:pemilik,admin'])->group(function () {

        // Products
        Route::post('/products/category/update', [ProductController::class, 'updateCategory'])->name('products.category.update');
        Route::post('/products/unit/update', [ProductController::class, 'updateUnit'])->name('products.unit.update');
        Route::delete('/products/category/delete', [ProductController::class, 'destroyCategory'])->name('products.category.destroy');
        Route::delete('/products/unit/delete', [ProductController::class, 'destroyUnit'])->name('products.unit.destroy');
        Route::resource('products', ProductController::class)->except(['index', 'show', 'destroy']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Suppliers
        Route::resource('suppliers', SupplierController::class)->except(['show']);

        // Purchases / Restok
        Route::post('/purchases/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchases.approve');
        Route::resource('purchases', PurchaseController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'edit',
            'update',
            'destroy',
        ]);

        // Stock Opname
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/create', [StockController::class, 'create'])->name('stock.create');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        Route::get('/stock/{date}/{warehouse_id}', [StockController::class, 'show'])->name('stock.show');
        Route::post('/stock/{date}/{warehouse_id}/approve', [StockController::class, 'approve'])->name('stock.approve');

        // Gudang
        Route::resource('warehouses', WarehouseController::class)->except(['show']);

        // Transfer Antar Gudang
        Route::resource('stock-transfers', StockTransferController::class)->only([
            'index',
            'create',
            'store',
        ]);

        // Promotions
        Route::resource('promotions', PromotionController::class);

    });

    Route::middleware(['role:pemilik'])->group(function () {
        // Laporan Penjualan
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    });

    // ---- KASIR + ADMIN + PEMILIK ----
    Route::middleware(['role:pemilik,admin,kasir'])->group(function () {

        // POS
        Route::get('/kasir', [TransactionController::class, 'create'])->name('kasir.pos');
        Route::post('/kasir', [TransactionController::class, 'store'])->name('kasir.store');
        Route::get('/kasir/receipt/{transaction}', [TransactionController::class, 'receipt'])->name('kasir.receipt');
        Route::get('/kasir/history', [TransactionController::class, 'index'])->name('kasir.history');
        Route::get('/kasir/history/{transaction}/edit', [TransactionController::class, 'edit'])->name('kasir.edit');
        Route::put('/kasir/history/{transaction}', [TransactionController::class, 'update'])->name('kasir.update');
        Route::delete('/kasir/history/{transaction}', [TransactionController::class, 'destroy'])->name('kasir.destroy');
        Route::get('/kasir/history/{transaction}', [TransactionController::class, 'show'])->name('kasir.show');

        // API: price check
        Route::post('/api/price-check', [TransactionController::class, 'priceCheck'])->name('api.price-check');

        // Log Draft Action
        Route::post('/kasir/log-draft', [TransactionController::class, 'logDraftAction'])->name('kasir.log-draft');

        // Products (Read Only for Kasir)
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');

        // Product live search
        // Harus diletakkan sebelum route resource products agar tidak terbaca sebagai products/{product}
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

        // Products Show (Must be below search)
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

        // Customers
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    });

    // ---- PEMILIK ONLY ----
    Route::middleware(['role:pemilik'])->group(function () {

        // Users
        Route::resource('users', UserController::class);

        // Membership rules
        Route::get('/membership', [MembershipRuleController::class, 'index'])->name('membership.index');
        Route::put('/membership', [MembershipRuleController::class, 'update'])->name('membership.update');

        // Laporan Laba Kotor
        Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');

        // Activity logs
        Route::get('/reports/activity-logs', [ReportController::class, 'activityLogs'])->name('reports.activity');
    });
});