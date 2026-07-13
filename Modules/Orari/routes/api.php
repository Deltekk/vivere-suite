<?php

use Illuminate\Support\Facades\Route;
use Modules\Orari\Http\Controllers\OrariController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('oraris', OrariController::class)->names('orari');
});
