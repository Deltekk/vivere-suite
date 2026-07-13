<?php

use Illuminate\Support\Facades\Route;
use Modules\Eventi\Http\Controllers\EventiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('eventis', EventiController::class)->names('eventi');
});
