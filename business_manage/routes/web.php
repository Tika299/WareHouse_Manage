<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReturnFormController;
use App\Http\Controllers\ShippingUnitController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\InternalTransferController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\CreditLogController;
use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockAuditController;
use App\Http\Controllers\InternalExportController;
use App\Http\Controllers\PricingController;


// Auth routes (Xác thực email...)
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
});

// NHÓM ROUTE CHÍNH SAU KHI ĐĂNG NHẬP
Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD (SỬA TẠI ĐÂY: Trỏ vào Controller thay vì function)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- NHÓM KHO HÀNG ---
    Route::prefix('inventory')->group(function () {
        Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
        Route::post('/pricing/update-all', [PricingController::class, 'updateAll'])->name('pricing.updateAll');
        Route::get('/products/search-ajax', [ProductController::class, 'searchAjax'])->name('products.searchAjax');
        Route::get('/products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.template');
        Route::post('/products/import-excel', [ProductController::class, 'import'])->name('products.import');

        Route::resource('products', ProductController::class);
        Route::resource('imports', ImportController::class);
        Route::resource('internal_exports', InternalExportController::class);
        Route::get('/audits/search-products', [StockAuditController::class, 'searchProducts'])->name('audits.searchProducts');
        Route::resource('audits', StockAuditController::class);

        Route::get('/lookup', [InventoryController::class, 'index'])->name('inventoryLookup.index');
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    });

    // --- NHÓM BÁN HÀNG ---
    Route::prefix('sales')->group(function () {
        Route::resource('exports', ExportController::class);
        Route::get('/barter', [ExportController::class, 'barter'])->name('exports.barter');
        Route::post('/barter', [ExportController::class, 'storeBarter'])->name('exports.storeBarter');

        Route::get('customers/search-ajax', [CustomerController::class, 'searchAjax'])->name('customers.searchAjax');
        Route::resource('customers', CustomerController::class);

        Route::get('/returns/search-orders', [CustomerReturnController::class, 'searchOrdersAjax'])->name('customer_returns.searchOrdersAjax');
        Route::get('/returns/get-order-details/{id}', [CustomerReturnController::class, 'getOrderDetails']);
        Route::resource('customer_returns', CustomerReturnController::class);

        Route::resource('shipping_units', ShippingUnitController::class);
        Route::resource('returns', ReturnFormController::class)->names([
            'index'   => 'returnforms.index',
            'create'  => 'returnforms.create',
            'store'   => 'returnforms.store',
            'show'    => 'returnforms.show',
            'edit'    => 'returnforms.edit',
            'update'  => 'returnforms.update',
            'destroy' => 'returnforms.destroy',
        ]);
    });

    // --- NHÓM TÀI CHÍNH ---
    Route::prefix('finance')->group(function () {
        Route::get('/providers/search-ajax', [ProviderController::class, 'searchAjax'])->name('providers.searchAjax');
        Route::resource('vouchers', VoucherController::class);
        Route::resource('accounts', AccountController::class);
        Route::resource('transfers', InternalTransferController::class)->names([
            'index' => 'internal_transfers.index',
            'create' => 'internal_transfers.create',
            'store' => 'internal_transfers.store',
            'show' => 'internal_transfers.show'
        ]);
        Route::resource('providers', ProviderController::class);
        Route::get('/credit-logs', [CreditLogController::class, 'index'])->name('credit_logs.index');
    });

    // --- NHÓM BÁO CÁO ---
    Route::prefix('reports')->group(function () {
        Route::get('/overview', [ReportController::class, 'index'])->name('reportOverview');
        Route::get('/export-import', [ReportController::class, 'exportImport'])->name('reportExportImport');
        Route::get('/debt', [ReportController::class, 'debtReport'])->name('reportDebt');
        Route::get('/payments', [ReportController::class, 'paymentReport'])->name('reportPayments');
    });

    // --- CÁ NHÂN ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.users.edit');
});

require __DIR__ . '/auth.php';
