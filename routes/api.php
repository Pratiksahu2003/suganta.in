<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\OptionController;
use App\Http\Controllers\Api\V1\RegistrationController;
use App\Http\Controllers\Api\V1\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api and use the 'api' middleware group
| (throttling, SubstituteBindings). Use version prefixes (e.g. v1) for
| backward-compatible API versioning.
|
*/

Route::prefix('v1')->group(function (): void {
    // Auth Routes
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('forgot-password', 'forgotPassword');
        Route::post('reset-password', 'resetPassword');
        
        // Protected Routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', 'logout');
            Route::post('logout-all', 'logoutFromAllDevices');
            Route::post('refresh-token', 'refreshToken');
        });
    });

    Route::get('options', [OptionController::class, 'index']);
    Route::get('registration/charges', [RegistrationController::class, 'charges']);
});
