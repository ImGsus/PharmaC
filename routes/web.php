<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\PrescriptionController;
use App\Http\Controllers\Admin\TemperatureController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\InventoryCheckController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware(['auth', 'audit'])->group(function(){
    Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
    Route::get('dashboard/resources',[DashboardController::class,'resources'])->name('dashboard.resources');
    Route::get('home', function(){
        return redirect()->route('dashboard');
    });
    Route::get('',[DashboardController::class,'Index']);
    Route::get('notifications',[NotificationController::class,'index'])->name('notifications.index');
    Route::get('notifications/modal-content',[NotificationController::class,'modalContent'])->name('notifications.modal-content');
    Route::get('notification',[NotificationController::class,'markAsRead'])->name('mark-as-read');
    Route::get('notification-read',[NotificationController::class,'read'])->name('read');
    Route::get('notification/read/{id}',[NotificationController::class,'readOne'])->name('notification.read-one');
    Route::post('notification/mark-single/{id}',[NotificationController::class,'markSingleAsRead'])->name('notification.mark-single');
    Route::delete('notification/{id}',[NotificationController::class,'destroyAjax'])->name('notification.destroy-ajax');
    Route::post('notifications/clear-read',[NotificationController::class,'destroyAllRead'])->name('notifications.clear-read');
    Route::get('profile',[UserController::class,'profile'])->name('profile');
    Route::post('profile/{user}',[UserController::class,'updateProfile'])->name('profile.update');
    Route::put('profile/update-password/{user}',[UserController::class,'updatePassword'])->name('update-password');
    Route::post('profile/two-factor/send',[UserController::class,'sendTwoFactorCode'])->name('two-factor.send');
    Route::post('profile/two-factor/enable',[UserController::class,'enableTwoFactor'])->name('two-factor.enable');
    Route::post('profile/two-factor/disable',[UserController::class,'disableTwoFactor'])->name('two-factor.disable');
    Route::post('logout',[LogoutController::class,'index'])->name('logout');
    Route::get('verify-account',[RegisterController::class,'choice'])->name('verification.choice');
    Route::post('verify-account/send',[RegisterController::class,'sendCode'])->name('verification.send');
    Route::get('verify-account/code',[RegisterController::class,'form'])->name('verification.form');
    Route::post('verify-account/code',[RegisterController::class,'verify'])->name('verification.verify');

    Route::resource('users',UserController::class);
    Route::resource('permissions',PermissionController::class)->only(['index','store','destroy']);
    Route::put('permission',[PermissionController::class,'update'])->name('permissions.update');
    Route::resource('roles',RoleController::class);
    Route::resource('suppliers',SupplierController::class);
    Route::resource('categories',CategoryController::class)->only(['index','store','destroy']);
    Route::put('categories',[CategoryController::class,'update'])->name('categories.update');
    Route::delete('purchases/bulk-delete',[PurchaseController::class,'bulkDestroy'])->name('purchases.bulk-destroy');
    Route::delete('purchases/supplier/{supplier}/delete',[PurchaseController::class,'destroySupplier'])->name('purchases.supplier-destroy');
    Route::resource('purchases',PurchaseController::class)->except('show');
    Route::get('purchases/reports',[PurchaseController::class,'reports'])->name('purchases.report');
    Route::post('purchases/reports',[PurchaseController::class,'generateReport']);
    Route::resource('products',ProductController::class)->except('show');
    Route::post('products/{product}/status',[ProductController::class,'toggleStatus'])->name('products.toggle-status');
    Route::get('products/outstock',[ProductController::class,'outstock'])->name('outstock');
    Route::get('products/expired',[ProductController::class,'expired'])->name('expired');
    Route::resource('sales',SaleController::class)->except('show');
    Route::get('sales/reports',[SaleController::class,'reports'])->name('sales.report');
    Route::post('sales/reports',[SaleController::class,'generateReport']);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('reports/{report}/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');

    Route::get('prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::post('prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::post('prescriptions/analyze', [PrescriptionController::class, 'analyze'])->name('prescriptions.analyze');
    Route::get('prescriptions/gemini-credentials', [PrescriptionController::class, 'geminiCredentials'])->name('prescriptions.gemini-credentials.index');
    Route::post('prescriptions/gemini-credentials', [PrescriptionController::class, 'storeGeminiCredential'])->name('prescriptions.gemini-credentials.store');
    Route::delete('prescriptions/gemini-credentials/{credential}', [PrescriptionController::class, 'deleteGeminiCredential'])->name('prescriptions.gemini-credentials.destroy');
    Route::get('prescriptions/groq-credentials', [PrescriptionController::class, 'groqCredentials'])->name('prescriptions.groq-credentials.index');
    Route::post('prescriptions/groq-credentials', [PrescriptionController::class, 'storeGroqCredential'])->name('prescriptions.groq-credentials.store');
    Route::delete('prescriptions/groq-credentials/{credential}', [PrescriptionController::class, 'deleteGroqCredential'])->name('prescriptions.groq-credentials.destroy');
    Route::post('prescriptions/match-catalog', [PrescriptionController::class, 'matchCatalog'])->name('prescriptions.match-catalog');
    Route::patch('prescriptions/{prescription}/status', [PrescriptionController::class, 'updateStatus'])->name('prescriptions.status');
    Route::delete('prescriptions/{prescription}', [PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');
    Route::get('temperature', [TemperatureController::class, 'index'])->name('temperature.index');
    Route::post('temperature', [TemperatureController::class, 'store'])->name('temperature.store');
    Route::put('temperature/{reading}', [TemperatureController::class, 'update'])->name('temperature.update');
    Route::delete('temperature/{reading}', [TemperatureController::class, 'destroy'])->name('temperature.destroy');
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('inventory-check/modal-content', [InventoryCheckController::class, 'modalContent'])
        ->middleware('can:view-products')
        ->name('inventory-check.modal-content');

    // POS / Cashier (PharMac-styled) — replaces the old Webby barcode scanner
    Route::get('pos/orders',           [PosController::class, 'index'])->name('pos.orders');
    Route::get('pos/orders/history',   [PosController::class, 'history'])->name('pos.orders.history');
    Route::get('pos/orders/orders',    [PosController::class, 'ordersList'])->name('pos.orders.list');
    Route::get('pos/orders/report',    [PosController::class, 'report'])->name('pos.orders.report');
    Route::post('pos/orders/scan',     [PosController::class, 'scan'])->name('pos.orders.scan');
    Route::get('pos/orders/{sale}/receipt', [PosController::class, 'receipt'])->name('pos.orders.receipt');

    // POS sessions (cashier shift timer)
    Route::post('pos/session/start',   [PosController::class, 'sessionStart'])->name('pos.session.start');
    Route::post('pos/session/end',     [PosController::class, 'sessionEnd'])->name('pos.session.end');
    Route::get('pos/session/history',  [PosController::class, 'sessionHistory'])->name('pos.session.history');

    Route::get('backup', [BackupController::class,'index'])->name('backup.index');
    Route::get('archive', [BackupController::class,'archiveIndex'])->name('archive.index');
    Route::put('backup/create', [BackupController::class,'create'])->name('backup.store');
    Route::post('backup/import', [BackupController::class,'importBundle'])->name('backup.import');
    Route::get('backup/download', [BackupController::class,'download'])->name('backup.download');
    Route::delete('backup/delete', [BackupController::class,'destroy'])->name('backup.destroy');
    Route::post('backup/archive/{archive}/recover', [BackupController::class,'recoverArchive'])->name('backup.archive.recover');
    Route::delete('backup/archive/{archive}', [BackupController::class,'destroyArchive'])->name('backup.archive.destroy');
    Route::post('backup/archive/generic/{archive}/recover', [BackupController::class,'restoreGenericArchive'])->name('backup.archive.generic.recover');
    Route::delete('backup/archive/generic/{archive}', [BackupController::class,'destroyGenericArchive'])->name('backup.archive.generic.destroy');

    Route::get('settings',[SettingController::class,'index'])->name('settings');

    Route::get('storage/system/{folder}/{filename}', function ($folder, $filename) {
        abort_unless(in_array($folder, ['purchases', 'profiles', 'prescriptions'], true), 404);
        abort_if($filename === '' || basename($filename) !== $filename, 404);
        $path = storage_path('app/system/'.$folder.'/'.$filename);
        if (!is_file($path)) {
            $legacyPath = $folder === 'purchases'
                ? storage_path('app/purchases/'.$filename)
                : ($folder === 'profiles' ? public_path('storage/users/'.$filename) : public_path('storage/prescriptions/'.$filename));
            $path = is_file($legacyPath) ? $legacyPath : null;
        }
        abort_unless($path, 404);
        return Response::file($path);
    })->where(['folder' => 'purchases|profiles|prescriptions', 'filename' => '[A-Za-z0-9_.\-]+']);

    Route::get('storage/purchases/{filename}', function ($filename) {
        return redirect('storage/system/purchases/'.$filename, 301);
    })->where('filename', '[A-Za-z0-9_.\-]+');
});

Route::middleware(['guest'])->group(function () {
    Route::get('',function(){
        return redirect()->route('login');
    });

    Route::get('login',[LoginController::class,'index'])->name('login');
    Route::post('login',[LoginController::class,'login']);
    Route::post('login/two-factor',[LoginController::class,'verifyTwoFactor'])->name('login.two-factor');

    Route::get('register',[RegisterController::class,'index'])->name('register');
    Route::post('register',[RegisterController::class,'store']);

    Route::get('forgot-password',[ForgotPasswordController::class,'index'])->name('password.request');
    Route::post('forgot-password',[ForgotPasswordController::class,'requestEmail']);
    Route::get('reset-password/{token}',[ResetPasswordController::class,'index'])->name('password.reset');
    Route::post('reset-password',[ResetPasswordController::class,'resetPassword'])->name('password.update');
});
