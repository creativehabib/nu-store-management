<?php

use App\Http\Controllers\Api\V1\InventoryDataController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('api.token')
    ->group(function (): void {
        Route::get('inventory', [InventoryDataController::class, 'index'])->name('api.v1.inventory.index');
        Route::get('categories', [InventoryDataController::class, 'categories'])->name('api.v1.categories.index');
        Route::get('departments', [InventoryDataController::class, 'departments'])->name('api.v1.departments.index');
        Route::get('designations', [InventoryDataController::class, 'designations'])->name('api.v1.designations.index');
        Route::get('products', [InventoryDataController::class, 'products'])->name('api.v1.products.index');
        Route::get('purposes', [InventoryDataController::class, 'purposes'])->name('api.v1.purposes.index');
        Route::get('requisitions', [InventoryDataController::class, 'requisitions'])->name('api.v1.requisitions.index');
        Route::get('stock-entries', [InventoryDataController::class, 'stockEntries'])->name('api.v1.stock_entries.index');
    });
