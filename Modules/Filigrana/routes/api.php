<?php

use Illuminate\Support\Facades\Route;
use Modules\Filigrana\Http\Controllers\FiligranaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('filigranas', FiligranaController::class)->names('filigrana');
});
