<?php

use App\Http\Controllers\Installer\InstallerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installer Routes
|--------------------------------------------------------------------------
| These routes are excluded from the InstalledMiddleware.
| They are only accessible when APP_INSTALLED=false.
*/

Route::prefix('install')->name('installer.')->middleware('installer')->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
    Route::get('/permissions', [InstallerController::class, 'permissions'])->name('permissions');
    Route::post('/permissions/fix', [InstallerController::class, 'fixPermissions'])->name('fix-permissions');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database/test', [InstallerController::class, 'testDatabase'])->name('test-database');
    Route::get('/mail-config', [InstallerController::class, 'mailConfig'])->name('mail-config');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::get('/finalize', function () { return view('installer.finalize'); })->name('finalize');
    Route::post('/run', [InstallerController::class, 'install'])->name('run');
    Route::get('/complete', [InstallerController::class, 'complete'])->name('complete');
});
