<?php

use Illuminate\Support\Facades\Route;
use Modules\Magazzino\Http\Controllers\MagazzinoController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('magazzinos', MagazzinoController::class)->names('magazzino');
});
