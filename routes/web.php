<?php

use App\Http\Controllers\Install\InstallController;
use App\Livewire\Admin\BackupManagement;
use App\Livewire\Admin\CacheManagement;
use App\Livewire\Admin\GeneralSettings;
use App\Livewire\Admin\LanguageManager;
use App\Livewire\Admin\MailSettings;
use App\Livewire\Admin\SystemInformation;
use App\Livewire\Admin\UserApprovalManager;
use App\Livewire\Category\CategoryManager;
use App\Livewire\Inventory\StockInManager;
use App\Livewire\Product\ProductManager;
use App\Livewire\Report\ReportManager;
use App\Livewire\Requisition\CreateRequisition;
use App\Livewire\Requisition\MyRequisitions;
use App\Livewire\Workflow\ApprovalQueue;
use App\Livewire\Workflow\FinalPrint;
use App\Livewire\Workflow\InitiatorQueue;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'install', 'as' => 'install.'], function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('requirements', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('permissions', [InstallController::class, 'permissions'])->name('permissions');
    Route::get('environment', [InstallController::class, 'environment'])->name('environment');
    Route::post('environment/save', [InstallController::class, 'saveEnvironment'])->name('environment.save');
    Route::get('run-install', [InstallController::class, 'runInstall'])->name('run');
    Route::get('account', [InstallController::class, 'account'])->name('account');
    Route::post('account', [InstallController::class, 'storeAccount'])->name('account.store');

    //    Route::resource('accounts', AccountController::class)->only(['index', 'store']);
    //    Route::resource('licenses', LicenseController::class)->only(['index', 'store']);
    //    Route::get('final', [FinalController::class, 'index'])->name('final');
    //
    //    Route::post('licenses/skip', [LicenseController::class, 'skip'])->name('licenses.skip');
});

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'approved'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('/admin/categories', CategoryManager::class)->name('admin.categories');
    Route::get('/admin/products', ProductManager::class)->name('admin.products');
    Route::get('/admin/user-approvals', UserApprovalManager::class)->name('admin.user_approvals');
    Route::get('/requisition/create', CreateRequisition::class)->name('requisition.create');
    Route::get('/requisition/my-history', MyRequisitions::class)->name('requisition.my_history');
    Route::get('/workflow/initiator-queue', InitiatorQueue::class)->name('workflow.initiator');
    Route::get('/workflow/approval-queue', ApprovalQueue::class)->name('workflow.approval');
    Route::get('/workflow/print/{id}', FinalPrint::class)->name('workflow.print');
    Route::get('/inventory/stock-in', StockInManager::class)->name('inventory.stock_in');
    Route::get('/report/summary', ReportManager::class)->name('report.summary');
    Route::get('/admin/language-settings', LanguageManager::class)->name('admin.language_settings');
    Route::get('/admin/mail-settings', MailSettings::class)->name('admin.mail_settings');
    Route::get('/admin/system-info', SystemInformation::class)->name('admin.system_info');
    Route::get('/admin/cache-management', CacheManagement::class)->name('admin.cache_management');
    Route::get('/admin/general-settings', GeneralSettings::class)->name('admin.general_settings');
    Route::get('/admin/database-backup', BackupManagement::class)->name('admin.backup');
});

require __DIR__.'/settings.php';
