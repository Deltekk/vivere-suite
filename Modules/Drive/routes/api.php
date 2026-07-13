<?php

use Illuminate\Support\Facades\Route;
use Modules\Drive\Http\Controllers\DriveController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('drives', DriveController::class)->names('drive');
});
