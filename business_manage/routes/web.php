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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StockAuditController;
use App\Http\Controllers\InternalExportController;
use App\Http\Controllers\PricingController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard', ['activeGroup' => 'dashboard']);
    })->name('dashboard');

    // --- NHÓM KHO HÀNG ---
    Route::prefix('inventory')->group(function () {
        Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
        Route::post('/pricing/update-all', [PricingController::class, 'updateAll'])->name('pricing.updateAll');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/san-pham/nhap-excel', [ProductController::class, 'import'])->name('products.import');
        Route::resource('imports', ImportController::class);
        Route::get('/lookup', [InventoryController::class, 'index'])->name('inventoryLookup.index');
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/san-pham/tai-file-mau', [ProductController::class, 'downloadTemplate'])->name('products.template');
        Route::resource('products', ProductController::class);
        Route::resource('internal_exports', InternalExportController::class);
    });

    // --- NHÓM BÁN HÀNG ---
    Route::prefix('sales')->group(function () {
        Route::resource('exports', ExportController::class);
        Route::resource('customers', CustomerController::class);

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
        Route::get('/barter', [ExportController::class, 'barter'])->name('exports.barter');
        Route::post('/barter', [ExportController::class, 'storeBarter'])->name('exports.storeBarter');
    });

    // --- NHÓM TÀI CHÍNH ---
    Route::prefix('finance')->group(function () {
        Route::resource('vouchers', VoucherController::class);
        Route::resource('accounts', AccountController::class);
        Route::resource('transfers', InternalTransferController::class)->names([
            'index'   => 'internal_transfers.index',
            'create'  => 'internal_transfers.create',
            'store'   => 'internal_transfers.store',
            'show'    => 'internal_transfers.show',
            'edit'    => 'internal_transfers.edit',
            'update'  => 'internal_transfers.update',
            'destroy' => 'internal_transfers.destroy',
        ]);
        Route::resource('providers', ProviderController::class);
        Route::get('/credit-logs', [CreditLogController::class, 'index'])->name('credit_logs.index');
    });

    Route::prefix('reports')->group(function () {
        Route::get('/overview', [ReportController::class, 'index'])->name('reportOverview');
        Route::get('/export-import', [ReportController::class, 'exportImport'])->name('reportExportImport');
        Route::get('/debt', [ReportController::class, 'debtReport'])->name('reportDebt');
        Route::get('/payments', [ReportController::class, 'paymentReport'])->name('reportPayments');
    });


    // Dùng resource để tự động có index, create, store, edit, update, destroy
    Route::resource('providers', ProviderController::class);

    // Đảm bảo có route show để xem lịch sử nợ
    Route::get('/providers/{provider}', [ProviderController::class, 'show'])->name('providers.show');

    Route::resource('audits', StockAuditController::class);

    // --- NHÓM BÁO CÁO ---
    Route::get('/reports', [ReportController::class, 'index'])->name('reportOverview');

    // --- THÔNG TIN CÁ NHÂN ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.users.edit');
});

require __DIR__ . '/auth.php';
