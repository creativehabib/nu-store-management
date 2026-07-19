<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InventoryDataController;
use App\Http\Controllers\Api\V1\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('api.token')
    ->group(function (): void {
        Route::post('auth/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

        Route::middleware('api.user.token')->group(function (): void {
            Route::get('auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::get('dashboard', [DashboardController::class, 'index'])->name('api.v1.dashboard.index');
            Route::get('my-requisitions', [InventoryDataController::class, 'myRequisitions'])->name('api.v1.my_requisitions.index');
        });
        Route::get('inventory', [InventoryDataController::class, 'index'])->name('api.v1.inventory.index');
        Route::get('categories', [InventoryDataController::class, 'categories'])->name('api.v1.categories.index');
        Route::get('departments', [InventoryDataController::class, 'departments'])->name('api.v1.departments.index');
        Route::get('designations', [InventoryDataController::class, 'designations'])->name('api.v1.designations.index');
        Route::get('products', [InventoryDataController::class, 'products'])->name('api.v1.products.index');
        Route::get('purposes', [InventoryDataController::class, 'purposes'])->name('api.v1.purposes.index');
        Route::get('requisitions', [InventoryDataController::class, 'requisitions'])->name('api.v1.requisitions.index');
        Route::get('settings', [SettingsController::class, 'index'])->name('api.v1.settings.index');
        Route::get('stock-entries', [InventoryDataController::class, 'stockEntries'])->name('api.v1.stock_entries.index');
    });
