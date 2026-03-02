<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\OptionController;

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
    Route::get('options', [OptionController::class, 'index']);
});
