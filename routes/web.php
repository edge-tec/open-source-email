<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MailboxController;
use App\Http\Controllers\Admin\AliasController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\SpamController;
use App\Http\Controllers\Admin\StorageController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Webmail\InboxController;
use App\Http\Controllers\Webmail\ComposeController;
use App\Http\Controllers\Webmail\MessageController;
use App\Http\Controllers\Webmail\FolderController;
use App\Http\Controllers\Webmail\SettingsController as WebmailSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Load installer routes
require __DIR__ . '/installer.php';

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if (in_array($user->role, ['super_admin', 'admin', 'reseller'])) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('webmail.inbox');
    }
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Two-Factor Authentication
Route::middleware('auth')->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.verify');
    Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify.submit');
});

// Admin Panel
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'two-factor'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Domain Management
    Route::resource('domains', DomainController::class);
    Route::post('domains/{domain}/generate-dkim', [DomainController::class, 'generateDkim'])->name('domains.generate-dkim');
    Route::post('domains/{domain}/ssl', [DomainController::class, 'installSsl'])->name('domains.ssl');

    // User Management
    Route::resource('users', UserController::class);

    // Mailbox Management
    Route::resource('mailboxes', MailboxController::class);
    Route::get('mailboxes/{mailbox}/webmail', [MailboxController::class, 'webmailLogin'])->name('mailboxes.webmail');

    // Alias Management
    Route::resource('aliases', AliasController::class);

    // Logs
    Route::get('logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('logs/{log}', [LogController::class, 'show'])->name('logs.show');

    // Queue Management
    Route::get('queue', [QueueController::class, 'index'])->name('queue.index');
    Route::post('queue/{id}/retry', [QueueController::class, 'retry'])->name('queue.retry');
    Route::delete('queue/{id}', [QueueController::class, 'destroy'])->name('queue.destroy');

    // Spam Management
    Route::get('spam', [SpamController::class, 'index'])->name('spam.index');
    Route::post('spam/filters', [SpamController::class, 'storeFilter'])->name('spam.store-filter');
    Route::delete('spam/filters/{filter}', [SpamController::class, 'destroyFilter'])->name('spam.destroy-filter');

    // Storage
    Route::get('storage', [StorageController::class, 'index'])->name('storage.index');

    // Security
    Route::get('security', [SecurityController::class, 'index'])->name('security.index');
    Route::post('security/ssl/self-signed', [SecurityController::class, 'generateSelfSigned'])->name('security.ssl.self-signed');
    Route::post('security/ssl/custom', [SecurityController::class, 'installCustomSsl'])->name('security.ssl.custom');
});

// Webmail
Route::prefix('webmail')->name('webmail.')->middleware(['auth', 'two-factor'])->group(function () {
    Route::get('/', [InboxController::class, 'index'])->name('inbox');
    Route::get('/folder/{folder}', [InboxController::class, 'folder'])->name('folder');
    Route::get('/search', [InboxController::class, 'search'])->name('search');
    Route::post('/bulk', [InboxController::class, 'bulk'])->name('bulk');
    Route::post('/trash/empty', [InboxController::class, 'emptyTrash'])->name('trash.empty');

    Route::get('/compose', [ComposeController::class, 'create'])->name('compose');
    Route::post('/compose', [ComposeController::class, 'send'])->name('send');
    Route::post('/compose/upload', [ComposeController::class, 'uploadAttachment'])->name('upload');

    Route::get('/message/{uid}', [MessageController::class, 'show'])->name('message.show');
    Route::post('/message/{uid}/reply', [MessageController::class, 'reply'])->name('message.reply');
    Route::post('/message/{uid}/forward', [MessageController::class, 'forward'])->name('message.forward');
    Route::post('/message/{uid}/move', [MessageController::class, 'move'])->name('message.move');
    Route::post('/message/{uid}/toggle-read', [MessageController::class, 'toggleRead'])->name('message.toggle-read');
    Route::delete('/message/{uid}', [MessageController::class, 'destroy'])->name('message.destroy');

    Route::get('/folders', [FolderController::class, 'index'])->name('folders');
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    Route::get('/settings', [WebmailSettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [WebmailSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/filter', [WebmailSettingsController::class, 'addFilter'])->name('settings.filter.add');
    Route::delete('/settings/filter/{id}', [WebmailSettingsController::class, 'destroyFilter'])->name('settings.filter.destroy');
});
