<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DomainApiController;
use App\Http\Controllers\Api\MailboxApiController;
use App\Http\Controllers\Api\AliasApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\LogApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Authentication
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // Domains
        Route::apiResource('domains', DomainApiController::class);
        Route::post('domains/{domain}/dkim', [DomainApiController::class, 'generateDkim'])->name('domains.dkim');

        // Mailboxes
        Route::apiResource('mailboxes', MailboxApiController::class);

        // Aliases
        Route::apiResource('aliases', AliasApiController::class);

        // Users (admin only)
        Route::apiResource('users', UserApiController::class);

        // Logs
        Route::get('logs', [LogApiController::class, 'index'])->name('logs.index');
        Route::get('logs/{log}', [LogApiController::class, 'show'])->name('logs.show');
        Route::get('logs/stats/summary', [LogApiController::class, 'summary'])->name('logs.summary');
    });
});
