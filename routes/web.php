<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\HotspotProfileController;
use App\Http\Controllers\HotspotUserController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\RouterSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout Route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Management Application Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/live', [DashboardController::class, 'liveData'])->name('api.dashboard.live');

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/api/live', [UserController::class, 'liveData'])->name('users.live');
        Route::get('/historical', [UserController::class, 'historical'])->name('users.historical');
        Route::get('/{username}', [UserController::class, 'show'])->name('users.show');
        Route::post('/{username}/disconnect', [UserController::class, 'disconnect'])->name('users.disconnect');
    });

    // Devices / Sessions
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/api/devices/live', [DeviceController::class, 'liveData'])->name('devices.live');

    // Hotspot
    Route::prefix('hotspot')->group(function () {
        Route::get('/profiles', [HotspotProfileController::class, 'index'])->name('hotspot.profiles');
        Route::post('/profiles', [HotspotProfileController::class, 'store'])->name('hotspot.profiles.store');
        Route::post('/profiles/sync', [HotspotProfileController::class, 'syncFromRouter'])->name('hotspot.profiles.sync');
        Route::put('/profiles/{profile}', [HotspotProfileController::class, 'update'])->name('hotspot.profiles.update');
        Route::delete('/profiles/{profile}', [HotspotProfileController::class, 'destroy'])->name('hotspot.profiles.destroy');

        Route::get('/users', [HotspotUserController::class, 'index'])->name('hotspot.users');
        Route::post('/users', [HotspotUserController::class, 'store'])->name('hotspot.users.store');
        Route::post('/users/sync', [HotspotUserController::class, 'syncFromRouter'])->name('hotspot.users.sync');
        Route::put('/users/{user}', [HotspotUserController::class, 'update'])->name('hotspot.users.update');
        Route::delete('/users/{user}', [HotspotUserController::class, 'destroy'])->name('hotspot.users.destroy');

        Route::get('/generate', [VoucherController::class, 'generateView'])->name('hotspot.generate');
        Route::post('/generate', [VoucherController::class, 'generateBatch'])->name('hotspot.generate.batch');
    });

    // Voucher Printing
    Route::prefix('vouchers/print')->group(function () {
        Route::get('/58mm', [VoucherController::class, 'print58mm'])->name('vouchers.print.58mm');
        Route::get('/80mm', [VoucherController::class, 'print80mm'])->name('vouchers.print.80mm');
        Route::get('/grid', [VoucherController::class, 'printGrid'])->name('vouchers.print.grid');
    });

    // POS & Kasir
    Route::prefix('pos')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('pos.index');
        Route::get('/vouchers', [PosController::class, 'vouchers'])->name('pos.vouchers');
        Route::get('/monthly', [PosController::class, 'monthly'])->name('pos.monthly');
        Route::post('/monthly/customers', [PosController::class, 'storeCustomer'])->name('pos.monthly.customers.store');
        Route::put('/monthly/customers/{customer}', [PosController::class, 'updateCustomer'])->name('pos.monthly.customers.update');
        Route::delete('/monthly/customers/{customer}', [PosController::class, 'deleteCustomer'])->name('pos.monthly.customers.delete');
        Route::post('/monthly/payments', [PosController::class, 'storePayment'])->name('pos.monthly.payments.store');
        Route::post('/monthly/payments/{payment}/void', [PosController::class, 'voidPayment'])->name('pos.monthly.payments.void');

        Route::get('/shifts', [PosController::class, 'shifts'])->name('pos.shifts');
        Route::post('/shifts/open', [PosController::class, 'openShift'])->name('pos.shifts.open');
        Route::post('/shifts/close', [PosController::class, 'closeShift'])->name('pos.shifts.close');
    });

    // Settings & System
    Route::prefix('settings')->group(function () {
        Route::get('/router', [RouterSettingController::class, 'index'])->name('settings.router');
        Route::post('/router', [RouterSettingController::class, 'update'])->name('settings.router.update');
        Route::post('/router/test', [RouterSettingController::class, 'testConnection'])->name('settings.router.test');
        Route::post('/router/sync', [RouterSettingController::class, 'syncAll'])->name('settings.router.sync');

        Route::get('/roles', function () {
            return view('settings.roles');
        })->name('settings.roles');

        Route::get('/templates', function () {
            return view('settings.templates');
        })->name('settings.templates');
    });

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
});


